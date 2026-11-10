<?php
/**
 * ReporteModel
 * Consultas de los informes de rendición de cuentas.
 *
 * Solo trae lo que los demás modelos no resuelven ya: el detalle que
 * acompaña a cada cifra (quién pagó qué y cuándo). Los totales de
 * matrícula y seminario salen de PagoModel y SeminarioModel, para que
 * el informe y el panel nunca digan números distintos.
 *
 * Las reglas de plata del proyecto valen aquí igual:
 *   ingresos → estatus = 'confirmado' (ya deja fuera lo anulado)
 *   gastos   → estatus = 'activo', explícito
 */

require_once APP_PATH . '/models/Model.php';

class ReporteModel extends Model {

    /** Rango de fechas de los filtros, ya saneado */
    public static function rango(array $filtros): array {
        $valida = function (?string $f): ?string {
            $d = $f ? DateTime::createFromFormat('Y-m-d', $f) : null;
            return $d && $d->format('Y-m-d') === $f ? $f : null;
        };
        return [$valida($filtros['desde'] ?? null), $valida($filtros['hasta'] ?? null)];
    }

    /** prepare + execute + fetchAll, que es lo único que hace falta acá */
    private function consulta(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Préstamos ─────────────────────────────────────────────

    /**
     * Devoluciones de cada préstamo: los gastos que lo fueron pagando.
     * Indexado por préstamo para pintarlas debajo de su fila.
     */
    public function devoluciones(): array {
        $filas = $this->db->query("
            SELECT g.prestamo_id, g.id, g.fecha_gasto, g.concepto,
                   g.monto_usd, g.metodo_pago, g.referencia
            FROM gastos g
            WHERE g.prestamo_id IS NOT NULL AND g.estatus = 'activo'
            ORDER BY g.fecha_gasto ASC, g.id ASC
        ")->fetchAll();

        $out = [];
        foreach ($filas as $f) {
            $out[(int) $f['prestamo_id']][] = $f;
        }
        return $out;
    }

    /** Lo recibido y lo devuelto en préstamos, para el resumen */
    public function totalesPrestamos(): array {
        $row = $this->db->query("
            SELECT
                COALESCE(SUM(CASE WHEN p.estatus <> 'anulado' THEN p.monto_usd END), 0) AS recibido,
                COALESCE(SUM(CASE WHEN p.estatus = 'activo'   THEN p.monto_usd END), 0) AS vigente,
                COUNT(CASE WHEN p.estatus = 'activo' THEN 1 END)                        AS activos,
                COUNT(CASE WHEN p.estatus IN ('pagado','condonado') THEN 1 END)         AS cerrados
            FROM prestamos p
        ")->fetch();

        $devuelto = (float) $this->db->query("
            SELECT COALESCE(SUM(g.monto_usd), 0)
            FROM gastos g
            JOIN prestamos p ON p.id = g.prestamo_id AND p.estatus <> 'anulado'
            WHERE g.estatus = 'activo'
        ")->fetchColumn();

        return [
            'recibido' => (float) $row['recibido'],
            'devuelto' => $devuelto,
            'saldo'    => (float) $row['recibido'] - $devuelto,
            'activos'  => (int) $row['activos'],
            'cerrados' => (int) $row['cerrados'],
        ];
    }

    // ── Matrículas ────────────────────────────────────────────

    /**
     * Abonos de matrícula, agrupados por participante.
     * Incluye los pendientes de confirmar, marcados aparte: en una
     * rendición de cuentas hay que poder decir qué está en revisión.
     */
    public function pagosMatricula(): array {
        $filas = $this->db->query("
            SELECT i.aspirante_id, i.id, i.fecha, i.monto_usd, i.monto_ves, i.tasa_cambio,
                   i.metodo_pago, i.banco_origen, i.referencia, i.estatus, i.aportante,
                   c.nombre AS cuenta
            FROM ingresos i
            LEFT JOIN cuentas c ON c.id = i.cuenta_id
            WHERE i.origen = 'matricula'
              AND i.aspirante_id IS NOT NULL
              AND i.estatus IN ('confirmado','pendiente')
            ORDER BY i.fecha ASC, i.id ASC
        ")->fetchAll();

        $out = [];
        foreach ($filas as $f) {
            $out[(int) $f['aspirante_id']][] = $f;
        }
        return $out;
    }

    // ── Seminario ─────────────────────────────────────────────

    /**
     * Cargos del STBV por participante, con el gasto que los pagó.
     * Así el informe puede mostrar con qué transferencia se cubrió
     * cada cargo y no solo que está pagado.
     */
    public function cargosSeminario(): array {
        $filas = $this->db->query("
            SELECT c.aspirante_id, c.id, c.trimestre, c.concepto, c.descripcion,
                   c.monto_usd, c.estatus, c.fecha_pago,
                   g.id AS gasto_id, g.fecha_gasto, g.referencia, g.metodo_pago
            FROM seminario_cargos c
            JOIN aspirantes a ON a.id = c.aspirante_id AND a.estatus = 'aprobada'
            LEFT JOIN gastos g ON g.id = c.gasto_id AND g.estatus = 'activo'
            ORDER BY c.aspirante_id, c.trimestre, c.orden, c.id
        ")->fetchAll();

        $out = [];
        foreach ($filas as $f) {
            $out[(int) $f['aspirante_id']][] = $f;
        }
        return $out;
    }

    /** Las transferencias hechas al STBV, una fila por pago */
    public function pagosSeminario(): array {
        return $this->db->query("
            SELECT g.id, g.fecha_gasto, g.concepto, g.monto_usd, g.metodo_pago,
                   g.referencia, g.beneficiario, g.comprobante_ruta,
                   COUNT(c.id)                       AS cargos,
                   COUNT(DISTINCT c.aspirante_id)    AS participantes
            FROM gastos g
            LEFT JOIN seminario_cargos c ON c.gasto_id = g.id
            WHERE g.estatus = 'activo'
              AND g.categoria IN ('seminario_inscripcion','seminario_mensualidad')
            GROUP BY g.id
            ORDER BY g.fecha_gasto DESC, g.id DESC
        ")->fetchAll();
    }

    // ── Gastos ────────────────────────────────────────────────

    /** Gastos del rango, agrupados por rubro */
    public function gastosPorRubro(array $filtros = []): array {
        [$where, $params] = $this->filtroGastos($filtros);

        return $this->consulta("
            SELECT g.categoria,
                   COUNT(*)                        AS cantidad,
                   COALESCE(SUM(g.monto_usd), 0)   AS total,
                   COALESCE(SUM(CASE WHEN g.cobertura = 'adicional' THEN g.monto_usd END), 0) AS adicional
            FROM gastos g
            WHERE " . implode(' AND ', $where) . "
            GROUP BY g.categoria
            ORDER BY total DESC
        ", $params);
    }

    /** El detalle de esos mismos gastos */
    public function gastosDetalle(array $filtros = []): array {
        [$where, $params] = $this->filtroGastos($filtros);

        return $this->consulta("
            SELECT g.id, g.fecha_gasto, g.concepto, g.categoria, g.cobertura, g.cobertura_motivo,
                   g.beneficiario, g.monto_usd, g.monto_ves, g.tasa_cambio,
                   g.metodo_pago, g.referencia, g.comprobante_ruta,
                   f.nombre AS fondo, cu.nombre AS cuenta,
                   COALESCE(e.nombre, u.nombre) AS cargado_por
            FROM gastos g
            LEFT JOIN fondos  f  ON f.id  = g.fondo_id
            LEFT JOIN cuentas cu ON cu.id = g.cuenta_id
            LEFT JOIN usuarios u ON u.id  = g.registrado_por
            LEFT JOIN enlaces_caja e ON e.id = g.enlace_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY g.fecha_gasto DESC, g.id DESC
        ", $params);
    }

    /**
     * Condiciones comunes de los dos de arriba, para que el detalle
     * y el agrupado nunca sumen cosas distintas.
     */
    private function filtroGastos(array $filtros): array {
        $where  = ["g.estatus = 'activo'"];
        $params = [];

        [$desde, $hasta] = self::rango($filtros);
        if ($desde) { $where[] = 'g.fecha_gasto >= :desde'; $params[':desde'] = $desde; }
        if ($hasta) { $where[] = 'g.fecha_gasto <= :hasta'; $params[':hasta'] = $hasta; }

        if (!empty($filtros['categoria']) && isset(GastoModel::CATEGORIAS[$filtros['categoria']])) {
            $where[] = 'g.categoria = :cat';
            $params[':cat'] = $filtros['categoria'];
        }
        if (($filtros['cobertura'] ?? '') === 'adicional' || ($filtros['cobertura'] ?? '') === 'presupuesto') {
            $where[] = 'g.cobertura = :cob';
            $params[':cob'] = $filtros['cobertura'];
        }
        return [$where, $params];
    }

    // ── Fondos ────────────────────────────────────────────────

    /**
     * Qué entró y qué salió de cada fondo. Es lo que responde por el
     * dinero con destino propio: lo que gira la DIME para los traslados
     * de las prácticas se carga a un fondo y aquí sale con sus gastos.
     */
    public function fondos(): array {
        return $this->db->query("
            SELECT f.id, f.nombre, f.descripcion, f.tipo, f.activo,
                   COALESCE((
                       SELECT SUM(i.monto_usd) FROM ingresos i
                       WHERE i.fondo_id = f.id AND i.estatus = 'confirmado'
                   ), 0) AS entradas,
                   COALESCE((
                       SELECT SUM(g.monto_usd) FROM gastos g
                       WHERE g.fondo_id = f.id AND g.estatus = 'activo'
                   ), 0) AS salidas
            FROM fondos f
            ORDER BY f.activo DESC, f.nombre
        ")->fetchAll();
    }

    /** Movimientos de un fondo, entradas y salidas en una sola lista */
    public function movimientosFondo(int $fondo_id): array {
        return $this->consulta("
            SELECT 'ingreso' AS tipo, i.fecha AS fecha, i.concepto, i.monto_usd,
                   i.referencia, i.metodo_pago,
                   COALESCE(i.aportante, '') AS contraparte, '' AS rubro
            FROM ingresos i
            WHERE i.fondo_id = :f1 AND i.estatus = 'confirmado'
            UNION ALL
            SELECT 'gasto', g.fecha_gasto, g.concepto, g.monto_usd,
                   g.referencia, g.metodo_pago,
                   COALESCE(g.beneficiario, ''), g.categoria
            FROM gastos g
            WHERE g.fondo_id = :f2 AND g.estatus = 'activo'
            ORDER BY fecha ASC
        ", [':f1' => $fondo_id, ':f2' => $fondo_id]);
    }

    // ── Resumen gerencial ─────────────────────────────────────

    /** Lo que entró y lo que salió en todo el ciclo */
    public function totalesCaja(): array {
        $ingresos = (float) $this->db->query(
            "SELECT COALESCE(SUM(monto_usd), 0) FROM ingresos WHERE estatus = 'confirmado'"
        )->fetchColumn();

        $gastos = (float) $this->db->query(
            "SELECT COALESCE(SUM(monto_usd), 0) FROM gastos WHERE estatus = 'activo'"
        )->fetchColumn();

        return ['ingresos' => $ingresos, 'gastos' => $gastos, 'saldo' => $ingresos - $gastos];
    }

    /** Primera y última fecha con movimiento, para encabezar el informe */
    public function periodo(): array {
        return $this->db->query("
            SELECT MIN(f) AS desde, MAX(f) AS hasta FROM (
                SELECT MIN(fecha) f FROM ingresos WHERE estatus = 'confirmado'
                UNION ALL SELECT MAX(fecha) FROM ingresos WHERE estatus = 'confirmado'
                UNION ALL SELECT MIN(fecha_gasto) FROM gastos WHERE estatus = 'activo'
                UNION ALL SELECT MAX(fecha_gasto) FROM gastos WHERE estatus = 'activo'
            ) t
        ")->fetch() ?: ['desde' => null, 'hasta' => null];
    }
}
