<?php
/**
 * Vista: /admin/colaboradores
 * Gestión de registros de colaboradores (Sé Parte del Movimiento)
 */

$tipoLabel = [
    'economico'   => ['ico' => 'fa-dollar-sign',     'color' => '#16a34a', 'label' => 'Apoyo Económico'],
    'especie'      => ['ico' => 'fa-box-open',        'color' => '#d97706', 'label' => 'Donación en Especie'],
    'servicios'    => ['ico' => 'fa-briefcase',       'color' => '#2563eb', 'label' => 'Servicios Profesionales'],
    'voluntariado' => ['ico' => 'fa-hands-helping',   'color' => '#7c3aed', 'label' => 'Voluntariado'],
    'otro'         => ['ico' => 'fa-question-circle', 'color' => '#6b7280', 'label' => 'Otro'],
];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="admin-wrap">
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main">

        <!-- Header -->
        <div class="admin-topbar">
            <div>
                <h1 class="admin-topbar__titulo">
                    <i class="fas fa-handshake"></i> Colaboradores
                </h1>
                <p class="admin-topbar__sub">Personas y organizaciones que desean apoyar el programa</p>
            </div>
        </div>

        <?php if ($flash): ?>
        <div class="alert alert--<?= $flash['tipo'] ?>">
            <i class="fas <?= $flash['tipo'] === 'exito' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
        <?php endif; ?>

        <!-- KPIs -->
        <div class="colab-kpis">
            <div class="colab-kpi">
                <span class="colab-kpi__num"><?= $total ?></span>
                <span class="colab-kpi__label">Total registros</span>
            </div>
            <div class="colab-kpi colab-kpi--warn">
                <span class="colab-kpi__num"><?= $pendientes ?></span>
                <span class="colab-kpi__label">Pendientes revisión</span>
            </div>
            <div class="colab-kpi colab-kpi--ok">
                <span class="colab-kpi__num"><?= $aprobados ?></span>
                <span class="colab-kpi__label">Aprobados</span>
            </div>
        </div>

        <!-- Filtros -->
        <div class="colab-filtros">
            <a href="/admin/colaboradores"
               class="colab-filtro <?= $filtro === 'todos' ? 'activo' : '' ?>">
                <i class="fas fa-list"></i> Todos (<?= $total ?>)
            </a>
            <a href="/admin/colaboradores?filtro=pendientes"
               class="colab-filtro <?= $filtro === 'pendientes' ? 'activo' : '' ?>">
                <i class="fas fa-clock"></i> Pendientes (<?= $pendientes ?>)
            </a>
            <a href="/admin/colaboradores?filtro=aprobados"
               class="colab-filtro <?= $filtro === 'aprobados' ? 'activo' : '' ?>">
                <i class="fas fa-check-circle"></i> Aprobados (<?= $aprobados ?>)
            </a>
        </div>

        <!-- Tabla -->
        <?php if (empty($colaboradores)): ?>
        <div class="colab-vacio">
            <i class="fas fa-inbox"></i>
            <p>No hay registros <?= $filtro !== 'todos' ? 'en este filtro' : 'aún' ?>.</p>
        </div>
        <?php else: ?>
        <div class="colab-tabla-wrap">
            <table class="colab-tabla">
                <thead>
                    <tr>
                        <th>Persona / Org.</th>
                        <th>Email</th>
                        <th>Tipo de colaboración</th>
                        <th>Mensaje</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($colaboradores as $c):
                    $t = $tipoLabel[$c['tipo']] ?? $tipoLabel['otro'];
                ?>
                <tr class="<?= $c['aprobado'] ? '' : 'colab-fila--pendiente' ?>">
                    <td>
                        <div class="colab-persona">
                            <div class="colab-persona__inicial">
                                <?= strtoupper(mb_substr($c['nombre'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($c['nombre']) ?></strong>
                                <?php if ($c['organizacion']): ?>
                                <span class="colab-org"><?= htmlspecialchars($c['organizacion']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="colab-email">
                            <?= htmlspecialchars($c['email']) ?>
                        </a>
                    </td>
                    <td>
                        <span class="colab-tipo-badge" style="--badge-color:<?= $t['color'] ?>">
                            <i class="fas <?= $t['ico'] ?>"></i>
                            <?= $t['label'] ?>
                        </span>
                    </td>
                    <td class="colab-msg">
                        <?php if ($c['mensaje']): ?>
                        <button class="colab-ver-msg" onclick="verMensaje(this)"
                                data-msg="<?= htmlspecialchars($c['mensaje']) ?>">
                            <i class="fas fa-comment-alt"></i> Ver
                        </button>
                        <?php else: ?>
                        <span class="colab-sin-msg">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="colab-fecha">
                        <?= date('d/m/Y', strtotime($c['created_at'])) ?><br>
                        <small><?= date('H:i', strtotime($c['created_at'])) ?></small>
                    </td>
                    <td>
                        <?php if ($c['aprobado']): ?>
                        <span class="colab-estatus colab-estatus--ok">
                            <i class="fas fa-check-circle"></i> Aprobado
                        </span>
                        <?php else: ?>
                        <span class="colab-estatus colab-estatus--pend">
                            <i class="fas fa-clock"></i> Pendiente
                        </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="colab-acciones">
                            <?php if (!$c['aprobado']): ?>
                            <form method="POST" action="/admin/colaboradores" style="display:inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id"     value="<?= $c['id'] ?>">
                                <input type="hidden" name="accion" value="aprobar">
                                <button type="submit" class="colab-btn colab-btn--ok"
                                        title="Marcar como aprobado">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="/admin/colaboradores" style="display:inline"
                                  onsubmit="return confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id"     value="<?= $c['id'] ?>">
                                <input type="hidden" name="accion" value="rechazar">
                                <button type="submit" class="colab-btn colab-btn--del" title="Eliminar registro">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </main>
</div>

<!-- Modal mensaje -->
<div id="modal-msg" class="colab-modal-overlay" style="display:none" onclick="cerrarMsg(event)">
    <div class="colab-modal">
        <div class="colab-modal__header">
            <i class="fas fa-comment-alt"></i> Mensaje del colaborador
            <button class="colab-modal__cerrar" onclick="cerrarModalMsg()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="modal-msg-body" class="colab-modal__body"></div>
    </div>
</div>

<style>
/* ── LAYOUT ───────────────────────────────────────── */
.admin-wrap  { display: flex; min-height: 100vh; background: #f8fafc; }
.admin-main  { flex: 1; padding: 2rem 2.5rem; overflow-y: auto; }
.admin-topbar { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1.5rem; }
.admin-topbar__titulo { font-size:1.5rem; font-weight:800; color:#1a1a1a; display:flex; align-items:center; gap:.6rem; margin:0; }
.admin-topbar__titulo i { color:var(--verde); }
.admin-topbar__sub { color:#6b7280; font-size:.85rem; margin:.25rem 0 0; }

/* Flash */
.alert { display:flex; align-items:center; gap:.7rem; padding:.9rem 1.25rem;
         border-radius:12px; font-size:.88rem; font-weight:600; margin-bottom:1.5rem; }
.alert--exito  { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
.alert--error  { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }

/* ── KPIs ─────────────────────────────────────────── */
.colab-kpis { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
.colab-kpi  { background:#fff; border:1px solid #e5e7eb; border-radius:16px;
              padding:1.25rem 1.5rem; text-align:center;
              box-shadow:0 2px 8px rgba(0,0,0,.05); }
.colab-kpi--warn { border-top:3px solid #f59e0b; }
.colab-kpi--ok   { border-top:3px solid #16a34a; }
.colab-kpi__num  { display:block; font-size:2.2rem; font-weight:900; color:#111; }
.colab-kpi--warn .colab-kpi__num { color:#d97706; }
.colab-kpi--ok   .colab-kpi__num { color:#16a34a; }
.colab-kpi__label { font-size:.78rem; color:#6b7280; font-weight:500; text-transform:uppercase; letter-spacing:.4px; }

/* ── FILTROS ──────────────────────────────────────── */
.colab-filtros { display:flex; gap:.75rem; margin-bottom:1.25rem; flex-wrap:wrap; }
.colab-filtro  { padding:.55rem 1.1rem; border-radius:50px;
                 font-size:.83rem; font-weight:600; color:#374151;
                 background:#fff; border:1px solid #e5e7eb;
                 text-decoration:none; display:flex; align-items:center; gap:.4rem;
                 transition:all .18s; }
.colab-filtro:hover  { border-color:var(--verde); color:var(--verde); }
.colab-filtro.activo { background:var(--verde); color:#fff; border-color:var(--verde); }

/* ── TABLA ────────────────────────────────────────── */
.colab-tabla-wrap { background:#fff; border-radius:16px; border:1px solid #e5e7eb;
                    overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.05); }
.colab-tabla { width:100%; border-collapse:collapse; font-size:.85rem; }
.colab-tabla thead tr { background:#f1f5f9; }
.colab-tabla th { padding:.75rem 1rem; text-align:left; font-size:.75rem;
                  font-weight:700; text-transform:uppercase; letter-spacing:.5px;
                  color:#6b7280; border-bottom:1px solid #e5e7eb; }
.colab-tabla td { padding:.9rem 1rem; border-bottom:1px solid #f1f5f9;
                  vertical-align:middle; color:#374151; }
.colab-tabla tr:last-child td { border-bottom:none; }
.colab-tabla tr:hover td { background:#fafafa; }
.colab-fila--pendiente td:first-child { border-left:3px solid #f59e0b; }

/* Persona */
.colab-persona { display:flex; align-items:center; gap:.75rem; }
.colab-persona__inicial {
    width:36px; height:36px; border-radius:50%;
    background:linear-gradient(135deg,var(--verde),var(--verde-dark));
    color:#fff; font-weight:800; font-size:1rem;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.colab-persona strong { display:block; font-size:.88rem; }
.colab-org { font-size:.75rem; color:#6b7280; }
.colab-email { color:var(--verde); text-decoration:none; font-size:.82rem; }
.colab-email:hover { text-decoration:underline; }

/* Tipo badge */
.colab-tipo-badge {
    display:inline-flex; align-items:center; gap:.35rem;
    padding:.3rem .75rem; border-radius:50px; font-size:.75rem; font-weight:700;
    background:color-mix(in srgb, var(--badge-color) 12%, white);
    color:var(--badge-color);
    border:1px solid color-mix(in srgb, var(--badge-color) 25%, white);
}

/* Mensaje */
.colab-ver-msg {
    background:#f1f5f9; border:1px solid #e2e8f0; border-radius:8px;
    padding:.35rem .7rem; font-size:.78rem; font-weight:600; color:#475569;
    cursor:pointer; display:inline-flex; align-items:center; gap:.35rem;
    transition:.15s;
}
.colab-ver-msg:hover { background:#e0e7ef; }
.colab-sin-msg { color:#d1d5db; }
.colab-fecha { font-size:.82rem; color:#6b7280; }
.colab-fecha small { color:#9ca3af; }

/* Estatus */
.colab-estatus { display:inline-flex; align-items:center; gap:.35rem;
                 padding:.3rem .75rem; border-radius:50px; font-size:.75rem; font-weight:700; }
.colab-estatus--ok   { background:#dcfce7; color:#166534; }
.colab-estatus--pend { background:#fef9c3; color:#92400e; }

/* Acciones */
.colab-acciones { display:flex; gap:.4rem; }
.colab-btn { width:32px; height:32px; border-radius:8px; border:none;
             cursor:pointer; font-size:.85rem; display:flex; align-items:center;
             justify-content:center; transition:.15s; }
.colab-btn--ok  { background:#dcfce7; color:#166534; }
.colab-btn--ok:hover  { background:#bbf7d0; }
.colab-btn--del { background:#fee2e2; color:#991b1b; }
.colab-btn--del:hover { background:#fecaca; }

/* Vacío */
.colab-vacio { text-align:center; padding:4rem 2rem; color:#9ca3af; }
.colab-vacio i { font-size:3rem; margin-bottom:1rem; display:block; }
.colab-vacio p { font-size:.95rem; }

/* ── MODAL ────────────────────────────────────────── */
.colab-modal-overlay {
    position:fixed; inset:0; background:rgba(0,0,0,.45);
    display:flex; align-items:center; justify-content:center; z-index:1000;
}
.colab-modal {
    background:#fff; border-radius:18px; width:min(500px,90vw);
    box-shadow:0 20px 60px rgba(0,0,0,.25); overflow:hidden;
}
.colab-modal__header {
    display:flex; align-items:center; justify-content:space-between;
    padding:1rem 1.5rem; background:#f8fafc; border-bottom:1px solid #e5e7eb;
    font-weight:700; font-size:.95rem; color:#374151;
}
.colab-modal__header i { color:var(--verde); }
.colab-modal__cerrar {
    background:none; border:none; cursor:pointer; color:#9ca3af;
    font-size:1.1rem; padding:.2rem; transition:.15s;
}
.colab-modal__cerrar:hover { color:#374151; }
.colab-modal__body { padding:1.5rem; font-size:.9rem; color:#374151; line-height:1.7; }

/* Responsive */
@media (max-width: 768px) {
    .admin-main { padding:1rem; }
    .colab-kpis { grid-template-columns:1fr 1fr; }
    .colab-tabla-wrap { overflow-x:auto; }
    .colab-tabla { min-width:750px; }
}
</style>

<script>
function verMensaje(btn) {
    document.getElementById('modal-msg-body').textContent = btn.dataset.msg;
    document.getElementById('modal-msg').style.display = 'flex';
}
function cerrarModalMsg() {
    document.getElementById('modal-msg').style.display = 'none';
}
function cerrarMsg(e) {
    if (e.target === document.getElementById('modal-msg')) cerrarModalMsg();
}
</script>
