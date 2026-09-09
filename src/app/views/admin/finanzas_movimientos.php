<?php $titulo = 'Ingresos y gastos'; ?>

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
                <h1>Ingresos y gastos</h1>
                <p>Todo el movimiento de dinero del programa</p>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                <button class="btn btn--sm btn--verde" data-modal="modal-ingreso">
                    <i class="fas fa-arrow-down"></i> Registrar ingreso
                </button>
                <button class="btn btn--sm btn--peligro" data-modal="modal-gasto">
                    <i class="fas fa-arrow-up"></i> Registrar gasto
                </button>
            </div>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <!-- Filtros ────────────────────────────────────────── -->
        <section class="admin-panel" style="margin-bottom:1.5rem">
            <form method="GET" action="/admin/finanzas/movimientos" style="padding:1.25rem">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                <div class="form-grid-3">
                    <div class="form-grupo">
                        <label>Fondo</label>
                        <select name="fondo_id">
                            <option value="">Todos</option>
                            <?php foreach ($fondos as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= (string)$filtros['fondo_id'] === (string)$f['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Desde</label>
                        <input type="date" name="desde" value="<?= htmlspecialchars($filtros['desde']) ?>">
                    </div>
                    <div class="form-grupo">
                        <label>Hasta</label>
                        <input type="date" name="hasta" value="<?= htmlspecialchars($filtros['hasta']) ?>">
                    </div>
                </div>
                <div style="display:flex;gap:.5rem">
                    <button type="submit" class="btn btn--sm btn--primario"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="/admin/finanzas/movimientos" class="btn btn--sm btn--outline">Limpiar</a>
                </div>
            </form>
        </section>

        <!-- Ingresos ───────────────────────────────────────── -->
        <section class="admin-panel" style="margin-bottom:1.5rem">
            <div class="admin-panel__header">
                <h2><i class="fas fa-arrow-down texto-verde"></i> Ingresos</h2>
                <span class="badge badge--neutro">
                    $<?= number_format(array_sum(array_map(fn($i) => $i['estatus'] === 'confirmado' ? (float)$i['monto_usd'] : 0, $ingresos)), 2) ?>
                </span>
            </div>

            <?php if (empty($ingresos)): ?>
            <p class="fin-vacio"><i class="fas fa-inbox"></i> Sin ingresos en este filtro.</p>
            <?php else: ?>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Origen</th><th>De quién</th><th>Concepto</th>
                        <th>Fondo</th><th>Monto</th><th>Estatus</th><th>Comp.</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ingresos as $i): ?>
                <?php $anul_i = $i['estatus'] === 'anulado'; ?>
                <tr class="<?= $anul_i ? 'anulado' : '' ?>">
                    <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($i['fecha'])) ?></td>
                    <td><span class="badge badge--info"><?= htmlspecialchars($origenes[$i['origen']] ?? $i['origen']) ?></span></td>
                    <td><?= htmlspecialchars($i['nombre_estudiante'] ?? $i['aportante'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($i['concepto']) ?></td>
                    <td class="texto-muted"><?= htmlspecialchars($i['fondo_nombre'] ?? '—') ?></td>
                    <td style="white-space:nowrap">
                        <strong class="<?= $anul_i ? 'monto-anulado' : '' ?>">$<?= number_format($i['monto_usd'], 2) ?></strong>
                        <?php if (!empty($i['monto_ves'])): ?>
                        <br><small class="texto-muted">Bs <?= number_format($i['monto_ves'], 2) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($anul_i): ?>
                        <span class="badge badge--peligro">Anulado</span>
                        <?php if (!empty($i['motivo_anulacion'])): ?>
                        <span class="fin-motivo"><?= htmlspecialchars($i['motivo_anulacion']) ?></span>
                        <?php endif; ?>
                        <?php elseif ($i['estatus'] === 'confirmado'): ?>
                        <span class="badge badge--exito">Confirmado</span>
                        <?php else: ?>
                        <span class="badge badge--warning">Pendiente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($i['comprobante_ruta'])): ?>
                        <a href="/<?= htmlspecialchars($i['comprobante_ruta']) ?>" target="_blank" class="btn btn--xs btn--outline">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php else: ?>
                        <span class="texto-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap">
                        <?php if ($i['origen'] === 'prestamo'): ?>
                        <span class="texto-muted" title="Se administra desde Préstamos">
                            <i class="fas fa-lock"></i>
                        </span>
                        <?php elseif ($anul_i): ?>
                        <button type="button" class="btn btn--xs btn--outline" title="Reactivar"
                                onclick="reactivarMov('ingreso', <?= $i['id'] ?>)">
                            <i class="fas fa-rotate-left"></i>
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn btn--xs btn--outline" title="Editar"
                                data-editar-ingreso="<?= htmlspecialchars(json_encode([
                                    'id'           => $i['id'],
                                    'fecha'        => $i['fecha'],
                                    'origen'       => $i['origen'],
                                    'aspirante_id' => $i['aspirante_id'],
                                    'fondo_id'     => $i['fondo_id'],
                                    'cuenta_id'    => $i['cuenta_id'],
                                    'aportante'    => $i['aportante'],
                                    'concepto'     => $i['concepto'],
                                    'monto_usd'    => $i['monto_usd'],
                                    'monto_ves'    => $i['monto_ves'],
                                    'tasa_cambio'  => $i['tasa_cambio'],
                                    'metodo_pago'  => $i['metodo_pago'],
                                    'banco_origen' => $i['banco_origen'],
                                    'referencia'   => $i['referencia'],
                                    'notas'        => $i['notas'],
                                ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" class="btn btn--xs btn--outline" title="Anular"
                                onclick="abrirAnular('ingreso', <?= $i['id'] ?>, '<?= htmlspecialchars(addslashes($i['concepto'] . ' — $' . number_format($i['monto_usd'], 2)), ENT_QUOTES) ?>')">
                            <i class="fas fa-ban"></i>
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

        <!-- Gastos ─────────────────────────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-arrow-up texto-rojo"></i> Gastos</h2>
                <span class="badge badge--neutro">
                    $<?= number_format(array_sum(array_map(fn($g) => $g['estatus'] === 'activo' ? (float)$g['monto_usd'] : 0, $gastos)), 2) ?>
                </span>
            </div>

            <?php if (empty($gastos)): ?>
            <p class="fin-vacio"><i class="fas fa-inbox"></i> Sin gastos en este filtro.</p>
            <?php else: ?>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Concepto</th><th>Rubro</th><th>Beneficiario</th>
                        <th>Fondo</th><th>Monto</th><th>Comp.</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($gastos as $g): ?>
                <?php $anul_g = $g['estatus'] === 'anulado'; ?>
                <tr class="<?= $anul_g ? 'anulado' : '' ?>">
                    <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($g['fecha_gasto'])) ?></td>
                    <td>
                        <?= htmlspecialchars($g['concepto']) ?>
                        <?php if (!empty($g['prestamista'])): ?>
                        <br><small class="texto-muted">Devolución a <?= htmlspecialchars($g['prestamista']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge--neutro"><?= htmlspecialchars($categorias[$g['categoria']] ?? $g['categoria']) ?></span></td>
                    <td><?= htmlspecialchars($g['beneficiario'] ?? '—') ?></td>
                    <td class="texto-muted"><?= htmlspecialchars($g['fondo_nombre'] ?? '—') ?></td>
                    <td style="white-space:nowrap">
                        <strong class="<?= $anul_g ? 'monto-anulado' : '' ?>">$<?= number_format($g['monto_usd'], 2) ?></strong>
                        <?php if ($anul_g): ?>
                        <br><span class="badge badge--peligro badge--xs">Anulado</span>
                        <?php if (!empty($g['motivo_anulacion'])): ?>
                        <span class="fin-motivo"><?= htmlspecialchars($g['motivo_anulacion']) ?></span>
                        <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($g['monto_ves'])): ?>
                        <br><small class="texto-muted">Bs <?= number_format($g['monto_ves'], 2) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($g['comprobante_ruta'])): ?>
                        <a href="/<?= htmlspecialchars($g['comprobante_ruta']) ?>" target="_blank" class="btn btn--xs btn--outline">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php else: ?>
                        <span class="texto-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap">
                        <?php if ($anul_g): ?>
                        <button type="button" class="btn btn--xs btn--outline" title="Reactivar"
                                onclick="reactivarMov('gasto', <?= $g['id'] ?>)">
                            <i class="fas fa-rotate-left"></i>
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn btn--xs btn--outline" title="Editar"
                                data-editar-gasto="<?= htmlspecialchars(json_encode([
                                    'id'           => $g['id'],
                                    'fecha_gasto'  => $g['fecha_gasto'],
                                    'concepto'     => $g['concepto'],
                                    'categoria'    => $g['categoria'],
                                    'fondo_id'     => $g['fondo_id'],
                                    'cuenta_id'    => $g['cuenta_id'],
                                    'prestamo_id'  => $g['prestamo_id'],
                                    'beneficiario' => $g['beneficiario'],
                                    'monto_usd'    => $g['monto_usd'],
                                    'monto_ves'    => $g['monto_ves'],
                                    'tasa_cambio'  => $g['tasa_cambio'],
                                    'metodo_pago'  => $g['metodo_pago'],
                                    'referencia'   => $g['referencia'],
                                    'notas'        => $g['notas'],
                                ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" class="btn btn--xs btn--outline" title="Anular"
                                onclick="abrirAnular('gasto', <?= $g['id'] ?>, '<?= htmlspecialchars(addslashes($g['concepto'] . ' — $' . number_format($g['monto_usd'], 2)), ENT_QUOTES) ?>')">
                            <i class="fas fa-ban"></i>
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

<!-- Modal: Registrar ingreso ───────────────────────────────── -->
<div class="modal-overlay" id="modal-ingreso">
    <div class="modal modal--ancho">
        <div class="modal__header">
            <h3><i class="fas fa-arrow-down"></i> <span id="titulo-ingreso">Registrar ingreso</span></h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-ingreso')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/ingreso" enctype="multipart/form-data" id="form-ingreso">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="ing-id" value="">
            <div class="modal__body modal__body--scroll">
                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Origen <span class="req">*</span></label>
                        <select name="origen" id="ing-origen" required>
                            <?php foreach ($origenes as $k => $v): ?>
                            <?php if ($k === 'prestamo') continue; ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="fin-nota">Los préstamos se registran en su propia sección.</span>
                    </div>
                    <div class="form-grupo">
                        <label>Fecha <span class="req">*</span></label>
                        <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-grupo" id="ing-participante" style="display:none">
                    <label>Participante <span class="req">*</span></label>
                    <select name="aspirante_id">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($aprobados as $a): ?>
                        <option value="<?= $a['id'] ?>">
                            <?= htmlspecialchars($a['nombres'] . ' ' . $a['apellidos']) ?> — <?= htmlspecialchars($a['cedula']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo" id="ing-aportante">
                    <label>Quién aportó</label>
                    <input type="text" name="aportante" maxlength="150"
                           placeholder="Nombre de la persona, iglesia u organización">
                </div>

                <div class="form-grupo">
                    <label>Concepto <span class="req">*</span></label>
                    <input type="text" name="concepto" required maxlength="200"
                           placeholder="Ej: Aporte para franelas de los participantes">
                </div>

                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Fondo</label>
                        <select name="fondo_id">
                            <option value="">Sin etiquetar</option>
                            <?php foreach ($fondos as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Entró a</label>
                        <select name="cuenta_id">
                            <option value="">Sin especificar</option>
                            <?php foreach ($cuentas as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid-3">
                    <div class="form-grupo">
                        <label>Monto USD</label>
                        <input type="number" name="monto_usd" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-grupo">
                        <label>Monto Bs</label>
                        <input type="number" name="monto_ves" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-grupo">
                        <label>Tasa</label>
                        <input type="number" name="tasa_cambio" step="0.0001" min="0" placeholder="Bs por USD">
                    </div>
                </div>
                <span class="fin-nota" style="margin-bottom:1rem">Si el pago fue en bolívares, llena monto en Bs y la tasa: el sistema calcula el equivalente en dólares.</span>

                <div class="form-grid-3">
                    <div class="form-grupo">
                        <label>Método <span class="req">*</span></label>
                        <select name="metodo_pago" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="zelle">Zelle</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Banco</label>
                        <input type="text" name="banco_origen" maxlength="100">
                    </div>
                    <div class="form-grupo">
                        <label>Referencia</label>
                        <input type="text" name="referencia" maxlength="100">
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Comprobante (JPG/PNG/PDF, máx 5 MB)</label>
                    <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="form-grupo">
                    <label>Notas</label>
                    <textarea name="notas" rows="2" maxlength="500"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-ingreso')">Cancelar</button>
                <button type="submit" class="btn btn--verde"><i class="fas fa-save"></i> <span id="btn-ingreso">Guardar ingreso</span></button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Registrar gasto ─────────────────────────────────── -->
<div class="modal-overlay" id="modal-gasto">
    <div class="modal modal--ancho">
        <div class="modal__header">
            <h3><i class="fas fa-arrow-up"></i> <span id="titulo-gasto">Registrar gasto</span></h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-gasto')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/gasto" enctype="multipart/form-data" id="form-gasto">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="gasto-id" value="">
            <div class="modal__body modal__body--scroll">
                <div class="form-grupo">
                    <label>Concepto <span class="req">*</span></label>
                    <input type="text" name="concepto" required maxlength="200"
                           placeholder="Ej: Compra de 12 franelas">
                </div>

                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Rubro <span class="req">*</span></label>
                        <select name="categoria" id="gasto-categoria" required>
                            <?php foreach ($categorias as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Fecha <span class="req">*</span></label>
                        <input type="date" name="fecha_gasto" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-grupo" id="gasto-prestamo" style="display:none">
                    <label>¿Qué préstamo estás devolviendo? <span class="req">*</span></label>
                    <select name="prestamo_id">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($prestamos as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['prestamista']) ?> — <?= htmlspecialchars($p['concepto']) ?>
                            (queda $<?= number_format((float)$p['monto_usd'] - (float)$p['devuelto_usd'], 2) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Fondo</label>
                        <select name="fondo_id">
                            <option value="">Sin etiquetar</option>
                            <?php foreach ($fondos as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Salió de</label>
                        <select name="cuenta_id">
                            <option value="">Sin especificar</option>
                            <?php foreach ($cuentas as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grupo">
                    <label>A quién se le pagó</label>
                    <input type="text" name="beneficiario" maxlength="150"
                           placeholder="Ej: Seminario STBV, señora María (cocina), transportista">
                </div>

                <div class="form-grid-3">
                    <div class="form-grupo">
                        <label>Monto USD</label>
                        <input type="number" name="monto_usd" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-grupo">
                        <label>Monto Bs</label>
                        <input type="number" name="monto_ves" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-grupo">
                        <label>Tasa</label>
                        <input type="number" name="tasa_cambio" step="0.0001" min="0" placeholder="Bs por USD">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Método <span class="req">*</span></label>
                        <select name="metodo_pago" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="zelle">Zelle</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Referencia</label>
                        <input type="text" name="referencia" maxlength="100">
                    </div>
                </div>

                <div class="form-grupo">
                    <label>Comprobante (JPG/PNG/PDF, máx 5 MB)</label>
                    <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf">
                </div>
                <div class="form-grupo">
                    <label>Notas</label>
                    <textarea name="notas" rows="2" maxlength="500"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-gasto')">Cancelar</button>
                <button type="submit" class="btn btn--peligro"><i class="fas fa-save"></i> <span id="btn-gasto">Guardar gasto</span></button>
            </div>
        </form>
    </div>
</div>

<script>
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
        if (btn.dataset.modal === 'modal-ingreso')   { modoIngreso(null); return; }
        if (btn.dataset.modal === 'modal-gasto')     { modoGasto(null);   return; }
        document.getElementById(btn.dataset.modal).classList.add('abierto');
    });
});

// El campo de participante solo aplica a las matrículas
var origenSel = document.getElementById('ing-origen');
function alternarParticipante() {
    var esMatricula = origenSel.value === 'matricula';
    document.getElementById('ing-participante').style.display = esMatricula ? 'block' : 'none';
    document.getElementById('ing-aportante').style.display    = esMatricula ? 'none'  : 'block';
    document.querySelector('#ing-participante select').required = esMatricula;
}
origenSel.addEventListener('change', alternarParticipante);
alternarParticipante();

// Devolver un préstamo obliga a decir cuál
var catSel = document.getElementById('gasto-categoria');
function alternarPrestamo() {
    var esDevolucion = catSel.value === 'devolucion_prestamo';
    document.getElementById('gasto-prestamo').style.display = esDevolucion ? 'block' : 'none';
    document.querySelector('#gasto-prestamo select').required = esDevolucion;
}
catSel.addEventListener('change', alternarPrestamo);
alternarPrestamo();

// ── Editar: se reusa el mismo modal, precargado ─────────────
function llenarForm(form, datos) {
    Object.keys(datos).forEach(function (campo) {
        var el = form.elements[campo];
        if (!el || el.type === 'file' || campo === 'id') return;
        var v = datos[campo];
        el.value = (v === null || v === undefined) ? '' : v;
    });
}

function modoIngreso(datos) {
    var form = document.getElementById('form-ingreso');
    var esEdicion = !!datos;
    form.action = esEdicion ? '/admin/finanzas/ingreso/editar' : '/admin/finanzas/ingreso';
    document.getElementById('titulo-ingreso').textContent = esEdicion ? 'Editar ingreso' : 'Registrar ingreso';
    document.getElementById('btn-ingreso').textContent    = esEdicion ? 'Guardar cambios' : 'Guardar ingreso';
    if (esEdicion) {
        llenarForm(form, datos);
        document.getElementById('ing-id').value = datos.id;
    } else {
        form.reset();
        document.getElementById('ing-id').value = '';
    }
    alternarParticipante();
    document.getElementById('modal-ingreso').classList.add('abierto');
}

function modoGasto(datos) {
    var form = document.getElementById('form-gasto');
    var esEdicion = !!datos;
    form.action = esEdicion ? '/admin/finanzas/gasto/editar' : '/admin/finanzas/gasto';
    document.getElementById('titulo-gasto').textContent = esEdicion ? 'Editar gasto' : 'Registrar gasto';
    document.getElementById('btn-gasto').textContent    = esEdicion ? 'Guardar cambios' : 'Guardar gasto';
    if (esEdicion) {
        llenarForm(form, datos);
        document.getElementById('gasto-id').value = datos.id;
    } else {
        form.reset();
        document.getElementById('gasto-id').value = '';
    }
    alternarPrestamo();
    document.getElementById('modal-gasto').classList.add('abierto');
}

document.querySelectorAll('[data-editar-ingreso]').forEach(function (b) {
    b.addEventListener('click', function () {
        modoIngreso(JSON.parse(b.dataset.editarIngreso));
    });
});
document.querySelectorAll('[data-editar-gasto]').forEach(function (b) {
    b.addEventListener('click', function () {
        modoGasto(JSON.parse(b.dataset.editarGasto));
    });
});
</script>
