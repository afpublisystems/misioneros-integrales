<?php
/**
 * Vista: /galeria
 * Descripción: Galería multimedia por sede — Misioneros Integrales CNBV/DIME
 * Layout: main.php
 */
?>

<!-- ═══════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════ -->
<section class="gal-hero">
    <div class="gal-hero__overlay"></div>
    <div class="gal-hero__content">
        <span class="gal-hero__etiqueta">CNBV · DIME · Venezuela</span>
        <h1 class="gal-hero__titulo">Galería del<br><em>Programa</em></h1>
        <p class="gal-hero__sub">Conoce el impacto visual de Misioneros Integrales en cada sede</p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     FILTRO POR SEDE
═══════════════════════════════════════════════ -->
<section class="seccion seccion--crema" style="padding-top:2rem; padding-bottom:2rem;">
    <div class="container">
        <div class="gal-filtros">
            <button class="gal-filtro activo" data-sede="todas">
                <i class="fas fa-th-large"></i> Todas las sedes
            </button>
            <?php foreach ($sedes as $sede): ?>
            <button class="gal-filtro" data-sede="<?= $sede['id'] ?>">
                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($sede['nombre']) ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     GALERÍA PRINCIPAL
═══════════════════════════════════════════════ -->
<section class="seccion" style="padding-top:1.5rem;">
    <div class="container">

        <?php if (empty($multimedia)): ?>
        <!-- Estado vacío — programa aún no ha iniciado -->
        <div class="gal-vacia">
            <div class="gal-vacia__icono"><i class="fas fa-images"></i></div>
            <h2>La galería se irá llenando</h2>
            <p>
                El Ciclo 1 del programa inicia en <strong>Julio 2026</strong>.
                Aquí publicaremos fotos y videos de cada sede a medida que avancemos por Venezuela.
            </p>
            <p style="font-size:0.9rem; color:#6b7280; margin-top:0.5rem;">
                Sedes del itinerario: Los Teques · Maracay · San Felipe · Valencia · Acarigua · Barquisimeto · Trujillo
            </p>
            <a href="/registro" class="btn-cta-naranja" style="margin-top:1.5rem;">
                <i class="fas fa-user-plus"></i> Sé parte de este programa
            </a>
        </div>
        <?php else: ?>
        <!-- Grid de medios -->
        <div class="gal-grid" id="gal-grid">
            <?php foreach ($multimedia as $item): ?>
            <div class="gal-item <?= !$item['activo'] ? 'gal-item--inactiva' : '' ?>"
                 data-sede="<?= $item['sede_id'] ?>"
                 data-tipo="<?= $item['tipo'] ?>">
                <?php if ($item['tipo'] === 'video'): ?>
                    <!-- Video embed -->
                    <div class="gal-item__video">
                        <div class="gal-item__video-thumb"
                             onclick="abrirVideo('<?= htmlspecialchars($item['url']) ?>', '<?= htmlspecialchars(addslashes($item['titulo'])) ?>')"
                             style="background-image:url('<?= htmlspecialchars($item['thumb_url'] ?? '') ?>')">
                            <div class="gal-item__play"><i class="fas fa-play-circle"></i></div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Foto -->
                    <div class="gal-item__foto"
                         onclick="abrirFoto('<?= htmlspecialchars($item['url']) ?>', '<?= htmlspecialchars(addslashes($item['titulo'])) ?>', '<?= htmlspecialchars(addslashes($item['descripcion'] ?? '')) ?>')"
                         style="background-image:url('<?= htmlspecialchars($item['url']) ?>')">
                        <?php if ($item['destacado']): ?>
                        <span class="gal-item__destacado"><i class="fas fa-star"></i></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="gal-item__info">
                    <div class="gal-item__titulo"><?= htmlspecialchars($item['titulo']) ?></div>
                    <div class="gal-item__sede">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($item['sede_nombre'] ?? '') ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!-- ═══════════════════════════════════════════════
     ITINERARIO — 7 SEDES
═══════════════════════════════════════════════ -->
<section class="seccion seccion--crema">
    <div class="container">
        <div class="seccion__encabezado">
            <span class="seccion-label">Itinerario</span>
            <h2 class="seccion__titulo">Las 7 Sedes del Programa</h2>
            <p class="seccion__subtitulo">Ciclo 1 · Julio 2026 — Febrero 2027</p>
        </div>

        <div class="sedes-grid">
            <?php
            $sedes_iconos = ['fa-city','fa-mountain','fa-water','fa-building','fa-tractor','fa-sun','fa-cloud-sun'];
            foreach ($sedes as $i => $sede):
            ?>
            <div class="sede-card">
                <div class="sede-card__orden"><?= $sede['orden'] ?></div>
                <div class="sede-card__icono">
                    <i class="fas <?= $sedes_iconos[$i] ?? 'fa-map-marker-alt' ?>"></i>
                </div>
                <h3 class="sede-card__nombre"><?= htmlspecialchars($sede['nombre']) ?></h3>
                <div class="sede-card__estado"><?= htmlspecialchars($sede['estado']) ?></div>
                <div class="sede-card__mes">
                    <i class="fas fa-calendar-alt"></i>
                    <?= htmlspecialchars($sede['mes']) ?> <?= !empty($sede['fecha_inicio']) ? date('Y', strtotime($sede['fecha_inicio'])) : '' ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     CTA
═══════════════════════════════════════════════ -->
<section class="seccion seccion--verde">
    <div class="container" style="text-align:center;">
        <h2 class="seccion__titulo">¿Quieres ser parte de la historia?</h2>
        <p class="seccion__subtitulo" style="max-width:500px; margin:0 auto 2rem;">
            Postúlate para el Ciclo 1 y deja tu huella en cada una de las 7 sedes.
        </p>
        <a href="/registro" class="btn-cta-naranja">
            <i class="fas fa-user-plus"></i> Postularme ahora
        </a>
    </div>
</section>

<!-- Modal foto ───────────────────────────────────────── -->
<div class="gal-modal" id="gal-modal" style="display:none;" onclick="cerrarModal()">
    <div class="gal-modal__box" onclick="event.stopPropagation()">
        <button class="gal-modal__cerrar" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
        <img src="" alt="" id="gal-modal-img" class="gal-modal__img">
        <div class="gal-modal__caption">
            <strong id="gal-modal-titulo"></strong>
            <span id="gal-modal-desc"></span>
        </div>
    </div>
</div>

<!-- Modal video ──────────────────────────────────────── -->
<div class="gal-modal" id="gal-modal-video" style="display:none;" onclick="cerrarModalVideo()">
    <div class="gal-modal__box gal-modal__box--video" onclick="event.stopPropagation()">
        <button class="gal-modal__cerrar" onclick="cerrarModalVideo()"><i class="fas fa-times"></i></button>
        <div class="gal-modal__iframe-wrap">
            <iframe id="gal-modal-iframe" src="" frameborder="0" allowfullscreen></iframe>
        </div>
        <div class="gal-modal__caption">
            <strong id="gal-modal-video-titulo"></strong>
        </div>
    </div>
</div>

<script>
// Filtros por sede
document.querySelectorAll('.gal-filtro').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.gal-filtro').forEach(b => b.classList.remove('activo'));
        this.classList.add('activo');
        const sede = this.dataset.sede;
        document.querySelectorAll('.gal-item').forEach(item => {
            if (sede === 'todas' || item.dataset.sede === sede) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
});

function abrirFoto(url, titulo, desc) {
    document.getElementById('gal-modal-img').src   = url;
    document.getElementById('gal-modal-titulo').textContent = titulo;
    document.getElementById('gal-modal-desc').textContent   = desc;
    document.getElementById('gal-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function cerrarModal() {
    document.getElementById('gal-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function abrirVideo(url, titulo) {
    document.getElementById('gal-modal-iframe').src = url;
    document.getElementById('gal-modal-video-titulo').textContent = titulo;
    document.getElementById('gal-modal-video').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function cerrarModalVideo() {
    document.getElementById('gal-modal-iframe').src = '';
    document.getElementById('gal-modal-video').style.display = 'none';
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { cerrarModal(); cerrarModalVideo(); }
});
</script>

<style>
/* ── Hero ─────────────────────────────────────────────── */
.gal-hero {
    position: relative; min-height: 50vh;
    background: linear-gradient(160deg, #0f5a45 0%, #167a5e 50%, #1a9070 100%);
    display: flex; align-items: center; justify-content: center; text-align: center;
    overflow: hidden;
}
.gal-hero__overlay {
    position: absolute; inset: 0;
    background: repeating-linear-gradient(
        -45deg, transparent, transparent 20px,
        rgba(255,255,255,0.02) 20px, rgba(255,255,255,0.02) 40px
    );
}
.gal-hero__content { position: relative; z-index: 1; padding: 4rem 1.5rem; }
.gal-hero__etiqueta {
    display: inline-block; font-size: 0.75rem; font-weight: 700;
    letter-spacing: 0.15em; color: var(--dorado); text-transform: uppercase;
    background: rgba(255,255,255,0.1); padding: 0.3rem 1rem; border-radius: 2rem; margin-bottom: 1.25rem;
}
.gal-hero__titulo {
    font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 900; color: white; line-height: 1.15; margin-bottom: 1rem;
}
.gal-hero__titulo em { color: var(--dorado); font-style: normal; }
.gal-hero__sub { color: rgba(255,255,255,0.85); font-size: 1.05rem; }

/* ── Filtros ──────────────────────────────────────────── */
.gal-filtros {
    display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: center;
}
.gal-filtro {
    background: white; border: 2px solid #e2e8f0; color: #64748b;
    padding: 0.5rem 1.1rem; border-radius: 2rem; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
}
.gal-filtro:hover { border-color: var(--verde); color: var(--verde); }
.gal-filtro.activo { background: var(--verde); border-color: var(--verde); color: white; }

/* ── Grid galería ─────────────────────────────────────── */
.gal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.25rem;
}
.gal-item { border-radius: 0.75rem; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08); background: white; }
.gal-item__foto {
    height: 200px; background-size: cover; background-position: center;
    cursor: pointer; position: relative; transition: transform 0.3s;
    background-color: var(--verde-light);
}
.gal-item__foto:hover { transform: scale(1.02); }
.gal-item__destacado {
    position: absolute; top: 0.75rem; right: 0.75rem;
    background: var(--dorado); color: white;
    width: 1.75rem; height: 1.75rem; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; font-size: 0.7rem;
}
.gal-item__video-thumb {
    height: 200px; background-size: cover; background-position: center;
    cursor: pointer; position: relative; background-color: #1e293b;
    display: flex; align-items: center; justify-content: center;
}
.gal-item__play {
    font-size: 3.5rem; color: white; opacity: 0.9; transition: transform 0.2s, opacity 0.2s;
}
.gal-item__video-thumb:hover .gal-item__play { transform: scale(1.1); opacity: 1; }
.gal-item__info { padding: 0.75rem 1rem; }
.gal-item__titulo { font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 0.25rem; }
.gal-item__sede { font-size: 0.75rem; color: var(--gris); }
.gal-item__sede i { color: var(--verde); margin-right: 0.25rem; }

/* ── Estado vacío ─────────────────────────────────────── */
.gal-vacia {
    text-align: center; padding: 4rem 2rem;
    background: white; border-radius: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.gal-vacia__icono { font-size: 4rem; color: #d1d5db; margin-bottom: 1.5rem; }
.gal-vacia h2 { font-size: 1.5rem; color: var(--verde-dark); margin-bottom: 0.75rem; }
.gal-vacia p { color: #6b7280; line-height: 1.7; max-width: 500px; margin: 0 auto; }

/* ── Sedes grid ───────────────────────────────────────── */
.sedes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem; margin-top: 2.5rem;
}
.sede-card {
    background: white; border-radius: 0.75rem; padding: 1.5rem 1rem;
    text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    border-bottom: 3px solid var(--verde); position: relative;
    transition: transform 0.2s;
}
.sede-card:hover { transform: translateY(-3px); }
.sede-card__orden {
    position: absolute; top: 0.6rem; right: 0.8rem;
    font-size: 1.5rem; font-weight: 900; color: #f1f5f9;
}
.sede-card__icono {
    width: 2.75rem; height: 2.75rem; background: var(--verde-light); color: var(--verde);
    border-radius: 0.6rem; display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; margin: 0 auto 0.75rem;
}
.sede-card__nombre { font-size: 1rem; font-weight: 700; color: var(--verde-dark); margin-bottom: 0.2rem; }
.sede-card__estado { font-size: 0.75rem; color: #94a3b8; margin-bottom: 0.5rem; }
.sede-card__mes { font-size: 0.75rem; color: var(--dorado-dark); font-weight: 600; }
.sede-card__mes i { margin-right: 0.25rem; }

/* ── Modals ───────────────────────────────────────────── */
.gal-modal {
    position: fixed; inset: 0; background: rgba(0,0,0,0.9);
    display: flex; align-items: center; justify-content: center; z-index: 9999;
    padding: 1.5rem;
}
.gal-modal__box {
    background: white; border-radius: 1rem; overflow: hidden;
    max-width: 880px; width: 100%; position: relative; max-height: 90vh;
    display: flex; flex-direction: column;
}
.gal-modal__box--video { max-width: 900px; }
.gal-modal__cerrar {
    position: absolute; top: 0.75rem; right: 0.75rem; z-index: 10;
    width: 2rem; height: 2rem; background: rgba(0,0,0,0.6); color: white;
    border: none; border-radius: 50%; cursor: pointer; font-size: 0.85rem;
    display: flex; align-items: center; justify-content: center;
}
.gal-modal__img { width: 100%; max-height: 70vh; object-fit: contain; background: #000; }
.gal-modal__iframe-wrap { position: relative; padding-bottom: 56.25%; height: 0; }
.gal-modal__iframe-wrap iframe { position: absolute; inset: 0; width: 100%; height: 100%; }
.gal-modal__caption {
    padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 0.25rem;
}
.gal-modal__caption strong { font-size: 0.95rem; color: #1e293b; }
.gal-modal__caption span { font-size: 0.83rem; color: #6b7280; }

.seccion-label {
    display: inline-block; font-size: 0.72rem; font-weight: 700;
    letter-spacing: 0.12em; text-transform: uppercase; color: var(--verde);
    background: var(--verde-light); padding: 0.25rem 0.85rem;
    border-radius: 2rem; margin-bottom: 0.75rem;
}
.btn-cta-naranja {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--naranja); color: white; padding: 0.85rem 2rem;
    border-radius: 0.5rem; font-weight: 700; font-size: 1rem;
    transition: background 0.2s, transform 0.2s; text-decoration: none;
}
.btn-cta-naranja:hover { background: var(--naranja-dark); transform: translateY(-2px); }
</style>
