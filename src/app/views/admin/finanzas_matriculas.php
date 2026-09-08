<?php $titulo = 'Matrículas y becas'; ?>

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
                <h1>Matrículas y becas</h1>
                <p>Lo que aporta cada participante según su beca</p>
            </div>
            <a href="/admin/finanzas/exportar?tipo=matriculas" class="btn btn--sm btn--outline">
                <i class="fas fa-file-csv"></i> Exportar
            </a>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <div class="fin-grid">
            <div class="fin-kpi">
                <span class="fin-kpi__label">Participantes</span>
                <span class="fin-kpi__num"><?= $totales['participantes'] ?></span>
                <span class="fin-kpi__pie">Con postulación aprobada</span>
            </div>
            <div class="fin-kpi">
                <span class="fin-kpi__label">Costo del ciclo</span>
                <span class="fin-kpi__num">$<?= number_format($totales['costo_total'], 2) ?></span>
                <span class="fin-kpi__pie">Sin descontar becas</span>
            </div>
            <div class="fin-kpi">
                <span class="fin-kpi__label">Cubierto por becas</span>
                <span class="fin-kpi__num" style="color:#0ea5e9">$<?= number_format($totales['becado'], 2) ?></span>
            </div>
            <div class="fin-kpi">
                <span class="fin-kpi__label">Deben pagar</span>
                <span class="fin-kpi__num">$<?= number_format($totales['esperado'], 2) ?></span>
            </div>
            <div class="fin-kpi fin-kpi--ok">
                <span class="fin-kpi__label">Cobrado</span>
                <span class="fin-kpi__num">$<?= number_format($totales['cobrado'], 2) ?></span>
                <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:<?= $totales['porcentaje'] ?>%"></div></div>
                <span class="fin-kpi__pie">Faltan $<?= number_format($totales['por_cobrar'], 2) ?></span>
            </div>
        </div>

        <section class="admin-card">
            <div class="admin-card__head">
                <h2><i class="fas fa-users"></i> Estado por participante</h2>
            </div>

            <?php if (empty($resumen)): ?>
            <p class="admin-empty">
                <i class="fas fa-user-slash"></i>
                Todavía no hay participantes aprobados. Las cuotas se generan al aprobar la postulación.
            </p>
            <?php else: ?>
            <div class="tabla-responsive">
            <table class="admin-tabla">
                <thead>
                    <tr>
                        <th>Participante</th>
                        <th>Beca</th>
                        <th>Debe pagar</th>
                        <th>Pagado</th>
                        <th>Saldo</th>
                        <th>Cuotas</th>
                        <th>Próximo vence</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resumen as $r): ?>
                <?php
                    $a_pagar = (float) $r['total_a_pagar'];
                    $pagado  = (float) $r['total_pagado'];
                    $saldo   = round(max($a_pagar - $pagado, 0), 2);
                    $pct     = $a_pagar > 0 ? min(round($pagado / $a_pagar * 100), 100) : 100;
                    $beca    = (float) $r['beca_pct'];
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($r['nombre']) ?></strong><br>
                        <small class="texto-muted"><?= htmlspecialchars($r['cedula']) ?></small>
                        <?php if ((int)$r['abonos_pendientes'] > 0): ?>
                        <br><span class="badge badge--warning btn--xs"><?= $r['abonos_pendientes'] ?> por confirmar</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $beca >= 100 ? 'badge--success' : 'badge--info' ?>">
                            <?= rtrim(rtrim(number_format($beca, 2), '0'), '.') ?>%
                        </span>
                        <br><small class="texto-muted">de $<?= number_format($r['costo_base_usd'], 2) ?></small>
                    </td>
                    <td><strong>$<?= number_format($a_pagar, 2) ?></strong></td>
                    <td class="texto-verde">$<?= number_format($pagado, 2) ?></td>
                    <td>
                        <?php if ($beca >= 100): ?>
                        <span class="badge badge--success">Exonerado</span>
                        <?php elseif ($saldo > 0): ?>
                        <span class="texto-rojo">$<?= number_format($saldo, 2) ?></span>
                        <?php else: ?>
                        <span class="texto-verde"><i class="fas fa-check"></i> Al día</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $r['cuotas_completadas'] ?> / <?= $r['total_cuotas'] ?>
                        <div class="progress-bar-wrap" style="width:90px"><div class="progress-bar-fill" style="width:<?= $pct ?>%"></div></div>
                    </td>
                    <td class="texto-muted" style="white-space:nowrap">
                        <?= $r['proximo_vencimiento'] ? date('d/m/Y', strtotime($r['proximo_vencimiento'])) : '—' ?>
                    </td>
                    <td style="white-space:nowrap">
                        <button class="btn btn--xs btn--outline" onclick="verCuotas(<?= $r['aspirante_id'] ?>)">
                            <i class="fas fa-list"></i>
                        </button>
                        <button class="btn btn--xs btn--outline"
                                onclick="editarBeca(<?= $r['aspirante_id'] ?>, <?= $beca ?>, <?= (float)$r['costo_base_usd'] ?>, '<?= htmlspecialchars(addslashes($r['nombre']), ENT_QUOTES) ?>')">
                            <i class="fas fa-percent"></i>
                        </button>
                    </td>
                </tr>
                <tr id="cuotas-<?= $r['aspirante_id'] ?>" style="display:none">
                    <td colspan="8" style="background:#0a1a12">
                        <?php if (empty($cuotas[$r['aspirante_id']])): ?>
                        <p class="texto-muted" style="padding:.75rem">Sin cuotas generadas.</p>
                        <?php else: ?>
                        <table class="admin-tabla" style="margin:.5rem 0">
                            <thead>
                                <tr><th>Cuota</th><th>Vence</th><th>Esperado</th><th>Abonado</th><th>Estatus</th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cuotas[$r['aspirante_id']] as $c): ?>
                            <tr>
                                <td>#<?= $c['cuota_numero'] ?></td>
                                <td><?= $c['fecha_vencimiento'] ? date('d/m/Y', strtotime($c['fecha_vencimiento'])) : '—' ?></td>
                                <td>$<?= number_format($c['monto_esperado_usd'], 2) ?></td>
                                <td>$<?= number_format($c['monto_acumulado_usd'], 2) ?></td>
                                <td>
                                    <?php
                                        $clase = match ($c['estatus']) {
                                            'completada' => 'badge--success',
                                            'parcial'    => 'badge--warning',
                                            'exonerada'  => 'badge--info',
                                            default      => 'badge--secondary',
                                        };
                                    ?>
                                    <span class="badge <?= $clase ?>"><?= ucfirst($c['estatus']) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </section>

    </main>
</div>

<!-- Modal: Editar beca ─────────────────────────────────────── -->
<div class="modal" id="modal-beca" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-beca')"></div>
    <div class="modal__box" style="max-width:460px">
        <div class="modal__head">
            <h3><i class="fas fa-percent"></i> Beca de <span id="beca-nombre"></span></h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-beca')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/beca">
            <?= csrf_field() ?>
            <input type="hidden" name="aspirante_id" id="beca-id">
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Costo base USD</label>
                        <input type="number" name="costo_base_usd" id="beca-costo" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Beca % <span class="req">*</span></label>
                        <input type="number" name="beca_pct" id="beca-pct" class="form-control" step="0.01" min="0" max="100" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Atajos</label>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                        <button type="button" class="btn btn--xs btn--outline" onclick="ponerBeca(50)">50% — beca CNBV</button>
                        <button type="button" class="btn btn--xs btn--outline" onclick="ponerBeca(100)">100% — exonerado</button>
                        <button type="button" class="btn btn--xs btn--outline" onclick="ponerBeca(0)">0% — paga completo</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nota</label>
                    <input type="text" name="beca_notas" class="form-control" maxlength="255"
                           placeholder="Quién aprobó la beca y por qué">
                </div>
                <p class="ayuda">Al guardar se recalculan sus cuotas y se vuelve a repartir lo que ya haya pagado.</p>
            </div>
            <div class="modal__foot">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-beca')">Cancelar</button>
                <button type="submit" class="btn btn--primario"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}
function verCuotas(id) {
    var fila = document.getElementById('cuotas-' + id);
    fila.style.display = fila.style.display === 'none' ? 'table-row' : 'none';
}
function editarBeca(id, beca, costo, nombre) {
    document.getElementById('beca-id').value      = id;
    document.getElementById('beca-pct').value     = beca;
    document.getElementById('beca-costo').value   = costo;
    document.getElementById('beca-nombre').textContent = nombre;
    document.getElementById('modal-beca').style.display = 'flex';
}
function ponerBeca(valor) {
    document.getElementById('beca-pct').value = valor;
}
</script>
