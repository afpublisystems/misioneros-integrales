<?php $titulo = 'Traslados de fondos'; ?>

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
                <h1>Traslados de fondos</h1>
                <p>Lo que se le pide a tesorería que verifique y transfiera</p>
            </div>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <div class="fin-kpis">
            <div class="kpi-card kpi-card--revision">
                <div class="kpi-card__icono"><i class="fas fa-inbox"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $totales['sin_reportar'] ?></span>
                    <span class="kpi-card__label">Pagos sin reportar</span>
                    <span class="kpi-card__pie">
                        $<?= number_format($totales['sin_reportar_usd'], 2) ?>
                        <?php if ($totales['sin_reportar_ves'] > 0): ?>
                        · Bs <?= number_format($totales['sin_reportar_ves'], 2) ?>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            <div class="kpi-card kpi-card--azul">
                <div class="kpi-card__icono"><i class="fas fa-paper-plane"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['en_proceso_usd'], 2) ?></span>
                    <span class="kpi-card__label">En manos de tesorería</span>
                    <span class="kpi-card__pie">Enviado o verificado, sin trasladar</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--aprobada">
                <div class="kpi-card__icono"><i class="fas fa-circle-check"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['trasladado_usd'], 2) ?></span>
                    <span class="kpi-card__label">Ya trasladado</span>
                </div>
            </div>
        </div>

        <!-- ── Armar una solicitud ──────────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-file-circle-plus"></i> Pagos sin reportar</h2>
            </div>

            <form method="GET" action="/admin/finanzas/traslados" class="fin-filtros">
                <div class="form-grupo">
                    <label>Desde</label>
                    <input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>">
                </div>
                <div class="form-grupo">
                    <label>Hasta</label>
                    <input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>">
                </div>
                <div class="form-grupo">
                    <label>Cuenta</label>
                    <select name="cuenta_id">
                        <option value="">Todas</option>
                        <?php foreach ($cuentas as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (string)$filtros['cuenta_id'] === (string)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grupo">
                    <label class="fin-check">
                        <input type="checkbox" name="solo_bolivares" value="1" <?= $filtros['solo_bolivares'] ? 'checked' : '' ?>>
                        Solo los que entraron en bolívares
                    </label>
                </div>
                <div class="form-grupo">
                    <button type="submit" class="btn btn--sm btn--outline"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="/admin/finanzas/traslados" class="btn btn--sm btn--outline">Limpiar</a>
                </div>
            </form>

            <?php if (empty($sin_reportar)): ?>
            <p class="fin-vacio">
                <i class="fas fa-check-double"></i>
                No hay pagos pendientes por reportar con ese filtro.
            </p>
            <?php else: ?>
            <form method="POST" action="/admin/finanzas/traslado" id="form-solicitud">
                <?= csrf_field() ?>
                <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th style="width:2rem"><input type="checkbox" onclick="marcarTodos(this)"></th>
                            <th>Fecha</th>
                            <th>De quién</th>
                            <th>Concepto</th>
                            <th>Banco / referencia</th>
                            <th class="num">Monto Bs</th>
                            <th class="num">Monto USD</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sin_reportar as $p): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="ingresos[]" value="<?= $p['id'] ?>"
                                   class="chk-pago" data-usd="<?= (float) $p['monto_usd'] ?>"
                                   data-ves="<?= (float) $p['monto_ves'] ?>" onchange="actualizarTotal()">
                        </td>
                        <td class="fecha"><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                        <td>
                            <?= htmlspecialchars($p['de_quien'] ?: '—') ?>
                            <?php if (!empty($p['cedula'])): ?>
                            <br><small class="texto-muted"><?= htmlspecialchars($p['cedula']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['concepto']) ?></td>
                        <td>
                            <?= htmlspecialchars($p['banco_origen'] ?: '—') ?>
                            <br><small class="texto-muted"><?= htmlspecialchars($p['referencia'] ?: 'sin referencia') ?></small>
                        </td>
                        <td class="num"><?= $p['monto_ves'] ? number_format($p['monto_ves'], 2) : '—' ?></td>
                        <td class="num">$<?= number_format($p['monto_usd'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <div class="fin-barra-accion">
                    <span id="resumen-seleccion" class="texto-muted">Ningún pago marcado</span>
                    <button type="button" class="btn btn--sm btn--primario" onclick="abrirSolicitud()">
                        <i class="fas fa-file-export"></i> Armar solicitud
                    </button>
                </div>

                <!-- Datos de la solicitud -->
                <div class="modal-overlay" id="modal-solicitud">
                    <div class="modal">
                        <div class="modal__header">
                            <h3><i class="fas fa-file-export"></i> Nueva solicitud de traslado</h3>
                            <button type="button" class="modal__cerrar" onclick="cerrarModal('modal-solicitud')">&times;</button>
                        </div>
                        <div class="modal__body">
                            <p class="fin-dato">
                                <span class="fin-dato__label">Pagos marcados</span>
                                <span class="fin-dato__valor" id="modal-resumen"></span>
                            </p>
                            <div class="form-grid-2">
                                <div class="form-grupo">
                                    <label>Fecha <span class="req">*</span></label>
                                    <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Concepto <span class="req">*</span></label>
                                    <input type="text" name="concepto" maxlength="200" required
                                           placeholder="Ej: Matrículas recibidas en septiembre">
                                </div>
                            </div>
                            <div class="form-grupo">
                                <label>Trasladar a</label>
                                <input type="text" name="destino" maxlength="200"
                                       placeholder="Cuenta o persona a la que debe ir el dinero">
                            </div>
                            <div class="form-grupo">
                                <label>Observaciones</label>
                                <textarea name="notas" rows="2" maxlength="500"></textarea>
                            </div>
                            <p class="fin-nota">
                                Los pagos marcados quedan amarrados a esta solicitud y no vuelven a aparecer
                                en la lista. Si la anulas, regresan.
                            </p>
                        </div>
                        <div class="modal__footer">
                            <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-solicitud')">Cancelar</button>
                            <button type="submit" class="btn btn--primario"><i class="fas fa-save"></i> Crear solicitud</button>
                        </div>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </section>

        <!-- ── Solicitudes hechas ───────────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-folder-open"></i> Solicitudes</h2>
            </div>

            <?php if (empty($solicitudes)): ?>
            <p class="fin-vacio">
                <i class="fas fa-folder-open"></i>
                Todavía no has armado ninguna solicitud.
            </p>
            <?php else: ?>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Solicitud</th>
                        <th>Concepto</th>
                        <th class="num">Pagos</th>
                        <th class="num">Monto Bs</th>
                        <th class="num">Monto USD</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($solicitudes as $s): ?>
                <tr<?= $s['estatus'] === 'anulada' ? ' class="anulado"' : '' ?>>
                    <td>
                        <strong><?= htmlspecialchars($s['codigo']) ?></strong><br>
                        <small class="texto-muted"><?= date('d/m/Y', strtotime($s['fecha'])) ?></small>
                    </td>
                    <td>
                        <?= htmlspecialchars($s['concepto']) ?>
                        <?php if (!empty($s['destino'])): ?>
                        <br><small class="texto-muted">→ <?= htmlspecialchars($s['destino']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="num"><?= $s['pagos'] ?></td>
                    <td class="num"><?= number_format($s['total_ves'], 2) ?></td>
                    <td class="num"><strong>$<?= number_format($s['total_usd'], 2) ?></strong></td>
                    <td>
                        <?php
                            $clase = match ($s['estatus']) {
                                'trasladada' => 'badge--exito',
                                'verificada' => 'badge--info',
                                'anulada'    => 'badge--peligro',
                                default      => 'badge--warning',
                            };
                        ?>
                        <span class="badge <?= $clase ?>"><?= $estatus[$s['estatus']] ?? $s['estatus'] ?></span>
                    </td>
                    <td style="white-space:nowrap">
                        <a href="/admin/finanzas/traslado?id=<?= $s['id'] ?>" class="btn btn--xs btn--outline" title="Ver e imprimir">
                            <i class="fas fa-print"></i>
                        </a>
                        <?php if ($s['estatus'] !== 'anulada' && $s['estatus'] !== 'trasladada'): ?>
                        <button class="btn btn--xs btn--outline" title="Cambiar estado"
                                onclick="cambiarEstado('<?= htmlspecialchars($s['codigo']) ?>', <?= $s['id'] ?>, '<?= $s['estatus'] ?>')">
                            <i class="fas fa-rotate"></i>
                        </button>
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

<!-- Modal: cambiar estado ──────────────────────────────────── -->
<div class="modal-overlay" id="modal-estado">
    <div class="modal">
        <div class="modal__header">
            <h3><i class="fas fa-rotate"></i> Solicitud <span id="estado-codigo"></span></h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-estado')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/traslado/estatus">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="estado-id">
            <div class="modal__body">
                <div class="form-grupo">
                    <label>Nuevo estado <span class="req">*</span></label>
                    <select name="estatus" id="estado-select" required>
                        <option value="verificada">Verificada por tesorería</option>
                        <option value="trasladada">Trasladada — el dinero ya llegó</option>
                        <option value="anulada">Anulada — devolver los pagos a la lista</option>
                    </select>
                </div>
                <p class="fin-nota">
                    Anularla libera sus pagos para que entren en otra solicitud. El documento se queda guardado.
                </p>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-estado')">Cancelar</button>
                <button type="submit" class="btn btn--primario"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.fin-filtros {
    display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;
    padding: 0 1.25rem 1rem;
}
.fin-filtros .form-grupo { margin-bottom: 0; }
.fin-check { display: flex; align-items: center; gap: .45rem; font-size: .82rem; }
.fin-check input { width: auto; }
.fin-barra-accion {
    display: flex; justify-content: space-between; align-items: center;
    gap: 1rem; flex-wrap: wrap; padding: 1rem 1.25rem; border-top: 1px solid #f1f5f9;
}
</style>

<script>
function cerrarModal(id) {
    document.getElementById(id).classList.remove('abierto');
}
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

function marcarTodos(maestro) {
    document.querySelectorAll('.chk-pago').forEach(function (c) { c.checked = maestro.checked; });
    actualizarTotal();
}
function seleccionados() {
    return Array.prototype.slice.call(document.querySelectorAll('.chk-pago:checked'));
}
function actualizarTotal() {
    var marcados = seleccionados();
    var usd = 0, ves = 0;
    marcados.forEach(function (c) {
        usd += parseFloat(c.dataset.usd || 0);
        ves += parseFloat(c.dataset.ves || 0);
    });
    var texto = marcados.length === 0
        ? 'Ningún pago marcado'
        : marcados.length + (marcados.length === 1 ? ' pago · ' : ' pagos · ') +
          'Bs ' + ves.toFixed(2) + ' · $' + usd.toFixed(2);
    document.getElementById('resumen-seleccion').textContent = texto;
    return texto;
}
function abrirSolicitud() {
    if (seleccionados().length === 0) {
        alert('Marca al menos un pago.');
        return;
    }
    document.getElementById('modal-resumen').textContent = actualizarTotal();
    document.getElementById('modal-solicitud').classList.add('abierto');
}
function cambiarEstado(codigo, id, actual) {
    document.getElementById('estado-codigo').textContent = codigo;
    document.getElementById('estado-id').value = id;
    document.getElementById('estado-select').value = actual === 'verificada' ? 'trasladada' : 'verificada';
    document.getElementById('modal-estado').classList.add('abierto');
}
</script>
