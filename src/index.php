<?php
/**
 * Punto de entrada único — Router Principal
 * Misioneros Integrales - CNBV/DIME
 */

// ============================================================
// ENTORNO — detección automática dev/producción
// ============================================================
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('APP_DEV', in_array($host, ['localhost', 'localhost:8080', '127.0.0.1', '127.0.0.1:8080']));

if (APP_DEV) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

define('BASE_PATH', __DIR__);
define('APP_PATH',  BASE_PATH . '/app');

// Autoloader simple para clases MVC
spl_autoload_register(function (string $clase) {
    $rutas = [
        APP_PATH . '/controllers/' . $clase . '.php',
        APP_PATH . '/models/'      . $clase . '.php',
    ];
    foreach ($rutas as $ruta) {
        if (file_exists($ruta)) {
            require_once $ruta;
            return;
        }
    }
});

// Cargar configuración de BD
require_once APP_PATH . '/config/db.php';

// Iniciar sesión con flags de seguridad
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['SERVER_PORT'] ?? 80) == 443;

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'cookie_secure'   => $isHttps,
]);

// ============================================================
// CSRF — generación de token y función helper para vistas
// ============================================================
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Genera el campo oculto CSRF para incluir en formularios POST.
 * Uso en vistas: <?= csrf_field() ?>
 */
function csrf_field(): string {
    return '<input type="hidden" name="_token" value="'
        . htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8')
        . '">';
}

// Obtener la ruta solicitada
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// ============================================================
// TABLA DE RUTAS
// ============================================================
$rutas = [
    // Públicas
    'GET'  => [
        '/'                     => ['PublicoController', 'home'],
        '/programa'             => ['PublicoController', 'programa'],
        '/requisitos'           => ['PublicoController', 'requisitos'],
        '/pensum'               => ['PublicoController', 'pensum'],
        '/galeria'              => ['PublicoController', 'galeria'],
        '/impacto'              => ['PublicoController', 'impacto'],
        '/contacto'             => ['PublicoController', 'contacto'],
        // Auth
        '/login'                => ['AuthController',    'loginForm'],
        '/registro'             => ['AuthController',    'registroForm'],
        '/stand'                => ['AuthController',    'standForm'],
        '/api/cupos'            => ['AuthController',    'apiCupos'],
        '/logout'               => ['AuthController',    'logout'],
        // Candidato
        '/candidato/dashboard'  => ['CandidatoController', 'dashboard'],
        '/candidato/perfil'     => ['CandidatoController', 'perfil'],
        '/candidato/documentos' => ['CandidatoController', 'documentos'],
        '/candidato/test'       => ['CandidatoController', 'test'],
        '/candidato/resultado-test' => ['CandidatoController', 'resultadoTest'],
        '/candidato/perfil/clave'   => ['CandidatoController', 'cambiarPassword'],
        // Admin
        '/admin'                => ['AdminController',   'dashboard'],
        '/admin/candidatos'     => ['AdminController',   'candidatos'],
        '/admin/galeria'        => ['AdminController',   'galeria'],
        '/admin/estadisticas'   => ['AdminController',   'estadisticas'],
        '/admin/colaboradores'  => ['AdminController',   'colaboradores'],
        '/admin/perfil'         => ['AdminController',   'perfil'],
    ],
    'POST' => [
        '/login'                    => ['AuthController',       'login'],
        '/registro'                 => ['AuthController',       'registro'],
        '/stand'                    => ['AuthController',       'registroStand'],
        '/candidato/perfil'         => ['CandidatoController',  'guardarPerfil'],
        '/candidato/documentos'     => ['CandidatoController',  'subirDocumento'],
        '/candidato/test'           => ['CandidatoController',  'guardarTest'],
        '/candidato/perfil/clave'   => ['CandidatoController',  'cambiarPassword'],
        '/candidato/postular'       => ['CandidatoController',  'enviarPostulacion'],
        '/admin/candidatos'         => ['AdminController',      'actualizarEstatus'],
        '/admin/galeria'            => ['AdminController',      'gestionarGaleria'],
        '/admin/estadisticas'       => ['AdminController',      'actualizarEstadisticas'],
        '/admin/colaboradores'      => ['AdminController',      'gestionarColaborador'],
        '/admin/perfil'             => ['AdminController',      'actualizarPerfil'],
        '/colaborar'                => ['PublicoController',    'registrarColaborador'],
        '/contacto'                 => ['PublicoController',    'enviarContacto'],
    ],
];

$metodo = $_SERVER['REQUEST_METHOD'];

// ============================================================
// CSRF — verificación centralizada para todos los POST
// ============================================================
if ($metodo === 'POST') {
    $token_recibido = $_POST['_token'] ?? '';
    if (
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token_recibido)
    ) {
        http_response_code(403);
        require APP_PATH . '/views/errors/403.php';
        exit;
    }
}

if (isset($rutas[$metodo][$uri])) {
    [$controlador, $accion] = $rutas[$metodo][$uri];

    // Verificar que el controlador existe
    $archivo = APP_PATH . '/controllers/' . $controlador . '.php';
    if (file_exists($archivo)) {
        require_once $archivo;
        $ctrl = new $controlador();
        $ctrl->$accion();
    } else {
        require_once APP_PATH . '/views/errors/en_construccion.php';
    }
} else {
    http_response_code(404);
    require_once APP_PATH . '/views/errors/404.php';
}
