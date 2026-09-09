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
}
