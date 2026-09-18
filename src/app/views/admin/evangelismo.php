<?php
$titulo    = 'Evangelismo';
$es_admin  = $_SESSION['usuario_rol'] === 'admin';
$link_pin  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/evangelismo';
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
                <h1>Evangelismo</h1>
                <p>Personas alcanzadas en campo, decisiones de fe y discipulado</p>
            </div>
            <button type="button" class="btn btn--primario" onclick="abrirPersona(null)">
                <i class="fas fa-plus"></i> Registrar persona
            </button>
        </div>

        <!-- Totales ─────────────────────────────────────────── -->
        <div class="evg-kpis">
            <div class="kpi-card kpi-card--enviada">
                <div class="kpi-card__icono"><i class="fas fa-bullhorn"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $totales['evangelizados'] ?></span>
                    <span class="kpi-card__label">Personas evangelizadas</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--revision">
                <div class="kpi-card__icono"><i class="fas fa-heart"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $totales['decisiones'] ?></span>
                    <span class="kpi-card__label">Decisiones de fe</span>
                </div>
            </div>
            <div class="kpi-card kpi-card--aprobada">
                <div class="kpi-card__icono"><i class="fas fa-book-open"></i></div>
                <div class="kpi-card__datos">
                    <span class="kpi-card__num"><?= $totales['discipulados'] ?></span>
                    <span class="kpi-card__label">En discipulado</span>
                </div>
            </div>
        </div>

        <div class="evg-cols">

            <!-- Por sede ────────────────────────────────────── -->
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-map-marker-alt"></i> Por sede</h2>
                </div>
                <?php if (empty($por_sede)): ?>
                <p class="evg-vacio">Todavía no hay registros.</p>
                <?php else: ?>
                <div class="tabla-wrap">
                <table class="tabla">
                    <thead><tr><th>Sede</th><th>Evangelizados</th><th>Decisiones</th><th>Discipulado</th></tr></thead>
                    <tbody>
                    <?php foreach ($por_sede as $s): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($s['sede']) ?></strong></td>
                        <td><?= (int) $s['evangelizados'] ?></td>
                        <td><?= (int) $s['decisiones'] ?></td>
                        <td><?= (int) $s['discipulados'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </section>

            <!-- Acceso con PIN ──────────────────────────────── -->
            <section class="admin-panel">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-key"></i> Acceso con PIN</h2>
                    <span class="badge <?= $pin_activo ? 'badge--aprobada' : 'badge--borrador' ?>">
                        <?= $pin_activo ? 'Activo' : 'Desactivado' ?>
                    </span>
                </div>
                <div class="evg-pin">
                    <p class="texto-muted">
                        El equipo en campo registra desde el teléfono, sin usuario, en este enlace.
                        Solo pueden cargar personas: la lista y los datos se ven únicamente aquí.
                    </p>
                    <div class="evg-link">
                        <input type="text" value="<?= htmlspecialchars($link_pin) ?>" readonly id="evg-link">
                        <button type="button" class="btn btn--sm btn--outline" onclick="copiarLink()">
                            <i class="fas fa-copy"></i> <span id="evg-copiar">Copiar</span>
                        </button>
                    </div>

                    <?php if ($es_admin): ?>
                    <form method="POST" action="/admin/evangelismo/pin" class="evg-pin__form">
                        <?= csrf_field() ?>
                        <div class="form-grid-2">
                            <div class="form-grupo">
                                <label><?= $pin_activo ? 'Nuevo PIN' : 'PIN' ?></label>
                                <input type="password" name="pin" minlength="6" maxlength="20" required autocomplete="new-password">
                            </div>
                            <div class="form-grupo">
                                <label>Repite el PIN</label>
                                <input type="password" name="pin2" minlength="6" maxlength="20" required autocomplete="new-password">
                            </div>
                        </div>
                        <button type="submit" class="btn btn--sm btn--verde">
                            <i class="fas fa-save"></i> <?= $pin_activo ? 'Cambiar PIN' : 'Activar acceso' ?>
                        </button>
                    </form>
                    <?php if ($pin_activo): ?>
                    <form method="POST" action="/admin/evangelismo/pin"
                          onsubmit="return confirm('¿Desactivar el acceso con PIN? Nadie podrá registrar desde el enlace hasta que pongas uno nuevo.')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="desactivar">
                        <button type="submit" class="btn btn--sm btn--outline evg-rojo">
                            <i class="fas fa-ban"></i> Desactivar acceso
                        </button>
                    </form>
                    <?php endif; ?>
                    <p class="texto-muted" style="margin-top:.75rem">Mínimo 6 caracteres. Al cambiarlo, quien tenga el anterior queda afuera.</p>
                    <?php else: ?>
                    <p class="texto-muted">Solo un administrador puede cambiar el PIN.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Filtros ─────────────────────────────────────────── -->
        <div class="filtros-tabs">
            <?php
            $etapas = ['' => 'Todas', 'decision' => 'Decisión de fe', 'discipulado' => 'En discipulado'];
            foreach ($etapas as $val => $label):
                $qs = http_build_query(array_filter(['etapa' => $val, 'sede_id' => $filtros['sede_id'], 'q' => $filtros['q']]));
            ?>
            <a href="/admin/evangelismo<?= $qs ? '?' . $qs : '' ?>"
               class="filtro-tab <?= $filtros['etapa'] === $val ? 'activo' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <form method="GET" action="/admin/evangelismo" class="busqueda-bar">
            <?php if ($filtros['etapa']): ?>
            <input type="hidden" name="etapa" value="<?= htmlspecialchars($filtros['etapa']) ?>">
            <?php endif; ?>
            <div class="busqueda-bar__input">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($filtros['q']) ?>"
                       placeholder="Buscar por nombre, teléfono o dirección">
            </div>
            <select name="sede_id" class="evg-select" onchange="this.form.submit()">
                <option value="">Todas las sedes</option>
                <?php foreach ($sedes as $s): ?>
                <option value="<?= $s['id'] ?>" <?= (string) $filtros['sede_id'] === (string) $s['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn--verde">Buscar</button>
            <?php if ($filtros['q'] || $filtros['sede_id'] || $filtros['etapa']): ?>
            <a href="/admin/evangelismo" class="btn btn--outline"><i class="fas fa-times"></i> Limpiar</a>
            <?php endif; ?>
        </form>

        <!-- Lista ───────────────────────────────────────────── -->
        <div class="admin-panel">
            <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>Persona</th><th>Edad</th><th>Teléfono</th><th>Dirección</th>
                            <th>Sede</th><th>Etapa</th><th>Contacto</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($personas)): ?>
                        <tr><td colspan="8" class="tabla__vacio"><i class="fas fa-inbox"></i> No hay registros con ese filtro.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($personas as $p): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?></strong>
                                <?php if ($p['responsable']): ?>
                                <br><small class="texto-muted">Por <?= htmlspecialchars($p['responsable']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= $p['edad'] ?? '—' ?></td>
                            <td style="white-space:nowrap"><?= htmlspecialchars($p['telefono'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($p['direccion'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($p['sede_nombre'] ?? '—') ?></td>
                            <td>
                                <?php if ($p['discipulado']): ?>
                                <span class="badge badge--aprobada">Discipulado</span>
                                <?php elseif ($p['decision_fe']): ?>
                                <span class="badge badge--en_revision">Decisión de fe</span>
                                <?php else: ?>
                                <span class="badge badge--enviada">Evangelizado</span>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap">
                                <?= date('d/m/Y', strtotime($p['fecha_contacto'])) ?>
                                <?php if ($p['registrado_via'] === 'pin'): ?>
                                <br><small class="texto-muted" title="Cargado desde el enlace con PIN"><i class="fas fa-key"></i> PIN</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="tabla__acciones">
                                    <button type="button" class="btn btn--sm btn--outline" title="Editar"
                                            data-persona="<?= htmlspecialchars(json_encode([
                                                'id'             => $p['id'],
                                                'nombres'        => $p['nombres'],
                                                'apellidos'      => $p['apellidos'],
                                                'edad'           => $p['edad'],
                                                'telefono'       => $p['telefono'],
                                                'direccion'      => $p['direccion'],
                                                'sede_id'        => $p['sede_id'],
                                                'fecha_contacto' => $p['fecha_contacto'],
                                                'decision_fe'    => (int) $p['decision_fe'],
                                                'discipulado'    => (int) $p['discipulado'],
                                                'responsable'    => $p['responsable'],
                                                'notas'          => $p['notas'],
                                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <?php if ($es_admin): ?>
                                    <form method="POST" action="/admin/evangelismo/eliminar"
                                          onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars(addslashes($p['nombres'] . ' ' . $p['apellidos']), ENT_QUOTES) ?>? No se puede deshacer.')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
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
        </div>

    </main>
</div>

<!-- Modal: Registrar / editar persona ───────────────────────── -->
<div class="modal-overlay" id="modal-persona" style="display:none"
     onclick="if (event.target === this) cerrarPersona()">
    <div class="modal" style="max-width:640px">
        <div class="modal__header">
            <h3><i class="fas fa-user-plus"></i> <span id="persona-titulo">Registrar persona</span></h3>
            <button type="button" class="modal__cerrar" onclick="cerrarPersona()">&times;</button>
        </div>
        <form method="POST" action="/admin/evangelismo" id="form-persona">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="">
            <div class="modal__body" style="max-height:70vh;overflow-y:auto">
                <?php include __DIR__ . '/../partials/evangelismo_campos.php'; ?>
                <div class="form-grupo">
                    <label>Quién hizo el contacto</label>
                    <input type="text" name="responsable" maxlength="150" placeholder="Nombre del participante o del equipo">
                </div>
                <div class="form-grupo">
                    <label>Notas</label>
                    <textarea name="notas" rows="2" style="min-height:0"></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarPersona()">Cancelar</button>
                <button type="submit" class="btn btn--verde"><i class="fas fa-save"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
var sedeActual = <?= json_encode($sede_actual) ?>;

function abrirPersona(datos) {
    var form = document.getElementById('form-persona');
    form.reset();
    form.elements.id.value = '';
    document.getElementById('persona-titulo').textContent = datos ? 'Editar persona' : 'Registrar persona';
    if (datos) {
        Object.keys(datos).forEach(function (campo) {
            var el = form.elements[campo];
            if (!el) return;
            if (el.type === 'checkbox') el.checked = datos[campo] == 1;
            else el.value = datos[campo] === null ? '' : datos[campo];
        });
    } else if (sedeActual) {
        form.elements.sede_id.value = sedeActual;
    }
    document.getElementById('modal-persona').style.display = 'flex';
}
function cerrarPersona() {
    document.getElementById('modal-persona').style.display = 'none';
}
document.querySelectorAll('[data-persona]').forEach(function (b) {
    b.addEventListener('click', function () { abrirPersona(JSON.parse(b.dataset.persona)); });
});
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarPersona(); });

function copiarLink() {
    var input = document.getElementById('evg-link');
    input.select();
    navigator.clipboard.writeText(input.value).then(function () {
        document.getElementById('evg-copiar').textContent = 'Copiado';
    });
}
</script>

<style>
.evg-kpis { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.evg-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; align-items: start; }
.evg-cols .admin-panel { margin: 0; }
.evg-vacio { padding: 1.5rem; color: var(--gris); text-align: center; }
.evg-pin { padding: 1.25rem; }
.evg-pin .texto-muted { color: var(--gris); font-size: 0.82rem; margin-bottom: 0.75rem; }
.evg-link { display: flex; gap: 0.5rem; margin-bottom: 1.25rem; }
.evg-link input {
    flex: 1; min-width: 0; padding: 0.5rem 0.75rem; font-size: 0.85rem;
    border: 2px solid #e5e7eb; border-radius: var(--radio); background: #f8fafc;
}
.evg-pin__form { margin-bottom: 0.5rem; }
.evg-select {
    padding: 0.6rem 0.75rem; border: 2px solid #e5e7eb;
    border-radius: var(--radio); font-family: inherit; background: var(--blanco);
}
.evg-rojo { color: #dc2626; border-color: #fca5a5; }
.evg-rojo:hover { background: #fee2e2; color: #b91c1c; }
.tabla__acciones form { display: inline; margin: 0; }
.texto-muted { color: var(--gris); font-size: 0.8rem; }
@media (max-width: 1200px) {
    .evg-cols { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .evg-kpis { grid-template-columns: 1fr; }
    .busqueda-bar { flex-wrap: wrap; }
}
</style>
