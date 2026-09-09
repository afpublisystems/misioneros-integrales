<?php
/**
 * Sub-navegación del módulo de finanzas.
 * Los estilos aquí son solo los que app.css no cubre: las pestañas,
 * el badge de saldo y unos utilitarios de texto. Todo lo demás
 * (paneles, tablas, formularios, modales) usa las clases de app.css.
 */
$uri_fin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$secciones = [
    '/admin/finanzas'             => ['fa-chart-pie',            'Resumen'],
    '/admin/finanzas/movimientos' => ['fa-right-left',           'Ingresos y gastos'],
    '/admin/finanzas/matriculas'  => ['fa-graduation-cap',       'Matrículas y becas'],
    '/admin/finanzas/prestamos'   => ['fa-hand-holding-dollar',  'Préstamos'],
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
/* ── Pestañas del módulo ─────────────────────────────────── */
.fin-tabs {
    display: flex; gap: 0.25rem; flex-wrap: wrap;
    border-bottom: 1px solid #e5e7eb; margin-bottom: 1.75rem;
}
.fin-tab {
    padding: 0.6rem 1rem; color: var(--gris); text-decoration: none;
    font-size: 0.85rem; font-weight: 600; border-bottom: 2px solid transparent;
    display: flex; align-items: center; gap: 0.4rem; white-space: nowrap;
    transition: var(--transicion);
}
.fin-tab:hover  { color: var(--gris-dark); background: #f8fafc; }
.fin-tab i      { font-size: 0.8rem; }
.fin-tab.activo { color: var(--verde); border-bottom-color: var(--verde); }

/* ── Rejilla de KPIs que se adapta a la cantidad ─────────── */
.fin-kpis {
    display: grid; gap: 1rem; margin-bottom: 1.75rem;
    grid-template-columns: repeat(auto-fit, minmax(205px, 1fr));
}
.kpi-card__pie {
    font-size: 0.7rem; color: var(--gris);
    margin-top: 0.3rem; line-height: 1.35;
}
.kpi-card--azul   { border-left-color: #0ea5e9; }
.kpi-card--azul   .kpi-card__icono { background: #e0f2fe; color: #0ea5e9; }
.kpi-card--azul   .kpi-card__num   { color: #0ea5e9; }
.kpi-card--neutro { border-left-color: #94a3b8; }
.kpi-card--neutro .kpi-card__icono { background: #f1f5f9; color: #64748b; }
.kpi-card--neutro .kpi-card__num   { color: #334155; }
.kpi-card__num { font-size: 1.45rem; }

/* ── Utilitarios de texto ────────────────────────────────── */
.texto-muted { color: var(--gris); font-size: 0.8rem; }
.texto-verde { color: var(--verde); font-weight: 600; }
.texto-rojo  { color: #dc2626; font-weight: 600; }

/* ── Celdas de dinero: alineadas y sin partirse ──────────── */
.tabla td.num, .tabla th.num { text-align: right; white-space: nowrap; }
.tabla td.fecha { white-space: nowrap; }

/* ── Barra de progreso ───────────────────────────────────── */
.fin-barra {
    background: #f1f5f9; border-radius: 999px; height: 6px;
    overflow: hidden; margin-top: 0.35rem; min-width: 70px;
}
.fin-barra__fill { background: var(--verde); height: 100%; border-radius: 999px; }

/* ── Fila de dato suelto (cuadros sin tabla) ─────────────── */
.fin-dato {
    display: flex; justify-content: space-between; align-items: baseline;
    gap: 1rem; padding: 0.6rem 0; border-bottom: 1px solid #f1f5f9;
}
.fin-dato:last-child { border-bottom: none; }
.fin-dato__label { font-size: 0.85rem; color: var(--gris-dark); }
.fin-dato__valor { font-size: 0.95rem; font-weight: 700; white-space: nowrap; }
.fin-dato--total { border-top: 2px solid #e5e7eb; margin-top: 0.35rem; padding-top: 0.8rem; }
.fin-dato--total .fin-dato__label { font-weight: 700; }
.fin-dato--total .fin-dato__valor { font-size: 1.15rem; color: var(--verde); }
.fin-dato--alerta .fin-dato__valor { color: #dc2626; }

/* ── Nota explicativa larga (form-ayuda es flex y las parte) ── */
.fin-nota {
    display: block; font-size: 0.78rem; color: var(--gris);
    line-height: 1.55; margin-top: 0.4rem;
}
.fin-nota strong { color: var(--gris-dark); }

/* ── Panel vacío ─────────────────────────────────────────── */
.fin-vacio {
    text-align: center; padding: 2.5rem 1.5rem; color: var(--gris);
    font-size: 0.85rem; font-style: italic;
}
.fin-vacio i { display: block; font-size: 1.75rem; margin-bottom: 0.6rem; color: #d1d5db; }

/* ── Badges que app.css no trae ──────────────────────────── */
.badge--info      { background: #dbeafe; color: #1d4ed8; }
.badge--warning   { background: #fef9c3; color: #92400e; }
.badge--exito     { background: #dcfce7; color: #166534; }
.badge--peligro   { background: #fee2e2; color: #991b1b; }
.badge--neutro    { background: #f1f5f9; color: var(--gris); }
.badge--xs        { padding: 0.15rem 0.5rem; font-size: 0.65rem; }

.btn--xs {
    padding: 0.3rem 0.6rem; font-size: 0.75rem;
    display: inline-flex; align-items: center; gap: 0.3rem;
}
.btn--peligro {
    background: #dc2626; color: var(--blanco); border: 2px solid #dc2626;
}
.btn--peligro:hover { background: #b91c1c; border-color: #b91c1c; }

/* ── El modal de app.css nace oculto; se muestra con .abierto ── */
.modal-overlay { display: none; }
.modal-overlay.abierto { display: flex; }
.modal--ancho { max-width: 640px; }
.modal__body--scroll { max-height: 70vh; overflow-y: auto; }

/* ── Barra de acciones del encabezado ────────────────────── */
.fin-acciones { display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; }

@media (max-width: 640px) {
    .fin-tab { padding: 0.55rem 0.7rem; font-size: 0.8rem; }
}
</style>
