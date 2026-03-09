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
        $this->render('candidato/test', ['titulo' => 'Test Vocacional']);
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
