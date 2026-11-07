<?php
/**
 * EvangelismoController
 * Registro de personas alcanzadas en campo.
 *
 * Dos entradas: el panel admin (/admin/evangelismo) y un acceso
 * público con PIN (/evangelismo) para que el equipo en campo cargue
 * desde el teléfono sin usuario. Quien entra con PIN solo puede
 * registrar: nunca ve la lista, porque tiene teléfonos y direcciones.
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/EvangelismoModel.php';

class EvangelismoController extends Controller {

    /** Horas que dura la sesión abierta con el PIN */
    private const HORAS_PIN = 12;

    private EvangelismoModel $modelo;

    public function __construct() {
        $this->modelo = new EvangelismoModel();
    }

    // ══════════════════════════════════════════════════════════
    // PANEL ADMIN
    // ══════════════════════════════════════════════════════════

    // ── GET /admin/evangelismo ────────────────────────────────
    public function index(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $filtros = [
            'sede_id'   => $_GET['sede_id']   ?? '',
            'sector_id' => $_GET['sector_id'] ?? '',
            'etapa'     => $_GET['etapa']     ?? '',
            'q'         => trim($_GET['q']    ?? ''),
        ];

        $this->render('admin/evangelismo', [
            'titulo'          => 'Evangelismo',
            'filtros'         => $filtros,
            'personas'        => $this->modelo->listar($filtros),
            'totales'         => $this->modelo->totales(),
            'por_sede'        => $this->modelo->totalesPorSede(),
            'sedes'           => $this->modelo->sedes(),
            'sectores'        => $this->modelo->sectores(),
            'sectores_todos'  => $this->modelo->sectoresTodos(),
            'lecciones'       => $this->modelo->lecciones(),
            'sede_actual'     => $this->modelo->sedeActual(),
            'sede_fija'       => $this->modelo->sedeFija(),
            'sede_itinerario' => $this->modelo->sedeEnFecha(date('Y-m-d')),
            'pin_activo'      => $this->modelo->hashPin() !== null,
        ], 'admin');
    }

    // ── POST /admin/evangelismo — registrar o editar ──────────
    public function guardar(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $volver = '/admin/evangelismo';
        $datos  = $this->datosDesdePost();
        if (is_string($datos)) {
            $this->flash('error', $datos);
            $this->redirigir($volver);
        }

        $id     = (int) ($_POST['id'] ?? 0);
        $actual = $id ? $this->modelo->porId($id) : null;

        if ($id && !$actual) {
            $this->flash('error', 'No se encontró el registro.');
            $this->redirigir($volver);
        }

        $foto = $this->fotoDesdePost($actual['foto'] ?? null);
        if (is_string($foto)) {
            $this->flash('error', $foto);
            $this->redirigir($volver);
        }
        if ($foto !== null) {
            $datos['foto'] = $foto['nombre'];
        }

        if ($id) {
            $this->modelo->actualizar($id, $datos);
            $this->flash('exito', 'Registro actualizado.');
        } else {
            $datos['registrado_via'] = 'admin';
            $datos['registrado_por'] = (int) $_SESSION['usuario_id'];
            $id = $this->modelo->registrar($datos);
            $this->flash('exito', 'Persona registrada.');
        }

        $this->guardarLecciones($id, $datos['discipulado']);

        // La anterior se borra recién acá, con el registro ya guardado
        if ($foto !== null && $foto['anterior']) {
            $this->borrarFoto($foto['anterior']);
        }

        $this->redirigir($volver);
    }

    // ── POST /admin/evangelismo/eliminar ──────────────────────
    public function eliminar(): void {
        $this->requireAuth('admin');

        $id      = (int) ($_POST['id'] ?? 0);
        $persona = $id ? $this->modelo->porId($id) : null;
        $ok      = $persona && $this->modelo->eliminar($id);
        if ($ok && $persona['foto']) {
            $this->borrarFoto($persona['foto']);
        }
        $this->flash($ok ? 'exito' : 'error', $ok ? 'Registro eliminado.' : 'No se pudo eliminar.');
        $this->redirigir('/admin/evangelismo');
    }

    // ── POST /admin/evangelismo/pin ───────────────────────────
    public function pin(): void {
        $this->requireAuth('admin');

        if (($_POST['accion'] ?? '') === 'desactivar') {
            $this->modelo->guardarPin(null);
            $this->flash('exito', 'Acceso con PIN desactivado. Quien estuviera dentro queda afuera.');
            $this->redirigir('/admin/evangelismo');
        }

        $pin  = trim($_POST['pin']  ?? '');
        $pin2 = trim($_POST['pin2'] ?? '');

        if (mb_strlen($pin) < 6 || mb_strlen($pin) > 20) {
            $this->flash('error', 'El PIN debe tener entre 6 y 20 caracteres.');
        } elseif ($pin !== $pin2) {
            $this->flash('error', 'Los dos PIN no coinciden.');
        } else {
            $this->modelo->guardarPin($pin);
            $this->flash('exito', 'PIN guardado. Quien tenía el anterior tiene que entrar con el nuevo.');
        }
        $this->redirigir('/admin/evangelismo');
    }

    // ── POST /admin/evangelismo/sede ──────────────────────────
    // Cuando el equipo está en un lugar fuera del itinerario, el admin
    // fija la sede para que los formularios la traigan marcada.
    public function sede(): void {
        $this->requireAuth('admin');

        $sede_id = (int) ($_POST['sede_id'] ?? 0);
        $validas = array_column($this->modelo->sedes(), 'nombre', 'id');

        if ($sede_id && !isset($validas[$sede_id])) {
            $this->flash('error', 'Esa sede no existe.');
        } else {
            $this->modelo->fijarSede($sede_id ?: null);
            $this->flash('exito', $sede_id
                ? 'Listo: los registros nuevos vienen marcados en ' . $validas[$sede_id] . '.'
                : 'Listo: la sede vuelve a salir según el itinerario.');
        }
        $this->redirigir('/admin/evangelismo');
    }

    // ── GET /admin/evangelismo/foto?id=N ──────────────────────
    // Las fotos no se sirven por URL: uploads/evangelismo/ está
    // cerrado por .htaccess y se pasan por acá, con sesión abierta.
    public function foto(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $persona = $this->modelo->porId((int) ($_GET['id'] ?? 0));
        $archivo = $persona && $persona['foto']
            ? BASE_PATH . '/uploads/evangelismo/' . basename($persona['foto'])
            : null;

        if (!$archivo || !is_file($archivo)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: ' . (mime_content_type($archivo) ?: 'application/octet-stream'));
        header('Content-Length: ' . filesize($archivo));
        header('Cache-Control: private, no-store');
        header('X-Content-Type-Options: nosniff');
        readfile($archivo);
        exit;
    }

    // ── POST /admin/evangelismo/sector ────────────────────────
    public function sector(): void {
        $this->requireAuth('admin');
        $volver = '/admin/evangelismo';

        if (($_POST['accion'] ?? '') === 'alternar') {
            $this->modelo->alternarSector((int) ($_POST['sector_id'] ?? 0));
            $this->redirigir($volver);
        }

        $sede_id = (int) ($_POST['sede_id'] ?? 0);
        $nombre  = trim($_POST['nombre'] ?? '');

        if ($nombre === '' || mb_strlen($nombre) > 120) {
            $this->flash('error', 'Escribe el nombre de la ciudad o sector.');
        } elseif (!in_array($sede_id, array_column($this->modelo->sedes(), 'id'))) {
            $this->flash('error', 'Elige a qué sede pertenece.');
        } else {
            $this->modelo->agregarSector($sede_id, $nombre);
            $this->flash('exito', 'Sector agregado: ' . $nombre . '.');
        }
        $this->redirigir($volver);
    }

    // ══════════════════════════════════════════════════════════
    // ACCESO PÚBLICO CON PIN
    // ══════════════════════════════════════════════════════════

    // ── GET /evangelismo ──────────────────────────────────────
    public function publico(): void {
        $hash = $this->modelo->hashPin();

        $this->render('publico/evangelismo', [
            'titulo'      => 'Registro de evangelismo',
            'habilitado'  => $hash !== null,
            'dentro'      => $this->pinVigente($hash),
            'responsable' => $_SESSION['evangelismo']['responsable'] ?? '',
            'sedes'       => $this->modelo->sedes(),
            'sectores'    => $this->modelo->sectores(),
            'lecciones'   => $this->modelo->lecciones(),
            'sede_actual' => $this->modelo->sedeActual(),
        ]);
    }

    // ── POST /evangelismo/pin ─────────────────────────────────
    public function entrar(): void {
        $hash  = $this->modelo->hashPin();
        $clave = 'pin:' . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida');

        if ($hash === null) {
            $this->redirigir('/evangelismo');
        }

        $bloqueo = $this->bloqueo($clave);
        if ($bloqueo) {
            $this->flash('error', $bloqueo);
            $this->redirigir('/evangelismo');
        }

        $responsable = trim($_POST['responsable'] ?? '');
        if ($responsable === '') {
            $this->flash('error', 'Escribe tu nombre para saber quién hizo cada registro.');
            $this->redirigir('/evangelismo');
        }

        if (!password_verify(trim($_POST['pin'] ?? ''), $hash)) {
            $this->sumarIntento($clave);
            $this->flash('error', 'PIN incorrecto.');
            $this->redirigir('/evangelismo');
        }

        $this->limpiarIntentos($clave);
        session_regenerate_id(true);
        $_SESSION['evangelismo'] = [
            'hash'        => $hash,
            'hasta'       => time() + self::HORAS_PIN * 3600,
            'responsable' => mb_substr($responsable, 0, 150),
        ];
        $this->redirigir('/evangelismo');
    }

    // ── POST /evangelismo ─────────────────────────────────────
    public function registrarPublico(): void {
        if (!$this->pinVigente($this->modelo->hashPin())) {
            $this->flash('error', 'Tu acceso venció. Vuelve a entrar con el PIN.');
            $this->redirigir('/evangelismo');
        }

        $datos = $this->datosDesdePost();
        if (is_string($datos)) {
            $this->flash('error', $datos);
            $this->redirigir('/evangelismo');
        }

        $foto = $this->fotoDesdePost(null);
        if (is_string($foto)) {
            $this->flash('error', $foto);
            $this->redirigir('/evangelismo');
        }
        if ($foto !== null) {
            $datos['foto'] = $foto['nombre'];
        }

        // Si no escribió otro responsable, cuenta quien entró con el PIN
        $datos['responsable']    = $datos['responsable'] ?? $_SESSION['evangelismo']['responsable'];
        $datos['registrado_via'] = 'pin';
        $this->guardarLecciones($this->modelo->registrar($datos), $datos['discipulado']);

        $this->flash('exito', 'Listo, quedó registrado ' . trim($datos['nombres'] . ' ' . $datos['apellidos']) . '.');
        $this->redirigir('/evangelismo');
    }

    // ── POST /evangelismo/salir ───────────────────────────────
    public function salir(): void {
        unset($_SESSION['evangelismo']);
        $this->redirigir('/evangelismo');
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * La sesión con PIN vale mientras no venza y mientras el PIN
     * sea el mismo: si el admin lo cambia, todos quedan afuera.
     */
    private function pinVigente(?string $hash): bool {
        $s = $_SESSION['evangelismo'] ?? null;
        return $hash !== null && $s
            && hash_equals($hash, $s['hash'])
            && $s['hasta'] > time();
    }

    /**
     * El avance solo se toca mientras la persona esté en discipulado.
     * Si sale, lo dado queda guardado: es historia, no se borra.
     */
    private function guardarLecciones(int $persona_id, int $discipulado): void {
        if ($persona_id && $discipulado) {
            $this->modelo->guardarAvance($persona_id, (array) ($_POST['lecciones'] ?? []));
        }
    }

    /**
     * Resuelve qué foto queda en el registro.
     * @param  string|null $anterior  Foto que ya tenía, si se está editando
     * @return array|string|null  ['nombre','anterior'] si cambia, el mensaje
     *                            de error si falla, o null si se deja igual
     */
    private function fotoDesdePost(?string $anterior): array|string|null {
        if (!empty($_POST['quitar_foto']) && $anterior) {
            return ['nombre' => null, 'anterior' => $anterior];
        }

        $file = $_FILES['foto'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return 'No se pudo subir la foto. Intenta de nuevo con una más liviana.';
        }

        $nombre = $this->subirFoto($file);
        if ($nombre === false) {
            return 'La foto tiene que ser JPG, PNG o WEBP y pesar menos de 5 MB.';
        }
        return ['nombre' => $nombre, 'anterior' => $anterior];
    }

    /**
     * Guarda la foto en uploads/evangelismo/
     * @return string|false  Nombre del archivo o false si no sirve
     */
    private function subirFoto(array $file): string|false {
        $extensiones = ['jpg' => 'jpg', 'jpeg' => 'jpg', 'png' => 'png', 'webp' => 'webp'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!isset($extensiones[$ext]) || $file['size'] > 5 * 1024 * 1024) {
            return false;
        }
        // Que sea una imagen de verdad y no otra cosa con el nombre cambiado
        if (!@getimagesize($file['tmp_name'])) {
            return false;
        }

        $dir = BASE_PATH . '/uploads/evangelismo/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        // Si la carpeta se recrea en el servidor, que no quede abierta.
        // En <FilesMatch> porque el .htaccess de uploads/ concede las
        // imágenes con uno, y esa regla gana sobre una suelta.
        if (!is_file($dir . '.htaccess')) {
            file_put_contents($dir . '.htaccess', "<FilesMatch \".*\">
    Require all denied
</FilesMatch>
");
        }

        $nombre = 'p_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extensiones[$ext];
        return move_uploaded_file($file['tmp_name'], $dir . $nombre) ? $nombre : false;
    }

    private function borrarFoto(string $nombre): void {
        $archivo = BASE_PATH . '/uploads/evangelismo/' . basename($nombre);
        if (is_file($archivo)) {
            unlink($archivo);
        }
    }

    /**
     * Arma los datos del formulario o devuelve el mensaje de error
     */
    private function datosDesdePost(): array|string {
        $tipo      = ($_POST['tipo'] ?? '') === 'contacto' ? 'contacto' : 'evangelizada';
        $nombres   = trim($_POST['nombres']   ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        if ($nombres === '') {
            return 'El nombre es obligatorio.';
        }
        // En un contacto espiritual a veces solo se sabe el nombre
        if ($tipo === 'evangelizada' && $apellidos === '') {
            return 'Nombre y apellido son obligatorios.';
        }

        $acompanamiento = array_values(array_intersect(
            array_keys(EvangelismoModel::ACOMPANAMIENTOS),
            (array) ($_POST['acompanamiento'] ?? [])
        ));
        if ($tipo === 'contacto' && !$acompanamiento) {
            return 'Marca si hubo oración, consejo o palabra de aliento.';
        }

        $edad = trim($_POST['edad'] ?? '');
        if ($edad !== '' && (!ctype_digit($edad) || (int) $edad > 120)) {
            return 'La edad no es válida.';
        }

        $fecha = $_POST['fecha_contacto'] ?? '';
        $f     = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$f || $f->format('Y-m-d') !== $fecha) {
            $fecha = date('Y-m-d');
        } elseif ($fecha > date('Y-m-d')) {
            return 'La fecha del contacto no puede ser futura.';
        }

        $sede_id = (int) ($_POST['sede_id'] ?? 0);
        if ($sede_id && !in_array($sede_id, array_column($this->modelo->sedes(), 'id'))) {
            $sede_id = 0;
        }

        // El sector tiene que ser de la sede elegida, no de otra
        $sector_id = (int) ($_POST['sector_id'] ?? 0);
        if ($sector_id && !($sede_id && $this->modelo->sectorValido($sector_id, $sede_id))) {
            $sector_id = 0;
        }

        // La etapa solo aplica a quien escuchó el evangelio. Se elige una
        // sola: sin decisión, decisión de fe o discipulado. Quien está en
        // discipulado decidió antes, así que arrastra decision_fe.
        $es_evangelizada = $tipo === 'evangelizada';
        $etapa           = $es_evangelizada ? ($_POST['etapa'] ?? 'ninguna') : 'ninguna';
        $discipulado     = $etapa === 'discipulado';
        $decision        = $discipulado || $etapa === 'decision';

        return [
            'nombres'        => mb_substr($nombres, 0, 100),
            'apellidos'      => mb_substr($apellidos, 0, 100) ?: null,
            'edad'           => $edad === '' ? null : (int) $edad,
            'telefono'       => mb_substr(trim($_POST['telefono'] ?? ''), 0, 30) ?: null,
            'direccion'      => mb_substr(trim($_POST['direccion'] ?? ''), 0, 255) ?: null,
            'sede_id'        => $sede_id ?: null,
            'sector_id'      => $sector_id ?: null,
            'fecha_contacto' => $fecha,
            'tipo'           => $tipo,
            'acompanamiento' => $es_evangelizada ? null : implode(',', $acompanamiento),
            'decision_fe'    => $decision ? 1 : 0,
            'discipulado'    => $discipulado ? 1 : 0,
            // Lo puede pedir cualquiera, haya decidido o no
            'desea_visita'   => empty($_POST['desea_visita']) ? 0 : 1,
            'responsable'    => mb_substr(trim($_POST['responsable'] ?? ''), 0, 150) ?: null,
            'notas'          => trim($_POST['notas'] ?? '') ?: null,
        ];
    }

    // Mismo esquema que el login: 5 fallos bloquean la IP 15 minutos.
    // Se reusa login_intentos con el prefijo "pin:" en la clave.

    private function bloqueo(string $clave): ?string {
        $stmt = Database::getConnection()->prepare(
            "SELECT bloqueado_hasta FROM login_intentos WHERE ip = ? AND bloqueado_hasta > NOW() LIMIT 1"
        );
        $stmt->execute([$clave]);
        return $stmt->fetchColumn()
            ? 'Demasiados intentos fallidos. Espera unos minutos e intenta de nuevo.'
            : null;
    }

    private function sumarIntento(string $clave): void {
        // El orden importa: MySQL asigna de izquierda a derecha, así que
        // bloqueado_hasta tiene que leer intentos antes de que suba.
        Database::getConnection()->prepare("
            INSERT INTO login_intentos (ip, intentos)
            VALUES (?, 1)
            ON DUPLICATE KEY UPDATE
                bloqueado_hasta = IF(intentos + 1 >= 5, DATE_ADD(NOW(), INTERVAL 15 MINUTE), NULL),
                intentos        = intentos + 1
        ")->execute([$clave]);
    }

    private function limpiarIntentos(string $clave): void {
        Database::getConnection()
            ->prepare("DELETE FROM login_intentos WHERE ip = ?")
            ->execute([$clave]);
    }
}
