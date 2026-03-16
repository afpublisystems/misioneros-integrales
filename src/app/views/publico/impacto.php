<?php
/**
 * Vista: /impacto
 * Descripción: Página de Impacto — Misioneros Integrales CNBV/DIME
 * Layout: main.php
 */
?>

<!-- ═══════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════ -->
<section class="imp-hero">
    <div class="imp-hero__overlay"></div>
    <div class="imp-hero__content">
        <span class="imp-hero__etiqueta">CNBV · DIME · Venezuela</span>
        <h1 class="imp-hero__titulo">Nuestro<br><em>Impacto</em></h1>
        <p class="imp-hero__sub">"El que fue sembrado en buena tierra da fruto y produce a ciento, a sesenta, y a treinta por uno."</p>
        <p class="imp-hero__ref">— Mateo 13:23</p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     CONTADORES DE IMPACTO (desde BD)
═══════════════════════════════════════════════ -->
<section class="seccion seccion--verde">
    <div class="container">
        <div class="seccion__encabezado">
            <h2 class="seccion__titulo" style="color:var(--dorado)">Números que hablan</h2>
            <p class="seccion__subtitulo" style="color:rgba(255,255,255,0.8)">
                El fruto acumulado de los ciclos anteriores del programa
            </p>
        </div>

        <div class="imp-stats-grid">
            <?php if (!empty($stats)): ?>
                <?php foreach ($stats as $s): ?>
                <?php if (!$s['activo']) continue; ?>
                <div class="imp-stat-card">
                    <div class="imp-stat-card__icono">
                        <i class="fas <?= htmlspecialchars($s['icono'] ?? 'fa-chart-bar') ?>"></i>
                    </div>
                    <div class="imp-stat-card__valor" data-target="<?= (int)$s['valor'] ?>">0</div>
                    <div class="imp-stat-card__label"><?= htmlspecialchars($s['etiqueta']) ?></div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Placeholders si aún no hay datos -->
                <?php
                $placeholders = [
                    ['fa-user-graduate', 'Misioneros Capacitados'],
                    ['fa-church',        'Iglesias Plantadas'],
                    ['fa-briefcase',     'Microempresas Misioneras'],
                    ['fa-map-marker-alt','Estados Alcanzados'],
                ];
                foreach ($placeholders as $p):
                ?>
                <div class="imp-stat-card">
                    <div class="imp-stat-card__icono"><i class="fas <?= $p[0] ?>"></i></div>
                    <div class="imp-stat-card__valor">—</div>
                    <div class="imp-stat-card__label"><?= $p[1] ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <p class="imp-stats-nota">
            <i class="fas fa-info-circle"></i>
            Estadísticas del programa desde su fundación. Se actualizan al finalizar cada ciclo.
        </p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     MODELO DE CICLOS SIMULTÁNEOS
═══════════════════════════════════════════════ -->
<section class="seccion seccion--crema" style="padding-bottom:2rem;">
    <div class="container">
        <div class="seccion__encabezado">
            <span class="seccion-label">Modelo del Programa</span>
            <h2 class="seccion__titulo">Un programa que crece cada año</h2>
            <p class="seccion__subtitulo">
                Cada año se abre una nueva promoción. Con el tiempo, varias cohortes avanzan simultáneamente por sus ciclos.
            </p>
        </div>

        <div class="ciclos-modelo">

            <div class="ciclos-modelo__fila ciclos-modelo__fila--encabezado">
                <div class="ciclos-modelo__col-año"></div>
                <div class="ciclos-modelo__col-ciclo">Ciclo 1 <small>(8 meses)</small></div>
                <div class="ciclos-modelo__col-ciclo">Ciclo 2 <small>(8 meses)</small></div>
                <div class="ciclos-modelo__col-ciclo">Ciclo 3 <small>(8 meses)</small></div>
                <div class="ciclos-modelo__col-resultado">Resultado</div>
            </div>

            <div class="ciclos-modelo__fila">
                <div class="ciclos-modelo__col-año"><span class="ciclos-año">2026</span><small>Año 1</small></div>
                <div class="ciclos-modelo__col-ciclo">
                    <div class="ciclos-badge ciclos-badge--c1">
                        <i class="fas fa-users"></i> Prom. 2026 · Ciclo 1
                    </div>
                </div>
                <div class="ciclos-modelo__col-ciclo ciclos-vacio">—</div>
                <div class="ciclos-modelo__col-ciclo ciclos-vacio">—</div>
                <div class="ciclos-modelo__col-resultado"><span class="ciclos-res">~40 activos</span></div>
            </div>

            <div class="ciclos-modelo__fila">
                <div class="ciclos-modelo__col-año"><span class="ciclos-año">2027</span><small>Año 2</small></div>
                <div class="ciclos-modelo__col-ciclo">
                    <div class="ciclos-badge ciclos-badge--c1">
                        <i class="fas fa-users"></i> Prom. 2027 · Ciclo 1
                    </div>
                </div>
                <div class="ciclos-modelo__col-ciclo">
                    <div class="ciclos-badge ciclos-badge--c2">
                        <i class="fas fa-user-graduate"></i> Prom. 2026 · Ciclo 2
                    </div>
                </div>
                <div class="ciclos-modelo__col-ciclo ciclos-vacio">—</div>
                <div class="ciclos-modelo__col-resultado"><span class="ciclos-res">~80 activos</span></div>
            </div>

            <div class="ciclos-modelo__fila ciclos-modelo__fila--destacada">
                <div class="ciclos-modelo__col-año"><span class="ciclos-año">2028</span><small>Año 3+</small></div>
                <div class="ciclos-modelo__col-ciclo">
                    <div class="ciclos-badge ciclos-badge--c1">
                        <i class="fas fa-users"></i> Prom. 2028 · Ciclo 1
                    </div>
                </div>
                <div class="ciclos-modelo__col-ciclo">
                    <div class="ciclos-badge ciclos-badge--c2">
                        <i class="fas fa-user-graduate"></i> Prom. 2027 · Ciclo 2
                    </div>
                </div>
                <div class="ciclos-modelo__col-ciclo">
                    <div class="ciclos-badge ciclos-badge--c3">
                        <i class="fas fa-award"></i> Prom. 2026 · Ciclo 3
                    </div>
                </div>
                <div class="ciclos-modelo__col-resultado"><span class="ciclos-res ciclos-res--full">~120 activos + <br>primera Certificación</span></div>
            </div>

        </div>

        <div class="ciclos-nota">
            <i class="fas fa-lightbulb"></i>
            <p>
                Los candidatos <strong>siempre ingresan por el Ciclo 1</strong>. Al completar los 3 ciclos (uno por año)
                reciben la <strong>Certificación Ministerial — Mención Misiones</strong> de la CNBV. Cada año una nueva promoción
                se suma al programa.
            </p>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     VISIÓN E IMPACTO ESPERADO — PROMOCIÓN 2026
═══════════════════════════════════════════════ -->
<section class="seccion">
    <div class="container">
        <div class="seccion__encabezado">
            <span class="seccion-label">Promoción 2026 · Ciclo 1</span>
            <h2 class="seccion__titulo">Lo que esperamos lograr</h2>
            <p class="seccion__subtitulo">Proyecciones de impacto para la primera promoción del programa. Cada año se abre una nueva cohorte.</p>
        </div>

        <div class="imp-metas-grid">

            <div class="imp-meta-card imp-meta-card--destacada">
                <div class="imp-meta-card__icono"><i class="fas fa-users"></i></div>
                <div class="imp-meta-card__num">40</div>
                <div class="imp-meta-card__label">Misioneros formados</div>
                <p>Seleccionaremos y formaremos a 40 hombres y mujeres con llamado misionero de toda Venezuela.</p>
            </div>

            <div class="imp-meta-card">
                <div class="imp-meta-card__icono"><i class="fas fa-map-marked-alt"></i></div>
                <div class="imp-meta-card__num">7</div>
                <div class="imp-meta-card__label">Estados recorridos</div>
                <p>Miranda, Aragua, Yaracuy, Carabobo, Portuguesa, Lara y Trujillo — formación itinerante real.</p>
            </div>

            <div class="imp-meta-card">
                <div class="imp-meta-card__icono"><i class="fas fa-book-open"></i></div>
                <div class="imp-meta-card__num">3</div>
                <div class="imp-meta-card__label">Ciclos hasta la Certificación</div>
                <p>Al completar los 3 ciclos, los participantes reciben la Certificación Ministerial — Mención Misiones (CNBV). Cada año se incorpora una nueva cohorte.</p>
            </div>

            <div class="imp-meta-card">
                <div class="imp-meta-card__icono"><i class="fas fa-handshake"></i></div>
                <div class="imp-meta-card__num">7</div>
                <div class="imp-meta-card__label">Iglesias anfitrionas</div>
                <p>Una iglesia local en cada ciudad será sede del programa, fortaleciendo la red eclesiástica bautista.</p>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     DIMENSIONES DEL IMPACTO
═══════════════════════════════════════════════ -->
<section class="seccion seccion--crema">
    <div class="container">
        <div class="seccion__encabezado">
            <span class="seccion-label">Transformación</span>
            <h2 class="seccion__titulo">Un impacto en <span class="highlight-dorado">4 dimensiones</span></h2>
        </div>

        <div class="imp-dimensiones">

            <div class="imp-dimension">
                <div class="imp-dimension__icono imp-dimension__icono--espiritual">
                    <i class="fas fa-cross"></i>
                </div>
                <div class="imp-dimension__contenido">
                    <h3>Impacto Espiritual</h3>
                    <p>
                        Formamos líderes con profunda vida devocional, sólido fundamento bíblico y pasión por
                        la evangelización. Cada misionero egresado lleva consigo el fuego del Espíritu para
                        transformar comunidades.
                    </p>
                    <ul class="imp-dimension__lista">
                        <li><i class="fas fa-check"></i> Formación bíblica y teológica intensiva</li>
                        <li><i class="fas fa-check"></i> Discipulado práctico en campo</li>
                        <li><i class="fas fa-check"></i> Evangelización en cada sede</li>
                    </ul>
                </div>
            </div>

            <div class="imp-dimension imp-dimension--invertida">
                <div class="imp-dimension__icono imp-dimension__icono--social">
                    <i class="fas fa-hands-helping"></i>
                </div>
                <div class="imp-dimension__contenido">
                    <h3>Impacto Social</h3>
                    <p>
                        Misioneros Integrales no solo predica el evangelio; también promueve el desarrollo
                        comunitario, la educación y el servicio social en las comunidades donde opera.
                    </p>
                    <ul class="imp-dimension__lista">
                        <li><i class="fas fa-check"></i> Programas de ayuda comunitaria</li>
                        <li><i class="fas fa-check"></i> Talleres de habilidades para la vida</li>
                        <li><i class="fas fa-check"></i> Apoyo a familias vulnerables</li>
                    </ul>
                </div>
            </div>

            <div class="imp-dimension">
                <div class="imp-dimension__icono imp-dimension__icono--economico">
                    <i class="fas fa-seedling"></i>
                </div>
                <div class="imp-dimension__contenido">
                    <h3>Impacto Económico</h3>
                    <p>
                        Capacitamos a los misioneros en el modelo de <strong>microempresa misionera</strong>:
                        negocios autosustentables que financian la obra sin depender solo de las ofrendas.
                    </p>
                    <ul class="imp-dimension__lista">
                        <li><i class="fas fa-check"></i> Formación en emprendimiento cristiano</li>
                        <li><i class="fas fa-check"></i> Modelos de autofinanciamiento misionero</li>
                        <li><i class="fas fa-check"></i> Sostenibilidad a largo plazo</li>
                    </ul>
                </div>
            </div>

            <div class="imp-dimension imp-dimension--invertida">
                <div class="imp-dimension__icono imp-dimension__icono--eclesial">
                    <i class="fas fa-church"></i>
                </div>
                <div class="imp-dimension__contenido">
                    <h3>Impacto Eclesial</h3>
                    <p>
                        Cada egresado regresa a su iglesia local con nuevas herramientas para multiplicar
                        discípulos, plantar congregaciones y fortalecer la red eclesiástica bautista venezolana.
                    </p>
                    <ul class="imp-dimension__lista">
                        <li><i class="fas fa-check"></i> Plantación de nuevas iglesias</li>
                        <li><i class="fas fa-check"></i> Fortalecimiento de la CNBV</li>
                        <li><i class="fas fa-check"></i> Red de misioneros egresados</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     ITINERARIO DE IMPACTO — MAPA
═══════════════════════════════════════════════ -->
<section class="seccion">
    <div class="container">
        <div class="seccion__encabezado">
            <span class="seccion-label">Venezuela</span>
            <h2 class="seccion__titulo">El itinerario de transformación</h2>
        </div>

        <div class="imp-itinerario">
            <?php
            $sedes_itinerario = [
                ['Los Teques',   'Miranda',    'Julio 2026',          '1'],
                ['Maracay',      'Aragua',     'Agosto 2026',         '2'],
                ['San Felipe',   'Yaracuy',    'Septiembre 2026',     '3'],
                ['Valencia',     'Carabobo',   'Octubre 2026',        '4'],
                ['Acarigua',     'Portuguesa', 'Noviembre 2026',      '5'],
                ['Barquisimeto', 'Lara',       'Dic. 2026 – Ene. 2027','6'],
                ['Trujillo',     'Trujillo',   'Febrero 2027',        '7'],
            ];
            foreach ($sedes_itinerario as $s):
            ?>
            <div class="imp-sede-paso">
                <div class="imp-sede-paso__burbuja"><?= $s[3] ?></div>
                <?php if ((int)$s[3] < 7): ?><div class="imp-sede-paso__conector"></div><?php endif; ?>
                <div class="imp-sede-paso__info">
                    <strong><?= $s[0] ?></strong>
                    <span><?= $s[1] ?></span>
                    <small><?= $s[2] ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     CTA FINAL
═══════════════════════════════════════════════ -->
<section class="seccion seccion--verde">
    <div class="container" style="text-align:center;">
        <span class="seccion-label" style="color:var(--dorado)">¿Quieres ser parte?</span>
        <h2 class="seccion__titulo">Sé el siguiente misionero transformado</h2>
        <p class="seccion__subtitulo" style="max-width:500px; margin:0 auto 2rem;">
            Postúlate para la Promoción 2026 (Ciclo 1). Las plazas son limitadas y nuevas cohortes se abren cada año.
        </p>
        <div style="display:flex; gap:1rem; justify-content:center; flex-wrap:wrap;">
            <a href="/registro" class="btn-cta-naranja">
                <i class="fas fa-user-plus"></i> Postularme ahora
            </a>
            <a href="/requisitos" class="btn btn--outline" style="color:white;border-color:rgba(255,255,255,0.5);">
                <i class="fas fa-clipboard-check"></i> Ver requisitos
            </a>
        </div>
    </div>
</section>

<script>
// Animación de contadores
function animarContadores() {
    document.querySelectorAll('.imp-stat-card__valor[data-target]').forEach(el => {
        const target = parseInt(el.dataset.target) || 0;
        if (target === 0) { el.textContent = '0'; return; }
        const duration = 2000;
        const start = performance.now();
        function update(now) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.floor(eased * target).toLocaleString('es-VE');
            if (progress < 1) requestAnimationFrame(update);
            else el.textContent = target.toLocaleString('es-VE');
        }
        requestAnimationFrame(update);
    });
}

// Disparar cuando la sección sea visible
const statsSection = document.querySelector('.imp-stats-grid');
if (statsSection) {
    const observer = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
            animarContadores();
            observer.disconnect();
        }
    }, { threshold: 0.3 });
    observer.observe(statsSection);
}
</script>

<style>
/* ── Hero ─────────────────────────────────────────────── */
.imp-hero {
    position: relative; min-height: 55vh;
    background: linear-gradient(160deg, #0a3d2b 0%, #0f5a45 40%, #167a5e 100%);
    display: flex; align-items: center; justify-content: center; text-align: center;
    overflow: hidden;
}
.imp-hero__overlay {
    position: absolute; inset: 0;
    background: radial-gradient(ellipse at 30% 70%, rgba(206,162,55,0.15) 0%, transparent 60%);
}
.imp-hero__content { position: relative; z-index: 1; padding: 4rem 1.5rem; }
.imp-hero__etiqueta {
    display: inline-block; font-size: 0.75rem; font-weight: 700;
    letter-spacing: 0.15em; color: var(--dorado); text-transform: uppercase;
    background: rgba(255,255,255,0.1); padding: 0.3rem 1rem; border-radius: 2rem; margin-bottom: 1.25rem;
}
.imp-hero__titulo {
    font-size: clamp(2.5rem, 6vw, 4rem); font-weight: 900; color: white; line-height: 1.1; margin-bottom: 1.5rem;
}
.imp-hero__titulo em { color: var(--dorado); font-style: normal; }
.imp-hero__sub {
    font-style: italic; color: rgba(255,255,255,0.85); font-size: 1.05rem;
    max-width: 560px; margin: 0 auto 0.5rem; line-height: 1.6;
}
.imp-hero__ref { color: var(--dorado); font-size: 0.85rem; font-weight: 600; }

/* ── Stats grid ───────────────────────────────────────── */
.imp-stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.5rem; margin-bottom: 1.25rem;
}
.imp-stat-card {
    text-align: center; padding: 2rem 1rem;
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
    border-radius: 1rem; backdrop-filter: blur(4px); transition: transform 0.2s;
}
.imp-stat-card:hover { transform: translateY(-4px); }
.imp-stat-card__icono {
    font-size: 2rem; color: var(--dorado); margin-bottom: 0.75rem;
}
.imp-stat-card__valor {
    font-size: 3rem; font-weight: 900; color: white; line-height: 1;
    margin-bottom: 0.5rem;
}
.imp-stat-card__label {
    font-size: 0.8rem; color: rgba(255,255,255,0.8); line-height: 1.3;
}
.imp-stats-nota {
    text-align: center; color: rgba(255,255,255,0.5); font-size: 0.78rem;
}
.imp-stats-nota i { margin-right: 0.4rem; }

/* ── Metas ────────────────────────────────────────────── */
.imp-metas-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1.25rem; margin-top: 2.5rem;
}
.imp-meta-card {
    background: white; border-radius: 1rem; padding: 1.75rem 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07); text-align: center;
    border-top: 4px solid var(--verde); transition: transform 0.2s;
}
.imp-meta-card:hover { transform: translateY(-4px); }
.imp-meta-card--destacada { border-top-color: var(--dorado); }
.imp-meta-card__icono {
    width: 3rem; height: 3rem; background: var(--verde-light); color: var(--verde);
    border-radius: 0.75rem; display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem; margin: 0 auto 0.75rem;
}
.imp-meta-card--destacada .imp-meta-card__icono { background: var(--dorado-light); color: var(--dorado-dark); }
.imp-meta-card__num { font-size: 2.5rem; font-weight: 900; color: var(--verde-dark); line-height: 1; margin-bottom: 0.25rem; }
.imp-meta-card--destacada .imp-meta-card__num { color: var(--dorado-dark); }
.imp-meta-card__label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 0.75rem; }
.imp-meta-card p { font-size: 0.83rem; color: #6b7280; line-height: 1.6; }

/* ── Dimensiones ──────────────────────────────────────── */
.imp-dimensiones { display: flex; flex-direction: column; gap: 2rem; margin-top: 2.5rem; }
.imp-dimension {
    display: grid; grid-template-columns: 80px 1fr; gap: 2rem; align-items: start;
    background: white; border-radius: 1rem; padding: 2rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.imp-dimension--invertida { background: #f8fafc; }
@media(max-width:640px) { .imp-dimension { grid-template-columns: 1fr; } }
.imp-dimension__icono {
    width: 4.5rem; height: 4.5rem; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center; font-size: 1.75rem;
}
.imp-dimension__icono--espiritual { background: #ede9fe; color: #7c3aed; }
.imp-dimension__icono--social     { background: #dbeafe; color: #2563eb; }
.imp-dimension__icono--economico  { background: var(--verde-light); color: var(--verde); }
.imp-dimension__icono--eclesial   { background: var(--dorado-light); color: var(--dorado-dark); }
.imp-dimension__contenido h3 { font-size: 1.2rem; font-weight: 800; color: var(--verde-dark); margin-bottom: 0.5rem; }
.imp-dimension__contenido p { font-size: 0.88rem; color: #6b7280; line-height: 1.7; margin-bottom: 0.75rem; }
.imp-dimension__lista { list-style: none; padding: 0; margin: 0; }
.imp-dimension__lista li { font-size: 0.83rem; color: #4b5563; padding: 0.2rem 0; }
.imp-dimension__lista li i { color: var(--verde); margin-right: 0.5rem; }

/* ── Itinerario ───────────────────────────────────────── */
.imp-itinerario {
    display: flex; flex-wrap: wrap; justify-content: center; gap: 0;
    margin-top: 2.5rem;
}
.imp-sede-paso {
    display: flex; flex-direction: column; align-items: center;
    position: relative; min-width: 110px;
}
.imp-sede-paso__burbuja {
    width: 2.75rem; height: 2.75rem; background: var(--verde); color: white;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 1rem; z-index: 1; box-shadow: 0 0 0 5px var(--verde-light);
}
.imp-sede-paso__conector {
    position: absolute; top: 1.35rem; left: calc(50% + 1.4rem);
    width: calc(100% - 2.8rem); height: 2px;
    background: linear-gradient(to right, var(--verde), var(--verde-light));
}
.imp-sede-paso__info {
    text-align: center; margin-top: 0.75rem;
    display: flex; flex-direction: column; gap: 0.1rem;
}
.imp-sede-paso__info strong { font-size: 0.85rem; color: var(--verde-dark); }
.imp-sede-paso__info span   { font-size: 0.72rem; color: #94a3b8; }
.imp-sede-paso__info small  { font-size: 0.68rem; color: var(--dorado-dark); font-weight: 600; }

.highlight-dorado { color: var(--dorado); }
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

/* ── Modelo de ciclos simultáneos ─────────────────────── */
.ciclos-modelo {
    width: 100%; border-radius: 0.75rem; overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-top: 2rem;
    border: 1px solid #e2e8f0;
}
.ciclos-modelo__fila {
    display: grid;
    grid-template-columns: 110px 1fr 1fr 1fr 140px;
    gap: 0; background: white;
    border-bottom: 1px solid #f1f5f9;
}
.ciclos-modelo__fila:last-child { border-bottom: none; }
.ciclos-modelo__fila--encabezado {
    background: var(--verde-dark); color: white;
    font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
}
.ciclos-modelo__fila--destacada { background: #f0fdf4; }
.ciclos-modelo__col-año,
.ciclos-modelo__col-ciclo,
.ciclos-modelo__col-resultado {
    padding: 0.85rem 1rem; display: flex; flex-direction: column;
    align-items: center; justify-content: center; text-align: center;
    border-right: 1px solid rgba(255,255,255,0.15);
}
.ciclos-modelo__fila:not(.ciclos-modelo__fila--encabezado) .ciclos-modelo__col-año,
.ciclos-modelo__fila:not(.ciclos-modelo__fila--encabezado) .ciclos-modelo__col-ciclo,
.ciclos-modelo__fila:not(.ciclos-modelo__fila--encabezado) .ciclos-modelo__col-resultado {
    border-right: 1px solid #f1f5f9;
}
.ciclos-modelo__col-año { background: rgba(0,0,0,0.03); }
.ciclos-año { font-size: 1.3rem; font-weight: 900; color: var(--verde-dark); }
.ciclos-modelo__col-año small { font-size: 0.7rem; color: #94a3b8; }
.ciclos-vacio { color: #d1d5db; font-size: 1.2rem; }
.ciclos-badge {
    font-size: 0.75rem; font-weight: 600; padding: 0.45rem 0.75rem;
    border-radius: 0.5rem; display: flex; align-items: center; gap: 0.4rem;
    width: 100%; justify-content: center;
}
.ciclos-badge--c1 { background: #dbeafe; color: #1e40af; }
.ciclos-badge--c2 { background: var(--dorado-light); color: var(--dorado-dark); }
.ciclos-badge--c3 { background: var(--verde-light); color: var(--verde-dark); }
.ciclos-res { font-size: 0.78rem; font-weight: 700; color: #64748b; }
.ciclos-res--full { color: var(--verde-dark); }
.ciclos-nota {
    display: flex; gap: 1rem; align-items: flex-start;
    background: var(--dorado-light); border: 1px solid var(--dorado);
    border-radius: 0.75rem; padding: 1rem 1.25rem; margin-top: 1.25rem;
}
.ciclos-nota i { color: var(--dorado-dark); font-size: 1.1rem; margin-top: 0.1rem; flex-shrink: 0; }
.ciclos-nota p { font-size: 0.85rem; color: #4b5563; margin: 0; line-height: 1.6; }
@media(max-width:700px) {
    .ciclos-modelo__fila { grid-template-columns: 70px 1fr 1fr; }
    .ciclos-modelo__col-ciclo:nth-child(4),
    .ciclos-modelo__col-resultado { display: none; }
}
</style>
