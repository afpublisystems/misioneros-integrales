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
            'actividades' => self::ACTIVIDADES_GALERIA,
        ]);
    }

    public function impacto(): void {
        $db = Database::getConnection();

        $stmt = $db->query("SELECT * FROM impacto_estadisticas WHERE activo = 1 ORDER BY orden");
        $stats = $stmt->fetchAll();

        require_once APP_PATH . '/models/EvangelismoModel.php';

        $this->render('publico/impacto', [
            'titulo' => 'Impacto',
            'stats'  => $stats,
            'campo'  => (new EvangelismoModel())->totales(),
        ]);
    }

    // ── GET /cohorte-1 ────────────────────────────────
    // Los 14 del ciclo en curso, con el retrato que se les tomó para
    // el programa (aspirantes.foto_publica).
    public function cohorte(): void {
        $db = Database::getConnection();

        $filas = $db->query("
            SELECT nombres, apellidos, ciudad_origen, estado_origen, iglesia, foto_publica
            FROM aspirantes
            WHERE cohorte = 1 AND estatus = 'aprobada'
            ORDER BY nombres ASC, apellidos ASC
        ")->fetchAll();

        $participantes = [];
        $ciudades = [];
        $iglesias = [];

        foreach ($filas as $f) {
            $ciudad = trim($f['ciudad_origen']);
            $estado = trim($f['estado_origen']);
            // Ciudad y estado iguales (La Guaira, por ejemplo) se leen feo repetidos
            $lugar = $ciudad;
            if ($estado !== '' && mb_strtolower($estado) !== mb_strtolower($ciudad)) {
                $lugar = $ciudad !== '' ? $ciudad . ', ' . $estado : $estado;
            }

            $nombre = explode(' ', trim($f['nombres']))[0];
            $apellido = explode(' ', trim($f['apellidos']))[0];
            $iglesia = $this->nombreIglesia($f['iglesia']);

            $participantes[] = [
                'nombres'      => $f['nombres'],
                'apellidos'    => $f['apellidos'],
                'nombre_corto' => trim($nombre . ' ' . $apellido),
                'ciudad'       => $lugar,
                'iglesia'      => $iglesia,
                'foto_publica' => $f['foto_publica'],
            ];

            if ($ciudad !== '') $ciudades[mb_strtolower($ciudad)] = true;
            if ($iglesia !== '') $iglesias[$this->claveIglesia($iglesia)] = true;
        }

        $this->render('publico/cohorte', [
            'titulo'        => '1era Cohorte',
            'participantes' => $participantes,
            'ciudades'      => count($ciudades),
            'iglesias'      => count($iglesias),
        ]);
    }

    /**
     * Para contar iglesias, no para mostrarlas. Dos de la misma iglesia la
     * escriben distinto («I.B. Casa de Dios» y «Casa de Dios») y sin esto
     * el contador las toma como dos.
     *
     * Se queda con lo único que las distingue: el nombre propio, sin
     * tildes, sin puntuación y sin las palabras que llevan todas.
     */
    private function claveIglesia(string $nombre): string {
        $clave = mb_strtolower($nombre);
        $clave = strtr($clave, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','ñ'=>'n']);
        $clave = preg_replace('/[^a-z0-9\s]/u', ' ', $clave);
        // «primera» y «segunda» sí distinguen: Primera IB de Valencia no es la IB de Valencia.
        // La «i» y la «b» sueltas son lo que queda de un «I.B.» al quitarle los puntos.
        $clave = preg_replace('/\b(iglesia|bautista|ib|i|b|de|del|la|el|los|las|y)\b/u', ' ', $clave);
        $clave = trim(preg_replace('/\s+/u', ' ', $clave));

        return $clave !== '' ? $clave : mb_strtolower($nombre);
    }

    /**
     * Cada quien escribe el nombre de su iglesia a su manera: «I.B. Casa de
     * Dios», «Bautista Central», «Luz y Vida». En la página se leen todas
     * igual, sin tocar lo que puso en su ficha.
     *
     * Las que ya traen su nombre completo —«Primera Iglesia Bautista…»—
     * se respetan tal cual.
     */
    private function nombreIglesia(?string $crudo): string {
        $nombre = trim(preg_replace('/\s+/u', ' ', (string) $crudo));
        if ($nombre === '') return '';

        // Abreviaturas del principio: I.B., IB, Igl. Bautista
        $nombre = preg_replace('/^(I\.?\s?B\.?|Igl?\.?)\s+/iu', 'Iglesia Bautista ', $nombre, 1);
        $nombre = preg_replace('/^Iglesia Bautista Bautista\s+/iu', 'Iglesia Bautista ', $nombre, 1);

        // Hay quien la escribe toda en minúsculas; el resto del nombre se respeta
        $nombre = preg_replace('/^iglesia\s+bautista\b/iu', 'Iglesia Bautista', $nombre, 1);

        $minusculas = mb_strtolower($nombre);
        $tiene_iglesia  = str_contains($minusculas, 'iglesia');
        $tiene_bautista = str_contains($minusculas, 'bautista');

        if ($tiene_iglesia && $tiene_bautista) return $nombre;   // ya está completa
        if ($tiene_bautista) return 'Iglesia ' . $nombre;        // «Bautista Central»
        if ($tiene_iglesia)  return $nombre;                     // no asumimos la denominación
        return 'Iglesia Bautista ' . $nombre;                    // «Luz y Vida»
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
