<?php
/**
 * DocumentoModel
 * Gestión de archivos subidos por el candidato
 */

require_once APP_PATH . '/models/Model.php';

class DocumentoModel extends Model {

    protected string $tabla = 'documentos';

    /**
     * Documentos de un aspirante
     */
    public function porAspirante(int $aspirante_id): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM documentos WHERE aspirante_id = :id ORDER BY created_at DESC"
        );
        $stmt->execute([':id' => $aspirante_id]);
        return $stmt->fetchAll();
    }

    /**
     * Guardar o reemplazar documento por tipo
     */
    public function guardar(array $datos): int|bool {
        // Verificar si ya existe uno del mismo tipo para este aspirante
        $stmt = $this->db->prepare(
            "SELECT id FROM documentos WHERE aspirante_id = :aid AND tipo = :tipo LIMIT 1"
        );
        $stmt->execute([':aid' => $datos['aspirante_id'], ':tipo' => $datos['tipo']]);
        $existente = $stmt->fetch();

        if ($existente) {
            return $this->actualizar($existente['id'], $datos);
        }
        return $this->insertar($datos);
    }
}
