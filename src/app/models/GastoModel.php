<?php
/**
 * GastoModel
 * Gastos operativos del programa de formación
 */

require_once APP_PATH . '/models/Model.php';

class GastoModel extends Model {

    protected string $tabla = 'gastos';

    /**
     * Registrar un gasto
     */
    public function registrar(array $datos, int $usuario_id): int {
        return $this->insertar([
            'concepto'         => trim($datos['concepto']),
            'categoria'        => $datos['categoria'],
            'monto_usd'        => !empty($datos['monto_usd'])  ? (float)$datos['monto_usd']  : null,
            'monto_ves'        => !empty($datos['monto_ves'])  ? (float)$datos['monto_ves']  : null,
            'metodo_pago'      => $datos['metodo_pago'],
            'referencia'       => trim($datos['referencia'] ?? ''),
            'comprobante_ruta' => $datos['comprobante_ruta'] ?? null,
            'fecha_gasto'      => $datos['fecha_gasto'],
            'registrado_por'   => $usuario_id,
            'notas'            => trim($datos['notas'] ?? ''),
        ]);
    }

    /**
     * Gastos recientes con nombre de quien registró
     */
    public function recientes(int $limite = 20): array {
        $stmt = $this->db->prepare("
            SELECT g.*, CONCAT(u.nombre, ' ', u.apellido) AS registrado_por_nombre
            FROM gastos g
            JOIN usuarios u ON u.id = g.registrado_por
            ORDER BY g.fecha_gasto DESC, g.id DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Todos los gastos para exportar CSV
     */
    public function todosParaExportar(): array {
        $stmt = $this->db->query("
            SELECT g.concepto, g.categoria, g.monto_usd, g.monto_ves,
                   g.metodo_pago, g.referencia, g.fecha_gasto,
                   CONCAT(u.nombre, ' ', u.apellido) AS registrado_por,
                   g.notas
            FROM gastos g
            JOIN usuarios u ON u.id = g.registrado_por
            ORDER BY g.fecha_gasto ASC
        ");
        return $stmt->fetchAll();
    }
}
