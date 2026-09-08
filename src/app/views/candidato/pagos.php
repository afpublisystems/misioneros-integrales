<?php $titulo = 'Mis Pagos'; ?>

<div class="dashboard-layout">

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
            <?php $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
            <a href="/candidato/dashboard"  class="dash-nav__item <?= $uri === '/candidato/dashboard' ? 'activo' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Mi Dashboard
            </a>
            <a href="/candidato/perfil" class="dash-nav__item <?= str_starts_with($uri, '/candidato/perfil') ? 'activo' : '' ?>">
                <i class="fas fa-user-edit"></i> Mi Perfil
            </a>
            <a href="/candidato/documentos" class="dash-nav__item <?= $uri === '/candidato/documentos' ? 'activo' : '' ?>">
                <i class="fas fa-folder-open"></i> Documentos
            </a>
            <a href="/candidato/test" class="dash-nav__item <?= $uri === '/candidato/test' ? 'activo' : '' ?>">
                <i class="fas fa-clipboard-list"></i> Test Vocacional
            </a>
            <a href="/candidato/pagos" class="dash-nav__item activo">
                <i class="fas fa-dollar-sign"></i> Mis Pagos
            </a>
            <a href="/logout" class="dash-nav__item dash-nav__item--logout">
                <i class="fas fa-sign-out-alt"></i> Cerrar sesión
            </a>
        </nav>
    </aside>

    <main class="dash-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="dash-page-header">
            <h1><i class="fas fa-dollar-sign"></i> Mis Pagos</h1>
            <p>
                Ciclo 1 · costo $<?= number_format($estado['costo_base'], 2) ?> USD
                <?php if ($estado['beca_pct'] > 0): ?>
                · beca <?= rtrim(rtrim(number_format($estado['beca_pct'], 2), '0'), '.') ?>%
                <?php endif; ?>
            </p>
        </div>

        <?php if (empty($cuotas)): ?>
        <div class="dash-card" style="text-align:center;padding:3rem">
            <i class="fas fa-lock" style="font-size:2.5rem;color:#6b7280;margin-bottom:1rem"></i>
            <p>Las cuotas se activarán una vez que tu postulación sea <strong>aprobada</strong>.</p>
        </div>

        <?php elseif ($estado['total_a_pagar'] <= 0): ?>
        <div class="dash-card" style="text-align:center;padding:3rem">
            <i class="fas fa-award" style="font-size:2.5rem;color:#22c55e;margin-bottom:1rem"></i>
            <h2 style="margin-bottom:.5rem">Tienes beca completa</h2>
            <p class="texto-muted">No tienes que pagar matrícula para este ciclo.</p>
            <?php if (!empty($estado['beca_notas'])): ?>
            <p class="texto-muted" style="margin-top:.75rem"><?= htmlspecialchars($estado['beca_notas']) ?></p>
            <?php endif; ?>
        </div>

        <?php else: ?>

        <!-- Resumen ────────────────────────────────────────── -->
        <?php $completadas = array_filter($cuotas, fn($c) => $c['estatus'] === 'completada'); ?>
        <div class="pagos-resumen">
            <div class="pagos-resumen__item">
                <span class="pagos-resumen__num"><?= count($completadas) ?>/<?= count($cuotas) ?></span>
                <span class="pagos-resumen__label">Cuotas completadas</span>
            </div>
            <div class="pagos-resumen__item">
                <span class="pagos-resumen__num">$<?= number_format($estado['total_pagado'], 2) ?></span>
                <span class="pagos-resumen__label">Total pagado</span>
            </div>
            <div class="pagos-resumen__item">
                <span class="pagos-resumen__num pagos-resumen__num--pendiente">
                    $<?= number_format($estado['saldo'], 2) ?>
                </span>
                <span class="pagos-resumen__label">Saldo pendiente</span>
            </div>
            <div class="pagos-resumen__barra">
                <div class="pagos-resumen__fill" style="width:<?= $estado['porcentaje'] ?>%"></div>
                <span><?= $estado['porcentaje'] ?>%</span>
            </div>
        </div>

        <div class="dash-card" style="margin-top:1.5rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap;justify-content:space-between">
            <div>
                <strong>¿Ya pagaste?</strong>
                <p class="texto-muted" style="margin:.25rem 0 0">
                    Reporta el pago y sube tu comprobante. La cuota mínima es
                    $<?= number_format($cuotas[0]['monto_esperado_usd'], 2) ?> al mes, pero puedes
                    abonar varias cuotas o el ciclo completo cuando quieras.
                </p>
            </div>
            <button class="btn btn--primario" onclick="abrirAbono(<?= number_format($estado['saldo'], 2, '.', '') ?>)">
                <i class="fas fa-upload"></i> Reportar pago
            </button>
        </div>

        <!-- Detalle de cuotas ──────────────────────────────── -->
        <div class="dash-card" style="margin-top:1.5rem">
            <h2 style="margin-bottom:1.25rem"><i class="fas fa-list"></i> Detalle de cuotas</h2>

            <?php foreach ($cuotas as $c):
                $restante = max(0, (float)$c['monto_esperado_usd'] - (float)$c['monto_acumulado_usd']);
            ?>
            <div class="cuota-card cuota-card--<?= $c['estatus'] ?>">
                <div class="cuota-card__num">
                    <?php if ($c['estatus'] === 'completada'): ?>
                    <i class="fas fa-check-circle" style="color:#22c55e;font-size:1.4rem"></i>
                    <?php elseif ($c['estatus'] === 'parcial'): ?>
                    <i class="fas fa-adjust" style="color:#f59e0b;font-size:1.4rem"></i>
                    <?php else: ?>
                    <span class="cuota-num-badge"><?= $c['cuota_numero'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="cuota-card__info">
                    <strong>Cuota #<?= $c['cuota_numero'] ?></strong>
                    <?php if ($c['fecha_vencimiento']): ?>
                    <small class="texto-muted">Vence: <?= date('d/m/Y', strtotime($c['fecha_vencimiento'])) ?></small>
                    <?php endif; ?>
                </div>

                <div class="cuota-card__montos">
                    <span>$<?= number_format($c['monto_esperado_usd'], 2) ?></span>
                    <?php if ($c['monto_acumulado_usd'] > 0 && $c['estatus'] !== 'completada'): ?>
                    <small class="texto-muted">Abonado: $<?= number_format($c['monto_acumulado_usd'], 2) ?></small>
                    <small class="texto-rojo">Restante: $<?= number_format($restante, 2) ?></small>
                    <?php endif; ?>
                </div>

                <div class="cuota-card__estatus">
                    <?php if ($c['estatus'] === 'completada'): ?>
                    <span class="badge badge--verde">Completada</span>
                    <?php elseif ($c['estatus'] === 'parcial'): ?>
                    <span class="badge badge--warning">Parcial</span>
                    <?php else: ?>
                    <span class="badge badge--gris">Pendiente</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagos reportados ───────────────────────────────── -->
        <?php if (!empty($abonos)): ?>
        <div class="dash-card" style="margin-top:1.5rem">
            <h2 style="margin-bottom:1.25rem"><i class="fas fa-receipt"></i> Pagos que has reportado</h2>

            <?php foreach ($abonos as $ab): ?>
            <div class="abono-fila">
                <div class="abono-fila__fecha">
                    <?= date('d/m/Y', strtotime($ab['fecha'])) ?>
                    <small class="texto-muted"><?= htmlspecialchars(str_replace('_', ' ', $ab['metodo_pago'])) ?></small>
                </div>
                <div class="abono-fila__monto">
                    <strong>$<?= number_format($ab['monto_usd'], 2) ?></strong>
                    <?php if (!empty($ab['monto_ves'])): ?>
                    <small class="texto-muted">Bs <?= number_format($ab['monto_ves'], 2) ?></small>
                    <?php endif; ?>
                </div>
                <div class="abono-fila__estatus">
                    <?php if ($ab['estatus'] === 'confirmado'): ?>
                    <span class="badge badge--verde">Confirmado</span>
                    <?php elseif ($ab['estatus'] === 'rechazado'): ?>
                    <span class="badge badge--rojo">Rechazado</span>
                    <?php else: ?>
                    <span class="badge badge--info">En revisión</span>
                    <?php endif; ?>
                    <?php if (!empty($ab['notas_admin'])): ?>
                    <small class="texto-muted"><?= htmlspecialchars($ab['notas_admin']) ?></small>
                    <?php endif; ?>
                </div>
                <div class="abono-fila__comp">
                    <?php if (!empty($ab['comprobante_ruta'])): ?>
                    <a href="/<?= htmlspecialchars($ab['comprobante_ruta']) ?>" target="_blank" class="btn btn--xs btn--outline">
                        <i class="fas fa-eye"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php endif; ?>

    </main>
</div>

<!-- Modal: Reportar pago ───────────────────────────────────── -->
<div class="modal" id="modal-abono" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-abono')"></div>
    <div class="modal__box" style="max-width:520px">
        <div class="modal__head">
            <h3><i class="fas fa-upload"></i> Reportar pago</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-abono')">&times;</button>
        </div>
        <form method="POST" action="/candidato/pagos" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Monto pagado (USD)</label>
                        <input type="number" name="monto_declarado_usd" id="abono-monto-usd"
                               class="form-control" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Fecha del pago <span class="req">*</span></label>
                        <input type="date" name="fecha_pago_declarado" class="form-control"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Monto en Bs</label>
                        <input type="number" name="monto_declarado_ves" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="form-group">
                        <label>Tasa de cambio (Bs/$)</label>
                        <input type="number" name="tasa_cambio" class="form-control" step="0.01" min="0" placeholder="Ej: 55.30">
                    </div>
                </div>
                <small class="texto-muted" style="display:block;margin-bottom:1rem">
                    Si pagaste en bolívares, llena el monto en Bs y la tasa: calculamos el equivalente en dólares.
                </small>
                <div class="form-row">
                    <div class="form-group">
                        <label>Método de pago <span class="req">*</span></label>
                        <select name="metodo_pago" class="form-control" required>
                            <option value="">— Seleccionar —</option>
                            <option value="transferencia">Transferencia bancaria</option>
                            <option value="zelle">Zelle / PayPal</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="efectivo">Efectivo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Banco origen</label>
                        <input type="text" name="banco_origen" class="form-control" maxlength="100" placeholder="Ej: Banesco">
                    </div>
                </div>
                <div class="form-group">
                    <label>Número de referencia</label>
                    <input type="text" name="referencia" class="form-control" maxlength="100"
                           placeholder="Número de comprobante o transacción">
                </div>
                <div class="form-group">
                    <label>Comprobante de pago (JPG/PNG/PDF, máx 5 MB)</label>
                    <input type="file" name="comprobante" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
            </div>
            <div class="modal__foot">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-abono')">Cancelar</button>
                <button type="submit" class="btn btn--primario"><i class="fas fa-paper-plane"></i> Enviar comprobante</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirAbono(sugerido) {
    document.getElementById('abono-monto-usd').value = sugerido;
    document.getElementById('modal-abono').style.display = 'flex';
}
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
<style>
.pagos-resumen {
    display:grid; grid-template-columns:repeat(3,1fr) 2fr;
    gap:1rem; background:#0f2419; border:1px solid #1e3a2a;
    border-radius:12px; padding:1.25rem 1.5rem;
    align-items:center; margin-bottom:1.5rem;
}
.pagos-resumen__item { text-align:center; }
.pagos-resumen__num  { display:block; font-size:1.5rem; font-weight:700; color:#f0f6f1; }
.pagos-resumen__num--pendiente { color:#f59e0b; }
.pagos-resumen__label { font-size:.8rem; color:#9ca3af; }
.pagos-resumen__barra {
    background:#1e3a2a; border-radius:6px; height:10px;
    position:relative; overflow:hidden;
}
.pagos-resumen__barra span {
    position:absolute; right:0; top:-18px; font-size:.8rem; color:#9ca3af;
}
.pagos-resumen__fill { background:#22c55e; height:100%; border-radius:6px; transition:width .4s; }

.cuota-card {
    display:grid;
    grid-template-columns:48px 1fr auto auto auto;
    gap:1rem; align-items:center;
    padding:.9rem 1rem;
    border-bottom:1px solid #1e3a2a;
}
.cuota-card:last-of-type { border-bottom:none; }
.cuota-card--completada { opacity:.8; }
.cuota-card__num { text-align:center; }
.cuota-num-badge {
    display:inline-flex; align-items:center; justify-content:center;
    width:32px; height:32px; border-radius:50%;
    background:#1e3a2a; color:#9ca3af; font-weight:700; font-size:.9rem;
}
.cuota-card__info { display:flex; flex-direction:column; gap:.15rem; }
.cuota-card__montos { text-align:right; display:flex; flex-direction:column; gap:.1rem; }
.cuota-rechazo {
    display:flex; align-items:center; gap:.75rem;
    background:#2d1515; border-left:3px solid #ef4444;
    padding:.65rem 1rem; margin:.25rem 0; border-radius:0 6px 6px 0;
    font-size:.85rem; color:#fca5a5;
}
.cuota-rechazo i { color:#ef4444; }
.badge--verde  { background:#166534; color:#86efac; }
.badge--gris   { background:#374151; color:#9ca3af; }
.badge--warning { background:#92400e; color:#fde68a; }
.badge--info   { background:#075985; color:#bae6fd; }
.badge--rojo   { background:#7f1d1d; color:#fca5a5; }
.abono-fila {
    display:grid; grid-template-columns:1fr auto 1fr auto;
    gap:1rem; align-items:center;
    padding:.75rem 0; border-bottom:1px solid #1e3a2a;
}
.abono-fila:last-child { border-bottom:none; }
.abono-fila__fecha,
.abono-fila__monto,
.abono-fila__estatus { display:flex; flex-direction:column; gap:.15rem; }
.abono-fila__monto   { text-align:right; }
@media (max-width:640px) {
    .abono-fila { grid-template-columns:1fr auto; }
    .abono-fila__monto { text-align:right; }
}
.texto-muted { color:#9ca3af; font-size:.82rem; }
.texto-rojo  { color:#f87171; font-size:.82rem; }
.req { color:#ef4444; }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
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
.modal__head h3 { margin:0; font-size:1rem; color:#f0f6f1; }
.modal__cerrar { background:none; border:none; color:#9ca3af; font-size:1.4rem; cursor:pointer; }
.modal__body { padding:1.25rem 1.5rem; }
.modal__foot {
    padding:1rem 1.5rem; border-top:1px solid #1e3a2a;
    display:flex; justify-content:flex-end; gap:.75rem;
}
@media(max-width:640px) {
    .pagos-resumen { grid-template-columns:1fr 1fr; }
    .cuota-card { grid-template-columns:40px 1fr auto; }
    .cuota-card__montos, .cuota-card__accion { grid-column:2/-1; }
    .form-row { grid-template-columns:1fr; }
}
</style>
