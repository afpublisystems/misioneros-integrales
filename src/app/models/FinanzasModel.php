<?php
/**
 * FinanzasModel
 * Catálogos (fondos y cuentas) y las cuentas consolidadas del programa.
 */

require_once APP_PATH . '/models/Model.php';

class FinanzasModel extends Model {

    protected string $tabla = 'fondos';

    // ─────────────────────────────────────────────────────────
    // Catálogos
    // ─────────────────────────────────────────────────────────

    public function fondos(bool $solo_activos = true): array {
        $sql = "SELECT * FROM fondos" . ($solo_activos ? " WHERE activo = 1" : "");
        return $this->db->query($sql . " ORDER BY tipo ASC, nombre ASC")->fetchAll();
    }

    public function cuentas(bool $solo_activas = true): array {
        $sql = "SELECT * FROM cuentas" . ($solo_activas ? " WHERE activa = 1" : "");
        return $this->db->query($sql . " ORDER BY orden ASC, nombre ASC")->fetchAll();
    }

    public function crearFondo(string $nombre, ?string $descripcion, string $tipo = 'especifico'): int {
        return $this->insertar([
            'nombre'      => $nombre,
            'descripcion' => $descripcion ?: null,
            'tipo'        => $tipo,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // Consolidado
    // ─────────────────────────────────────────────────────────

    /**
     * Los números que importan de un vistazo.
     *
     * El disponible descuenta la deuda por préstamos: tener 300
     * en caja de los cuales 180 son prestados no son 300 propios.
     */
    public function kpis(): array {
        $ingresos = (float) $this->db->query(
            "SELECT COALESCE(SUM(monto_usd), 0) FROM ingresos WHERE estatus = 'confirmado'"
        )->fetchColumn();

        $gastos = (float) $this->db->query(
            "SELECT COALESCE(SUM(monto_usd), 0) FROM gastos WHERE estatus = 'activo'"
        )->fetchColumn();

        $deuda = (new PrestamoModel())->saldoTotal();

        $pendientes = (int) $this->db->query(
            "SELECT COUNT(*) FROM ingresos WHERE estatus = 'pendiente'"
        )->fetchColumn();

        return [
            'ingresos'            => $ingresos,
            'gastos'              => $gastos,
            'en_caja'             => round($ingresos - $gastos, 2),
            'deuda_prestamos'     => $deuda,
            'disponible_real'     => round($ingresos - $gastos - $deuda, 2),
            'ingresos_pendientes' => $pendientes,
        ];
    }

    /**
     * Estado de cada fondo: qué entró, qué salió y qué queda.
     * Es lo que responde "de lo de las franelas, ¿cuánto sobra?".
     */
    public function resumenPorFondo(): array {
        return $this->db->query("
            SELECT f.id, f.nombre, f.descripcion, f.tipo,
                   COALESCE((
                       SELECT SUM(i.monto_usd) FROM ingresos i
                       WHERE i.fondo_id = f.id AND i.estatus = 'confirmado'
                   ), 0) AS ingresos,
                   COALESCE((
                       SELECT SUM(g.monto_usd) FROM gastos g
                       WHERE g.fondo_id = f.id AND g.estatus = 'activo'
                   ), 0) AS gastos
            FROM fondos f
            WHERE f.activo = 1
            ORDER BY f.tipo ASC, f.nombre ASC
        ")->fetchAll();
    }

    /**
     * Cuánto hay en cada cuenta (Zelle, pago móvil, efectivo…)
     */
    public function resumenPorCuenta(): array {
        return $this->db->query("
            SELECT c.id, c.nombre, c.tipo, c.moneda,
                   COALESCE((
                       SELECT SUM(i.monto_usd) FROM ingresos i
                       WHERE i.cuenta_id = c.id AND i.estatus = 'confirmado'
                   ), 0) AS entradas,
                   COALESCE((
                       SELECT SUM(g.monto_usd) FROM gastos g
                       WHERE g.cuenta_id = c.id AND g.estatus = 'activo'
                   ), 0) AS salidas
            FROM cuentas c
            WHERE c.activa = 1
            ORDER BY c.orden ASC
        ")->fetchAll();
    }

    /**
     * Movimiento mes a mes, para ver el flujo del ciclo
     */
    public function flujoMensual(int $meses = 12): array {
        $stmt = $this->db->prepare("
            SELECT mes,
                   COALESCE(SUM(entrada), 0) AS ingresos,
                   COALESCE(SUM(salida), 0)  AS gastos
            FROM (
                SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, monto_usd AS entrada, 0 AS salida
                FROM ingresos WHERE estatus = 'confirmado'
                UNION ALL
                SELECT DATE_FORMAT(fecha_gasto, '%Y-%m') AS mes, 0 AS entrada, monto_usd AS salida
                FROM gastos WHERE estatus = 'activo'
            ) m
            GROUP BY mes
            ORDER BY mes DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $meses, PDO::PARAM_INT);
        $stmt->execute();
        return array_reverse($stmt->fetchAll());
    }

    /**
     * Ingresos y gastos desde una fecha, mezclados y del más nuevo al más
     * viejo. Incluye anulados y pendientes: la pantalla de caja los muestra
     * marcados para que se vea qué se cargó, pero no los suma.
     */
    public function movimientosDesde(string $desde): array {
        $stmt = $this->db->prepare("
            SELECT 'ingreso' AS tipo, id, fecha, concepto, origen AS rubro,
                   monto_usd, monto_ves, estatus,
                   (estatus = 'confirmado') AS cuenta, created_at
            FROM ingresos WHERE fecha >= :d1
            UNION ALL
            SELECT 'gasto', id, fecha_gasto, concepto, categoria,
                   monto_usd, monto_ves, estatus,
                   (estatus = 'activo'), created_at
            FROM gastos WHERE fecha_gasto >= :d2
            ORDER BY fecha DESC, created_at DESC
        ");
        $stmt->execute([':d1' => $desde, ':d2' => $desde]);
        return $stmt->fetchAll();
    }

    /**
     * La última tasa con la que se cargó algo en bolívares, para no
     * tener que escribirla en cada movimiento del mismo día.
     */
    public function ultimaTasa(): ?float {
        $tasa = $this->db->query("
            SELECT tasa_cambio FROM (
                SELECT tasa_cambio, created_at FROM ingresos WHERE tasa_cambio > 0
                UNION ALL
                SELECT tasa_cambio, created_at FROM gastos WHERE tasa_cambio > 0
            ) t ORDER BY created_at DESC LIMIT 1
        ")->fetchColumn();
        return $tasa !== false ? (float) $tasa : null;
    }
}
