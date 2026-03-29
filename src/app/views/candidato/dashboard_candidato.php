<?php $titulo = 'Mi Postulación'; ?>

<div class="dashboard-layout">

    <!-- Sidebar del candidato -->
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
            <a href="/candidato/pagos" class="dash-nav__item">
                <i class="fas fa-dollar-sign"></i> Mis Pagos
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
