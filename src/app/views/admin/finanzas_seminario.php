<?php $titulo = 'Seminario (STBV)'; ?>

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
                <h1>Seminario (STBV)</h1>
                <p>Lo que hay que pagarle al Seminario por cada participante</p>
            </div>
            <div class="fin-acciones">
                <a href="/admin/finanzas/exportar?tipo=seminario" class="btn btn--sm btn--outline">
                    <i class="fas fa-file-csv"></i> Exportar
                </a>
                <form method="POST" action="/admin/finanzas/seminario/generar" style="display:inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--sm btn--outline">
                        <i class="fas fa-wand-magic-sparkles"></i> Generar cargos
                    </button>
                </form>
            </div>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <div class="fin-kpis">
            <div class="kpi-card kpi-card--total">
                <div class="kpi-card__icono"><i class="fas fa-users"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $totales['participantes'] ?></span>
                    <span class="kpi-card__label">Con cargos cargados</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--revision">
                <div class="kpi-card__icono"><i class="fas fa-graduation-cap"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['total'], 2) ?></span>
                    <span class="kpi-card__label">Total al Seminario</span>
                    <span class="kpi-card__pie">Inscripción, carnet, ternanet y mensualidades</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--aprobada">
                <div class="kpi-card__icono"><i class="fas fa-circle-check"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['pagado'], 2) ?></span>
                    <span class="kpi-card__label">Pagado</span>
                    <span class="kpi-card__pie"><?= $totales['porcentaje'] ?>% del total</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--neutro">
                <div class="kpi-card__icono"><i class="fas fa-hourglass-half"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num">$<?= number_format($totales['pendiente'], 2) ?></span>
                    <span class="kpi-card__label">Por pagar</span>
                </div>
            </div>
        </div>

        <!-- ── Estado por participante ──────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-users"></i> Estado por participante</h2>
            </div>

            <?php if (empty($resumen)): ?>
            <p class="fin-vacio">
                <i class="fas fa-user-slash"></i>
                No hay participantes aprobados todavía.
            </p>
            <?php else: ?>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Participante</th>
                        <th class="num">Cargos</th>
                        <th class="num">Total</th>
                        <th class="num">Pagado</th>
                        <th class="num">Por pagar</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resumen as $r): ?>
                <?php
                    $total = (float) $r['total_usd'];
                    $pct   = $total > 0 ? min(round((float) $r['pagado_usd'] / $total * 100), 100) : 0;
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($r['nombre']) ?></strong><br>
                        <small class="texto-muted"><?= htmlspecialchars($r['cedula']) ?></small>
                    </td>
                    <td class="num"><?= $r['cargos'] ?></td>
                    <td class="num"><strong>$<?= number_format($total, 2) ?></strong></td>
                    <td class="num texto-verde">
                        $<?= number_format($r['pagado_usd'], 2) ?>
                        <div class="fin-barra" style="width:80px"><div class="fin-barra__fill" style="width:<?= $pct ?>%"></div></div>
                    </td>
                    <td class="num">
                        <?php if ((float) $r['pendiente_usd'] > 0): ?>
                        <span class="texto-rojo">$<?= number_format($r['pendiente_usd'], 2) ?></span>
                        <?php else: ?>
                        <span class="texto-verde"><i class="fas fa-check"></i> Al día</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap">
                        <button class="btn btn--xs btn--outline" title="Ver cargos" onclick="verCargos(<?= $r['aspirante_id'] ?>)">
                            <i class="fas fa-list"></i>
                        </button>
                        <button class="btn btn--xs btn--outline" title="Agregar cargo"
                                onclick="nuevoCargo(<?= $r['aspirante_id'] ?>, '<?= htmlspecialchars(addslashes($r['nombre']), ENT_QUOTES) ?>')">
                            <i class="fas fa-plus"></i>
                        </button>
                    </td>
                </tr>
                <tr id="cargos-<?= $r['aspirante_id'] ?>" style="display:none">
                    <td colspan="6" style="background:#f8fafc;padding:0">
                        <?php if (empty($cargos[$r['aspirante_id']])): ?>
                        <p class="texto-muted" style="padding:.75rem">
                            Sin cargos. Usa «Generar cargos» para cargarle los del ciclo.
                        </p>
                        <?php else: ?>
                        <table class="tabla" style="margin:.5rem 0">
                            <thead>
                                <tr>
                                    <th>Trim.</th><th>Concepto</th><th class="num">Monto</th>
                                    <th>Estatus</th><th>Pagado el</th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($cargos[$r['aspirante_id']] as $c): ?>
                            <tr>
                                <td><?= $c['trimestre'] ?></td>
                                <td>
                                    <?= htmlspecialchars($c['descripcion']) ?><br>
                                    <small class="texto-muted"><?= $conceptos[$c['concepto']] ?? $c['concepto'] ?></small>
                                </td>
                                <td class="num">$<?= number_format($c['monto_usd'], 2) ?></td>
                                <td>
                                    <?php
                                        $clase = match ($c['estatus']) {
                                            'pagado'    => 'badge--exito',
                                            'exonerado' => 'badge--info',
                                            default     => 'badge--neutro',
                                        };
                                    ?>
                                    <span class="badge <?= $clase ?>"><?= ucfirst($c['estatus']) ?></span>
                                </td>
                                <td class="fecha texto-muted">
                                    <?= $c['fecha_pago'] ? date('d/m/Y', strtotime($c['fecha_pago'])) : '—' ?>
                                </td>
                                <td style="white-space:nowrap">
                                    <?php if ($c['estatus'] !== 'pagado'): ?>
                                    <button class="btn btn--xs btn--outline" title="Editar"
                                            onclick="editarCargo(<?= htmlspecialchars(json_encode([
                                                'id'          => (int) $c['id'],
                                                'trimestre'   => (int) $c['trimestre'],
                                                'concepto'    => $c['concepto'],
                                                'descripcion' => $c['descripcion'],
                                                'monto'       => (float) $c['monto_usd'],
                                                'estatus'     => $c['estatus'],
                                                'nombre'      => $r['nombre'],
                                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>)">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <button class="btn btn--xs btn--outline" title="Eliminar"
                                            onclick="eliminarCargo(<?= (int) $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['descripcion']), ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
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

        <!-- ── Pagarle al Seminario ─────────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-money-bill-transfer"></i> Pagarle al Seminario</h2>
            </div>

            <?php if (empty($pendientes)): ?>
            <p class="fin-vacio">
                <i class="fas fa-check-double"></i>
                No queda nada pendiente con el Seminario.
            </p>
            <?php else: ?>
            <form method="POST" action="/admin/finanzas/seminario/pago" enctype="multipart/form-data" id="form-pago-stbv">
                <?= csrf_field() ?>
                <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th style="width:2rem"><input type="checkbox" onclick="marcarTodos(this)"></th>
                            <th>Participante</th>
                            <th>Trim.</th>
                            <th>Concepto</th>
                            <th class="num">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($pendientes as $c): ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="cargos[]" value="<?= $c['id'] ?>" class="chk-cargo"
                                   data-monto="<?= (float) $c['monto_usd'] ?>" onchange="actualizarTotal()">
                        </td>
                        <td>
                            <?= htmlspecialchars($c['nombre']) ?><br>
                            <small class="texto-muted"><?= htmlspecialchars($c['cedula']) ?></small>
                        </td>
                        <td><?= $c['trimestre'] ?></td>
                        <td><?= htmlspecialchars($c['descripcion']) ?></td>
                        <td class="num">$<?= number_format($c['monto_usd'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <div class="fin-barra-accion">
                    <span id="resumen-cargos" class="texto-muted">Ningún cargo marcado</span>
                    <button type="button" class="btn btn--sm btn--verde" onclick="abrirPago()">
                        <i class="fas fa-money-bill-transfer"></i> Registrar pago
                    </button>
                </div>

                <div class="modal-overlay" id="modal-pago">
                    <div class="modal modal--ancho">
                        <div class="modal__header">
                            <h3><i class="fas fa-money-bill-transfer"></i> Pago al Seminario</h3>
                            <button type="button" class="modal__cerrar" onclick="cerrarModal('modal-pago')">&times;</button>
                        </div>
                        <div class="modal__body modal__body--scroll">
                            <div class="fin-dato">
                                <span class="fin-dato__label">Cargos marcados</span>
                                <span class="fin-dato__valor" id="pago-resumen"></span>
                            </div>

                            <div class="form-grid-2">
                                <div class="form-grupo">
                                    <label>Fecha <span class="req">*</span></label>
                                    <input type="date" name="fecha_gasto" value="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div class="form-grupo">
                                    <label>Rubro <span class="req">*</span></label>
                                    <select name="categoria" required>
                                        <option value="seminario_mensualidad">Seminario — mensualidad</option>
                                        <option value="seminario_inscripcion">Seminario — inscripción</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-grupo">
                                <label>Concepto</label>
                                <input type="text" name="concepto" maxlength="200"
                                       placeholder="Pago al Seminario — se arma solo si lo dejas vacío">
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

                            <div class="form-grid-3">
                                <div class="form-grupo">
                                    <label>Monto USD</label>
                                    <input type="number" name="monto_usd" id="pago-usd" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="form-grupo">
                                    <label>Monto Bs</label>
                                    <input type="number" name="monto_ves" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="form-grupo">
                                    <label>Tasa</label>
                                    <input type="number" name="tasa_cambio" step="0.0001" min="0"
                                           placeholder="<?= $ultima_tasa ? number_format($ultima_tasa, 2, '.', '') : 'Bs por USD' ?>">
                                </div>
                            </div>
                            <span class="fin-nota">
                                Si lo dejas vacío se guarda por la suma de los cargos marcados. El STBV cobra a la
                                tasa del BCV del día, así que si pagaste en bolívares pon el monto en Bs y la tasa.
                            </span>

                            <div class="form-grid-3" style="margin-top:1rem">
                                <div class="form-grupo">
                                    <label>Método <span class="req">*</span></label>
                                    <select name="metodo_pago" required>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="pago_movil">Pago Móvil</option>
                                        <option value="zelle">Zelle</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div class="form-grupo">
                                    <label>Referencia</label>
                                    <input type="text" name="referencia" maxlength="100">
                                </div>
                                <div class="form-grupo">
                                    <label>Beneficiario</label>
                                    <input type="text" name="beneficiario" maxlength="150"
                                           value="Seminario Teológico Bautista de Venezuela">
                                </div>
                            </div>

                            <div class="form-grupo">
                                <label>Comprobante (JPG/PNG/PDF, máx 5 MB)</label>
                                <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf">
                            </div>
                            <div class="form-grupo">
                                <label>Notas</label>
                                <textarea name="notas" rows="2" maxlength="500"
                                          placeholder="Recuerda reportar la transferencia a directorstbv2026@gmail.com"></textarea>
                            </div>
                        </div>
                        <div class="modal__footer">
                            <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-pago')">Cancelar</button>
                            <button type="submit" class="btn btn--verde"><i class="fas fa-save"></i> Guardar pago</button>
                        </div>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </section>

    </main>
</div>

<!-- Modal: cargo ───────────────────────────────────────────── -->
<div class="modal-overlay" id="modal-cargo">
    <div class="modal">
        <div class="modal__header">
            <h3><i class="fas fa-receipt"></i> <span id="cargo-titulo">Cargo</span></h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-cargo')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/seminario/cargo">
            <?= csrf_field() ?>
            <input type="hidden" name="accion"       id="cargo-accion" value="agregar">
            <input type="hidden" name="id"           id="cargo-id">
            <input type="hidden" name="aspirante_id" id="cargo-aspirante">
            <div class="modal__body">
                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Trimestre <span class="req">*</span></label>
                        <input type="number" name="trimestre" id="cargo-trimestre" min="1" max="4" value="1" required>
                    </div>
                    <div class="form-grupo">
                        <label>Concepto <span class="req">*</span></label>
                        <select name="concepto" id="cargo-concepto" required>
                            <?php foreach ($conceptos as $k => $v): ?>
                            <option value="<?= $k ?>"><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-grupo">
                    <label>Descripción <span class="req">*</span></label>
                    <input type="text" name="descripcion" id="cargo-descripcion" maxlength="120" required
                           placeholder="Ej: Mensualidad abril">
                </div>
                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Monto USD <span class="req">*</span></label>
                        <input type="number" name="monto_usd" id="cargo-monto" step="0.01" min="0" required>
                    </div>
                    <div class="form-grupo">
                        <label>Estatus</label>
                        <select name="estatus" id="cargo-estatus">
                            <option value="pendiente">Pendiente</option>
                            <option value="exonerado">Exonerado — no se le cobra</option>
                        </select>
                    </div>
                </div>
                <div class="form-grupo">
                    <label>Nota</label>
                    <input type="text" name="notas" maxlength="255">
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-cargo')">Cancelar</button>
                <button type="submit" class="btn btn--primario"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="/admin/finanzas/seminario/cargo" id="form-eliminar-cargo" style="display:none">
    <?= csrf_field() ?>
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" id="eliminar-cargo-id">
</form>

<style>
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

function verCargos(id) {
    var fila = document.getElementById('cargos-' + id);
    fila.style.display = fila.style.display === 'none' ? 'table-row' : 'none';
}

function marcarTodos(maestro) {
    document.querySelectorAll('.chk-cargo').forEach(function (c) { c.checked = maestro.checked; });
    actualizarTotal();
}
function actualizarTotal() {
    var marcados = Array.prototype.slice.call(document.querySelectorAll('.chk-cargo:checked'));
    var total = 0;
    marcados.forEach(function (c) { total += parseFloat(c.dataset.monto || 0); });
    var texto = marcados.length === 0
        ? 'Ningún cargo marcado'
        : marcados.length + (marcados.length === 1 ? ' cargo · $' : ' cargos · $') + total.toFixed(2);
    document.getElementById('resumen-cargos').textContent = texto;
    return { texto: texto, total: total, cantidad: marcados.length };
}
function abrirPago() {
    var sel = actualizarTotal();
    if (sel.cantidad === 0) {
        alert('Marca los cargos que cubre el pago.');
        return;
    }
    document.getElementById('pago-resumen').textContent = sel.texto;
    document.getElementById('pago-usd').placeholder = sel.total.toFixed(2);
    document.getElementById('modal-pago').classList.add('abierto');
}

function nuevoCargo(aspiranteId, nombre) {
    document.getElementById('cargo-accion').value      = 'agregar';
    document.getElementById('cargo-id').value          = '';
    document.getElementById('cargo-aspirante').value   = aspiranteId;
    document.getElementById('cargo-titulo').textContent = 'Nuevo cargo de ' + nombre;
    document.getElementById('cargo-trimestre').value   = 1;
    document.getElementById('cargo-concepto').value    = 'mensualidad';
    document.getElementById('cargo-descripcion').value = '';
    document.getElementById('cargo-monto').value       = '25.00';
    document.getElementById('cargo-estatus').value     = 'pendiente';
    document.getElementById('modal-cargo').classList.add('abierto');
}
function editarCargo(c) {
    document.getElementById('cargo-accion').value      = 'editar';
    document.getElementById('cargo-id').value          = c.id;
    document.getElementById('cargo-aspirante').value   = '';
    document.getElementById('cargo-titulo').textContent = 'Cargo de ' + c.nombre;
    document.getElementById('cargo-trimestre').value   = c.trimestre;
    document.getElementById('cargo-concepto').value    = c.concepto;
    document.getElementById('cargo-descripcion').value = c.descripcion;
    document.getElementById('cargo-monto').value       = c.monto;
    document.getElementById('cargo-estatus').value     = c.estatus;
    document.getElementById('modal-cargo').classList.add('abierto');
}
function eliminarCargo(id, descripcion) {
    if (!confirm('¿Eliminar el cargo "' + descripcion + '"?')) return;
    document.getElementById('eliminar-cargo-id').value = id;
    document.getElementById('form-eliminar-cargo').submit();
}
</script>
