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
                <button class="btn btn--sm btn--success" data-modal="modal-ingreso">
                    <i class="fas fa-arrow-down"></i> Registrar ingreso
                </button>
                <button class="btn btn--sm btn--danger" data-modal="modal-gasto">
                    <i class="fas fa-arrow-up"></i> Registrar gasto
                </button>
            </div>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <!-- Filtros ────────────────────────────────────────── -->
        <section class="admin-card" style="margin-bottom:1.5rem">
            <form method="GET" action="/admin/finanzas/movimientos" style="padding:1.25rem">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
                <div class="form-row--3">
                    <div class="form-group">
                        <label>Fondo</label>
                        <select name="fondo_id" class="form-control">
                            <option value="">Todos</option>
                            <?php foreach ($fondos as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= (string)$filtros['fondo_id'] === (string)$f['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Desde</label>
                        <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($filtros['desde']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Hasta</label>
                        <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($filtros['hasta']) ?>">
                    </div>
                </div>
                <div style="display:flex;gap:.5rem">
                    <button type="submit" class="btn btn--sm btn--primario"><i class="fas fa-filter"></i> Filtrar</button>
                    <a href="/admin/finanzas/movimientos" class="btn btn--sm btn--outline">Limpiar</a>
                </div>
            </form>
        </section>

        <!-- Ingresos ───────────────────────────────────────── -->
        <section class="admin-card" style="margin-bottom:1.5rem">
            <div class="admin-card__head">
                <h2><i class="fas fa-arrow-down texto-verde"></i> Ingresos</h2>
                <span class="badge badge--secondary">
                    $<?= number_format(array_sum(array_map(fn($i) => $i['estatus'] === 'confirmado' ? (float)$i['monto_usd'] : 0, $ingresos)), 2) ?>
                </span>
            </div>

            <?php if (empty($ingresos)): ?>
            <p class="admin-empty"><i class="fas fa-inbox"></i> Sin ingresos en este filtro.</p>
            <?php else: ?>
            <div class="tabla-responsive">
            <table class="admin-tabla">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Origen</th><th>De quién</th><th>Concepto</th>
                        <th>Fondo</th><th>Monto</th><th>Estatus</th><th>Comp.</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($ingresos as $i): ?>
                <tr>
                    <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($i['fecha'])) ?></td>
                    <td><span class="badge badge--info"><?= htmlspecialchars($origenes[$i['origen']] ?? $i['origen']) ?></span></td>
                    <td><?= htmlspecialchars($i['nombre_estudiante'] ?? $i['aportante'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($i['concepto']) ?></td>
                    <td class="texto-muted"><?= htmlspecialchars($i['fondo_nombre'] ?? '—') ?></td>
                    <td style="white-space:nowrap">
                        <strong>$<?= number_format($i['monto_usd'], 2) ?></strong>
                        <?php if (!empty($i['monto_ves'])): ?>
                        <br><small class="texto-muted">Bs <?= number_format($i['monto_ves'], 2) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($i['estatus'] === 'confirmado'): ?>
                        <span class="badge badge--success">Confirmado</span>
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
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </section>

        <!-- Gastos ─────────────────────────────────────────── -->
        <section class="admin-card">
            <div class="admin-card__head">
                <h2><i class="fas fa-arrow-up texto-rojo"></i> Gastos</h2>
                <span class="badge badge--secondary">
                    $<?= number_format(array_sum(array_map(fn($g) => (float)$g['monto_usd'], $gastos)), 2) ?>
                </span>
            </div>

            <?php if (empty($gastos)): ?>
            <p class="admin-empty"><i class="fas fa-inbox"></i> Sin gastos en este filtro.</p>
            <?php else: ?>
            <div class="tabla-responsive">
            <table class="admin-tabla">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Concepto</th><th>Rubro</th><th>Beneficiario</th>
                        <th>Fondo</th><th>Monto</th><th>Comp.</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($gastos as $g): ?>
                <tr>
                    <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($g['fecha_gasto'])) ?></td>
                    <td>
                        <?= htmlspecialchars($g['concepto']) ?>
                        <?php if (!empty($g['prestamista'])): ?>
                        <br><small class="texto-muted">Devolución a <?= htmlspecialchars($g['prestamista']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge badge--secondary"><?= htmlspecialchars($categorias[$g['categoria']] ?? $g['categoria']) ?></span></td>
                    <td><?= htmlspecialchars($g['beneficiario'] ?? '—') ?></td>
                    <td class="texto-muted"><?= htmlspecialchars($g['fondo_nombre'] ?? '—') ?></td>
                    <td style="white-space:nowrap">
                        <strong>$<?= number_format($g['monto_usd'], 2) ?></strong>
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
<div class="modal" id="modal-ingreso" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-ingreso')"></div>
    <div class="modal__box" style="max-width:620px">
        <div class="modal__head">
            <h3><i class="fas fa-arrow-down"></i> Registrar ingreso</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-ingreso')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/ingreso" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Origen <span class="req">*</span></label>
                        <select name="origen" id="ing-origen" class="form-control" required>
                            <?php foreach ($origenes as $k => $v): ?>
                            <?php if ($k === 'prestamo') continue; ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="ayuda">Los préstamos se registran en su propia sección.</span>
                    </div>
                    <div class="form-group">
                        <label>Fecha <span class="req">*</span></label>
                        <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group" id="ing-participante" style="display:none">
                    <label>Participante <span class="req">*</span></label>
                    <select name="aspirante_id" class="form-control">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($aprobados as $a): ?>
                        <option value="<?= $a['id'] ?>">
                            <?= htmlspecialchars($a['nombres'] . ' ' . $a['apellidos']) ?> — <?= htmlspecialchars($a['cedula']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group" id="ing-aportante">
                    <label>Quién aportó</label>
                    <input type="text" name="aportante" class="form-control" maxlength="150"
                           placeholder="Nombre de la persona, iglesia u organización">
                </div>

                <div class="form-group">
                    <label>Concepto <span class="req">*</span></label>
                    <input type="text" name="concepto" class="form-control" required maxlength="200"
                           placeholder="Ej: Aporte para franelas de los participantes">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Fondo</label>
                        <select name="fondo_id" class="form-control">
                            <option value="">Sin etiquetar</option>
                            <?php foreach ($fondos as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Entró a</label>
                        <select name="cuenta_id" class="form-control">
                            <option value="">Sin especificar</option>
                            <?php foreach ($cuentas as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row--3">
                    <div class="form-group">
                        <label>Monto USD</label>
                        <input type="number" name="monto_usd" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Monto Bs</label>
                        <input type="number" name="monto_ves" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Tasa</label>
                        <input type="number" name="tasa_cambio" class="form-control" step="0.0001" min="0" placeholder="Bs por USD">
                    </div>
                </div>
                <span class="ayuda" style="margin-bottom:1rem">Si el pago fue en bolívares, llena monto en Bs y la tasa: el sistema calcula el equivalente en dólares.</span>

                <div class="form-row--3">
                    <div class="form-group">
                        <label>Método <span class="req">*</span></label>
                        <select name="metodo_pago" class="form-control" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="zelle">Zelle</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Banco</label>
                        <input type="text" name="banco_origen" class="form-control" maxlength="100">
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
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-ingreso')">Cancelar</button>
                <button type="submit" class="btn btn--success"><i class="fas fa-save"></i> Guardar ingreso</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Registrar gasto ─────────────────────────────────── -->
<div class="modal" id="modal-gasto" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-gasto')"></div>
    <div class="modal__box" style="max-width:620px">
        <div class="modal__head">
            <h3><i class="fas fa-arrow-up"></i> Registrar gasto</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-gasto')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/gasto" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal__body">
                <div class="form-group">
                    <label>Concepto <span class="req">*</span></label>
                    <input type="text" name="concepto" class="form-control" required maxlength="200"
                           placeholder="Ej: Compra de 12 franelas">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Rubro <span class="req">*</span></label>
                        <select name="categoria" id="gasto-categoria" class="form-control" required>
                            <?php foreach ($categorias as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fecha <span class="req">*</span></label>
                        <input type="date" name="fecha_gasto" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="form-group" id="gasto-prestamo" style="display:none">
                    <label>¿Qué préstamo estás devolviendo? <span class="req">*</span></label>
                    <select name="prestamo_id" class="form-control">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($prestamos as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['prestamista']) ?> — <?= htmlspecialchars($p['concepto']) ?>
                            (queda $<?= number_format((float)$p['monto_usd'] - (float)$p['devuelto_usd'], 2) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Fondo</label>
                        <select name="fondo_id" class="form-control">
                            <option value="">Sin etiquetar</option>
                            <?php foreach ($fondos as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Salió de</label>
                        <select name="cuenta_id" class="form-control">
                            <option value="">Sin especificar</option>
                            <?php foreach ($cuentas as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>A quién se le pagó</label>
                    <input type="text" name="beneficiario" class="form-control" maxlength="150"
                           placeholder="Ej: Seminario STBV, señora María (cocina), transportista">
                </div>

                <div class="form-row--3">
                    <div class="form-group">
                        <label>Monto USD</label>
                        <input type="number" name="monto_usd" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Monto Bs</label>
                        <input type="number" name="monto_ves" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label>Tasa</label>
                        <input type="number" name="tasa_cambio" class="form-control" step="0.0001" min="0" placeholder="Bs por USD">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Método <span class="req">*</span></label>
                        <select name="metodo_pago" class="form-control" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="zelle">Zelle</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="otro">Otro</option>
                        </select>
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
                <button type="submit" class="btn btn--danger"><i class="fas fa-save"></i> Guardar gasto</button>
            </div>
        </form>
    </div>
</div>

<script>
function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}
document.querySelectorAll('[data-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById(btn.dataset.modal).style.display = 'flex';
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
</script>
