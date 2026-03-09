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
