<?php $titulo = 'Mi Postulación'; ?>

<div class="dashboard-layout">

    <!-- Sidebar del candidato -->
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
            <a href="/candidato/dashboard"  class="dash-nav__item activo">
                <i class="fas fa-tachometer-alt"></i> Mi Dashboard
            </a>
            <a href="/candidato/perfil" class="dash-nav__item">
                <i class="fas fa-user-edit"></i> Mi Perfil
            </a>
            <a href="/candidato/documentos" class="dash-nav__item">
                <i class="fas fa-folder-open"></i> Documentos
            </a>
            <a href="/candidato/test" class="dash-nav__item">
                <i class="fas fa-clipboard-list"></i> Test Vocacional
            </a>
            <a href="/logout" class="dash-nav__item dash-nav__item--logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </nav>
    </aside>

    <!-- Contenido principal -->
    <main class="dash-main">

        <!-- Flash message -->
        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <!-- Encabezado -->
        <div class="dash-header">
            <div>
                <h1>Mi Postulación</h1>
                <p>Ciclo 1 — Julio 2026 · Programa de Formación Misioneros Integrales</p>
            </div>
            <?php if (empty($aspirante) || $aspirante['estatus'] === 'borrador'): ?>
            <a href="/candidato/perfil" class="btn btn--verde">
                <i class="fas fa-arrow-right"></i>
                <?= empty($aspirante) ? 'Completar perfil' : 'Continuar postulación' ?>
            </a>
            <?php endif; ?>
        </div>

        <!-- Barra de progreso general -->
        <div class="dash-progreso-card">
            <div class="dash-progreso-card__header">
                <span>Progreso de tu postulación</span>
                <strong><?= $progreso['pct'] ?>%</strong>
            </div>
            <div class="dash-progreso-bar">
                <div class="dash-progreso-bar__fill" style="width: <?= $progreso['pct'] ?>%"></div>
            </div>
            <p class="dash-progreso-card__texto">
                <?php if ($progreso['pct'] == 0): ?>
                    Completa tu perfil para iniciar el proceso de selección.
                <?php elseif ($progreso['pct'] < 100): ?>
                    Etapa <?= $progreso['etapa_actual'] ?> de 5 completada. ¡Sigue adelante!
                <?php else: ?>
                    ¡Felicitaciones! Has completado todas las etapas. Espera confirmación.
                <?php endif; ?>
            </p>
        </div>

        <!-- Etapas del proceso -->
        <h2 class="dash-section-titulo">Etapas del Proceso de Selección</h2>
        <div class="etapas-grid">
            <?php foreach ($progreso['etapas'] as $i => $etapa): ?>
            <div class="etapa-card etapa-card--<?= $etapa['estatus'] ?>">
                <div class="etapa-card__num"><?= $i + 1 ?></div>
                <div class="etapa-card__icono">
                    <i class="fas <?= $etapa['icono'] ?>"></i>
                </div>
                <h3><?= $etapa['nombre'] ?></h3>
                <div class="etapa-card__estatus">
                    <?php if ($etapa['estatus'] === 'aprobado'): ?>
                        <i class="fas fa-check-circle"></i> Completado
                    <?php elseif ($etapa['estatus'] === 'en_proceso'): ?>
                        <i class="fas fa-clock"></i> En proceso
                    <?php else: ?>
                        <i class="fas fa-lock"></i> Pendiente
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Info si no hay perfil aún -->
        <?php if (empty($aspirante)): ?>
        <div class="dash-inicio-card">
            <div class="dash-inicio-card__icono">
                <i class="fas fa-user-edit"></i>
            </div>
            <h3>Comienza completando tu perfil</h3>
            <p>Necesitamos tus datos personales, eclesiales y académicos para iniciar tu proceso de postulación al programa.</p>
            <a href="/candidato/perfil" class="btn btn--verde btn--lg">
                <i class="fas fa-arrow-right"></i> Ir a mi perfil
            </a>
        </div>
        <?php else: ?>
        <!-- Resumen de datos -->
        <div class="dash-resumen">
            <h2 class="dash-section-titulo">Resumen de tu Postulación</h2>
            <div class="dash-resumen-grid">
                <div class="resumen-item">
                    <span class="resumen-item__label"><i class="fas fa-user"></i> Nombre completo</span>
                    <span><?= htmlspecialchars($aspirante['nombres'] . ' ' . $aspirante['apellidos']) ?></span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-item__label"><i class="fas fa-id-card"></i> Cédula</span>
                    <span><?= htmlspecialchars($aspirante['cedula'] ?: '—') ?></span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-item__label"><i class="fas fa-church"></i> Iglesia</span>
                    <span><?= htmlspecialchars($aspirante['iglesia'] ?: '—') ?></span>
                </div>
                <div class="resumen-item">
                    <span class="resumen-item__label"><i class="fas fa-map-marker-alt"></i> Ciudad</span>
                    <span><?= htmlspecialchars($aspirante['ciudad_origen'] ?: '—') ?></span>
                </div>
            </div>
            <a href="/candidato/perfil" class="btn btn--outline" style="margin-top:1rem">
                <i class="fas fa-edit"></i> Editar perfil
            </a>
        </div>
        <?php endif; ?>

    </main>
</div>

<style>
.dashboard-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: calc(100vh - 73px);
}

/* Sidebar */
.dash-sidebar {
    background: var(--verde-dark);
    color: var(--blanco);
    padding: 2rem 0;
    display: flex; flex-direction: column; gap: 0;
}
.dash-sidebar__user {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0 1.5rem 1.75rem;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 1rem;
}
.dash-sidebar__avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--dorado); color: var(--verde-dark);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; font-weight: 900; flex-shrink: 0;
}
.dash-sidebar__info { display: flex; flex-direction: column; gap: 0.25rem; }
.dash-sidebar__info strong { font-size: 0.88rem; line-height: 1.3; }

.dash-nav { display: flex; flex-direction: column; }
.dash-nav__item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.85rem 1.5rem; font-size: 0.88rem; font-weight: 600;
    color: rgba(255,255,255,0.75); transition: all 0.2s;
    border-left: 3px solid transparent;
}
.dash-nav__item:hover, .dash-nav__item.activo {
    background: rgba(255,255,255,0.08);
    color: var(--blanco);
    border-left-color: var(--dorado);
}
.dash-nav__item--logout {
    margin-top: auto; color: rgba(255,255,255,0.4);
    border-top: 1px solid rgba(255,255,255,0.08);
    margin-top: 1rem; padding-top: 1rem;
}
.dash-nav__item--logout:hover { color: #f87171; background: rgba(220,38,38,0.1); border-left-color: #f87171; }

/* Main */
.dash-main { background: var(--gris-claro); padding: 2rem 2.5rem; overflow-y: auto; }

.dash-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;
}
.dash-header h1 { font-size: 1.7rem; font-weight: 900; color: var(--verde); }
.dash-header p   { color: var(--gris); font-size: 0.88rem; margin-top: 0.2rem; }

/* Progreso */
.dash-progreso-card {
    background: var(--blanco); border-radius: var(--radio-lg);
    padding: 1.5rem 2rem; margin-bottom: 2rem;
    box-shadow: var(--sombra);
}
.dash-progreso-card__header {
    display: flex; justify-content: space-between;
    font-size: 0.88rem; color: var(--gris-dark);
    margin-bottom: 0.75rem;
}
.dash-progreso-card__header strong { color: var(--verde); font-size: 1rem; }
.dash-progreso-bar {
    height: 10px; background: #e5e7eb; border-radius: 999px; overflow: hidden;
}
.dash-progreso-bar__fill {
    height: 100%; background: linear-gradient(90deg, var(--verde), var(--verde-mid));
    border-radius: 999px; transition: width 1s ease;
}
.dash-progreso-card__texto {
    font-size: 0.83rem; color: var(--gris); margin-top: 0.6rem;
}

/* Etapas */
.dash-section-titulo {
    font-size: 1rem; font-weight: 700; color: var(--gris-dark);
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 1rem;
}
.etapas-grid {
    display: grid; grid-template-columns: repeat(5, 1fr);
    gap: 1rem; margin-bottom: 2rem;
}
.etapa-card {
    background: var(--blanco); border-radius: var(--radio-lg);
    padding: 1.25rem 1rem; text-align: center;
    border: 2px solid #e5e7eb; position: relative;
    box-shadow: var(--sombra); transition: var(--transicion);
}
.etapa-card--aprobado { border-color: var(--verde); }
.etapa-card--en_proceso { border-color: var(--dorado); }

.etapa-card__num {
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    width: 24px; height: 24px; border-radius: 50%;
    background: #e5e7eb; font-size: 0.72rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
}
.etapa-card--aprobado   .etapa-card__num { background: var(--verde); color: white; }
.etapa-card--en_proceso .etapa-card__num { background: var(--dorado); color: var(--verde-dark); }

.etapa-card__icono { font-size: 1.75rem; margin: 0.5rem 0 0.5rem; color: #d1d5db; }
.etapa-card--aprobado   .etapa-card__icono { color: var(--verde); }
.etapa-card--en_proceso .etapa-card__icono { color: var(--dorado); }

.etapa-card h3 { font-size: 0.78rem; font-weight: 700; color: var(--gris-dark); margin-bottom: 0.4rem; }
.etapa-card__estatus {
    font-size: 0.72rem; color: var(--gris); display: flex;
    align-items: center; justify-content: center; gap: 0.3rem;
}
.etapa-card--aprobado   .etapa-card__estatus { color: var(--verde); }
.etapa-card--en_proceso .etapa-card__estatus { color: var(--dorado-dark); }

/* Inicio (sin perfil) */
.dash-inicio-card {
    background: var(--blanco); border-radius: var(--radio-lg);
    padding: 3rem; text-align: center; box-shadow: var(--sombra);
    max-width: 500px; margin: 0 auto;
}
.dash-inicio-card__icono {
    font-size: 3rem; color: var(--verde-light); margin-bottom: 1rem;
}
.dash-inicio-card h3 { font-size: 1.2rem; font-weight: 800; color: var(--verde); margin-bottom: 0.5rem; }
.dash-inicio-card p  { color: var(--gris); font-size: 0.9rem; margin-bottom: 1.5rem; line-height: 1.6; }

/* Resumen */
.dash-resumen { background: var(--blanco); border-radius: var(--radio-lg); padding: 1.75rem; box-shadow: var(--sombra); }
.dash-resumen-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.resumen-item { display: flex; flex-direction: column; gap: 0.25rem; }
.resumen-item__label { font-size: 0.78rem; color: var(--gris); font-weight: 600; display: flex; align-items: center; gap: 0.3rem; }
.resumen-item__label i { color: var(--verde); }
.resumen-item span:last-child { font-size: 0.92rem; font-weight: 600; color: var(--gris-dark); }

@media (max-width: 1024px) {
    .etapas-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 768px) {
    .dashboard-layout { grid-template-columns: 1fr; }
    .dash-sidebar { display: none; }
    .dash-main { padding: 1.5rem 1rem; }
    .etapas-grid { grid-template-columns: repeat(2, 1fr); }
    .dash-resumen-grid { grid-template-columns: 1fr; }
}
</style>
