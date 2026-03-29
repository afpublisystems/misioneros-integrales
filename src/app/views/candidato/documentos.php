<?php $titulo = 'Mis Documentos'; ?>

<div class="dashboard-layout">

    <!-- Sidebar -->
    <aside class="dash-sidebar">
        <div class="dash-sidebar__user">
            <div class="dash-sidebar__avatar">
                <?= mb_strtoupper(mb_substr($_SESSION['usuario_nombre'] ?: $_SESSION['usuario_email'], 0, 1)) ?>
            </div>
            <div class="dash-sidebar__info">
                <strong><?= htmlspecialchars($_SESSION['usuario_nombre'] ?: $_SESSION['usuario_email']) ?></strong>
                <span class="badge badge--<?= $aspirante['estatus'] ?? 'borrador' ?>">
                    <?= ucfirst(str_replace('_', ' ', $aspirante['estatus'] ?? 'borrador')) ?>
                </span>
            </div>
        </div>
        <nav class="dash-nav">
            <a href="/candidato/dashboard"  class="dash-nav__item"><i class="fas fa-tachometer-alt"></i> Mi Dashboard</a>
            <a href="/candidato/perfil"     class="dash-nav__item"><i class="fas fa-user-edit"></i> Mi Perfil</a>
            <a href="/candidato/documentos" class="dash-nav__item activo"><i class="fas fa-folder-open"></i> Documentos</a>
            <a href="/candidato/test"       class="dash-nav__item"><i class="fas fa-clipboard-list"></i> Test Vocacional</a>
            <a href="/candidato/pagos"      class="dash-nav__item"><i class="fas fa-dollar-sign"></i> Mis Pagos</a>
            <a href="/logout" class="dash-nav__item dash-nav__item--logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </nav>
    </aside>

    <!-- Main -->
    <main class="dash-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="dash-header">
            <div>
                <h1>Mis Documentos</h1>
                <p>Sube los documentos requeridos para tu postulación</p>
            </div>
        </div>

        <!-- Documentos requeridos -->
        <?php
        $docs_requeridos = [
            [
                'tipo'        => 'cedula',
                'nombre'      => 'Cédula de Identidad',
                'descripcion' => 'Copia legible de ambas caras de tu cédula de identidad',
                'icono'       => 'fa-id-card',
                'formatos'    => 'PDF, JPG, PNG',
                'obligatorio' => true,
            ],
            [
                'tipo'        => 'titulo_bachiller',
                'nombre'      => 'Título de Bachiller',
                'descripcion' => 'Copia certificada o fotografía legible del título de bachiller',
                'icono'       => 'fa-graduation-cap',
                'formatos'    => 'PDF, JPG, PNG',
                'obligatorio' => true,
            ],
            [
                'tipo'        => 'carta_pastoral',
                'nombre'      => 'Carta Pastoral',
                'descripcion' => 'Carta de recomendación firmada por tu pastor con datos de la iglesia',
                'icono'       => 'fa-church',
                'formatos'    => 'PDF, JPG, PNG',
                'obligatorio' => true,
            ],
            [
                'tipo'        => 'foto_reciente',
                'nombre'      => 'Foto Reciente',
                'descripcion' => 'Foto tipo carné o retrato reciente, fondo claro',
                'icono'       => 'fa-camera',
                'formatos'    => 'JPG, PNG',
                'obligatorio' => true,
            ],
            [
                'tipo'        => 'otros',
                'nombre'      => 'Documentos Adicionales',
                'descripcion' => 'Cualquier otro documento que desees incluir (títulos, certificados, etc.)',
                'icono'       => 'fa-paperclip',
                'formatos'    => 'PDF, JPG, PNG',
                'obligatorio' => false,
            ],
        ];

        // Indexar documentos subidos por tipo
        $subidos = [];
        foreach ($documentos as $doc) {
            $subidos[$doc['tipo']] = $doc;
        }
        ?>

        <div class="docs-grid">
            <?php foreach ($docs_requeridos as $req): ?>
            <?php $subido = $subidos[$req['tipo']] ?? null; ?>
            <div class="doc-card <?= $subido ? 'doc-card--subido' : '' ?>">
                <div class="doc-card__header">
                    <div class="doc-card__icono">
                        <i class="fas <?= $req['icono'] ?>"></i>
                    </div>
                    <div class="doc-card__info">
                        <h3>
                            <?= $req['nombre'] ?>
                            <?php if ($req['obligatorio']): ?>
                            <span class="req">*</span>
                            <?php endif; ?>
                        </h3>
                        <p><?= $req['descripcion'] ?></p>
                        <small>Formatos: <?= $req['formatos'] ?> · Máx. 5 MB</small>
                    </div>
                    <?php if ($subido): ?>
                    <div class="doc-card__estado">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($subido): ?>
                <!-- Documento ya subido -->
                <div class="doc-subido">
                    <div class="doc-subido__nombre">
                        <i class="fas fa-file-alt"></i>
                        <?= htmlspecialchars($subido['nombre_original']) ?>
                    </div>
                    <div class="doc-subido__meta">
                        Subido el <?= date('d/m/Y H:i', strtotime($subido['created_at'])) ?>
                        · <?= round($subido['tamano'] / 1024) ?> KB
                    </div>
                    <div class="doc-subido__acciones">
                        <a href="/uploads/documentos/<?= $subido['archivo'] ?>" target="_blank"
                           class="btn btn--outline btn--sm">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                        <!-- Permitir reemplazar -->
                        <button type="button" class="btn btn--outline btn--sm"
                                onclick="mostrarUpload('<?= $req['tipo'] ?>')">
                            <i class="fas fa-sync-alt"></i> Reemplazar
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Form de subida (oculto si ya hay archivo) -->
                <form method="POST" action="/candidato/documentos"
                      enctype="multipart/form-data"
                      class="doc-upload-form <?= $subido ? 'oculto' : '' ?>"
                      id="form-<?= $req['tipo'] ?>"
                      data-tipo="<?= $req['tipo'] ?>">
                    <?= csrf_field() ?>

                    <input type="hidden" name="tipo" value="<?= $req['tipo'] ?>">

                    <div class="drop-area" id="drop-<?= $req['tipo'] ?>"
                         onclick="document.getElementById('file-<?= $req['tipo'] ?>').click()">
                        <i class="fas fa-cloud-upload-alt drop-area__icono"></i>
                        <p>Arrastra el archivo aquí o <strong>haz clic para seleccionar</strong></p>
                        <small><?= $req['formatos'] ?> · Máx. 5 MB</small>
                        <div class="drop-area__preview" id="preview-<?= $req['tipo'] ?>"></div>
                    </div>

                    <input type="file" id="file-<?= $req['tipo'] ?>" name="archivo"
                           accept=".pdf,.jpg,.jpeg,.png" class="file-input-hidden"
                           onchange="previsualizarArchivo(this, '<?= $req['tipo'] ?>')">

                    <div class="doc-upload-form__btns">
                        <button type="submit" class="btn btn--verde" disabled
                                id="btn-upload-<?= $req['tipo'] ?>">
                            <i class="fas fa-upload"></i> Subir documento
                        </button>
                        <?php if ($subido): ?>
                        <button type="button" class="btn btn--outline"
                                onclick="ocultarUpload('<?= $req['tipo'] ?>')">
                            Cancelar
                        </button>
                        <?php endif; ?>
                    </div>

                    <!-- Barra de progreso de subida -->
                    <div class="upload-progress oculto" id="progress-<?= $req['tipo'] ?>">
                        <div class="upload-progress__bar">
                            <div class="upload-progress__fill" id="fill-<?= $req['tipo'] ?>"></div>
                        </div>
                        <span id="pct-<?= $req['tipo'] ?>">0%</span>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Resumen de estado -->
        <div class="docs-resumen">
            <?php
            $total_req    = count(array_filter($docs_requeridos, fn($d) => $d['obligatorio']));
            $total_subidos = count(array_filter($docs_requeridos, fn($d) => $d['obligatorio'] && isset($subidos[$d['tipo']])));
            ?>
            <div class="docs-resumen__pct">
                <strong><?= $total_subidos ?>/<?= $total_req ?></strong> documentos obligatorios subidos
            </div>
            <?php if ($total_subidos >= $total_req): ?>
            <div class="alerta alerta--exito">
                <i class="fas fa-check-circle"></i>
                ¡Todos los documentos obligatorios han sido subidos! Puedes continuar con el test vocacional.
                <a href="/candidato/test" class="btn btn--verde btn--sm" style="margin-left:1rem">
                    Ir al test <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<script>
// ── Previsualizar archivo seleccionado ─────────────────────
function previsualizarArchivo(input, tipo) {
    const file    = input.files[0];
    const preview = document.getElementById(`preview-${tipo}`);
    const btnUp   = document.getElementById(`btn-upload-${tipo}`);

    if (!file) return;

    // Validar tamaño (5 MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('El archivo supera los 5 MB permitidos.');
        input.value = '';
        return;
    }

    preview.textContent = `✓ ${file.name} (${(file.size / 1024).toFixed(0)} KB)`;
    btnUp.disabled = false;
}

// ── Drag & Drop ───────────────────────────────────────────
document.querySelectorAll('.drop-area').forEach(zona => {
    const tipo = zona.id.replace('drop-', '');

    zona.addEventListener('dragover', e => {
        e.preventDefault(); zona.classList.add('arrastrando');
    });
    zona.addEventListener('dragleave', () => zona.classList.remove('arrastrando'));
    zona.addEventListener('drop', e => {
        e.preventDefault(); zona.classList.remove('arrastrando');
        const files = e.dataTransfer.files;
        if (files.length) {
            const input = document.getElementById(`file-${tipo}`);
            input.files = files;
            previsualizarArchivo(input, tipo);
        }
    });
});

// ── Simular progreso durante el submit ────────────────────
document.querySelectorAll('.doc-upload-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const tipo     = this.dataset.tipo;
        const progWrap = document.getElementById(`progress-${tipo}`);
        const fill     = document.getElementById(`fill-${tipo}`);
        const pct      = document.getElementById(`pct-${tipo}`);

        progWrap.classList.remove('oculto');
        let v = 0;
        const interval = setInterval(() => {
            v = Math.min(v + Math.random() * 15, 90);
            fill.style.width  = v + '%';
            pct.textContent   = Math.round(v) + '%';
        }, 200);

        // Limpiar al cargar (el form se envía normalmente)
        window.addEventListener('beforeunload', () => clearInterval(interval));
    });
});

// ── Mostrar/ocultar form de reemplazo ─────────────────────
function mostrarUpload(tipo) {
    document.getElementById(`form-${tipo}`)?.classList.remove('oculto');
}
function ocultarUpload(tipo) {
    document.getElementById(`form-${tipo}`)?.classList.add('oculto');
}
</script>
