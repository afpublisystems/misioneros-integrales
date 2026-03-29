<?php $titulo = 'Finanzas'; ?>

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
                <h1>Finanzas</h1>
                <p>Control de ingresos y gastos — Cohorte 2026</p>
            </div>
            <div style="display:flex;gap:.75rem;align-items:center;">
                <a href="/admin/finanzas/exportar?tipo=pagos"  class="btn btn--sm btn--outline">
                    <i class="fas fa-file-csv"></i> Exportar pagos
                </a>
                <a href="/admin/finanzas/exportar?tipo=gastos" class="btn btn--sm btn--outline">
                    <i class="fas fa-file-csv"></i> Exportar gastos
                </a>
                <button class="btn btn--sm btn--primario" data-modal="modal-gasto">
                    <i class="fas fa-plus"></i> Registrar gasto
                </button>
            </div>
        </div>

        <!-- KPIs Financieros ──────────────────────────────── -->
        <?php
            $esperado = $kpis['total_esperado'];
            $cobrado  = $kpis['total_cobrado'];
            $gastos_t = $kpis['total_gastos'];
            $saldo    = $kpis['saldo_neto'];
            $pct      = $esperado > 0 ? min(round(($cobrado / $esperado) * 100), 100) : 0;
        ?>
        <div class="kpi-grid">
            <div class="kpi-card kpi-card--total">
                <div class="kpi-card__icono"><i class="fas fa-bullseye"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($esperado, 2) ?></span>
                    <span class="kpi-card__label">Total esperado</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--aprobada">
                <div class="kpi-card__icono"><i class="fas fa-check-circle"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($cobrado, 2) ?></span>
                    <span class="kpi-card__label">Total cobrado</span>
                </div>
                <div class="kpi-card__barra">
                    <div class="kpi-card__fill" style="width:<?= $pct ?>%"></div>
                </div>
            </div>
            <div class="kpi-card kpi-card--rechazada">
                <div class="kpi-card__icono"><i class="fas fa-shopping-cart"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($gastos_t, 2) ?></span>
                    <span class="kpi-card__label">Total gastos</span>
                </div>
            </div>
            <div class="kpi-card <?= $saldo >= 0 ? 'kpi-card--aprobada' : 'kpi-card--rechazada' ?>">
                <div class="kpi-card__icono"><i class="fas fa-balance-scale"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $saldo >= 0 ? '+' : '' ?>$<?= number_format($saldo, 2) ?></span>
                    <span class="kpi-card__label">Saldo neto</span>
                </div>
            </div>
        </div>

        <div class="admin-cols">

            <!-- Abonos pendientes ──────────────────────────── -->
            <section class="admin-card" style="flex:2">
                <div class="admin-card__head">
                    <h2><i class="fas fa-clock"></i> Abonos pendientes de confirmación</h2>
                    <span class="badge badge--warning"><?= count($abonos_pendientes) ?></span>
                </div>

                <?php if (empty($abonos_pendientes)): ?>
                <p class="admin-empty"><i class="fas fa-check-circle"></i> No hay abonos pendientes.</p>
                <?php else: ?>
                <div class="tabla-responsive">
                <table class="admin-tabla">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Cuota</th>
                            <th>Declarado</th>
                            <th>Esperado / Acumulado</th>
                            <th>Método</th>
                            <th>Fecha</th>
                            <th>Comp.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($abonos_pendientes as $ab): ?>
                        <?php $diferencia = (float)$ab['monto_declarado_usd'] - ((float)$ab['monto_esperado_usd'] - (float)$ab['monto_acumulado_usd']); ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($ab['nombre_estudiante']) ?></strong><br>
                                <small class="texto-muted"><?= htmlspecialchars($ab['cedula']) ?></small>
                            </td>
                            <td><span class="badge badge--info">Cuota #<?= $ab['cuota_numero'] ?></span></td>
                            <td>
                                <strong>$<?= number_format($ab['monto_declarado_usd'], 2) ?></strong>
                                <?php if (!empty($ab['monto_declarado_ves'])): ?>
                                <br><small class="texto-muted">Bs <?= number_format($ab['monto_declarado_ves'], 2) ?></small>
                                <?php endif; ?>
                                <?php if (abs($diferencia) > 0.01): ?>
                                <br><small class="<?= $diferencia >= 0 ? 'texto-verde' : 'texto-rojo' ?>">
                                    <?= $diferencia >= 0 ? '+' : '' ?>$<?= number_format($diferencia, 2) ?>
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                $<?= number_format($ab['monto_esperado_usd'], 2) ?><br>
                                <small class="texto-muted">Acum: $<?= number_format($ab['monto_acumulado_usd'], 2) ?></small>
                            </td>
                            <td><?= htmlspecialchars(str_replace('_', ' ', $ab['metodo_pago'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($ab['fecha_pago_declarado'])) ?></td>
                            <td>
                                <?php if ($ab['comprobante_ruta']): ?>
                                <a href="/<?= htmlspecialchars($ab['comprobante_ruta']) ?>" target="_blank" class="btn btn--xs btn--outline">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <?php else: ?>
                                <span class="texto-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap">
                                <button class="btn btn--xs btn--success" onclick="abrirConfirmar(<?= $ab['id'] ?>, 'confirmar')">
                                    <i class="fas fa-check"></i> Confirmar
                                </button>
                                <button class="btn btn--xs btn--danger" onclick="abrirConfirmar(<?= $ab['id'] ?>, 'rechazar')">
                                    <i class="fas fa-times"></i> Rechazar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </section>

            <!-- Gastos recientes ───────────────────────────── -->
            <section class="admin-card" style="flex:1">
                <div class="admin-card__head">
                    <h2><i class="fas fa-receipt"></i> Gastos recientes</h2>
                </div>
                <?php if (empty($gastos_recientes)): ?>
                <p class="admin-empty"><i class="fas fa-inbox"></i> Sin gastos registrados.</p>
                <?php else: ?>
                <ul class="lista-gastos">
                <?php foreach ($gastos_recientes as $g): ?>
                    <li class="lista-gastos__item">
                        <div class="lista-gastos__info">
                            <span class="lista-gastos__concepto"><?= htmlspecialchars($g['concepto']) ?></span>
                            <span class="lista-gastos__cat badge badge--secondary"><?= htmlspecialchars($g['categoria']) ?></span>
                        </div>
                        <div class="lista-gastos__monto">
                            <?php if ($g['monto_usd']): ?>
                            <strong>$<?= number_format($g['monto_usd'], 2) ?></strong>
                            <?php endif; ?>
                            <?php if ($g['monto_ves']): ?>
                            <small class="texto-muted">Bs <?= number_format($g['monto_ves'], 2) ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="lista-gastos__fecha texto-muted"><?= date('d/m/Y', strtotime($g['fecha_gasto'])) ?></div>
                    </li>
                <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>

        </div>

        <!-- Resumen por estudiante ─────────────────────────── -->
        <?php if (!empty($resumen_estudiantes)): ?>
        <section class="admin-card" style="margin-top:1.5rem">
            <div class="admin-card__head">
                <h2><i class="fas fa-users"></i> Estado de pagos por estudiante</h2>
            </div>
            <div class="tabla-responsive">
            <table class="admin-tabla">
                <thead>
                    <tr>
                        <th>Estudiante</th>
                        <th>Cédula</th>
                        <th>Cuotas completadas</th>
                        <th>Total pagado</th>
                        <th>Saldo pendiente</th>
                        <th>Progreso</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resumen_estudiantes as $r): ?>
                <?php $pct_est = $r['total_cuotas'] > 0 ? round(($r['cuotas_completadas'] / $r['total_cuotas']) * 100) : 0; ?>
                <tr>
                    <td><?= htmlspecialchars($r['nombre']) ?></td>
                    <td><?= htmlspecialchars($r['cedula']) ?></td>
                    <td><?= $r['cuotas_completadas'] ?> / <?= $r['total_cuotas'] ?></td>
                    <td>$<?= number_format($r['total_pagado_usd'], 2) ?></td>
                    <td>
                        <?php if ($r['saldo_pendiente_usd'] > 0): ?>
                        <span class="texto-rojo">$<?= number_format($r['saldo_pendiente_usd'], 2) ?></span>
                        <?php else: ?>
                        <span class="texto-verde"><i class="fas fa-check"></i> Al día</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar-fill" style="width:<?= $pct_est ?>%"></div>
                        </div>
                        <small><?= $pct_est ?>%</small>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
        <?php endif; ?>

    </main>
</div>

<!-- Modal: Registrar Gasto ─────────────────────────────────── -->
<div class="modal" id="modal-gasto" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-gasto')"></div>
    <div class="modal__box" style="max-width:540px">
        <div class="modal__head">
            <h3><i class="fas fa-receipt"></i> Registrar Gasto</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-gasto')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/gasto" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal__body">
                <div class="form-group">
                    <label>Concepto <span class="req">*</span></label>
                    <input type="text" name="concepto" class="form-control" required maxlength="200">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Categoría <span class="req">*</span></label>
                        <select name="categoria" class="form-control" required>
                            <option value="logistica">Logística</option>
                            <option value="comunicacion">Comunicación</option>
                            <option value="materiales">Materiales</option>
                            <option value="operativo">Operativo</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Método de pago <span class="req">*</span></label>
                        <select name="metodo_pago" class="form-control" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="zelle">Zelle / PayPal</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Monto USD</label>
                        <input type="number" name="monto_usd" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Monto VES (Bs)</label>
                        <input type="number" name="monto_ves" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha <span class="req">*</span></label>
                        <input type="date" name="fecha_gasto" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Referencia</label>
                        <input type="text" name="referencia" class="form-control" maxlength="100">
                    </div>
                </div>
                <div class="form-group">
                    <label>Comprobante (JPG/PNG/PDF, máx 5 MB)</label>
                    <input type="file" name="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <textarea name="notas" class="form-control" rows="2" maxlength="500"></textarea>
                </div>
            </div>
            <div class="modal__foot">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-gasto')">Cancelar</button>
                <button type="submit" class="btn btn--primario"><i class="fas fa-save"></i> Guardar gasto</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Confirmar / Rechazar Abono ──────────────────────── -->
<div class="modal" id="modal-confirmar" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-confirmar')"></div>
    <div class="modal__box" style="max-width:420px">
        <div class="modal__head">
            <h3 id="modal-confirmar-titulo">Confirmar pago</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-confirmar')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/confirmar">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="conf-id">
            <input type="hidden" name="accion" id="conf-accion">
            <div class="modal__body">
                <div class="form-group">
                    <label>Notas para el estudiante</label>
                    <textarea name="notas_admin" id="conf-notas" class="form-control" rows="3"
                              placeholder="Opcional al confirmar, obligatorio al rechazar"></textarea>
                </div>
            </div>
            <div class="modal__foot">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-confirmar')">Cancelar</button>
                <button type="submit" class="btn btn--primario" id="conf-btn">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirConfirmar(id, accion) {
    document.getElementById('conf-id').value     = id;
    document.getElementById('conf-accion').value = accion;
    var esRechazo = accion === 'rechazar';
    document.getElementById('modal-confirmar-titulo').textContent = esRechazo ? 'Rechazar abono' : 'Confirmar pago';
    document.getElementById('conf-btn').textContent               = esRechazo ? 'Rechazar' : 'Confirmar pago';
    document.getElementById('conf-btn').className = 'btn ' + (esRechazo ? 'btn--danger' : 'btn--success');
    document.getElementById('conf-notas').required = esRechazo;
    document.getElementById('modal-confirmar').style.display = 'flex';
}
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}
document.querySelectorAll('[data-modal]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById(btn.dataset.modal).style.display = 'flex';
    });
});
</script>

<style>
.texto-verde { color: #22c55e; }
.texto-rojo  { color: #ef4444; }
.texto-muted { color: #9ca3af; font-size:.85em; }
.lista-gastos { list-style:none; margin:0; padding:0; }
.lista-gastos__item {
    display:flex; align-items:center; gap:.75rem;
    padding:.65rem 1rem; border-bottom:1px solid #1e3a2a;
}
.lista-gastos__item:last-child { border-bottom:none; }
.lista-gastos__info  { flex:1; }
.lista-gastos__concepto { display:block; font-size:.9rem; }
.lista-gastos__monto { text-align:right; min-width:80px; }
.lista-gastos__fecha { min-width:70px; font-size:.82rem; text-align:right; }
.progress-bar-wrap {
    background:#1e3a2a; border-radius:4px; height:6px;
    width:120px; overflow:hidden; display:inline-block;
}
.progress-bar-fill {
    background:#22c55e; height:100%; border-radius:4px;
    transition: width .3s;
}
.badge--info      { background:#0ea5e9; color:#fff; }
.badge--warning   { background:#f59e0b; color:#fff; }
.badge--secondary { background:#374151; color:#d1d5db; }
.btn--success { background:#22c55e; color:#fff; border-color:#22c55e; }
.btn--danger  { background:#ef4444; color:#fff; border-color:#ef4444; }
.btn--xs { padding:.2rem .5rem; font-size:.78rem; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.admin-empty {
    text-align:center; padding:2rem; color:#6b7280;
}
.modal {
    position:fixed; inset:0; z-index:1000;
    display:flex; align-items:center; justify-content:center;
}
.modal__overlay { position:absolute; inset:0; background:rgba(0,0,0,.6); }
.modal__box {
    position:relative; z-index:1;
    background:#0f2419; border:1px solid #1e3a2a;
    border-radius:12px; width:90%; max-height:90vh; overflow-y:auto;
}
.modal__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:1.25rem 1.5rem; border-bottom:1px solid #1e3a2a;
}
.modal__head h3 { margin:0; font-size:1.1rem; color:#f0f6f1; }
.modal__cerrar { background:none; border:none; color:#9ca3af; font-size:1.4rem; cursor:pointer; }
.modal__body { padding:1.25rem 1.5rem; }
.modal__foot {
    padding:1rem 1.5rem; border-top:1px solid #1e3a2a;
    display:flex; justify-content:flex-end; gap:.75rem;
}
</style>
