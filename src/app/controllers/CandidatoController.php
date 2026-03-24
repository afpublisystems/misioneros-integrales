<?php
/**
 * CandidatoController
 * Dashboard y gestión de postulación del candidato
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/AspiranteModel.php';
require_once APP_PATH . '/helpers/TestScorer.php';

class CandidatoController extends Controller {

    private AspiranteModel $aspirantes;

    public function __construct() {
        $this->aspirantes = new AspiranteModel();
    }

    public function dashboard(): void {
        $this->requireAuth('candidato');

        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']) ?: null;

        // Cargar flujo_proceso real del aspirante
        $flujo = [];
        if ($aspirante) {
            $db   = Database::getConnection();
            $stmt = $db->prepare(
                "SELECT etapa, estatus, fecha_inicio, fecha_cierre
                 FROM flujo_proceso WHERE aspirante_id = ?"
            );
            $stmt->execute([$aspirante['id']]);
            foreach ($stmt->fetchAll() as $row) {
                $flujo[$row['etapa']] = $row;
            }
        }

        $progreso   = $this->calcularProgreso($aspirante, $flujo);
        $proximo    = $this->proximoPaso($aspirante, $flujo);
        $requisitos = $this->validarRequisitos($aspirante);

        $this->render('candidato/dashboard', [
            'titulo'      => 'Mi Postulación',
            'aspirante'   => $aspirante,
            'progreso'    => $progreso,
            'flujo'       => $flujo,
            'proximo'     => $proximo,
            'requisitos'  => $requisitos,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // ENVIAR POSTULACIÓN
    // ─────────────────────────────────────────────────────────────────
    public function enviarPostulacion(): void {
        $this->requireAuth('candidato');

        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']);
        if (!$aspirante || $aspirante['estatus'] !== 'borrador') {
            $this->redirigir('/candidato/dashboard');
            return;
        }

        $req = $this->validarRequisitos($aspirante);
        if (!$req['lista']) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Completa todos los requisitos antes de enviar tu postulación.'];
            $this->redirigir('/candidato/dashboard');
            return;
        }

        $this->aspirantes->actualizar($aspirante['id'], ['estatus' => 'enviada']);

        // Registrar en flujo_proceso
        $db = Database::getConnection();
        $db->prepare("
            INSERT INTO flujo_proceso (aspirante_id, etapa, estatus, fecha_inicio)
            VALUES (?, 'solicitud_formal', 'aprobado', NOW())
            ON DUPLICATE KEY UPDATE
                estatus      = 'aprobado',
                fecha_cierre = NOW(),
                updated_at   = NOW()
        ")->execute([$aspirante['id']]);

        $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => '¡Postulación enviada! El equipo coordinador revisará tu solicitud y se comunicará contigo.'];
        $this->redirigir('/candidato/dashboard');
    }

    public function perfil(): void {
        $this->requireAuth('candidato');
        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']);
        $this->render('candidato/perfil', [
            'titulo'    => 'Mi Perfil',
            'aspirante' => $aspirante ?: [],
        ]);
    }

    public function guardarPerfil(): void {
        $this->requireAuth('candidato');
        $this->aspirantes->guardarPerfil($_SESSION['usuario_id'], $_POST);
        $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => 'Perfil guardado correctamente.'];
        $this->redirigir('/candidato/perfil');
    }

    public function documentos(): void {
        $this->requireAuth('candidato');

        $aspirante  = $this->aspirantes->porUsuario($_SESSION['usuario_id']);
        $documentos = [];

        if ($aspirante) {
            require_once APP_PATH . '/models/DocumentoModel.php';
            $docModel   = new DocumentoModel();
            $documentos = $docModel->porAspirante($aspirante['id']);
        }

        $this->render('candidato/documentos', [
            'titulo'      => 'Mis Documentos',
            'aspirante'   => $aspirante ?: [],
            'documentos'  => $documentos,
        ]);
    }

    public function subirDocumento(): void {
        $this->requireAuth('candidato');

        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']);
        if (!$aspirante) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Debes completar tu perfil antes de subir documentos.'];
            $this->redirigir('/candidato/perfil');
        }

        // Validar que llegó un archivo
        if (empty($_FILES['archivo']['tmp_name'])) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'No se recibió ningún archivo.'];
            $this->redirigir('/candidato/documentos');
        }

        $tipos_validos = ['cedula','titulo_bachiller','carta_pastoral','foto_reciente','otros'];
        $tipo          = $_POST['tipo'] ?? '';
        if (!in_array($tipo, $tipos_validos)) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Tipo de documento no válido.'];
            $this->redirigir('/candidato/documentos');
        }

        $archivo = $_FILES['archivo'];

        // Validar extensión
        $ext_validas = ['pdf','jpg','jpeg','png'];
        $ext         = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $ext_validas)) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Formato no permitido. Usa PDF, JPG o PNG.'];
            $this->redirigir('/candidato/documentos');
        }

        // Validar tamaño (5 MB)
        if ($archivo['size'] > 5 * 1024 * 1024) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'El archivo supera los 5 MB permitidos.'];
            $this->redirigir('/candidato/documentos');
        }

        // Nombre seguro único
        $nombre_archivo = $aspirante['id'] . '_' . $tipo . '_' . time() . '.' . $ext;
        $destino        = BASE_PATH . '/public/uploads/documentos/' . $nombre_archivo;

        if (!move_uploaded_file($archivo['tmp_name'], $destino)) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Error al guardar el archivo. Intenta nuevamente.'];
            $this->redirigir('/candidato/documentos');
        }

        // Guardar en BD
        require_once APP_PATH . '/models/DocumentoModel.php';
        $docModel = new DocumentoModel();
        $docModel->guardar([
            'aspirante_id'    => $aspirante['id'],
            'tipo'            => $tipo,
            'archivo'         => $nombre_archivo,
            'nombre_original' => $archivo['name'],
            'tamano'          => $archivo['size'],
            'mime'            => $archivo['type'],
        ]);

        $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => 'Documento subido correctamente.'];
        $this->redirigir('/candidato/documentos');
    }

    public function test(): void {
        $this->requireAuth('candidato');

        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']);
        if (!$aspirante) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Debes completar tu perfil antes de acceder al test vocacional.'];
            $this->redirigir('/candidato/perfil');
            return;
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM test_vocacional WHERE aspirante_id = ?");
        $stmt->execute([$aspirante['id']]);
        $test = $stmt->fetch() ?: null;

        $respuestas = [];
        if ($test && !empty($test['respuestas'])) {
            $respuestas = json_decode($test['respuestas'], true) ?: [];
        }

        // Recuperar la parte donde se quedó (desde sesión temporal)
        $parte_inicial = 1;
        if (isset($_SESSION['test_parte_actual'])) {
            $parte_inicial = (int)$_SESSION['test_parte_actual'];
            unset($_SESSION['test_parte_actual']);
        }

        $this->render('candidato/test', [
            'titulo'        => 'Test Vocacional',
            'aspirante'     => $aspirante,
            'test'          => $test,
            'respuestas'    => $respuestas,
            'parte_inicial' => $parte_inicial,
        ]);
    }

    public function guardarTest(): void {
        $this->requireAuth('candidato');

        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']);
        if (!$aspirante) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => 'Debes completar tu perfil primero.'];
            $this->redirigir('/candidato/perfil');
            return;
        }

        $db = Database::getConnection();

        // Verificar si ya existe y si está completado
        $stmt = $db->prepare("SELECT id, completado FROM test_vocacional WHERE aspirante_id = ?");
        $stmt->execute([$aspirante['id']]);
        $existente = $stmt->fetch();

        if ($existente && $existente['completado']) {
            $_SESSION['flash'] = ['tipo' => 'info', 'msg' => 'Tu test ya fue enviado. Contacta al equipo coordinador si necesitas realizar cambios.'];
            $this->redirigir('/candidato/test');
            return;
        }

        $completado = isset($_POST['completado']) && $_POST['completado'] == '1' ? 1 : 0;
        $respuestas = $_POST['respuestas'] ?? [];
        $json       = json_encode($respuestas, JSON_UNESCAPED_UNICODE);

        // Calcular puntaje orientacional si se está completando
        $puntaje_total = null;
        if ($completado) {
            $perfil = TestScorer::calcular($respuestas);
            $puntaje_total = $perfil['puntaje_total'];
        }

        if ($existente) {
            $stmt2 = $db->prepare("
                UPDATE test_vocacional
                SET respuestas = ?, completado = ?,
                    puntaje_total = COALESCE(?, puntaje_total),
                    fecha_cierre = IF(? = 1, NOW(), fecha_cierre)
                WHERE aspirante_id = ?
            ");
            $stmt2->execute([$json, $completado, $puntaje_total, $completado, $aspirante['id']]);
        } else {
            $stmt2 = $db->prepare("
                INSERT INTO test_vocacional (aspirante_id, respuestas, completado, puntaje_total, fecha_inicio, fecha_cierre)
                VALUES (?, ?, ?, ?, NOW(), IF(? = 1, NOW(), NULL))
            ");
            $stmt2->execute([$aspirante['id'], $json, $completado, $puntaje_total, $completado]);
        }

        // Guardar parte actual para volver al mismo lugar
        $_SESSION['test_parte_actual'] = (int)($_POST['parte_actual'] ?? 1);

        if ($completado) {
            // Fase 7b: marcar etapa test_vocacional → en_proceso en flujo_proceso
            $db->prepare("
                INSERT INTO flujo_proceso (aspirante_id, etapa, estatus, fecha_inicio)
                VALUES (?, 'test_vocacional', 'en_proceso', NOW())
                ON DUPLICATE KEY UPDATE
                    estatus      = IF(estatus = 'pendiente', 'en_proceso', estatus),
                    fecha_inicio = COALESCE(fecha_inicio, NOW()),
                    updated_at   = NOW()
            ")->execute([$aspirante['id']]);

            // Fase 7b: marcar solicitud_formal → aprobado si estaba pendiente
            $db->prepare("
                INSERT INTO flujo_proceso (aspirante_id, etapa, estatus, fecha_inicio, fecha_cierre)
                VALUES (?, 'solicitud_formal', 'aprobado', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    estatus      = IF(estatus = 'pendiente', 'aprobado', estatus),
                    fecha_inicio = COALESCE(fecha_inicio, NOW()),
                    fecha_cierre = IF(estatus = 'pendiente', NOW(), fecha_cierre),
                    updated_at   = NOW()
            ")->execute([$aspirante['id']]);

            $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => '¡Test vocacional enviado exitosamente! El equipo coordinador lo revisará pronto.'];
            $_SESSION['test_parte_actual'] = 1;
        } else {
            $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => 'Progreso guardado. Puedes continuar en otro momento desde donde lo dejaste.'];
        }

        $this->redirigir('/candidato/test');
    }

    // Verifica si el candidato cumple los requisitos mínimos para enviar la postulación
    private function validarRequisitos(?array $aspirante): array {
        $checks = [];

        if (!$aspirante) {
            return ['lista' => false, 'checks' => [], 'faltantes' => 1];
        }

        // Campos obligatorios del perfil
        $campos = [
            'nombres'   => 'Nombre completo',
            'apellidos' => 'Apellidos',
            'cedula'    => 'Cédula de identidad',
            'telefono'  => 'Teléfono',
            'iglesia'   => 'Iglesia local',
            'pastor'    => 'Nombre del pastor',
        ];
        foreach ($campos as $campo => $label) {
            $ok = !empty(trim($aspirante[$campo] ?? ''));
            $checks[] = ['label' => $label, 'ok' => $ok];
        }

        // Documentos obligatorios
        $tipos_req = ['cedula', 'titulo_bachiller', 'carta_pastoral', 'foto_reciente'];
        $labels_doc = [
            'cedula'          => 'Cédula (documento)',
            'titulo_bachiller'=> 'Título de Bachiller',
            'carta_pastoral'  => 'Carta Pastoral',
            'foto_reciente'   => 'Foto Reciente',
        ];

        $db   = Database::getConnection();
        $stmt = $db->prepare(
            "SELECT tipo FROM documentos WHERE aspirante_id = ? AND tipo IN ('cedula','titulo_bachiller','carta_pastoral','foto_reciente')"
        );
        $stmt->execute([$aspirante['id']]);
        $docs_subidos = array_column($stmt->fetchAll(), 'tipo');

        foreach ($tipos_req as $tipo) {
            $checks[] = [
                'label' => $labels_doc[$tipo],
                'ok'    => in_array($tipo, $docs_subidos),
            ];
        }

        $faltantes = count(array_filter($checks, fn($c) => !$c['ok']));

        return [
            'lista'     => $faltantes === 0,
            'checks'    => $checks,
            'faltantes' => $faltantes,
        ];
    }

    // Calcular el progreso de las 5 etapas usando flujo_proceso real
    private function calcularProgreso(?array $aspirante, array $flujo = []): array {
        $etapas = [
            ['clave' => 'solicitud_formal',      'nombre' => 'Solicitud Formal',      'icono' => 'fa-file-alt'],
            ['clave' => 'evaluacion_documental',  'nombre' => 'Ev. Documental',        'icono' => 'fa-folder-open'],
            ['clave' => 'test_vocacional',         'nombre' => 'Test Vocacional',       'icono' => 'fa-clipboard-list'],
            ['clave' => 'entrevista_personal',     'nombre' => 'Entrevista Personal',   'icono' => 'fa-comments'],
            ['clave' => 'confirmacion_admision',   'nombre' => 'Confirmación',          'icono' => 'fa-check-circle'],
        ];

        if (!$aspirante) {
            foreach ($etapas as &$e) $e['estatus'] = 'pendiente';
            unset($e);
            return ['etapas' => $etapas, 'pct' => 0, 'etapa_actual' => 0];
        }

        foreach ($etapas as &$e) {
            if (isset($flujo[$e['clave']])) {
                $e['estatus'] = $flujo[$e['clave']]['estatus'];
            } elseif ($e['clave'] === 'solicitud_formal') {
                // Inferir desde estatus del aspirante si no hay registro en flujo
                $e['estatus'] = match($aspirante['estatus']) {
                    'enviada', 'en_revision', 'aprobada' => 'aprobado',
                    'borrador' => 'en_proceso',
                    default    => 'pendiente',
                };
            } else {
                $e['estatus'] = 'pendiente';
            }
        }
        unset($e);

        $aprobadas = count(array_filter($etapas, fn($e) => $e['estatus'] === 'aprobado'));
        $pct       = (int)(($aprobadas / count($etapas)) * 100);

        return [
            'etapas'       => $etapas,
            'pct'          => $pct,
            'etapa_actual' => $aprobadas,
        ];
    }

    // Determinar el próximo paso contextual del candidato
    private function proximoPaso(?array $aspirante, array $flujo): array {
        if (!$aspirante) {
            return [
                'icono'  => 'fa-user-edit',
                'titulo' => 'Completa tu perfil',
                'texto'  => 'Para iniciar tu postulación, llena tus datos personales, eclesiales y académicos.',
                'accion' => ['url' => '/candidato/perfil', 'texto' => 'Ir a mi perfil'],
                'tipo'   => 'accion',
            ];
        }

        // Etapa rechazada → notificar
        foreach ($flujo as $datos) {
            if ($datos['estatus'] === 'rechazado') {
                return [
                    'icono'  => 'fa-times-circle',
                    'titulo' => 'Postulación no aprobada en esta etapa',
                    'texto'  => 'Tu postulación no pudo continuar. Si tienes preguntas, contáctanos.',
                    'accion' => ['url' => '/contacto', 'texto' => 'Contactar al equipo'],
                    'tipo'   => 'error',
                ];
            }
        }

        $s = fn(string $etapa) => $flujo[$etapa]['estatus'] ?? 'pendiente';

        // Admitido
        if ($s('confirmacion_admision') === 'aprobado') {
            return [
                'icono'  => 'fa-star',
                'titulo' => '¡Fuiste admitido/a al programa!',
                'texto'  => 'El equipo coordinador se pondrá en contacto para los detalles de inicio.',
                'accion' => null,
                'tipo'   => 'exito',
            ];
        }

        // Confirmación en proceso
        if ($s('confirmacion_admision') === 'en_proceso') {
            return [
                'icono'  => 'fa-hourglass-half',
                'titulo' => 'Evaluación final en proceso',
                'texto'  => 'El equipo está realizando la evaluación final de tu postulación. Te contactaremos pronto.',
                'accion' => null,
                'tipo'   => 'espera',
            ];
        }

        // Entrevista activa
        if (in_array($s('entrevista_personal'), ['en_proceso', 'aprobado'])) {
            return [
                'icono'  => 'fa-comments',
                'titulo' => 'Entrevista personal coordinada',
                'texto'  => 'El equipo evaluador se comunicará contigo para la fecha y modalidad de la entrevista.',
                'accion' => null,
                'tipo'   => 'espera',
            ];
        }

        // Test enviado (en_proceso)
        if ($s('test_vocacional') === 'en_proceso') {
            return [
                'icono'  => 'fa-clipboard-check',
                'titulo' => 'Test vocacional recibido',
                'texto'  => 'Tu test fue enviado. El equipo lo revisará y coordinará la siguiente etapa. Tiempo estimado: 5-10 días hábiles.',
                'accion' => ['url' => '/candidato/resultado-test', 'texto' => 'Ver mi perfil vocacional'],
                'tipo'   => 'espera',
            ];
        }

        // Evaluación documental aprobada → hacer test
        if ($s('evaluacion_documental') === 'aprobado') {
            return [
                'icono'  => 'fa-clipboard-list',
                'titulo' => 'Realiza el test vocacional',
                'texto'  => 'Tus documentos fueron aprobados. El siguiente paso es completar el test vocacional.',
                'accion' => ['url' => '/candidato/test', 'texto' => 'Ir al test'],
                'tipo'   => 'accion',
            ];
        }

        // Documentos en revisión
        if ($s('evaluacion_documental') === 'en_proceso') {
            return [
                'icono'  => 'fa-folder-open',
                'titulo' => 'Documentos en revisión',
                'texto'  => 'El equipo está revisando tus documentos. Tiempo estimado: 5-7 días hábiles.',
                'accion' => ['url' => '/candidato/documentos', 'texto' => 'Ver mis documentos'],
                'tipo'   => 'espera',
            ];
        }

        // Solicitud aprobada → subir documentos
        if ($s('solicitud_formal') === 'aprobado') {
            return [
                'icono'  => 'fa-folder-open',
                'titulo' => 'Sube tus documentos requeridos',
                'texto'  => 'Tu solicitud fue recibida. Ahora debes subir los documentos para continuar el proceso.',
                'accion' => ['url' => '/candidato/documentos', 'texto' => 'Subir documentos'],
                'tipo'   => 'accion',
            ];
        }

        // Perfil en borrador
        if ($aspirante['estatus'] === 'borrador') {
            return [
                'icono'  => 'fa-user-edit',
                'titulo' => 'Completa y envía tu postulación',
                'texto'  => 'Llena todos tus datos y envía tu postulación para iniciar el proceso de selección.',
                'accion' => ['url' => '/candidato/perfil', 'texto' => 'Completar perfil'],
                'tipo'   => 'accion',
            ];
        }

        // Enviada, en espera general
        return [
            'icono'  => 'fa-hourglass-half',
            'titulo' => 'Postulación enviada',
            'texto'  => 'Tu solicitud está siendo revisada por el equipo coordinador. Te contactaremos pronto.',
            'accion' => null,
            'tipo'   => 'espera',
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // CAMBIAR CONTRASEÑA (desde perfil del candidato)
    // ─────────────────────────────────────────────────────────────────
    public function cambiarPassword(): void {
        $this->requireAuth('candidato');

        $id       = (int) $_SESSION['usuario_id'];
        $actual   = $_POST['password_actual']   ?? '';
        $nueva    = $_POST['password_nueva']    ?? '';
        $confirma = $_POST['password_confirma'] ?? '';
        $errores  = [];

        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($actual, $hash))
            $errores[] = 'La contraseña actual no es correcta.';
        if (strlen($nueva) < 8)
            $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        if ($nueva !== $confirma)
            $errores[] = 'La confirmación no coincide.';

        if ($errores) {
            $_SESSION['flash'] = ['tipo' => 'error', 'msg' => implode(' ', $errores)];
        } else {
            require_once APP_PATH . '/models/UsuarioModel.php';
            (new UsuarioModel())->cambiarPassword($id, $nueva);
            $_SESSION['flash'] = ['tipo' => 'exito', 'msg' => 'Contraseña actualizada correctamente.'];
        }

        $this->redirigir('/candidato/perfil?tab=seguridad');
    }

    // ─────────────────────────────────────────────────────────────────
    // RESULTADO DEL TEST VOCACIONAL (orientacional, no definitorio)
    // ─────────────────────────────────────────────────────────────────
    public function resultadoTest(): void {
        $this->requireAuth('candidato');

        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']);
        if (!$aspirante) {
            $this->redirigir('/candidato/dashboard');
            return;
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM test_vocacional WHERE aspirante_id = ? AND completado = 1");
        $stmt->execute([$aspirante['id']]);
        $test = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$test) {
            $_SESSION['flash'] = ['tipo' => 'info', 'msg' => 'Debes completar y enviar el test para ver tu perfil vocacional.'];
            $this->redirigir('/candidato/test');
            return;
        }

        $respuestas = json_decode($test['respuestas'], true) ?? [];
        $perfil     = TestScorer::calcular($respuestas);

        $this->render('candidato/resultado_test', [
            'titulo'     => 'Mi Perfil Vocacional',
            'aspirante'  => $aspirante,
            'test'       => $test,
            'respuestas' => $respuestas,
            'perfil'     => $perfil,
        ]);
    }
}
