<?php
/**
 * PracticasController
 * Reportes semanales de las prácticas misioneras por iglesia y el
 * informe imprimible que sale de ellos.
 *
 * La semana va de lunes a domingo: el trabajo en las iglesias es de
 * viernes a domingo, así que cada fin de semana cae entero en una.
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/PracticaModel.php';

class PracticasController extends Controller {

    private PracticaModel $modelo;

    public function __construct() {
        $this->modelo = new PracticaModel();
    }

    // ── GET /admin/practicas ──────────────────────────────────
    public function index(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        [$lunes, $domingo] = $this->semana();
        $actividades = $this->modelo->entre($lunes, $domingo);

        $this->render('admin/practicas', [
            'titulo'      => 'Prácticas',
            'lunes'       => $lunes,
            'domingo'     => $domingo,
            'numero'      => $this->numeroSemana($lunes),
            'actividades' => $actividades,
            'resumen'     => $this->resumen($actividades),
            'acumulado'   => $this->modelo->acumuladoPorIglesia(),
            'iglesias'    => $this->modelo->iglesias(),
            'lecciones'   => (new EvangelismoModel())->lecciones(),
        ], 'admin');
    }

    // ── GET /admin/practicas/informe ──────────────────────────
    public function informe(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        [$lunes, $domingo] = $this->semana();
        $actividades = $this->modelo->entre($lunes, $domingo);

        $this->render('admin/practicas_informe', [
            'titulo'      => 'Informe de prácticas',
            'lunes'       => $lunes,
            'domingo'     => $domingo,
            'numero'      => $this->numeroSemana($lunes),
            'actividades' => $actividades,
            'resumen'     => $this->resumen($actividades),
        ], 'impresion');
    }

    // ── GET /admin/practicas/cierre ───────────────────────────
    // Informe de todo lo logrado en un lugar. Por defecto toma el
    // rango completo con actividades; se puede acotar con ?desde y
    // ?hasta para cerrar un lugar sin arrastrar el anterior.
    public function cierre(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $rango = $this->modelo->rango();
        if (!$rango) {
            $this->flash('error', 'Todavía no hay actividades cargadas.');
            $this->redirigir('/admin/practicas');
        }

        $desde = $this->fecha($_GET['desde'] ?? '') ?: $rango['desde'];
        $hasta = $this->fecha($_GET['hasta'] ?? '') ?: $rango['hasta'];
        if ($desde > $hasta) {
            [$desde, $hasta] = [$hasta, $desde];
        }

        $this->render('admin/practicas_cierre', [
            'titulo'       => 'Informe de cierre',
            'lugar'        => trim((string) ($_GET['lugar'] ?? '')) ?: 'Carayaca, La Guaira',
            'desde'        => $desde,
            'hasta'        => $hasta,
            'totales'      => $this->modelo->totalesEntre($desde, $hasta),
            'por_iglesia'  => $this->modelo->porIglesiaEntre($desde, $hasta),
            'alcances'     => $this->modelo->alcancesEntre($desde, $hasta),
            'organiza'     => $this->modelo->organizacionesEntre($desde, $hasta),
            'lecciones'    => $this->modelo->discipuladoPorLeccion($desde, $hasta),
            'semanas'      => $this->modelo->porSemanaEntre($desde, $hasta),
            'actividades'  => $this->modelo->entre($desde, $hasta),
        ], 'impresion');
    }

    // ── POST /admin/practicas — registrar o editar ────────────
    public function guardar(): void {
        $this->requireAnyRole(['admin', 'evaluador']);

        $datos  = $this->datosDesdePost();
        $volver = '/admin/practicas?semana=' . (is_array($datos) ? $datos['fecha'] : urlencode($_POST['fecha'] ?? ''));
        if (is_string($datos)) {
            $this->flash('error', $datos);
            $this->redirigir($volver);
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            if (!$this->modelo->porId($id)) {
                $this->flash('error', 'No se encontró la actividad.');
                $this->redirigir($volver);
            }
            $this->modelo->actualizar($id, $datos);
            $this->flash('exito', 'Actividad actualizada.');
        } else {
            $datos['registrado_por'] = (int) $_SESSION['usuario_id'];
            $this->modelo->insertar($datos);
            $this->flash('exito', 'Actividad registrada.');
        }
        $this->redirigir($volver);
    }

    // ── POST /admin/practicas/eliminar ────────────────────────
    public function eliminar(): void {
        $this->requireAuth('admin');

        $id  = (int) ($_POST['id'] ?? 0);
        $act = $id ? $this->modelo->porId($id) : false;
        $ok  = $act && $this->modelo->eliminar($id);
        $this->flash($ok ? 'exito' : 'error', $ok ? 'Actividad eliminada.' : 'No se pudo eliminar.');
        $this->redirigir('/admin/practicas' . ($act ? '?semana=' . $act['fecha'] : ''));
    }

    // ── Helpers ───────────────────────────────────────────────

    /**
     * Lunes y domingo de la semana pedida en ?semana=AAAA-MM-DD. Sin
     * fecha, la de la última actividad cargada: el informe se arma
     * días después del fin de semana, no el mismo lunes.
     */
    private function semana(): array {
        $fecha = $_GET['semana'] ?? '';
        $f     = DateTime::createFromFormat('!Y-m-d', $fecha);
        if (!$f || $f->format('Y-m-d') !== $fecha) {
            $rango = $this->modelo->rango();
            $f     = new DateTime($rango['hasta'] ?? 'today');
        }
        $f->modify('monday this week');
        $lunes = $f->format('Y-m-d');
        $f->modify('+6 days');
        return [$lunes, $f->format('Y-m-d')];
    }

    /** Semana de prácticas contando desde la primera con actividades */
    private function numeroSemana(string $lunes): ?int {
        $rango = $this->modelo->rango();
        if (!$rango) {
            return null;
        }
        $inicio = new DateTime($rango['desde']);
        $inicio->modify('monday this week');
        $dias = (int) $inicio->diff(new DateTime($lunes))->format('%r%a');
        return $dias >= 0 ? intdiv($dias, 7) + 1 : null;
    }

    /**
     * Agrupa las actividades por iglesia y suma lo de la semana.
     * El culto dominical se suma aparte porque es lo que se compara
     * de una semana a otra.
     */
    private function resumen(array $actividades): array {
        $vacio = ['adultos' => 0, 'jovenes' => 0, 'ninos' => 0, 'comunidad' => 0, 'equipo' => 0,
                  'desglose' => true, 'hubo_culto' => false,
                  'evangelizados' => 0, 'decisiones' => 0, 'discipulado' => 0];

        $por_iglesia = [];
        foreach ($this->modelo->iglesias() as $i) {
            $por_iglesia[$i['id']] = $vacio + ['nombre' => $i['nombre'], 'sector' => $i['sector'], 'actividades' => []];
        }

        foreach ($actividades as $a) {
            $r = &$por_iglesia[$a['iglesia_id']];
            $r['actividades'][] = $a;
            foreach (['evangelizados', 'decisiones', 'discipulado'] as $c) {
                $r[$c] += (int) $a[$c];
            }
            if ($a['tipo'] === 'culto') {
                $r['hubo_culto'] = true;
                $r['comunidad'] += (int) $a['comunidad'];
                $r['equipo']    += (int) $a['equipo'];
                if ($a['adultos'] === null) {
                    $r['desglose'] = false;
                }
                foreach (['adultos', 'jovenes', 'ninos'] as $c) {
                    $r[$c] += (int) $a[$c];
                }
            }
            unset($r);
        }

        $total = $vacio;
        foreach ($por_iglesia as $r) {
            foreach (['adultos', 'jovenes', 'ninos', 'comunidad', 'equipo', 'evangelizados', 'decisiones', 'discipulado'] as $c) {
                $total[$c] += $r[$c];
            }
            if ($r['hubo_culto'] && !$r['desglose']) {
                $total['desglose'] = false;
            }
        }

        return ['iglesias' => $por_iglesia, 'total' => $total];
    }

    /**
     * Arma los datos del formulario o devuelve el mensaje de error
     */
    /** Una fecha de la URL, o null si no sirve */
    private function fecha(string $f): ?string {
        $d = DateTime::createFromFormat('!Y-m-d', $f);
        return $d && $d->format('Y-m-d') === $f ? $f : null;
    }

    private function datosDesdePost(): array|string {
        $iglesia_id = (int) ($_POST['iglesia_id'] ?? 0);
        if (!in_array($iglesia_id, array_map('intval', array_column($this->modelo->iglesias(), 'id')), true)) {
            return 'Elige la iglesia.';
        }

        $tipo = $_POST['tipo'] ?? '';
        if (!isset(PracticaModel::TIPOS[$tipo])) {
            return 'Elige el tipo de actividad.';
        }

        $fecha = $_POST['fecha'] ?? '';
        $f     = DateTime::createFromFormat('!Y-m-d', $fecha);
        if (!$f || $f->format('Y-m-d') !== $fecha) {
            return 'La fecha no es válida.';
        }
        if ($fecha > date('Y-m-d')) {
            return 'La fecha no puede ser futura.';
        }

        $numeros = [];
        foreach (['adultos', 'jovenes', 'ninos', 'comunidad', 'equipo', 'evangelizados', 'decisiones', 'discipulado'] as $c) {
            $v = trim($_POST[$c] ?? '');
            if ($v !== '' && (!ctype_digit($v) || (int) $v > 5000)) {
                return 'Revisa los números: solo enteros, sin letras.';
            }
            $numeros[$c] = $v === '' ? null : (int) $v;
        }

        // Si se tomó el desglose, la comunidad es su suma; si no, se
        // escribe el total directo (actividades donde solo se contó gente)
        $desglose = $numeros['adultos'] !== null || $numeros['jovenes'] !== null || $numeros['ninos'] !== null;
        if ($desglose) {
            foreach (['adultos', 'jovenes', 'ninos'] as $c) {
                $numeros[$c] ??= 0;
            }
            $numeros['comunidad'] = $numeros['adultos'] + $numeros['jovenes'] + $numeros['ninos'];
        }

        // A quién fue dirigida solo aplica a las reuniones por organización
        $dirigida = [];
        if ($tipo === 'organizacion') {
            $dirigida = array_values(array_intersect(
                array_keys(PracticaModel::ORGANIZACIONES),
                (array) ($_POST['dirigida_a'] ?? [])
            ));
        }

        $leccion_id = (int) ($_POST['leccion_id'] ?? 0);
        if ($leccion_id && !in_array($leccion_id, array_map('intval', array_column((new EvangelismoModel())->lecciones(), 'id')), true)) {
            $leccion_id = 0;
        }

        return [
            'iglesia_id'    => $iglesia_id,
            'fecha'         => $fecha,
            'tipo'          => $tipo,
            'descripcion'   => mb_substr(trim($_POST['descripcion'] ?? ''), 0, 255) ?: null,
            'dirigida_a'    => $dirigida ? implode(',', $dirigida) : null,
            'adultos'       => $numeros['adultos'],
            'jovenes'       => $numeros['jovenes'],
            'ninos'         => $numeros['ninos'],
            'comunidad'     => $numeros['comunidad'] ?? 0,
            'equipo'        => $numeros['equipo'] ?? 0,
            'evangelizados' => $numeros['evangelizados'] ?? 0,
            'decisiones'    => $numeros['decisiones'] ?? 0,
            'discipulado'   => $numeros['discipulado'] ?? 0,
            'leccion_id'    => $leccion_id ?: null,
        ];
    }
}
