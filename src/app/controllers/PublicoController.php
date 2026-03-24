<?php
require_once APP_PATH . '/controllers/Controller.php';

class PublicoController extends Controller {

    public function home(): void {
        $this->render('publico/home', [
            'titulo'      => 'Inicio',
            'clase_body'  => 'pagina-home',
        ]);
    }

    public function programa(): void {
        $this->render('publico/programa', ['titulo' => 'El Programa']);
    }

    public function pensum(): void {
        $this->render('publico/pensum', ['titulo' => 'Malla Curricular']);
    }

    public function requisitos(): void {
        $this->render('publico/requisitos', ['titulo' => 'Requisitos']);
    }

    public function galeria(): void {
        $db = Database::getConnection();

        $stmt = $db->query("SELECT id, nombre, estado, mes, orden, fecha_inicio FROM sedes WHERE activa = 1 ORDER BY orden");
        $sedes = $stmt->fetchAll();

        $stmt2 = $db->query("
            SELECT m.*, s.nombre AS sede_nombre
            FROM multimedia m
            JOIN sedes s ON s.id = m.sede_id
            WHERE m.activo = 1
            ORDER BY m.destacado DESC, m.orden ASC, m.id DESC
        ");
        $multimedia = $stmt2->fetchAll();

        $this->render('publico/galeria', [
            'titulo'     => 'Galería',
            'sedes'      => $sedes,
            'multimedia' => $multimedia,
        ]);
    }

    public function impacto(): void {
        $db = Database::getConnection();

        $stmt = $db->query("SELECT * FROM impacto_estadisticas WHERE activo = 1 ORDER BY orden");
        $stats = $stmt->fetchAll();

        $this->render('publico/impacto', [
            'titulo' => 'Impacto',
            'stats'  => $stats,
        ]);
    }

    public function contacto(): void {
        $enviado = false;
        if (!empty($_SESSION['contacto_enviado'])) {
            $enviado = true;
            unset($_SESSION['contacto_enviado']);
        }
        $this->render('publico/contacto', [
            'titulo'  => 'Contacto',
            'enviado' => $enviado,
        ]);
    }

    public function enviarContacto(): void {
        $nombre   = trim($_POST['nombre']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $telefono = trim($_POST['telefono'] ?? '') ?: null;
        $asunto   = trim($_POST['asunto']   ?? '');
        $mensaje  = trim($_POST['mensaje']  ?? '');

        $asuntos_validos = ['postulacion', 'requisitos', 'documentos', 'fechas', 'iglesia', 'otro'];
        $errores = [];

        if (empty($nombre))                                   $errores[] = 'El nombre es obligatorio.';
        if (empty($email))                                    $errores[] = 'El correo electrónico es obligatorio.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errores[] = 'El correo electrónico no es válido.';
        if (!in_array($asunto, $asuntos_validos, true))       $errores[] = 'Selecciona un asunto válido.';
        if (empty($mensaje))                                  $errores[] = 'El mensaje no puede estar vacío.';
        elseif (mb_strlen($mensaje) < 10)                     $errores[] = 'El mensaje es demasiado corto.';

        if (!empty($errores)) {
            $this->render('publico/contacto', [
                'titulo'  => 'Contacto',
                'errores' => $errores,
            ]);
            return;
        }

        try {
            $db = Database::getConnection();
            $db->prepare(
                "INSERT INTO mensajes_contacto (nombre, email, telefono, asunto, mensaje)
                 VALUES (:nombre, :email, :telefono, :asunto, :mensaje)"
            )->execute([
                ':nombre'   => $nombre,
                ':email'    => $email,
                ':telefono' => $telefono,
                ':asunto'   => $asunto,
                ':mensaje'  => $mensaje,
            ]);

            $_SESSION['contacto_enviado'] = true;
            $this->redirigir('/contacto');

        } catch (Exception $e) {
            $this->render('publico/contacto', [
                'titulo'  => 'Contacto',
                'errores' => ['Error al enviar el mensaje. Por favor intenta de nuevo.'],
            ]);
        }
    }

    public function registrarColaborador(): void {
        $nombre       = trim($_POST['nombre']       ?? '');
        $email        = trim($_POST['email']        ?? '');
        $organizacion = trim($_POST['organizacion'] ?? '') ?: null;
        $tipo         = trim($_POST['tipo']         ?? 'otro');
        $mensaje      = trim($_POST['mensaje']      ?? '') ?: null;

        $tipos_validos = ['economico', 'especie', 'servicios', 'voluntariado', 'otro'];

        // Validación básica
        if (empty($nombre) || empty($email)) {
            $_SESSION['flash_colabora'] = [
                'tipo'    => 'error',
                'mensaje' => 'Por favor completa tu nombre y correo electrónico.',
            ];
            header('Location: /#colabora');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_colabora'] = [
                'tipo'    => 'error',
                'mensaje' => 'El correo electrónico ingresado no es válido.',
            ];
            header('Location: /#colabora');
            exit;
        }

        if (!in_array($tipo, $tipos_validos, true)) {
            $tipo = 'otro';
        }

        try {
            $db   = Database::getConnection();
            $stmt = $db->prepare(
                "INSERT INTO colaboradores (nombre, organizacion, email, tipo, mensaje)
                 VALUES (:nombre, :organizacion, :email, :tipo, :mensaje)"
            );
            $stmt->execute([
                ':nombre'       => $nombre,
                ':organizacion' => $organizacion,
                ':email'        => $email,
                ':tipo'         => $tipo,
                ':mensaje'      => $mensaje,
            ]);

            $_SESSION['flash_colabora'] = [
                'tipo'    => 'exito',
                'mensaje' => '¡Gracias, ' . htmlspecialchars($nombre) . '! Hemos recibido tu interés. Pronto nos pondremos en contacto contigo.',
            ];
        } catch (Exception $e) {
            $_SESSION['flash_colabora'] = [
                'tipo'    => 'error',
                'mensaje' => 'Ocurrió un error al registrar tu información. Por favor intenta nuevamente.',
            ];
        }

        header('Location: /#colabora');
        exit;
    }
}
