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
     * Edita un préstamo y sincroniza el ingreso que generó.
     *
     * El préstamo y su ingreso son la misma plata vista de dos formas;
     * si se editan por separado la caja deja de cuadrar.
     */
    public function editar(int $id, array $d): bool {
        if (!$this->porId($id)) return false;

        $this->actualizar($id, [
            'prestamista'      => $d['prestamista'],
            'telefono'         => $d['telefono'] ?: null,
            'concepto'         => $d['concepto'],
            'fondo_id'         => $d['fondo_id'] ?: null,
            'monto_usd'        => $d['monto_usd'],
            'fecha_prestamo'   => $d['fecha_prestamo'],
            'fecha_compromiso' => $d['fecha_compromiso'] ?: null,
            'notas'            => $d['notas'] ?: null,
        ]);

        $this->db->prepare("
            UPDATE ingresos
            SET fecha       = :fecha,
                fondo_id    = :fondo,
                cuenta_id   = :cuenta,
                aportante   = :aportante,
                concepto    = :concepto,
                monto_usd   = :monto,
                monto_ves   = :ves,
                tasa_cambio = :tasa,
                metodo_pago = :metodo,
                referencia  = :ref
            WHERE prestamo_id = :id AND origen = 'prestamo'
        ")->execute([
            ':fecha'     => $d['fecha_prestamo'],
            ':fondo'     => $d['fondo_id']  ?: null,
            ':cuenta'    => $d['cuenta_id'] ?: null,
            ':aportante' => $d['prestamista'],
            ':concepto'  => 'Préstamo: ' . $d['concepto'],
            ':monto'     => $d['monto_usd'],
            ':ves'       => $d['monto_ves']   ?: null,
            ':tasa'      => $d['tasa_cambio'] ?: null,
            ':metodo'    => $d['metodo_pago'] ?? 'efectivo',
            ':ref'       => $d['referencia'] ?: null,
            ':id'        => $id,
        ]);

        $this->actualizarEstatus($id);
        return true;
    }

    /**
     * Anula un préstamo y el ingreso que generó.
     *
     * Si ya tiene devoluciones registradas no se puede anular: habría
     * que decidir qué pasa con esos pagos. Se anulan primero ellas.
     */
    public function anular(int $id, int $admin_id, string $motivo): array {
        $prestamo = $this->porId($id);
        if (!$prestamo) {
            return ['ok' => false, 'msg' => 'No se encontró el préstamo.'];
        }
        if ($prestamo['estatus'] === 'anulado') {
            return ['ok' => false, 'msg' => 'Ese préstamo ya está anulado.'];
        }

        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM gastos
            WHERE prestamo_id = :id AND estatus = 'activo'
        ");
        $stmt->execute([':id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return [
                'ok'  => false,
                'msg' => 'Este préstamo tiene devoluciones registradas. Anula primero esas '
                       . 'devoluciones en Ingresos y gastos, y después el préstamo.',
            ];
        }

        $this->db->prepare("
            UPDATE prestamos
            SET estatus = 'anulado', anulado_en = NOW(),
                anulado_por = :admin, motivo_anulacion = :motivo
            WHERE id = :id
        ")->execute([':admin' => $admin_id, ':motivo' => $motivo, ':id' => $id]);

        // El ingreso espejo se va con él, o la caja quedaría inflada
        $this->db->prepare("
            UPDATE ingresos
            SET estatus = 'anulado', anulado_en = NOW(),
                anulado_por = :admin, motivo_anulacion = :motivo
            WHERE prestamo_id = :id AND origen = 'prestamo'
        ")->execute([':admin' => $admin_id, ':motivo' => $motivo, ':id' => $id]);

        return ['ok' => true, 'msg' => 'Préstamo anulado. El ingreso en caja se anuló con él.'];
    }

    /**
     * Devuelve un préstamo anulado a circulación, junto con su ingreso
     */
    public function reactivar(int $id): bool {
        $prestamo = $this->porId($id);
        if (!$prestamo || $prestamo['estatus'] !== 'anulado') return false;

        $this->db->prepare("
            UPDATE prestamos
            SET estatus = 'activo', anulado_en = NULL,
                anulado_por = NULL, motivo_anulacion = NULL
            WHERE id = :id
        ")->execute([':id' => $id]);

        $this->db->prepare("
            UPDATE ingresos
            SET estatus = 'confirmado', anulado_en = NULL,
                anulado_por = NULL, motivo_anulacion = NULL
            WHERE prestamo_id = :id AND origen = 'prestamo'
        ")->execute([':id' => $id]);

        $this->actualizarEstatus($id);
        return true;
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
                       WHERE g.prestamo_id = p.id AND g.estatus = 'activo'
                   ), 0) AS devuelto_usd
            FROM prestamos p
            LEFT JOIN fondos f ON f.id = p.fondo_id
            ORDER BY p.estatus = 'anulado' ASC, p.estatus = 'activo' DESC, p.fecha_prestamo DESC
        ")->fetchAll();
    }

    /**
     * Total pendiente de devolver
     */
    public function saldoTotal(): float {
        return (float) $this->db->query("
            SELECT COALESCE(SUM(
                p.monto_usd - COALESCE((
                    SELECT SUM(g.monto_usd) FROM gastos g
                    WHERE g.prestamo_id = p.id AND g.estatus = 'activo'
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
                   COALESCE((SELECT SUM(g.monto_usd) FROM gastos g
                             WHERE g.prestamo_id = p.id AND g.estatus = 'activo'), 0) AS devuelto
            FROM prestamos p WHERE p.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $p = $stmt->fetch();
        if (!$p) return;

        $estatus = (float) $p['devuelto'] >= (float) $p['monto_usd'] - 0.005 ? 'pagado' : 'activo';
        $this->db->prepare("
            UPDATE prestamos SET estatus = :e
            WHERE id = :id AND estatus NOT IN ('condonado', 'anulado')
        ")->execute([':e' => $estatus, ':id' => $id]);
    }

    /**
     * Préstamos activos, para el selector de devoluciones
     */
    public function activos(): array {
        return $this->db->query("
            SELECT p.id, p.prestamista, p.concepto, p.monto_usd,
                   COALESCE((SELECT SUM(g.monto_usd) FROM gastos g
                             WHERE g.prestamo_id = p.id AND g.estatus = 'activo'), 0) AS devuelto_usd
            FROM prestamos p
            WHERE p.estatus = 'activo'
            ORDER BY p.fecha_prestamo ASC
        ")->fetchAll();
    }
}
