<?php
/**
 * AspiranteModel
 * Gestión del perfil del candidato al programa
 */

require_once APP_PATH . '/models/Model.php';

class AspiranteModel extends Model {

    protected string $tabla = 'aspirantes';

    /**
     * Obtener aspirante por usuario_id
     */
    public function porUsuario(int $usuario_id): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM aspirantes WHERE usuario_id = :uid LIMIT 1"
        );
        $stmt->execute([':uid' => $usuario_id]);
        return $stmt->fetch();
    }

    /**
     * Obtener aspirante con datos completos (JOIN flujo_proceso)
     */
    public function perfilCompleto(int $usuario_id): array|false {
        $stmt = $this->db->prepare("
            SELECT a.*,
                   u.email,
                   (SELECT COUNT(*) FROM flujo_proceso fp
                    WHERE fp.aspirante_id = a.id AND fp.estatus = 'aprobado') AS etapas_aprobadas
            FROM aspirantes a
            JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.usuario_id = :uid
            LIMIT 1
        ");
        $stmt->execute([':uid' => $usuario_id]);
        return $stmt->fetch();
    }

    /**
     * Guardar o actualizar perfil del aspirante
     */
    public function guardarPerfil(int $usuario_id, array $datos) {
        $existente = $this->porUsuario($usuario_id);

        $campos = [
            'nombres'              => trim($datos['nombres'] ?? ''),
            'apellidos'            => trim($datos['apellidos'] ?? ''),
            'cedula'               => trim($datos['cedula'] ?? ''),
            'fecha_nacimiento'     => $datos['fecha_nacimiento'] ?? null,
            'edad'                 => (int)($datos['edad'] ?? 0),
            'genero'               => $datos['genero'] ?? 'masculino',
            'estado_civil'         => $datos['estado_civil'] ?? 'soltero',
            'hijos'                => (int)($datos['hijos'] ?? 0),
            'telefono'             => trim($datos['telefono'] ?? ''),
            'email'                => strtolower(trim($datos['email'] ?? '')),
            'ciudad_origen'        => trim($datos['ciudad_origen'] ?? ''),
            'estado_origen'        => trim($datos['estado_origen'] ?? ''),
            'direccion'            => trim($datos['direccion'] ?? ''),
            'iglesia'              => trim($datos['iglesia'] ?? ''),
            'pastor'               => trim($datos['pastor'] ?? ''),
            'telefono_pastor'      => trim($datos['telefono_pastor'] ?? ''),
            'anos_bautizado'       => (int)($datos['anos_bautizado'] ?? 0),
            'nivel_academico'      => $datos['nivel_academico'] ?? 'bachiller',
            'titulo_bachiller'     => isset($datos['titulo_bachiller']) ? 1 : 0,
            'compromiso_movilidad' => isset($datos['compromiso_movilidad']) ? 1 : 0,
            'detalle_impedimento'  => trim($datos['detalle_impedimento'] ?? ''),
        ];

        if ($existente) {
            return $this->actualizar($existente['id'], $campos);
        } else {
            $campos['usuario_id'] = $usuario_id;
            $campos['estatus']    = 'borrador';
            return $this->insertar($campos);
        }
    }

    /**
     * Enviar postulación — cambia estatus a 'enviada'
     * Solo si tiene todos los campos obligatorios
     */
    public function enviarPostulacion(int $aspirante_id): bool {
        return $this->actualizar($aspirante_id, ['estatus' => 'enviada']);
    }

    /**
     * Obtener todos — para el panel admin
     */
    public function todosConEstatus(string $estatus = ''): array {
        $sql = "SELECT a.*, u.email FROM aspirantes a JOIN usuarios u ON u.id = a.usuario_id";
        $params = [];
        if ($estatus) {
            $sql .= " WHERE a.estatus = :estatus";
            $params[':estatus'] = $estatus;
        }
        $sql .= " ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
