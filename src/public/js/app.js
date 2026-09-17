// app.js — Misioneros Integrales

document.addEventListener('DOMContentLoaded', () => {

    // ── Menú móvil ──────────────────────────────────────────
    const toggle  = document.getElementById('navbar-toggle');
    const menu    = document.getElementById('navbar-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            menu.classList.toggle('abierto');
        });
    }

    // ── Menú del panel admin en móvil ───────────────────────
    const adminToggle  = document.getElementById('admin-menu-toggle');
    const adminSidebar = document.getElementById('admin-sidebar');
    const adminOverlay = document.getElementById('admin-overlay');
    const adminCerrar  = document.getElementById('admin-menu-cerrar');

    if (adminToggle && adminSidebar && adminOverlay) {
        const abrirMenu = (abierto) => {
            adminSidebar.classList.toggle('abierto', abierto);
            adminOverlay.classList.toggle('abierto', abierto);
            document.body.classList.toggle('menu-abierto', abierto);
            adminToggle.setAttribute('aria-expanded', abierto ? 'true' : 'false');
        };

        adminToggle.addEventListener('click', () => {
            abrirMenu(!adminSidebar.classList.contains('abierto'));
        });
        adminOverlay.addEventListener('click', () => abrirMenu(false));
        if (adminCerrar) adminCerrar.addEventListener('click', () => abrirMenu(false));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') abrirMenu(false);
        });
    }

    // ── Cerrar alertas ──────────────────────────────────────
    document.querySelectorAll('.alerta [data-cerrar]').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.alerta').remove();
        });
    });

    // ── Auto-cerrar alertas de éxito ────────────────────────
    const alertaExito = document.querySelector('.alerta--exito');
    if (alertaExito) {
        setTimeout(() => {
            alertaExito.style.opacity = '0';
            alertaExito.style.transition = 'opacity 0.5s';
            setTimeout(() => alertaExito.remove(), 500);
        }, 4000);
    }

});
