<?php
/**
 * IngresoModel
 * Todo el dinero que entra al programa: matrículas de los
 * participantes, aportes de la CNBV, donaciones y préstamos.
 *
 * Un ingreso de matrícula cargado por el candidato entra
 * 'pendiente' y espera confirmación; uno cargado por el admin
 * entra 'confirmado' de una vez.
 */

require_once APP_PATH . '/models/Model.php';

class IngresoModel extends Model {

    protected string $tabla = 'ingresos';

    public const ORIGENES = [
        'matricula'   => 'Matrícula de participante',
        'aporte_cnbv' => 'Aporte de la CNBV',
        'donacion'    => 'Donación / ofrenda',
        'prestamo'    => 'Préstamo recibido',
        'reintegro'   => 'Reintegro / devolución a favor',
        'otro'        => 'Otro',
    ];

    /**
     * Registra un ingreso. Si es matrícula confirmada, reparte
     * el dinero sobre las cuotas del participante.
     */
    public function registrar(array $d): int {
        // Una matrícula sin fondo cae al de matrículas, para que el
        // reporte por fondo no se quede corto cuando carga el candidato
        $fondo_id = $d['fondo_id'] ?: null;
        if ($fondo_id === null && $d['origen'] === 'matricula') {
            $fondo_id = $this->fondoMatriculas();
        }

        $id = $this->insertar([
            'fecha'            => $d['fecha'],
            'origen'           => $d['origen'],
            'aspirante_id'     => $d['aspirante_id'] ?? null,
            'prestamo_id'      => $d['prestamo_id']  ?? null,
            'fondo_id'         => $fondo_id,
            'cuenta_id'        => $d['cuenta_id']    ?: null,
            'aportante'        => $d['aportante']    ?: null,
            'concepto'         => $d['concepto'],
            'monto_usd'        => $d['monto_usd'],
            'monto_ves'        => $d['monto_ves']    ?: null,
            'tasa_cambio'      => $d['tasa_cambio']  ?: null,
            'metodo_pago'      => $d['metodo_pago'],
            'banco_origen'     => $d['banco_origen'] ?: null,
            'referencia'       => $d['referencia']   ?: null,
            'comprobante_ruta' => $d['comprobante_ruta'] ?? null,
            'estatus'          => $d['estatus'],
            'registrado_via'   => $d['registrado_via'],
            'registrado_por'   => $d['registrado_por'] ?? null,
            'confirmado_por'   => $d['estatus'] === 'confirmado' ? ($d['registrado_por'] ?? null) : null,
            'fecha_confirmacion' => $d['estatus'] === 'confirmado' ? date('Y-m-d H:i:s') : null,
            'notas'            => $d['notas'] ?: null,
        ]);

        if ($d['origen'] === 'matricula' && $d['estatus'] === 'confirmado' && !empty($d['aspirante_id'])) {
            (new PagoModel())->recalcularCuotas((int) $d['aspirante_id']);
        }

        return $id;
    }

    /**
     * Edita un ingreso ya registrado.
     *
     * Si cambia el monto o el participante hay que rehacer el reparto
     * de cuotas, y de los dos: el que estaba antes y el que queda.
     */
    public function editar(int $id, array $d): bool {
        $antes = $this->porId($id);
        if (!$antes) return false;

        $aspirante_id = $d['origen'] === 'matricula' ? ($d['aspirante_id'] ?: null) : null;
        $fondo_id     = $d['fondo_id'] ?: null;
        if ($fondo_id === null && $d['origen'] === 'matricula') {
            $fondo_id = $this->fondoMatriculas();
        }

        $campos = [
            'fecha'        => $d['fecha'],
            'origen'       => $d['origen'],
            'aspirante_id' => $aspirante_id,
            'fondo_id'     => $fondo_id,
            'cuenta_id'    => $d['cuenta_id'] ?: null,
            'aportante'    => $d['aportante'] ?: null,
            'concepto'     => $d['concepto'],
            'monto_usd'    => $d['monto_usd'],
            'monto_ves'    => $d['monto_ves'] ?: null,
            'tasa_cambio'  => $d['tasa_cambio'] ?: null,
            'metodo_pago'  => $d['metodo_pago'],
            'banco_origen' => $d['banco_origen'] ?: null,
            'referencia'   => $d['referencia'] ?: null,
            'notas'        => $d['notas'] ?: null,
        ];
        // El comprobante solo se toca si subieron uno nuevo
        if (!empty($d['comprobante_ruta'])) {
            $campos['comprobante_ruta'] = $d['comprobante_ruta'];
        }

        $this->actualizar($id, $campos);

        $pagos = new PagoModel();
        foreach (array_unique(array_filter([$antes['aspirante_id'], $aspirante_id])) as $asp) {
            $pagos->recalcularCuotas((int) $asp);
        }
        return true;
    }

    private function fondoMatriculas(): ?int {
        $id = $this->db->query(
            "SELECT id FROM fondos WHERE nombre = 'Matrículas' AND activo = 1 LIMIT 1"
        )->fetchColumn();
        return $id ? (int) $id : null;
    }

    /**
     * Confirma un ingreso pendiente
     */
    public function confirmar(int $id, int $admin_id, ?string $notas = null): bool {
        $ing = $this->porId($id);
        if (!$ing || $ing['estatus'] !== 'pendiente') return false;

        $this->db->prepare("
            UPDATE ingresos
            SET estatus = 'confirmado', confirmado_por = :admin,
                fecha_confirmacion = NOW(), notas_admin = :notas
            WHERE id = :id
        ")->execute([':admin' => $admin_id, ':notas' => $notas, ':id' => $id]);

        if ($ing['origen'] === 'matricula' && !empty($ing['aspirante_id'])) {
            (new PagoModel())->recalcularCuotas((int) $ing['aspirante_id']);
        }
        return true;
    }

    /**
     * Rechaza un ingreso pendiente
     */
    public function rechazar(int $id, int $admin_id, string $notas): bool {
        $ing = $this->porId($id);
        if (!$ing || $ing['estatus'] !== 'pendiente') return false;

        $this->db->prepare("
            UPDATE ingresos
            SET estatus = 'rechazado', confirmado_por = :admin,
                fecha_confirmacion = NOW(), notas_admin = :notas
            WHERE id = :id
        ")->execute([':admin' => $admin_id, ':notas' => $notas, ':id' => $id]);

        if ($ing['origen'] === 'matricula' && !empty($ing['aspirante_id'])) {
            (new PagoModel())->recalcularCuotas((int) $ing['aspirante_id']);
        }
        return true;
    }

    /**
     * Ingresos pendientes de confirmar (comprobantes de candidatos)
     */
    public function pendientes(): array {
        return $this->db->query("
            SELECT i.*,
                   CONCAT(a.nombres, ' ', a.apellidos) AS nombre_estudiante,
                   a.cedula,
                   f.nombre AS fondo_nombre
            FROM ingresos i
            LEFT JOIN aspirantes a ON a.id = i.aspirante_id
            LEFT JOIN fondos     f ON f.id = i.fondo_id
            WHERE i.estatus = 'pendiente'
            ORDER BY i.created_at ASC
        ")->fetchAll();
    }

    /**
     * Listado con filtros opcionales de origen, fondo y rango de fechas
     */
    public function listar(array $filtros = [], int $limite = 200): array {
        $where  = ["i.estatus <> 'rechazado'"];
        $params = [];

        if (!empty($filtros['origen'])) {
            $where[] = 'i.origen = :origen';
            $params[':origen'] = $filtros['origen'];
        }
        if (!empty($filtros['fondo_id'])) {
            $where[] = 'i.fondo_id = :fondo';
            $params[':fondo'] = (int) $filtros['fondo_id'];
        }
        if (!empty($filtros['desde'])) {
            $where[] = 'i.fecha >= :desde';
            $params[':desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $where[] = 'i.fecha <= :hasta';
            $params[':hasta'] = $filtros['hasta'];
        }

        $stmt = $this->db->prepare("
            SELECT i.*,
                   CONCAT(a.nombres, ' ', a.apellidos) AS nombre_estudiante,
                   f.nombre AS fondo_nombre,
                   c.nombre AS cuenta_nombre
            FROM ingresos i
            LEFT JOIN aspirantes a ON a.id = i.aspirante_id
            LEFT JOIN fondos     f ON f.id = i.fondo_id
            LEFT JOIN cuentas    c ON c.id = i.cuenta_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY i.fecha DESC, i.id DESC
            LIMIT :lim
        ");
        foreach ($params as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ingresos de un participante (historial para su panel)
     */
    public function porAspirante(int $aspirante_id): array {
        $stmt = $this->db->prepare("
            SELECT * FROM ingresos
            WHERE aspirante_id = :id AND origen = 'matricula'
            ORDER BY fecha DESC, id DESC
        ");
        $stmt->execute([':id' => $aspirante_id]);
        return $stmt->fetchAll();
    }

    /**
     * Total confirmado por origen
     */
    public function totalesPorOrigen(): array {
        $filas = $this->db->query("
            SELECT origen, COALESCE(SUM(monto_usd), 0) AS total, COUNT(*) AS cantidad
            FROM ingresos
            WHERE estatus = 'confirmado'
            GROUP BY origen
        ")->fetchAll();

        $out = [];
        foreach ($filas as $f) {
            $out[$f['origen']] = ['total' => (float) $f['total'], 'cantidad' => (int) $f['cantidad']];
        }
        return $out;
    }

    /**
     * Todos los ingresos confirmados para exportar
     */
    public function todosParaExportar(): array {
        return $this->db->query("
            SELECT i.fecha, i.origen,
                   COALESCE(CONCAT(a.nombres, ' ', a.apellidos), i.aportante, '') AS de_quien,
                   i.concepto,
                   f.nombre AS fondo,
                   c.nombre AS cuenta,
                   i.monto_usd, i.monto_ves, i.tasa_cambio,
                   i.metodo_pago, i.referencia, i.estatus, i.notas
            FROM ingresos i
            LEFT JOIN aspirantes a ON a.id = i.aspirante_id
            LEFT JOIN fondos     f ON f.id = i.fondo_id
            LEFT JOIN cuentas    c ON c.id = i.cuenta_id
            ORDER BY i.fecha ASC, i.id ASC
        ")->fetchAll();
    }
}
