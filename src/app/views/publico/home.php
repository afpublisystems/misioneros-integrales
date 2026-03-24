<?php $titulo = 'Inicio'; ?>

<!-- ═══════════════════════════════════════════════════════
     HERO — Sin cards blancos, logos transparentes sobre verde
     ═══════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero__overlay"></div>
    <div class="hero__dots"></div>

    <div class="container hero__contenido">

        <!-- Logos directamente sobre fondo — sin chip ni card blanco -->
        <div class="hero__orgs">
            <img src="/public/assets/logos/logo-cnbv-t.png"
                 alt="CNBV - Convención Nacional Bautista de Venezuela"
                 class="hero__org-img">
            <div class="hero__org-sep"></div>
            <img src="/public/assets/logos/logo-dime-t.png"
                 alt="DIME - Dirección de Misiones y Evangelización"
                 class="hero__org-img">
        </div>

        <!-- Logo MI transparente directo sobre verde — SIN card blanco -->
        <div class="hero__logo-principal">
            <img src="/public/assets/logos/logo-mi-completo-t.png"
                 alt="Misioneros Integrales — De la formación a la misión">
        </div>

        <!-- Versículo -->
        <blockquote class="hero__versiculo">
            <i class="fas fa-quote-left"></i>
            Pero el que fue sembrado en buena tierra, este es el que oye la Palabra
            y la entiende, y da fruto y produce a ciento, a sesenta, y a treinta por uno.
            <cite>— Mateo 13:23</cite>
        </blockquote>

        <!-- Certificación -->
        <div class="hero__cert">
            <i class="fas fa-graduation-cap"></i>
            <div>
                <strong>Certificación al completar los 3 ciclos:</strong>
                Certificación Ministerial — Mención Misiones (CNBV)
            </div>
        </div>

        <!-- CTA -->
        <div class="hero__cta">
            <a href="/registro" class="btn-hero btn-hero--naranja">
                <i class="fas fa-user-plus"></i>
                <span>Postularme ahora</span>
            </a>
            <a href="/programa" class="btn-hero btn-hero--outline">
                <i class="fas fa-play-circle"></i>
                <span>Conocer el programa</span>
            </a>
        </div>

        <!-- Scroll -->
        <div class="hero__scroll">
            <span>Descubre más</span>
            <i class="fas fa-chevron-down hero__scroll-arrow"></i>
        </div>

    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     CONTADOR REGRESIVO
     ═══════════════════════════════════════════════════════ -->
<section class="countdown-band">
    <div class="container countdown-band__inner">
        <div class="countdown-band__texto">
            <i class="fas fa-calendar-alt"></i>
            <span>Convocatoria <strong>Ciclo 1 · 2026</strong> — Postulaciones abiertas</span>
        </div>
        <div class="countdown-wrap">
            <div class="countdown-item" id="cd-dias"><span class="cd-num">--</span><span class="cd-label">días</span></div>
            <div class="countdown-sep">:</div>
            <div class="countdown-item" id="cd-horas"><span class="cd-num">--</span><span class="cd-label">horas</span></div>
            <div class="countdown-sep">:</div>
            <div class="countdown-item" id="cd-mins"><span class="cd-num">--</span><span class="cd-label">min</span></div>
            <div class="countdown-sep">:</div>
            <div class="countdown-item" id="cd-segs"><span class="cd-num">--</span><span class="cd-label">seg</span></div>
        </div>
        <a href="/registro" class="btn btn--naranja btn--sm">
            <i class="fas fa-user-plus"></i> Postularme
        </a>
    </div>
</section>

<script>
(function() {
    var fin = new Date('2026-06-30T23:59:59');
    function actualizar() {
        var ahora = new Date();
        var diff  = fin - ahora;
        if (diff <= 0) {
            document.querySelector('.countdown-band__texto span').textContent = 'El plazo de postulación ha cerrado.';
            return;
        }
        var d = Math.floor(diff / 86400000);
        var h = Math.floor((diff % 86400000) / 3600000);
        var m = Math.floor((diff % 3600000) / 60000);
        var s = Math.floor((diff % 60000) / 1000);
        document.querySelector('#cd-dias .cd-num').textContent  = String(d).padStart(2,'0');
        document.querySelector('#cd-horas .cd-num').textContent = String(h).padStart(2,'0');
        document.querySelector('#cd-mins .cd-num').textContent  = String(m).padStart(2,'0');
        document.querySelector('#cd-segs .cd-num').textContent  = String(s).padStart(2,'0');
    }
    actualizar();
    setInterval(actualizar, 1000);
})();
</script>

<style>
.countdown-band {
    background: var(--verde-dark); color: white;
    padding: 1rem 0; border-top: 3px solid var(--dorado);
}
.countdown-band__inner {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap;
}
.countdown-band__texto {
    display: flex; align-items: center; gap: 0.75rem;
    font-size: 0.88rem; color: rgba(255,255,255,0.85);
}
.countdown-band__texto i { color: var(--dorado); font-size: 1.1rem; }
.countdown-band__texto strong { color: white; }
.countdown-wrap {
    display: flex; align-items: center; gap: 0.25rem;
}
.countdown-item {
    text-align: center; min-width: 48px;
    background: rgba(255,255,255,0.08); border-radius: 6px; padding: 0.35rem 0.5rem;
}
.cd-num   { display: block; font-size: 1.3rem; font-weight: 900; line-height: 1; color: var(--dorado); }
.cd-label { display: block; font-size: 0.58rem; text-transform: uppercase; letter-spacing: 0.5px; color: rgba(255,255,255,0.6); margin-top: 0.1rem; }
.countdown-sep { font-size: 1.3rem; font-weight: 900; color: var(--dorado); padding: 0 0.1rem; margin-bottom: 0.75rem; }
@media (max-width: 768px) {
    .countdown-band__inner { justify-content: center; }
    .countdown-band__texto { text-align: center; justify-content: center; width: 100%; }
}
</style>

<!-- ═══════════════════════════════════════════════════════
     IMPACTO ESPERADO — Cards doradas dinámicas
     ═══════════════════════════════════════════════════════ -->
<section class="seccion impacto-seccion">
    <div class="impacto-seccion__header">
        <div class="container">
            <div class="impacto-seccion__titulo-wrap">
                <span class="tag-label"><i class="fas fa-chart-line"></i> Meta del Programa</span>
                <h2>Impacto Esperado</h2>
                <p>Al completar los 3 ciclos de formación — 2026 · 2027 · 2028</p>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="impacto-grid">
            <?php
            $impacto = [
                ['num'=>'120',  'icono'=>'fa-user-graduate', 'titulo'=>'Misioneros Capacitados',
                 'desc'=>'Formados para hacer discípulos y plantar iglesias en comunidades de Venezuela'],
                ['num'=>'200+', 'icono'=>'fa-church', 'titulo'=>'Iglesias Plantadas / Revitalizadas',
                 'desc'=>'Cada iglesia con discipulado activo y vida ministerial sostenida en el tiempo'],
                ['num'=>'70+',  'icono'=>'fa-briefcase', 'titulo'=>'Microempresas Misioneras',
                 'desc'=>'Misioneros con oficio propio para sostenerse y servir donde son enviados'],
                ['num'=>'21+',  'icono'=>'fa-map-marked-alt', 'titulo'=>'Estados Alcanzados',
                 'desc'=>'El evangelio presente en todo el territorio nacional'],
            ];
            foreach ($impacto as $item): ?>
            <div class="impacto-item">
                <div class="impacto-item__icono">
                    <i class="fas <?= $item['icono'] ?>"></i>
                </div>
                <div class="impacto-item__num"><?= $item['num'] ?></div>
                <h3><?= $item['titulo'] ?></h3>
                <p><?= $item['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     EJES FORMATIVOS — Rediseño con tabs visuales
     ═══════════════════════════════════════════════════════ -->
<section class="seccion ejes-seccion">
    <div class="container">
        <div class="seccion-header">
            <span class="tag-label tag-label--verde"><i class="fas fa-layer-group"></i> Formación Integral</span>
            <h2 class="seccion__titulo">Ejes Formativos</h2>
            <p class="seccion__subtitulo">Tres áreas que forman al misionero de manera integral: fe, oficio y práctica de campo</p>
        </div>

        <div class="ejes-grid">
            <!-- Eje 1 -->
            <div class="eje">
                <div class="eje__num">01</div>
                <div class="eje__icono-wrap eje__icono-wrap--azul">
                    <i class="fas fa-bible"></i>
                </div>
                <h3 class="eje__titulo">Teológica, Bíblica<br>y Ministerial</h3>
                <p class="eje__desc"><strong>45 materias</strong> en 3 niveles progresivos para construir base teológica y liderazgo ministerial desde cero.</p>
                <ul class="eje__lista">
                    <li><i class="fas fa-check-circle"></i> Biblia y Teología Sistemática</li>
                    <li><i class="fas fa-check-circle"></i> Hermenéutica y Predicación</li>
                    <li><i class="fas fa-check-circle"></i> Liderazgo y Plantación de Iglesias</li>
                </ul>
            </div>

            <!-- Eje 2 — Destacado -->
            <div class="eje eje--verde">
                <div class="eje__num eje__num--dorado">02</div>
                <div class="eje__icono-wrap eje__icono-wrap--dorado">
                    <i class="fas fa-tools"></i>
                </div>
                <h3 class="eje__titulo">Habilidades<br>Autosustentables</h3>
                <p class="eje__desc">Cada participante aprende un oficio que le permite sostenerse donde sea enviado y generar trabajo en su comunidad.</p>
                <ul class="eje__lista">
                    <li><i class="fas fa-check-circle"></i> Panadería y Repostería</li>
                    <li><i class="fas fa-check-circle"></i> Confección y Textiles</li>
                    <li><i class="fas fa-check-circle"></i> Finanzas y Emprendimiento</li>
                </ul>
            </div>

            <!-- Eje 3 -->
            <div class="eje">
                <div class="eje__num">03</div>
                <div class="eje__icono-wrap eje__icono-wrap--naranja">
                    <i class="fas fa-globe-americas"></i>
                </div>
                <h3 class="eje__titulo">Prácticas<br>Misioneras</h3>
                <p class="eje__desc">Cada fin de semana, los participantes sirven en iglesias locales de la ciudad sede, poniendo en práctica lo que aprendieron esa semana.</p>
                <ul class="eje__lista">
                    <li><i class="fas fa-check-circle"></i> Plantación de Iglesias</li>
                    <li><i class="fas fa-check-circle"></i> Revitalización Ministerial</li>
                    <li><i class="fas fa-check-circle"></i> Servicio Social Comunitario</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     ESTRUCTURA — Teaser: 3 números + 3 ejes + CTA
     (El detalle completo vive en /programa)
     ═══════════════════════════════════════════════════════ -->
<section class="seccion seccion--verde estructura-seccion">
    <div class="container">
        <div class="seccion-header">
            <span class="tag-label tag-label--dorado"><i class="fas fa-map-signs"></i> Ciclo 1 · 2026–2027</span>
            <h2 class="seccion__titulo">Estructura del Programa</h2>
            <p class="seccion__subtitulo">Itinerante por diseño: cada mes, una ciudad diferente en Venezuela</p>
        </div>

        <!-- Tres números impactantes -->
        <div class="est-stats">
            <div class="est-stat">
                <span class="est-stat__num">8</span>
                <span class="est-stat__label">Meses de<br>formación intensiva</span>
            </div>
            <div class="est-stat est-stat--dorado">
                <span class="est-stat__num">45</span>
                <span class="est-stat__label">Materias en<br>3 ciclos anuales</span>
            </div>
            <div class="est-stat">
                <span class="est-stat__num">7</span>
                <span class="est-stat__label">Ciudades de<br>Venezuela sede</span>
            </div>
        </div>

        <!-- Vista previa de los 3 ejes -->
        <div class="est-ejes">
            <div class="est-eje">
                <div class="est-eje__icono"><i class="fas fa-bible"></i></div>
                <div class="est-eje__texto">
                    <strong>Teológica y Ministerial</strong>
                    <span>Hermenéutica, Missiología, Liderazgo pastoral y Teología Sistemática</span>
                </div>
            </div>
            <div class="est-eje">
                <div class="est-eje__icono"><i class="fas fa-seedling"></i></div>
                <div class="est-eje__texto">
                    <strong>Habilidades Autosustentables</strong>
                    <span>Panadería, confección, finanzas: oficios para sostenerse y crear trabajo</span>
                </div>
            </div>
            <div class="est-eje">
                <div class="est-eje__icono"><i class="fas fa-hands-helping"></i></div>
                <div class="est-eje__texto">
                    <strong>Prácticas Misioneras</strong>
                    <span>Plantación de iglesias, evangelismo real en cada ciudad sede cada semana</span>
                </div>
            </div>
        </div>

        <!-- CTA al programa completo -->
        <div class="est-cta">
            <a href="/programa" class="btn-est-detalle">
                Ver la estructura completa del programa
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     PERFIL — ¿Quién puede postularse?
     ═══════════════════════════════════════════════════════ -->
<section class="seccion perfil-seccion">
    <div class="container">
        <div class="perfil-wrap">
            <div class="perfil-texto">
                <span class="tag-label tag-label--verde"><i class="fas fa-user-check"></i> Perfil del Participante</span>
                <h2>¿Es este programa para ti?</h2>
                <p class="perfil-intro">Buscamos personas entre 18 y 40 años con llamado misionero, respaldo de su pastor y disponibilidad para moverse a cada ciudad del itinerario.</p>
                <ul class="perfil-requisitos">
                    <li><i class="fas fa-check-circle"></i> 18 a 40 años de edad</li>
                    <li><i class="fas fa-check-circle"></i> Bautizado hace mínimo 1 año</li>
                    <li><i class="fas fa-check-circle"></i> Participación activa en su iglesia local</li>
                    <li><i class="fas fa-check-circle"></i> Respaldo y carta pastoral</li>
                    <li><i class="fas fa-check-circle"></i> Título de bachiller</li>
                    <li><i class="fas fa-check-circle"></i> Disponibilidad total para trasladarse mensualmente</li>
                </ul>
                <a href="/requisitos" class="btn btn--verde" style="margin-top:1.5rem">
                    Ver requisitos completos <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="perfil-inversion">
                <div class="inv-card">
                    <div class="inv-card__titulo">Inversión del Programa</div>
                    <div class="inv-card__real">
                        <span class="inv-label">Costo real:</span>
                        <span class="inv-precio tachado">$1.500 USD</span>
                        <span class="inv-periodo">por año</span>
                    </div>
                    <div class="inv-card__beca">
                        <div class="beca-badge">🎓 Beca CNBV 50%</div>
                        <div class="beca-precio">$750 <span>USD/año</span></div>
                        <div class="beca-ciclos">por los 3 años del programa</div>
                    </div>
                    <div class="inv-card__total">
                        <i class="fas fa-chart-bar"></i>
                        Inversión total del programa: <strong>$60.000 USD</strong><br>
                        <small>40 misioneros · Aporte CNBV 50% + Participantes 50%</small>
                    </div>
                    <a href="/registro" class="btn btn--naranja btn--block" style="margin-top:1.5rem; padding:1rem">
                        <i class="fas fa-user-plus"></i> Postularme ahora
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     RESPALDO INSTITUCIONAL — CNBV y DIME
     ═══════════════════════════════════════════════════════ -->
<section class="seccion respaldo-seccion">
    <div class="container">

        <div class="respaldo-header">
            <p class="respaldo-header__sup">
                <i class="fas fa-handshake"></i> Respaldo institucional
            </p>
            <h2>Un programa con más de 70 años de historia detrás</h2>
            <p class="respaldo-header__sub">
                Este programa no nació en un escritorio. Lo impulsan dos de las instituciones
                bautistas más consolidadas de Venezuela.
            </p>
        </div>

        <div class="respaldo-cards">

            <!-- CNBV -->
            <div class="respaldo-card">
                <div class="respaldo-card__logo">
                    <img src="/public/assets/logos/logo-cnbv-t.png"
                         alt="Convención Nacional Bautista de Venezuela">
                </div>
                <div class="respaldo-card__cuerpo">
                    <h3>Convención Nacional Bautista de Venezuela</h3>
                    <p class="respaldo-card__sigla">CNBV · Fundada en 1951</p>
                    <p class="respaldo-card__desc">
                        Organismo que reúne a las iglesias bautistas de Venezuela desde hace más
                        de 70 años. Con sede en Caracas y presencia en todo el territorio nacional,
                        la CNBV agrupa cerca de <strong>1.000 iglesias afiliadas</strong> y coordina
                        la vida eclesial, educativa y misionera del movimiento bautista venezolano.
                    </p>
                    <div class="respaldo-card__stats">
                        <div class="respaldo-stat">
                            <strong>1951</strong>
                            <span>Año de fundación</span>
                        </div>
                        <div class="respaldo-stat">
                            <strong>~1.000</strong>
                            <span>Iglesias afiliadas</span>
                        </div>
                        <div class="respaldo-stat">
                            <strong>Nacional</strong>
                            <span>Alcance territorial</span>
                        </div>
                    </div>
                    <a href="https://cnbv.org" target="_blank" rel="noopener" class="respaldo-card__link">
                        <i class="fas fa-external-link-alt"></i> cnbv.org
                    </a>
                </div>
            </div>

            <!-- DIME -->
            <div class="respaldo-card">
                <div class="respaldo-card__logo">
                    <img src="/public/assets/logos/logo-dime-t.png"
                         alt="Dirección de Misiones y Evangelización — CNBV">
                </div>
                <div class="respaldo-card__cuerpo">
                    <h3>Dirección de Misiones y Evangelización</h3>
                    <p class="respaldo-card__sigla">DIME · Departamento oficial de la CNBV</p>
                    <p class="respaldo-card__desc">
                        Brazo misionero de la CNBV, responsable de planificar y ejecutar la
                        estrategia evangelizadora y misionera a nivel nacional. La DIME coordina
                        los proyectos de alcance, forma a los obreros del campo y promueve el
                        avance del evangelio hacia los pueblos no alcanzados de Venezuela y más allá.
                        <strong>Misioneros Integrales es un programa de la DIME.</strong>
                    </p>
                    <div class="respaldo-card__stats">
                        <div class="respaldo-stat">
                            <strong>Misiones</strong>
                            <span>Nacionales e internacionales</span>
                        </div>
                        <div class="respaldo-stat">
                            <strong>Formación</strong>
                            <span>De obreros y misioneros</span>
                        </div>
                        <div class="respaldo-stat">
                            <strong>Alcance</strong>
                            <span>Pueblos no alcanzados</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Nota de aval -->
        <div class="respaldo-aval">
            <i class="fas fa-award"></i>
            <p>
                Al completar el programa recibes una
                <strong>Certificación Ministerial — Mención Misiones</strong>
                avalada por la CNBV, reconocida por las iglesias e instituciones bautistas
                de Venezuela.
            </p>
        </div>

    </div>
</section>

<style>
/* ── Respaldo Institucional ──────────────────────────── */
.respaldo-seccion {
    background: #fff;
    border-top: 1px solid rgba(22,122,94,0.1);
}

.respaldo-header { text-align: center; max-width: 620px; margin: 0 auto 3rem; }
.respaldo-header__sup {
    font-size: 0.78rem; font-weight: 700;
    color: var(--verde); text-transform: uppercase; letter-spacing: 1.5px;
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    margin-bottom: 0.75rem;
}
.respaldo-header h2 {
    font-size: 2rem; font-weight: 900;
    color: var(--verde-dark); margin: 0 0 0.85rem;
    line-height: 1.2;
}
.respaldo-header__sub {
    color: var(--gris); font-size: 0.95rem; line-height: 1.6; margin: 0;
}

/* Cards */
.respaldo-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.75rem;
    margin-bottom: 2rem;
}

.respaldo-card {
    border: 1px solid rgba(22,122,94,0.12);
    border-radius: 20px;
    overflow: hidden;
    transition: box-shadow 0.25s, transform 0.25s;
    background: var(--crema, #faf8f3);
    display: flex;
    flex-direction: column;
}
.respaldo-card:hover {
    box-shadow: 0 12px 40px rgba(22,122,94,0.12);
    transform: translateY(-3px);
}

.respaldo-card__logo {
    background: var(--verde-dark);
    padding: 2.5rem 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 160px;
}
.respaldo-card__logo img {
    max-width: 170px;
    max-height: 85px;
    object-fit: contain;
    filter: brightness(0) invert(1);
    opacity: 0.9;
}

.respaldo-card__cuerpo {
    padding: 1.75rem;
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    flex: 1;
}

.respaldo-card__cuerpo h3 {
    font-size: 1.1rem; font-weight: 900;
    color: var(--verde-dark); margin: 0; line-height: 1.3;
}
.respaldo-card__sigla {
    font-size: 0.78rem; font-weight: 700;
    color: var(--verde); text-transform: uppercase;
    letter-spacing: 0.8px; margin: 0;
}
.respaldo-card__desc {
    font-size: 0.88rem; color: var(--gris-dark);
    line-height: 1.65; margin: 0;
}
.respaldo-card__desc strong { color: var(--verde-dark); }

/* Mini stats */
.respaldo-card__stats {
    display: flex;
    gap: 0;
    border-top: 1px solid rgba(22,122,94,0.1);
    border-bottom: 1px solid rgba(22,122,94,0.1);
    padding: 0.85rem 0;
    margin: 0.25rem 0;
}
.respaldo-stat {
    flex: 1;
    text-align: center;
    padding: 0 0.5rem;
    border-right: 1px solid rgba(22,122,94,0.1);
}
.respaldo-stat:last-child { border-right: none; }
.respaldo-stat strong {
    display: block; font-size: 0.95rem; font-weight: 900;
    color: var(--verde); line-height: 1.2;
}
.respaldo-stat span {
    display: block; font-size: 0.68rem;
    color: var(--gris); margin-top: 0.15rem;
    line-height: 1.3;
}

.respaldo-card__link {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.82rem; font-weight: 700; color: var(--verde);
    text-decoration: none; margin-top: auto;
    transition: color 0.2s;
}
.respaldo-card__link:hover { color: var(--verde-dark); }

/* Aval */
.respaldo-aval {
    display: flex; gap: 1rem; align-items: flex-start;
    background: rgba(206,162,55,0.08);
    border: 1px solid rgba(206,162,55,0.3);
    border-left: 4px solid var(--dorado);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    max-width: 720px;
    margin: 0 auto;
}
.respaldo-aval i {
    color: var(--dorado); font-size: 1.4rem; flex-shrink: 0; margin-top: 2px;
}
.respaldo-aval p {
    font-size: 0.9rem; color: var(--gris-dark);
    line-height: 1.6; margin: 0;
}
.respaldo-aval strong { color: var(--verde-dark); }

@media (max-width: 768px) {
    .respaldo-cards { grid-template-columns: 1fr; }
    .respaldo-header h2 { font-size: 1.6rem; }
    .respaldo-card__logo { min-height: 110px; padding: 1.5rem; }
    .respaldo-card__logo img { max-width: 140px; }
}
</style>

<!-- ═══════════════════════════════════════════════════════
     CTA FINAL — Verde con llamado a la acción
     ═══════════════════════════════════════════════════════ -->
<section class="cta-final">
    <div class="cta-final__overlay"></div>
    <div class="container cta-final__contenido">
        <i class="fas fa-globe-americas cta-final__icono"></i>
        <h2>"Ve y haz discípulos a todas las naciones"</h2>
        <p>Mateo 28:19 — El llamado es claro. La formación empieza aquí.</p>
        <div class="cta-final__acciones">
            <a href="/registro" class="btn-hero btn-hero--naranja">
                <i class="fas fa-arrow-right"></i>
                <span>Comenzar mi postulación</span>
            </a>
            <a href="/contacto" class="btn-hero btn-hero--outline">
                <i class="fas fa-envelope"></i>
                <span>Escribirnos</span>
            </a>
        </div>
        <p class="cta-final__contacto">
            <i class="fas fa-phone"></i> 0424-5886540 &nbsp;·&nbsp;
            <i class="fas fa-phone"></i> 0424-5905392 &nbsp;·&nbsp;
            <i class="fas fa-envelope"></i> misionerosintegrales.cnbv@gmail.com
        </p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     COLABORA CON NOSOTROS — Únete Como Colaborador
     ═══════════════════════════════════════════════════════ -->
<section class="seccion colabora-seccion" id="colabora">
    <div class="container">
        <div class="seccion-header">
            <span class="tag-label tag-label--dorado"><i class="fas fa-hands-helping"></i> Colabora con Nosotros</span>
            <h2 class="seccion__titulo">Colabora con el Programa</h2>
            <p class="seccion__subtitulo">Con tu apoyo podemos formar y enviar más misioneros a todo el país</p>
        </div>

        <!-- Tarjetas de tipo de colaboración -->
        <div class="colabora-tipos">
            <div class="colabora-tipo-card">
                <div class="colabora-tipo-card__icono">💰</div>
                <h3>Apoyo Económico</h3>
                <p>Contribuye financieramente al sostenimiento del programa, becas y materiales de formación.</p>
            </div>
            <div class="colabora-tipo-card colabora-tipo-card--destacado">
                <div class="colabora-tipo-card__icono">📦</div>
                <h3>Donación en Especie</h3>
                <p>Dona alimentos, materiales, equipos o insumos que los misioneros necesitan durante su formación.</p>
            </div>
            <div class="colabora-tipo-card">
                <div class="colabora-tipo-card__icono">🛠</div>
                <h3>Servicios Profesionales</h3>
                <p>Ofrece tus habilidades: diseño, contabilidad, medicina, tecnología, capacitación y más.</p>
            </div>
            <div class="colabora-tipo-card">
                <div class="colabora-tipo-card__icono">🤝</div>
                <h3>Voluntariado</h3>
                <p>Dedica tu tiempo sirviendo directamente en actividades del programa o en las sedes locales.</p>
            </div>
        </div>

        <!-- Formulario de registro de colaborador -->
        <div class="colabora-form-wrap">
            <div class="colabora-form-header">
                <h3><i class="fas fa-envelope-open-text"></i> Regístrate como Colaborador</h3>
                <p>Déjanos tus datos y te contactamos para coordinar juntos.</p>
            </div>

            <?php if (!empty($_SESSION['flash_colabora'])): ?>
            <div class="colabora-flash <?= $_SESSION['flash_colabora']['tipo'] === 'exito' ? 'colabora-flash--exito' : 'colabora-flash--error' ?>">
                <i class="fas <?= $_SESSION['flash_colabora']['tipo'] === 'exito' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= htmlspecialchars($_SESSION['flash_colabora']['mensaje']) ?>
            </div>
            <?php unset($_SESSION['flash_colabora']); ?>
            <?php endif; ?>

            <form action="/colaborar" method="POST" class="colabora-form" novalidate>
                <?= csrf_field() ?>
                <div class="colabora-form__fila">
                    <div class="colabora-form__grupo">
                        <label for="col_nombre"><i class="fas fa-user"></i> Nombre completo <span class="req">*</span></label>
                        <input type="text" id="col_nombre" name="nombre" placeholder="Tu nombre completo" required maxlength="200">
                    </div>
                    <div class="colabora-form__grupo">
                        <label for="col_email"><i class="fas fa-envelope"></i> Correo electrónico <span class="req">*</span></label>
                        <input type="email" id="col_email" name="email" placeholder="tucorreo@ejemplo.com" required maxlength="200">
                    </div>
                </div>
                <div class="colabora-form__fila">
                    <div class="colabora-form__grupo">
                        <label for="col_organizacion"><i class="fas fa-building"></i> Organización / Iglesia <span class="opcional">(opcional)</span></label>
                        <input type="text" id="col_organizacion" name="organizacion" placeholder="Nombre de tu iglesia u organización" maxlength="200">
                    </div>
                    <div class="colabora-form__grupo">
                        <label for="col_tipo"><i class="fas fa-tags"></i> Tipo de colaboración <span class="req">*</span></label>
                        <select id="col_tipo" name="tipo" required>
                            <option value="">— Selecciona una opción —</option>
                            <option value="economico">💰 Apoyo Económico</option>
                            <option value="especie">📦 Donación en Especie</option>
                            <option value="servicios">🛠 Servicios Profesionales</option>
                            <option value="voluntariado">🤝 Voluntariado</option>
                            <option value="otro">✨ Otro</option>
                        </select>
                    </div>
                </div>
                <div class="colabora-form__grupo colabora-form__grupo--full">
                    <label for="col_mensaje"><i class="fas fa-comment-alt"></i> Mensaje <span class="opcional">(opcional)</span></label>
                    <textarea id="col_mensaje" name="mensaje" rows="3" placeholder="Cuéntanos cómo deseas colaborar o cualquier detalle adicional..." maxlength="1000"></textarea>
                </div>
                <div class="colabora-form__submit">
                    <button type="submit" class="btn-colaborar">
                        <i class="fas fa-paper-plane"></i>
                        <span>Enviar mi interés</span>
                    </button>
                    <p class="colabora-form__privacidad">
                        <i class="fas fa-lock"></i> Tu información es confidencial y solo será usada para coordinar tu colaboración.
                    </p>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════
     CARRUSEL DE ORGANIZACIONES ALIADAS
     ═══════════════════════════════════════════════════════ -->
<section class="aliados-seccion">
    <div class="container">
        <div class="aliados-header">
            <span class="tag-label"><i class="fas fa-handshake"></i> Organizaciones Aliadas</span>
            <h2>Quiénes caminan con nosotros</h2>
            <p>Iglesias, asociaciones e instituciones que apoyan el programa en Venezuela</p>
        </div>
    </div>

    <!-- Pista del carrusel — ancho completo -->
    <div class="carrusel-wrap">
        <div class="carrusel-track" id="carrusel-track">
            <?php
            // Lista de organizaciones aliadas
            // Para agregar más: solo añadir elementos a este array
            // Si tienen logo: ['nombre' => '...', 'logo' => '/public/assets/logos/org-xxx.png']
            // Si solo texto:  ['nombre' => '...', 'logo' => null]
            $aliados = [
                ['nombre' => 'CNBV',           'siglas' => 'Convención Nacional Bautista de Venezuela',     'logo' => '/public/assets/logos/logo-cnbv-t.png'],
                ['nombre' => 'DIME',           'siglas' => 'Dirección de Misiones y Evangelización',        'logo' => '/public/assets/logos/logo-dime-t.png'],
                ['nombre' => 'STBV',           'siglas' => 'Seminario Teológico Bautista de Venezuela',     'logo' => '/public/assets/logos/Seminario_Teologico_Bautista.png'],
                ['nombre' => 'FBCC',           'siglas' => 'Fundación Bautista Campo Carabobo',             'logo' => '/public/assets/logos/Fundacion_Bautista_Campo_Carabobo.jpg'],
                ['nombre' => 'ASIBEL',         'siglas' => 'Asociación Iglesias Bautistas Edo. Lara',       'logo' => '/public/assets/logos/ASIBEL.png'],
                ['nombre' => 'Asoc. Miranda',  'siglas' => 'Asociación Bautista del Estado Miranda',        'logo' => null],
                ['nombre' => 'CBCC',           'siglas' => 'Convención Bautista del Centro y Carabobo',     'logo' => null],
                ['nombre' => 'Asoc. Yaracuy',  'siglas' => 'Asociación Bautista de Yaracuy',                'logo' => null],
                ['nombre' => 'Asoc. Portuguesa','siglas' => 'Asociación Bautista de Portuguesa',            'logo' => null],
                ['nombre' => 'Asoc. Trujillo', 'siglas' => 'Asociación Bautista de Trujillo',               'logo' => null],
            ];

            // Duplicar para loop continuo
            $todos = array_merge($aliados, $aliados);
            foreach ($todos as $aliado): ?>
            <div class="carrusel-item">
                <?php if ($aliado['logo']): ?>
                    <img src="<?= $aliado['logo'] ?>" alt="<?= $aliado['nombre'] ?>"
                         class="carrusel-item__logo">
                <?php else: ?>
                    <div class="carrusel-item__inicial">
                        <?= mb_substr($aliado['nombre'], 0, 2) ?>
                    </div>
                <?php endif; ?>
                <div class="carrusel-item__siglas"><?= $aliado['siglas'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ─── ESTILOS ESPECÍFICOS DEL HOME ─────────────────────── -->
<style>
/* ── HERO ─────────────────────────────────────────────────── */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex; align-items: center;
    background-image:
        url('https://images.unsplash.com/photo-1593113630400-ea4288922559?w=1600&q=80');
    background-size: cover;
    background-position: center 30%;
    background-attachment: fixed;
    color: var(--blanco);
    overflow: hidden;
}
.hero__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(
        135deg,
        rgba(8, 60, 43, 0.95) 0%,
        rgba(22, 122, 94, 0.88) 50%,
        rgba(5, 45, 30, 0.96) 100%
    );
}
.hero__dots {
    position: absolute; inset: 0; pointer-events: none;
    background-image: radial-gradient(circle, rgba(255,255,255,0.05) 1px, transparent 1px);
    background-size: 28px 28px;
}
.hero__contenido {
    position: relative; z-index: 2;
    display: flex; flex-direction: column;
    align-items: center; text-align: center;
    gap: 1.75rem; padding-top: 2rem; padding-bottom: 4rem;
}

/* Logos CNBV + DIME — directo sobre verde, sin caja */
.hero__orgs {
    display: flex; align-items: center; gap: 2rem;
}
.hero__org-img {
    height: 48px; width: auto;
    object-fit: contain;
    /* Ajuste fino de brillo para verse sobre verde oscuro */
    filter: brightness(0) invert(1);
    opacity: 0.92;
    transition: opacity 0.3s;
}
.hero__org-img:hover { opacity: 1; }
.hero__org-sep {
    width: 1px; height: 40px;
    background: rgba(255,255,255,0.3);
}

/* Logo MI — transparente, directo sobre el hero, SIN card blanco */
.hero__logo-principal {
    max-width: 580px; width: 90%;
    /* Sombra suave de texto para que resalte sobre la imagen */
    filter: drop-shadow(0 4px 24px rgba(0,0,0,0.35));
}
.hero__logo-principal img {
    width: 100%; height: auto;
    max-height: 160px; object-fit: contain;
}

/* Versículo */
.hero__versiculo {
    max-width: 580px;
    font-size: 0.92rem; font-style: italic;
    color: rgba(255,255,255,0.9);
    border-left: 3px solid var(--dorado);
    padding-left: 1rem; text-align: left;
    line-height: 1.6;
}
.hero__versiculo .fa-quote-left { color: var(--dorado); margin-right: 0.4rem; font-size: 0.8rem; }
.hero__versiculo cite {
    display: block; margin-top: 0.4rem;
    font-style: normal; font-weight: 700; color: var(--dorado); font-size: 0.82rem;
}

/* Certificación */
.hero__cert {
    display: flex; align-items: center; gap: 0.75rem;
    background: rgba(206,162,55,0.18);
    border: 1px solid rgba(206,162,55,0.45);
    border-radius: var(--radio); padding: 0.85rem 1.5rem;
    font-size: 0.88rem; max-width: 560px;
    color: rgba(255,255,255,0.9);
}
.hero__cert i { color: var(--dorado); font-size: 1.3rem; flex-shrink: 0; }
.hero__cert strong { color: #f5d070; }

/* Botones hero */
.hero__cta { display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; }

.btn-hero {
    display: inline-flex; align-items: center; gap: 0.6rem;
    padding: 0.95rem 2rem; border-radius: var(--radio);
    font-family: 'Montserrat', sans-serif;
    font-weight: 700; font-size: 0.95rem;
    cursor: pointer; border: 2px solid transparent;
    transition: all 0.3s; text-decoration: none;
}
.btn-hero--naranja {
    background: var(--naranja); color: var(--blanco); border-color: var(--naranja);
}
.btn-hero--naranja:hover {
    background: var(--naranja-dark); transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(247,148,29,0.45);
}
.btn-hero--outline {
    background: rgba(255,255,255,0.1); color: var(--blanco);
    border-color: rgba(255,255,255,0.5); backdrop-filter: blur(4px);
}
.btn-hero--outline:hover {
    background: rgba(255,255,255,0.2); transform: translateY(-3px);
}

/* Scroll indicator */
.hero__scroll {
    display: flex; flex-direction: column; align-items: center; gap: 0.4rem;
    color: rgba(255,255,255,0.6); font-size: 0.75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 1px;
    margin-top: 1rem;
}
.hero__scroll-arrow {
    animation: bounceDown 1.5s infinite;
    font-size: 1.1rem; color: var(--dorado);
}
@keyframes bounceDown {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(6px); }
}

/* ── IMPACTO ──────────────────────────────────────────────── */
.impacto-seccion { padding: 0; }
.impacto-seccion__header {
    background: var(--verde-dark);
    padding: 3rem 0 1rem;
    text-align: center;
}
.impacto-seccion__titulo-wrap { color: var(--blanco); }
.impacto-seccion__titulo-wrap h2 {
    font-size: 2rem; font-weight: 800;
    color: var(--dorado); margin: 0.5rem 0 0.25rem;
}
.impacto-seccion__titulo-wrap p { color: rgba(255,255,255,0.75); font-size: 0.95rem; }

.impacto-grid {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 0; margin-top: 0;
}
.impacto-item {
    background: var(--dorado);
    padding: 2.5rem 1.5rem; text-align: center;
    border-right: 1px solid rgba(10,75,55,0.15);
    transition: var(--transicion); cursor: default;
}
.impacto-item:last-child { border-right: none; }
.impacto-item:hover { background: #b8891e; }
.impacto-item__icono {
    font-size: 2.2rem; color: var(--verde-dark);
    margin-bottom: 0.75rem;
}
.impacto-item__num {
    font-size: 3.2rem; font-weight: 900;
    color: var(--verde-dark); line-height: 1;
    margin-bottom: 0.4rem;
}
.impacto-item h3 {
    font-size: 0.78rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: 0.5px;
    color: var(--verde-dark); margin-bottom: 0.6rem;
}
.impacto-item p { font-size: 0.78rem; color: rgba(0,50,30,0.75); line-height: 1.5; }

/* ── TAGS ─────────────────────────────────────────────────── */
.tag-label {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: var(--verde-light); color: var(--verde);
    padding: 0.3rem 0.9rem; border-radius: 999px;
    font-size: 0.75rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 0.75rem;
}
.tag-label--verde { background: var(--verde-light); color: var(--verde); }
.tag-label--dorado { background: var(--dorado-light); color: var(--dorado-dark); }

.seccion-header { text-align: center; margin-bottom: 3rem; }

/* ── EJES FORMATIVOS ──────────────────────────────────────── */
.ejes-seccion { background: var(--crema); }
.ejes-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem;
}
.eje {
    background: var(--blanco); border-radius: 20px;
    padding: 2.5rem 2rem; position: relative;
    border: 2px solid rgba(22,122,94,0.1);
    transition: var(--transicion);
    overflow: hidden;
}
.eje::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 4px;
    background: var(--verde);
}
.eje:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(22,122,94,0.15);
    border-color: rgba(22,122,94,0.3);
}
.eje--verde {
    background: var(--verde);
    border-color: var(--verde);
    color: var(--blanco);
}
.eje--verde::before { background: var(--dorado); }
.eje--verde .eje__titulo { color: var(--blanco); }
.eje--verde .eje__desc  { color: rgba(255,255,255,0.9); }
.eje--verde .eje__lista li { color: rgba(255,255,255,0.9); }
.eje--verde .eje__lista i  { color: var(--dorado) !important; }

.eje__num {
    position: absolute; top: 1.25rem; right: 1.5rem;
    font-size: 4rem; font-weight: 900;
    color: rgba(22,122,94,0.08); line-height: 1;
    font-style: italic;
}
.eje__num--dorado { color: rgba(206,162,55,0.2); }

.eje__icono-wrap {
    width: 64px; height: 64px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem; margin-bottom: 1.25rem;
}
.eje__icono-wrap--azul    { background: var(--azul-light); color: var(--azul); }
.eje__icono-wrap--dorado  { background: rgba(206,162,55,0.2); color: var(--dorado); }
.eje__icono-wrap--naranja { background: rgba(247,148,29,0.12); color: var(--naranja); }

.eje__titulo {
    font-size: 1.15rem; font-weight: 800;
    color: var(--verde); margin-bottom: 0.75rem; line-height: 1.3;
}
.eje__desc {
    font-size: 0.88rem; color: var(--gris); line-height: 1.7;
    margin-bottom: 1.25rem;
}
.eje__desc strong { color: var(--verde); }
.eje--verde .eje__desc strong { color: var(--dorado); }

.eje__lista { display: flex; flex-direction: column; gap: 0.5rem; }
.eje__lista li {
    display: flex; align-items: center; gap: 0.5rem;
    font-size: 0.83rem; font-weight: 500; color: var(--gris-dark);
}
.eje__lista i { color: var(--verde); flex-shrink: 0; }

/* ── ESTRUCTURA TEASER ────────────────────────────────────── */
.est-stats {
    display: grid; grid-template-columns: repeat(3,1fr);
    gap: 1.5rem; margin: 2.5rem 0 2.5rem;
}
.est-stat {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 20px; padding: 2rem 1.5rem;
    text-align: center; transition: var(--transicion);
}
.est-stat:hover { background: rgba(255,255,255,0.16); transform: translateY(-4px); }
.est-stat--dorado { background: rgba(206,162,55,0.18); border-color: rgba(206,162,55,0.4); }
.est-stat__num {
    display: block; font-size: 3.5rem; font-weight: 900;
    color: var(--dorado); line-height: 1;
    text-shadow: 0 2px 12px rgba(206,162,55,0.4);
}
.est-stat--dorado .est-stat__num { color: #fff; text-shadow: none; }
.est-stat__label {
    display: block; font-size: 0.85rem; color: rgba(255,255,255,0.75);
    line-height: 1.4; margin-top: 0.5rem; font-weight: 500;
}

.est-ejes {
    display: flex; flex-direction: column; gap: 1rem;
    margin-bottom: 2.5rem;
}
.est-eje {
    display: flex; align-items: center; gap: 1.25rem;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 14px; padding: 1.1rem 1.5rem;
    transition: var(--transicion);
}
.est-eje:hover { background: rgba(255,255,255,0.13); }
.est-eje__icono {
    width: 46px; height: 46px; border-radius: 12px;
    background: rgba(206,162,55,0.2); border: 1px solid rgba(206,162,55,0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: var(--dorado); flex-shrink: 0;
}
.est-eje__texto { display: flex; flex-direction: column; gap: 0.25rem; }
.est-eje__texto strong { font-size: 1rem; font-weight: 700; color: #fff; }
.est-eje__texto span { font-size: 0.85rem; color: rgba(255,255,255,0.65); line-height: 1.4; }

.est-cta { text-align: center; }
.btn-est-detalle {
    display: inline-flex; align-items: center; gap: 0.6rem;
    background: var(--dorado); color: var(--verde-dark);
    font-weight: 800; font-size: 0.95rem;
    padding: 0.9rem 2rem; border-radius: 50px;
    text-decoration: none; transition: var(--transicion);
    box-shadow: 0 6px 20px rgba(206,162,55,0.35);
}
.btn-est-detalle:hover {
    background: #d4a830; transform: translateY(-2px);
    box-shadow: 0 8px 28px rgba(206,162,55,0.5);
}

/* ── PERFIL + INVERSIÓN ───────────────────────────────────── */
.perfil-seccion { background: var(--blanco); }
.perfil-wrap {
    display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;
}
.perfil-texto h2 {
    font-size: 1.9rem; font-weight: 800;
    color: var(--verde); margin: 0.5rem 0 1rem;
}
.perfil-intro { color: var(--gris); font-size: 1rem; margin-bottom: 1.5rem; }
.perfil-requisitos { display: flex; flex-direction: column; gap: 0.75rem; }
.perfil-requisitos li {
    display: flex; align-items: center; gap: 0.75rem;
    font-size: 0.92rem; font-weight: 500; color: var(--gris-dark);
}
.perfil-requisitos i { color: var(--verde); font-size: 1rem; flex-shrink: 0; }

/* Inversión card */
.inv-card {
    background: linear-gradient(145deg, var(--verde-dark), var(--verde));
    border-radius: 20px; padding: 2rem;
    color: var(--blanco); box-shadow: var(--sombra-lg);
}
.inv-card__titulo {
    font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 1px; color: var(--dorado); margin-bottom: 1.5rem;
}
.inv-card__real {
    display: flex; align-items: center; gap: 0.75rem;
    margin-bottom: 1.25rem; flex-wrap: wrap;
}
.inv-label { font-size: 0.82rem; color: rgba(255,255,255,0.7); }
.inv-precio { font-size: 1.1rem; font-weight: 700; }
.inv-precio.tachado { text-decoration: line-through; color: rgba(255,255,255,0.5); }
.inv-periodo { font-size: 0.78rem; color: rgba(255,255,255,0.6); }

.inv-card__beca {
    background: rgba(206,162,55,0.2);
    border: 1px solid rgba(206,162,55,0.4);
    border-radius: 12px; padding: 1.25rem; text-align: center; margin-bottom: 1.25rem;
}
.beca-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: var(--dorado); color: var(--verde-dark);
    padding: 0.25rem 0.75rem; border-radius: 999px;
    font-size: 0.75rem; font-weight: 800; margin-bottom: 0.75rem;
}
.beca-precio { font-size: 2.5rem; font-weight: 900; color: var(--dorado); line-height: 1; }
.beca-precio span { font-size: 1rem; font-weight: 600; color: rgba(255,255,255,0.8); }
.beca-ciclos { font-size: 0.8rem; color: rgba(255,255,255,0.7); margin-top: 0.25rem; }

.inv-card__total {
    font-size: 0.8rem; color: rgba(255,255,255,0.75);
    background: rgba(0,0,0,0.15); border-radius: var(--radio);
    padding: 0.75rem; line-height: 1.6;
}
.inv-card__total i { color: var(--dorado); margin-right: 0.3rem; }
.inv-card__total strong { color: var(--dorado); font-size: 0.95rem; }
.inv-card__total small { color: rgba(255,255,255,0.55); }

/* ── CTA FINAL ────────────────────────────────────────────── */
.cta-final {
    position: relative; padding: 6rem 0;
    background-image: url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=1400&q=80');
    background-size: cover; background-position: center;
    background-attachment: fixed;
    text-align: center; color: var(--blanco);
}
.cta-final__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(10,75,55,0.94), rgba(22,122,94,0.9));
}
.cta-final__contenido { position: relative; z-index: 2; max-width: 700px; margin: 0 auto; }
.cta-final__icono { font-size: 3.5rem; color: var(--dorado); margin-bottom: 1rem; }
.cta-final h2 { font-size: 2rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--blanco); }
.cta-final p  { color: rgba(255,255,255,0.8); font-size: 1rem; margin-bottom: 2rem; font-style: italic; }
.cta-final__acciones { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2rem; }
.cta-final__contacto {
    font-size: 0.83rem; color: rgba(255,255,255,0.6);
    display: flex; align-items: center; justify-content: center;
    gap: 0.5rem; flex-wrap: wrap;
}
.cta-final__contacto i { color: var(--dorado); }

/* ── RESPONSIVE ───────────────────────────────────────────── */
@media (max-width: 1024px) {
    .impacto-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
    .ejes-grid, .est-stats, .perfil-wrap { grid-template-columns: 1fr; }
    .est-stat { padding: 1.5rem 1rem; }
    .est-stat__num { font-size: 2.8rem; }
    .hero__logo-principal { max-width: 520px; }
    .hero { background-attachment: scroll; }
    .cta-final { background-attachment: scroll; }
}
@media (max-width: 600px) {
    .impacto-grid { grid-template-columns: repeat(2, 1fr); }
    .hero__orgs { flex-direction: column; gap: 0.75rem; }
    .hero__org-sep { width: 60px; height: 1px; }
}

/* ── CARRUSEL ALIADOS ─────────────────────────────────────── */
.aliados-seccion {
    background: var(--verde-dark);
    padding: 3.5rem 0;
    overflow: hidden;
}
.aliados-header {
    text-align: center; color: var(--blanco);
    margin-bottom: 2.5rem;
}
.aliados-header .tag-label {
    background: rgba(206,162,55,0.2);
    color: var(--dorado); border: 1px solid rgba(206,162,55,0.3);
}
.aliados-header h2 {
    font-size: 1.6rem; font-weight: 800;
    color: var(--blanco); margin: 0.5rem 0 0.25rem;
}
.aliados-header p { font-size: 0.9rem; color: rgba(255,255,255,0.65); }

.carrusel-wrap {
    position: relative; width: 100%;
    mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
    -webkit-mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
}
.carrusel-track {
    display: flex; gap: 1.25rem;
    width: max-content;
    animation: carrusel-scroll 38s linear infinite;
    padding: 0.5rem 1rem;
}
.carrusel-track:hover { animation-play-state: paused; }

@keyframes carrusel-scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}

.carrusel-item {
    flex-shrink: 0;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 16px;
    padding: 1.5rem 1.75rem;
    text-align: center;
    min-width: 200px; max-width: 220px;
    display: flex; flex-direction: column;
    align-items: center; gap: 0.6rem;
    transition: background 0.3s, transform 0.3s, border-color 0.3s;
    cursor: default;
}
.carrusel-item:hover {
    background: rgba(255,255,255,0.15);
    transform: translateY(-4px);
    border-color: rgba(206,162,55,0.5);
}
.carrusel-item__logo {
    height: 44px; width: auto; max-width: 140px;
    object-fit: contain;
    filter: brightness(0) invert(1);
    opacity: 0.85; transition: opacity 0.3s;
}
.carrusel-item:hover .carrusel-item__logo { opacity: 1; }

.carrusel-item__inicial {
    width: 52px; height: 52px;
    background: var(--dorado);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; font-weight: 900;
    color: var(--verde-dark);
}
.carrusel-item__nombre {
    font-size: 0.95rem; font-weight: 800; color: var(--blanco);
}
.carrusel-item__siglas {
    font-size: 0.72rem; color: rgba(255,255,255,0.6);
    line-height: 1.4;
}

/* ── COLABORA CON NOSOTROS ────────────────────────────── */
.colabora-seccion {
    background: linear-gradient(160deg, var(--verde-dark) 0%, var(--verde) 60%, #1a9070 100%);
    color: var(--blanco);
    padding: 5rem 0;
}
.colabora-seccion .seccion-header { color: var(--blanco); }
.colabora-seccion .seccion__titulo { color: var(--blanco); }
.colabora-seccion .seccion__subtitulo { color: rgba(255,255,255,0.8); }

/* Tarjetas de tipo */
.colabora-tipos {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 3.5rem;
}
.colabora-tipo-card {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 18px;
    padding: 2rem 1.5rem;
    text-align: center;
    transition: var(--transicion);
    cursor: default;
}
.colabora-tipo-card:hover {
    background: rgba(255,255,255,0.18);
    transform: translateY(-5px);
    border-color: rgba(206,162,55,0.5);
    box-shadow: 0 12px 32px rgba(0,0,0,0.2);
}
.colabora-tipo-card--destacado {
    background: rgba(206,162,55,0.2);
    border-color: rgba(206,162,55,0.45);
}
.colabora-tipo-card--destacado:hover {
    background: rgba(206,162,55,0.3);
}
.colabora-tipo-card__icono {
    font-size: 2.8rem;
    margin-bottom: 1rem;
    display: block;
    line-height: 1;
}
.colabora-tipo-card h3 {
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--dorado);
    margin-bottom: 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.colabora-tipo-card p {
    font-size: 0.82rem;
    color: rgba(255,255,255,0.8);
    line-height: 1.6;
}

/* Formulario */
.colabora-form-wrap {
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 24px;
    padding: 2.5rem 2.5rem 2rem;
    backdrop-filter: blur(4px);
}
.colabora-form-header {
    text-align: center;
    margin-bottom: 2rem;
}
.colabora-form-header h3 {
    font-size: 1.3rem;
    font-weight: 800;
    color: var(--dorado);
    margin-bottom: 0.4rem;
}
.colabora-form-header h3 i { margin-right: 0.4rem; }
.colabora-form-header p {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.75);
}

.colabora-flash {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem 1.25rem; border-radius: 12px;
    font-size: 0.9rem; font-weight: 600;
    margin-bottom: 1.5rem;
}
.colabora-flash i { font-size: 1.1rem; flex-shrink: 0; }
.colabora-flash--exito {
    background: rgba(40,200,120,0.2);
    border: 1px solid rgba(40,200,120,0.4);
    color: #a0f0c8;
}
.colabora-flash--error {
    background: rgba(220,50,50,0.2);
    border: 1px solid rgba(220,50,50,0.35);
    color: #f5b4b4;
}

.colabora-form { display: flex; flex-direction: column; gap: 1.25rem; }
.colabora-form__fila {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;
}
.colabora-form__grupo {
    display: flex; flex-direction: column; gap: 0.4rem;
}
.colabora-form__grupo--full { grid-column: 1 / -1; }

.colabora-form label {
    font-size: 0.8rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    color: rgba(255,255,255,0.85);
}
.colabora-form label i { color: var(--dorado); margin-right: 0.3rem; }
.colabora-form .req { color: var(--naranja); font-size: 0.85rem; }
.colabora-form .opcional { color: rgba(255,255,255,0.45); font-weight: 400; text-transform: none; letter-spacing: 0; }

.colabora-form input,
.colabora-form select,
.colabora-form textarea {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 10px;
    padding: 0.75rem 1rem;
    color: var(--blanco);
    font-family: 'Montserrat', sans-serif;
    font-size: 0.9rem;
    transition: border-color 0.3s, background 0.3s;
    outline: none;
    width: 100%;
    box-sizing: border-box;
}
.colabora-form input::placeholder,
.colabora-form textarea::placeholder { color: rgba(255,255,255,0.4); }
.colabora-form select option { background: var(--verde-dark); color: var(--blanco); }
.colabora-form input:focus,
.colabora-form select:focus,
.colabora-form textarea:focus {
    border-color: var(--dorado);
    background: rgba(255,255,255,0.18);
    box-shadow: 0 0 0 3px rgba(206,162,55,0.2);
}
.colabora-form textarea { resize: vertical; min-height: 90px; }

.colabora-form__submit {
    display: flex; flex-direction: column;
    align-items: center; gap: 0.75rem;
    margin-top: 0.5rem;
}
.btn-colaborar {
    display: inline-flex; align-items: center; gap: 0.6rem;
    background: var(--naranja); color: var(--blanco);
    border: none; border-radius: 12px;
    padding: 1rem 2.5rem;
    font-family: 'Montserrat', sans-serif;
    font-weight: 800; font-size: 1rem;
    cursor: pointer; transition: all 0.3s;
    letter-spacing: 0.3px;
}
.btn-colaborar:hover {
    background: var(--naranja-dark);
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(247,148,29,0.45);
}
.colabora-form__privacidad {
    font-size: 0.75rem; color: rgba(255,255,255,0.5);
    display: flex; align-items: center; gap: 0.4rem;
    text-align: center;
}
.colabora-form__privacidad i { color: var(--dorado); }

/* Responsive colabora */
@media (max-width: 1024px) {
    .colabora-tipos { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .colabora-form-wrap { padding: 1.75rem 1.25rem 1.5rem; }
    .colabora-form__fila { grid-template-columns: 1fr; }
    .colabora-tipos { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
    .colabora-tipos { grid-template-columns: 1fr; }
}
</style>

<?php if (empty($_SESSION['usuario_id'])): ?>
<!-- ═══════════════════════════════════════════════════════
     POPUP DE LANZAMIENTO — Convocatoria Cohorte 2026
     Solo se muestra una vez por sesión (sessionStorage)
     ═══════════════════════════════════════════════════════ -->
<div id="popup-lanzamiento" class="popup-backdrop" role="dialog" aria-modal="true" aria-label="Convocatoria abierta">

    <div class="popup-card">

        <!-- Botón cerrar -->
        <button class="popup-cerrar" id="popup-cerrar" aria-label="Cerrar">
            <i class="fas fa-times"></i>
        </button>

        <!-- Panel izquierdo: visual -->
        <div class="popup-visual">
            <div class="popup-visual__overlay"></div>
            <div class="popup-visual__contenido">
                <img src="/public/assets/logos/logo-mi-completo-t.png"
                     alt="Misioneros Integrales" class="popup-logo">
                <div class="popup-badge">
                    <i class="fas fa-satellite-dish"></i>
                    Convocatoria abierta · Cohorte 2026
                </div>
                <div class="popup-orgs">
                    <img src="/public/assets/logos/logo-cnbv-t.png" alt="CNBV">
                    <span>×</span>
                    <img src="/public/assets/logos/logo-dime-t.png" alt="DIME">
                </div>
            </div>
        </div>

        <!-- Panel derecho: llamado -->
        <div class="popup-cuerpo">

            <p class="popup-sobre">Programa de Formación Misionera — CNBV / DIME</p>
            <h2 class="popup-titulo">¿Dios te está<br>llamando a las misiones?</h2>

            <p class="popup-texto">
                Este programa te forma durante 8 meses para llevar el evangelio
                a las naciones — con herramientas reales, respaldo institucional
                y una comunidad que camina contigo.
            </p>

            <!-- Cupos y tiempo -->
            <div class="popup-stats">
                <div class="popup-stat">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <strong>Inicio</strong>
                        <span>Julio 2026</span>
                    </div>
                </div>
                <div class="popup-stat">
                    <i class="fas fa-users"></i>
                    <div>
                        <strong>Cupos limitados</strong>
                        <span>20 por cohorte</span>
                    </div>
                </div>
                <div class="popup-stat">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Cierre convocatoria</strong>
                        <span id="popup-dias-restantes">—</span>
                    </div>
                </div>
            </div>

            <!-- CTAs -->
            <div class="popup-ctas">
                <a href="/registro" class="popup-btn popup-btn--principal">
                    <i class="fas fa-rocket"></i>
                    Postularme ahora
                </a>
                <a href="/programa" class="popup-btn popup-btn--secundario" id="popup-saber-mas">
                    <i class="fas fa-book-open"></i>
                    Conocer el programa
                </a>
            </div>

            <p class="popup-dismiss" id="popup-dismiss-link">
                Ahora no — <button type="button" onclick="cerrarPopup()">cerrar</button>
            </p>

        </div>
    </div>
</div>

<style>
/* ── Backdrop ────────────────────────────────────────── */
.popup-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(5, 30, 20, 0.82);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    z-index: 9000;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
}
.popup-backdrop.visible {
    display: flex;
    animation: popupFadeIn 0.35s ease;
}
@keyframes popupFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

/* ── Card ────────────────────────────────────────────── */
.popup-card {
    position: relative;
    display: grid;
    grid-template-columns: 280px 1fr;
    width: 100%;
    max-width: 780px;
    max-height: 90vh;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 32px 80px rgba(0,0,0,0.55), 0 0 0 1px rgba(206,162,55,0.2);
    animation: popupSlideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes popupSlideUp {
    from { transform: translateY(40px) scale(0.96); opacity: 0; }
    to   { transform: translateY(0)    scale(1);    opacity: 1; }
}

/* ── Cerrar ──────────────────────────────────────────── */
.popup-cerrar {
    position: absolute;
    top: 1rem; right: 1rem;
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: #fff;
    font-size: 1rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    z-index: 10;
    transition: background 0.2s, transform 0.15s;
}
.popup-cerrar:hover { background: rgba(255,255,255,0.3); transform: scale(1.1); }

/* ── Panel visual (izquierdo) ────────────────────────── */
.popup-visual {
    position: relative;
    background:
        linear-gradient(160deg, rgba(8,60,43,0.97) 0%, rgba(22,122,94,0.93) 100%),
        url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&q=75')
        center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 2rem;
}
.popup-visual__overlay {
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 50% 30%, rgba(206,162,55,0.12) 0%, transparent 65%);
}
.popup-visual__contenido {
    position: relative;
    display: flex; flex-direction: column;
    align-items: center; gap: 1.5rem;
    text-align: center;
}
.popup-logo {
    width: 180px;
    filter: drop-shadow(0 6px 20px rgba(0,0,0,0.4));
}
.popup-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(206,162,55,0.18);
    border: 1px solid rgba(206,162,55,0.45);
    border-radius: 100px;
    padding: 0.45rem 1rem;
    font-size: 0.72rem;
    font-weight: 700;
    color: var(--dorado, #cea237);
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.popup-orgs {
    display: flex; align-items: center; gap: 1rem;
}
.popup-orgs img {
    height: 22px;
    filter: brightness(0) invert(1);
    opacity: 0.55;
}
.popup-orgs span {
    color: rgba(255,255,255,0.3);
    font-weight: 700;
    font-size: 1rem;
}

/* ── Panel cuerpo (derecho) ──────────────────────────── */
.popup-cuerpo {
    background: var(--crema, #faf8f3);
    padding: 2.5rem 2.25rem;
    display: flex; flex-direction: column;
    justify-content: center;
    gap: 1.15rem;
    overflow-y: auto;
}
.popup-sobre {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--verde, #167a5e);
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0;
}
.popup-titulo {
    font-size: 1.75rem;
    font-weight: 900;
    color: var(--verde-dark, #083c2b);
    line-height: 1.2;
    margin: 0;
}
.popup-texto {
    font-size: 0.9rem;
    color: var(--gris, #6b7280);
    line-height: 1.65;
    margin: 0;
}

/* Stats / datos rápidos */
.popup-stats {
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
    background: rgba(22,122,94,0.05);
    border: 1px solid rgba(22,122,94,0.12);
    border-radius: 14px;
    padding: 1rem 1.1rem;
}
.popup-stat {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.popup-stat i {
    width: 28px; height: 28px;
    background: rgba(22,122,94,0.1);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: var(--verde, #167a5e);
    font-size: 0.8rem;
    flex-shrink: 0;
}
.popup-stat div { display: flex; flex-direction: column; line-height: 1.3; }
.popup-stat strong { font-size: 0.8rem; color: var(--gris-dark, #374151); font-weight: 700; }
.popup-stat span   { font-size: 0.78rem; color: var(--gris, #6b7280); }

/* CTAs */
.popup-ctas {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.popup-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 0.85rem 1.5rem;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 800;
    font-family: inherit;
    text-decoration: none;
    transition: background 0.2s, transform 0.1s;
    cursor: pointer;
    border: none;
}
.popup-btn:active { transform: scale(0.98); }
.popup-btn--principal {
    background: var(--verde, #167a5e);
    color: #fff;
}
.popup-btn--principal:hover { background: #0f5a45; }
.popup-btn--secundario {
    background: transparent;
    color: var(--verde, #167a5e);
    border: 2px solid var(--verde, #167a5e);
}
.popup-btn--secundario:hover { background: rgba(22,122,94,0.07); }

.popup-dismiss {
    margin: 0;
    text-align: center;
    font-size: 0.8rem;
    color: var(--gris, #6b7280);
}
.popup-dismiss button {
    background: none;
    border: none;
    color: var(--gris, #6b7280);
    font-family: inherit;
    font-size: inherit;
    text-decoration: underline;
    cursor: pointer;
    padding: 0;
}
.popup-dismiss button:hover { color: var(--verde, #167a5e); }

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 640px) {
    .popup-backdrop { padding: 0; align-items: flex-end; }
    .popup-card {
        grid-template-columns: 1fr;
        border-radius: 24px 24px 0 0;
        max-height: 92vh;
    }
    .popup-visual { display: none; }
    .popup-cuerpo { padding: 2rem 1.5rem 1.5rem; }
    .popup-titulo { font-size: 1.4rem; }
    .popup-cerrar { background: rgba(0,0,0,0.15); color: var(--verde-dark, #083c2b); border-color: rgba(0,0,0,0.15); }
}
@media (min-width: 641px) and (max-width: 900px) {
    .popup-card { grid-template-columns: 220px 1fr; }
    .popup-logo { width: 140px; }
}
</style>

<script>
(function () {
    var KEY    = 'mi_popup_visto';
    var popup  = document.getElementById('popup-lanzamiento');
    var btnX   = document.getElementById('popup-cerrar');
    var btnSM  = document.getElementById('popup-saber-mas');

    // No mostrar si ya fue visto en esta sesión
    if (sessionStorage.getItem(KEY)) return;

    function cerrarPopup() {
        popup.style.animation = 'popupFadeIn 0.25s ease reverse forwards';
        setTimeout(function () { popup.classList.remove('visible'); }, 220);
        sessionStorage.setItem(KEY, '1');
    }
    window.cerrarPopup = cerrarPopup;

    // Mostrar con delay (la página carga primero)
    setTimeout(function () {
        popup.classList.add('visible');
    }, 1200);

    // Cerrar con botón X
    btnX.addEventListener('click', cerrarPopup);

    // Cerrar al hacer clic en el backdrop (fuera del card)
    popup.addEventListener('click', function (e) {
        if (e.target === popup) cerrarPopup();
    });

    // "Conocer el programa" también cierra el popup antes de navegar
    if (btnSM) {
        btnSM.addEventListener('click', function () {
            sessionStorage.setItem(KEY, '1');
        });
    }

    // Tecla Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && popup.classList.contains('visible')) cerrarPopup();
    });

    // Días restantes para el cierre de convocatoria
    var fin = new Date('2026-06-30T23:59:59');
    var diff = fin - new Date();
    var el   = document.getElementById('popup-dias-restantes');
    if (el && diff > 0) {
        var dias = Math.ceil(diff / 86400000);
        el.textContent = dias + ' días restantes';
    } else if (el) {
        el.textContent = 'Convocatoria cerrada';
    }
})();
</script>
<?php endif; ?>
