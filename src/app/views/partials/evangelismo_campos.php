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
            <?php foreach ($sedes as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (int) $s['id'] === (int) $sede_actual ? 'selected' : '' ?>>
                <?= htmlspecialchars($s['nombre']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-grupo">
        <label>Ciudad o sector</label>
        <select name="sector_id" class="evg-sector">
            <option value="">Sin sector</option>
            <?php foreach ($sectores as $sec): ?>
            <option value="<?= $sec['id'] ?>" data-sede="<?= $sec['sede_id'] ?>">
                <?= htmlspecialchars($sec['nombre']) ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="form-grupo">
    <label>Fecha del contacto</label>
    <input type="date" name="fecha_contacto" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
</div>

<div class="evg-etapas evg-solo-evangelizada">
    <span class="evg-etapas__titulo">¿Hasta dónde llegó?</span>
    <label class="check-label">
        <input type="radio" name="etapa" value="ninguna" checked>
        <span>
            <strong>Sin decisión</strong>
            <small>Escuchó el evangelio y no tomó decisión</small>
        </span>
    </label>
    <label class="check-label">
        <input type="radio" name="etapa" value="decision">
        <span><strong>Tomó decisión de fe</strong></span>
    </label>
    <label class="check-label">
        <input type="radio" name="etapa" value="discipulado">
        <span><strong>Está en discipulado</strong></span>
    </label>

    <div class="evg-lecciones" hidden>
        <?php $nivel_previo = null; foreach ($lecciones as $lec): ?>
            <?php if ($lec['nivel'] !== $nivel_previo): ?>
            <span class="evg-lecciones__nivel"><?= htmlspecialchars($lec['nivel']) ?></span>
            <?php $nivel_previo = $lec['nivel']; endif; ?>
        <label class="check-label">
            <input type="checkbox" name="lecciones[]" value="<?= $lec['id'] ?>">
            <span><?= $lec['numero'] ?>. <?= htmlspecialchars($lec['titulo']) ?></span>
        </label>
        <?php endforeach; ?>
    </div>
</div>

<label class="check-label evg-visita">
    <input type="checkbox" name="desea_visita" value="1">
    <span>
        <strong>Desea ser visitado</strong>
        <small>Pidió que lo visiten. Vale igual si tomó decisión o no</small>
    </span>
</label>

<div class="form-grupo">
    <label>Foto (opcional)</label>
    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" class="evg-foto__input">
    <small class="evg-ayuda">JPG, PNG o WEBP, hasta 5 MB. Pídele permiso a la persona antes de tomarla.</small>
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
    evgLecciones(form);
}

// Las lecciones salen solo cuando la persona está en discipulado
function evgLecciones(form) {
    var caja = form.querySelector('.evg-lecciones');
    if (!caja) return;
    caja.hidden = form.elements.tipo.value === 'contacto'
               || form.elements.etapa.value !== 'discipulado';
}

// Cada sede tiene sus sectores: se muestran solo los de la sede elegida
function evgSectores(form) {
    var sel = form.elements.sector_id;
    if (!sel) return;
    var sede = form.elements.sede_id.value;
    var vale = false;

    Array.prototype.forEach.call(sel.options, function (op) {
        if (!op.value) return;
        var suya = op.dataset.sede === sede;
        op.hidden = op.disabled = !suya;
        if (suya && op.value === sel.value) vale = true;
    });
    if (!vale) sel.value = '';
}

document.querySelectorAll('form').forEach(function (f) {
    if (!f.elements.tipo) return;
    evgTipo(f);
    evgSectores(f);
    f.querySelectorAll('[name="etapa"]').forEach(function (r) {
        r.addEventListener('change', function () { evgLecciones(f); });
    });
    f.elements.sede_id.addEventListener('change', function () { evgSectores(f); });
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
.evg-etapas .check-label small,
.evg-visita small { display: block; font-size: 0.75rem; color: var(--gris); line-height: 1.35; margin-top: 0.1rem; }
.evg-lecciones {
    display: flex; flex-direction: column; gap: 0.6rem;
    padding: 0.85rem 1rem; margin-top: 0.25rem;
    background: var(--blanco); border: 1px solid #bbf7d0; border-radius: var(--radio);
}
.evg-lecciones[hidden] { display: none; }
.evg-lecciones__nivel {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.5px; color: var(--verde);
}
.evg-lecciones .check-label { font-size: 0.84rem; align-items: center; }
.evg-visita {
    padding: 0.85rem 1rem; margin-bottom: 1.25rem;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radio);
}
.evg-visita input { accent-color: var(--dorado); }
.evg-foto__input { width: 100%; font-size: 0.85rem; }
.evg-ayuda { display: block; margin-top: 0.3rem; font-size: 0.75rem; color: var(--gris); line-height: 1.35; }
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
