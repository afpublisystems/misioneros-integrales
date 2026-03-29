<?php
/**
 * PagoModel
 * Gestión de cuotas y abonos del programa de formación
 */

require_once APP_PATH . '/models/Model.php';

class PagoModel extends Model {

    protected string $tabla = 'cuotas_estudiantes';

    // ─────────────────────────────────────────────────────────
    // CUOTAS
    // ─────────────────────────────────────────────────────────

    /**
     * Genera 7 cuotas al aprobar un estudiante.
     * Las fechas de vencimiento inician en julio 2026 (+1 mes por cuota).
     */
    public function generarCuotas(int $aspirante_id, int $total = 7, float $monto = 107.14): void {
        // Verificar que no existan ya
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM cuotas_estudiantes WHERE aspirante_id = :id"
        );
        $stmt->execute([':id' => $aspirante_id]);
        if ((int) $stmt->fetchColumn() > 0) return;

        $inicio = new DateTime('2026-07-01');
        for ($i = 1; $i <= $total; $i++) {
            $vencimiento = clone $inicio;
            $vencimiento->modify('+' . ($i - 1) . ' month');
            $this->db->prepare("
                INSERT INTO cuotas_estudiantes
                    (aspirante_id, cuota_numero, monto_esperado_usd, fecha_vencimiento)
                VALUES (:asp, :num, :monto, :fecha)
            ")->execute([
                ':asp'   => $aspirante_id,
                ':num'   => $i,
                ':monto' => $monto,
                ':fecha' => $vencimiento->format('Y-m-d'),
            ]);
        }
    }

    /**
     * Cuotas de un aspirante con su último abono pendiente
     */
    public function cuotasPorAspirante(int $aspirante_id): array {
        $stmt = $this->db->prepare("
            SELECT c.*,
                   (SELECT a.id FROM abonos a
                    WHERE a.cuota_id = c.id AND a.estatus = 'pendiente'
                    ORDER BY a.created_at DESC LIMIT 1) AS abono_pendiente_id,
                   (SELECT a.monto_declarado_usd FROM abonos a
                    WHERE a.cuota_id = c.id AND a.estatus = 'rechazado'
                    ORDER BY a.created_at DESC LIMIT 1) AS ultimo_monto_rechazado,
                   (SELECT a.notas_admin FROM abonos a
                    WHERE a.cuota_id = c.id AND a.estatus = 'rechazado'
                    ORDER BY a.created_at DESC LIMIT 1) AS nota_rechazo
            FROM cuotas_estudiantes c
            WHERE c.aspirante_id = :id
            ORDER BY c.cuota_numero ASC
        ");
        $stmt->execute([':id' => $aspirante_id]);
        return $stmt->fetchAll();
    }

    /**
     * Resumen de pagos de todos los estudiantes (para admin)
     */
    public function resumenPorEstudiante(): array {
        $stmt = $this->db->query("
            SELECT a.id AS aspirante_id,
                   CONCAT(a.nombres, ' ', a.apellidos) AS nombre,
                   a.cedula,
                   COUNT(c.id)                                         AS total_cuotas,
                   SUM(c.estatus = 'completada')                       AS cuotas_completadas,
                   SUM(c.monto_acumulado_usd)                          AS total_pagado_usd,
                   SUM(c.monto_esperado_usd) - SUM(c.monto_acumulado_usd) AS saldo_pendiente_usd
            FROM aspirantes a
            JOIN cuotas_estudiantes c ON c.aspirante_id = a.id
            GROUP BY a.id
            ORDER BY a.apellidos ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * KPIs financieros globales
     */
    public function kpis(): array {
        $row = $this->db->query("
            SELECT
                SUM(monto_esperado_usd)  AS total_esperado,
                SUM(monto_acumulado_usd) AS total_cobrado
            FROM cuotas_estudiantes
        ")->fetch();

        $gastos = $this->db->query("
            SELECT COALESCE(SUM(monto_usd), 0) AS total_gastos FROM gastos
        ")->fetchColumn();

        return [
            'total_esperado' => (float)($row['total_esperado'] ?? 0),
            'total_cobrado'  => (float)($row['total_cobrado']  ?? 0),
            'total_gastos'   => (float)$gastos,
            'saldo_neto'     => (float)($row['total_cobrado'] ?? 0) - (float)$gastos,
        ];
    }

    // ─────────────────────────────────────────────────────────
    // ABONOS
    // ─────────────────────────────────────────────────────────

    /**
     * Registrar un abono enviado por el estudiante
     */
    public function registrarAbono(array $datos): int {
        $stmt = $this->db->prepare("
            INSERT INTO abonos
                (cuota_id, aspirante_id, monto_declarado_usd, monto_declarado_ves,
                 tasa_cambio, metodo_pago, banco_origen, referencia,
                 comprobante_ruta, fecha_pago_declarado)
            VALUES
                (:cuota_id, :aspirante_id, :monto_usd, :monto_ves,
                 :tasa, :metodo, :banco, :ref,
                 :ruta, :fecha)
        ");
        $stmt->execute([
            ':cuota_id'    => $datos['cuota_id'],
            ':aspirante_id'=> $datos['aspirante_id'],
            ':monto_usd'   => $datos['monto_declarado_usd'],
            ':monto_ves'   => $datos['monto_declarado_ves'] ?? null,
            ':tasa'        => $datos['tasa_cambio'] ?? null,
            ':metodo'      => $datos['metodo_pago'],
            ':banco'       => $datos['banco_origen'] ?? null,
            ':ref'         => $datos['referencia'] ?? null,
            ':ruta'        => $datos['comprobante_ruta'] ?? null,
            ':fecha'       => $datos['fecha_pago_declarado'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Abonos pendientes de confirmación (para admin)
     */
    public function abonosPendientes(): array {
        $stmt = $this->db->query("
            SELECT ab.*,
                   CONCAT(a.nombres, ' ', a.apellidos) AS nombre_estudiante,
                   a.cedula,
                   c.cuota_numero,
                   c.monto_esperado_usd,
                   c.monto_acumulado_usd
            FROM abonos ab
            JOIN cuotas_estudiantes c ON c.id = ab.cuota_id
            JOIN aspirantes a         ON a.id = ab.aspirante_id
            WHERE ab.estatus = 'pendiente'
            ORDER BY ab.created_at ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Obtener abono por ID con datos de cuota y aspirante
     */
    public function abonoCompleto(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT ab.*, c.cuota_numero, c.monto_esperado_usd, c.monto_acumulado_usd,
                   c.aspirante_id AS asp_id
            FROM abonos ab
            JOIN cuotas_estudiantes c ON c.id = ab.cuota_id
            WHERE ab.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Confirmar un abono y actualizar acumulado de la cuota.
     * Si hay excedente lo aplica a la cuota siguiente.
     */
    public function confirmarAbono(int $abono_id, int $admin_id, ?string $notas = null): bool {
        $abono = $this->abonoCompleto($abono_id);
        if (!$abono || $abono['estatus'] !== 'pendiente') return false;

        // Marcar abono como confirmado
        $this->db->prepare("
            UPDATE abonos
            SET estatus = 'confirmado',
                fecha_confirmacion = NOW(),
                confirmado_por = :admin,
                notas_admin = :notas
            WHERE id = :id
        ")->execute([':admin' => $admin_id, ':notas' => $notas, ':id' => $abono_id]);

        // Calcular nuevo acumulado
        $cuota_id      = $abono['cuota_id'];
        $aspirante_id  = $abono['asp_id'];
        $monto         = (float)$abono['monto_declarado_usd'];
        $acumulado     = (float)$abono['monto_acumulado_usd'] + $monto;
        $esperado      = (float)$abono['monto_esperado_usd'];
        $num_cuota     = (int)$abono['cuota_numero'];

        if ($acumulado >= $esperado) {
            // Cuota completada
            $this->db->prepare("
                UPDATE cuotas_estudiantes
                SET monto_acumulado_usd = :acum, estatus = 'completada'
                WHERE id = :id
            ")->execute([':acum' => $acumulado, ':id' => $cuota_id]);

            // Aplicar excedente a la siguiente cuota
            $excedente = round($acumulado - $esperado, 2);
            if ($excedente > 0) {
                $this->db->prepare("
                    UPDATE cuotas_estudiantes
                    SET monto_acumulado_usd = monto_acumulado_usd + :exc,
                        estatus = CASE
                            WHEN monto_acumulado_usd + :exc2 >= monto_esperado_usd THEN 'completada'
                            ELSE 'parcial'
                        END
                    WHERE aspirante_id = :asp AND cuota_numero = :num
                ")->execute([
                    ':exc'  => $excedente,
                    ':exc2' => $excedente,
                    ':asp'  => $aspirante_id,
                    ':num'  => $num_cuota + 1,
                ]);
            }
        } else {
            // Pago parcial
            $this->db->prepare("
                UPDATE cuotas_estudiantes
                SET monto_acumulado_usd = :acum, estatus = 'parcial'
                WHERE id = :id
            ")->execute([':acum' => $acumulado, ':id' => $cuota_id]);
        }

        return true;
    }

    /**
     * Rechazar un abono (no modifica el acumulado)
     */
    public function rechazarAbono(int $abono_id, int $admin_id, string $notas): bool {
        return (bool) $this->db->prepare("
            UPDATE abonos
            SET estatus = 'rechazado',
                fecha_confirmacion = NOW(),
                confirmado_por = :admin,
                notas_admin = :notas
            WHERE id = :id AND estatus = 'pendiente'
        ")->execute([':admin' => $admin_id, ':notas' => $notas, ':id' => $abono_id]);
    }

    /**
     * Todos los abonos de un aspirante (para historial candidato)
     */
    public function abonosPorAspirante(int $aspirante_id): array {
        $stmt = $this->db->prepare("
            SELECT ab.*, c.cuota_numero
            FROM abonos ab
            JOIN cuotas_estudiantes c ON c.id = ab.cuota_id
            WHERE ab.aspirante_id = :id
            ORDER BY c.cuota_numero ASC, ab.created_at DESC
        ");
        $stmt->execute([':id' => $aspirante_id]);
        return $stmt->fetchAll();
    }

    /**
     * Todos los abonos confirmados (para exportar CSV)
     */
    public function todosParaExportar(): array {
        $stmt = $this->db->query("
            SELECT CONCAT(a.nombres, ' ', a.apellidos) AS estudiante,
                   a.cedula,
                   c.cuota_numero,
                   ab.monto_declarado_usd,
                   ab.monto_declarado_ves,
                   ab.tasa_cambio,
                   ab.metodo_pago,
                   ab.referencia,
                   ab.estatus,
                   ab.fecha_pago_declarado,
                   ab.fecha_confirmacion
            FROM abonos ab
            JOIN cuotas_estudiantes c ON c.id = ab.cuota_id
            JOIN aspirantes a         ON a.id = ab.aspirante_id
            ORDER BY ab.fecha_pago_declarado ASC
        ");
        return $stmt->fetchAll();
    }
}
