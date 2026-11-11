<?php
/**
 * Estilos compartidos de los informes de prácticas.
 * El semanal y el de cierre son el mismo documento con distinto
 * alcance, así que el formato vive en un solo sitio: si cambia la
 * identidad, cambia en los dos a la vez.
 */
?>
<style>
    .inf-head { background: #03140E; color: #fff; border-radius: 10px; padding: 16px 20px; display: flex; align-items: center; gap: 18px; }
    .inf-head img { height: 58px; }
    .inf-head .t1 { font-size: 11px; letter-spacing: .12em; text-transform: uppercase; color: #EDBE55; font-weight: 700; }
    .inf-head h1 { margin: 2px 0; font-size: 22px; font-weight: 800; }
    .inf-head .t2 { font-size: 12px; opacity: .85; }
    .inf h2 { font-size: 15px; color: #03140E; border-bottom: 2px solid #EDBE55; padding-bottom: 3px; margin: 22px 0 8px; }
    .inf h3 { font-size: 13px; margin: 0 0 2px; color: #03140E; }
    .inf-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-top: 14px; }
    .inf-kpi { border: 1px solid #d8d2bd; border-radius: 8px; padding: 9px 10px; background: #fbf8ef; }
    .inf-kpi b { display: block; font-size: 26px; font-weight: 800; color: #03140E; line-height: 1.1; }
    .inf-kpi span { font-size: 11px; color: #5a6660; }
    .inf table { width: 100%; border-collapse: collapse; margin: 4px 0 6px; }
    .inf th, .inf td { padding: 6px 8px; border-bottom: 1px solid #e3dfd0; text-align: right; }
    .inf th:first-child, .inf td:first-child { text-align: left; }
    .inf thead th { background: #03140E; color: #fff; font-weight: 600; }
    .inf tfoot td { font-weight: 700; background: #f3ecd6; }
    .inf-iglesia { break-inside: avoid; border-left: 4px solid #EDBE55; padding: 2px 0 2px 12px; margin: 14px 0; }
    .inf-iglesia .lugar { font-size: 11px; color: #5a6660; }
    .inf ul { margin: 6px 0; padding-left: 18px; }
    .inf li { margin: 3px 0; line-height: 1.45; }
    .inf .nota { font-size: 11px; color: #5a6660; margin: 2px 0; }
    .inf footer { margin-top: 20px; font-size: 10px; color: #7a847f; border-top: 1px solid #e3dfd0; padding-top: 6px; }
    @media print {
        .inf-head, .inf thead th, .inf tfoot td, .inf-kpi { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
