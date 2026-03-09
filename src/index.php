<?php
/**
 * Punto de entrada único — Router Principal
 * Misioneros Integrales - CNBV/DIME
 */

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

// Iniciar sesión
session_start();

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
        '/galeria'              => ['PublicoController', 'galeria'],
        '/impacto'              => ['PublicoController', 'impacto'],
        '/contacto'             => ['PublicoController', 'contacto'],
        // Auth
        '/login'                => ['AuthController',    'loginForm'],
        '/registro'             => ['AuthController',    'registroForm'],
        '/logout'               => ['AuthController',    'logout'],
        // Candidato
        '/candidato/dashboard'  => ['CandidatoController', 'dashboard'],
        '/candidato/perfil'     => ['CandidatoController', 'perfil'],
        '/candidato/documentos' => ['CandidatoController', 'documentos'],
        '/candidato/test'       => ['CandidatoController', 'test'],
        // Admin
        '/admin'                => ['AdminController',   'dashboard'],
        '/admin/candidatos'     => ['AdminController',   'candidatos'],
        '/admin/galeria'        => ['AdminController',   'galeria'],
        '/admin/estadisticas'   => ['AdminController',   'estadisticas'],
        '/admin/perfil'         => ['AdminController',   'perfil'],
    ],
    'POST' => [
        '/login'                => ['AuthController',       'login'],
        '/registro'             => ['AuthController',       'registro'],
        '/candidato/perfil'     => ['CandidatoController',  'guardarPerfil'],
        '/candidato/documentos' => ['CandidatoController',  'subirDocumento'],
        '/candidato/test'       => ['CandidatoController',  'guardarTest'],
        '/admin/candidatos'     => ['AdminController',      'actualizarEstatus'],
        '/admin/estadisticas'   => ['AdminController',      'actualizarEstadisticas'],
        '/admin/perfil'         => ['AdminController',      'actualizarPerfil'],
    ],
];

$metodo = $_SERVER['REQUEST_METHOD'];

if (isset($rutas[$metodo][$uri])) {
    [$controlador, $accion] = $rutas[$metodo][$uri];

    // Verificar que el controlador existe
    $archivo = APP_PATH . '/controllers/' . $controlador . '.php';
    if (file_exists($archivo)) {
        require_once $archivo;
        $ctrl = new $controlador();
        $ctrl->$accion();
    } else {
        // Controlador no implementado aún
        require_once APP_PATH . '/views/errors/en_construccion.php';
    }
} else {
    // Ruta no encontrada
    http_response_code(404);
    require_once APP_PATH . '/views/errors/404.php';
}
