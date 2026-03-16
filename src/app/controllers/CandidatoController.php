<?php
/**
 * CandidatoController
 * Dashboard y gestión de postulación del candidato
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/AspiranteModel.php';

class CandidatoController extends Controller {

    private AspiranteModel $aspirantes;

    public function __construct() {
        $this->aspirantes = new AspiranteModel();
    }

    public function dashboard(): void {
        $this->requireAuth('candidato');

        $aspirante = $this->aspirantes->porUsuario($_SESSION['usuario_id']) ?: null;
        $progreso  = $this->calcularProgreso($aspirante);

        $this->render('candidato/dashboard', [
            'titulo'    => 'Mi Postulación',
            'aspirante' => $aspirante,
            'progreso'  => $progreso,
        ]);
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

        if ($existente) {
            $stmt2 = $db->prepare("
                UPDATE test_vocacional
                SET respuestas = ?, completado = ?, fecha_cierre = IF(? = 1, NOW(), fecha_cierre)
                WHERE aspirante_id = ?
            ");
            $stmt2->execute([$json, $completado, $completado, $aspirante['id']]);
        } else {
            $stmt2 = $db->prepare("
                INSERT INTO test_vocacional (aspirante_id, respuestas, completado, fecha_inicio, fecha_cierre)
                VALUES (?, ?, ?, NOW(), IF(? = 1, NOW(), NULL))
            ");
            $stmt2->execute([$aspirante['id'], $json, $completado, $completado]);
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

    // Calcular el progreso de las 5 etapas
    private function calcularProgreso(?array $aspirante): array {
        $etapas = [
            ['clave' => 'solicitud_formal',      'nombre' => 'Solicitud Formal',       'icono' => 'fa-file-alt'],
            ['clave' => 'evaluacion_documental',  'nombre' => 'Evaluación Documental',  'icono' => 'fa-folder-open'],
            ['clave' => 'test_vocacional',         'nombre' => 'Test Vocacional',        'icono' => 'fa-clipboard-list'],
            ['clave' => 'entrevista_personal',     'nombre' => 'Entrevista Personal',    'icono' => 'fa-comments'],
            ['clave' => 'confirmacion_admision',   'nombre' => 'Confirmación',           'icono' => 'fa-check-circle'],
        ];

        // Si no hay aspirante, todo pendiente
        if (!$aspirante) {
            foreach ($etapas as &$e) $e['estatus'] = 'pendiente';
            return ['etapas' => $etapas, 'pct' => 0, 'etapa_actual' => 0];
        }

        // Primer paso aprobado si el perfil está enviado
        $etapas[0]['estatus'] = match($aspirante['estatus']) {
            'enviada', 'en_revision', 'aprobada' => 'aprobado',
            'borrador' => 'en_proceso',
            default    => 'pendiente',
        };

        for ($i = 1; $i < count($etapas); $i++) {
            $etapas[$i]['estatus'] = 'pendiente';
        }

        $aprobadas = count(array_filter($etapas, fn($e) => $e['estatus'] === 'aprobado'));
        $pct = (int)(($aprobadas / count($etapas)) * 100);

        return [
            'etapas'       => $etapas,
            'pct'          => $pct,
            'etapa_actual' => $aprobadas,
        ];
    }
}
