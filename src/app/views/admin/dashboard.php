<?php $titulo = 'Panel de Administración'; ?>

<div class="admin-layout">

    <!-- Sidebar Admin -->
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <!-- Main -->
    <main class="admin-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="admin-header">
            <div>
                <h1>Panel de Administración</h1>
                <p>Programa Misioneros Integrales — Ciclo 1 · Julio 2026</p>
            </div>
            <div class="admin-header__fecha">
                <i class="fas fa-calendar-alt"></i>
                <?= date('d/m/Y H:i') ?>
            </div>
        </div>

        <!-- KPIs ────────────────────────────────────────────── -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-card--total">
                <div class="kpi-card__icono"><i class="fas fa-users"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $kpis['total'] ?></span>
                    <span class="kpi-card__label">Total registrados</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--enviada">
                <div class="kpi-card__icono"><i class="fas fa-paper-plane"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $kpis['enviada'] ?></span>
                    <span class="kpi-card__label">Postulaciones enviadas</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--revision">
                <div class="kpi-card__icono"><i class="fas fa-search"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $kpis['en_revision'] ?></span>
                    <span class="kpi-card__label">En revisión</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--aprobada">
                <div class="kpi-card__icono"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $kpis['aprobada'] ?></span>
                    <span class="kpi-card__label">Aprobados</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--rechazada">
                <div class="kpi-card__icono"><i class="fas fa-times-circle"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $kpis['rechazada'] ?></span>
                    <span class="kpi-card__label">Rechazados</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--meta">
                <div class="kpi-card__icono"><i class="fas fa-bullseye"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">40</span>
                    <span class="kpi-card__label">Meta del ciclo</span>
                </div>
                <div class="kpi-card__barra">
                    <div class="kpi-card__fill" style="width: <?= min(($kpis['aprobada'] / 40) * 100, 100) ?>%"></div>
                </div>
            </div>
        </div>

        <div class="admin-cols">

            <!-- Últimos registros ──────────────────────────── -->
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-clock"></i> Últimas Postulaciones</h2>
                    <a href="/admin/candidatos" class="btn btn--outline btn--sm">Ver todas</a>
                </div>
                <div class="tabla-wrap">
                    <table class="tabla">
                        <thead>
                            <tr>
                                <th>Candidato</th>
                                <th>Iglesia</th>
                                <th>Estado</th>
                                <th>Estatus</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($recientes)): ?>
                            <tr><td colspan="5" class="tabla__vacio">No hay postulaciones aún</td></tr>
                        <?php else: ?>
                            <?php foreach ($recientes as $a): ?>
                            <tr>
                                <td>
                                    <div class="tabla__candidato">
                                        <div class="tabla__avatar">
                                            <?= mb_strtoupper(mb_substr($a['nombres'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($a['nombres'] . ' ' . $a['apellidos']) ?></strong>
                                            <small><?= htmlspecialchars($a['email'] ?? '') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($a['iglesia'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($a['estado_origen'] ?? '—') ?></td>
                                <td><span class="badge badge--<?= $a['estatus'] ?>"><?= ucfirst(str_replace('_', ' ', $a['estatus'])) ?></span></td>
                                <td>
                                    <a href="/admin/candidatos?ver=<?= $a['id'] ?>" class="btn btn--outline btn--xs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Distribución por estado ────────────────────── -->
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-map-marked-alt"></i> Por Estado</h2>
                </div>
                <?php if (empty($por_estado)): ?>
                    <p class="tabla__vacio">Sin datos aún</p>
                <?php else: ?>
                    <?php
                    $max = max(array_column($por_estado, 'total'));
                    foreach ($por_estado as $row):
                        $pct = $max > 0 ? round(($row['total'] / $max) * 100) : 0;
                    ?>
                    <div class="estado-row">
                        <div class="estado-row__nombre"><?= htmlspecialchars($row['estado_origen']) ?></div>
                        <div class="estado-row__barra">
                            <div class="estado-row__fill" style="width: <?= $pct ?>%"></div>
                        </div>
                        <div class="estado-row__num"><?= $row['total'] ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

    </main>
</div>
