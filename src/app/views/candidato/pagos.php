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
            <p>Cohorte 2026 · $750 USD en 7 cuotas mensuales</p>
        </div>

        <?php if (empty($cuotas)): ?>
        <div class="dash-card" style="text-align:center;padding:3rem">
            <i class="fas fa-lock" style="font-size:2.5rem;color:#6b7280;margin-bottom:1rem"></i>
            <p>Las cuotas se activarán una vez que tu postulación sea <strong>aprobada</strong>.</p>
        </div>
        <?php else: ?>

        <!-- Resumen ────────────────────────────────────────── -->
        <?php
            $completadas = array_filter($cuotas, fn($c) => $c['estatus'] === 'completada');
            $total_pagado = array_sum(array_column($cuotas, 'monto_acumulado_usd'));
            $total_esperado = array_sum(array_column($cuotas, 'monto_esperado_usd'));
            $pct = $total_esperado > 0 ? min(round(($total_pagado / $total_esperado) * 100), 100) : 0;
        ?>
        <div class="pagos-resumen">
            <div class="pagos-resumen__item">
                <span class="pagos-resumen__num"><?= count($completadas) ?>/<?= count($cuotas) ?></span>
                <span class="pagos-resumen__label">Cuotas completadas</span>
            </div>
            <div class="pagos-resumen__item">
                <span class="pagos-resumen__num">$<?= number_format($total_pagado, 2) ?></span>
                <span class="pagos-resumen__label">Total pagado</span>
            </div>
            <div class="pagos-resumen__item">
                <span class="pagos-resumen__num pagos-resumen__num--pendiente">
                    $<?= number_format(max(0, $total_esperado - $total_pagado), 2) ?>
                </span>
                <span class="pagos-resumen__label">Saldo pendiente</span>
            </div>
            <div class="pagos-resumen__barra">
                <div class="pagos-resumen__fill" style="width:<?= $pct ?>%"></div>
                <span><?= $pct ?>%</span>
            </div>
        </div>

        <!-- Tabla de cuotas ────────────────────────────────── -->
        <div class="dash-card" style="margin-top:1.5rem">
            <h2 style="margin-bottom:1.25rem"><i class="fas fa-list"></i> Detalle de cuotas</h2>

            <?php foreach ($cuotas as $c):
                $restante = max(0, (float)$c['monto_esperado_usd'] - (float)$c['monto_acumulado_usd']);

                // Buscar abono rechazado de esta cuota
                $ab_rechazado = null;
                foreach ($abonos as $ab) {
                    if ($ab['cuota_numero'] == $c['cuota_numero'] && $ab['estatus'] === 'rechazado') {
                        $ab_rechazado = $ab;
                        break;
                    }
                }

                // Abono pendiente de esta cuota
                $tiene_pendiente = false;
                foreach ($abonos as $ab) {
                    if ($ab['cuota_numero'] == $c['cuota_numero'] && $ab['estatus'] === 'pendiente') {
                        $tiene_pendiente = true;
                        break;
                    }
                }
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
                    <?php elseif ($tiene_pendiente): ?>
                    <span class="badge badge--info">En revisión</span>
                    <?php else: ?>
                    <span class="badge badge--gris">Pendiente</span>
                    <?php endif; ?>
                </div>

                <div class="cuota-card__accion">
                    <?php if ($c['estatus'] !== 'completada' && !$tiene_pendiente): ?>
                    <button class="btn btn--sm btn--primario"
                            onclick="abrirAbono(<?= $c['id'] ?>, <?= $c['cuota_numero'] ?>, <?= number_format($restante, 2, '.', '') ?>)">
                        <i class="fas fa-upload"></i>
                        <?= $c['estatus'] === 'parcial' ? 'Completar pago' : 'Subir comprobante' ?>
                    </button>
                    <?php elseif ($tiene_pendiente): ?>
                    <span class="texto-muted" style="font-size:.85rem"><i class="fas fa-clock"></i> Pendiente de revisión</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($ab_rechazado): ?>
            <div class="cuota-rechazo">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Último abono rechazado:</strong>
                    <?= htmlspecialchars($ab_rechazado['notas_admin'] ?? 'Sin nota') ?>
                </div>
                <button class="btn btn--xs btn--outline"
                        onclick="abrirAbono(<?= $c['id'] ?>, <?= $c['cuota_numero'] ?>, <?= number_format($restante, 2, '.', '') ?>)">
                    Volver a subir
                </button>
            </div>
            <?php endif; ?>

            <?php endforeach; ?>
        </div>

        <?php endif; ?>

    </main>
</div>

<!-- Modal: Subir Abono ─────────────────────────────────────── -->
<div class="modal" id="modal-abono" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-abono')"></div>
    <div class="modal__box" style="max-width:520px">
        <div class="modal__head">
            <h3><i class="fas fa-upload"></i> Subir comprobante — Cuota <span id="modal-cuota-num"></span></h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-abono')">&times;</button>
        </div>
        <form method="POST" action="/candidato/pagos" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="cuota_id" id="abono-cuota-id">
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Monto pagado (USD) <span class="req">*</span></label>
                        <input type="number" name="monto_declarado_usd" id="abono-monto-usd"
                               class="form-control" step="0.01" min="0.01" required>
                        <small class="texto-muted">Restante de esta cuota: $<span id="abono-restante"></span></small>
                    </div>
                    <div class="form-group">
                        <label>Monto en Bs (opcional)</label>
                        <input type="number" name="monto_declarado_ves" class="form-control" step="0.01" min="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tasa de cambio (Bs/$)</label>
                        <input type="number" name="tasa_cambio" class="form-control" step="0.01" min="0" placeholder="Ej: 55.30">
                    </div>
                    <div class="form-group">
                        <label>Fecha del pago <span class="req">*</span></label>
                        <input type="date" name="fecha_pago_declarado" class="form-control"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
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
function abrirAbono(cuotaId, numCuota, restante) {
    document.getElementById('abono-cuota-id').value    = cuotaId;
    document.getElementById('modal-cuota-num').textContent = '#' + numCuota;
    document.getElementById('abono-monto-usd').value   = restante;
    document.getElementById('abono-restante').textContent  = restante.toFixed(2);
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
