<?php
/**
 * Vista: /cohorte-1
 * Los 14 participantes de la 1era cohorte, con su ciudad y su iglesia.
 * Layout: main.php
 */
?>

<!-- ═══════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════ -->
<section class="coh-hero">
    <div class="coh-hero__overlay"></div>
    <div class="coh-hero__content">
        <span class="coh-hero__etiqueta">1era Cohorte · Ciclo 1 · 2026 – 2027</span>
        <h1 class="coh-hero__titulo">Los que están<br><em>en la ruta</em></h1>
        <p class="coh-hero__sub">
            <?= count($participantes) ?> misioneros de distintas iglesias bautistas, formándose
            mientras acompañan a las iglesias de cinco ciudades del país.
        </p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     PARTICIPANTES
═══════════════════════════════════════════════ -->
<section class="seccion">
    <div class="container">

        <?php if (empty($participantes)): ?>
        <div class="seccion__encabezado">
            <h2 class="seccion__titulo">Pronto, los rostros de la cohorte</h2>
            <p class="seccion__subtitulo">Estamos terminando de reunir las fichas de los participantes.</p>
        </div>
        <?php else: ?>

        <div class="seccion__encabezado">
            <span class="seccion-label">Quiénes son</span>
            <h2 class="seccion__titulo">La primera cohorte</h2>
            <p class="seccion__subtitulo">
                Vienen de <?= $ciudades ?> ciudades y de <?= $iglesias ?> iglesias.
                Durante ocho meses viven, estudian y sirven juntos.
            </p>
        </div>

        <div class="coh-grid">
            <?php foreach ($participantes as $p): ?>
            <article class="coh-card">
                <div class="coh-card__foto">
                    <?php if (!empty($p['foto_publica'])): ?>
                    <img src="/public/assets/cohorte1/<?= htmlspecialchars($p['foto_publica']) ?>"
                         alt="<?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?>"
                         loading="lazy" width="800" height="1000">
                    <?php else: ?>
                    <span class="coh-card__inicial">
                        <?= htmlspecialchars(mb_substr($p['nombres'], 0, 1) . mb_substr($p['apellidos'], 0, 1)) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="coh-card__datos">
                    <h3 class="coh-card__nombre"><?= htmlspecialchars($p['nombre_corto']) ?></h3>
                    <?php if (!empty($p['ciudad'])): ?>
                    <p class="coh-card__ciudad">
                        <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['ciudad']) ?>
                    </p>
                    <?php endif; ?>
                    <?php if (!empty($p['iglesia'])): ?>
                    <p class="coh-card__iglesia"><?= htmlspecialchars($p['iglesia']) ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php endif; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     CTA — 2da cohorte
═══════════════════════════════════════════════ -->
<section class="seccion seccion--verde">
    <div class="container" style="text-align:center">
        <span class="seccion-label" style="color:var(--dorado)">2da Cohorte · 2027</span>
        <h2 class="seccion__titulo">El próximo grupo puede incluirte</h2>
        <p class="seccion__subtitulo" style="max-width:520px;margin:0 auto 2rem">
            Las postulaciones para el ciclo de septiembre 2027 a mayo 2028 ya están abiertas.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
            <a href="/registro" class="btn-cta-naranja">
                <i class="fas fa-user-plus"></i> Postularme
            </a>
            <a href="/requisitos" class="btn btn--outline" style="color:white;border-color:rgba(255,255,255,.5)">
                <i class="fas fa-clipboard-check"></i> Ver requisitos
            </a>
        </div>
    </div>
</section>

<style>
/* Estas dos no están en app.css: cada página pública las trae */
.seccion__encabezado { text-align: center; margin-bottom: 2.5rem; }
.seccion-label {
    display: inline-block; font-size: .72rem; font-weight: 700;
    letter-spacing: .12em; text-transform: uppercase; color: var(--verde);
    background: var(--verde-light); padding: .25rem .85rem;
    border-radius: 2rem; margin-bottom: .75rem;
}
.btn-cta-naranja {
    display: inline-flex; align-items: center; gap: .5rem;
    background: var(--naranja); color: #fff; padding: .85rem 2rem;
    border-radius: 2rem; font-weight: 700; text-decoration: none;
    transition: background .2s ease, transform .2s ease;
}
.btn-cta-naranja:hover { background: var(--naranja-dark); transform: translateY(-2px); }

/* ── Hero ────────────────────────────────────────────────── */
.coh-hero {
    position: relative; min-height: 46vh; display: flex; align-items: center;
    justify-content: center; text-align: center; padding: 7rem 1.5rem 4rem;
    background: linear-gradient(135deg, var(--verde-dark) 0%, var(--verde) 100%);
    color: var(--blanco); overflow: hidden;
}
.coh-hero__overlay {
    position: absolute; inset: 0;
    background: radial-gradient(circle at 30% 20%, rgba(206,162,55,.18), transparent 55%);
}
.coh-hero__content { position: relative; max-width: 720px; }
.coh-hero__etiqueta {
    display: inline-block; font-size: .78rem; letter-spacing: .14em; text-transform: uppercase;
    font-weight: 700; color: var(--dorado); margin-bottom: 1rem;
}
.coh-hero__titulo {
    font-size: clamp(2.1rem, 5vw, 3.4rem); line-height: 1.1; margin: 0 0 1.1rem; font-weight: 800;
}
.coh-hero__titulo em { color: var(--dorado); font-style: italic; }
.coh-hero__sub { font-size: 1.02rem; line-height: 1.65; color: rgba(255,255,255,.88); margin: 0; }

/* ── Rejilla de participantes ────────────────────────────── */
.coh-grid {
    display: grid; gap: 1.5rem;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
}
.coh-card {
    background: var(--blanco); border-radius: 14px; overflow: hidden;
    box-shadow: 0 2px 10px rgba(17,24,39,.07); border: 1px solid #eef1f4;
    transition: transform .25s ease, box-shadow .25s ease;
}
.coh-card:hover { transform: translateY(-4px); box-shadow: 0 10px 24px rgba(17,24,39,.12); }

/* El 4:5 lo fija la tarjeta, no la imagen: todas se ven iguales
   aunque alguna venga con otro recorte. */
.coh-card__foto {
    position: relative; aspect-ratio: 4 / 5; background: var(--verde-light);
    display: flex; align-items: center; justify-content: center;
}
.coh-card__foto img { width: 100%; height: 100%; object-fit: cover; display: block; }
.coh-card__inicial {
    font-size: 2.6rem; font-weight: 800; color: var(--verde); letter-spacing: .05em;
}
.coh-card__datos { padding: .95rem 1rem 1.15rem; }
.coh-card__nombre {
    font-size: 1rem; font-weight: 700; color: var(--azul); margin: 0 0 .35rem; line-height: 1.3;
}
.coh-card__ciudad {
    font-size: .8rem; color: var(--verde); font-weight: 600; margin: 0 0 .3rem;
    display: flex; align-items: center; gap: .35rem;
}
.coh-card__iglesia { font-size: .78rem; color: var(--gris); margin: 0; line-height: 1.45; }

@media (max-width: 560px) {
    .coh-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
    .coh-card__datos { padding: .75rem .8rem 1rem; }
    .coh-card__nombre { font-size: .9rem; }
}
</style>
