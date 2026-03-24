<?php
$titulo = 'Malla Curricular — Licenciatura en Teología';

/* ══════════════════════════════════════════════════════════════
   DATOS DEL PENSUM
   Fuente: DIGEN · Hoja de Trabajo 2026
   Para actualizar: editar este array y refrescar la página
   ══════════════════════════════════════════════════════════════ */

$areas = [
    'fundamentos'  => ['label' => 'Fundamentos',   'color' => '#1e3a5f'],
    'biblia'       => ['label' => 'Biblia',         'color' => '#2e7d32'],
    'teologia'     => ['label' => 'Teología',       'color' => '#6a1b9a'],
    'ministerio'   => ['label' => 'Ministerio',     'color' => '#e65100'],
    'humanidades'  => ['label' => 'Humanidades',    'color' => '#546e7a'],
    'misionologia' => ['label' => 'Misionología',   'color' => '#b71c1c'],
    'investigacion'=> ['label' => 'Investigación',  'color' => '#1a237e'],
];

$pensum = [
    [
        'anio'   => 1,
        'titulo' => 'Fundamentos Bíblicos y Ministeriales',
        'trimestres' => [
            1 => [
                ['cod'=>'IB012',   'nombre'=>'Intro. Bíblica',    'uc'=>3, 'area'=>'fundamentos'],
                ['cod'=>'TI0120',  'nombre'=>'Lenguaje',          'uc'=>2, 'area'=>'fundamentos'],
                ['cod'=>'VD0120',  'nombre'=>'Vida Discipular',   'uc'=>2, 'area'=>'fundamentos'],
                ['cod'=>'MN0120',  'nombre'=>'Formación',         'uc'=>2, 'area'=>'fundamentos'],
                ['cod'=>'AT012',   'nombre'=>'Intro. A.T.',       'uc'=>2, 'area'=>'biblia'],
            ],
            2 => [
                ['cod'=>'NT012',   'nombre'=>'Intro. N.T.',       'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'HR012',   'nombre'=>'Hermenéutica',      'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'HI012',   'nombre'=>'Hist. Iglesia 1',   'uc'=>2, 'area'=>'humanidades'],
                ['cod'=>'AD032',   'nombre'=>'Liderazgo',         'uc'=>2, 'area'=>'ministerio'],
                ['cod'=>'AD022',   'nombre'=>'Mayordomía',        'uc'=>2, 'area'=>'ministerio'],
            ],
            3 => [
                ['cod'=>'EV013',   'nombre'=>'Evangelismo',       'uc'=>3, 'area'=>'biblia'],
                ['cod'=>'NT022',   'nombre'=>'Evangelios',        'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'AT022',   'nombre'=>'Pentateuco',        'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'HI022',   'nombre'=>'Hist. Iglesia 2',   'uc'=>2, 'area'=>'humanidades'],
                ['cod'=>'ED012',   'nombre'=>'Enseñanza',         'uc'=>2, 'area'=>'humanidades'],
            ],
        ],
    ],
    [
        'anio'   => 2,
        'titulo' => 'Teología Sistemática y Exégesis',
        'trimestres' => [
            4 => [
                ['cod'=>'TE013',   'nombre'=>'Sistemática 1',     'uc'=>3, 'area'=>'teologia'],
                ['cod'=>'TE032',   'nombre'=>'Adoración',         'uc'=>2, 'area'=>'teologia'],
                ['cod'=>'NT032',   'nombre'=>'Hechos',            'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'NT042',   'nombre'=>'Epístolas',         'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'AD012',   'nombre'=>'Adm. Iglesia',      'uc'=>2, 'area'=>'ministerio'],
            ],
            5 => [
                ['cod'=>'TE023',   'nombre'=>'Sistemática 2',     'uc'=>3, 'area'=>'teologia'],
                ['cod'=>'ET013',   'nombre'=>'Ética',             'uc'=>3, 'area'=>'teologia'],
                ['cod'=>'ET023',   'nombre'=>'Matrimonio y Familia','uc'=>3,'area'=>'teologia'],
                ['cod'=>'AT032',   'nombre'=>'L. Históricos',     'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'PR013',   'nombre'=>'Predicación 1',     'uc'=>3, 'area'=>'ministerio'],
            ],
            6 => [
                ['cod'=>'TE052',   'nombre'=>'Teol. de la Iglesia','uc'=>2,'area'=>'teologia'],
                ['cod'=>'AT052',   'nombre'=>'L. Proféticos',     'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'AT042',   'nombre'=>'L. Sapienciales',   'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'NT052',   'nombre'=>'Apocalipsis',       'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'IIB013',  'nombre'=>'Idiomas Bíblicos',  'uc'=>3, 'area'=>'biblia'],
            ],
        ],
    ],
    [
        'anio'   => 3,
        'titulo' => 'Profundización Teológica',
        'trimestres' => [
            7 => [
                ['cod'=>'AT062',   'nombre'=>'Teol. del A.T.',    'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'NT062',   'nombre'=>'Teol. del N.T.',    'uc'=>2, 'area'=>'biblia'],
                ['cod'=>'MI013',   'nombre'=>'Intro. Misionología','uc'=>3,'area'=>'misionologia'],
                ['cod'=>'HI033',   'nombre'=>'Hist. Bautista',    'uc'=>3, 'area'=>'humanidades'],
                ['cod'=>'PR023',   'nombre'=>'Predicación 2',     'uc'=>3, 'area'=>'ministerio'],
            ],
            8 => [
                ['cod'=>'PS013',   'nombre'=>'Psicología',        'uc'=>3, 'area'=>'humanidades'],
                ['cod'=>'FI012',   'nombre'=>'Hist. de la Filosofía','uc'=>2,'area'=>'humanidades'],
                ['cod'=>'FI022',   'nombre'=>'Filosofía General', 'uc'=>2, 'area'=>'humanidades'],
                ['cod'=>'MN023',   'nombre'=>'Asesoramiento',     'uc'=>3, 'area'=>'ministerio'],
            ],
            9 => [
                ['cod'=>'AN112',   'nombre'=>'Antropología',      'uc'=>2, 'area'=>'misionologia'],
                ['cod'=>'MI152',   'nombre'=>'Intercultural',     'uc'=>2, 'area'=>'misionologia'],
                ['cod'=>'PE012',   'nombre'=>'Pedagogía',         'uc'=>2, 'area'=>'humanidades'],
                ['cod'=>'HI112',   'nombre'=>'Hist. de Misiones', 'uc'=>2, 'area'=>'humanidades'],
                ['cod'=>'TI0310',  'nombre'=>'Investigación',     'uc'=>1, 'area'=>'investigacion'],
            ],
        ],
    ],
];

// Totales calculados
$total_materias = 0;
$total_uc       = 0;
foreach ($pensum as $anio) {
    foreach ($anio['trimestres'] as $materias) {
        $total_materias += count($materias);
        foreach ($materias as $m) $total_uc += $m['uc'];
    }
}
?>

<!-- ── Banner estado: pendiente de validación ────────── -->
<div class="pensum-aviso">
    <div class="container pensum-aviso__inner">
        <div class="pensum-aviso__icono">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div>
            <strong>Malla curricular en proceso de validación</strong>
            <span>Este pensum fue elaborado por DIGEN y está siendo evaluado por el
            Seminario Teológico Bautista de Venezuela (STBV) para el otorgamiento
            del título de Licenciatura. El contenido puede cambiar antes de su aprobación definitiva.</span>
        </div>
        <div class="pensum-aviso__stbv">
            <img src="/public/assets/logos/logo-stbv.jpg"
                 alt="STBV" title="Seminario Teológico Bautista de Venezuela">
        </div>
    </div>
</div>

<!-- ── Hero de la página ─────────────────────────────── -->
<section class="pensum-hero">
    <div class="container pensum-hero__inner">
        <div>
            <p class="pensum-hero__sup">
                <i class="fas fa-book-open"></i>
                Programa académico — DIME / CNBV
            </p>
            <h1>Licenciatura en Teología<br><span>Mención Misiones</span></h1>
            <p class="pensum-hero__sub">
                Formación integral en 3 ciclos para quienes sienten el llamado a las misiones.
                Al completar los ~45 cursos recibes la certificación oficial
                avalada por la CNBV — pendiente de validación del STBV.
            </p>
        </div>
        <div class="pensum-hero__stats">
            <div class="pensum-stat-box">
                <strong>3</strong><span>Ciclos</span>
            </div>
            <div class="pensum-stat-box">
                <strong>9</strong><span>Trimestres</span>
            </div>
            <div class="pensum-stat-box">
                <strong>~45</strong><span>Materias</span>
            </div>
            <div class="pensum-stat-box pensum-stat-box--dorado">
                <strong>~100</strong><span>Unidades de Crédito</span>
            </div>
        </div>
    </div>
</section>

<!-- ── Leyenda de áreas ───────────────────────────────── -->
<div class="pensum-leyenda container">
    <?php foreach ($areas as $key => $area): ?>
    <span class="pensum-tag" style="--area-color:<?= $area['color'] ?>">
        <?= htmlspecialchars($area['label']) ?>
    </span>
    <?php endforeach; ?>
</div>

<!-- ── Tabs de años ───────────────────────────────────── -->
<div class="pensum-tabs container" id="pensum-tabs">
    <?php foreach ($pensum as $i => $anio): ?>
    <button class="pensum-tab <?= $i === 0 ? 'activo' : '' ?>"
            data-tab="anio<?= $anio['anio'] ?>">
        <span class="pensum-tab__num">Ciclo <?= $anio['anio'] ?></span>
        <span class="pensum-tab__sub"><?= htmlspecialchars($anio['titulo']) ?></span>
    </button>
    <?php endforeach; ?>
</div>

<!-- ── Grillas por año ────────────────────────────────── -->
<div class="container pensum-contenido">

    <?php foreach ($pensum as $i => $anio): ?>
    <div class="pensum-anio <?= $i === 0 ? 'visible' : '' ?>"
         id="anio<?= $anio['anio'] ?>">

        <div class="pensum-anio__header">
            <h2>
                <span class="pensum-anio__num">Ciclo <?= $anio['anio'] ?></span>
                <?= htmlspecialchars($anio['titulo']) ?>
            </h2>
            <?php /* El número de trimestres en este ciclo */ ?>
            <?php
            $uc_anio = 0;
            $mat_anio = 0;
            foreach ($anio['trimestres'] as $materias) {
                $mat_anio += count($materias);
                foreach ($materias as $m) $uc_anio += $m['uc'];
            }
            ?>
            <span class="pensum-anio__resumen">
                <?= $mat_anio ?> materias · <?= $uc_anio ?> UC
            </span>
        </div>

        <div class="pensum-grid">
            <?php foreach ($anio['trimestres'] as $num_trim => $materias): ?>
            <div class="pensum-trimestre">
                <div class="pensum-trimestre__header">
                    <span>Trimestre <?= $num_trim ?></span>
                    <span class="pensum-trimestre__uc">
                        <?= array_sum(array_column($materias, 'uc')) ?> UC
                    </span>
                </div>
                <?php foreach ($materias as $m): ?>
                <?php $color = $areas[$m['area']]['color'] ?? '#083c2b'; ?>
                <div class="pensum-materia" style="--mc:<?= $color ?>">
                    <div class="pensum-materia__top">
                        <span class="pensum-materia__cod"><?= htmlspecialchars($m['cod']) ?></span>
                        <span class="pensum-materia__uc"><?= $m['uc'] ?> UC</span>
                    </div>
                    <div class="pensum-materia__nombre"><?= htmlspecialchars($m['nombre']) ?></div>
                    <div class="pensum-materia__area"><?= htmlspecialchars($areas[$m['area']]['label']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
    <?php endforeach; ?>

</div>

<!-- ── CTA final ──────────────────────────────────────── -->
<div class="pensum-cta container">
    <div class="pensum-cta__card">
        <div class="pensum-cta__texto">
            <h3>¿Este camino es tuyo?</h3>
            <p>La primera cohorte inicia en julio 2026. Las postulaciones están abiertas.</p>
        </div>
        <div class="pensum-cta__acciones">
            <a href="/registro" class="btn btn--verde">
                <i class="fas fa-user-plus"></i> Postularme ahora
            </a>
            <a href="/programa" class="btn btn--outline">
                <i class="fas fa-arrow-left"></i> Ver el programa
            </a>
        </div>
    </div>
</div>

<style>
/* ── Aviso validación ────────────────────────────────── */
.pensum-aviso {
    background: #fff8e1;
    border-bottom: 2px solid #f59e0b;
}
.pensum-aviso__inner {
    display: flex; align-items: center; gap: 1rem;
    padding: 0.9rem 0; flex-wrap: wrap;
}
.pensum-aviso__icono {
    width: 38px; height: 38px; flex-shrink: 0;
    background: #f59e0b; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1rem;
}
.pensum-aviso__inner > div:nth-child(2) {
    flex: 1; font-size: 0.86rem; color: #78350f; min-width: 200px;
}
.pensum-aviso__inner > div:nth-child(2) strong {
    display: block; margin-bottom: 0.2rem; font-size: 0.9rem; color: #92400e;
}
.pensum-aviso__stbv img {
    height: 44px; object-fit: contain; opacity: 0.85;
}

/* ── Hero ────────────────────────────────────────────── */
.pensum-hero {
    background: linear-gradient(135deg, var(--verde-dark) 0%, #167a5e 100%);
    padding: 4rem 0 3rem;
    color: #fff;
}
.pensum-hero__inner {
    display: flex; align-items: center; justify-content: space-between;
    gap: 2.5rem; flex-wrap: wrap;
}
.pensum-hero__sup {
    font-size: 0.78rem; font-weight: 700;
    color: var(--dorado, #cea237); text-transform: uppercase;
    letter-spacing: 1.5px; margin: 0 0 0.75rem;
    display: flex; align-items: center; gap: 0.4rem;
}
.pensum-hero h1 {
    font-size: 2.4rem; font-weight: 900; margin: 0 0 1rem;
    line-height: 1.1; color: #fff;
}
.pensum-hero h1 span { color: var(--dorado, #cea237); }
.pensum-hero__sub {
    font-size: 0.95rem; color: rgba(255,255,255,0.75);
    line-height: 1.65; margin: 0; max-width: 520px;
}

.pensum-hero__stats {
    display: flex; gap: 1rem; flex-wrap: wrap; flex-shrink: 0;
}
.pensum-stat-box {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 14px; padding: 1rem 1.25rem;
    text-align: center; min-width: 80px;
}
.pensum-stat-box strong {
    display: block; font-size: 2rem; font-weight: 900;
    color: #fff; line-height: 1;
}
.pensum-stat-box span {
    display: block; font-size: 0.7rem;
    color: rgba(255,255,255,0.6); margin-top: 0.2rem;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.pensum-stat-box--dorado {
    background: rgba(206,162,55,0.2);
    border-color: rgba(206,162,55,0.4);
}
.pensum-stat-box--dorado strong { color: var(--dorado, #cea237); }

/* ── Leyenda ─────────────────────────────────────────── */
.pensum-leyenda {
    display: flex; flex-wrap: wrap; gap: 0.6rem;
    padding-top: 1.75rem; padding-bottom: 0.25rem;
}
.pensum-tag {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.8rem;
    border-radius: 100px;
    font-size: 0.75rem; font-weight: 700;
    background: var(--area-color);
    color: #fff;
    opacity: 0.9;
}
.pensum-tag::before {
    content: '';
    width: 7px; height: 7px; border-radius: 50%;
    background: rgba(255,255,255,0.6);
}

/* ── Tabs ────────────────────────────────────────────── */
.pensum-tabs {
    display: flex; gap: 0.5rem; flex-wrap: wrap;
    padding-top: 1.5rem; padding-bottom: 0;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 2rem;
}
.pensum-tab {
    display: flex; flex-direction: column; align-items: flex-start;
    padding: 0.75rem 1.25rem;
    background: none; border: none;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    cursor: pointer; font-family: inherit;
    border-radius: 8px 8px 0 0;
    transition: background 0.2s, border-color 0.2s;
    text-align: left;
}
.pensum-tab:hover { background: rgba(22,122,94,0.06); }
.pensum-tab.activo {
    border-bottom-color: var(--verde, #167a5e);
    background: rgba(22,122,94,0.06);
}
.pensum-tab__num {
    font-size: 0.82rem; font-weight: 800;
    color: var(--verde, #167a5e); text-transform: uppercase; letter-spacing: 0.5px;
}
.pensum-tab__sub {
    font-size: 0.75rem; color: var(--gris, #6b7280); margin-top: 0.1rem;
    max-width: 160px; line-height: 1.3;
}
.pensum-tab.activo .pensum-tab__num { color: var(--verde-dark, #083c2b); }
.pensum-tab.activo .pensum-tab__sub { color: var(--gris-dark, #374151); }

/* ── Contenido por año ───────────────────────────────── */
.pensum-anio { display: none; }
.pensum-anio.visible { display: block; }

.pensum-anio__header {
    display: flex; align-items: baseline; gap: 1rem; flex-wrap: wrap;
    margin-bottom: 1.5rem;
}
.pensum-anio__header h2 {
    font-size: 1.35rem; font-weight: 900; color: var(--verde-dark);
    margin: 0; display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;
}
.pensum-anio__num {
    background: var(--verde-dark);
    color: #fff; font-size: 0.72rem; font-weight: 800;
    padding: 0.2rem 0.6rem; border-radius: 6px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.pensum-anio__resumen {
    font-size: 0.82rem; color: var(--gris); margin-left: auto;
    white-space: nowrap;
}

/* ── Grid de trimestres ──────────────────────────────── */
.pensum-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

.pensum-trimestre {
    background: var(--crema, #faf8f3);
    border: 1px solid rgba(22,122,94,0.1);
    border-radius: 16px;
    overflow: hidden;
}
.pensum-trimestre__header {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--verde-dark); color: #fff;
    padding: 0.65rem 1rem;
    font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px;
}
.pensum-trimestre__uc {
    color: var(--dorado, #cea237);
    font-size: 0.8rem;
}

/* ── Tarjeta de materia ──────────────────────────────── */
.pensum-materia {
    display: flex; flex-direction: column;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid rgba(22,122,94,0.07);
    border-left: 3px solid var(--mc, #167a5e);
    transition: background 0.15s;
}
.pensum-materia:last-child { border-bottom: none; }
.pensum-materia:hover { background: rgba(0,0,0,0.02); }

.pensum-materia__top {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 0.2rem;
}
.pensum-materia__cod {
    font-size: 0.68rem; font-weight: 800;
    color: var(--mc, #167a5e);
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}
.pensum-materia__uc {
    font-size: 0.68rem; font-weight: 700;
    background: rgba(0,0,0,0.06); border-radius: 4px;
    padding: 0.1rem 0.35rem; color: var(--gris-dark);
}
.pensum-materia__nombre {
    font-size: 0.86rem; font-weight: 700;
    color: var(--gris-dark, #374151); line-height: 1.3;
}
.pensum-materia__area {
    font-size: 0.68rem; color: var(--gris, #6b7280);
    margin-top: 0.2rem;
}

/* ── CTA final ───────────────────────────────────────── */
.pensum-cta { padding: 2.5rem 0 3.5rem; }
.pensum-cta__card {
    background: linear-gradient(135deg, var(--verde-dark) 0%, #167a5e 100%);
    border-radius: 20px; padding: 2rem 2.5rem;
    display: flex; align-items: center;
    justify-content: space-between; gap: 2rem; flex-wrap: wrap;
}
.pensum-cta__texto h3 {
    font-size: 1.35rem; font-weight: 900; color: #fff; margin: 0 0 0.35rem;
}
.pensum-cta__texto p { font-size: 0.9rem; color: rgba(255,255,255,0.7); margin: 0; }
.pensum-cta__acciones { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.pensum-cta__acciones .btn--outline {
    border-color: rgba(255,255,255,0.4); color: rgba(255,255,255,0.85);
}
.pensum-cta__acciones .btn--outline:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.7);
}

/* ── Responsive ──────────────────────────────────────── */
@media (max-width: 900px) {
    .pensum-grid { grid-template-columns: 1fr 1fr; }
    .pensum-hero h1 { font-size: 1.9rem; }
    .pensum-tab__sub { display: none; }
    .pensum-tab { padding: 0.65rem 1rem; }
}
@media (max-width: 600px) {
    .pensum-grid { grid-template-columns: 1fr; }
    .pensum-hero__inner { flex-direction: column; }
    .pensum-hero__stats { width: 100%; }
    .pensum-tabs { gap: 0.25rem; }
    .pensum-aviso__stbv { display: none; }
    .pensum-cta__card { flex-direction: column; text-align: center; }
}
</style>

<script>
// Tabs interactivos
document.getElementById('pensum-tabs').addEventListener('click', function(e) {
    var btn = e.target.closest('.pensum-tab');
    if (!btn) return;

    // Activar tab
    document.querySelectorAll('.pensum-tab').forEach(t => t.classList.remove('activo'));
    btn.classList.add('activo');

    // Mostrar panel
    var target = btn.dataset.tab;
    document.querySelectorAll('.pensum-anio').forEach(p => p.classList.remove('visible'));
    document.getElementById(target)?.classList.add('visible');

    // Scroll suave al panel en móvil
    if (window.innerWidth < 768) {
        document.getElementById(target)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
</script>
