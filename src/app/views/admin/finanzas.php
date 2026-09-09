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
                <p>Ingresos y gastos del programa — Ciclo 1</p>
            </div>
            <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                <a href="/admin/finanzas/exportar?tipo=ingresos" class="btn btn--sm btn--outline">
                    <i class="fas fa-file-csv"></i> Ingresos
                </a>
                <a href="/admin/finanzas/exportar?tipo=gastos" class="btn btn--sm btn--outline">
                    <i class="fas fa-file-csv"></i> Gastos
                </a>
                <a href="/admin/finanzas/movimientos" class="btn btn--sm btn--primario">
                    <i class="fas fa-plus"></i> Registrar movimiento
                </a>
            </div>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <!-- Números del programa ───────────────────────────── -->
        <div class="fin-kpis">
            <div class="kpi-card kpi-card--neutro">
                <div class="kpi-card__icono"><i class="fas fa-arrow-down"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($kpis['ingresos'], 2) ?></span>
                    <span class="kpi-card__label">Total ingresado</span>
                    <span class="kpi-card__pie">Confirmado, de todas las fuentes</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--rechazada">
                <div class="kpi-card__icono"><i class="fas fa-arrow-up"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($kpis['gastos'], 2) ?></span>
                    <span class="kpi-card__label">Total gastado</span>
                </div>
            </div>
            <div class="kpi-card <?= $kpis['en_caja'] >= 0 ? 'kpi-card--total' : 'kpi-card--rechazada' ?>">
                <div class="kpi-card__icono"><i class="fas fa-wallet"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= usd($kpis['en_caja']) ?></span>
                    <span class="kpi-card__label">En caja</span>
                    <span class="kpi-card__pie">Ingresos menos gastos</span>
                </div>
            </div>
            <div class="kpi-card <?= $kpis['deuda_prestamos'] > 0 ? 'kpi-card--revision' : 'kpi-card--neutro' ?>">
                <div class="kpi-card__icono"><i class="fas fa-hand-holding-dollar"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($kpis['deuda_prestamos'], 2) ?></span>
                    <span class="kpi-card__label">Por devolver</span>
                    <span class="kpi-card__pie">Préstamos pendientes</span>
                </div>
            </div>
            <div class="kpi-card <?= $kpis['disponible_real'] >= 0 ? 'kpi-card--aprobada' : 'kpi-card--rechazada' ?>">
                <div class="kpi-card__icono"><i class="fas fa-scale-balanced"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= usd($kpis['disponible_real']) ?></span>
                    <span class="kpi-card__label">Disponible real</span>
                    <span class="kpi-card__pie">Ya descontada la deuda</span>
                </div>
            </div>
        </div>

        <!-- Matrículas ─────────────────────────────────────── -->
        <section class="admin-panel" style="margin-bottom:1.5rem">
            <div class="admin-panel__header">
                <h2><i class="fas fa-graduation-cap"></i> Matrículas de la cohorte</h2>
                <a href="/admin/finanzas/matriculas" class="btn btn--xs btn--outline">Ver detalle</a>
            </div>
            <div style="padding:1.25rem">
                <?php if ($matricula['participantes'] === 0): ?>
                <p class="fin-vacio"><i class="fas fa-user-slash"></i> Todavía no hay participantes aprobados.</p>
                <?php else: ?>
                <div class="fin-dato">
                    <span class="fin-dato__label">Participantes aprobados</span>
                    <span class="fin-dato__valor"><?= $matricula['participantes'] ?></span>
                </div>
                <div class="fin-dato">
                    <span class="fin-dato__label">Costo del ciclo, sin descontar becas</span>
                    <span class="fin-dato__valor">$<?= number_format($matricula['costo_total'], 2) ?></span>
                </div>
                <div class="fin-dato">
                    <span class="fin-dato__label">Cubierto por becas</span>
                    <span class="fin-dato__valor" style="color:#0ea5e9">$<?= number_format($matricula['becado'], 2) ?></span>
                </div>
                <div class="fin-dato">
                    <span class="fin-dato__label">Deben pagar los participantes</span>
                    <span class="fin-dato__valor">$<?= number_format($matricula['esperado'], 2) ?></span>
                </div>
                <div class="fin-dato fin-dato--total">
                    <span class="fin-dato__label">Cobrado hasta hoy</span>
                    <span>
                        <span class="fin-dato__valor">$<?= number_format($matricula['cobrado'], 2) ?></span>
                        <div class="fin-barra"><div class="fin-barra__fill" style="width:<?= $matricula['porcentaje'] ?>%"></div></div>
                        <small class="texto-muted"><?= $matricula['porcentaje'] ?>% · faltan $<?= number_format($matricula['por_cobrar'], 2) ?></small>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Pagos por confirmar ────────────────────────────── -->
        <section class="admin-panel" style="margin-bottom:1.5rem">
            <div class="admin-panel__header">
                <h2><i class="fas fa-clock"></i> Pagos por confirmar</h2>
                <span class="badge badge--warning"><?= count($pendientes) ?></span>
            </div>

            <?php if (empty($pendientes)): ?>
            <p class="fin-vacio"><i class="fas fa-check-circle"></i> Nada pendiente por revisar.</p>
            <?php else: ?>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>De quién</th>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Fecha</th>
                        <th>Comp.</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pendientes as $p): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($p['nombre_estudiante'] ?? $p['aportante'] ?? 'Sin identificar') ?></strong>
                            <?php if (!empty($p['cedula'])): ?>
                            <br><small class="texto-muted"><?= htmlspecialchars($p['cedula']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['concepto']) ?></td>
                        <td>
                            <strong>$<?= number_format($p['monto_usd'], 2) ?></strong>
                            <?php if (!empty($p['monto_ves'])): ?>
                            <br><small class="texto-muted">Bs <?= number_format($p['monto_ves'], 2) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(str_replace('_', ' ', $p['metodo_pago'])) ?>
                            <?php if (!empty($p['referencia'])): ?>
                            <br><small class="texto-muted">Ref: <?= htmlspecialchars($p['referencia']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                        <td>
                            <?php if (!empty($p['comprobante_ruta'])): ?>
                            <a href="/<?= htmlspecialchars($p['comprobante_ruta']) ?>" target="_blank" class="btn btn--xs btn--outline">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php else: ?>
                            <span class="texto-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space:nowrap">
                            <button class="btn btn--xs btn--verde" onclick="abrirConfirmar(<?= $p['id'] ?>, 'confirmar')">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn--xs btn--peligro" onclick="abrirConfirmar(<?= $p['id'] ?>, 'rechazar')">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </section>

        <div class="admin-cols">

            <!-- Fondos ─────────────────────────────────────── -->
            <section class="admin-panel" style="flex:1">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-tags"></i> Fondos</h2>
                    <button class="btn btn--xs btn--outline" data-modal="modal-fondo">
                        <i class="fas fa-plus"></i> Nuevo
                    </button>
                </div>
                <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr><th>Fondo</th><th>Entró</th><th>Salió</th><th>Queda</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($fondos as $f): ?>
                    <?php $saldo_f = (float)$f['ingresos'] - (float)$f['gastos']; ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($f['nombre']) ?>
                            <?php if ($f['tipo'] === 'especifico'): ?>
                            <span class="badge badge--neutro btn--xs">etiquetado</span>
                            <?php endif; ?>
                        </td>
                        <td>$<?= number_format($f['ingresos'], 2) ?></td>
                        <td>$<?= number_format($f['gastos'], 2) ?></td>
                        <td class="<?= $saldo_f < 0 ? 'texto-rojo' : 'texto-verde' ?>">
                            <?= usd($saldo_f) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </section>

            <!-- Cuentas ────────────────────────────────────── -->
            <section class="admin-panel" style="flex:1">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-wallet"></i> Dónde está el dinero</h2>
                </div>
                <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr><th>Cuenta</th><th>Entró</th><th>Salió</th><th>Saldo</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($cuentas as $c): ?>
                    <?php $saldo_c = (float)$c['entradas'] - (float)$c['salidas']; ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nombre']) ?></td>
                        <td>$<?= number_format($c['entradas'], 2) ?></td>
                        <td>$<?= number_format($c['salidas'], 2) ?></td>
                        <td class="<?= $saldo_c < 0 ? 'texto-rojo' : '' ?>"><?= usd($saldo_c) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="fin-nota" style="padding:0 1rem 1rem">Todo consolidado en dólares. Los movimientos en bolívares se convierten con la tasa que registres.</p>
                </div>
            </section>

        </div>

        <div class="admin-cols" style="margin-top:1.5rem">

            <!-- Gasto por rubro ────────────────────────────── -->
            <section class="admin-panel" style="flex:1">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-chart-simple"></i> Gasto por rubro</h2>
                </div>
                <?php if (empty($gastos_categoria)): ?>
                <p class="fin-vacio"><i class="fas fa-inbox"></i> Sin gastos registrados.</p>
                <?php else: ?>
                <?php $mayor = max(array_map(fn($g) => (float)$g['total'], $gastos_categoria)) ?: 1; ?>
                <div style="padding:1rem 1.25rem">
                <?php foreach ($gastos_categoria as $g): ?>
                    <div style="margin-bottom:.9rem">
                        <div style="display:flex;justify-content:space-between;font-size:.88rem">
                            <span><?= htmlspecialchars(GastoModel::CATEGORIAS[$g['categoria']] ?? $g['categoria']) ?></span>
                            <strong>$<?= number_format($g['total'], 2) ?></strong>
                        </div>
                        <div class="fin-barra">
                            <div class="fin-barra__fill" style="width:<?= round((float)$g['total'] / $mayor * 100) ?>%;background:#0ea5e9"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- Últimos movimientos ────────────────────────── -->
            <section class="admin-panel" style="flex:1">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-list"></i> Últimos movimientos</h2>
                    <a href="/admin/finanzas/movimientos" class="btn btn--xs btn--outline">Ver todos</a>
                </div>
                <?php
                    $mov = [];
                    foreach ($ultimos_ingresos as $i) {
                        $mov[] = ['fecha' => $i['fecha'], 'signo' => '+', 'concepto' => $i['concepto'], 'monto' => $i['monto_usd']];
                    }
                    foreach ($ultimos_gastos as $g) {
                        $mov[] = ['fecha' => $g['fecha_gasto'], 'signo' => '−', 'concepto' => $g['concepto'], 'monto' => $g['monto_usd']];
                    }
                    usort($mov, fn($a, $b) => strcmp($b['fecha'], $a['fecha']));
                    $mov = array_slice($mov, 0, 10);
                ?>
                <?php if (empty($mov)): ?>
                <p class="fin-vacio"><i class="fas fa-inbox"></i> Todavía no hay movimientos.</p>
                <?php else: ?>
                <div class="tabla-wrap">
                <table class="tabla">
                    <tbody>
                    <?php foreach ($mov as $m): ?>
                    <tr>
                        <td class="texto-muted" style="white-space:nowrap"><?= date('d/m', strtotime($m['fecha'])) ?></td>
                        <td><?= htmlspecialchars($m['concepto']) ?></td>
                        <td style="text-align:right;white-space:nowrap"
                            class="<?= $m['signo'] === '+' ? 'texto-verde' : 'texto-rojo' ?>">
                            <?= $m['signo'] ?>$<?= number_format($m['monto'], 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </section>

        </div>

    </main>
</div>

<!-- Modal: Confirmar / Rechazar pago ───────────────────────── -->
<div class="modal-overlay" id="modal-confirmar">
    <div class="modal">
        <div class="modal__header">
            <h3 id="modal-confirmar-titulo">Confirmar pago</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-confirmar')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/confirmar">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="conf-id">
            <input type="hidden" name="accion" id="conf-accion">
            <div class="modal__body">
                <div class="form-grupo">
                    <label>Notas para el participante</label>
                    <textarea name="notas_admin" id="conf-notas" rows="3"
                              placeholder="Opcional al confirmar, obligatorio al rechazar"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-confirmar')">Cancelar</button>
                <button type="submit" class="btn btn--primario" id="conf-btn">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Nuevo fondo ─────────────────────────────────────── -->
<div class="modal-overlay" id="modal-fondo">
    <div class="modal">
        <div class="modal__header">
            <h3><i class="fas fa-tags"></i> Nuevo fondo</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-fondo')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/fondo">
            <?= csrf_field() ?>
            <div class="modal__body">
                <div class="form-grupo">
                    <label>Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" required maxlength="120"
                           placeholder="Ej: Franelas cohorte 1">
                </div>
                <div class="form-grupo">
                    <label>Para qué es</label>
                    <input type="text" name="descripcion" maxlength="255">
                </div>
                <div class="form-grupo">
                    <label>Tipo</label>
                    <select name="tipo">
                        <option value="especifico">Etiquetado — dinero con un destino puntual</option>
                        <option value="general">General — entra a la caja común</option>
                    </select>
                    <span class="fin-nota">Un fondo etiquetado te deja ver cuánto entró para ese fin y cuánto se gastó.</span>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-fondo')">Cancelar</button>
                <button type="submit" class="btn btn--primario"><i class="fas fa-save"></i> Crear fondo</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirConfirmar(id, accion) {
    document.getElementById('conf-id').value     = id;
    document.getElementById('conf-accion').value = accion;
    var esRechazo = accion === 'rechazar';
    document.getElementById('modal-confirmar-titulo').textContent = esRechazo ? 'Rechazar pago' : 'Confirmar pago';
    document.getElementById('conf-btn').textContent               = esRechazo ? 'Rechazar' : 'Confirmar pago';
    document.getElementById('conf-btn').className = 'btn ' + (esRechazo ? 'btn--peligro' : 'btn--verde');
    document.getElementById('conf-notas').required = esRechazo;
    document.getElementById('modal-confirmar').classList.add('abierto');
}
function cerrarModal(id) {
    document.getElementById(id).classList.remove('abierto');
}
// Cerrar al hacer clic en el fondo, fuera del cuadro
document.querySelectorAll('.modal-overlay').forEach(function (ov) {
    ov.addEventListener('click', function (e) {
        if (e.target === ov) ov.classList.remove('abierto');
    });
});
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.modal-overlay.abierto').forEach(function (ov) {
        ov.classList.remove('abierto');
    });
});
document.querySelectorAll('[data-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById(btn.dataset.modal).classList.add('abierto');
    });
});
</script>
