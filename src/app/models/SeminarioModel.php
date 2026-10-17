<?php
/**
 * SeminarioModel
 * Lo que hay que pagarle al Seminario Teológico Bautista de Venezuela
 * por cada participante.
 *
 * Según la nota del STBV del 31/08/2026, el primer trimestre son
 * USD 130 por misionero: inscripción 25, carnet 10, ternanet 20 y
 * tres mensualidades de 25. El segundo trimestre son las otras tres
 * mensualidades.
 *
 * Esta plata sale de la matrícula que paga el participante, así que
 * acá no hay deuda nueva para él: es el control de cuánto le toca al
 * Seminario por cada uno y qué se le ha pagado. El pago real vive en
 * `gastos`; cada cargo guarda con qué gasto se saldó.
 */

require_once APP_PATH . '/models/Model.php';

class SeminarioModel extends Model {

    protected string $tabla = 'seminario_cargos';

    public const CONCEPTOS = [
        'inscripcion'  => 'Inscripción',
        'carnet'       => 'Carnet institucional',
        'ternanet'     => 'Sistema Ternanet',
        'mensualidad'  => 'Mensualidad',
        'otro'         => 'Otro',
    ];

    /**
     * Cargos estándar del ciclo. Los meses del segundo trimestre se
     * pueden cambiar desde el panel si el Seminario mueve el calendario.
     */
    public const PLANTILLA = [
        [1, 'inscripcion', 'Inscripción (expediente y entrevista)', 25.00],
        [1, 'carnet',      'Carnet institucional',                  10.00],
        [1, 'ternanet',    'Sistema Ternanet',                      20.00],
        [1, 'mensualidad', 'Mensualidad septiembre',                25.00],
        [1, 'mensualidad', 'Mensualidad octubre',                   25.00],
        [1, 'mensualidad', 'Mensualidad noviembre',                 25.00],
        [2, 'mensualidad', 'Mensualidad enero',                     25.00],
        [2, 'mensualidad', 'Mensualidad febrero',                   25.00],
        [2, 'mensualidad', 'Mensualidad marzo',                     25.00],
    ];

    /**
     * Carga la plantilla a un participante que todavía no la tiene
     */
    public function generarCargos(int $aspirante_id): int {
        if ($this->contar('aspirante_id = :id', [':id' => $aspirante_id]) > 0) return 0;

        $insert = $this->db->prepare("
            INSERT INTO seminario_cargos
                (aspirante_id, trimestre, concepto, descripcion, monto_usd, orden)
            VALUES (:asp, :tri, :con, :desc, :monto, :orden)
        ");

        foreach (self::PLANTILLA as $i => [$trimestre, $concepto, $descripcion, $monto]) {
            $insert->execute([
                ':asp'   => $aspirante_id,
                ':tri'   => $trimestre,
                ':con'   => $concepto,
                ':desc'  => $descripcion,
                ':monto' => $monto,
                ':orden' => $i + 1,
            ]);
        }
        return count(self::PLANTILLA);
    }

    /**
     * Carga la plantilla a todos los participantes aprobados que
     * todavía no la tengan. Devuelve a cuántos se les generó.
     */
    public function generarParaAprobados(): int {
        $ids = $this->db->query(
            "SELECT id FROM aspirantes WHERE estatus = 'aprobada'"
        )->fetchAll(PDO::FETCH_COLUMN);

        $generados = 0;
        foreach ($ids as $id) {
            if ($this->generarCargos((int) $id) > 0) $generados++;
        }
        return $generados;
    }

    /**
     * Cargos de un participante
     */
    public function cargosPorAspirante(int $aspirante_id): array {
        $stmt = $this->db->prepare("
            SELECT c.*, g.fecha_gasto, g.referencia AS gasto_referencia
            FROM seminario_cargos c
            LEFT JOIN gastos g ON g.id = c.gasto_id AND g.estatus = 'activo'
            WHERE c.aspirante_id = :id
            ORDER BY c.trimestre ASC, c.orden ASC, c.id ASC
        ");
        $stmt->execute([':id' => $aspirante_id]);
        return $stmt->fetchAll();
    }

    /**
     * Estado por participante: cuánto le toca al STBV por él y
     * cuánto se le ha pagado ya.
     */
    public function resumenPorEstudiante(): array {
        return $this->db->query("
            SELECT a.id AS aspirante_id,
                   CONCAT(a.nombres, ' ', a.apellidos) AS nombre,
                   a.cedula,
                   COUNT(c.id)                                                             AS cargos,
                   COALESCE(SUM(CASE WHEN c.estatus <> 'exonerado' THEN c.monto_usd END), 0) AS total_usd,
                   COALESCE(SUM(CASE WHEN c.estatus = 'pagado'   THEN c.monto_usd END), 0) AS pagado_usd,
                   COALESCE(SUM(CASE WHEN c.estatus = 'pendiente' THEN c.monto_usd END), 0) AS pendiente_usd
            FROM aspirantes a
            LEFT JOIN seminario_cargos c ON c.aspirante_id = a.id
            WHERE a.estatus = 'aprobada'
            GROUP BY a.id
            ORDER BY a.apellidos ASC, a.nombres ASC
        ")->fetchAll();
    }

    /**
     * Cargos pendientes de pagar, con el nombre del participante.
     * Es la lista que se marca al registrarle un pago al Seminario.
     */
    public function pendientes(): array {
        return $this->db->query("
            SELECT c.*,
                   CONCAT(a.nombres, ' ', a.apellidos) AS nombre,
                   a.cedula
            FROM seminario_cargos c
            JOIN aspirantes a ON a.id = c.aspirante_id
            WHERE c.estatus = 'pendiente'
            ORDER BY a.apellidos ASC, a.nombres ASC, c.trimestre ASC, c.orden ASC
        ")->fetchAll();
    }

    /**
     * Totales de toda la cohorte
     */
    public function totales(): array {
        $row = $this->db->query("
            SELECT
                COUNT(DISTINCT c.aspirante_id)                                          AS participantes,
                COALESCE(SUM(CASE WHEN c.estatus <> 'exonerado' THEN c.monto_usd END), 0) AS total,
                COALESCE(SUM(CASE WHEN c.estatus = 'pagado'    THEN c.monto_usd END), 0) AS pagado,
                COALESCE(SUM(CASE WHEN c.estatus = 'pendiente' THEN c.monto_usd END), 0) AS pendiente
            FROM seminario_cargos c
            JOIN aspirantes a ON a.id = c.aspirante_id AND a.estatus = 'aprobada'
        ")->fetch();

        $total = (float) $row['total'];

        return [
            'participantes' => (int) $row['participantes'],
            'total'         => $total,
            'pagado'        => (float) $row['pagado'],
            'pendiente'     => (float) $row['pendiente'],
            'porcentaje'    => $total > 0 ? min(round((float) $row['pagado'] / $total * 100), 100) : 0,
        ];
    }

    /**
     * Marca como pagados los cargos que cubre un gasto.
     * Solo toca los que siguen pendientes, por si llega repetido.
     */
    public function marcarPagados(array $cargo_ids, int $gasto_id, string $fecha): int {
        $ids = array_values(array_filter(array_map('intval', $cargo_ids)));
        if (empty($ids)) return 0;

        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("
            UPDATE seminario_cargos
            SET estatus = 'pagado', gasto_id = ?, fecha_pago = ?
            WHERE id IN ($marcadores) AND estatus = 'pendiente'
        ");
        $stmt->execute(array_merge([$gasto_id, $fecha], $ids));
        return $stmt->rowCount();
    }

    /**
     * Suma de los cargos indicados que todavía están pendientes
     */
    public function totalDeCargos(array $cargo_ids): float {
        $ids = array_values(array_filter(array_map('intval', $cargo_ids)));
        if (empty($ids)) return 0.0;

        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(monto_usd), 0) FROM seminario_cargos
            WHERE id IN ($marcadores) AND estatus = 'pendiente'
        ");
        $stmt->execute($ids);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Agrega un cargo suelto a un participante
     */
    public function agregarCargo(array $d): int {
        return $this->insertar([
            'aspirante_id' => $d['aspirante_id'],
            'trimestre'    => $d['trimestre'],
            'concepto'     => $d['concepto'],
            'descripcion'  => $d['descripcion'],
            'monto_usd'    => $d['monto_usd'],
            'orden'        => 99,
            'notas'        => $d['notas'] ?: null,
        ]);
    }

    /**
     * Edita un cargo. Lo ya pagado no se toca: para corregir un pago
     * se anula el gasto y se vuelve a registrar.
     */
    public function editarCargo(int $id, array $d): bool {
        $cargo = $this->porId($id);
        if (!$cargo || $cargo['estatus'] === 'pagado') return false;

        return $this->actualizar($id, [
            'descripcion' => $d['descripcion'],
            'monto_usd'   => $d['monto_usd'],
            'trimestre'   => $d['trimestre'],
            'estatus'     => $d['estatus'],
            'notas'       => $d['notas'] ?: null,
        ]);
    }

    /**
     * Borra un cargo que todavía no se ha pagado
     */
    public function eliminarCargo(int $id): bool {
        $cargo = $this->porId($id);
        if (!$cargo || $cargo['estatus'] === 'pagado') return false;
        return $this->eliminar($id);
    }

    /**
     * Libera los cargos que había cubierto un gasto anulado
     */
    public function liberarPorGasto(int $gasto_id): void {
        $this->db->prepare("
            UPDATE seminario_cargos
            SET estatus = 'pendiente', gasto_id = NULL, fecha_pago = NULL
            WHERE gasto_id = :id
        ")->execute([':id' => $gasto_id]);
    }
}
