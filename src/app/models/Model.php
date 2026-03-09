<?php
/**
 * Modelo Base
 * Todos los modelos extienden esta clase
 */

class Model {

    protected PDO $db;
    protected string $tabla = '';

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtener todos los registros
     */
    public function todos(string $orden = 'id DESC'): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tabla} ORDER BY {$orden}"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener un registro por ID
     */
    public function porId(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM {$this->tabla} WHERE id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Insertar un registro
     * @param array $datos ['campo' => 'valor']
     */
    public function insertar(array $datos): int {
        $campos    = implode(', ', array_keys($datos));
        $valores   = implode(', ', array_map(fn($k) => ':' . $k, array_keys($datos)));
        $stmt = $this->db->prepare(
            "INSERT INTO {$this->tabla} ({$campos}) VALUES ({$valores})"
        );
        $stmt->execute($datos);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualizar un registro por ID
     * @param array $datos ['campo' => 'valor']
     */
    public function actualizar(int $id, array $datos): bool {
        $set = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($datos)));
        $datos[':id'] = $id;
        $stmt = $this->db->prepare(
            "UPDATE {$this->tabla} SET {$set} WHERE id = :id"
        );
        return $stmt->execute($datos);
    }

    /**
     * Eliminar un registro por ID
     */
    public function eliminar(int $id): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM {$this->tabla} WHERE id = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Contar registros (con filtro opcional)
     */
    public function contar(string $where = '', array $params = []): int {
        $sql  = "SELECT COUNT(*) FROM {$this->tabla}";
        $sql .= $where ? " WHERE {$where}" : '';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
