<?php
/**
 * EnlaceCajaModel
 * Enlaces personales para cargar gastos desde el teléfono sin entrar
 * al sistema. El token solo existe en el enlace; aquí se guarda su hash.
 */

require_once APP_PATH . '/models/Model.php';

class EnlaceCajaModel extends Model {

    protected string $tabla = 'enlaces_caja';

    /** Crea el enlace y devuelve el token, que no se vuelve a poder leer */
    public function crear(string $nombre, int $admin_id): string {
        $token = bin2hex(random_bytes(16));
        $this->insertar([
            'nombre'     => $nombre,
            'token_hash' => hash('sha256', $token),
            'creado_por' => $admin_id,
        ]);
        return $token;
    }

    /** Token nuevo para el mismo responsable; el anterior deja de servir */
    public function regenerar(int $id): string {
        $token = bin2hex(random_bytes(16));
        $this->actualizar($id, ['token_hash' => hash('sha256', $token), 'activo' => 1]);
        return $token;
    }

    public function activar(int $id, bool $activo): void {
        $this->actualizar($id, ['activo' => $activo ? 1 : 0]);
    }

    /** El enlace activo que corresponde a un token, o null */
    public function porToken(string $token): ?array {
        if (!preg_match('/^[0-9a-f]{32}$/', $token)) return null;

        $stmt = $this->db->prepare("
            SELECT * FROM enlaces_caja WHERE token_hash = :h AND activo = 1
        ");
        $stmt->execute([':h' => hash('sha256', $token)]);
        return $stmt->fetch() ?: null;
    }

    public function marcarUso(int $id): void {
        $this->db->prepare("UPDATE enlaces_caja SET ultimo_uso = NOW() WHERE id = :id")
                 ->execute([':id' => $id]);
    }

    /** Todos los enlaces, con cuánto se ha cargado por cada uno */
    public function listar(): array {
        return $this->db->query("
            SELECT e.*,
                   COUNT(g.id)                     AS gastos,
                   COALESCE(SUM(g.monto_usd), 0)   AS total_usd
            FROM enlaces_caja e
            LEFT JOIN gastos g ON g.enlace_id = e.id AND g.estatus = 'activo'
            GROUP BY e.id
            ORDER BY e.activo DESC, e.nombre
        ")->fetchAll();
    }

    /** Lo que cargó este enlace desde una fecha, para que lo confirme en el teléfono */
    public function gastosDesde(int $id, string $desde): array {
        $stmt = $this->db->prepare("
            SELECT fecha_gasto, concepto, categoria, cobertura, monto_usd, monto_ves, estatus
            FROM gastos
            WHERE enlace_id = :id AND created_at >= :desde
            ORDER BY created_at DESC
            LIMIT 30
        ");
        $stmt->execute([':id' => $id, ':desde' => $desde]);
        return $stmt->fetchAll();
    }
}
