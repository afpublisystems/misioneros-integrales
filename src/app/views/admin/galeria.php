<?php
// Flash messages
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$es_admin = $_SESSION['usuario_rol'] === 'admin';
?>

<div class="admin-layout">

    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main">

<div class="admin-header">
    <div>
        <h1><?= htmlspecialchars($titulo) ?></h1>
        <p>Administra las fotos y videos de cada sede del itinerario</p>
    </div>
    <?php if ($es_admin): ?>
    <button type="button" class="btn btn--outline" onclick="alternarCiudades()">
        <i class="fas fa-city"></i> Editar ciudades
    </button>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
<div class="alerta alerta--<?= $flash['tipo'] ?>">
    <i class="fas <?= $flash['tipo'] === 'exito' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<?php if ($es_admin): ?>
<!-- Ciudades del itinerario ─────────────────────────────────── -->
<section class="admin-panel" id="panel-ciudades" style="display:none;margin-bottom:1.5rem">
    <div class="admin-panel__header">
        <h2><i class="fas fa-city"></i> Ciudades del itinerario</h2>
        <button type="button" class="btn btn--sm btn--verde" onclick="abrirCiudad(null)">
            <i class="fas fa-plus"></i> Agregar ciudad
        </button>
    </div>
    <p class="ciudades-nota">
        Las activas salen en la galería pública y en el registro de evangelismo, en este orden.
        Una ciudad no se borra porque puede tener fotos y personas registradas: si ya no van, desactívala.
    </p>
    <div class="tabla-wrap">
        <table class="tabla">
            <thead>
                <tr><th>Orden</th><th>Ciudad</th><th>Estado</th><th>Meses</th><th>Fechas</th><th></th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($sedes as $sede): ?>
            <tr class="<?= $sede['activa'] ? '' : 'ciudad-inactiva' ?>">
                <td><?= (int) $sede['orden'] ?></td>
                <td><strong><?= htmlspecialchars($sede['nombre']) ?></strong></td>
                <td><?= htmlspecialchars($sede['estado']) ?></td>
                <td><?= htmlspecialchars($sede['mes']) ?></td>
                <td style="white-space:nowrap">
                    <?php if ($sede['fecha_inicio'] || $sede['fecha_fin']): ?>
                    <?= $sede['fecha_inicio'] ? date('d/m/Y', strtotime($sede['fecha_inicio'])) : '…' ?>
                    – <?= $sede['fecha_fin'] ? date('d/m/Y', strtotime($sede['fecha_fin'])) : '…' ?>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <span class="badge <?= $sede['activa'] ? 'badge--aprobada' : 'badge--borrador' ?>">
                        <?= $sede['activa'] ? 'Activa' : 'Inactiva' ?>
                    </span>
                </td>
                <td>
                    <button type="button" class="btn btn--sm btn--outline" title="Editar"
                            data-ciudad="<?= htmlspecialchars(json_encode([
                                'id'           => $sede['id'],
                                'nombre'       => $sede['nombre'],
                                'estado'       => $sede['estado'],
                                'mes'          => $sede['mes'],
                                'orden'        => $sede['orden'],
                                'fecha_inicio' => $sede['fecha_inicio'],
                                'fecha_fin'    => $sede['fecha_fin'],
                                'activa'       => (int) $sede['activa'],
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-pen"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Modal: ciudad ───────────────────────────────────────────── -->
<div class="modal-overlay" id="modal-ciudad" style="display:none"
     onclick="if (event.target === this) cerrarCiudad()">
    <div class="modal" style="max-width:560px">
        <div class="modal__header">
            <h3><i class="fas fa-city"></i> <span id="ciudad-titulo">Agregar ciudad</span></h3>
            <button type="button" class="modal__cerrar" onclick="cerrarCiudad()">&times;</button>
        </div>
        <form method="POST" action="/admin/galeria/sede" id="form-ciudad">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="modal__body">
                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Ciudad <span class="req">*</span></label>
                        <input type="text" name="nombre" maxlength="100" required placeholder="Ej: Los Teques">
                    </div>
                    <div class="form-grupo">
                        <label>Estado <span class="req">*</span></label>
                        <input type="text" name="estado" maxlength="100" required placeholder="Ej: Miranda">
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Meses <span class="req">*</span></label>
                        <input type="text" name="mes" maxlength="50" required placeholder="Ej: Septiembre">
                    </div>
                    <div class="form-grupo">
                        <label>Orden en el itinerario <span class="req">*</span></label>
                        <input type="number" name="orden" min="1" max="99" required>
                    </div>
                </div>
                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Desde</label>
                        <input type="date" name="fecha_inicio">
                    </div>
                    <div class="form-grupo">
                        <label>Hasta</label>
                        <input type="date" name="fecha_fin">
                    </div>
                </div>
                <p class="ciudades-nota" style="padding:0;margin:-.5rem 0 1rem">
                    Con las fechas, el registro de evangelismo marca sola esta ciudad mientras el grupo esté ahí.
                </p>
                <label class="check-label">
                    <input type="checkbox" name="activa" value="1" checked>
                    <span><strong>Activa</strong>: sale en la galería pública y en evangelismo</span>
                </label>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarCiudad()">Cancelar</button>
                <button type="submit" class="btn btn--verde"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Sedes ──────────────────────────────────────────────────── -->
<div class="galeria-sedes-grid">
    <?php foreach ($sedes as $sede): ?>
    <?php $activa = $sede_sel && $sede_sel['id'] == $sede['id']; ?>
    <a href="/admin/galeria?sede=<?= $sede['id'] ?>"
       class="galeria-sede-card <?= $activa ? 'galeria-sede-card--activa' : '' ?> <?= $sede['activa'] ? '' : 'galeria-sede-card--inactiva' ?>">
        <div class="galeria-sede-card__icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="galeria-sede-card__info">
            <div class="galeria-sede-card__nombre"><?= htmlspecialchars($sede['nombre']) ?></div>
            <div class="galeria-sede-card__estado">
                <?= $sede['activa'] ? htmlspecialchars($sede['estado'] . ' · ' . $sede['mes']) : 'Inactiva · no sale en el sitio' ?>
            </div>
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

        <form method="POST" action="/admin/galeria" enctype="multipart/form-data" class="galeria-form" id="form-subir">
            <?= csrf_field() ?>
            <input type="hidden" name="accion"   value="subir">
            <input type="hidden" name="sede_id"  value="<?= $sede_sel['id'] ?>">
            <input type="hidden" name="tipo"     id="tipo-hidden" value="foto">

            <div class="galeria-form__row">
                <div class="form-grupo" style="flex:1">
                    <label>Título *</label>
                    <input type="text" name="titulo" required placeholder="Ej. Inauguración sede La Guaira">
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
                <small style="color:#64748b">Si subes varias fotos o videos juntos, todos llevan este título y esta descripción.</small>
            </div>

            <!-- Zona foto -->
            <div id="zona-foto">
                <div class="form-grupo">
                    <label>Fotos (JPG, PNG, WEBP o GIF)</label>
                    <div class="galeria-dropzone" id="dropzone"
                         ondragover="event.preventDefault()" ondrop="soltarArchivo(event)">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span id="dropzone-label">Arrastra las fotos aquí o haz clic para elegirlas. Puedes elegir varias.</span>
                        <input type="file" id="archivo-input" accept="image/*" multiple
                               style="display:none" onchange="agregarFotos(this.files); this.value = ''">
                    </div>
                    <small style="color:#64748b">Las fotos pesadas se achican solas antes de subir, sin que se note en pantalla.</small>
                    <div class="galeria-cola" id="cola-fotos"></div>
                    <div class="galeria-cola__resumen" id="cola-resumen" hidden></div>
                </div>
            </div>

            <!-- Zona video -->
            <div id="zona-video" style="display:none">
                <div class="form-grupo">
                    <label>Enlaces de los videos (YouTube o Vimeo)</label>
                    <textarea name="video_url" rows="3" style="min-height:0"
                              placeholder="https://www.youtube.com/watch?v=...&#10;Uno por línea si son varios"></textarea>
                    <small style="color:#64748b">La miniatura se saca sola de YouTube.</small>
                </div>
            </div>

            <div class="galeria-form__footer">
                <button type="submit" class="btn btn--verde" id="btn-subir">
                    <i class="fas fa-save"></i> <span id="btn-subir-texto">Guardar</span>
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

    </main>
</div>

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
.galeria-sede-card--inactiva { opacity: .55; border-style: dashed; }

.ciudades-nota { font-size: .82rem; color: #64748b; padding: 1rem 1.25rem 0; line-height: 1.5; }
.ciudad-inactiva td { color: #94a3b8; }

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

.galeria-cola { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .6rem; margin-top: .75rem; }
.galeria-cola:empty { display: none; }
.cola-item { position: relative; border: 2px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #fff; }
.cola-item img { width: 100%; height: 90px; object-fit: cover; display: block; }
.cola-item__info { padding: .35rem .45rem; font-size: .7rem; line-height: 1.35; color: #475569; }
.cola-item__nombre { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 600; }
.cola-item__quitar {
    position: absolute; top: 4px; right: 4px; width: 22px; height: 22px;
    border: none; border-radius: 50%; background: rgba(15,23,42,.7); color: #fff;
    cursor: pointer; font-size: .75rem; line-height: 22px; padding: 0;
}
.cola-item--subiendo { border-color: var(--dorado); }
.cola-item--lista { border-color: var(--verde); }
.cola-item--lista .cola-item__estado { color: var(--verde-dark); font-weight: 600; }
.cola-item--error { border-color: #ef4444; }
.cola-item--error .cola-item__estado { color: #b91c1c; font-weight: 600; }
.galeria-cola__resumen {
    margin-top: .75rem; padding: .7rem .9rem; border-radius: 8px;
    background: #fef2f2; color: #991b1b; font-size: .85rem;
}
.galeria-cola__resumen a { color: inherit; font-weight: 700; }
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
function alternarCiudades() {
    var panel = document.getElementById('panel-ciudades');
    panel.style.display = panel.style.display === 'none' ? '' : 'none';
}
function abrirCiudad(datos) {
    var form = document.getElementById('form-ciudad');
    form.reset();
    form.elements.id.value = '';
    document.getElementById('ciudad-titulo').textContent = datos ? 'Editar ciudad' : 'Agregar ciudad';
    if (datos) {
        Object.keys(datos).forEach(function (campo) {
            var el = form.elements[campo];
            if (!el) return;
            if (el.type === 'checkbox') el.checked = datos[campo] == 1;
            else el.value = datos[campo] === null ? '' : datos[campo];
        });
    }
    document.getElementById('modal-ciudad').style.display = 'flex';
}
function cerrarCiudad() {
    document.getElementById('modal-ciudad').style.display = 'none';
}
document.querySelectorAll('[data-ciudad]').forEach(function (b) {
    b.addEventListener('click', function () { abrirCiudad(JSON.parse(b.dataset.ciudad)); });
});

function cambiarTipoMedia(tipo, btn) {
    document.getElementById('tipo-hidden').value = tipo;
    document.getElementById('zona-foto').style.display  = tipo === 'foto'  ? '' : 'none';
    document.getElementById('zona-video').style.display = tipo === 'video' ? '' : 'none';

    document.querySelectorAll('.galeria-tab').forEach(t => t.classList.remove('galeria-tab--activo'));
    btn.classList.add('galeria-tab--activo');
    actualizarBoton();
}

// Click en dropzone abre selector de archivo
document.getElementById('dropzone')?.addEventListener('click', () => {
    document.getElementById('archivo-input').click();
});

// ── Varias fotos: se comprimen en el navegador y suben de a una ──
// Así se ahorran datos, y si una falla las demás siguen.
const MAX_LADO = 1920;
const CALIDAD  = 0.82;
const UN_MB    = 1024 * 1024;
let cola = [];

function pesoLegible(bytes) {
    return bytes >= UN_MB ? (bytes / UN_MB).toFixed(1) + ' MB' : Math.round(bytes / 1024) + ' KB';
}

function agregarFotos(archivos) {
    [...archivos].filter(f => f.type.startsWith('image/')).forEach(file => {
        const nodo = document.createElement('div');
        nodo.className = 'cola-item';
        nodo.innerHTML = '<img alt=""><button type="button" class="cola-item__quitar" title="Quitar">&times;</button>'
                       + '<div class="cola-item__info"><div class="cola-item__nombre"></div><div class="cola-item__estado"></div></div>';
        nodo.querySelector('img').src = URL.createObjectURL(file);
        nodo.querySelector('.cola-item__nombre').textContent = file.name;
        nodo.querySelector('.cola-item__estado').textContent = pesoLegible(file.size);

        const item = { file, nodo };
        nodo.querySelector('.cola-item__quitar').onclick = () => {
            cola = cola.filter(i => i !== item);
            nodo.remove();
            actualizarBoton();
        };
        cola.push(item);
        document.getElementById('cola-fotos').appendChild(nodo);
    });
    document.getElementById('cola-resumen').hidden = true;
    actualizarBoton();
}

function soltarArchivo(e) {
    e.preventDefault();
    agregarFotos(e.dataTransfer.files);
}

function actualizarBoton() {
    const esFoto = document.getElementById('tipo-hidden').value === 'foto';
    document.getElementById('btn-subir-texto').textContent =
        esFoto && cola.length > 1 ? 'Subir ' + cola.length + ' fotos' : 'Guardar';
}

// Achica la foto a MAX_LADO px por el lado más largo y la pasa a JPEG.
// Si ya es liviana, si es un GIF (puede ser animado) o si el navegador
// no la puede leer, sube la original.
async function comprimir(file) {
    if (file.type === 'image/gif') return file;
    let img;
    try {
        img = await createImageBitmap(file);
    } catch (e) {
        return file;
    }
    const escala = Math.min(1, MAX_LADO / Math.max(img.width, img.height));
    if (escala === 1 && file.size <= UN_MB) return file;

    const canvas = document.createElement('canvas');
    canvas.width  = Math.round(img.width * escala);
    canvas.height = Math.round(img.height * escala);
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = '#fff';                 // lo transparente de un PNG queda en blanco
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

    const blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', CALIDAD));
    if (!blob || blob.size >= file.size) return file;
    return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
}

document.getElementById('form-subir')?.addEventListener('submit', async e => {
    if (document.getElementById('tipo-hidden').value !== 'foto') return;   // los videos van por el envío normal
    e.preventDefault();
    if (!cola.length) {
        alert('Elige al menos una foto.');
        return;
    }

    const form    = e.target;
    const boton   = document.getElementById('btn-subir');
    const resumen = document.getElementById('cola-resumen');
    const total   = cola.length;
    let subidas   = 0;

    boton.disabled = true;
    resumen.hidden = true;

    for (const [i, item] of [...cola].entries()) {
        const estado = item.nodo.querySelector('.cola-item__estado');
        item.nodo.querySelector('.cola-item__quitar').hidden = true;
        item.nodo.className = 'cola-item cola-item--subiendo';
        document.getElementById('btn-subir-texto').textContent = 'Subiendo ' + (i + 1) + ' de ' + total + '…';

        estado.textContent = 'Comprimiendo…';
        const archivo = await comprimir(item.file);
        estado.textContent = archivo === item.file
            ? 'Subiendo ' + pesoLegible(archivo.size) + '…'
            : pesoLegible(item.file.size) + ' → ' + pesoLegible(archivo.size) + '…';

        const datos = new FormData(form);
        datos.append('archivo', archivo);
        datos.append('ajax', '1');

        let ok = false, msg = '';
        try {
            const resp = await fetch(form.action, { method: 'POST', body: datos });
            const json = await resp.json().catch(() => null);
            ok  = resp.ok && json && json.ok;
            msg = json ? json.msg : 'El servidor no la aceptó (' + resp.status + ')';
        } catch (err) {
            msg = 'Sin conexión';
        }

        if (ok) {
            subidas++;
            item.nodo.className = 'cola-item cola-item--lista';
            estado.textContent = 'Lista ✓';
            cola = cola.filter(c => c !== item);
        } else {
            item.nodo.className = 'cola-item cola-item--error';
            estado.textContent = msg;
            item.nodo.querySelector('.cola-item__quitar').hidden = false;
        }
    }

    if (subidas === total) {
        location.reload();
        return;
    }
    // Quedan en la cola solo las que fallaron, listas para reintentar
    document.querySelectorAll('.cola-item--lista').forEach(n => n.remove());
    resumen.innerHTML = 'Se subieron ' + subidas + ' de ' + total + '. Las marcadas en rojo no subieron: '
                      + 'puedes quitarlas o darle a Subir otra vez. '
                      + (subidas ? '<a href="">Ver las que sí subieron</a>' : '');
    resumen.hidden = false;
    boton.disabled = false;
    actualizarBoton();
});
</script>
