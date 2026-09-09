<?php
/**
 * GastoModel
 * Egresos del programa de formación.
 *
 * Las categorías siguen los rubros del presupuesto del Ciclo 1
 * para que el informe salga directo, sin reclasificar nada.
 */

require_once APP_PATH . '/models/Model.php';

class GastoModel extends Model {

    protected string $tabla = 'gastos';

    public const CATEGORIAS = [
        'alimentacion'           => 'Alimentación (olla común)',
        'hospedaje'              => 'Hospedaje / alquiler de sede',
        'transporte'             => 'Transporte y traslados',
        'seminario_inscripcion'  => 'Seminario — inscripción',
        'seminario_mensualidad'  => 'Seminario — mensualidad',
        'materiales'             => 'Materiales y talleres',
        'uniformes'              => 'Uniformes y franelas',
        'personal_apoyo'         => 'Apoyo a cocineros y personal',
        'viaticos_facilitadores' => 'Viáticos de facilitadores',
        'coordinacion'           => 'Coordinación y logística',
        'administrativo'         => 'Gastos administrativos',
        'devolucion_prestamo'    => 'Devolución de préstamo',
        'otro'                   => 'Otro',
    ];

    /**
     * Registrar un gasto
     */
    public function registrar(array $d, int $usuario_id): int {
        $id = $this->insertar([
            'fecha_gasto'      => $d['fecha_gasto'],
            'concepto'         => $d['concepto'],
            'categoria'        => $d['categoria'],
            'fondo_id'         => $d['fondo_id']    ?: null,
            'cuenta_id'        => $d['cuenta_id']   ?: null,
            'prestamo_id'      => $d['prestamo_id'] ?: null,
            'beneficiario'     => $d['beneficiario'] ?: null,
            'monto_usd'        => $d['monto_usd'],
            'monto_ves'        => $d['monto_ves']   ?: null,
            'tasa_cambio'      => $d['tasa_cambio'] ?: null,
            'metodo_pago'      => $d['metodo_pago'],
            'referencia'       => $d['referencia']  ?: null,
            'comprobante_ruta' => $d['comprobante_ruta'] ?? null,
            'registrado_por'   => $usuario_id,
            'notas'            => $d['notas'] ?: null,
        ]);

        if (!empty($d['prestamo_id'])) {
            (new PrestamoModel())->actualizarEstatus((int) $d['prestamo_id']);
        }

        return $id;
    }

    /**
     * Edita un gasto ya registrado.
     *
     * Si el gasto era (o pasa a ser) devolución de un préstamo, hay
     * que revisar el estatus de los dos préstamos involucrados.
     */
    public function editar(int $id, array $d): bool {
        $antes = $this->porId($id);
        if (!$antes) return false;

        $prestamo_id = $d['prestamo_id'] ?: null;

        $campos = [
            'fecha_gasto'  => $d['fecha_gasto'],
            'concepto'     => $d['concepto'],
            'categoria'    => $d['categoria'],
            'fondo_id'     => $d['fondo_id']  ?: null,
            'cuenta_id'    => $d['cuenta_id'] ?: null,
            'prestamo_id'  => $prestamo_id,
            'beneficiario' => $d['beneficiario'] ?: null,
            'monto_usd'    => $d['monto_usd'],
            'monto_ves'    => $d['monto_ves']   ?: null,
            'tasa_cambio'  => $d['tasa_cambio'] ?: null,
            'metodo_pago'  => $d['metodo_pago'],
            'referencia'   => $d['referencia'] ?: null,
            'notas'        => $d['notas'] ?: null,
        ];
        if (!empty($d['comprobante_ruta'])) {
            $campos['comprobante_ruta'] = $d['comprobante_ruta'];
        }

        $this->actualizar($id, $campos);

        $prestamos = new PrestamoModel();
        foreach (array_unique(array_filter([$antes['prestamo_id'], $prestamo_id])) as $pid) {
            $prestamos->actualizarEstatus((int) $pid);
        }
        return true;
    }

    /**
     * Listado con filtros opcionales
     */
    public function listar(array $filtros = [], int $limite = 200): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['categoria'])) {
            $where[] = 'g.categoria = :cat';
            $params[':cat'] = $filtros['categoria'];
        }
        if (!empty($filtros['fondo_id'])) {
            $where[] = 'g.fondo_id = :fondo';
            $params[':fondo'] = (int) $filtros['fondo_id'];
        }
        if (!empty($filtros['desde'])) {
            $where[] = 'g.fecha_gasto >= :desde';
            $params[':desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $where[] = 'g.fecha_gasto <= :hasta';
            $params[':hasta'] = $filtros['hasta'];
        }

        $stmt = $this->db->prepare("
            SELECT g.*,
                   CONCAT(u.nombre, ' ', u.apellido) AS registrado_por_nombre,
                   f.nombre AS fondo_nombre,
                   c.nombre AS cuenta_nombre,
                   p.prestamista
            FROM gastos g
            LEFT JOIN usuarios  u ON u.id = g.registrado_por
            LEFT JOIN fondos    f ON f.id = g.fondo_id
            LEFT JOIN cuentas   c ON c.id = g.cuenta_id
            LEFT JOIN prestamos p ON p.id = g.prestamo_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY g.fecha_gasto DESC, g.id DESC
            LIMIT :lim
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Gastos recientes
     */
    public function recientes(int $limite = 15): array {
        return $this->listar([], $limite);
    }

    /**
     * Total gastado
     */
    public function total(): float {
        return (float) $this->db->query(
            "SELECT COALESCE(SUM(monto_usd), 0) FROM gastos"
        )->fetchColumn();
    }

    /**
     * Gasto acumulado por categoría, de mayor a menor
     */
    public function totalesPorCategoria(): array {
        return $this->db->query("
            SELECT categoria, COALESCE(SUM(monto_usd), 0) AS total, COUNT(*) AS cantidad
            FROM gastos
            GROUP BY categoria
            ORDER BY total DESC
        ")->fetchAll();
    }

    /**
     * Todos los gastos para exportar CSV
     */
    public function todosParaExportar(): array {
        return $this->db->query("
            SELECT g.fecha_gasto, g.concepto, g.categoria,
                   f.nombre AS fondo, c.nombre AS cuenta,
                   g.beneficiario, g.monto_usd, g.monto_ves, g.tasa_cambio,
                   g.metodo_pago, g.referencia,
                   CONCAT(u.nombre, ' ', u.apellido) AS registrado_por,
                   g.notas
            FROM gastos g
            LEFT JOIN usuarios u ON u.id = g.registrado_por
            LEFT JOIN fondos   f ON f.id = g.fondo_id
            LEFT JOIN cuentas  c ON c.id = g.cuenta_id
            ORDER BY g.fecha_gasto ASC, g.id ASC
        ")->fetchAll();
    }
}
