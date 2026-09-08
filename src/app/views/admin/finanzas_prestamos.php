<?php $titulo = 'Préstamos'; ?>

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
                <h1>Préstamos</h1>
                <p>Dinero que entró a caja pero hay que devolver</p>
            </div>
            <button class="btn btn--sm btn--primario" data-modal="modal-prestamo">
                <i class="fas fa-plus"></i> Registrar préstamo
            </button>
        </div>

        <?php include __DIR__ . '/../partials/finanzas_nav.php'; ?>

        <div class="fin-grid">
            <div class="fin-kpi <?= $saldo > 0 ? 'fin-kpi--aviso' : 'fin-kpi--ok' ?>">
                <span class="fin-kpi__label">Pendiente por devolver</span>
                <span class="fin-kpi__num">$<?= number_format($saldo, 2) ?></span>
                <span class="fin-kpi__pie">Suma de los préstamos activos</span>
            </div>
        </div>

        <section class="admin-card">
            <div class="admin-card__head">
                <h2><i class="fas fa-hand-holding-dollar"></i> Préstamos registrados</h2>
            </div>

            <?php if (empty($prestamos)): ?>
            <p class="admin-empty">
                <i class="fas fa-inbox"></i>
                No hay préstamos registrados.
            </p>
            <?php else: ?>
            <div class="tabla-responsive">
            <table class="admin-tabla">
                <thead>
                    <tr>
                        <th>Prestamista</th>
                        <th>Para qué</th>
                        <th>Fondo</th>
                        <th>Monto</th>
                        <th>Devuelto</th>
                        <th>Falta</th>
                        <th>Recibido</th>
                        <th>Compromiso</th>
                        <th>Estatus</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($prestamos as $p): ?>
                <?php
                    $devuelto = (float) $p['devuelto_usd'];
                    $falta    = round((float) $p['monto_usd'] - $devuelto, 2);
                    $vencido  = $p['estatus'] === 'activo'
                                && !empty($p['fecha_compromiso'])
                                && $p['fecha_compromiso'] < date('Y-m-d');
                ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($p['prestamista']) ?></strong>
                        <?php if (!empty($p['telefono'])): ?>
                        <br><small class="texto-muted"><?= htmlspecialchars($p['telefono']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($p['concepto']) ?>
                        <?php if (!empty($p['notas'])): ?>
                        <br><small class="texto-muted"><?= htmlspecialchars($p['notas']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td class="texto-muted"><?= htmlspecialchars($p['fondo_nombre'] ?? '—') ?></td>
                    <td><strong>$<?= number_format($p['monto_usd'], 2) ?></strong></td>
                    <td class="texto-verde">$<?= number_format($devuelto, 2) ?></td>
                    <td class="<?= $falta > 0 ? 'texto-rojo' : 'texto-verde' ?>">$<?= number_format(max($falta, 0), 2) ?></td>
                    <td style="white-space:nowrap"><?= date('d/m/Y', strtotime($p['fecha_prestamo'])) ?></td>
                    <td style="white-space:nowrap" class="<?= $vencido ? 'texto-rojo' : 'texto-muted' ?>">
                        <?= $p['fecha_compromiso'] ? date('d/m/Y', strtotime($p['fecha_compromiso'])) : '—' ?>
                        <?php if ($vencido): ?><br><small>vencido</small><?php endif; ?>
                    </td>
                    <td>
                        <?php
                            $clase = match ($p['estatus']) {
                                'pagado'    => 'badge--success',
                                'condonado' => 'badge--info',
                                default     => 'badge--warning',
                            };
                        ?>
                        <span class="badge <?= $clase ?>"><?= ucfirst($p['estatus']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <p class="ayuda" style="padding:0 1.25rem 1.25rem">
                Para abonar a un préstamo, registra un gasto con el rubro
                <strong>Devolución de préstamo</strong> desde Ingresos y gastos.
                El saldo se actualiza solo y el préstamo se marca pagado al completarse.
            </p>
            <?php endif; ?>
        </section>

    </main>
</div>

<!-- Modal: Registrar préstamo ──────────────────────────────── -->
<div class="modal" id="modal-prestamo" style="display:none">
    <div class="modal__overlay" onclick="cerrarModal('modal-prestamo')"></div>
    <div class="modal__box" style="max-width:600px">
        <div class="modal__head">
            <h3><i class="fas fa-hand-holding-dollar"></i> Registrar préstamo</h3>
            <button class="modal__cerrar" onclick="cerrarModal('modal-prestamo')">&times;</button>
        </div>
        <form method="POST" action="/admin/finanzas/prestamo">
            <?= csrf_field() ?>
            <div class="modal__body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Quién prestó <span class="req">*</span></label>
                        <input type="text" name="prestamista" class="form-control" required maxlength="150">
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control" maxlength="30">
                    </div>
                </div>

                <div class="form-group">
                    <label>Para qué <span class="req">*</span></label>
                    <input type="text" name="concepto" class="form-control" required maxlength="200"
                           placeholder="Ej: Compra de franelas de los participantes">
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
                        <label>Fecha del préstamo <span class="req">*</span></label>
                        <input type="date" name="fecha_prestamo" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de devolución</label>
                        <input type="date" name="fecha_compromiso" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label>Referencia</label>
                    <input type="text" name="referencia" class="form-control" maxlength="100">
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <textarea name="notas" class="form-control" rows="2" maxlength="500"></textarea>
                </div>

                <p class="ayuda">
                    Al guardar, el monto entra como ingreso a caja y queda registrado como deuda.
                    El disponible real del programa lo descuenta automáticamente.
                </p>
            </div>
            <div class="modal__foot">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-prestamo')">Cancelar</button>
                <button type="submit" class="btn btn--primario"><i class="fas fa-save"></i> Registrar</button>
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
</script>
