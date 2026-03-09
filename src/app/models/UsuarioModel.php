<?php
/**
 * UsuarioModel
 * Gestión de usuarios y autenticación
 */

require_once APP_PATH . '/models/Model.php';

class UsuarioModel extends Model {

    protected string $tabla = 'usuarios';

    /**
     * Buscar usuario por email
     */
    public function porEmail(string $email): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1"
        );
        $stmt->execute([':email' => strtolower(trim($email))]);
        return $stmt->fetch();
    }

    /**
     * Verificar credenciales — retorna el usuario o false
     */
    public function autenticar(string $email, string $password): array|false {
        $usuario = $this->porEmail($email);
        if (!$usuario) return false;
        if (!password_verify($password, $usuario['password'])) return false;

        // Actualizar último acceso
        $this->db->prepare(
            "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id"
        )->execute([':id' => $usuario['id']]);

        return $usuario;
    }

    /**
     * Registrar nuevo usuario candidato
     */
    public function registrar(array $datos): int|false {
        // Verificar que el email no exista
        if ($this->porEmail($datos['email'])) return false;

        return $this->insertar([
            'nombre'   => trim($datos['nombre']),
            'apellido' => trim($datos['apellido']),
            'email'    => strtolower(trim($datos['email'])),
            'password' => password_hash($datos['password'], PASSWORD_BCRYPT),
            'rol'      => 'candidato',
            'activo'   => 1,
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword(int $id, string $nueva): bool {
        return $this->actualizar($id, [
            'password' => password_hash($nueva, PASSWORD_BCRYPT)
        ]);
    }
}
