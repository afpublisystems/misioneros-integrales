<?php
/**
 * Sub-navegación y estilos compartidos del módulo de finanzas.
 * Se incluye al inicio de cada vista de /admin/finanzas.
 */
$uri_fin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$secciones = [
    '/admin/finanzas'             => ['fa-chart-pie',    'Resumen'],
    '/admin/finanzas/movimientos' => ['fa-right-left',   'Ingresos y gastos'],
    '/admin/finanzas/matriculas'  => ['fa-graduation-cap','Matrículas y becas'],
    '/admin/finanzas/prestamos'   => ['fa-hand-holding-dollar', 'Préstamos'],
];
?>
<nav class="fin-tabs">
    <?php foreach ($secciones as $ruta => [$icono, $texto]): ?>
    <a href="<?= $ruta ?>" class="fin-tab <?= $uri_fin === $ruta ? 'activo' : '' ?>">
        <i class="fas <?= $icono ?>"></i> <?= $texto ?>
    </a>
    <?php endforeach; ?>
</nav>

<style>
.fin-tabs {
    display:flex; gap:.35rem; flex-wrap:wrap;
    border-bottom:1px solid #1e3a2a; margin-bottom:1.5rem;
}
.fin-tab {
    padding:.65rem 1.1rem; color:#9ca3af; text-decoration:none;
    font-size:.9rem; border-bottom:2px solid transparent;
    display:flex; align-items:center; gap:.45rem; white-space:nowrap;
}
.fin-tab:hover  { color:#d1d5db; }
.fin-tab.activo { color:#22c55e; border-bottom-color:#22c55e; font-weight:600; }

.texto-verde { color:#22c55e; }
.texto-rojo  { color:#ef4444; }
.texto-muted { color:#9ca3af; font-size:.85em; }

.fin-grid {
    display:grid; gap:1rem;
    grid-template-columns:repeat(auto-fit, minmax(190px, 1fr));
    margin-bottom:1.5rem;
}
.fin-kpi {
    background:#0f2419; border:1px solid #1e3a2a; border-radius:12px;
    padding:1.1rem 1.25rem;
}
.fin-kpi__label {
    display:block; font-size:.78rem; color:#9ca3af;
    text-transform:uppercase; letter-spacing:.04em; margin-bottom:.4rem;
}
.fin-kpi__num  { font-size:1.6rem; font-weight:800; color:#f0f6f1; line-height:1.1; }
.fin-kpi__pie  { display:block; font-size:.8rem; color:#6b7280; margin-top:.35rem; }
.fin-kpi--ok    .fin-kpi__num { color:#22c55e; }
.fin-kpi--alerta .fin-kpi__num { color:#ef4444; }
.fin-kpi--aviso  .fin-kpi__num { color:#f59e0b; }

.progress-bar-wrap {
    background:#1e3a2a; border-radius:4px; height:6px;
    width:100%; overflow:hidden; display:block; margin-top:.5rem;
}
.progress-bar-fill { background:#22c55e; height:100%; border-radius:4px; }

.badge--info      { background:#0ea5e9; color:#fff; }
.badge--warning   { background:#f59e0b; color:#fff; }
.badge--success   { background:#22c55e; color:#fff; }
.badge--danger    { background:#ef4444; color:#fff; }
.badge--secondary { background:#374151; color:#d1d5db; }
.btn--success { background:#22c55e; color:#fff; border-color:#22c55e; }
.btn--danger  { background:#ef4444; color:#fff; border-color:#ef4444; }
.btn--xs { padding:.2rem .5rem; font-size:.78rem; }

.form-row { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
.form-row--3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; }
@media (max-width:700px) {
    .form-row, .form-row--3 { grid-template-columns:1fr; }
}
.admin-empty { text-align:center; padding:2rem; color:#6b7280; }

.modal { position:fixed; inset:0; z-index:1000; display:flex; align-items:center; justify-content:center; }
.modal__overlay { position:absolute; inset:0; background:rgba(0,0,0,.6); }
.modal__box {
    position:relative; z-index:1; background:#0f2419; border:1px solid #1e3a2a;
    border-radius:12px; width:90%; max-height:90vh; overflow-y:auto;
}
.modal__head {
    display:flex; align-items:center; justify-content:space-between;
    padding:1.25rem 1.5rem; border-bottom:1px solid #1e3a2a;
}
.modal__head h3 { margin:0; font-size:1.1rem; color:#f0f6f1; }
.modal__cerrar { background:none; border:none; color:#9ca3af; font-size:1.4rem; cursor:pointer; }
.modal__body { padding:1.25rem 1.5rem; }
.modal__foot {
    padding:1rem 1.5rem; border-top:1px solid #1e3a2a;
    display:flex; justify-content:flex-end; gap:.75rem;
}
.ayuda { font-size:.8rem; color:#6b7280; margin-top:.3rem; display:block; }
</style>
