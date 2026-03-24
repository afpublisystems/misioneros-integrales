<?php $titulo = $aspirante['nombres'] . ' ' . $aspirante['apellidos']; ?>

<div class="admin-layout">
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <!-- Header ──────────────────────────────────────────── -->
        <div class="admin-header">
            <div style="display:flex; align-items:center; gap:1rem;">
                <a href="/admin/candidatos" class="btn btn--outline btn--sm">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                <div>
                    <h1><?= htmlspecialchars($aspirante['nombres'] . ' ' . $aspirante['apellidos']) ?></h1>
                    <p>
                        Cédula: <strong><?= htmlspecialchars($aspirante['cedula'] ?? '—') ?></strong>
                        &nbsp;·&nbsp;
                        Registro #<?= $aspirante['id'] ?>
                        &nbsp;·&nbsp;
                        <?= date('d/m/Y', strtotime($aspirante['created_at'])) ?>
                    </p>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.75rem;">
                <span class="badge badge--<?= $aspirante['estatus'] ?>" style="font-size:0.9rem; padding:0.35rem 1rem;">
                    <?= ucfirst(str_replace('_', ' ', $aspirante['estatus'])) ?>
                </span>
                <button type="button" class="btn btn--verde"
                        onclick="document.getElementById('modal-estatus').style.display='flex'">
                    <i class="fas fa-exchange-alt"></i> Cambiar estatus
                </button>
            </div>
        </div>

        <div class="ver-candidato-grid">

            <!-- Columna izquierda ──────────────────────────── -->
            <div class="ver-candidato-col ver-candidato-col--principal">

                <!-- Datos personales ──────────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-user"></i> Datos Personales</h2>
                    </div>
                    <div class="detalle-grid">
                        <div class="detalle-item">
                            <span class="detalle-item__label">Nombres</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['nombres'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Apellidos</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['apellidos'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Cédula</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['cedula'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Fecha de nacimiento</span>
                            <span class="detalle-item__val">
                                <?= $aspirante['fecha_nacimiento']
                                    ? date('d/m/Y', strtotime($aspirante['fecha_nacimiento']))
                                    : '—' ?>
                                (<?= $aspirante['edad'] ?> años)
                            </span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Género</span>
                            <span class="detalle-item__val"><?= ucfirst($aspirante['genero'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Estado civil</span>
                            <span class="detalle-item__val"><?= ucfirst($aspirante['estado_civil'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Hijos</span>
                            <span class="detalle-item__val"><?= $aspirante['hijos'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>

                <!-- Contacto ──────────────────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-address-card"></i> Contacto</h2>
                    </div>
                    <div class="detalle-grid">
                        <div class="detalle-item">
                            <span class="detalle-item__label">Correo</span>
                            <span class="detalle-item__val">
                                <a href="mailto:<?= htmlspecialchars($aspirante['email'] ?? '') ?>">
                                    <?= htmlspecialchars($aspirante['email'] ?? '—') ?>
                                </a>
                            </span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Teléfono</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['telefono'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Ciudad / Estado</span>
                            <span class="detalle-item__val">
                                <?= htmlspecialchars($aspirante['ciudad_origen'] ?? '—') ?>
                                <?= $aspirante['estado_origen'] ? '— ' . htmlspecialchars($aspirante['estado_origen']) : '' ?>
                            </span>
                        </div>
                        <?php if ($aspirante['direccion'] ?? null): ?>
                        <div class="detalle-item detalle-item--full">
                            <span class="detalle-item__label">Dirección</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['direccion']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Datos eclesiales ──────────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-church"></i> Datos Eclesiales</h2>
                    </div>
                    <div class="detalle-grid">
                        <div class="detalle-item">
                            <span class="detalle-item__label">Iglesia</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['iglesia'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Pastor</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['pastor'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Tel. Pastor</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['telefono_pastor'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Años bautizado</span>
                            <span class="detalle-item__val"><?= $aspirante['anos_bautizado'] ?? '—' ?> año(s)</span>
                        </div>
                    </div>
                </div>

                <!-- Académico y movilidad ─────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-graduation-cap"></i> Académico & Movilidad</h2>
                    </div>
                    <div class="detalle-grid">
                        <div class="detalle-item">
                            <span class="detalle-item__label">Nivel académico</span>
                            <span class="detalle-item__val"><?= ucfirst($aspirante['nivel_academico'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Título bachiller</span>
                            <span class="detalle-item__val">
                                <?php if ($aspirante['titulo_bachiller'] ?? false): ?>
                                    <span class="movilidad-ok"><i class="fas fa-check"></i> Sí</span>
                                <?php else: ?>
                                    <span class="movilidad-no"><i class="fas fa-times"></i> No</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Compromiso de movilidad</span>
                            <span class="detalle-item__val">
                                <?php if ($aspirante['compromiso_movilidad'] ?? false): ?>
                                    <span class="movilidad-ok"><i class="fas fa-check"></i> Puede movilizarse</span>
                                <?php else: ?>
                                    <span class="movilidad-no"><i class="fas fa-times"></i> Tiene impedimento</span>
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if (!($aspirante['compromiso_movilidad'] ?? true) && ($aspirante['detalle_impedimento'] ?? '')): ?>
                        <div class="detalle-item detalle-item--full">
                            <span class="detalle-item__label">Detalle del impedimento</span>
                            <span class="detalle-item__val detalle-item__val--nota">
                                <?= htmlspecialchars($aspirante['detalle_impedimento']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Columna derecha ────────────────────────────── -->
            <div class="ver-candidato-col ver-candidato-col--lateral">

                <!-- Flujo del proceso ─────────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-tasks"></i> Flujo del Proceso</h2>
                    </div>
                    <div class="flujo-proceso">
                        <?php
                        $etapas_def = [
                            'solicitud_formal'       => ['label' => 'Solicitud Formal',         'icono' => 'fa-file-alt'],
                            'evaluacion_documental'  => ['label' => 'Evaluación Documental',    'icono' => 'fa-folder-open'],
                            'test_vocacional'        => ['label' => 'Test Vocacional',           'icono' => 'fa-clipboard-list'],
                            'entrevista_personal'    => ['label' => 'Entrevista Personal',       'icono' => 'fa-user-tie'],
                            'confirmacion_admision'  => ['label' => 'Confirmación de Admisión',  'icono' => 'fa-check-double'],
                        ];

                        // Indexar flujo por etapa
                        $flujo_idx = [];
                        foreach ($flujo as $f) {
                            $flujo_idx[$f['etapa']] = $f;
                        }

                        foreach ($etapas_def as $clave => $def):
                            $ef  = $flujo_idx[$clave] ?? null;
                            $est = $ef['estatus'] ?? 'pendiente';
                        ?>
                        <div class="flujo-paso flujo-paso--<?= $est ?>">
                            <div class="flujo-paso__icono">
                                <i class="fas <?= $def['icono'] ?>"></i>
                            </div>
                            <div class="flujo-paso__info">
                                <div class="flujo-paso__nombre"><?= $def['label'] ?></div>
                                <div class="flujo-paso__est">
                                    <?php
                                    $labels_est = [
                                        'pendiente'  => 'Pendiente',
                                        'en_proceso' => 'En proceso',
                                        'aprobado'   => 'Aprobado',
                                        'rechazado'  => 'Rechazado',
                                    ];
                                    echo $labels_est[$est] ?? ucfirst($est);
                                    ?>
                                    <?php if ($ef && $ef['fecha_cierre']): ?>
                                        <small> · <?= date('d/m/Y', strtotime($ef['fecha_cierre'])) ?></small>
                                    <?php elseif ($ef && $ef['fecha_inicio'] && $est === 'en_proceso'): ?>
                                        <small> · desde <?= date('d/m/Y', strtotime($ef['fecha_inicio'])) ?></small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($ef && $ef['notas']): ?>
                                <div class="flujo-paso__nota"><?= htmlspecialchars($ef['notas']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flujo-paso__badge" style="display:flex; align-items:center; gap:.4rem;">
                                <?php if ($est === 'aprobado'): ?>
                                    <i class="fas fa-check-circle" style="color:var(--verde)"></i>
                                <?php elseif ($est === 'rechazado'): ?>
                                    <i class="fas fa-times-circle" style="color:#ef4444"></i>
                                <?php elseif ($est === 'en_proceso'): ?>
                                    <i class="fas fa-spinner fa-spin" style="color:var(--dorado)"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock" style="color:#94a3b8"></i>
                                <?php endif; ?>
                                <button type="button"
                                        class="flujo-btn-editar"
                                        title="Actualizar etapa"
                                        onclick="editarEtapa('<?= $clave ?>','<?= htmlspecialchars($def['label']) ?>')">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Test Vocacional ──────────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-clipboard-list"></i> Test Vocacional</h2>
                        <?php
                        $test_est = 'pendiente';
                        if (!empty($test)) {
                            $test_est = $test['completado'] ? 'completado' : 'en_progreso';
                        }
                        $test_colors = [
                            'pendiente'   => ['bg' => '#f1f5f9', 'color' => '#64748b', 'label' => 'Pendiente'],
                            'en_progreso' => ['bg' => '#fef9c3', 'color' => '#854d0e', 'label' => 'En progreso'],
                            'completado'  => ['bg' => '#dcfce7', 'color' => '#15803d', 'label' => 'Completado'],
                        ];
                        $tc = $test_colors[$test_est];
                        ?>
                        <span style="background:<?= $tc['bg'] ?>; color:<?= $tc['color'] ?>; padding:.25rem .7rem; border-radius:999px; font-size:.75rem; font-weight:700;">
                            <?= $tc['label'] ?>
                        </span>
                    </div>
                    <?php if (empty($test)): ?>
                        <p class="tabla__vacio"><i class="fas fa-clock"></i> El candidato no ha iniciado el test</p>
                    <?php else: ?>
                    <div class="detalle-grid detalle-grid--compact">
                        <?php if ($test['fecha_inicio']): ?>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Iniciado</span>
                            <span class="detalle-item__val"><?= date('d/m/Y H:i', strtotime($test['fecha_inicio'])) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($test['completado'] && $test['fecha_cierre']): ?>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Completado</span>
                            <span class="detalle-item__val"><?= date('d/m/Y H:i', strtotime($test['fecha_cierre'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div style="padding:.75rem 1.25rem 1rem;">
                        <a href="/admin/candidatos?ver=<?= $aspirante['id'] ?>&test=1"
                           class="btn btn--verde btn--sm" style="width:100%; justify-content:center;">
                            <i class="fas fa-eye"></i>
                            <?= $test['completado'] ? 'Ver respuestas completas' : 'Ver progreso del test' ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Documentos ───────────────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-paperclip"></i> Documentos</h2>
                        <span class="badge badge--candidato"><?= count($documentos) ?></span>
                    </div>
                    <?php if (empty($documentos)): ?>
                        <p class="tabla__vacio"><i class="fas fa-inbox"></i> Sin documentos subidos</p>
                    <?php else: ?>
                    <div class="docs-lista">
                        <?php
                        $tipos_label = [
                            'carta_motivacion'  => 'Carta de Motivación',
                            'titulo_bachiller'  => 'Título de Bachiller',
                            'carta_pastoral'    => 'Carta Pastoral',
                            'cedula_identidad'  => 'Cédula de Identidad',
                            'foto_personal'     => 'Foto Personal',
                            'otro'              => 'Otro documento',
                        ];
                        foreach ($documentos as $doc):
                        ?>
                        <div class="doc-item">
                            <div class="doc-item__icono">
                                <?php if (str_starts_with($doc['mime_type'] ?? '', 'image/')): ?>
                                    <i class="fas fa-image"></i>
                                <?php else: ?>
                                    <i class="fas fa-file-pdf"></i>
                                <?php endif; ?>
                            </div>
                            <div class="doc-item__info">
                                <div class="doc-item__tipo">
                                    <?= $tipos_label[$doc['tipo']] ?? htmlspecialchars($doc['tipo']) ?>
                                </div>
                                <div class="doc-item__meta">
                                    <?= number_format($doc['tamanio_kb']) ?> KB
                                    · <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                </div>
                            </div>
                            <div class="doc-item__acciones">
                                <form method="POST" action="/admin/candidatos" style="display:inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="accion"    value="verificar_doc">
                                    <input type="hidden" name="doc_id"    value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="_redirect" value="/admin/candidatos?ver=<?= $aspirante['id'] ?>">
                                    <button type="submit"
                                        class="btn btn--xs <?= $doc['verificado'] ? 'btn--verde' : 'btn--outline' ?>"
                                        title="<?= $doc['verificado'] ? 'Quitar verificación' : 'Marcar como verificado' ?>">
                                        <i class="fas fa-check"></i>
                                        <?= $doc['verificado'] ? 'Verificado' : 'Verificar' ?>
                                    </button>
                                </form>
                                <a href="<?= htmlspecialchars($doc['ruta']) ?>" target="_blank"
                                   class="btn btn--outline btn--xs" title="Ver documento">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Meta del registro ────────────────────── -->
                <div class="admin-panel">
                    <div class="admin-panel__header">
                        <h2><i class="fas fa-info-circle"></i> Meta</h2>
                        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                        <button type="button" class="btn btn--outline btn--sm"
                                onclick="document.getElementById('modal-reset-clave').style.display='flex'"
                                title="Resetear contraseña del candidato">
                            <i class="fas fa-key"></i> Resetear clave
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="detalle-grid detalle-grid--compact">
                        <div class="detalle-item">
                            <span class="detalle-item__label">Email de cuenta</span>
                            <span class="detalle-item__val"><?= htmlspecialchars($aspirante['email'] ?? '—') ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Último acceso</span>
                            <span class="detalle-item__val">
                                <?= $aspirante['ultimo_acceso']
                                    ? date('d/m/Y H:i', strtotime($aspirante['ultimo_acceso']))
                                    : 'Nunca' ?>
                            </span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Registrado</span>
                            <span class="detalle-item__val"><?= date('d/m/Y H:i', strtotime($aspirante['created_at'])) ?></span>
                        </div>
                        <div class="detalle-item">
                            <span class="detalle-item__label">Última actualización</span>
                            <span class="detalle-item__val"><?= date('d/m/Y H:i', strtotime($aspirante['updated_at'])) ?></span>
                        </div>
                        <?php if ($aspirante['nota_evaluador'] ?? null): ?>
                        <div class="detalle-item detalle-item--full">
                            <span class="detalle-item__label">Nota del evaluador</span>
                            <span class="detalle-item__val detalle-item__val--nota">
                                <?= htmlspecialchars($aspirante['nota_evaluador']) ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </main>
</div>

<!-- Modal: Cambiar estatus ──────────────────────────────────── -->
<div class="modal-overlay" id="modal-estatus" style="display:none">
    <div class="modal">
        <div class="modal__header">
            <h3><i class="fas fa-exchange-alt"></i> Cambiar Estatus</h3>
            <button type="button" class="modal__cerrar"
                    onclick="document.getElementById('modal-estatus').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/candidatos">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $aspirante['id'] ?>">
            <input type="hidden" name="_redirect" value="/admin/candidatos?ver=<?= $aspirante['id'] ?>">
            <div class="modal__body">
                <p class="modal__subtitulo">
                    <?= htmlspecialchars($aspirante['nombres'] . ' ' . $aspirante['apellidos']) ?>
                </p>
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
                            <input type="radio" name="estatus" value="<?= $val ?>"
                                   <?= $aspirante['estatus'] === $val ? 'checked' : '' ?>>
                            <i class="fas <?= $est['icono'] ?>"></i>
                            <?= $est['label'] ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-grupo">
                    <label for="nota">Nota del evaluador (opcional)</label>
                    <textarea name="nota" id="nota" rows="3"
                              placeholder="Observaciones..."><?= htmlspecialchars($aspirante['nota_evaluador'] ?? '') ?></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline"
                        onclick="document.getElementById('modal-estatus').style.display='none'">
                    Cancelar
                </button>
                <button type="submit" class="btn btn--verde">
                    <i class="fas fa-save"></i> Guardar cambio
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Layout dos columnas */
.ver-candidato-grid {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 1.25rem;
    align-items: start;
}
@media (max-width: 1024px) {
    .ver-candidato-grid { grid-template-columns: 1fr; }
}

/* Detalle grid */
.detalle-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    padding: 0.5rem 0;
}
.detalle-grid--compact .detalle-item { padding: 0.55rem 1.25rem; }
.detalle-item {
    padding: 0.7rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
}
.detalle-item:last-child { border-bottom: none; }
.detalle-item--full { grid-column: 1 / -1; }
.detalle-item__label {
    display: block;
    font-size: 0.72rem;
    font-weight: 600;
    color: var(--gris);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.2rem;
}
.detalle-item__val { font-size: 0.88rem; color: #1e293b; }
.detalle-item__val a { color: var(--verde); }
.detalle-item__val--nota {
    background: #f8fafc;
    border-left: 3px solid var(--dorado);
    padding: 0.5rem 0.75rem;
    border-radius: 0 0.3rem 0.3rem 0;
    font-style: italic;
    display: block;
    margin-top: 0.25rem;
}

/* Flujo del proceso */
.flujo-proceso { padding: 0.75rem 0; }
.flujo-paso {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}
.flujo-paso:last-child { border-bottom: none; }
.flujo-paso__icono {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    flex-shrink: 0;
    background: #f1f5f9;
    color: #94a3b8;
}
.flujo-paso--aprobado .flujo-paso__icono { background: var(--verde-light); color: var(--verde); }
.flujo-paso--rechazado .flujo-paso__icono { background: #fee2e2; color: #ef4444; }
.flujo-paso--en_proceso .flujo-paso__icono { background: var(--dorado-light); color: var(--dorado-dark); }
.flujo-paso__info { flex: 1; }
.flujo-paso__nombre { font-size: 0.85rem; font-weight: 600; color: #1e293b; }
.flujo-paso__est { font-size: 0.75rem; color: var(--gris); margin-top: 0.1rem; }
.flujo-paso__nota {
    font-size: 0.75rem;
    color: #64748b;
    font-style: italic;
    margin-top: 0.25rem;
    padding: 0.3rem 0.5rem;
    background: #f8fafc;
    border-radius: 0.25rem;
}

/* Documentos */
.docs-lista { padding: 0.5rem 0; }
.doc-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #f1f5f9;
}
.doc-item:last-child { border-bottom: none; }
.doc-item__icono {
    width: 2.2rem;
    height: 2.2rem;
    background: #fef2f2;
    color: #ef4444;
    border-radius: 0.35rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.doc-item__info { flex: 1; min-width: 0; }
.doc-item__tipo { font-size: 0.82rem; font-weight: 600; color: #1e293b; }
.doc-item__meta { font-size: 0.72rem; color: var(--gris); }
.doc-item__acciones { display: flex; align-items: center; gap: 0.4rem; }

/* Movilidad */
.movilidad-ok { color: var(--verde); font-weight: 600; }
.movilidad-no { color: #ef4444; font-weight: 600; }
</style>

<!-- Modal: Actualizar etapa del flujo ───────────────────────── -->
<div class="modal-overlay" id="modal-flujo" style="display:none">
    <div class="modal">
        <div class="modal__header">
            <h3><i class="fas fa-tasks"></i> <span id="flujo-modal-titulo">Actualizar Etapa</span></h3>
            <button type="button" class="modal__cerrar" onclick="cerrarModalFlujo()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/candidatos">
            <?= csrf_field() ?>
            <input type="hidden" name="accion"       value="flujo">
            <input type="hidden" name="aspirante_id" value="<?= $aspirante['id'] ?>">
            <input type="hidden" name="etapa"        id="flujo-etapa" value="">
            <input type="hidden" name="_redirect"    value="/admin/candidatos?ver=<?= $aspirante['id'] ?>">
            <div class="modal__body">
                <p class="modal__subtitulo">
                    <?= htmlspecialchars($aspirante['nombres'] . ' ' . $aspirante['apellidos']) ?>
                </p>

                <div class="form-grupo">
                    <label>Nuevo estatus de la etapa</label>
                    <div class="estatus-opciones">
                        <?php foreach ([
                            'pendiente'  => ['label' => 'Pendiente',   'icono' => 'fa-clock',        'color' => 'gris'],
                            'en_proceso' => ['label' => 'En proceso',  'icono' => 'fa-spinner',      'color' => 'dorado'],
                            'aprobado'   => ['label' => 'Aprobado',    'icono' => 'fa-check-circle', 'color' => 'verde'],
                            'rechazado'  => ['label' => 'Rechazado',   'icono' => 'fa-times-circle', 'color' => 'rojo'],
                        ] as $val => $fopt): ?>
                        <label class="estatus-opcion estatus-opcion--<?= $fopt['color'] ?>">
                            <input type="radio" name="estatus" id="flujo-est-<?= $val ?>" value="<?= $val ?>">
                            <i class="fas <?= $fopt['icono'] ?>"></i>
                            <?= $fopt['label'] ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="flujo-notas">Nota del evaluador (opcional)</label>
                    <textarea name="notas" id="flujo-notas" rows="3"
                              placeholder="Observaciones sobre esta etapa..."></textarea>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline" onclick="cerrarModalFlujo()">Cancelar</button>
                <button type="submit" class="btn btn--verde">
                    <i class="fas fa-save"></i> Guardar etapa
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* Botón editar etapa */
.flujo-btn-editar {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 1px solid #e2e8f0;
    background: white;
    color: #94a3b8;
    font-size: .65rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s;
    flex-shrink: 0;
}
.flujo-btn-editar:hover {
    background: var(--verde);
    color: white;
    border-color: var(--verde);
}
.estatus-opcion--gris { border-color: #94a3b8 !important; }
.estatus-opcion--gris:has(input:checked) { background: #f1f5f9 !important; color: #475569 !important; }
</style>

<script>
// Datos actuales del flujo (para pre-poblar modal)
const flujoActual = <?= json_encode($flujo_idx) ?>;

function editarEtapa(clave, label) {
    document.getElementById('flujo-etapa').value        = clave;
    document.getElementById('flujo-modal-titulo').textContent = label;

    const actual = flujoActual[clave] || {};

    // Pre-seleccionar estatus actual
    const estActual = actual.estatus || 'pendiente';
    const radio = document.getElementById('flujo-est-' + estActual);
    if (radio) radio.checked = true;

    // Pre-llenar notas
    document.getElementById('flujo-notas').value = actual.notas || '';

    document.getElementById('modal-flujo').style.display = 'flex';
}

function cerrarModalFlujo() {
    document.getElementById('modal-flujo').style.display = 'none';
}

// Cerrar modales al hacer click fuera
document.getElementById('modal-estatus')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
document.getElementById('modal-flujo')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
document.getElementById('modal-reset-clave')?.addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<!-- Modal: Resetear contraseña del candidato (solo admin) ──── -->
<div class="modal-overlay" id="modal-reset-clave" style="display:none">
    <div class="modal">
        <div class="modal__header">
            <h3><i class="fas fa-key"></i> Resetear Contraseña</h3>
            <button type="button" class="modal__cerrar"
                    onclick="document.getElementById('modal-reset-clave').style.display='none'">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="/admin/candidatos">
            <?= csrf_field() ?>
            <input type="hidden" name="accion"     value="reset_clave">
            <input type="hidden" name="usuario_id" value="<?= $aspirante['usuario_id'] ?>">
            <input type="hidden" name="_redirect"  value="/admin/candidatos?ver=<?= $aspirante['id'] ?>">
            <div class="modal__body">
                <p class="modal__subtitulo">
                    <?= htmlspecialchars($aspirante['nombres'] . ' ' . $aspirante['apellidos']) ?>
                    <br><small style="color:var(--gris)"><?= htmlspecialchars($aspirante['email'] ?? '') ?></small>
                </p>
                <div class="aviso-info aviso-info--dorado" style="margin-bottom:1rem">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>Establece una contraseña temporal. Comunícasela al candidato por otro medio.</div>
                </div>
                <div class="form-grupo">
                    <label for="nueva_clave">Nueva contraseña <span class="req">*</span></label>
                    <input type="text" id="nueva_clave" name="nueva_clave" required
                           minlength="8" placeholder="Mínimo 8 caracteres"
                           autocomplete="off">
                    <small class="form-ayuda">Usa texto plano para que puedas comunicársela al candidato.</small>
                </div>
            </div>
            <div class="modal__footer">
                <button type="button" class="btn btn--outline"
                        onclick="document.getElementById('modal-reset-clave').style.display='none'">
                    Cancelar
                </button>
                <button type="submit" class="btn btn--verde">
                    <i class="fas fa-save"></i> Guardar nueva clave
                </button>
            </div>
        </form>
    </div>
</div>
