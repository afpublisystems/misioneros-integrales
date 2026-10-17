<?php
/**
 * TrasladoModel
 * Solicitudes de traslado de fondos a la tesorería de la Convención.
 *
 * Las matrículas en bolívares caen en la cuenta de la CNBV. Para
 * disponer de ese dinero hay que mandarle a tesorería el listado de
 * pagos con fecha, monto en Bs, banco y referencia; ellos verifican
 * y luego transfieren.
 *
 * Cada envío es una solicitud y cada ingreso guarda en cuál se fue,
 * así no se pide dos veces lo mismo. Anular una solicitud libera sus
 * ingresos para que entren en la siguiente.
 */

require_once APP_PATH . '/models/Model.php';

class TrasladoModel extends Model {

    protected string $tabla = 'solicitudes_traslado';

    public const ESTATUS = [
        'enviada'     => 'Enviada a tesorería',
        'verificada'  => 'Verificada',
        'trasladada'  => 'Trasladada',
        'anulada'     => 'Anulada',
    ];

    /**
     * Pagos confirmados que todavía no se han reportado.
     * Filtros opcionales: desde, hasta, cuenta_id, origen.
     */
    public function ingresosSinReportar(array $filtros = []): array {
        $where  = ["i.estatus = 'confirmado'", 'i.solicitud_id IS NULL'];
        $params = [];

        if (!empty($filtros['desde'])) {
            $where[] = 'i.fecha >= :desde';
            $params[':desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $where[] = 'i.fecha <= :hasta';
            $params[':hasta'] = $filtros['hasta'];
        }
        if (!empty($filtros['cuenta_id'])) {
            $where[] = 'i.cuenta_id = :cuenta';
            $params[':cuenta'] = (int) $filtros['cuenta_id'];
        }
        if (!empty($filtros['origen'])) {
            $where[] = 'i.origen = :origen';
            $params[':origen'] = $filtros['origen'];
        }
        if (!empty($filtros['solo_bolivares'])) {
            $where[] = 'i.monto_ves > 0';
        }

        $stmt = $this->db->prepare("
            SELECT i.*,
                   COALESCE(CONCAT(a.nombres, ' ', a.apellidos), i.aportante) AS de_quien,
                   a.cedula,
                   c.nombre AS cuenta_nombre
            FROM ingresos i
            LEFT JOIN aspirantes a ON a.id = i.aspirante_id
            LEFT JOIN cuentas    c ON c.id = i.cuenta_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY i.fecha ASC, i.id ASC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Crea la solicitud y le amarra los pagos seleccionados.
     *
     * El UPDATE vuelve a exigir que el ingreso esté libre y confirmado:
     * si alguien lo reportó en otra solicitud mientras se llenaba el
     * formulario, ese pago simplemente no entra.
     */
    public function crear(array $d, array $ingreso_ids, int $usuario_id): int {
        $ids = array_values(array_filter(array_map('intval', $ingreso_ids)));
        if (empty($ids)) return 0;

        $this->db->beginTransaction();
        try {
            $id = $this->insertar([
                'codigo'     => $this->siguienteCodigo(),
                'fecha'      => $d['fecha'],
                'concepto'   => $d['concepto'],
                'destino'    => $d['destino'] ?: null,
                'creada_por' => $usuario_id,
                'notas'      => $d['notas'] ?: null,
            ]);

            $marcadores = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $this->db->prepare("
                UPDATE ingresos SET solicitud_id = ?
                WHERE id IN ($marcadores)
                  AND solicitud_id IS NULL
                  AND estatus = 'confirmado'
            ");
            $stmt->execute(array_merge([$id], $ids));

            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Listado con el total de cada solicitud.
     * Los montos salen de los ingresos, no de una copia guardada:
     * si un pago se anula después, el total deja de contarlo.
     */
    public function listar(): array {
        return $this->db->query("
            SELECT s.*,
                   u.nombre AS creada_por_nombre,
                   COUNT(i.id)                        AS pagos,
                   COALESCE(SUM(i.monto_usd), 0)      AS total_usd,
                   COALESCE(SUM(i.monto_ves), 0)      AS total_ves
            FROM solicitudes_traslado s
            LEFT JOIN ingresos i ON i.solicitud_id = s.id AND i.estatus = 'confirmado'
            LEFT JOIN usuarios u ON u.id = s.creada_por
            GROUP BY s.id
            ORDER BY s.fecha DESC, s.id DESC
        ")->fetchAll();
    }

    /**
     * Una solicitud con sus totales
     */
    public function conTotales(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT s.*,
                   u.nombre AS creada_por_nombre,
                   COUNT(i.id)                   AS pagos,
                   COALESCE(SUM(i.monto_usd), 0) AS total_usd,
                   COALESCE(SUM(i.monto_ves), 0) AS total_ves
            FROM solicitudes_traslado s
            LEFT JOIN ingresos i ON i.solicitud_id = s.id AND i.estatus = 'confirmado'
            LEFT JOIN usuarios u ON u.id = s.creada_por
            WHERE s.id = :id
            GROUP BY s.id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Pagos incluidos en una solicitud
     */
    public function pagosDe(int $id): array {
        $stmt = $this->db->prepare("
            SELECT i.*,
                   COALESCE(CONCAT(a.nombres, ' ', a.apellidos), i.aportante) AS de_quien,
                   a.cedula,
                   c.nombre AS cuenta_nombre
            FROM ingresos i
            LEFT JOIN aspirantes a ON a.id = i.aspirante_id
            LEFT JOIN cuentas    c ON c.id = i.cuenta_id
            WHERE i.solicitud_id = :id
            ORDER BY i.fecha ASC, i.id ASC
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchAll();
    }

    /**
     * Mueve la solicitud de estado. Anularla devuelve sus pagos a la
     * bolsa de lo no reportado.
     */
    public function cambiarEstatus(int $id, string $estatus): bool {
        if (!array_key_exists($estatus, self::ESTATUS)) return false;

        $campos = ['estatus' => $estatus];
        if ($estatus === 'verificada') $campos['fecha_verificacion'] = date('Y-m-d H:i:s');
        if ($estatus === 'trasladada') $campos['fecha_traslado']     = date('Y-m-d H:i:s');

        $this->actualizar($id, $campos);

        if ($estatus === 'anulada') {
            $this->db->prepare("UPDATE ingresos SET solicitud_id = NULL WHERE solicitud_id = :id")
                     ->execute([':id' => $id]);
        }
        return true;
    }

    /**
     * Totales para los indicadores de la pantalla
     */
    public function totales(): array {
        $row = $this->db->query("
            SELECT
                COALESCE(SUM(CASE WHEN s.estatus IN ('enviada','verificada') THEN i.monto_usd END), 0) AS en_proceso_usd,
                COALESCE(SUM(CASE WHEN s.estatus IN ('enviada','verificada') THEN i.monto_ves END), 0) AS en_proceso_ves,
                COALESCE(SUM(CASE WHEN s.estatus = 'trasladada' THEN i.monto_usd END), 0)              AS trasladado_usd
            FROM solicitudes_traslado s
            JOIN ingresos i ON i.solicitud_id = s.id AND i.estatus = 'confirmado'
        ")->fetch();

        $sin = $this->db->query("
            SELECT COUNT(*) AS pagos,
                   COALESCE(SUM(monto_usd), 0) AS usd,
                   COALESCE(SUM(monto_ves), 0) AS ves
            FROM ingresos
            WHERE estatus = 'confirmado' AND solicitud_id IS NULL
        ")->fetch();

        return [
            'en_proceso_usd'  => (float) $row['en_proceso_usd'],
            'en_proceso_ves'  => (float) $row['en_proceso_ves'],
            'trasladado_usd'  => (float) $row['trasladado_usd'],
            'sin_reportar'    => (int)   $sin['pagos'],
            'sin_reportar_usd'=> (float) $sin['usd'],
            'sin_reportar_ves'=> (float) $sin['ves'],
        ];
    }

    /**
     * Correlativo por año: ST-2026-001
     */
    private function siguienteCodigo(): string {
        $anio = date('Y');
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM solicitudes_traslado WHERE codigo LIKE :patron"
        );
        $stmt->execute([':patron' => "ST-$anio-%"]);
        return sprintf('ST-%s-%03d', $anio, (int) $stmt->fetchColumn() + 1);
    }
}
