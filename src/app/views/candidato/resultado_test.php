<?php
/* ================================================================
   RESULTADO DEL TEST VOCACIONAL — Perfil orientacional
   Layout: candidato (sidebar + main)
================================================================ */

// Etiquetas legibles para respuestas seleccionadas
function etiqueta(array $mapa, string $val): string {
    return htmlspecialchars($mapa[$val] ?? $val);
}

$intereses_ministerio = [
    'evangelismo'=>'Evangelismo y predicación','discipulado'=>'Discipulado','plantacion'=>'Plantación de iglesias',
    'servicio_social'=>'Servicio social','ninos_jovenes'=>'Niños y jóvenes','adoracion'=>'Adoración y música',
    'ensenanza'=>'Enseñanza bíblica','compasion'=>'Ministerio de compasión',
];
$dones = [
    'evangelismo'=>'Evangelismo','ensenanza'=>'Enseñanza','servicio'=>'Servicio','liderazgo'=>'Liderazgo',
    'misericordia'=>'Misericordia','exhortacion'=>'Exhortación','discernimiento'=>'Discernimiento',
    'fe'=>'Fe','administracion'=>'Administración','ayuda'=>'Ayuda','hospitalidad'=>'Hospitalidad',
];
$plan_post = [
    'misionero_tc'=>'Servir como misionero/a a tiempo completo','plantar_iglesia'=>'Plantar o revitalizar una iglesia',
    'regresar_iglesia'=>'Regresar a mi iglesia con nuevas capacidades','estudiar_teologia'=>'Continuar estudiando teología',
    'emprendimiento'=>'Iniciar un emprendimiento misionero','no_claro'=>'Aún no lo sé con claridad','otro'=>'Otro',
];

// Color por score
function colorBarra(int $score): string {
    if ($score >= 75) return '#167a5e';
    if ($score >= 50) return '#cea237';
    return '#e67e22';
}
function etiquetaNivel(int $score): string {
    if ($score >= 75) return 'Fortaleza';
    if ($score >= 50) return 'En desarrollo';
    return 'Área de crecimiento';
}
?>

<div class="dashboard-layout">

<!-- ====================================================
     SIDEBAR
===================================================== -->
<aside class="dash-sidebar">
    <div class="dash-sidebar__user">
        <div class="dash-sidebar__avatar">
            <?= mb_strtoupper(mb_substr($_SESSION['usuario_nombre'], 0, 1)) ?>
        </div>
        <div class="dash-sidebar__info">
            <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
            <span class="badge badge--<?= $aspirante['estatus'] ?? 'borrador' ?>">
                <?= ucfirst(str_replace('_', ' ', $aspirante['estatus'] ?? 'borrador')) ?>
            </span>
        </div>
    </div>
    <nav class="dash-nav">
        <a href="/candidato/dashboard"  class="dash-nav__item"><i class="fas fa-tachometer-alt"></i> Mi Dashboard</a>
        <a href="/candidato/perfil"     class="dash-nav__item"><i class="fas fa-user-edit"></i> Mi Perfil</a>
        <a href="/candidato/documentos" class="dash-nav__item"><i class="fas fa-folder-open"></i> Documentos</a>
        <a href="/candidato/test"       class="dash-nav__item activo"><i class="fas fa-clipboard-list"></i> Test Vocacional</a>
        <a href="/candidato/pagos"      class="dash-nav__item"><i class="fas fa-dollar-sign"></i> Mis Pagos</a>
        <a href="/logout"               class="dash-nav__item dash-nav__item--logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
</aside>

<!-- ====================================================
     MAIN
===================================================== -->
<main class="dash-main">

    <!-- Header -->
    <div class="dash-header">
        <div>
            <h1>Mi Perfil Vocacional</h1>
            <p>Test completado el <?= date('d/m/Y', strtotime($test['fecha_cierre'])) ?></p>
        </div>
        <a href="/candidato/test" class="btn btn--outline btn--sm">
            <i class="fas fa-arrow-left"></i> Volver al test
        </a>
    </div>

    <!-- AVISO ORIENTACIONAL -->
    <div class="resultado-aviso">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>Perfil orientacional, no determinante.</strong>
            Este resultado refleja tendencias vocacionales basadas en tus respuestas.
            No es un puntaje de admisión. El equipo evaluador toma la decisión final
            considerando tu perfil completo, documentos y entrevista personal.
        </div>
    </div>

    <!-- FORTALEZAS DESTACADAS -->
    <div class="resultado-fortalezas-wrap">
        <h2 class="resultado-seccion-titulo"><i class="fas fa-star"></i> Tus áreas más fuertes</h2>
        <div class="resultado-fortalezas">
            <?php foreach ($perfil['fortalezas'] as $i => $nombre): ?>
            <div class="fortaleza-card fortaleza-card--<?= $i + 1 ?>">
                <div class="fortaleza-card__num"><?= $i + 1 ?></div>
                <div class="fortaleza-card__nombre"><?= htmlspecialchars($nombre) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- BARRAS POR DIMENSIÓN -->
    <div class="resultado-dims">
        <h2 class="resultado-seccion-titulo"><i class="fas fa-chart-bar"></i> Perfil por dimensión</h2>
        <p class="resultado-dims__nota">Cada barra muestra qué tan marcada es esa dimensión según tus respuestas.</p>

        <div class="resultado-dims__lista">
            <?php foreach ($perfil['dimensiones'] as $dim): ?>
            <div class="resultado-dim">
                <div class="resultado-dim__header">
                    <span class="resultado-dim__nombre">
                        <i class="fas <?= htmlspecialchars($dim['icono']) ?>"></i>
                        <?= htmlspecialchars($dim['nombre']) ?>
                    </span>
                    <span class="resultado-dim__badge" style="background:<?= colorBarra($dim['score']) ?>20; color:<?= colorBarra($dim['score']) ?>;">
                        <?= etiquetaNivel($dim['score']) ?>
                    </span>
                </div>
                <div class="resultado-dim__barra-bg">
                    <div class="resultado-dim__barra-fill"
                         style="width:<?= $dim['score'] ?>%; background:<?= colorBarra($dim['score']) ?>;">
                    </div>
                </div>
                <div class="resultado-dim__pct"><?= $dim['score'] ?>%</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- INTERESES Y DONES (respuestas cualitativas) -->
    <div class="resultado-cualitativo">
        <h2 class="resultado-seccion-titulo"><i class="fas fa-compass"></i> Lo que nos dijiste sobre ti</h2>

        <div class="resultado-tags-grupo">
            <?php if (!empty($respuestas['q6']) && is_array($respuestas['q6'])): ?>
            <div class="resultado-tag-bloque">
                <strong>Aspectos del ministerio que te atraen:</strong>
                <div class="resultado-tags">
                    <?php foreach ($respuestas['q6'] as $v): ?>
                    <span class="resultado-tag resultado-tag--verde"><?= htmlspecialchars($intereses_ministerio[$v] ?? $v) ?></span>
                    <?php endforeach; ?>
                    <?php if (!empty($respuestas['q6_otro'])): ?>
                    <span class="resultado-tag resultado-tag--gris"><?= htmlspecialchars($respuestas['q6_otro']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($respuestas['q26']) && is_array($respuestas['q26'])): ?>
            <div class="resultado-tag-bloque">
                <strong>Dones espirituales que identificas en ti:</strong>
                <div class="resultado-tags">
                    <?php foreach ($respuestas['q26'] as $v): ?>
                    <span class="resultado-tag resultado-tag--dorado"><?= htmlspecialchars($dones[$v] ?? $v) ?></span>
                    <?php endforeach; ?>
                    <?php if (!empty($respuestas['q26_otro'])): ?>
                    <span class="resultado-tag resultado-tag--gris"><?= htmlspecialchars($respuestas['q26_otro']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($respuestas['q38'])): ?>
            <?php $areas_emp = ['panaderia'=>'Panadería y repostería','textiles'=>'Textiles y sublimación','mantenimiento'=>'Mantenimiento','limpieza'=>'Limpieza y estética','artesania'=>'Artesanía']; ?>
            <div class="resultado-tag-bloque">
                <strong>Área de emprendimiento de interés:</strong>
                <div class="resultado-tags">
                    <span class="resultado-tag resultado-tag--naranja">
                        <?= htmlspecialchars($areas_emp[$respuestas['q38']] ?? $respuestas['q38']) ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($respuestas['q50'])): ?>
            <div class="resultado-tag-bloque">
                <strong>Plan después del programa:</strong>
                <div class="resultado-tags">
                    <span class="resultado-tag resultado-tag--azul">
                        <?= htmlspecialchars($plan_post[$respuestas['q50']] ?? $respuestas['q50']) ?>
                    </span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($respuestas['q12'])): ?>
        <div class="resultado-frase">
            <i class="fas fa-quote-left"></i>
            <blockquote>«<?= htmlspecialchars($respuestas['q12']) ?>»</blockquote>
            <small>— Tu relación con Dios en una frase</small>
        </div>
        <?php endif; ?>
    </div>

    <!-- PREGUNTAS ABIERTAS — para el evaluador (solo se muestran si tiene contenido) -->
    <?php
    $abiertas = [
        'q3'  => 'Eventos que confirmaron tu llamado misionero',
        'q13' => 'Mayor desafío espiritual actual',
        'q44' => 'Cómo manejas dificultades o fracasos',
        'q51' => 'Convivencia con diferencias (Escenario 1)',
        'q52' => 'Comunidad que rechaza el evangelio (Escenario 2)',
        'q53' => 'Emprendimiento sin resultados esperados (Escenario 3)',
    ];
    $hay_abiertas = false;
    foreach ($abiertas as $k => $_) {
        if (!empty($respuestas[$k])) { $hay_abiertas = true; break; }
    }
    ?>
    <?php if ($hay_abiertas): ?>
    <div class="resultado-abiertas">
        <h2 class="resultado-seccion-titulo">
            <i class="fas fa-pen-alt"></i> Tus respuestas escritas
            <span class="resultado-abiertas__nota">El equipo evaluador las lee en detalle</span>
        </h2>
        <?php foreach ($abiertas as $k => $label): ?>
            <?php if (!empty($respuestas[$k])): ?>
            <div class="resultado-abierta-item">
                <strong><?= htmlspecialchars($label) ?></strong>
                <p><?= nl2br(htmlspecialchars($respuestas[$k])) ?></p>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CTA -->
    <div class="resultado-cta">
        <a href="/candidato/dashboard" class="btn btn--verde">
            <i class="fas fa-tachometer-alt"></i> Ir a mi Dashboard
        </a>
        <a href="/contacto" class="btn btn--outline">
            <i class="fas fa-envelope"></i> Contactar al equipo
        </a>
    </div>

</main>
</div>

<style>
/* ── Aviso orientacional ─────────────────────────────── */
.resultado-aviso {
    display: flex; gap: 1rem; align-items: flex-start;
    background: #fffbeb; border: 1px solid var(--dorado);
    border-radius: 0.75rem; padding: 1rem 1.25rem; margin-bottom: 1.5rem;
    font-size: 0.875rem; color: #4b5563; line-height: 1.6;
}
.resultado-aviso i { color: var(--dorado); font-size: 1.1rem; margin-top: 0.15rem; flex-shrink: 0; }
.resultado-aviso strong { color: #92400e; }

/* ── Título de sección ───────────────────────────────── */
.resultado-seccion-titulo {
    font-size: 1.05rem; font-weight: 800; color: var(--verde-dark);
    margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;
}
.resultado-seccion-titulo i { color: var(--verde); }

/* ── Fortalezas ──────────────────────────────────────── */
.resultado-fortalezas-wrap {
    background: white; border-radius: 1rem; padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 1.5rem;
}
.resultado-fortalezas {
    display: flex; gap: 1rem; flex-wrap: wrap;
}
.fortaleza-card {
    flex: 1; min-width: 160px; border-radius: 0.75rem; padding: 1.25rem 1rem;
    display: flex; align-items: center; gap: 0.75rem;
    font-weight: 700; font-size: 0.9rem;
}
.fortaleza-card--1 { background: var(--verde); color: white; }
.fortaleza-card--2 { background: var(--verde-light); color: var(--verde-dark); border: 2px solid var(--verde); }
.fortaleza-card--3 { background: var(--dorado-light, #fef3c7); color: #92400e; border: 2px solid var(--dorado); }
.fortaleza-card__num {
    width: 2rem; height: 2rem; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 1rem; flex-shrink: 0;
    background: rgba(255,255,255,0.3);
}

/* ── Barras por dimensión ────────────────────────────── */
.resultado-dims {
    background: white; border-radius: 1rem; padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 1.5rem;
}
.resultado-dims__nota { font-size: 0.82rem; color: #6b7280; margin-bottom: 1.5rem; }
.resultado-dims__lista { display: flex; flex-direction: column; gap: 1rem; }
.resultado-dim { }
.resultado-dim__header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 0.4rem;
}
.resultado-dim__nombre { font-size: 0.88rem; font-weight: 600; color: #1e293b; }
.resultado-dim__nombre i { color: var(--verde); margin-right: 0.35rem; }
.resultado-dim__badge {
    font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.65rem;
    border-radius: 2rem;
}
.resultado-dim__barra-bg {
    height: 10px; background: #f1f5f9; border-radius: 2rem; overflow: hidden;
}
.resultado-dim__barra-fill {
    height: 100%; border-radius: 2rem;
    transition: width 1s ease-out;
}
.resultado-dim__pct {
    font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 0.2rem;
}

/* ── Cualitativo ─────────────────────────────────────── */
.resultado-cualitativo {
    background: white; border-radius: 1rem; padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 1.5rem;
}
.resultado-tags-grupo { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.25rem; }
.resultado-tag-bloque strong { font-size: 0.82rem; color: #64748b; display: block; margin-bottom: 0.4rem; }
.resultado-tags { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.resultado-tag {
    font-size: 0.8rem; font-weight: 600; padding: 0.3rem 0.85rem;
    border-radius: 2rem;
}
.resultado-tag--verde  { background: var(--verde-light); color: var(--verde-dark); }
.resultado-tag--dorado { background: #fef3c7; color: #92400e; }
.resultado-tag--naranja{ background: #fff7ed; color: #c2410c; }
.resultado-tag--azul   { background: #eff6ff; color: #1d4ed8; }
.resultado-tag--gris   { background: #f1f5f9; color: #475569; }
.resultado-frase {
    border-left: 3px solid var(--dorado); padding: 0.75rem 1rem;
    background: #fffbeb; border-radius: 0 0.5rem 0.5rem 0; margin-top: 1rem;
}
.resultado-frase i { color: var(--dorado); margin-right: 0.4rem; }
.resultado-frase blockquote {
    font-style: italic; color: #1e293b; font-size: 0.9rem;
    margin: 0.25rem 0; line-height: 1.5;
}
.resultado-frase small { font-size: 0.75rem; color: #94a3b8; }

/* ── Respuestas abiertas ─────────────────────────────── */
.resultado-abiertas {
    background: white; border-radius: 1rem; padding: 1.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 1.5rem;
}
.resultado-abiertas__nota {
    font-size: 0.72rem; font-weight: 400; color: #94a3b8;
    margin-left: 0.5rem;
}
.resultado-abierta-item {
    padding: 1rem 0; border-bottom: 1px solid #f1f5f9;
}
.resultado-abierta-item:last-child { border-bottom: none; }
.resultado-abierta-item strong { font-size: 0.82rem; color: var(--verde-dark); display: block; margin-bottom: 0.4rem; }
.resultado-abierta-item p { font-size: 0.85rem; color: #4b5563; line-height: 1.7; margin: 0; }

/* ── CTA final ───────────────────────────────────────── */
.resultado-cta {
    display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.5rem;
}
</style>
