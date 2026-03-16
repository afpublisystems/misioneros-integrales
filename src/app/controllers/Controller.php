<?php
/**
 * Controlador Base
 * Todos los controladores extienden esta clase
 */

class Controller {

    /**
     * Renderiza una vista dentro del layout principal
     */
    protected function render(string $vista, array $datos = [], string $layout = 'main'): void {
        // Extraer variables para la vista
        extract($datos);

        // Iniciar buffer de salida
        ob_start();
        $archivo = APP_PATH . '/views/' . $vista . '.php';
        if (file_exists($archivo)) {
            require $archivo;
        }
        $contenido = ob_get_clean();

        // Cargar layout
        $layoutArchivo = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutArchivo)) {
            require $layoutArchivo;
        } else {
            echo $contenido;
        }
    }

    /**
     * Renderiza una vista sin layout (para AJAX o fragmentos)
     */
    protected function renderParcial(string $vista, array $datos = []): void {
        extract($datos);
        $archivo = APP_PATH . '/views/' . $vista . '.php';
        if (file_exists($archivo)) {
            require $archivo;
        }
    }

    /**
     * Redirigir a una URL
     */
    protected function redirigir(string $url): void {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Verificar que el usuario esté autenticado
     */
    protected function requireAuth(string $rol = null): void {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirigir('/login');
        }
        if ($rol && $_SESSION['usuario_rol'] !== $rol) {
            http_response_code(403);
            require APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Responder con JSON (para endpoints AJAX)
     */
    protected function json(array $datos, int $codigo = 200): void {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Obtener dato POST saneado
     */
    protected function post(string $campo, string $default = ''): string {
        return htmlspecialchars(trim($_POST[$campo] ?? $default), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Obtener dato GET saneado
     */
    protected function get(string $campo, string $default = ''): string {
        return htmlspecialchars(trim($_GET[$campo] ?? $default), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Establecer mensaje flash de sesión
     */
    protected function flash(string $tipo, string $msg): void {
        $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
    }

    /**
     * Verificar que el usuario tenga uno de los roles indicados
     */
    protected function requireAnyRole(array $roles): void {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirigir('/login');
        }
        if (!in_array($_SESSION['usuario_rol'] ?? '', $roles)) {
            http_response_code(403);
            require APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Validar y sanear una URL de redirección interna (previene open-redirect)
     */
    protected function safeRedirect(string $url, string $fallback = '/'): string {
        // Solo permite rutas internas: empiezan con / pero no con //
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return $url;
        }
        return $fallback;
    }
}
