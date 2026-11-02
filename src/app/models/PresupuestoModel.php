<?php
/**
 * PresupuestoModel
 * El presupuesto aprobado del ciclo, fase por fase, contra lo que
 * de verdad se gastó.
 *
 * Lo gastado se parte en dos: lo que estaba previsto y lo adicional
 * (gastos.cobertura). Lo adicional es, sobre todo, lo que debían cubrir
 * las iglesias anfitrionas los fines de semana y terminó pagando el
 * programa.
 */

require_once APP_PATH . '/models/Model.php';

class PresupuestoModel extends Model {

    protected string $tabla = 'presupuesto_fases';

    /**
     * Rubros del presupuesto y qué categorías de gasto caen en cada uno.
     * Contingencia no tiene categoría: es un margen, no se gasta como tal.
     * Las devoluciones de préstamos no son gasto del programa y no entran.
     */
    public const RUBROS = [
        'alimentacion'           => ['Alimentación (olla común)',          ['alimentacion']],
        'hospedaje'              => ['Hospedaje / alquiler de sede',       ['hospedaje']],
        'transporte'             => ['Transporte y traslados',             ['transporte']],
        'seminario'              => ['Seminario (STBV)',                   ['seminario_inscripcion', 'seminario_mensualidad']],
        'materiales'             => ['Materiales y talleres',              ['materiales']],
        'coordinacion'           => ['Coordinación y logística',           ['coordinacion']],
        'administrativo'         => ['Oficina, estudio y administrativos', ['administrativo']],
        'personal_apoyo'         => ['Apoyo a cocineros y personal',       ['personal_apoyo']],
        'viaticos_facilitadores' => ['Facilitadores',                      ['viaticos_facilitadores']],
        'uniformes'              => ['Uniformes y franelas',               ['uniformes']],
        'otro'                   => ['Otros',                              ['otro']],
        'contingencia'           => ['Contingencia',                       []],
    ];

    public function fases(int $ciclo = 1): array {
        $stmt = $this->db->prepare("
            SELECT * FROM presupuesto_fases WHERE ciclo = :c ORDER BY numero
        ");
        $stmt->execute([':c' => $ciclo]);
        return $stmt->fetchAll();
    }

    /** La fase en curso, o la última que ya empezó */
    public function faseActual(array $fases): ?array {
        $hoy    = date('Y-m-d');
        $ultima = null;
        foreach ($fases as $f) {
            if ($f['fecha_inicio'] <= $hoy) $ultima = $f;
        }
        return $ultima ?? ($fases[0] ?? null);
    }

    /**
     * Cuadro por fase y rubro.
     *
     * Devuelve [fase_id => [rubro => [presupuesto, previsto, adicional]]].
     * La clave 0 junta lo gastado fuera de las fechas de cualquier fase
     * (preparación antes del inicio, pausa decembrina).
     */
    public function cuadro(int $ciclo = 1): array {
        $categoria_rubro = [];
        foreach (self::RUBROS as $rubro => [, $categorias]) {
            foreach ($categorias as $c) $categoria_rubro[$c] = $rubro;
        }

        $cuadro = [];
        $vacio  = ['presupuesto' => 0.0, 'previsto' => 0.0, 'adicional' => 0.0];

        $stmt = $this->db->prepare("
            SELECT m.fase_id, m.rubro, m.monto_usd
            FROM presupuesto_montos m
            JOIN presupuesto_fases f ON f.id = m.fase_id
            WHERE f.ciclo = :c
        ");
        $stmt->execute([':c' => $ciclo]);
        foreach ($stmt->fetchAll() as $r) {
            $cuadro[$r['fase_id']][$r['rubro']] ??= $vacio;
            $cuadro[$r['fase_id']][$r['rubro']]['presupuesto'] += (float) $r['monto_usd'];
        }

        // Todo lo gastado hasta el cierre del ciclo; lo de después es del siguiente
        $stmt = $this->db->prepare("
            SELECT f.id AS fase_id, g.categoria, g.cobertura, SUM(g.monto_usd) AS total
            FROM gastos g
            LEFT JOIN presupuesto_fases f
                   ON f.ciclo = :c AND g.fecha_gasto BETWEEN f.fecha_inicio AND f.fecha_fin
            WHERE g.estatus = 'activo'
              AND g.categoria <> 'devolucion_prestamo'
              AND g.fecha_gasto <= (SELECT MAX(fecha_fin) FROM presupuesto_fases WHERE ciclo = :c2)
            GROUP BY f.id, g.categoria, g.cobertura
        ");
        $stmt->execute([':c' => $ciclo, ':c2' => $ciclo]);
        foreach ($stmt->fetchAll() as $r) {
            $fase  = (int) $r['fase_id'];
            $rubro = $categoria_rubro[$r['categoria']] ?? 'otro';
            $cuadro[$fase][$rubro] ??= $vacio;
            $cuadro[$fase][$rubro][$r['cobertura'] === 'adicional' ? 'adicional' : 'previsto'] += (float) $r['total'];
        }

        return $cuadro;
    }

    /**
     * Qué fases mira el informe según ?fase=
     *   (vacío) la fase en curso · un id · 'hoy' las que ya empezaron · 'ciclo' todas
     * Las vistas de varias fases incluyen lo gastado fuera de fase (id 0).
     *
     * @return array{0: int[], 1: string, 2: string}  ids, etiqueta, clave
     */
    public function seleccion(array $fases, string $ver): array {
        if ($ver === 'ciclo') {
            return [array_merge(array_map(fn($f) => (int) $f['id'], $fases), [0]), 'Ciclo completo', 'ciclo'];
        }
        if ($ver === 'hoy') {
            $hoy = date('Y-m-d');
            $ids = array_map(fn($f) => (int) $f['id'], array_filter($fases, fn($f) => $f['fecha_inicio'] <= $hoy));
            return [array_merge(array_values($ids), [0]), 'Lo que va del ciclo', 'hoy'];
        }
        foreach ($fases as $f) {
            if ((string) $f['id'] === $ver) return [[(int) $f['id']], $f['nombre'], $ver];
        }
        $actual = $this->faseActual($fases);
        return $actual ? [[(int) $actual['id']], $actual['nombre'], (string) $actual['id']] : [[], '', ''];
    }

    /**
     * Suma el cuadro de las fases pedidas, rubro por rubro y en el orden
     * del presupuesto. Los rubros sin plata presupuestada ni gastada no salen.
     */
    public function filas(array $cuadro, array $fase_ids): array {
        $filas = [];
        foreach (self::RUBROS as $rubro => [$nombre]) {
            $fila = ['rubro' => $rubro, 'nombre' => $nombre, 'presupuesto' => 0.0, 'previsto' => 0.0, 'adicional' => 0.0];
            foreach ($fase_ids as $id) {
                foreach (['presupuesto', 'previsto', 'adicional'] as $k) {
                    $fila[$k] += $cuadro[$id][$rubro][$k] ?? 0;
                }
            }
            if ($fila['presupuesto'] || $fila['previsto'] || $fila['adicional']) $filas[] = $fila;
        }
        return $filas;
    }

    /** Totales de una lista de filas (o del cuadro de una fase) */
    public static function totales(array $filas): array {
        $t = ['presupuesto' => 0.0, 'previsto' => 0.0, 'adicional' => 0.0];
        foreach ($filas as $f) {
            foreach ($t as $k => $_) $t[$k] += $f[$k];
        }
        $t['gastado']    = $t['previsto'] + $t['adicional'];
        $t['diferencia'] = $t['presupuesto'] - $t['gastado'];
        return $t;
    }

    /**
     * Gastos marcados como adicionales, con su motivo y la fase en que caen
     * (fase_id 0 = fuera de fase). $fase_ids vacío = todo el ciclo.
     */
    public function adicionales(array $fases, array $fase_ids = []): array {
        if (!$fases) return [];

        $stmt = $this->db->prepare("
            SELECT g.id, g.fecha_gasto, g.concepto, g.categoria, g.beneficiario,
                   g.monto_usd, g.monto_ves, g.cobertura_motivo, g.comprobante_ruta
            FROM gastos g
            WHERE g.estatus = 'activo'
              AND g.cobertura = 'adicional'
              AND g.categoria <> 'devolucion_prestamo'
              AND g.fecha_gasto <= :cierre
            ORDER BY g.fecha_gasto, g.id
        ");
        $stmt->execute([':cierre' => end($fases)['fecha_fin']]);

        $lista = [];
        foreach ($stmt->fetchAll() as $g) {
            $g['fase_id'] = 0;
            foreach ($fases as $f) {
                if ($g['fecha_gasto'] >= $f['fecha_inicio'] && $g['fecha_gasto'] <= $f['fecha_fin']) {
                    $g['fase_id'] = (int) $f['id'];
                    break;
                }
            }
            if (!$fase_ids || in_array($g['fase_id'], $fase_ids, true)) $lista[] = $g;
        }
        return $lista;
    }
}
