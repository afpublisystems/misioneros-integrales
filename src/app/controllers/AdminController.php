<?php
/**
 * AdminController
 * Panel de administración CNBV/DIME
 * Roles permitidos: admin, evaluador
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/AspiranteModel.php';
require_once APP_PATH . '/models/UsuarioModel.php';

class AdminController extends Controller {

    private AspiranteModel $aspirantes;

    public function __construct() {
        $this->aspirantes = new AspiranteModel();
    }

    // ── GET /admin ────────────────────────────────────────────
    public function dashboard(): void {
        $this->requireAdminOEvaluador();

        $db = Database::getConnection();

        // KPIs generales
        $kpis = [
            'total'       => $this->contarPorEstatus(''),
            'borrador'    => $this->contarPorEstatus('borrador'),
            'enviada'     => $this->contarPorEstatus('enviada'),
            'en_revision' => $this->contarPorEstatus('en_revision'),
            'aprobada'    => $this->contarPorEstatus('aprobada'),
            'rechazada'   => $this->contarPorEstatus('rechazada'),
        ];

        // Últimos 5 aspirantes registrados
        $stmt = $db->prepare("
            SELECT a.id, a.nombres, a.apellidos, a.ciudad_origen, a.estado_origen,
                   a.iglesia, a.estatus, a.created_at, u.email
            FROM aspirantes a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $recientes = $stmt->fetchAll();

        // Distribución por estado
        $stmt2 = $db->prepare("
            SELECT estado_origen, COUNT(*) AS total
            FROM aspirantes
            GROUP BY estado_origen
            ORDER BY total DESC
            LIMIT 8
        ");
        $stmt2->execute();
        $por_estado = $stmt2->fetchAll();

        $this->render('admin/dashboard', [
            'titulo'     => 'Panel de Administración',
            'kpis'       => $kpis,
            'recientes'  => $recientes,
            'por_estado' => $por_estado,
        ], 'admin');
    }

    // ── GET /admin/candidatos ─────────────────────────────────
    public function candidatos(): void {
        $this->requireAdminOEvaluador();

        $filtro_estatus = $_GET['estatus'] ?? '';
        $busqueda       = trim($_GET['q'] ?? '');
        $db             = Database::getConnection();

        $sql    = "SELECT a.*, u.email FROM aspirantes a LEFT JOIN usuarios u ON u.id = a.usuario_id WHERE 1=1";
        $params = [];

        if ($filtro_estatus) {
            $sql .= " AND a.estatus = :estatus";
            $params[':estatus'] = $filtro_estatus;
        }
        if ($busqueda) {
            $sql .= " AND (a.nombres LIKE :q OR a.apellidos LIKE :q OR a.cedula LIKE :q OR a.iglesia LIKE :q)";
            $params[':q'] = "%{$busqueda}%";
        }

        $sql .= " ORDER BY a.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $lista = $stmt->fetchAll();

        $this->render('admin/candidatos', [
            'titulo'          => 'Gestión de Candidatos',
            'lista'           => $lista,
            'filtro_estatus'  => $filtro_estatus,
            'busqueda'        => $busqueda,
            'totales'         => [
                'todos'       => $this->contarPorEstatus(''),
                'enviada'     => $this->contarPorEstatus('enviada'),
                'en_revision' => $this->contarPorEstatus('en_revision'),
                'aprobada'    => $this->contarPorEstatus('aprobada'),
                'rechazada'   => $this->contarPorEstatus('rechazada'),
            ],
        ], 'admin');
    }

    // ── GET /admin/candidato/{id} — ver detalle ───────────────
    public function verCandidato(): void {
        $this->requireAdminOEvaluador();

        $id        = (int) ($_GET['id'] ?? 0);
        $db        = Database::getConnection();

        $stmt = $db->prepare("
            SELECT a.*, u.email, u.ultimo_acceso
            FROM aspirantes a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $aspirante = $stmt->fetch();

        if (!$aspirante) {
            $this->redirigir('/admin/candidatos');
        }

        // Documentos
        require_once APP_PATH . '/models/DocumentoModel.php';
        $docModel  = new DocumentoModel();
        $documentos = $docModel->porAspirante($id);

        // Flujo del proceso
        $stmt2 = $db->prepare(
            "SELECT * FROM flujo_proceso WHERE aspirante_id = :id ORDER BY etapa"
        );
        $stmt2->execute([':id' => $id]);
        $flujo = $stmt2->fetchAll();

        $this->render('admin/ver_candidato', [
            'titulo'     => $aspirante['nombres'] . ' ' . $aspirante['apellidos'],
            'aspirante'  => $aspirante,
            'documentos' => $documentos,
            'flujo'      => $flujo,
        ], 'admin');
    }

    // ── POST /admin/candidatos — cambiar estatus ──────────────
    public function actualizarEstatus(): void {
        $this->requireAdminOEvaluador();

        $id        = (int) ($_POST['id'] ?? 0);
        $estatus   = $_POST['estatus'] ?? '';
        $nota      = trim($_POST['nota'] ?? '');
        $estatuses = ['enviada', 'en_revision', 'aprobada', 'rechazada', 'borrador'];

        if ($id && in_array($estatus, $estatuses)) {
            $this->aspirantes->actualizar($id, [
                'estatus'       => $estatus,
                'nota_evaluador'=> $nota,
            ]);
            $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => 'Estatus actualizado correctamente.'];
        }

        $this->redirigir('/admin/candidatos');
    }

    // ── GET /admin/estadisticas ───────────────────────────────
    public function estadisticas(): void {
        $this->requireAdminOEvaluador();
        $db = Database::getConnection();

        $stmt = $db->query("SELECT * FROM impacto_estadisticas ORDER BY orden");
        $stats = $stmt->fetchAll();

        $this->render('admin/estadisticas', [
            'titulo' => 'Estadísticas de Impacto',
            'stats'  => $stats,
        ], 'admin');
    }

    // ── POST /admin/estadisticas ──────────────────────────────
    public function actualizarEstadisticas(): void {
        $this->requireAuth('admin'); // Solo admin, no evaluador
        $db = Database::getConnection();

        foreach ($_POST['valor'] as $id => $valor) {
            $stmt = $db->prepare("UPDATE impacto_estadisticas SET valor = :v WHERE id = :id");
            $stmt->execute([':v' => (int) $valor, ':id' => (int) $id]);
        }
        $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => 'Estadísticas actualizadas.'];
        $this->redirigir('/admin/estadisticas');
    }


    // ── GET /admin/perfil ─────────────────────────────────────
    public function perfil(): void {
        $this->requireAdminOEvaluador();
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT id, nombre, apellido, email, rol FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();

        $this->render('admin/perfil', [
            'titulo'  => 'Mi Perfil',
            'usuario' => $usuario,
        ], 'admin');
    }

    // ── POST /admin/perfil ────────────────────────────────────
    public function actualizarPerfil(): void {
        $this->requireAdminOEvaluador();

        $db      = Database::getConnection();
        $id      = (int) $_SESSION['usuario_id'];
        $accion  = $_POST['accion'] ?? '';
        $errores = [];

        if ($accion === 'datos') {
            $nombre   = trim($_POST['nombre']   ?? '');
            $apellido = trim($_POST['apellido']  ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));

            if (!$nombre || !$apellido)
                $errores[] = 'Nombre y apellido son obligatorios.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errores[] = 'El correo no es valido.';

            if (!$errores) {
                $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :e AND id != :id");
                $stmt->execute([':e' => $email, ':id' => $id]);
                if ($stmt->fetch()) $errores[] = 'Ese correo ya esta en uso.';
            }

            if (!$errores) {
                $stmt = $db->prepare("UPDATE usuarios SET nombre=:n, apellido=:a, email=:e WHERE id=:id");
                $stmt->execute([':n'=>$nombre, ':a'=>$apellido, ':e'=>$email, ':id'=>$id]);
                $_SESSION['usuario_nombre'] = $nombre . ' ' . $apellido;
                $_SESSION['usuario_email']  = $email;
                $_SESSION['flash'] = ['tipo'=>'exito', 'msg'=>'Datos actualizados correctamente.'];
                $this->redirigir('/admin/perfil');
            }

        } elseif ($accion === 'password') {
            $actual   = $_POST['password_actual']   ?? '';
            $nueva    = $_POST['password_nueva']    ?? '';
            $confirma = $_POST['password_confirma'] ?? '';

            $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($actual, $hash))
                $errores[] = 'La contrasena actual no es correcta.';
            if (strlen($nueva) < 8)
                $errores[] = 'La nueva contrasena debe tener al menos 8 caracteres.';
            if ($nueva !== $confirma)
                $errores[] = 'La confirmacion no coincide.';

            if (!$errores) {
                require_once APP_PATH . '/models/UsuarioModel.php';
                $uModel = new UsuarioModel();
                $uModel->cambiarPassword($id, $nueva);
                $_SESSION['flash'] = ['tipo'=>'exito', 'msg'=>'Contrasena actualizada correctamente.'];
                $this->redirigir('/admin/perfil');
            }
        }

        $stmt = $db->prepare("SELECT id, nombre, apellido, email, rol FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();

        $this->render('admin/perfil', [
            'titulo'  => 'Mi Perfil',
            'usuario' => $usuario,
            'errores' => $errores,
            'accion'  => $accion,
        ], 'admin');
    }

    // ── Helpers ───────────────────────────────────────────────
    private function requireAdminOEvaluador(): void {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirigir('/login');
        }
        if (!in_array($_SESSION['usuario_rol'], ['admin', 'evaluador'])) {
            http_response_code(403);
            require APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    private function contarPorEstatus(string $estatus): int {
        $db   = Database::getConnection();
        $sql  = $estatus
            ? "SELECT COUNT(*) FROM aspirantes WHERE estatus = :e"
            : "SELECT COUNT(*) FROM aspirantes";
        $stmt = $db->prepare($sql);
        $estatus ? $stmt->execute([':e' => $estatus]) : $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
