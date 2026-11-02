<?php
$titulo = 'Presupuesto';

$fecha_corta = fn(string $f) => date('d/m/y', strtotime($f));
$clase_dif   = fn(float $d) => $d < -0.005 ? 'texto-rojo' : 'texto-verde';
?>

<div class="admin-layout">

    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="admin-header">
            <div>
                <h1>Presupuesto contra lo real</h1>
                <p>Ciclo 1 · <?= htmlspecialchars($etiqueta) ?></p>
            </div>
            <div class="fin-acciones no-imprimir">
                <a href="/admin/finanzas/exportar?tipo=presupuesto&fase=<?= urlencode($ver) ?>" class="btn btn--sm btn--outline">
                    <i class="fas fa-file-csv"></i> Exportar
                </a>
                <button type="button" class="btn btn--sm btn--outline" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="no-imprimir">
            <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>
        </div>

        <?php if (!$fases): ?>
        <section class="admin-panel">
            <p class="fin-vacio"><i class="fas fa-database"></i> Falta cargar el presupuesto: aplica la migración 012.</p>
        </section>
        <?php else: ?>

        <form method="GET" action="/admin/finanzas/presupuesto" class="pre-selector no-imprimir">
            <label for="pre-fase">Ver</label>
            <select name="fase" id="pre-fase" onchange="this.form.submit()">
                <?php foreach ($fases as $f): ?>
                <option value="<?= $f['id'] ?>" <?= $ver === (string) $f['id'] ? 'selected' : '' ?>>
                    Fase <?= $f['numero'] ?> · <?= htmlspecialchars($f['nombre']) ?>
                </option>
                <?php endforeach; ?>
                <option value="hoy"   <?= $ver === 'hoy'   ? 'selected' : '' ?>>Lo que va del ciclo</option>
                <option value="ciclo" <?= $ver === 'ciclo' ? 'selected' : '' ?>>Ciclo completo</option>
            </select>
        </form>

        <div class="fin-kpis">
            <div class="kpi-card kpi-card--total">
                <div class="kpi-card__icono"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['presupuesto'], 2) ?></span>
                    <span class="kpi-card__label">Presupuestado</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--aprobada">
                <div class="kpi-card__icono"><i class="fas fa-receipt"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['previsto'], 2) ?></span>
                    <span class="kpi-card__label">Gastado en lo previsto</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--rechazada">
                <div class="kpi-card__icono"><i class="fas fa-triangle-exclamation"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['adicional'], 2) ?></span>
                    <span class="kpi-card__label">Gastos adicionales</span>
                    <span class="kpi-card__pie">Lo que no estaba en el presupuesto</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--neutro">
                <div class="kpi-card__icono"><i class="fas fa-scale-balanced"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num <?= $clase_dif($totales['diferencia']) ?>">
                        <?= $totales['diferencia'] < 0 ? '−' : '' ?>$<?= number_format(abs($totales['diferencia']), 2) ?>
                    </span>
                    <span class="kpi-card__label"><?= $totales['diferencia'] < 0 ? 'Déficit' : 'Queda del presupuesto' ?></span>
                    <span class="kpi-card__pie">
                        <?php if ($totales['adicional'] > 0): ?>
                        Sin los adicionales: <?= ($totales['presupuesto'] - $totales['previsto']) < 0 ? '−' : '' ?>$<?= number_format(abs($totales['presupuesto'] - $totales['previsto']), 2) ?>
                        <?php else: ?>
                        Presupuestado menos gastado
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Por rubro ───────────────────────────────────────── -->
        <section class="admin-panel" style="margin-bottom:1.5rem">
            <div class="admin-panel__header">
                <h2><i class="fas fa-list"></i> Por rubro · <?= htmlspecialchars($etiqueta) ?></h2>
            </div>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Rubro</th>
                        <th class="num">Presupuestado</th>
                        <th class="num">Gastado previsto</th>
                        <th class="num">Adicional</th>
                        <th class="num">Total gastado</th>
                        <th class="num">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $f): ?>
                <?php $gastado = $f['previsto'] + $f['adicional']; $dif = $f['presupuesto'] - $gastado; ?>
                <tr>
                    <td><?= htmlspecialchars($f['nombre']) ?></td>
                    <td class="num">$<?= number_format($f['presupuesto'], 2) ?></td>
                    <td class="num">$<?= number_format($f['previsto'], 2) ?></td>
                    <td class="num <?= $f['adicional'] > 0 ? 'texto-rojo' : 'texto-muted' ?>">$<?= number_format($f['adicional'], 2) ?></td>
                    <td class="num">$<?= number_format($gastado, 2) ?></td>
                    <td class="num <?= $clase_dif($dif) ?>"><?= $dif < 0 ? '−' : '' ?>$<?= number_format(abs($dif), 2) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="pre-total">
                        <td>Total</td>
                        <td class="num">$<?= number_format($totales['presupuesto'], 2) ?></td>
                        <td class="num">$<?= number_format($totales['previsto'], 2) ?></td>
                        <td class="num">$<?= number_format($totales['adicional'], 2) ?></td>
                        <td class="num">$<?= number_format($totales['gastado'], 2) ?></td>
                        <td class="num <?= $clase_dif($totales['diferencia']) ?>"><?= $totales['diferencia'] < 0 ? '−' : '' ?>$<?= number_format(abs($totales['diferencia']), 2) ?></td>
                    </tr>
                </tfoot>
            </table>
            </div>
            <p class="fin-nota" style="padding:0 1.25rem 1.25rem">
                <?php if ($ver === 'hoy'): ?>
                La fase en curso entra con su presupuesto completo, aunque no haya terminado.
                <?php endif; ?>
                La contingencia es el margen del 6%: se presupuesta, pero no se gasta como rubro propio.
                Los montos presupuestados son los del documento entregado a la DIME (12 participantes).
            </p>
        </section>

        <!-- Gastos adicionales ──────────────────────────────── -->
        <section class="admin-panel" style="margin-bottom:1.5rem">
            <div class="admin-panel__header">
                <h2><i class="fas fa-triangle-exclamation texto-rojo"></i> Gastos adicionales · <?= htmlspecialchars($etiqueta) ?></h2>
                <span class="badge badge--neutro">$<?= number_format($totales['adicional'], 2) ?></span>
            </div>
            <?php if (!$adicionales): ?>
            <p class="fin-vacio"><i class="fas fa-inbox"></i> No hay gastos marcados como adicionales aquí.</p>
            <?php else: ?>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr><th>Fecha</th><th>Concepto</th><th>Rubro</th><th>Por qué</th><th class="num">Monto</th><th class="no-imprimir">Comp.</th></tr>
                </thead>
                <tbody>
                <?php foreach ($adicionales as $g): ?>
                <tr>
                    <td class="fecha"><?= date('d/m/Y', strtotime($g['fecha_gasto'])) ?></td>
                    <td>
                        <?= htmlspecialchars($g['concepto']) ?>
                        <?php if (!empty($g['beneficiario'])): ?>
                        <br><small class="texto-muted"><?= htmlspecialchars($g['beneficiario']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($categorias[$g['categoria']] ?? $g['categoria']) ?></td>
                    <td><?= htmlspecialchars($g['cobertura_motivo'] ?? '') ?></td>
                    <td class="num">
                        $<?= number_format($g['monto_usd'], 2) ?>
                        <?php if (!empty($g['monto_ves'])): ?>
                        <br><small class="texto-muted">Bs <?= number_format($g['monto_ves'], 2) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="no-imprimir">
                        <?php if (!empty($g['comprobante_ruta'])): ?>
                        <a href="/<?= htmlspecialchars($g['comprobante_ruta']) ?>" target="_blank" class="btn btn--xs btn--outline">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php else: ?>
                        <span class="texto-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </section>

        <!-- Por fase ────────────────────────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-route"></i> Fase por fase</h2>
            </div>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fase</th><th>Fechas</th>
                        <th class="num">Presupuestado</th>
                        <th class="num">Gastado previsto</th>
                        <th class="num">Adicional</th>
                        <th class="num">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($por_fase as $f): ?>
                <tr class="<?= $ver === (string) $f['id'] ? 'pre-actual' : '' ?>">
                    <td>
                        <a href="/admin/finanzas/presupuesto?fase=<?= $f['id'] ?>">
                            <?= $f['numero'] ?> · <?= htmlspecialchars($f['nombre']) ?>
                        </a>
                    </td>
                    <td class="fecha texto-muted">
                        <?= $fecha_corta($f['fecha_inicio']) ?> – <?= $fecha_corta($f['fecha_fin']) ?>
                        · <?= $f['semanas'] ?> sem.
                    </td>
                    <td class="num">$<?= number_format($f['presupuesto'], 2) ?></td>
                    <td class="num">$<?= number_format($f['previsto'], 2) ?></td>
                    <td class="num <?= $f['adicional'] > 0 ? 'texto-rojo' : 'texto-muted' ?>">$<?= number_format($f['adicional'], 2) ?></td>
                    <td class="num <?= $clase_dif($f['diferencia']) ?>"><?= $f['diferencia'] < 0 ? '−' : '' ?>$<?= number_format(abs($f['diferencia']), 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if ($fuera['gastado'] > 0): ?>
                <tr>
                    <td>Fuera de las fases</td>
                    <td class="texto-muted">Antes del inicio o en la pausa de diciembre</td>
                    <td class="num texto-muted">—</td>
                    <td class="num">$<?= number_format($fuera['previsto'], 2) ?></td>
                    <td class="num <?= $fuera['adicional'] > 0 ? 'texto-rojo' : 'texto-muted' ?>">$<?= number_format($fuera['adicional'], 2) ?></td>
                    <td class="num texto-rojo">−$<?= number_format($fuera['gastado'], 2) ?></td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </section>

        <?php endif; ?>

    </main>
</div>

<style>
.pre-selector {
    display: flex; align-items: center; gap: .6rem; margin-bottom: 1.5rem;
    font-size: .85rem; font-weight: 600; color: var(--gris-dark);
}
.pre-selector select { max-width: 320px; }
.tabla tr.pre-total td { font-weight: 700; border-top: 2px solid #e5e7eb; }
.tabla tr.pre-actual td { background: #f0fdf4; }

@media print {
    @page { margin: 1.2cm; }
    .admin-sidebar, .admin-barra-movil, .no-imprimir { display: none !important; }
    .admin-main { margin: 0 !important; padding: 0 !important; }
    .admin-panel { box-shadow: none; break-inside: avoid; }
    .fin-kpis { grid-template-columns: repeat(4, 1fr); }
}
</style>
