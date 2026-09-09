<?php
/**
 * AdminController
 * Panel de administración CNBV/DIME
 * Roles permitidos: admin, evaluador
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/AspiranteModel.php';
require_once APP_PATH . '/models/UsuarioModel.php';
require_once APP_PATH . '/models/PagoModel.php';
require_once APP_PATH . '/models/GastoModel.php';
require_once APP_PATH . '/models/IngresoModel.php';
require_once APP_PATH . '/models/PrestamoModel.php';
require_once APP_PATH . '/models/FinanzasModel.php';
require_once APP_PATH . '/helpers/TestScorer.php';

class AdminController extends Controller {

    private AspiranteModel $aspirantes;

    public function __construct() {
        $this->aspirantes = new AspiranteModel();
    }

    // ── GET /admin ────────────────────────────────────────────
    public function dashboard(): void {
        $this->requireAdminOEvaluador();

        $db = Database::getConnection();

        // KPIs generales
        $kpis = [
            'total'       => $this->contarPorEstatus(''),
            'borrador'    => $this->contarPorEstatus('borrador'),
            'enviada'     => $this->contarPorEstatus('enviada'),
            'en_revision' => $this->contarPorEstatus('en_revision'),
            'aprobada'    => $this->contarPorEstatus('aprobada'),
            'rechazada'   => $this->contarPorEstatus('rechazada'),
        ];

        // Últimos 5 aspirantes registrados
        $stmt = $db->prepare("
            SELECT a.id, a.nombres, a.apellidos, a.ciudad_origen, a.estado_origen,
                   a.iglesia, a.estatus, a.created_at, u.email
            FROM aspirantes a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $recientes = $stmt->fetchAll();

        // Distribución por estado
        $stmt2 = $db->prepare("
            SELECT estado_origen, COUNT(*) AS total
            FROM aspirantes
            GROUP BY estado_origen
            ORDER BY total DESC
            LIMIT 8
        ");
        $stmt2->execute();
        $por_estado = $stmt2->fetchAll();

        $this->render('admin/dashboard', [
            'titulo'     => 'Panel de Administración',
            'kpis'       => $kpis,
            'recientes'  => $recientes,
            'por_estado' => $por_estado,
        ], 'admin');
    }

    // ── GET /admin/candidatos ─────────────────────────────────
    public function candidatos(): void {
        $this->requireAdminOEvaluador();

        // Si viene ?ver=ID delegamos al detalle
        if (isset($_GET['ver'])) {
            // Si además viene ?test=1, mostramos el test vocacional
            if (isset($_GET['test'])) {
                $this->verTest();
            } elseif (isset($_GET['ficha'])) {
                $this->fichaCandidato();
            } else {
                $this->verCandidato();
            }
            return;
        }

        // Si viene ?exportar=1, generar CSV
        if (isset($_GET['exportar'])) {
            $this->exportarCSV();
            return;
        }

        // Si viene ?listado=1, mostrar listado imprimible
        if (isset($_GET['listado'])) {
            $this->listadoCandidatos();
            return;
        }

        $filtro_estatus = $_GET['estatus'] ?? '';
        $busqueda       = trim($_GET['q'] ?? '');
        $db             = Database::getConnection();

        $sql    = "SELECT a.*, u.email FROM aspirantes a LEFT JOIN usuarios u ON u.id = a.usuario_id WHERE 1=1";
        $params = [];

        if ($filtro_estatus) {
            $sql .= " AND a.estatus = :estatus";
            $params[':estatus'] = $filtro_estatus;
        }
        if ($busqueda) {
            $sql .= " AND (a.nombres LIKE :q1 OR a.apellidos LIKE :q2 OR a.cedula LIKE :q3 OR a.iglesia LIKE :q4)";
            $like = "%{$busqueda}%";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }

        $sql .= " ORDER BY a.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $lista = $stmt->fetchAll();

        $this->render('admin/candidatos', [
            'titulo'          => 'Gestión de Candidatos',
            'lista'           => $lista,
            'filtro_estatus'  => $filtro_estatus,
            'busqueda'        => $busqueda,
            'totales'         => [
                'todos'       => $this->contarPorEstatus(''),
                'enviada'     => $this->contarPorEstatus('enviada'),
                'en_revision' => $this->contarPorEstatus('en_revision'),
                'aprobada'    => $this->contarPorEstatus('aprobada'),
                'rechazada'   => $this->contarPorEstatus('rechazada'),
            ],
        ], 'admin');
    }

    // ── GET /admin/candidatos?exportar=1 — descargar CSV ─────────
    public function exportarCSV(): void {
        $this->requireAdminOEvaluador();

        $db     = Database::getConnection();
        $filtro = $_GET['estatus'] ?? '';
        $sql    = "
            SELECT
                a.id,
                a.nombres,
                a.apellidos,
                a.cedula,
                u.email,
                a.telefono,
                a.genero,
                a.edad,
                a.estado_civil,
                a.ciudad_origen,
                a.estado_origen,
                a.iglesia,
                a.pastor,
                a.anos_bautizado,
                a.nivel_academico,
                a.estatus,
                a.created_at
            FROM aspirantes a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
        ";
        $params = [];
        if ($filtro) {
            $sql .= " WHERE a.estatus = :estatus";
            $params[':estatus'] = $filtro;
        }
        $sql .= " ORDER BY a.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $nombre_archivo = 'candidatos_' . date('Y-m-d') . ($filtro ? "_{$filtro}" : '') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $nombre_archivo . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        // BOM para que Excel abra con tildes correctamente
        echo "\xEF\xBB\xBF";

        $out = fopen('php://output', 'w');

        // Encabezados
        fputcsv($out, [
            'ID', 'Nombres', 'Apellidos', 'Cédula', 'Email',
            'Teléfono', 'Género', 'Edad', 'Estado Civil',
            'Ciudad', 'Estado', 'Iglesia', 'Pastor',
            'Años bautizado', 'Nivel académico', 'Estatus', 'Fecha registro',
        ], ';');

        foreach ($filas as $f) {
            fputcsv($out, [
                $f['id'],
                $f['nombres'],
                $f['apellidos'],
                $f['cedula'],
                $f['email'],
                $f['telefono'],
                $f['genero'],
                $f['edad'],
                $f['estado_civil'],
                $f['ciudad_origen'],
                $f['estado_origen'],
                $f['iglesia'],
                $f['pastor'],
                $f['anos_bautizado'],
                $f['nivel_academico'],
                ucfirst(str_replace('_', ' ', $f['estatus'])),
                $f['created_at'],
            ], ';');
        }

        fclose($out);
        exit;
    }

    // ── GET /admin/candidatos?listado=1 — listado imprimible ─────
    public function listadoCandidatos(): void {
        $this->requireAdminOEvaluador();

        $db             = Database::getConnection();
        $filtro_estatus = $_GET['estatus'] ?? '';
        $busqueda       = trim($_GET['q'] ?? '');

        $sql    = "SELECT a.*, u.email,
                   (SELECT d.ruta FROM documentos d
                    WHERE d.aspirante_id = a.id
                      AND d.tipo = 'foto_personal'
                      AND d.mime_type LIKE 'image/%'
                    ORDER BY d.id DESC LIMIT 1) AS foto_ruta
                   FROM aspirantes a
                   LEFT JOIN usuarios u ON u.id = a.usuario_id
                   WHERE 1=1";
        $params = [];

        if ($filtro_estatus) {
            $sql .= " AND a.estatus = :estatus";
            $params[':estatus'] = $filtro_estatus;
        }
        if ($busqueda) {
            $sql .= " AND (a.nombres LIKE :q1 OR a.apellidos LIKE :q2 OR a.cedula LIKE :q3 OR a.iglesia LIKE :q4)";
            $like = "%{$busqueda}%";
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
            $params[':q4'] = $like;
        }
        $sql .= " ORDER BY a.apellidos, a.nombres";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $lista = $stmt->fetchAll();

        $this->renderParcial('admin/listado_candidatos', [
            'lista'          => $lista,
            'filtro_estatus' => $filtro_estatus,
            'busqueda'       => $busqueda,
        ]);
    }

    // ── GET /admin/candidatos?ver=ID — ver detalle ───────────────
    public function verCandidato(): void {
        $this->requireAdminOEvaluador();

        $id = (int) ($_GET['ver'] ?? 0);
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT a.*, u.email, u.ultimo_acceso
            FROM aspirantes a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $aspirante = $stmt->fetch();

        if (!$aspirante) {
            $this->redirigir('/admin/candidatos');
        }

        // Documentos
        require_once APP_PATH . '/models/DocumentoModel.php';
        $docModel   = new DocumentoModel();
        $documentos = $docModel->porAspirante($id);

        // Flujo del proceso
        $stmt2 = $db->prepare("SELECT * FROM flujo_proceso WHERE aspirante_id = :id ORDER BY etapa");
        $stmt2->execute([':id' => $id]);
        $flujo = $stmt2->fetchAll();

        // Test vocacional
        $stmt3 = $db->prepare("SELECT * FROM test_vocacional WHERE aspirante_id = ? LIMIT 1");
        $stmt3->execute([$id]);
        $test = $stmt3->fetch() ?: null;

        $this->render('admin/ver_candidato', [
            'titulo'     => $aspirante['nombres'] . ' ' . $aspirante['apellidos'],
            'aspirante'  => $aspirante,
            'documentos' => $documentos,
            'flujo'      => $flujo,
            'test'       => $test,
        ], 'admin');
    }

    // ── GET /admin/candidatos?ver=ID&ficha=1 — ficha imprimible ──
    public function fichaCandidato(): void {
        $this->requireAdminOEvaluador();

        $id = (int) ($_GET['ver'] ?? 0);
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT a.*, u.email
            FROM aspirantes a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $aspirante = $stmt->fetch();

        if (!$aspirante) {
            $this->redirigir('/admin/candidatos');
            return;
        }

        // Foto del aspirante (documento tipo foto_personal)
        require_once APP_PATH . '/models/DocumentoModel.php';
        $docModel = new DocumentoModel();
        $foto     = $docModel->porTipo($id, 'foto_personal');
        $foto_ruta = ($foto && str_starts_with($foto['mime_type'] ?? '', 'image/'))
            ? '/' . ltrim($foto['ruta'], '/')
            : null;

        // Flujo del proceso
        $stmt2 = $db->prepare("SELECT * FROM flujo_proceso WHERE aspirante_id = :id ORDER BY etapa");
        $stmt2->execute([':id' => $id]);
        $flujo = $stmt2->fetchAll();

        // Perfil vocacional (si el test está completado)
        $stmt3 = $db->prepare("SELECT * FROM test_vocacional WHERE aspirante_id = ? LIMIT 1");
        $stmt3->execute([$id]);
        $test = $stmt3->fetch() ?: null;

        $scoring = null;
        if ($test && !empty($test['completado']) && !empty($test['respuestas'])) {
            $respuestas = json_decode($test['respuestas'], true) ?: [];
            $scoring = TestScorer::calcular($respuestas);
        }

        $this->renderParcial('admin/ficha_candidato', [
            'aspirante' => $aspirante,
            'foto_ruta' => $foto_ruta,
            'flujo'     => $flujo,
            'scoring'   => $scoring,
        ]);
    }

    // ── GET /admin/candidatos?ver=ID&test=1 — ver test vocacional ──
    public function verTest(): void {
        $this->requireAdminOEvaluador();

        $id = (int) ($_GET['ver'] ?? 0);
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT a.*, u.email
            FROM aspirantes a
            LEFT JOIN usuarios u ON u.id = a.usuario_id
            WHERE a.id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $aspirante = $stmt->fetch();

        if (!$aspirante) {
            $this->redirigir('/admin/candidatos');
            return;
        }

        $stmt2 = $db->prepare("SELECT * FROM test_vocacional WHERE aspirante_id = ? LIMIT 1");
        $stmt2->execute([$id]);
        $test = $stmt2->fetch() ?: null;

        $respuestas = [];
        $scoring    = null;
        if ($test && !empty($test['respuestas'])) {
            $respuestas = json_decode($test['respuestas'], true) ?: [];
            if (!empty($test['completado'])) {
                $scoring = TestScorer::calcular($respuestas);
            }
        }

        $this->render('admin/ver_test', [
            'titulo'     => 'Test Vocacional — ' . $aspirante['nombres'] . ' ' . $aspirante['apellidos'],
            'aspirante'  => $aspirante,
            'test'       => $test,
            'respuestas' => $respuestas,
            'scoring'    => $scoring,
        ], 'admin');
    }

    // ── POST /admin/candidatos — cambiar estatus ──────────────
    public function actualizarEstatus(): void {
        $this->requireAdminOEvaluador();

        // Si viene accion=flujo, delegamos al gestor de etapas
        if (($_POST['accion'] ?? '') === 'flujo') {
            $this->actualizarFlujo();
            return;
        }

        // Si viene accion=reset_clave, delegamos al reset de contraseña
        if (($_POST['accion'] ?? '') === 'reset_clave') {
            $this->resetearClave();
            return;
        }

        // Si viene accion=verificar_doc, toggleamos verificado del documento
        if (($_POST['accion'] ?? '') === 'verificar_doc') {
            $this->verificarDocumento();
            return;
        }

        // Si viene accion=subir_doc, el admin sube o reemplaza un documento del candidato
        if (($_POST['accion'] ?? '') === 'subir_doc') {
            $this->subirDocumentoAdmin();
            return;
        }

        $id        = (int) ($_POST['id'] ?? 0);
        $estatus   = $_POST['estatus'] ?? '';
        $nota      = trim($_POST['nota'] ?? '');
        $estatuses = ['enviada', 'en_revision', 'aprobada', 'rechazada', 'borrador'];

        if ($id && in_array($estatus, $estatuses)) {
            $this->aspirantes->actualizar($id, [
                'estatus'       => $estatus,
                'nota_evaluador'=> $nota,
            ]);

            // Al aprobar: generar las 7 cuotas si no existen aún
            if ($estatus === 'aprobada') {
                (new PagoModel())->generarCuotas($id);
            }

            $this->flash('exito', 'Estatus actualizado correctamente.');
        }

        // Redirigir al detalle si viene del modal de ver_candidato
        $redirect = $this->safeRedirect($_POST['_redirect'] ?? '', '/admin/candidatos');
        $this->redirigir($redirect);
    }

    // ── POST /admin/candidatos (accion=reset_clave) — resetear contraseña candidato ──
    public function resetearClave(): void {
        $this->requireAuth('admin'); // Solo admin, no evaluador

        $usuario_id = (int) ($_POST['usuario_id'] ?? 0);
        $nueva      = trim($_POST['nueva_clave'] ?? '');
        $redirect   = $this->safeRedirect($_POST['_redirect'] ?? '', '/admin/candidatos');

        if (!$usuario_id || strlen($nueva) < 8) {
            $this->flash('error', 'La contraseña debe tener al menos 8 caracteres.');
            $this->redirigir($redirect);
            return;
        }

        require_once APP_PATH . '/models/UsuarioModel.php';
        (new UsuarioModel())->cambiarPassword($usuario_id, $nueva);
        $this->flash('exito', 'Contraseña del candidato actualizada correctamente.');
        $this->redirigir($redirect);
    }

    // ── POST /admin/candidatos (accion=verificar_doc) — toggle verificado ──
    public function verificarDocumento(): void {
        $this->requireAdminOEvaluador();

        $doc_id   = (int) ($_POST['doc_id'] ?? 0);
        $redirect = $this->safeRedirect($_POST['_redirect'] ?? '', '/admin/candidatos');

        if ($doc_id) {
            $db = Database::getConnection();
            $db->prepare("UPDATE documentos SET verificado = NOT verificado WHERE id = ?")
               ->execute([$doc_id]);
            $this->flash('exito', 'Estado del documento actualizado.');
        }

        $this->redirigir($redirect);
    }

    // ── POST /admin/candidatos (accion=subir_doc) — admin sube/reemplaza documento ──
    public function subirDocumentoAdmin(): void {
        $this->requireAdminOEvaluador();

        $aspirante_id = (int) ($_POST['aspirante_id'] ?? 0);
        $tipo         = $_POST['tipo'] ?? '';
        $redirect     = $this->safeRedirect($_POST['_redirect'] ?? '', '/admin/candidatos');

        $aspirante = $this->aspirantes->porId($aspirante_id);
        if (!$aspirante) {
            $this->flash('error', 'Candidato no encontrado.');
            $this->redirigir('/admin/candidatos');
        }

        // Validar archivo recibido
        $err_php = $_FILES['archivo']['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($err_php !== UPLOAD_ERR_OK) {
            $grande  = in_array($err_php, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true);
            $this->flash('error', $grande
                ? 'El archivo es demasiado grande. Máximo permitido: 5 MB.'
                : 'No se recibió ningún archivo. Intenta de nuevo.');
            $this->redirigir($redirect);
        }

        $tipos_validos = ['cedula_identidad','titulo_bachiller','carta_pastoral','carta_motivacion','foto_personal','otro'];
        if (!in_array($tipo, $tipos_validos, true)) {
            $this->flash('error', 'Tipo de documento no válido.');
            $this->redirigir($redirect);
        }

        $archivo = $_FILES['archivo'];

        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['pdf','jpg','jpeg','png'], true)) {
            $this->flash('error', 'Formato no permitido. Usa PDF, JPG o PNG.');
            $this->redirigir($redirect);
        }
        if ($archivo['size'] > 5 * 1024 * 1024) {
            $this->flash('error', 'El archivo supera los 5 MB permitidos.');
            $this->redirigir($redirect);
        }

        $nombre_archivo = $aspirante_id . '_' . $tipo . '_' . time() . '.' . $ext;
        $dir_destino    = BASE_PATH . '/uploads/documentos/';
        if (!is_dir($dir_destino)) {
            mkdir($dir_destino, 0755, true);
        }
        if (!move_uploaded_file($archivo['tmp_name'], $dir_destino . $nombre_archivo)) {
            $this->flash('error', 'No se pudo guardar el archivo en el servidor.');
            $this->redirigir($redirect);
        }

        require_once APP_PATH . '/models/DocumentoModel.php';
        $docModel = new DocumentoModel();

        // Si ya existe uno del mismo tipo, borrar el archivo físico anterior para no dejar huérfanos
        $previo = $docModel->porTipo($aspirante_id, $tipo);
        if ($previo && !empty($previo['ruta'])) {
            $ruta_previa = BASE_PATH . '/' . $previo['ruta'];
            if (is_file($ruta_previa)) {
                @unlink($ruta_previa);
            }
        }

        $docModel->guardar([
            'aspirante_id'  => $aspirante_id,
            'tipo'          => $tipo,
            'nombre_archivo'=> $nombre_archivo,
            'ruta'          => 'uploads/documentos/' . $nombre_archivo,
            'mime_type'     => $archivo['type'],
            'tamanio_kb'    => (int) ceil($archivo['size'] / 1024),
        ]);

        $this->flash('exito', $previo
            ? 'Documento reemplazado correctamente.'
            : 'Documento subido correctamente.');
        $this->redirigir($redirect);
    }

    // ── POST /admin/candidatos (accion=flujo) — actualizar etapa ──
    public function actualizarFlujo(): void {
        $this->requireAdminOEvaluador();

        $aspirante_id    = (int) ($_POST['aspirante_id'] ?? 0);
        $etapa           = $_POST['etapa']   ?? '';
        $estatus         = $_POST['estatus'] ?? '';
        $notas           = trim($_POST['notas'] ?? '');

        $etapas_validas  = [
            'solicitud_formal', 'evaluacion_documental', 'test_vocacional',
            'entrevista_personal', 'confirmacion_admision',
        ];
        $estatuses_validos = ['pendiente', 'en_proceso', 'aprobado', 'rechazado'];

        if ($aspirante_id && in_array($etapa, $etapas_validas) && in_array($estatus, $estatuses_validos)) {
            $db = Database::getConnection();

            $db->prepare("
                INSERT INTO flujo_proceso (aspirante_id, etapa, estatus, notas, evaluador_id,
                                          fecha_inicio, fecha_cierre)
                VALUES (?, ?, ?, ?, ?,
                    IF(? != 'pendiente', NOW(), NULL),
                    IF(? IN ('aprobado','rechazado'), NOW(), NULL))
                ON DUPLICATE KEY UPDATE
                    estatus      = VALUES(estatus),
                    notas        = VALUES(notas),
                    evaluador_id = VALUES(evaluador_id),
                    fecha_inicio = CASE
                        WHEN fecha_inicio IS NULL AND VALUES(estatus) != 'pendiente' THEN NOW()
                        ELSE fecha_inicio
                    END,
                    fecha_cierre = CASE
                        WHEN VALUES(estatus) IN ('aprobado','rechazado') THEN NOW()
                        WHEN VALUES(estatus) NOT IN ('aprobado','rechazado') THEN NULL
                        ELSE fecha_cierre
                    END,
                    updated_at   = NOW()
            ")->execute([
                $aspirante_id, $etapa, $estatus, $notas ?: null, (int)$_SESSION['usuario_id'],
                $estatus,   // for fecha_inicio IF condition
                $estatus,   // for fecha_cierre IF condition
            ]);

            $this->flash('exito', 'Etapa «' . ucwords(str_replace('_', ' ', $etapa)) . '» actualizada correctamente.');
        }

        $redirect = $this->safeRedirect($_POST['_redirect'] ?? '', '/admin/candidatos');
        $this->redirigir($redirect);
    }

    // ── GET /admin/galeria ────────────────────────────────────
    public function galeria(): void {
        $this->requireAdminOEvaluador();
        $db = Database::getConnection();

        // Sedes con conteo de ítems
        $sedes = $db->query("
            SELECT s.*, COUNT(m.id) AS total_items
            FROM sedes s
            LEFT JOIN multimedia m ON m.sede_id = s.id
            GROUP BY s.id
            ORDER BY s.orden
        ")->fetchAll();

        $sede_sel = null;
        $items    = [];
        $sede_id  = (int)($_GET['sede'] ?? 0);

        if ($sede_id) {
            // Reutilizar datos ya cargados — evita query extra
            $matches = array_filter($sedes, fn($s) => (int)$s['id'] === $sede_id);
            $sede_sel = $matches ? array_values($matches)[0] : null;

            if ($sede_sel) {
                $stmt2 = $db->prepare("SELECT * FROM multimedia WHERE sede_id = ? ORDER BY orden, id DESC");
                $stmt2->execute([$sede_id]);
                $items = $stmt2->fetchAll();
            }
        }

        $this->render('admin/galeria', [
            'titulo'   => 'Galería Multimedia',
            'sedes'    => $sedes,
            'sede_sel' => $sede_sel,
            'items'    => $items,
        ], 'admin');
    }

    // ── POST /admin/galeria ───────────────────────────────────
    public function gestionarGaleria(): void {
        $this->requireAdminOEvaluador();

        $accion   = $_POST['accion']  ?? '';
        $sede_id  = (int)($_POST['sede_id'] ?? 0);
        $redirect = "/admin/galeria?sede={$sede_id}";
        $db       = Database::getConnection();

        if ($accion === 'subir' && $sede_id) {
            $tipo   = ($_POST['tipo'] ?? '') === 'video' ? 'video' : 'foto';
            $titulo = trim($_POST['titulo'] ?? '');
            $desc   = trim($_POST['descripcion'] ?? '') ?: null;
            $dest   = (int)($_POST['destacado'] ?? 0);

            if (!$titulo) {
                $this->flash('error', 'El título es requerido.');
                $this->redirigir($redirect);
                return;
            }

            if ($tipo === 'foto') {
                $file = $_FILES['archivo'] ?? null;
                if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                    $this->flash('error', 'Error al subir el archivo.');
                    $this->redirigir($redirect);
                    return;
                }
                // Validar MIME real + extensión
                $finfo   = new finfo(FILEINFO_MIME_TYPE);
                $mime    = $finfo->file($file['tmp_name']);
                $mimes_ok = ['image/jpeg','image/png','image/webp','image/gif'];
                $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($mime, $mimes_ok) || $file['size'] > 5 * 1024 * 1024) {
                    $this->flash('error', 'Solo JPG, PNG, WEBP, GIF. Máx 5 MB.');
                    $this->redirigir($redirect);
                    return;
                }
                // Nombre único a prueba de colisiones
                $nombre = 'gal_' . $sede_id . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $ruta   = BASE_PATH . '/public/uploads/galeria/' . $nombre;
                if (!move_uploaded_file($file['tmp_name'], $ruta)) {
                    $this->flash('error', 'No se pudo guardar el archivo.');
                    $this->redirigir($redirect);
                    return;
                }
                $url   = '/uploads/galeria/' . $nombre;
                $thumb = $url;

            } else {
                $url = trim($_POST['video_url'] ?? '');
                if (!$url) {
                    $this->flash('error', 'La URL del video es requerida.');
                    $this->redirigir($redirect);
                    return;
                }
                // Auto-extraer thumbnail de YouTube
                $thumb = null;
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
                    $thumb = "https://img.youtube.com/vi/{$m[1]}/mqdefault.jpg";
                }
            }

            $db->prepare("
                INSERT INTO multimedia (sede_id, titulo, descripcion, tipo, url, thumb_url, destacado, orden)
                VALUES (?, ?, ?, ?, ?, ?, ?,
                    (SELECT COALESCE(MAX(mx.orden), 0) + 1 FROM multimedia mx WHERE mx.sede_id = ?))
            ")->execute([$sede_id, $titulo, $desc, $tipo, $url, $thumb, $dest, $sede_id]);

            $this->flash('exito', 'Ítem agregado correctamente.');

        } elseif ($accion === 'eliminar') {
            $id   = (int)($_POST['item_id'] ?? 0);
            $stmt = $db->prepare("SELECT url, tipo FROM multimedia WHERE id = ? AND sede_id = ?");
            $stmt->execute([$id, $sede_id]);
            $item = $stmt->fetch();
            if ($item) {
                $db->prepare("DELETE FROM multimedia WHERE id = ?")->execute([$id]);
                if ($item['tipo'] === 'foto') {
                    $path = BASE_PATH . '/public' . $item['url'];
                    if (file_exists($path)) @unlink($path);
                }
                $this->flash('exito', 'Ítem eliminado.');
            }

        } elseif ($accion === 'toggle_destacado') {
            $id = (int)($_POST['item_id'] ?? 0);
            $db->prepare("UPDATE multimedia SET destacado = NOT destacado WHERE id = ? AND sede_id = ?")->execute([$id, $sede_id]);

        } elseif ($accion === 'toggle_activo') {
            $id = (int)($_POST['item_id'] ?? 0);
            $db->prepare("UPDATE multimedia SET activo = NOT activo WHERE id = ? AND sede_id = ?")->execute([$id, $sede_id]);
        }

        $this->redirigir($redirect);
    }

    // ── GET /admin/estadisticas ───────────────────────────────
    public function estadisticas(): void {
        $this->requireAdminOEvaluador();
        $db = Database::getConnection();

        $stmt = $db->query("SELECT * FROM impacto_estadisticas ORDER BY orden");
        $stats = $stmt->fetchAll();

        $this->render('admin/estadisticas', [
            'titulo' => 'Estadísticas de Impacto',
            'stats'  => $stats,
        ], 'admin');
    }

    // ── POST /admin/estadisticas ──────────────────────────────
    public function actualizarEstadisticas(): void {
        $this->requireAuth('admin'); // Solo admin, no evaluador
        $db = Database::getConnection();

        foreach ($_POST['valor'] as $id => $valor) {
            $stmt = $db->prepare("UPDATE impacto_estadisticas SET valor = :v WHERE id = :id");
            $stmt->execute([':v' => (int) $valor, ':id' => (int) $id]);
        }
        $this->flash('exito', 'Estadísticas actualizadas.');
        $this->redirigir('/admin/estadisticas');
    }


    // ── GET /admin/perfil ─────────────────────────────────────
    public function perfil(): void {
        $this->requireAdminOEvaluador();
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT id, nombre, apellido, email, rol FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();

        $this->render('admin/perfil', [
            'titulo'  => 'Mi Perfil',
            'usuario' => $usuario,
        ], 'admin');
    }

    // ── POST /admin/perfil ────────────────────────────────────
    public function actualizarPerfil(): void {
        $this->requireAdminOEvaluador();

        $db      = Database::getConnection();
        $id      = (int) $_SESSION['usuario_id'];
        $accion  = $_POST['accion'] ?? '';
        $errores = [];

        if ($accion === 'datos') {
            $nombre   = trim($_POST['nombre']   ?? '');
            $apellido = trim($_POST['apellido']  ?? '');
            $email    = strtolower(trim($_POST['email'] ?? ''));

            if (!$nombre || !$apellido)
                $errores[] = 'Nombre y apellido son obligatorios.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
                $errores[] = 'El correo no es valido.';

            if (!$errores) {
                $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = :e AND id != :id");
                $stmt->execute([':e' => $email, ':id' => $id]);
                if ($stmt->fetch()) $errores[] = 'Ese correo ya esta en uso.';
            }

            if (!$errores) {
                $stmt = $db->prepare("UPDATE usuarios SET nombre=:n, apellido=:a, email=:e WHERE id=:id");
                $stmt->execute([':n'=>$nombre, ':a'=>$apellido, ':e'=>$email, ':id'=>$id]);
                $_SESSION['usuario_nombre'] = $nombre . ' ' . $apellido;
                $_SESSION['usuario_email']  = $email;
                $this->flash('exito', 'Datos actualizados correctamente.');
                $this->redirigir('/admin/perfil');
            }

        } elseif ($accion === 'password') {
            $actual   = $_POST['password_actual']   ?? '';
            $nueva    = $_POST['password_nueva']    ?? '';
            $confirma = $_POST['password_confirma'] ?? '';

            $stmt = $db->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $hash = $stmt->fetchColumn();

            if (!password_verify($actual, $hash))
                $errores[] = 'La contrasena actual no es correcta.';
            if (strlen($nueva) < 8)
                $errores[] = 'La nueva contrasena debe tener al menos 8 caracteres.';
            if ($nueva !== $confirma)
                $errores[] = 'La confirmacion no coincide.';

            if (!$errores) {
                require_once APP_PATH . '/models/UsuarioModel.php';
                $uModel = new UsuarioModel();
                $uModel->cambiarPassword($id, $nueva);
                $this->flash('exito', 'Contrasena actualizada correctamente.');
                $this->redirigir('/admin/perfil');
            }
        }

        $stmt = $db->prepare("SELECT id, nombre, apellido, email, rol FROM usuarios WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $usuario = $stmt->fetch();

        $this->render('admin/perfil', [
            'titulo'  => 'Mi Perfil',
            'usuario' => $usuario,
            'errores' => $errores,
            'accion'  => $accion,
        ], 'admin');
    }

    // ── GET /admin/colaboradores ──────────────────────────────
    public function colaboradores(): void {
        $this->requireAdminOEvaluador();

        $db   = Database::getConnection();
        $filtro = $_GET['filtro'] ?? 'todos';

        if ($filtro === 'pendientes') {
            $where = 'WHERE aprobado = 0';
        } elseif ($filtro === 'aprobados') {
            $where = 'WHERE aprobado = 1';
        } else {
            $where = '';
        }

        $stmt = $db->query("
            SELECT id, nombre, organizacion, email, tipo, mensaje, aprobado, created_at
            FROM colaboradores
            $where
            ORDER BY aprobado ASC, created_at DESC
        ");
        $colaboradores = $stmt->fetchAll();

        // Contadores
        $total     = (int) $db->query("SELECT COUNT(*) FROM colaboradores")->fetchColumn();
        $pendientes = (int) $db->query("SELECT COUNT(*) FROM colaboradores WHERE aprobado = 0")->fetchColumn();
        $aprobados  = (int) $db->query("SELECT COUNT(*) FROM colaboradores WHERE aprobado = 1")->fetchColumn();

        $this->render('admin/colaboradores', [
            'titulo'       => 'Colaboradores',
            'colaboradores' => $colaboradores,
            'filtro'        => $filtro,
            'total'         => $total,
            'pendientes'    => $pendientes,
            'aprobados'     => $aprobados,
        ], 'admin');
    }

    // ── POST /admin/colaboradores ─────────────────────────────
    public function gestionarColaborador(): void {
        $this->requireAdminOEvaluador();

        $db     = Database::getConnection();
        $id     = (int) ($_POST['id'] ?? 0);
        $accion = $_POST['accion'] ?? '';

        if (!$id) {
            $this->flash('error', 'ID inválido.');
            $this->redirigir('/admin/colaboradores');
            return;
        }

        if ($accion === 'aprobar') {
            $db->prepare("UPDATE colaboradores SET aprobado = 1 WHERE id = :id")
               ->execute([':id' => $id]);
            $this->flash('exito', 'Colaborador aprobado.');
        } elseif ($accion === 'rechazar') {
            $db->prepare("DELETE FROM colaboradores WHERE id = :id")
               ->execute([':id' => $id]);
            $this->flash('exito', 'Registro eliminado.');
        } else {
            $this->flash('error', 'Acción no reconocida.');
        }

        $this->redirigir('/admin/colaboradores');
    }

    // ══════════════════════════════════════════════════════════
    // FINANZAS
    // ══════════════════════════════════════════════════════════

    // ── GET /admin/finanzas ───────────────────────────────────
    public function finanzas(): void {
        $this->requireAdminOEvaluador();

        $fin       = new FinanzasModel();
        $ingresos  = new IngresoModel();
        $gastos    = new GastoModel();
        $pagos     = new PagoModel();

        $this->render('admin/finanzas', [
            'titulo'              => 'Finanzas',
            'kpis'                => $fin->kpis(),
            'matricula'           => $pagos->totalesMatricula(),
            'fondos'              => $fin->resumenPorFondo(),
            'cuentas'             => $fin->resumenPorCuenta(),
            'flujo'               => $fin->flujoMensual(),
            'pendientes'          => $ingresos->pendientes(),
            'ultimos_ingresos'    => $ingresos->listar([], 8),
            'ultimos_gastos'      => $gastos->recientes(8),
            'gastos_categoria'    => $gastos->totalesPorCategoria(),
            'prestamos'           => (new PrestamoModel())->listar(),
        ], 'admin');
    }

    // ── GET /admin/finanzas/movimientos ───────────────────────
    public function movimientos(): void {
        $this->requireAdminOEvaluador();

        $fin = new FinanzasModel();

        $filtros = [
            'origen'    => $_GET['origen']    ?? '',
            'categoria' => $_GET['categoria'] ?? '',
            'fondo_id'  => $_GET['fondo_id']  ?? '',
            'desde'     => $_GET['desde']     ?? '',
            'hasta'     => $_GET['hasta']     ?? '',
        ];

        $this->render('admin/finanzas_movimientos', [
            'titulo'      => 'Ingresos y gastos',
            'filtros'     => $filtros,
            'ingresos'    => (new IngresoModel())->listar($filtros),
            'gastos'      => (new GastoModel())->listar($filtros),
            'fondos'      => $fin->fondos(),
            'cuentas'     => $fin->cuentas(),
            'categorias'  => GastoModel::CATEGORIAS,
            'origenes'    => IngresoModel::ORIGENES,
            'aprobados'   => (new AspiranteModel())->todosConEstatus('aprobada'),
            'prestamos'   => (new PrestamoModel())->activos(),
            'tab'         => $_GET['tab'] ?? 'ingresos',
        ], 'admin');
    }

    // ── POST /admin/finanzas/ingreso ──────────────────────────
    public function registrarIngreso(): void {
        $this->requireAdminOEvaluador();

        $origen  = $_POST['origen'] ?? '';
        $montos  = $this->normalizarMontos($_POST);

        if (!array_key_exists($origen, IngresoModel::ORIGENES) || $montos['monto_usd'] <= 0) {
            $this->flash('error', 'Revisa el origen y el monto del ingreso.');
            $this->redirigir('/admin/finanzas/movimientos');
            return;
        }

        $concepto = trim($_POST['concepto'] ?? '');
        if ($concepto === '') {
            $this->flash('error', 'El concepto es obligatorio.');
            $this->redirigir('/admin/finanzas/movimientos');
            return;
        }

        $aspirante_id = $origen === 'matricula' ? (int) ($_POST['aspirante_id'] ?? 0) : 0;
        if ($origen === 'matricula' && !$aspirante_id) {
            $this->flash('error', 'Indica de qué participante es la matrícula.');
            $this->redirigir('/admin/finanzas/movimientos');
            return;
        }

        $ruta = null;
        if (!empty($_FILES['comprobante']['name'])) {
            $ruta = $this->subirComprobante($_FILES['comprobante'], 'ing');
            if ($ruta === false) {
                $this->flash('error', 'Archivo inválido. Usa JPG, PNG o PDF (máx 5 MB).');
                $this->redirigir('/admin/finanzas/movimientos');
                return;
            }
        }

        (new IngresoModel())->registrar([
            'fecha'            => $_POST['fecha'] ?: date('Y-m-d'),
            'origen'           => $origen,
            'aspirante_id'     => $aspirante_id ?: null,
            'fondo_id'         => (int) ($_POST['fondo_id']  ?? 0),
            'cuenta_id'        => (int) ($_POST['cuenta_id'] ?? 0),
            'aportante'        => trim($_POST['aportante'] ?? ''),
            'concepto'         => $concepto,
            'monto_usd'        => $montos['monto_usd'],
            'monto_ves'        => $montos['monto_ves'],
            'tasa_cambio'      => $montos['tasa'],
            'metodo_pago'      => $_POST['metodo_pago'] ?? 'efectivo',
            'banco_origen'     => trim($_POST['banco_origen'] ?? ''),
            'referencia'       => trim($_POST['referencia'] ?? ''),
            'comprobante_ruta' => $ruta,
            'estatus'          => 'confirmado',
            'registrado_via'   => 'admin',
            'registrado_por'   => (int) $_SESSION['usuario_id'],
            'notas'            => trim($_POST['notas'] ?? ''),
        ]);

        $this->flash('exito', 'Ingreso registrado.');
        $this->redirigir('/admin/finanzas/movimientos');
    }

    // ── POST /admin/finanzas/gasto ────────────────────────────
    public function registrarGasto(): void {
        $this->requireAdminOEvaluador();

        $concepto  = trim($_POST['concepto'] ?? '');
        $categoria = $_POST['categoria'] ?? '';
        $montos    = $this->normalizarMontos($_POST);

        if ($concepto === '' || !array_key_exists($categoria, GastoModel::CATEGORIAS) || $montos['monto_usd'] <= 0) {
            $this->flash('error', 'Concepto, categoría y monto son obligatorios.');
            $this->redirigir('/admin/finanzas/movimientos?tab=gastos');
            return;
        }

        $prestamo_id = (int) ($_POST['prestamo_id'] ?? 0);
        if ($categoria === 'devolucion_prestamo' && !$prestamo_id) {
            $this->flash('error', 'Indica a qué préstamo corresponde la devolución.');
            $this->redirigir('/admin/finanzas/movimientos?tab=gastos');
            return;
        }

        $ruta = null;
        if (!empty($_FILES['comprobante']['name'])) {
            $ruta = $this->subirComprobante($_FILES['comprobante'], 'gasto');
            if ($ruta === false) {
                $this->flash('error', 'Archivo inválido. Usa JPG, PNG o PDF (máx 5 MB).');
                $this->redirigir('/admin/finanzas/movimientos?tab=gastos');
                return;
            }
        }

        (new GastoModel())->registrar([
            'fecha_gasto'      => $_POST['fecha_gasto'] ?: date('Y-m-d'),
            'concepto'         => $concepto,
            'categoria'        => $categoria,
            'fondo_id'         => (int) ($_POST['fondo_id']  ?? 0),
            'cuenta_id'        => (int) ($_POST['cuenta_id'] ?? 0),
            'prestamo_id'      => $prestamo_id,
            'beneficiario'     => trim($_POST['beneficiario'] ?? ''),
            'monto_usd'        => $montos['monto_usd'],
            'monto_ves'        => $montos['monto_ves'],
            'tasa_cambio'      => $montos['tasa'],
            'metodo_pago'      => $_POST['metodo_pago'] ?? 'efectivo',
            'referencia'       => trim($_POST['referencia'] ?? ''),
            'comprobante_ruta' => $ruta,
            'notas'            => trim($_POST['notas'] ?? ''),
        ], (int) $_SESSION['usuario_id']);

        $this->flash('exito', 'Gasto registrado.');
        $this->redirigir('/admin/finanzas/movimientos?tab=gastos');
    }

    // ── POST /admin/finanzas/confirmar ────────────────────────
    public function confirmarIngreso(): void {
        $this->requireAdminOEvaluador();

        $id     = (int) ($_POST['id'] ?? 0);
        $accion = $_POST['accion'] ?? '';
        $notas  = trim($_POST['notas_admin'] ?? '');

        if (!$id || !in_array($accion, ['confirmar', 'rechazar'])) {
            $this->flash('error', 'Solicitud inválida.');
            $this->redirigir('/admin/finanzas');
            return;
        }

        $ingresos = new IngresoModel();
        $admin_id = (int) $_SESSION['usuario_id'];

        if ($accion === 'confirmar') {
            $ok = $ingresos->confirmar($id, $admin_id, $notas ?: null);
            $this->flash($ok ? 'exito' : 'error', $ok ? 'Pago confirmado.' : 'No se pudo confirmar el pago.');
        } else {
            if ($notas === '') {
                $this->flash('error', 'Debes indicar el motivo del rechazo.');
                $this->redirigir('/admin/finanzas');
                return;
            }
            $ok = $ingresos->rechazar($id, $admin_id, $notas);
            $this->flash($ok ? 'exito' : 'error', $ok ? 'Pago rechazado.' : 'No se pudo rechazar.');
        }

        $this->redirigir('/admin/finanzas');
    }

    // ── GET /admin/finanzas/matriculas ────────────────────────
    public function matriculas(): void {
        $this->requireAdminOEvaluador();

        $pagos = new PagoModel();
        $resumen = $pagos->resumenPorEstudiante();

        // Cuotas de cada participante, para el detalle desplegable
        $cuotas = [];
        foreach ($resumen as $r) {
            $cuotas[$r['aspirante_id']] = $pagos->cuotasPorAspirante((int) $r['aspirante_id']);
        }

        $this->render('admin/finanzas_matriculas', [
            'titulo'    => 'Matrículas y becas',
            'resumen'   => $resumen,
            'cuotas'    => $cuotas,
            'totales'   => $pagos->totalesMatricula(),
        ], 'admin');
    }

    // ── POST /admin/finanzas/beca ─────────────────────────────
    public function actualizarBeca(): void {
        $this->requireAdminOEvaluador();

        $aspirante_id = (int) ($_POST['aspirante_id'] ?? 0);
        $beca         = (float) ($_POST['beca_pct'] ?? -1);
        $costo        = $_POST['costo_base_usd'] !== '' ? (float) $_POST['costo_base_usd'] : null;

        if (!$aspirante_id || $beca < 0 || $beca > 100) {
            $this->flash('error', 'La beca debe estar entre 0 y 100 por ciento.');
            $this->redirigir('/admin/finanzas/matriculas');
            return;
        }

        (new PagoModel())->actualizarBeca(
            $aspirante_id,
            $beca,
            trim($_POST['beca_notas'] ?? '') ?: null,
            $costo
        );

        $this->flash('exito', 'Beca actualizada y cuotas recalculadas.');
        $this->redirigir('/admin/finanzas/matriculas');
    }

    // ── GET /admin/finanzas/prestamos ─────────────────────────
    public function prestamos(): void {
        $this->requireAdminOEvaluador();

        $fin = new FinanzasModel();

        $this->render('admin/finanzas_prestamos', [
            'titulo'    => 'Préstamos',
            'prestamos' => (new PrestamoModel())->listar(),
            'saldo'     => (new PrestamoModel())->saldoTotal(),
            'fondos'    => $fin->fondos(),
            'cuentas'   => $fin->cuentas(),
        ], 'admin');
    }

    // ── POST /admin/finanzas/prestamo ─────────────────────────
    public function registrarPrestamo(): void {
        $this->requireAdminOEvaluador();

        $prestamista = trim($_POST['prestamista'] ?? '');
        $concepto    = trim($_POST['concepto'] ?? '');
        $montos      = $this->normalizarMontos($_POST);

        if ($prestamista === '' || $concepto === '' || $montos['monto_usd'] <= 0) {
            $this->flash('error', 'Quién prestó, para qué y cuánto son obligatorios.');
            $this->redirigir('/admin/finanzas/prestamos');
            return;
        }

        (new PrestamoModel())->registrar([
            'prestamista'      => $prestamista,
            'telefono'         => trim($_POST['telefono'] ?? ''),
            'concepto'         => $concepto,
            'fondo_id'         => (int) ($_POST['fondo_id']  ?? 0),
            'cuenta_id'        => (int) ($_POST['cuenta_id'] ?? 0),
            'monto_usd'        => $montos['monto_usd'],
            'monto_ves'        => $montos['monto_ves'],
            'tasa_cambio'      => $montos['tasa'],
            'metodo_pago'      => $_POST['metodo_pago'] ?? 'efectivo',
            'referencia'       => trim($_POST['referencia'] ?? ''),
            'fecha_prestamo'   => $_POST['fecha_prestamo'] ?: date('Y-m-d'),
            'fecha_compromiso' => $_POST['fecha_compromiso'] ?? '',
            'notas'            => trim($_POST['notas'] ?? ''),
        ], (int) $_SESSION['usuario_id']);

        $this->flash('exito', 'Préstamo registrado. El monto entró a caja y quedó como deuda por pagar.');
        $this->redirigir('/admin/finanzas/prestamos');
    }

    // ── POST /admin/finanzas/fondo ────────────────────────────
    public function crearFondo(): void {
        $this->requireAdminOEvaluador();

        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            $this->flash('error', 'El fondo necesita un nombre.');
            $this->redirigir('/admin/finanzas');
            return;
        }

        try {
            (new FinanzasModel())->crearFondo(
                $nombre,
                trim($_POST['descripcion'] ?? ''),
                ($_POST['tipo'] ?? 'especifico') === 'general' ? 'general' : 'especifico'
            );
            $this->flash('exito', 'Fondo creado.');
        } catch (PDOException $e) {
            $this->flash('error', 'Ya existe un fondo con ese nombre.');
        }

        $this->redirigir('/admin/finanzas');
    }

    // ── GET /admin/finanzas/exportar ──────────────────────────
    public function exportarFinanzas(): void {
        $this->requireAdminOEvaluador();

        $tipo  = $_GET['tipo'] ?? 'ingresos';
        $fecha = date('Y-m-d');

        header('Content-Type: text/csv; charset=utf-8');

        if ($tipo === 'gastos') {
            header("Content-Disposition: attachment; filename=gastos_{$fecha}.csv");
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8
            fputcsv($out, ['Fecha','Concepto','Categoría','Fondo','Cuenta','Beneficiario','Monto USD','Monto Bs','Tasa','Método','Referencia','Registrado por','Notas'], ';');
            foreach ((new GastoModel())->todosParaExportar() as $r) {
                fputcsv($out, [
                    $r['fecha_gasto'], $r['concepto'],
                    GastoModel::CATEGORIAS[$r['categoria']] ?? $r['categoria'],
                    $r['fondo'], $r['cuenta'], $r['beneficiario'],
                    $r['monto_usd'], $r['monto_ves'], $r['tasa_cambio'],
                    $r['metodo_pago'], $r['referencia'], $r['registrado_por'], $r['notas'],
                ], ';');
            }
        } elseif ($tipo === 'matriculas') {
            header("Content-Disposition: attachment; filename=matriculas_{$fecha}.csv");
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Participante','Cédula','Costo base USD','Beca %','A pagar USD','Pagado USD','Saldo USD','Cuotas completadas','Próximo vencimiento'], ';');
            foreach ((new PagoModel())->resumenPorEstudiante() as $r) {
                fputcsv($out, [
                    $r['nombre'], $r['cedula'], $r['costo_base_usd'], $r['beca_pct'],
                    $r['total_a_pagar'], $r['total_pagado'],
                    round((float)$r['total_a_pagar'] - (float)$r['total_pagado'], 2),
                    $r['cuotas_completadas'] . '/' . $r['total_cuotas'],
                    $r['proximo_vencimiento'],
                ], ';');
            }
        } else {
            header("Content-Disposition: attachment; filename=ingresos_{$fecha}.csv");
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Fecha','Origen','De quién','Concepto','Fondo','Cuenta','Monto USD','Monto Bs','Tasa','Método','Referencia','Estatus','Notas'], ';');
            foreach ((new IngresoModel())->todosParaExportar() as $r) {
                fputcsv($out, [
                    $r['fecha'],
                    IngresoModel::ORIGENES[$r['origen']] ?? $r['origen'],
                    $r['de_quien'], $r['concepto'], $r['fondo'], $r['cuenta'],
                    $r['monto_usd'], $r['monto_ves'], $r['tasa_cambio'],
                    $r['metodo_pago'], $r['referencia'], $r['estatus'], $r['notas'],
                ], ';');
            }
        }

        fclose($out);
        exit;
    }

    /**
     * Un movimiento puede venir en USD o en bolívares. Si viene en Bs
     * con tasa, se convierte: todo se consolida en USD.
     */
    private function normalizarMontos(array $post): array {
        $usd  = (float) ($post['monto_usd']   ?? 0);
        $ves  = (float) ($post['monto_ves']   ?? 0);
        $tasa = (float) ($post['tasa_cambio'] ?? 0);

        if ($usd <= 0 && $ves > 0 && $tasa > 0) {
            $usd = round($ves / $tasa, 2);
        }

        return [
            'monto_usd' => $usd,
            'monto_ves' => $ves ?: null,
            'tasa'      => $tasa ?: null,
        ];
    }


    // ── Helpers ───────────────────────────────────────────────
    private function requireAdminOEvaluador(): void {
        if (empty($_SESSION['usuario_id'])) {
            $this->redirigir('/login');
        }
        if (!in_array($_SESSION['usuario_rol'], ['admin', 'evaluador'])) {
            http_response_code(403);
            require APP_PATH . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Sube un comprobante de pago/gasto al servidor.
     * @return string|false  Ruta relativa o false si hay error
     */
    private function subirComprobante(array $file, string $prefijo = 'comp'): string|false {
        $extPermitidas = ['jpg','jpeg','png','pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $extPermitidas)) return false;
        if ($file['size'] > 5 * 1024 * 1024) return false;

        $dir = BASE_PATH . '/uploads/comprobantes/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $nombre = $prefijo . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], $dir . $nombre)) return false;

        return 'uploads/comprobantes/' . $nombre;
    }

    private function contarPorEstatus(string $estatus): int {
        $db   = Database::getConnection();
        $sql  = $estatus
            ? "SELECT COUNT(*) FROM aspirantes WHERE estatus = :e"
            : "SELECT COUNT(*) FROM aspirantes";
        $stmt = $db->prepare($sql);
        $estatus ? $stmt->execute([':e' => $estatus]) : $stmt->execute();
        return (int) $stmt->fetchColumn();
    }
}
