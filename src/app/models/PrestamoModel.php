<?php
/**
 * PrestamoModel
 * Dinero que entró pero hay que devolver.
 *
 * Registrar un préstamo crea también el ingreso correspondiente,
 * porque la plata sí entró a caja. Las devoluciones son gastos
 * con categoría 'devolucion_prestamo' apuntando al préstamo, así
 * el saldo sale de una sola fuente.
 */

require_once APP_PATH . '/models/Model.php';

class PrestamoModel extends Model {

    protected string $tabla = 'prestamos';

    /**
     * Registra el préstamo y su ingreso a caja
     */
    public function registrar(array $d, int $usuario_id): int {
        $id = $this->insertar([
            'prestamista'      => $d['prestamista'],
            'telefono'         => $d['telefono'] ?: null,
            'concepto'         => $d['concepto'],
            'fondo_id'         => $d['fondo_id'] ?: null,
            'monto_usd'        => $d['monto_usd'],
            'fecha_prestamo'   => $d['fecha_prestamo'],
            'fecha_compromiso' => $d['fecha_compromiso'] ?: null,
            'notas'            => $d['notas'] ?: null,
            'registrado_por'   => $usuario_id,
        ]);

        (new IngresoModel())->registrar([
            'fecha'          => $d['fecha_prestamo'],
            'origen'         => 'prestamo',
            'prestamo_id'    => $id,
            'fondo_id'       => $d['fondo_id'] ?: null,
            'cuenta_id'      => $d['cuenta_id'] ?: null,
            'aportante'      => $d['prestamista'],
            'concepto'       => 'Préstamo: ' . $d['concepto'],
            'monto_usd'      => $d['monto_usd'],
            'monto_ves'      => $d['monto_ves']   ?? null,
            'tasa_cambio'    => $d['tasa_cambio'] ?? null,
            'metodo_pago'    => $d['metodo_pago'] ?? 'efectivo',
            'banco_origen'   => null,
            'referencia'     => $d['referencia'] ?? null,
            'estatus'        => 'confirmado',
            'registrado_via' => 'admin',
            'registrado_por' => $usuario_id,
            'notas'          => null,
        ]);

        return $id;
    }

    /**
     * Préstamos con lo devuelto y el saldo vivo
     */
    public function listar(): array {
        return $this->db->query("
            SELECT p.*,
                   f.nombre AS fondo_nombre,
                   COALESCE((
                       SELECT SUM(g.monto_usd) FROM gastos g
                       WHERE g.prestamo_id = p.id
                   ), 0) AS devuelto_usd
            FROM prestamos p
            LEFT JOIN fondos f ON f.id = p.fondo_id
            ORDER BY p.estatus = 'activo' DESC, p.fecha_prestamo DESC
        ")->fetchAll();
    }

    /**
     * Total pendiente de devolver
     */
    public function saldoTotal(): float {
        return (float) $this->db->query("
            SELECT COALESCE(SUM(
                p.monto_usd - COALESCE((
                    SELECT SUM(g.monto_usd) FROM gastos g WHERE g.prestamo_id = p.id
                ), 0)
            ), 0)
            FROM prestamos p
            WHERE p.estatus = 'activo'
        ")->fetchColumn();
    }

    /**
     * Marca como pagado el préstamo si ya se devolvió todo
     */
    public function actualizarEstatus(int $id): void {
        $stmt = $this->db->prepare("
            SELECT p.monto_usd,
                   COALESCE((SELECT SUM(g.monto_usd) FROM gastos g WHERE g.prestamo_id = p.id), 0) AS devuelto
            FROM prestamos p WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $p = $stmt->fetch();
        if (!$p) return;

        $estatus = (float) $p['devuelto'] >= (float) $p['monto_usd'] - 0.005 ? 'pagado' : 'activo';
        $this->db->prepare("UPDATE prestamos SET estatus = :e WHERE id = :id AND estatus <> 'condonado'")
                 ->execute([':e' => $estatus, ':id' => $id]);
    }

    /**
     * Préstamos activos, para el selector de devoluciones
     */
    public function activos(): array {
        return $this->db->query("
            SELECT p.id, p.prestamista, p.concepto, p.monto_usd,
                   COALESCE((SELECT SUM(g.monto_usd) FROM gastos g WHERE g.prestamo_id = p.id), 0) AS devuelto_usd
            FROM prestamos p
            WHERE p.estatus = 'activo'
            ORDER BY p.fecha_prestamo ASC
        ")->fetchAll();
    }
}
