<?php // Campos de una persona alcanzada. Lo usan el panel admin y el acceso con PIN. ?>
<div class="form-grid-2">
    <div class="form-grupo">
        <label>Nombre <span class="req">*</span></label>
        <input type="text" name="nombres" maxlength="100" required autocomplete="off">
    </div>
    <div class="form-grupo">
        <label>Apellido <span class="req">*</span></label>
        <input type="text" name="apellidos" maxlength="100" required autocomplete="off">
    </div>
</div>

<div class="form-grid-2">
    <div class="form-grupo">
        <label>Edad</label>
        <input type="number" name="edad" min="0" max="120" inputmode="numeric">
    </div>
    <div class="form-grupo">
        <label>Teléfono</label>
        <input type="tel" name="telefono" maxlength="30" placeholder="0414-1234567" autocomplete="off">
    </div>
</div>

<div class="form-grupo">
    <label>Dirección</label>
    <input type="text" name="direccion" maxlength="255" placeholder="Sector, calle, casa o punto de referencia" autocomplete="off">
</div>

<div class="form-grid-2">
    <div class="form-grupo">
        <label>Sede</label>
        <select name="sede_id">
            <option value="">Sin sede</option>
            <?php foreach ([1 => 'Itinerario del ciclo', 0 => 'Otros lugares'] as $activa => $grupo): ?>
            <?php $del_grupo = array_filter($sedes, fn($s) => (int) $s['activa'] === $activa); ?>
            <?php if (!$del_grupo) continue; ?>
            <optgroup label="<?= $grupo ?>">
                <?php foreach ($del_grupo as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (int) $s['id'] === (int) $sede_actual ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </optgroup>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>Fecha del contacto</label>
        <input type="date" name="fecha_contacto" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
    </div>
</div>

<div class="evg-etapas">
    <label class="check-label">
        <input type="checkbox" name="decision_fe" value="1"
               onchange="if (!this.checked) this.form.discipulado.checked = false">
        <span><strong>Tomó decisión de fe</strong></span>
    </label>
    <label class="check-label">
        <input type="checkbox" name="discipulado" value="1"
               onchange="if (this.checked) this.form.decision_fe.checked = true">
        <span><strong>Está en discipulado</strong></span>
    </label>
</div>

<style>
.evg-etapas {
    display: flex; flex-direction: column; gap: 0.75rem;
    padding: 1rem; margin-bottom: 1.25rem;
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radio);
}
</style>
