<?php
/**
 * Caja: carga rápida de gastos e ingresos desde el teléfono.
 * Los estilos de aquí son solo los que app.css no cubre (selector de
 * tipo, monto grande y botones de rubro); el resto usa app.css.
 */
$hoy = date('Y-m-d');
$tasa_txt = $ultima_tasa ? rtrim(rtrim(number_format($ultima_tasa, 4, '.', ''), '0'), '.') : '';
?>
<div class="admin-layout">

    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main caja">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="caja-cifras">
            <div>
                <span>En caja</span>
                <strong><?= usd($kpis['en_caja']) ?></strong>
            </div>
            <div>
                <span>Entró esta semana</span>
                <strong class="caja-verde"><?= usd($entro) ?></strong>
            </div>
            <div>
                <span>Salió esta semana</span>
                <strong class="caja-rojo"><?= usd($salio) ?></strong>
            </div>
        </div>

        <form method="POST" action="/admin/caja" enctype="multipart/form-data" id="caja-form" class="admin-panel caja-form">
            <?= csrf_field() ?>
            <input type="hidden" name="tipo" id="caja-tipo" value="<?= $tipo ?>">
            <input type="hidden" name="monto_usd" id="caja-usd">
            <input type="hidden" name="monto_ves" id="caja-ves">
            <input type="hidden" name="metodo_pago" id="caja-metodo" value="efectivo">

            <div class="caja-switch">
                <button type="button" data-tipo="gasto" class="<?= $tipo === 'gasto' ? 'activo' : '' ?>">
                    <i class="fas fa-arrow-up"></i> Gasto
                </button>
                <button type="button" data-tipo="ingreso" class="<?= $tipo === 'ingreso' ? 'activo' : '' ?>">
                    <i class="fas fa-arrow-down"></i> Ingreso
                </button>
            </div>

            <div class="caja-monto">
                <input type="text" id="caja-monto" inputmode="decimal" autocomplete="off"
                       placeholder="0" required aria-label="Monto">
                <div class="caja-moneda">
                    <button type="button" data-moneda="USD" class="activo">$</button>
                    <button type="button" data-moneda="VES">Bs</button>
                </div>
            </div>
            <div class="caja-tasa" id="caja-tasa" hidden>
                <label for="caja-tasa-input">Tasa</label>
                <input type="text" name="tasa_cambio" id="caja-tasa-input" inputmode="decimal"
                       value="<?= $tasa_txt ?>" placeholder="Bs por dólar">
                <span id="caja-equivale"></span>
            </div>

            <p class="caja-etiqueta" data-grupo="gasto">¿En qué?</p>
            <p class="caja-etiqueta" data-grupo="ingreso">¿De qué es?</p>
            <div class="caja-chips" data-grupo="gasto">
                <?php foreach ($categorias as $clave => $nombre): ?>
                <label><input type="radio" name="categoria" value="<?= $clave ?>" required>
                    <span><?= htmlspecialchars($nombre) ?></span></label>
                <?php endforeach; ?>
            </div>
            <div class="caja-chips" data-grupo="ingreso">
                <?php foreach ($origenes as $clave => $nombre): ?>
                <label><input type="radio" name="origen" value="<?= $clave ?>" required>
                    <span><?= htmlspecialchars($nombre) ?></span></label>
                <?php endforeach; ?>
            </div>

            <?php if ($cuentas): ?>
            <p class="caja-etiqueta" data-grupo="gasto">¿De dónde salió?</p>
            <p class="caja-etiqueta" data-grupo="ingreso">¿A dónde entró?</p>
            <div class="caja-chips caja-chips--cuentas">
                <?php foreach ($cuentas as $c): ?>
                <label><input type="radio" name="cuenta_id" value="<?= (int) $c['id'] ?>"
                              data-metodo="<?= $c['tipo'] ?>" data-moneda="<?= $c['moneda'] ?>">
                    <span><?= htmlspecialchars($c['nombre']) ?></span></label>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="form-grupo">
                <input type="text" name="concepto" maxlength="200"
                       placeholder="Detalle (opcional): harina, aceite, gas…">
            </div>

            <label class="btn btn--outline btn--block caja-foto">
                <i class="fas fa-camera"></i> <span id="caja-foto-txt">Foto del recibo o capture</span>
                <input type="file" name="comprobante" id="caja-foto" accept="image/*,application/pdf" hidden>
            </label>

            <details class="caja-mas">
                <summary>Más datos</summary>
                <div class="form-grupo">
                    <label>Fecha</label>
                    <input type="date" name="fecha_gasto" value="<?= $hoy ?>" max="<?= $hoy ?>" data-grupo="gasto">
                    <input type="date" name="fecha" value="<?= $hoy ?>" max="<?= $hoy ?>" data-grupo="ingreso">
                </div>
                <?php if ($fondos): ?>
                <div class="form-grupo">
                    <label>Fondo</label>
                    <select name="fondo_id">
                        <option value="">Sin fondo específico</option>
                        <?php foreach ($fondos as $f): ?>
                        <option value="<?= (int) $f['id'] ?>"><?= htmlspecialchars($f['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-grupo" data-grupo="gasto">
                    <label>A quién se le pagó</label>
                    <input type="text" name="beneficiario" maxlength="150">
                </div>
                <div class="form-grupo" data-grupo="ingreso">
                    <label>Quién lo dio</label>
                    <input type="text" name="aportante" maxlength="150">
                </div>
                <div class="form-grupo">
                    <label>Referencia</label>
                    <input type="text" name="referencia" maxlength="100" inputmode="numeric">
                </div>
                <div class="form-grupo">
                    <label>Notas</label>
                    <textarea name="notas" rows="2"></textarea>
                </div>
            </details>

            <button type="submit" class="btn btn--primario btn--lg btn--block" id="caja-guardar">
                Guardar <?= $tipo ?>
            </button>
        </form>

        <div class="admin-panel">
            <h3 class="caja-titulo">Esta semana</h3>
            <?php if (!$semana): ?>
            <p class="caja-vacio">Nada cargado desde el lunes.</p>
            <?php else: ?>
            <ul class="caja-lista">
                <?php foreach ($semana as $m): ?>
                <?php $es_ing = $m['tipo'] === 'ingreso'; ?>
                <li class="<?= $m['cuenta'] ? '' : 'caja-no-cuenta' ?>">
                    <i class="fas fa-arrow-<?= $es_ing ? 'down caja-verde' : 'up caja-rojo' ?>"></i>
                    <div>
                        <strong><?= htmlspecialchars($m['concepto']) ?></strong>
                        <span>
                            <?= date('d/m', strtotime($m['fecha'])) ?>
                            <?php if (!$m['cuenta']): ?> · <?= htmlspecialchars($m['estatus']) ?><?php endif; ?>
                            <?php if ($m['monto_ves']): ?> · Bs <?= number_format($m['monto_ves'], 2, ',', '.') ?><?php endif; ?>
                        </span>
                    </div>
                    <b><?= ($es_ing ? '+' : '-') . usd($m['monto_usd']) ?></b>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <a href="/admin/finanzas/movimientos" class="caja-ver-todo">Ver todo en Finanzas <i class="fas fa-arrow-right"></i></a>
        </div>

    </main>
</div>

<style>
.caja { max-width: 560px; }
.caja [hidden] { display: none !important; }
.caja-verde { color: var(--verde); }
.caja-rojo  { color: #b91c1c; }

.caja-cifras {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: .5rem; margin-bottom: 1rem;
}
.caja-cifras div {
    background: var(--blanco); border-radius: 10px; padding: .6rem .7rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.caja-cifras span   { display: block; font-size: .7rem; color: var(--gris); }
.caja-cifras strong { font-size: 1.05rem; }

.caja-switch {
    display: grid; grid-template-columns: 1fr 1fr; gap: .25rem;
    background: var(--gris-claro); border-radius: 10px; padding: .25rem; margin-bottom: 1rem;
}
.caja-switch button, .caja-moneda button {
    border: 0; background: transparent; padding: .65rem; border-radius: 8px;
    font: inherit; font-weight: 700; color: var(--gris); cursor: pointer;
}
.caja-switch button.activo { background: var(--blanco); color: var(--negro); box-shadow: 0 1px 3px rgba(0,0,0,.1); }

.caja-monto { display: flex; gap: .5rem; align-items: stretch; }
.caja-monto input {
    flex: 1; min-width: 0; font-size: 2.2rem; font-weight: 800; padding: .4rem .7rem;
    border: 2px solid #e5e7eb; border-radius: 10px; font-family: inherit;
}
.caja-monto input:focus { outline: none; border-color: var(--verde); }
.caja-moneda { display: flex; flex-direction: column; gap: .25rem; }
.caja-moneda button { padding: .35rem .9rem; background: var(--gris-claro); }
.caja-moneda button.activo { background: var(--verde); color: var(--blanco); }

.caja-tasa { display: flex; align-items: center; gap: .5rem; margin-top: .5rem; font-size: .85rem; }
.caja-tasa input { width: 7rem; padding: .4rem .5rem; border: 1px solid #e5e7eb; border-radius: 6px; font: inherit; }
.caja-tasa span  { color: var(--gris); }

.caja-etiqueta { font-size: .8rem; font-weight: 700; color: var(--gris-dark); margin: 1rem 0 .4rem; }
.caja-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
.caja-chips label { cursor: pointer; }
.caja-chips input { position: absolute; opacity: 0; pointer-events: none; }
.caja-chips span {
    display: inline-block; padding: .45rem .75rem; border-radius: 999px; font-size: .85rem;
    border: 1px solid #d1d5db; background: var(--blanco);
}
.caja-chips input:checked + span { background: var(--verde); border-color: var(--verde); color: var(--blanco); }
.caja-chips input:focus-visible + span { outline: 2px solid var(--dorado); }

.caja-form .form-grupo { margin-top: 1rem; }
.caja-foto { margin-top: .75rem; }
.caja-foto.listo { border-color: var(--verde); color: var(--verde); }
.caja-mas { margin: 1rem 0; }
.caja-mas summary { cursor: pointer; font-size: .85rem; color: var(--gris); }

.caja-titulo { margin: 0 0 .75rem; font-size: 1rem; }
.caja-vacio  { color: var(--gris); font-size: .9rem; }
.caja-lista  { list-style: none; margin: 0; padding: 0; }
.caja-lista li {
    display: flex; align-items: center; gap: .75rem; padding: .6rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.caja-lista div    { flex: 1; min-width: 0; }
.caja-lista strong { display: block; font-size: .9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.caja-lista span   { font-size: .75rem; color: var(--gris); }
.caja-lista b      { white-space: nowrap; }
.caja-no-cuenta    { opacity: .5; }
.caja-no-cuenta b  { text-decoration: line-through; }
.caja-ver-todo { display: inline-block; margin-top: .75rem; font-size: .85rem; color: var(--verde); }
</style>

<script>
(function () {
    const form   = document.getElementById('caja-form');
    const monto  = document.getElementById('caja-monto');
    const tasa   = document.getElementById('caja-tasa-input');
    const equiv  = document.getElementById('caja-equivale');
    const foto   = document.getElementById('caja-foto');
    let moneda   = 'USD';

    // "1.234,56" o "1234.56" → 1234.56
    function numero(txt) {
        txt = (txt || '').trim().replace(/\s/g, '');
        if (txt.includes(',')) txt = txt.replace(/\./g, '').replace(',', '.');
        const n = parseFloat(txt);
        return isNaN(n) ? 0 : n;
    }

    function recordar(clave, valor) {
        try { localStorage.setItem('caja-' + clave, valor); } catch (e) {}
    }
    function recordado(clave) {
        try { return localStorage.getItem('caja-' + clave); } catch (e) { return null; }
    }

    function ponerTipo(tipo) {
        document.getElementById('caja-tipo').value = tipo;
        document.querySelectorAll('.caja-switch button').forEach(b =>
            b.classList.toggle('activo', b.dataset.tipo === tipo));
        // Lo del otro tipo se oculta y se desactiva para que no viaje en el POST
        document.querySelectorAll('[data-grupo]').forEach(el => {
            const mio = el.dataset.grupo === tipo;
            el.hidden = !mio;
            el.querySelectorAll('input, select, textarea').forEach(i => i.disabled = !mio);
            if (el.matches('input')) el.disabled = !mio;
        });
        document.getElementById('caja-guardar').textContent = 'Guardar ' + tipo;
        const cuenta = recordado('cuenta-' + tipo);
        const radio  = cuenta && form.querySelector('input[name="cuenta_id"][value="' + cuenta + '"]');
        if (radio) { radio.checked = true; elegirCuenta(radio); }
    }

    function ponerMoneda(m) {
        moneda = m;
        document.querySelectorAll('.caja-moneda button').forEach(b =>
            b.classList.toggle('activo', b.dataset.moneda === m));
        document.getElementById('caja-tasa').hidden = m !== 'VES';
        tasa.required = m === 'VES';
        calcular();
    }

    function calcular() {
        const n = numero(monto.value), t = numero(tasa.value);
        equiv.textContent = moneda === 'VES' && n && t ? '= $' + (n / t).toFixed(2) : '';
    }

    function elegirCuenta(radio) {
        document.getElementById('caja-metodo').value = radio.dataset.metodo;
        ponerMoneda(radio.dataset.moneda);
    }

    document.querySelectorAll('.caja-switch button').forEach(b =>
        b.addEventListener('click', () => ponerTipo(b.dataset.tipo)));
    document.querySelectorAll('.caja-moneda button').forEach(b =>
        b.addEventListener('click', () => ponerMoneda(b.dataset.moneda)));
    form.querySelectorAll('input[name="cuenta_id"]').forEach(r =>
        r.addEventListener('change', () => elegirCuenta(r)));
    monto.addEventListener('input', calcular);
    tasa.addEventListener('input', calcular);

    foto.addEventListener('change', () => {
        const hay = foto.files.length > 0;
        document.getElementById('caja-foto-txt').textContent = hay ? foto.files[0].name : 'Foto del recibo o capture';
        foto.closest('label').classList.toggle('listo', hay);
    });

    // Una foto del teléfono pasa fácil de 5 MB, que es el límite del servidor
    async function achicar(file) {
        if (!file.type.startsWith('image/') || file.type === 'image/gif') return file;
        let img;
        try { img = await createImageBitmap(file); } catch (e) { return file; }
        const escala = Math.min(1, 1600 / Math.max(img.width, img.height));
        const canvas = document.createElement('canvas');
        canvas.width  = Math.round(img.width * escala);
        canvas.height = Math.round(img.height * escala);
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
        const blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', 0.8));
        if (!blob || blob.size >= file.size) return file;
        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
    }

    let enviando = false;
    form.addEventListener('submit', async e => {
        if (enviando) return;
        e.preventDefault();

        const n = numero(monto.value);
        if (n <= 0) { monto.focus(); return; }
        document.getElementById('caja-usd').value = moneda === 'USD' ? n : '';
        document.getElementById('caja-ves').value = moneda === 'VES' ? n : '';
        tasa.value = moneda === 'VES' ? numero(tasa.value) : '';

        const boton = document.getElementById('caja-guardar');
        boton.disabled = true;
        boton.textContent = 'Guardando…';

        if (foto.files.length) {
            try {
                const dt = new DataTransfer();
                dt.items.add(await achicar(foto.files[0]));
                foto.files = dt.files;
            } catch (err) {}
        }

        const cuenta = form.querySelector('input[name="cuenta_id"]:checked');
        if (cuenta) recordar('cuenta-' + document.getElementById('caja-tipo').value, cuenta.value);

        enviando = true;
        form.submit();
    });

    ponerTipo(document.getElementById('caja-tipo').value);
})();
</script>
