<?php
/**
 * AuthController
 * Login, registro de candidatos y logout
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/UsuarioModel.php';
require_once APP_PATH . '/models/AspiranteModel.php';

class AuthController extends Controller {

    private UsuarioModel $usuarios;

    public function __construct() {
        $this->usuarios = new UsuarioModel();
    }

    // ── GET /login ────────────────────────────────────────────
    public function loginForm(): void {
        // Si ya está autenticado, redirigir
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirigirSegunRol();
        }
        $this->render('auth/login', ['titulo' => 'Iniciar Sesión']);
    }

    // ── POST /login ───────────────────────────────────────────
    public function login(): void {
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $email    = $this->post('email');
        $password = $this->post('password');
        $errores  = [];

        // ── Rate limiting por IP ──────────────────────────────
        $bloqueado = $this->verificarBloqueo($ip);
        if ($bloqueado !== null) {
            $errores[] = $bloqueado;
            $this->render('auth/login', [
                'titulo'  => 'Iniciar Sesión',
                'errores' => $errores,
                'email'   => $email,
            ]);
            return;
        }

        if (!$email || !$password) {
            $errores[] = 'Por favor completa todos los campos.';
        }

        if (empty($errores)) {
            $usuario = $this->usuarios->autenticar($email, $password);

            if ($usuario) {
                $this->limpiarIntentos($ip);
                session_regenerate_id(true);

                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
                $_SESSION['usuario_email']  = $usuario['email'];
                $_SESSION['usuario_rol']    = $usuario['rol'];

                $this->redirigirSegunRol();
            } else {
                $this->registrarIntento($ip);
                $errores[] = 'Email o contraseña incorrectos. Verifica tus datos.';
            }
        }

        $this->render('auth/login', [
            'titulo'  => 'Iniciar Sesión',
            'errores' => $errores,
            'email'   => $email,
        ]);
    }

    // ── Rate limiting helpers ─────────────────────────────────

    /**
     * Devuelve null si la IP puede intentar login.
     * Devuelve string con mensaje de error si está bloqueada.
     */
    private function verificarBloqueo(string $ip): ?string {
        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT intentos, bloqueado_hasta FROM login_intentos WHERE ip = ? LIMIT 1"
            );
            $stmt->execute([$ip]);
            $row = $stmt->fetch();

            if (!$row) return null;

            if ($row['bloqueado_hasta'] && new DateTime() < new DateTime($row['bloqueado_hasta'])) {
                $resta = (new DateTime($row['bloqueado_hasta']))->diff(new DateTime());
                $mins  = max(1, $resta->i + $resta->h * 60);
                return "Demasiados intentos fallidos. Espera {$mins} minuto(s) e intenta de nuevo.";
            }
        } catch (\PDOException $e) {
            // La tabla aún no existe: omitir rate limiting sin interrumpir el login
        }
        return null;
    }

    private function registrarIntento(string $ip): void {
        try {
            $db = Database::getConnection();
            $db->prepare("
                INSERT INTO login_intentos (ip, intentos)
                VALUES (?, 1)
                ON DUPLICATE KEY UPDATE
                    intentos        = intentos + 1,
                    bloqueado_hasta = IF(intentos + 1 >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NULL)
            ")->execute([$ip]);
        } catch (\PDOException $e) {
            // Omitir si la tabla no existe aún
        }
    }

    private function limpiarIntentos(string $ip): void {
        try {
            Database::getConnection()
                ->prepare("DELETE FROM login_intentos WHERE ip = ?")
                ->execute([$ip]);
        } catch (\PDOException $e) {
            // Omitir si la tabla no existe aún
        }
    }

    // ── GET /registro ─────────────────────────────────────────
    public function registroForm(): void {
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirigirSegunRol();
        }
        $this->render('auth/registro', ['titulo' => 'Crear cuenta — Postularme']);
    }

    // ── GET /stand ────────────────────────────────────────────
    public function standForm(): void {
        if (!empty($_SESSION['usuario_id'])) {
            $this->redirigirSegunRol();
        }
        $this->render('auth/stand', [
            'titulo' => 'Registro Rápido',
            'cupos'  => $this->consultarCupos(),
        ], 'stand');
    }

    // ── POST /stand ───────────────────────────────────────────
    public function registroStand(): void {
        $datos   = $_POST;
        $errores = $this->validarRegistro($datos);

        if (empty($errores)) {
            $id = $this->usuarios->registrar([
                'nombre'   => $this->post('nombre'),
                'apellido' => $this->post('apellido'),
                'email'    => $this->post('email'),
                'password' => $this->post('password'),
            ]);

            if ($id) {
                $usuario = $this->usuarios->porId($id);
                $_SESSION['usuario_id']     = (int) $usuario['id'];
                $_SESSION['usuario_nombre'] = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
                $_SESSION['usuario_email']  = $usuario['email'];
                $_SESSION['usuario_rol']    = 'candidato';
                session_regenerate_id(true);

                $this->redirigir('/candidato/dashboard');
            } else {
                $errores[] = 'El correo electrónico ya está registrado. ¿Ya tienes cuenta?';
            }
        }

        $this->render('auth/stand', [
            'titulo'  => 'Registro Rápido',
            'errores' => $errores,
            'datos'   => $datos,
            'cupos'   => $this->consultarCupos(),
        ], 'stand');
    }

    // ── GET /api/cupos ────────────────────────────────────────
    public function apiCupos(): void {
        $this->json($this->consultarCupos());
    }

    // ── Helper cupos ──────────────────────────────────────────
    private function consultarCupos(): array {
        $total = 40; // cupos primera cohorte
        try {
            $db       = Database::getConnection();
            $inscritos = (int) $db->query(
                "SELECT COUNT(*) FROM aspirantes WHERE estatus NOT IN ('rechazado')"
            )->fetchColumn();
        } catch (\PDOException $e) {
            $inscritos = 0;
        }
        return [
            'total'       => $total,
            'inscritos'   => $inscritos,
            'disponibles' => max(0, $total - $inscritos),
        ];
    }

    // ── POST /registro ────────────────────────────────────────
    public function registro(): void {
        $datos   = $_POST;
        $errores = $this->validarRegistro($datos);

        if (empty($errores)) {
            $id = $this->usuarios->registrar([
                'nombre'   => $this->post('nombre'),
                'apellido' => $this->post('apellido'),
                'email'    => $this->post('email'),
                'password' => $this->post('password'),
            ]);

            if ($id) {
                // Auto-login: primero poblar sesión, luego regenerar ID
                $usuario = $this->usuarios->porId($id);
                $_SESSION['usuario_id']     = (int) $usuario['id'];
                $_SESSION['usuario_nombre'] = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
                $_SESSION['usuario_email']  = $usuario['email'];
                $_SESSION['usuario_rol']    = 'candidato';
                session_regenerate_id(true);

                $this->redirigir('/candidato/dashboard');
            } else {
                $errores[] = 'El correo electrónico ya está registrado. ¿Ya tienes cuenta?';
            }
        }

        $this->render('auth/registro', [
            'titulo'  => 'Crear cuenta — Postularme',
            'errores' => $errores,
            'datos'   => $datos,
        ]);
    }

    // ── GET /logout ───────────────────────────────────────────
    public function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirigir('/login');
    }

    // ── Helpers privados ──────────────────────────────────────
    private function redirigirSegunRol(): void {
        match ($_SESSION['usuario_rol']) {
            'admin'     => $this->redirigir('/admin'),
            'evaluador' => $this->redirigir('/admin'),
            default     => $this->redirigir('/candidato/dashboard'),
        };
    }

    private function validarRegistro(array $d): array {
        $errores = [];

        if (empty(trim($d['nombre'] ?? '')))   $errores[] = 'El nombre es requerido.';
        if (empty(trim($d['apellido'] ?? '')))  $errores[] = 'El apellido es requerido.';

        $email = trim($d['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido.';
        }

        $pass  = $d['password']  ?? '';
        $pass2 = $d['password2'] ?? '';

        if (strlen($pass) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($pass !== $pass2) {
            $errores[] = 'Las contraseñas no coinciden.';
        }
        if (!isset($d['acepta_terminos'])) {
            $errores[] = 'Debes aceptar los términos y condiciones.';
        }

        return $errores;
    }
}
