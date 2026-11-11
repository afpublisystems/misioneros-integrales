<?php
$es_admin = $_SESSION['usuario_rol'] === 'admin';
$dias     = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
$dia      = fn(string $f) => $dias[(int) date('w', strtotime($f))] . ' ' . date('d/m', strtotime($f));
$anterior = date('Y-m-d', strtotime($lunes . ' -7 days'));
$siguiente = date('Y-m-d', strtotime($lunes . ' +7 days'));
$t        = $resumen['total'];
?>

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
                <h1>Prácticas misioneras</h1>
                <p>Lo que reporta cada grupo de su semana en la iglesia donde sirve</p>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                <a href="/admin/practicas/informe?semana=<?= $lunes ?>" target="_blank" class="btn btn--outline">
                    <i class="fas fa-print"></i> Informe de la semana
                </a>
                <a href="/admin/practicas/cierre" target="_blank" class="btn btn--outline"
                   title="Todo lo logrado en el lugar: fruto, alcances y avance del discipulado">
                    <i class="fas fa-flag-checkered"></i> Informe de cierre
                </a>
                <button type="button" class="btn btn--primario" onclick="abrirActividad(null)">
                    <i class="fas fa-plus"></i> Registrar actividad
                </button>
            </div>
        </div>

        <!-- Semana ───────────────────────────────────────────── -->
        <div class="admin-panel" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 1rem">
            <a href="/admin/practicas?semana=<?= $anterior ?>" class="btn btn--sm btn--outline"><i class="fas fa-chevron-left"></i> Anterior</a>
            <div style="text-align:center">
                <strong>Semana del <?= date('d/m', strtotime($lunes)) ?> al <?= date('d/m/Y', strtotime($domingo)) ?></strong>
                <?php if ($numero): ?><br><small class="texto-muted">Semana <?= $numero ?> de prácticas</small><?php endif; ?>
            </div>
            <a href="/admin/practicas?semana=<?= $siguiente ?>" class="btn btn--sm btn--outline">Siguiente <i class="fas fa-chevron-right"></i></a>
        </div>

        <div class="evg-kpis">
            <div class="kpi-card kpi-card--total">
                <div class="kpi-card__icono"><i class="fas fa-church"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $t['comunidad'] ?></span>
                    <span class="kpi-card__label">De la comunidad en los cultos del domingo</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--enviada">
                <div class="kpi-card__icono"><i class="fas fa-bullhorn"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $t['evangelizados'] ?></span>
                    <span class="kpi-card__label">Personas evangelizadas</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--revision">
                <div class="kpi-card__icono"><i class="fas fa-heart"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $t['decisiones'] ?></span>
                    <span class="kpi-card__label">Decisiones de fe</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--aprobada">
                <div class="kpi-card__icono"><i class="fas fa-book-open"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $t['discipulado'] ?></span>
                    <span class="kpi-card__label">En discipulado</span>
                </div>
            </div>
        </div>

        <!-- Actividades de la semana ─────────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-list"></i> Actividades de la semana</h2>
            </div>
            <?php if (empty($actividades)): ?>
            <p class="evg-vacio">No hay actividades cargadas en esta semana.</p>
            <?php else: ?>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Día</th><th>Iglesia</th><th>Actividad</th>
                        <th>Comunidad</th><th>Equipo</th>
                        <th>Evang.</th><th>Decis.</th><th>Discip.</th><th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($actividades as $a): ?>
                <tr>
                    <td style="white-space:nowrap"><?= $dia($a['fecha']) ?></td>
                    <td><?= htmlspecialchars($a['iglesia']) ?></td>
                    <td>
                        <strong><?= PracticaModel::TIPOS[$a['tipo']] ?></strong>
                        <?php if ($a['descripcion']): ?><br><small class="texto-muted"><?= htmlspecialchars($a['descripcion']) ?></small><?php endif; ?>
                    </td>
                    <td>
                        <?= $a['comunidad'] ?: '—' ?>
                        <?php if ($a['adultos'] !== null): ?>
                        <br><small class="texto-muted"><?= $a['adultos'] ?> ad · <?= $a['jovenes'] ?> jóv · <?= $a['ninos'] ?> niñ</small>
                        <?php endif; ?>
                    </td>
                    <td><?= $a['equipo'] ?: '—' ?></td>
                    <td><?= $a['evangelizados'] ?: '—' ?></td>
                    <td><?= $a['decisiones'] ?: '—' ?></td>
                    <td><?= $a['discipulado'] ?: '—' ?></td>
                    <td>
                        <div class="tabla__acciones">
                            <button type="button" class="btn btn--sm btn--outline" title="Editar"
                                    data-actividad="<?= htmlspecialchars(json_encode(array_intersect_key($a, array_flip([
                                        'id', 'iglesia_id', 'fecha', 'tipo', 'descripcion', 'adultos', 'jovenes', 'ninos',
                                        'comunidad', 'equipo', 'evangelizados', 'decisiones', 'discipulado',
                                        'dirigida_a', 'leccion_id',
                                    ])), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-pen"></i>
                            </button>
                            <?php if ($es_admin): ?>
                            <form method="POST" action="/admin/practicas/eliminar"
                                  onsubmit="return confirm('¿Eliminar esta actividad? No se puede deshacer.')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn btn--sm btn--outline evg-rojo" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </section>

        <!-- Acumulado ────────────────────────────────────────── -->
        <section class="admin-panel">
            <div class="admin-panel__header">
                <h2><i class="fas fa-chart-line"></i> Acumulado de las prácticas</h2>
            </div>
            <div class="tabla-wrap">
            <table class="tabla">
                <thead>
                    <tr><th>Iglesia</th><th>Actividades</th><th>Evangelizados</th><th>Decisiones</th><th>Discipulado</th><th>Promedio del domingo</th></tr>
                </thead>
                <tbody>
                <?php foreach ($acumulado as $r): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($r['iglesia']) ?></strong> <small class="texto-muted"><?= htmlspecialchars($r['sector'] ?? '') ?></small></td>
                    <td><?= (int) $r['actividades'] ?></td>
                    <td><?= (int) $r['evangelizados'] ?></td>
                    <td><?= (int) $r['decisiones'] ?></td>
                    <td><?= (int) $r['discipulado'] ?></td>
                    <td><?= $r['promedio_culto'] !== null ? (int) $r['promedio_culto'] . ' de la comunidad' : '—' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>

    </main>
</div>

<!-- Modal: registrar / editar actividad ──────────────────────── -->
<div class="modal-overlay" id="modal-actividad" style="display:none"
     onclick="if (event.target === this) cerrarActividad()">
    <div class="modal" style="max-width:640px">
        <div class="modal__header">
            <h3><i class="fas fa-church"></i> <span id="actividad-titulo">Registrar actividad</span></h3>
            <button type="button" class="modal__cerrar" onclick="cerrarActividad()">&times;</button>
        </div>
        <form method="POST" action="/admin/practicas" id="form-actividad">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="modal__body" style="max-height:70vh;overflow-y:auto">
                <div class="form-grid-2">
                    <div class="form-grupo">
                        <label>Iglesia</label>
                        <select name="iglesia_id" required>
                            <option value="">Elige…</option>
                            <?php foreach ($iglesias as $i): ?>
                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['nombre'] . ($i['sector'] ? ' · ' . $i['sector'] : '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-grupo">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="form-grupo">
                    <label>Actividad</label>
                    <select name="tipo" required>
                        <?php foreach (PracticaModel::TIPOS as $clave => $etiqueta): ?>
                        <option value="<?= $clave ?>"><?= $etiqueta ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grupo prac-solo-organizacion" hidden>
                    <label>¿A quién fue dirigida?</label>
                    <div class="prac-orgs">
                        <?php foreach (PracticaModel::ORGANIZACIONES as $clave => $etiqueta): ?>
                        <label class="check-label">
                            <input type="checkbox" name="dirigida_a[]" value="<?= $clave ?>">
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-grupo">
                    <label>Qué se hizo <small class="texto-muted">(opcional)</small></label>
                    <input type="text" name="descripcion" maxlength="255" placeholder="Ej.: Reflexiones de esperanza con las damas">
                </div>

                <p class="texto-muted" style="margin:.25rem 0 .75rem">
                    <strong>Asistencia de la comunidad.</strong> Si se contó por edades, llena el desglose y el total sale solo.
                    Si solo se contó la gente, escribe el total.
                </p>
                <div class="form-grid-3">
                    <div class="form-grupo"><label>Adultos</label><input type="number" name="adultos" min="0" inputmode="numeric"></div>
                    <div class="form-grupo"><label>Jóvenes</label><input type="number" name="jovenes" min="0" inputmode="numeric"></div>
                    <div class="form-grupo"><label>Niños</label><input type="number" name="ninos" min="0" inputmode="numeric"></div>
                </div>
                <div class="form-grid-2">
                    <div class="form-grupo"><label>Total de la comunidad</label><input type="number" name="comunidad" min="0" inputmode="numeric"></div>
                    <div class="form-grupo"><label>Equipo <small class="texto-muted">(misioneros, coordinadores, familia)</small></label><input type="number" name="equipo" min="0" inputmode="numeric"></div>
                </div>

                <div class="form-grid-3">
                    <div class="form-grupo"><label>Evangelizados</label><input type="number" name="evangelizados" min="0" inputmode="numeric"></div>
                    <div class="form-grupo"><label>Decisiones de fe</label><input type="number" name="decisiones" min="0" inputmode="numeric"></div>
                    <div class="form-grupo"><label>En discipulado</label><input type="number" name="discipulado" min="0" inputmode="numeric"></div>
                </div>
                <div class="form-grupo">
                    <label>Lección que se dio <small class="texto-muted">(opcional)</small></label>
                    <select name="leccion_id">
                        <option value="">Ninguna o no aplica</option>
                        <?php $nivel_previo = null; foreach ($lecciones as $l): ?>
                            <?php if ($l['nivel'] !== $nivel_previo): ?>
                            <?php if ($nivel_previo !== null): ?></optgroup><?php endif; ?>
                            <optgroup label="<?= htmlspecialchars($l['nivel']) ?>">
                            <?php $nivel_previo = $l['nivel']; endif; ?>
                        <option value="<?= $l['id'] ?>"><?= $l['numero'] ?>. <?= htmlspecialchars($l['titulo']) ?></option>
                        <?php endforeach; ?>
                        <?php if ($nivel_previo !== null): ?></optgroup><?php endif; ?>
                    </select>
                    <span class="texto-muted">Así el informe de cierre arma solo el avance por lección.</span>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarActividad()">Cancelar</button>
                <button type="submit" class="btn btn--verde"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.prac-orgs { display: flex; flex-wrap: wrap; gap: 0.5rem 1.25rem; }
.prac-orgs .check-label { font-size: 0.85rem; align-items: center; }
.prac-solo-organizacion[hidden] { display: none; }
</style>

<script>
var formAct = document.getElementById('form-actividad');

function abrirActividad(datos) {
    formAct.reset();
    formAct.elements.id.value = '';
    formAct.elements.fecha.value = '<?= min($domingo, date('Y-m-d')) ?>';
    document.getElementById('actividad-titulo').textContent = datos ? 'Editar actividad' : 'Registrar actividad';
    formAct.querySelectorAll('[name="dirigida_a[]"]').forEach(function (c) { c.checked = false; });

    if (datos) {
        Object.keys(datos).forEach(function (campo) {
            var el = formAct.elements[campo];
            // dirigida_a son varias casillas, no un campo con valor
            if (!el || campo === 'dirigida_a') return;
            el.value = datos[campo] === null ? '' : datos[campo];
        });
        // Un cero guardado es "no aplica": se muestra vacío para no confundir
        ['comunidad', 'equipo', 'evangelizados', 'decisiones', 'discipulado'].forEach(function (c) {
            if (formAct.elements[c].value === '0') formAct.elements[c].value = '';
        });
        var orgs = String(datos.dirigida_a || '').split(',');
        formAct.querySelectorAll('[name="dirigida_a[]"]').forEach(function (c) {
            c.checked = orgs.indexOf(c.value) !== -1;
        });
    }
    sumarDesglose();
    verOrganizaciones();
    document.getElementById('modal-actividad').style.display = 'flex';
}
function cerrarActividad() {
    document.getElementById('modal-actividad').style.display = 'none';
}

// Con desglose, el total de la comunidad es la suma y no se escribe a mano
function sumarDesglose() {
    var partes = ['adultos', 'jovenes', 'ninos'].map(function (c) { return formAct.elements[c].value; });
    var hay = partes.some(function (v) { return v !== ''; });
    formAct.elements.comunidad.readOnly = hay;
    if (hay) {
        formAct.elements.comunidad.value = partes.reduce(function (s, v) { return s + (parseInt(v, 10) || 0); }, 0);
    }
}
['adultos', 'jovenes', 'ninos'].forEach(function (c) { formAct.elements[c].addEventListener('input', sumarDesglose); });

// A quién va dirigida solo tiene sentido en las reuniones por organización
function verOrganizaciones() {
    var es = formAct.elements.tipo.value === 'organizacion';
    formAct.querySelectorAll('.prac-solo-organizacion').forEach(function (el) { el.hidden = !es; });
}
formAct.elements.tipo.addEventListener('change', verOrganizaciones);

document.querySelectorAll('[data-actividad]').forEach(function (b) {
    b.addEventListener('click', function () { abrirActividad(JSON.parse(b.dataset.actividad)); });
});
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarActividad(); });
</script>
