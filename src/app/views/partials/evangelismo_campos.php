<?php // Campos de una persona alcanzada. Lo usan el panel admin y el acceso con PIN. ?>
<div class="evg-tipo">
    <label class="evg-tipo__opcion">
        <input type="radio" name="tipo" value="evangelizada" checked onchange="evgTipo(this.form)">
        <span>
            <strong><i class="fas fa-bullhorn"></i> Evangelizado</strong>
            <small>Se le predicó el evangelio</small>
        </span>
    </label>
    <label class="evg-tipo__opcion">
        <input type="radio" name="tipo" value="contacto" onchange="evgTipo(this.form)">
        <span>
            <strong><i class="fas fa-hands-praying"></i> Contacto espiritual</strong>
            <small>Oración, consejo o aliento, sin llegar a predicarle</small>
        </span>
    </label>
</div>

<div class="form-grid-2">
    <div class="form-grupo">
        <label>Nombre <span class="req">*</span></label>
        <input type="text" name="nombres" maxlength="100" required autocomplete="off">
    </div>
    <div class="form-grupo">
        <label>Apellido <span class="req evg-solo-evangelizada">*</span></label>
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

<div class="evg-etapas evg-solo-evangelizada">
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

<div class="evg-etapas evg-solo-contacto">
    <span class="evg-etapas__titulo">¿Qué se hizo? <span class="req">*</span></span>
    <?php foreach (EvangelismoModel::ACOMPANAMIENTOS as $valor => $etiqueta): ?>
    <label class="check-label">
        <input type="checkbox" name="acompanamiento[]" value="<?= $valor ?>">
        <span><strong><?= $etiqueta ?></strong></span>
    </label>
    <?php endforeach; ?>
</div>

<script>
// Muestra lo que aplica a cada tipo: las etapas de fe solo para quien
// escuchó el evangelio; oración, consejo o aliento para el contacto.
function evgTipo(form) {
    var contacto = form.elements.tipo.value === 'contacto';
    form.querySelectorAll('.evg-solo-evangelizada').forEach(function (el) { el.hidden = contacto; });
    form.querySelectorAll('.evg-solo-contacto').forEach(function (el) { el.hidden = !contacto; });
    form.elements.apellidos.required = !contacto;
}
document.querySelectorAll('form').forEach(function (f) {
    if (f.elements.tipo) evgTipo(f);
});
</script>

<style>
.evg-etapas {
    display: flex; flex-direction: column; gap: 0.75rem;
    padding: 1rem; margin-bottom: 1.25rem;
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radio);
}
.evg-etapas[hidden], .evg-solo-evangelizada[hidden] { display: none; }
.evg-etapas__titulo { font-size: 0.85rem; font-weight: 600; color: var(--gris-dark); }
.evg-tipo { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.25rem; }
.evg-tipo__opcion {
    display: flex; gap: 0.6rem; align-items: flex-start; cursor: pointer;
    padding: 0.85rem; border: 2px solid #e5e7eb; border-radius: var(--radio);
    transition: border-color 0.2s, background 0.2s;
}
.evg-tipo__opcion:has(input:checked) { border-color: var(--verde); background: #f0fdf4; }
.evg-tipo__opcion input { margin-top: 3px; accent-color: var(--verde); }
.evg-tipo__opcion strong { display: block; font-size: 0.88rem; color: var(--gris-dark); }
.evg-tipo__opcion strong i { color: var(--verde); margin-right: 0.2rem; }
.evg-tipo__opcion small { display: block; font-size: 0.75rem; color: var(--gris); line-height: 1.35; margin-top: 0.15rem; }
@media (max-width: 480px) {
    .evg-tipo { grid-template-columns: 1fr; }
}
</style>
