<?php $titulo = 'Gestión de Candidatos'; ?>

<div class="admin-layout">
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="admin-header">
            <div>
                <h1>Candidatos</h1>
                <p>Gestión y evaluación de postulaciones</p>
            </div>
            <a href="/admin/candidatos?exportar=1<?= $filtro_estatus ? '&estatus='.urlencode($filtro_estatus) : '' ?>"
               class="btn btn--outline btn--sm" title="Descargar lista como CSV">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
        </div>

        <!-- Filtros rápidos por estatus ─────────────────────── -->
        <div class="filtros-tabs">
            <?php
            $filtros = [
                ''           => ['label' => 'Todos',       'num' => $totales['todos']],
                'enviada'    => ['label' => 'Enviadas',    'num' => $totales['enviada']],
                'en_revision'=> ['label' => 'En revisión', 'num' => $totales['en_revision']],
                'aprobada'   => ['label' => 'Aprobados',   'num' => $totales['aprobada']],
                'rechazada'  => ['label' => 'Rechazados',  'num' => $totales['rechazada']],
            ];
            foreach ($filtros as $val => $f):
            ?>
            <a href="/admin/candidatos?estatus=<?= $val ?><?= $busqueda ? '&q='.urlencode($busqueda) : '' ?>"
               class="filtro-tab <?= $filtro_estatus === $val ? 'activo' : '' ?>">
                <?= $f['label'] ?>
                <span class="filtro-tab__num"><?= $f['num'] ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Barra de búsqueda ───────────────────────────────── -->
        <form method="GET" action="/admin/candidatos" class="busqueda-bar">
            <div class="busqueda-bar__input">
                <i class="fas fa-search"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($busqueda) ?>"
                       placeholder="Buscar por nombre, cédula, iglesia...">
            </div>
            <?php if ($filtro_estatus): ?>
            <input type="hidden" name="estatus" value="<?= $filtro_estatus ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn--verde">Buscar</button>
            <?php if ($busqueda): ?>
            <a href="/admin/candidatos?estatus=<?= $filtro_estatus ?>" class="btn btn--outline">
                <i class="fas fa-times"></i> Limpiar
            </a>
            <?php endif; ?>
        </form>

        <!-- Tabla principal ─────────────────────────────────── -->
        <div class="admin-panel">
            <div class="tabla-wrap">
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Candidato</th>
                            <th>Cédula</th>
                            <th>Iglesia / Estado</th>
                            <th>Edad</th>
                            <th>Movilidad</th>
                            <th>Estatus</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($lista)): ?>
                        <tr>
                            <td colspan="9" class="tabla__vacio">
                                <i class="fas fa-inbox"></i>
                                No hay candidatos <?= $busqueda ? "con ese criterio" : "en este estado" ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lista as $i => $a): ?>
                        <tr id="fila-<?= $a['id'] ?>">
                            <td class="tabla__id"><?= $a['id'] ?></td>
                            <td>
                                <div class="tabla__candidato">
                                    <div class="tabla__avatar">
                                        <?= mb_strtoupper(mb_substr($a['nombres'] ?? $a['email'] ?? '?', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars(($a['nombres'] ?? '') . ' ' . ($a['apellidos'] ?? '')) ?></strong>
                                        <small><?= htmlspecialchars($a['email'] ?? '') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($a['cedula'] ?? '—') ?></td>
                            <td>
                                <div><?= htmlspecialchars($a['iglesia'] ?? '—') ?></div>
                                <small><?= htmlspecialchars($a['estado_origen'] ?? '') ?></small>
                            </td>
                            <td><?= $a['edad'] ?: '—' ?></td>
                            <td>
                                <?php if ($a['compromiso_movilidad'] ?? null): ?>
                                    <span class="movilidad-ok"><i class="fas fa-check"></i> Sí</span>
                                <?php elseif (isset($a['compromiso_movilidad'])): ?>
                                    <span class="movilidad-no"><i class="fas fa-times"></i> No</span>
                                <?php else: ?>
                                    <span class="texto-gris">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge--<?= $a['estatus'] ?>">
                                    <?= ucfirst(str_replace('_', ' ', $a['estatus'])) ?>
                                </span>
                            </td>
                            <td>
                                <small><?= date('d/m/Y', strtotime($a['created_at'])) ?></small>
                            </td>
                            <td>
                                <div class="tabla__acciones">
                                    <!-- Ver detalle -->
                                    <button type="button" class="btn btn--outline btn--xs"
                                            onclick="abrirDetalle(<?= $a['id'] ?>)"
                                            title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <!-- Cambiar estatus rápido -->
                                    <button type="button" class="btn btn--verde btn--xs"
                                            onclick="abrirCambioEstatus(<?= $a['id'] ?>, '<?= $a['estatus'] ?>', '<?= htmlspecialchars(addslashes($a['nombres'].' '.$a['apellidos'])) ?>')"
                                            title="Cambiar estatus">
                                        <i class="fas fa-exchange-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<!-- Modal: Cambiar estatus ──────────────────────────────────── -->
<div class="modal-overlay" id="modal-estatus" style="display:none">
    <div class="modal">
        <div class="modal__header">
            <h3><i class="fas fa-exchange-alt"></i> Cambiar Estatus</h3>
            <button type="button" class="modal__cerrar" onclick="cerrarModal('modal-estatus')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/candidatos">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="modal-id">
            <div class="modal__body">
                <p class="modal__subtitulo" id="modal-nombre"></p>

                <div class="form-grupo">
                    <label>Nuevo estatus</label>
                    <div class="estatus-opciones">
                        <?php foreach ([
                            'enviada'     => ['label' => 'Enviada',     'icono' => 'fa-paper-plane',  'color' => 'azul'],
                            'en_revision' => ['label' => 'En revisión', 'icono' => 'fa-search',       'color' => 'dorado'],
                            'aprobada'    => ['label' => 'Aprobada',    'icono' => 'fa-check-circle', 'color' => 'verde'],
                            'rechazada'   => ['label' => 'Rechazada',   'icono' => 'fa-times-circle', 'color' => 'rojo'],
                        ] as $val => $est): ?>
                        <label class="estatus-opcion estatus-opcion--<?= $est['color'] ?>">
                            <input type="radio" name="estatus" value="<?= $val ?>">
                            <i class="fas <?= $est['icono'] ?>"></i>
                            <?= $est['label'] ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="nota">Nota del evaluador (opcional)</label>
                    <textarea name="nota" id="nota" rows="3"
                              placeholder="Observaciones sobre esta decisión..."></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModal('modal-estatus')">Cancelar</button>
                <button type="submit" class="btn btn--verde">
                    <i class="fas fa-save"></i> Guardar cambio
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirCambioEstatus(id, estatusActual, nombre) {
    document.getElementById('modal-id').value    = id;
    document.getElementById('modal-nombre').textContent = nombre;
    // Marcar el radio del estatus actual
    document.querySelectorAll('[name="estatus"]').forEach(r => {
        r.checked = r.value === estatusActual;
    });
    document.getElementById('modal-estatus').style.display = 'flex';
}

function abrirDetalle(id) {
    window.location.href = '/admin/candidatos?ver=' + id;
}

function cerrarModal(id) {
    document.getElementById(id).style.display = 'none';
}

// Cerrar modal al click fuera
document.getElementById('modal-estatus')?.addEventListener('click', function(e) {
    if (e.target === this) cerrarModal('modal-estatus');
});
</script>
