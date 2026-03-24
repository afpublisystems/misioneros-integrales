<?php
// Flash messages
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="admin-content-header">
    <h1><i class="fas fa-images"></i> <?= htmlspecialchars($titulo) ?></h1>
    <p class="admin-content-subtitle">Administra las fotos y videos de cada sede del itinerario</p>
</div>

<?php if ($flash): ?>
<div class="flash flash--<?= $flash['tipo'] ?>">
    <i class="fas <?= $flash['tipo'] === 'exito' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- Sedes ──────────────────────────────────────────────────── -->
<div class="galeria-sedes-grid">
    <?php foreach ($sedes as $sede): ?>
    <?php $activa = $sede_sel && $sede_sel['id'] == $sede['id']; ?>
    <a href="/admin/galeria?sede=<?= $sede['id'] ?>"
       class="galeria-sede-card <?= $activa ? 'galeria-sede-card--activa' : '' ?>">
        <div class="galeria-sede-card__icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="galeria-sede-card__info">
            <div class="galeria-sede-card__nombre"><?= htmlspecialchars($sede['nombre']) ?></div>
            <div class="galeria-sede-card__estado"><?= htmlspecialchars($sede['estado']) ?> · <?= htmlspecialchars($sede['mes']) ?></div>
        </div>
        <div class="galeria-sede-card__badge">
            <?= $sede['total_items'] ?> ítem<?= $sede['total_items'] != 1 ? 's' : '' ?>
        </div>
    </a>
    <?php endforeach; ?>
</div>

<?php if (!$sede_sel): ?>
<!-- Estado vacío — no hay sede seleccionada -->
<div class="galeria-empty">
    <i class="fas fa-hand-pointer"></i>
    <p>Selecciona una sede para gestionar su galería</p>
</div>

<?php else: ?>
<!-- Panel de gestión de la sede seleccionada ─────────────── -->
<div class="galeria-panel">

    <!-- Agregar ítem ─────────────────────────── -->
    <div class="admin-panel">
        <div class="admin-panel__header">
            <h2><i class="fas fa-plus-circle"></i> Agregar a <?= htmlspecialchars($sede_sel['nombre']) ?></h2>
        </div>
        <div class="galeria-add-tabs">
            <button type="button" class="galeria-tab galeria-tab--activo" onclick="cambiarTipoMedia('foto', this)">
                <i class="fas fa-image"></i> Foto
            </button>
            <button type="button" class="galeria-tab" onclick="cambiarTipoMedia('video', this)">
                <i class="fab fa-youtube"></i> Video
            </button>
        </div>

        <form method="POST" action="/admin/galeria" enctype="multipart/form-data" class="galeria-form">
            <?= csrf_field() ?>
            <input type="hidden" name="accion"   value="subir">
            <input type="hidden" name="sede_id"  value="<?= $sede_sel['id'] ?>">
            <input type="hidden" name="tipo"     id="tipo-hidden" value="foto">

            <div class="galeria-form__row">
                <div class="form-grupo" style="flex:1">
                    <label>Título *</label>
                    <input type="text" name="titulo" required placeholder="Ej. Inauguración sede Los Teques">
                </div>
                <div class="form-grupo" style="flex:0 0 140px">
                    <label>&nbsp;</label>
                    <label class="galeria-check-label">
                        <input type="checkbox" name="destacado" value="1"> Destacar
                    </label>
                </div>
            </div>

            <div class="form-grupo">
                <label>Descripción (opcional)</label>
                <input type="text" name="descripcion" placeholder="Breve descripción del contenido">
            </div>

            <!-- Zona foto -->
            <div id="zona-foto">
                <div class="form-grupo">
                    <label>Archivo de imagen (JPG, PNG, WEBP, GIF — máx. 5 MB)</label>
                    <div class="galeria-dropzone" id="dropzone"
                         ondragover="event.preventDefault()" ondrop="soltarArchivo(event)">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span id="dropzone-label">Arrastra una imagen aquí o haz click para seleccionar</span>
                        <input type="file" name="archivo" id="archivo-input" accept="image/*"
                               style="display:none" onchange="previsualizarFoto(this)">
                    </div>
                    <div id="foto-preview" style="display:none; margin-top:.5rem; text-align:center">
                        <img id="foto-prev-img" src="" alt="" style="max-height:140px; border-radius:6px; border:2px solid var(--verde)">
                        <div style="font-size:.75rem; color:#64748b; margin-top:.25rem" id="foto-prev-nombre"></div>
                    </div>
                </div>
            </div>

            <!-- Zona video -->
            <div id="zona-video" style="display:none">
                <div class="form-grupo">
                    <label>URL del video (YouTube o Vimeo)</label>
                    <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                    <small style="color:#64748b">El thumbnail se extrae automáticamente de YouTube</small>
                </div>
            </div>

            <div class="galeria-form__footer">
                <button type="submit" class="btn btn--verde">
                    <i class="fas fa-save"></i> Guardar ítem
                </button>
            </div>
        </form>
    </div>

    <!-- Grid de ítems existentes ─────────────── -->
    <div class="admin-panel">
        <div class="admin-panel__header">
            <h2><i class="fas fa-th"></i> Ítems de <?= htmlspecialchars($sede_sel['nombre']) ?>
                <span class="badge-count"><?= count($items) ?></span>
            </h2>
        </div>

        <?php if (empty($items)): ?>
        <div class="galeria-empty-items">
            <i class="fas fa-photo-video"></i>
            <p>Aún no hay fotos ni videos para esta sede</p>
        </div>
        <?php else: ?>
        <div class="galeria-items-grid">
            <?php foreach ($items as $item): ?>
            <div class="galeria-item <?= !$item['activo'] ? 'galeria-item--inactivo' : '' ?>">

                <!-- Thumbnail -->
                <div class="galeria-item__thumb">
                    <?php if ($item['tipo'] === 'video'): ?>
                        <?php if ($item['thumb_url']): ?>
                            <img src="<?= htmlspecialchars($item['thumb_url']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>">
                        <?php else: ?>
                            <div class="galeria-item__no-thumb">
                                <i class="fab fa-youtube"></i>
                            </div>
                        <?php endif; ?>
                        <div class="galeria-item__play"><i class="fas fa-play"></i></div>
                    <?php else: ?>
                        <img src="<?= htmlspecialchars($item['url']) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>">
                    <?php endif; ?>

                    <?php if ($item['destacado']): ?>
                    <div class="galeria-item__star"><i class="fas fa-star"></i></div>
                    <?php endif; ?>
                </div>

                <!-- Info -->
                <div class="galeria-item__info">
                    <div class="galeria-item__titulo"><?= htmlspecialchars($item['titulo']) ?></div>
                    <?php if ($item['descripcion']): ?>
                    <div class="galeria-item__desc"><?= htmlspecialchars($item['descripcion']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Acciones -->
                <div class="galeria-item__acciones">
                    <!-- Toggle activo -->
                    <form method="POST" action="/admin/galeria" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion"   value="toggle_activo">
                        <input type="hidden" name="sede_id"  value="<?= $sede_sel['id'] ?>">
                        <input type="hidden" name="item_id"  value="<?= $item['id'] ?>">
                        <button type="submit" class="galeria-accion-btn <?= $item['activo'] ? 'galeria-accion-btn--verde' : 'galeria-accion-btn--gris' ?>"
                                title="<?= $item['activo'] ? 'Desactivar' : 'Activar' ?>">
                            <i class="fas <?= $item['activo'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                        </button>
                    </form>
                    <!-- Toggle destacado -->
                    <form method="POST" action="/admin/galeria" style="display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion"   value="toggle_destacado">
                        <input type="hidden" name="sede_id"  value="<?= $sede_sel['id'] ?>">
                        <input type="hidden" name="item_id"  value="<?= $item['id'] ?>">
                        <button type="submit" class="galeria-accion-btn <?= $item['destacado'] ? 'galeria-accion-btn--dorado' : 'galeria-accion-btn--gris' ?>"
                                title="<?= $item['destacado'] ? 'Quitar destacado' : 'Destacar' ?>">
                            <i class="fas fa-star"></i>
                        </button>
                    </form>
                    <!-- Eliminar -->
                    <form method="POST" action="/admin/galeria" style="display:inline"
                          onsubmit="return confirm('¿Eliminar este ítem? Esta acción no se puede deshacer.')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion"   value="eliminar">
                        <input type="hidden" name="sede_id"  value="<?= $sede_sel['id'] ?>">
                        <input type="hidden" name="item_id"  value="<?= $item['id'] ?>">
                        <button type="submit" class="galeria-accion-btn galeria-accion-btn--rojo" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div><!-- /galeria-panel -->
<?php endif; ?>

<style>
/* ── Galería Admin ─────────────────────────────────────────── */
.galeria-sedes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .75rem;
    margin-bottom: 1.5rem;
}
.galeria-sede-card {
    display: flex;
    align-items: center;
    gap: .6rem;
    padding: .7rem .9rem;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    text-decoration: none;
    color: #334155;
    transition: all .2s;
}
.galeria-sede-card:hover { border-color: var(--verde); background: var(--verde-light); }
.galeria-sede-card--activa { border-color: var(--verde); background: var(--verde-light); box-shadow: 0 0 0 3px rgba(22,122,94,.15); }
.galeria-sede-card__icon { font-size: 1.25rem; color: var(--verde); flex-shrink: 0; }
.galeria-sede-card--activa .galeria-sede-card__icon { color: var(--verde-dark); }
.galeria-sede-card__nombre { font-weight: 600; font-size: .85rem; }
.galeria-sede-card__estado { font-size: .72rem; color: #64748b; }
.galeria-sede-card__badge { margin-left: auto; font-size: .7rem; background: #f1f5f9; color: #475569; padding: .15rem .45rem; border-radius: 999px; white-space: nowrap; }
.galeria-sede-card--activa .galeria-sede-card__badge { background: var(--verde); color: white; }

.galeria-empty {
    text-align: center;
    padding: 3rem 1rem;
    color: #94a3b8;
}
.galeria-empty i { font-size: 3rem; margin-bottom: .75rem; display: block; }

.galeria-panel { display: flex; flex-direction: column; gap: 1.25rem; }

/* ── Tabs foto/video ── */
.galeria-add-tabs { display: flex; gap: .5rem; padding: 0 1.25rem 1rem; }
.galeria-tab {
    padding: .4rem 1rem;
    border-radius: 6px;
    border: 2px solid #e2e8f0;
    background: white;
    color: #64748b;
    font-size: .85rem;
    cursor: pointer;
    transition: all .2s;
    font-family: inherit;
}
.galeria-tab--activo { border-color: var(--verde); background: var(--verde); color: white; }

/* ── Form subir ── */
.galeria-form { padding: 0 1.25rem 1.25rem; }
.galeria-form__row { display: flex; gap: 1rem; }
.galeria-form__footer { display: flex; justify-content: flex-end; margin-top: 1rem; }

.galeria-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    color: #94a3b8;
}
.galeria-dropzone:hover { border-color: var(--verde); color: var(--verde); background: var(--verde-light); }
.galeria-dropzone i { font-size: 1.75rem; display: block; margin-bottom: .5rem; }
.galeria-check-label {
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    font-size: .85rem;
    color: #475569;
    height: 42px;
    margin-top: .25rem;
}
.galeria-check-label:has(input:checked) { border-color: var(--dorado); background: #fffbeb; color: var(--dorado); }

/* ── Grid ítems ── */
.galeria-items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 1rem;
    padding: .5rem 1.25rem 1.25rem;
}
.galeria-item {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    background: white;
    transition: box-shadow .2s;
}
.galeria-item:hover { box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.galeria-item--inactivo { opacity: .5; }

.galeria-item__thumb {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    background: #f1f5f9;
    overflow: hidden;
}
.galeria-item__thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.galeria-item__no-thumb {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ef4444;
    font-size: 2.5rem;
    background: #fef2f2;
}
.galeria-item__play {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,.35);
    color: white;
    font-size: 1.5rem;
}
.galeria-item__star {
    position: absolute;
    top: .35rem;
    right: .35rem;
    color: var(--dorado);
    font-size: .85rem;
    text-shadow: 0 1px 3px rgba(0,0,0,.4);
}

.galeria-item__info {
    padding: .5rem .6rem;
}
.galeria-item__titulo {
    font-size: .78rem;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.galeria-item__desc {
    font-size: .7rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.galeria-item__acciones {
    display: flex;
    gap: .3rem;
    padding: .4rem .6rem;
    border-top: 1px solid #f1f5f9;
    justify-content: flex-end;
}
.galeria-accion-btn {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-size: .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}
.galeria-accion-btn--verde  { background: #dcfce7; color: #16a34a; }
.galeria-accion-btn--verde:hover  { background: #16a34a; color: white; }
.galeria-accion-btn--dorado { background: #fef9c3; color: var(--dorado); }
.galeria-accion-btn--dorado:hover { background: var(--dorado); color: white; }
.galeria-accion-btn--gris   { background: #f1f5f9; color: #94a3b8; }
.galeria-accion-btn--gris:hover   { background: #cbd5e1; color: #475569; }
.galeria-accion-btn--rojo   { background: #fee2e2; color: #ef4444; }
.galeria-accion-btn--rojo:hover   { background: #ef4444; color: white; }

.galeria-empty-items {
    text-align: center;
    padding: 2rem;
    color: #94a3b8;
}
.galeria-empty-items i { font-size: 2.5rem; display: block; margin-bottom: .5rem; }

.badge-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 .4rem;
    background: var(--verde);
    color: white;
    border-radius: 999px;
    font-size: .7rem;
    font-weight: 600;
    margin-left: .4rem;
}
</style>

<script>
function cambiarTipoMedia(tipo, btn) {
    document.getElementById('tipo-hidden').value = tipo;
    document.getElementById('zona-foto').style.display  = tipo === 'foto'  ? '' : 'none';
    document.getElementById('zona-video').style.display = tipo === 'video' ? '' : 'none';

    document.querySelectorAll('.galeria-tab').forEach(t => t.classList.remove('galeria-tab--activo'));
    btn.classList.add('galeria-tab--activo');
}

// Click en dropzone abre selector de archivo
document.getElementById('dropzone')?.addEventListener('click', () => {
    document.getElementById('archivo-input').click();
});

function previsualizarFoto(input) {
    if (!input.files.length) return;
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('foto-prev-img').src    = e.target.result;
        document.getElementById('foto-prev-nombre').textContent = file.name;
        document.getElementById('foto-preview').style.display = '';
        document.getElementById('dropzone-label').textContent  = '✓ Archivo seleccionado';
    };
    reader.readAsDataURL(file);
}

function soltarArchivo(e) {
    e.preventDefault();
    const dt    = e.dataTransfer;
    const input = document.getElementById('archivo-input');
    if (dt.files.length) {
        // Simular asignación al input de tipo file
        const list = new DataTransfer();
        list.items.add(dt.files[0]);
        input.files = list.files;
        previsualizarFoto(input);
    }
}
</script>
