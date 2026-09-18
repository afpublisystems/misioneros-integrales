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
            'sede_id' => $_GET['sede_id'] ?? '',
            'etapa'   => $_GET['etapa']   ?? '',
            'q'       => trim($_GET['q']  ?? ''),
        ];

        $this->render('admin/evangelismo', [
            'titulo'      => 'Evangelismo',
            'filtros'     => $filtros,
            'personas'    => $this->modelo->listar($filtros),
            'totales'     => $this->modelo->totales(),
            'por_sede'    => $this->modelo->totalesPorSede(),
            'sedes'       => $this->modelo->sedes(),
            'sede_actual' => $this->modelo->sedeEnFecha(date('Y-m-d')),
            'pin_activo'  => $this->modelo->hashPin() !== null,
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

        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            if (!$this->modelo->porId($id)) {
                $this->flash('error', 'No se encontró el registro.');
                $this->redirigir($volver);
            }
            $this->modelo->actualizar($id, $datos);
            $this->flash('exito', 'Registro actualizado.');
        } else {
            $datos['registrado_via'] = 'admin';
            $datos['registrado_por'] = (int) $_SESSION['usuario_id'];
            $this->modelo->registrar($datos);
            $this->flash('exito', 'Persona registrada.');
        }

        $this->redirigir($volver);
    }

    // ── POST /admin/evangelismo/eliminar ──────────────────────
    public function eliminar(): void {
        $this->requireAuth('admin');

        $id = (int) ($_POST['id'] ?? 0);
        $ok = $id && $this->modelo->porId($id) && $this->modelo->eliminar($id);
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
            'sede_actual' => $this->modelo->sedeEnFecha(date('Y-m-d')),
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

        // Si no escribió otro responsable, cuenta quien entró con el PIN
        $datos['responsable']    = $datos['responsable'] ?? $_SESSION['evangelismo']['responsable'];
        $datos['registrado_via'] = 'pin';
        $this->modelo->registrar($datos);

        $this->flash('exito', 'Listo, quedó registrado ' . $datos['nombres'] . ' ' . $datos['apellidos'] . '.');
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
     * Arma los datos del formulario o devuelve el mensaje de error
     */
    private function datosDesdePost(): array|string {
        $nombres   = trim($_POST['nombres']   ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        if ($nombres === '' || $apellidos === '') {
            return 'Nombre y apellido son obligatorios.';
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

        $discipulado = !empty($_POST['discipulado']);

        return [
            'nombres'        => mb_substr($nombres, 0, 100),
            'apellidos'      => mb_substr($apellidos, 0, 100),
            'edad'           => $edad === '' ? null : (int) $edad,
            'telefono'       => mb_substr(trim($_POST['telefono'] ?? ''), 0, 30) ?: null,
            'direccion'      => mb_substr(trim($_POST['direccion'] ?? ''), 0, 255) ?: null,
            'sede_id'        => $sede_id ?: null,
            'fecha_contacto' => $fecha,
            // Nadie llega a discipulado sin haber decidido antes
            'decision_fe'    => ($discipulado || !empty($_POST['decision_fe'])) ? 1 : 0,
            'discipulado'    => $discipulado ? 1 : 0,
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
