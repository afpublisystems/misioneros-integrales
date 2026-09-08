<?php
/**
 * PagoModel
 * Cuotas de matrícula del participante.
 *
 * El monto que paga cada quien sale de su beca: costo_base_usd
 * menos el porcentaje becado. La beca CNBV estándar es 50% sobre
 * USD 1.500, así que la mayoría paga 750. Una beca del 100% deja
 * las cuotas en cero y marcadas como exoneradas.
 *
 * Los abonos NO viven aquí: son filas de `ingresos` con
 * origen = 'matricula'. Este modelo solo reparte lo confirmado
 * sobre las cuotas, en orden.
 */

require_once APP_PATH . '/models/Model.php';

class PagoModel extends Model {

    protected string $tabla = 'cuotas_estudiantes';

    /** Cuotas mensuales del ciclo */
    public const TOTAL_CUOTAS = 7;

    /** Vencimiento de la primera cuota — el ciclo arranca el 15/09/2026 */
    public const PRIMER_VENCIMIENTO = '2026-09-15';

    // ─────────────────────────────────────────────────────────
    // CUOTAS
    // ─────────────────────────────────────────────────────────

    /**
     * Genera las cuotas de un participante según su beca.
     * No hace nada si ya las tiene (para eso está regenerar()).
     */
    public function generarCuotas(int $aspirante_id, int $total = self::TOTAL_CUOTAS): void {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM cuotas_estudiantes WHERE aspirante_id = :id"
        );
        $stmt->execute([':id' => $aspirante_id]);
        if ((int) $stmt->fetchColumn() > 0) return;

        $montos = $this->montosPorCuota($aspirante_id, $total);
        $inicio = new DateTime(self::PRIMER_VENCIMIENTO);

        $insert = $this->db->prepare("
            INSERT INTO cuotas_estudiantes
                (aspirante_id, cuota_numero, monto_esperado_usd, estatus, fecha_vencimiento)
            VALUES (:asp, :num, :monto, :estatus, :fecha)
        ");

        foreach ($montos as $i => $monto) {
            $vencimiento = (clone $inicio)->modify('+' . $i . ' month');
            $insert->execute([
                ':asp'     => $aspirante_id,
                ':num'     => $i + 1,
                ':monto'   => $monto,
                ':estatus' => $monto > 0 ? 'pendiente' : 'exonerada',
                ':fecha'   => $vencimiento->format('Y-m-d'),
            ]);
        }
    }

    /**
     * Recalcula los montos cuando cambia la beca del participante.
     * Conserva lo ya pagado y vuelve a repartirlo.
     */
    public function regenerarCuotas(int $aspirante_id): void {
        $cuotas = $this->cuotasPorAspirante($aspirante_id);
        if (empty($cuotas)) {
            $this->generarCuotas($aspirante_id);
            $this->recalcularCuotas($aspirante_id);
            return;
        }

        $montos = $this->montosPorCuota($aspirante_id, count($cuotas));
        $upd = $this->db->prepare("
            UPDATE cuotas_estudiantes
            SET monto_esperado_usd = :monto
            WHERE aspirante_id = :asp AND cuota_numero = :num
        ");
        foreach ($montos as $i => $monto) {
            $upd->execute([':monto' => $monto, ':asp' => $aspirante_id, ':num' => $i + 1]);
        }

        $this->recalcularCuotas($aspirante_id);
    }

    /**
     * Reparte los ingresos de matrícula confirmados sobre las cuotas,
     * en orden. Un pago grande cubre varias cuotas de una vez; un
     * rechazo posterior deshace el avance sin dejar rastros raros.
     */
    public function recalcularCuotas(int $aspirante_id): void {
        $restante = $this->totalPagado($aspirante_id);

        $upd = $this->db->prepare("
            UPDATE cuotas_estudiantes
            SET monto_acumulado_usd = :acum, estatus = :estatus
            WHERE id = :id
        ");

        foreach ($this->cuotasPorAspirante($aspirante_id) as $c) {
            $esperado = (float) $c['monto_esperado_usd'];

            if ($esperado <= 0) {
                $upd->execute([':acum' => 0, ':estatus' => 'exonerada', ':id' => $c['id']]);
                continue;
            }

            $aplica    = min($restante, $esperado);
            $restante -= $aplica;

            $estatus = match (true) {
                $aplica >= $esperado - 0.005 => 'completada',
                $aplica > 0                  => 'parcial',
                default                      => 'pendiente',
            };

            $upd->execute([
                ':acum'    => round($aplica, 2),
                ':estatus' => $estatus,
                ':id'      => $c['id'],
            ]);
        }
    }

    /**
     * Total confirmado que ha abonado un participante a su matrícula
     */
    public function totalPagado(int $aspirante_id): float {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(monto_usd), 0)
            FROM ingresos
            WHERE aspirante_id = :id AND origen = 'matricula' AND estatus = 'confirmado'
        ");
        $stmt->execute([':id' => $aspirante_id]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Cuotas de un participante
     */
    public function cuotasPorAspirante(int $aspirante_id): array {
        $stmt = $this->db->prepare("
            SELECT * FROM cuotas_estudiantes
            WHERE aspirante_id = :id
            ORDER BY cuota_numero ASC
        ");
        $stmt->execute([':id' => $aspirante_id]);
        return $stmt->fetchAll();
    }

    /**
     * Estado de cuenta de un participante: cuánto le toca pagar,
     * cuánto lleva y cuánto debe.
     */
    public function estadoCuenta(int $aspirante_id): array {
        $stmt = $this->db->prepare("
            SELECT costo_base_usd, beca_pct, beca_notas
            FROM aspirantes WHERE id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $aspirante_id]);
        $asp = $stmt->fetch();

        $costo   = (float) ($asp['costo_base_usd'] ?? 1500);
        $beca    = (float) ($asp['beca_pct'] ?? 50);
        $a_pagar = round($costo * (1 - $beca / 100), 2);
        $pagado  = $this->totalPagado($aspirante_id);

        return [
            'costo_base'   => $costo,
            'beca_pct'     => $beca,
            'beca_notas'   => $asp['beca_notas'] ?? null,
            'monto_becado' => round($costo - $a_pagar, 2),
            'total_a_pagar'=> $a_pagar,
            'total_pagado' => $pagado,
            'saldo'        => round(max($a_pagar - $pagado, 0), 2),
            'saldo_favor'  => round(max($pagado - $a_pagar, 0), 2),
            'porcentaje'   => $a_pagar > 0 ? min(round($pagado / $a_pagar * 100), 100) : 100,
        ];
    }

    /**
     * Resumen de matrículas de toda la cohorte (para admin)
     */
    public function resumenPorEstudiante(): array {
        $stmt = $this->db->query("
            SELECT a.id AS aspirante_id,
                   CONCAT(a.nombres, ' ', a.apellidos) AS nombre,
                   a.cedula,
                   a.estatus,
                   a.costo_base_usd,
                   a.beca_pct,
                   ROUND(a.costo_base_usd * (1 - a.beca_pct / 100), 2) AS total_a_pagar,
                   COALESCE((
                       SELECT SUM(i.monto_usd) FROM ingresos i
                       WHERE i.aspirante_id = a.id
                         AND i.origen  = 'matricula'
                         AND i.estatus = 'confirmado'
                   ), 0) AS total_pagado,
                   COALESCE((
                       SELECT COUNT(*) FROM ingresos i
                       WHERE i.aspirante_id = a.id
                         AND i.origen  = 'matricula'
                         AND i.estatus = 'pendiente'
                   ), 0) AS abonos_pendientes,
                   (SELECT COUNT(*) FROM cuotas_estudiantes c
                    WHERE c.aspirante_id = a.id AND c.estatus = 'completada') AS cuotas_completadas,
                   (SELECT COUNT(*) FROM cuotas_estudiantes c
                    WHERE c.aspirante_id = a.id) AS total_cuotas,
                   (SELECT MIN(c.fecha_vencimiento) FROM cuotas_estudiantes c
                    WHERE c.aspirante_id = a.id
                      AND c.estatus IN ('pendiente','parcial')) AS proximo_vencimiento
            FROM aspirantes a
            WHERE a.estatus = 'aprobada'
            ORDER BY a.apellidos ASC, a.nombres ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Totales de matrícula de toda la cohorte
     */
    public function totalesMatricula(): array {
        $row = $this->db->query("
            SELECT
                COUNT(*)                                                   AS participantes,
                COALESCE(SUM(costo_base_usd), 0)                           AS costo_total,
                COALESCE(SUM(costo_base_usd * (1 - beca_pct / 100)), 0)    AS esperado,
                COALESCE(SUM(costo_base_usd * beca_pct / 100), 0)          AS becado
            FROM aspirantes
            WHERE estatus = 'aprobada'
        ")->fetch();

        $cobrado = (float) $this->db->query("
            SELECT COALESCE(SUM(monto_usd), 0) FROM ingresos
            WHERE origen = 'matricula' AND estatus = 'confirmado'
        ")->fetchColumn();

        $esperado = (float) $row['esperado'];

        return [
            'participantes' => (int) $row['participantes'],
            'costo_total'   => (float) $row['costo_total'],
            'becado'        => (float) $row['becado'],
            'esperado'      => $esperado,
            'cobrado'       => $cobrado,
            'por_cobrar'    => round(max($esperado - $cobrado, 0), 2),
            'porcentaje'    => $esperado > 0 ? min(round($cobrado / $esperado * 100), 100) : 0,
        ];
    }

    /**
     * Cambia la beca de un participante y rehace sus cuotas
     */
    public function actualizarBeca(int $aspirante_id, float $beca_pct, ?string $notas, ?float $costo_base = null): void {
        $sql    = "UPDATE aspirantes SET beca_pct = :beca, beca_notas = :notas";
        $params = [':beca' => $beca_pct, ':notas' => $notas, ':id' => $aspirante_id];

        if ($costo_base !== null) {
            $sql .= ", costo_base_usd = :costo";
            $params[':costo'] = $costo_base;
        }

        $this->db->prepare($sql . " WHERE id = :id")->execute($params);
        $this->regenerarCuotas($aspirante_id);
    }

    // ─────────────────────────────────────────────────────────
    // Reparto interno
    // ─────────────────────────────────────────────────────────

    /**
     * Divide el monto a pagar en N cuotas. La última absorbe el
     * redondeo para que la suma dé exacta.
     */
    private function montosPorCuota(int $aspirante_id, int $total): array {
        $stmt = $this->db->prepare(
            "SELECT costo_base_usd, beca_pct FROM aspirantes WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $aspirante_id]);
        $asp = $stmt->fetch();

        $costo   = (float) ($asp['costo_base_usd'] ?? 1500);
        $beca    = (float) ($asp['beca_pct'] ?? 50);
        $a_pagar = round($costo * (1 - $beca / 100), 2);

        if ($a_pagar <= 0) return array_fill(0, $total, 0.00);

        $base    = floor($a_pagar / $total * 100) / 100;
        $montos  = array_fill(0, $total - 1, $base);
        $montos[] = round($a_pagar - $base * ($total - 1), 2);

        return $montos;
    }
}
