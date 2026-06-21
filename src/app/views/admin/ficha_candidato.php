<?php
/**
 * Ficha de Postulante — hoja imprimible / exportable a PDF
 * Pensada para compartir con aliados y personas que quieran apoyar.
 * Se renderiza sin layout (renderParcial): es un documento HTML completo.
 */

// ── Etiquetas legibles ────────────────────────────────────────
$civil = [
    'soltero' => 'Soltero/a', 'casado' => 'Casado/a',
    'viudo' => 'Viudo/a', 'divorciado' => 'Divorciado/a',
];
$nivel = [
    'bachiller' => 'Bachiller', 'tecnico' => 'Técnico',
    'universitario' => 'Universitario', 'postgrado' => 'Postgrado',
];
$etapas_label = [
    'solicitud_formal'      => 'Solicitud formal',
    'evaluacion_documental' => 'Evaluación documental',
    'test_vocacional'       => 'Test vocacional',
    'entrevista_personal'   => 'Entrevista personal',
    'confirmacion_admision' => 'Confirmación de admisión',
];
$estatus_label = [
    'pendiente' => 'Pendiente', 'en_proceso' => 'En proceso',
    'aprobado' => 'Aprobado', 'rechazado' => 'No aprobado',
];

// Indexar flujo por etapa
$flujo_idx = [];
foreach ($flujo as $f) { $flujo_idx[$f['etapa']] = $f; }

$nombre_completo = trim($aspirante['nombres'] . ' ' . $aspirante['apellidos']);

// ── Resumen de presentación (armado con datos reales) ─────────
$g = $aspirante['genero'] === 'femenino' ? 'a' : 'o';
$civil_genero = [
    'soltero' => "solter{$g}", 'casado' => "casad{$g}",
    'viudo' => "viud{$g}", 'divorciado' => "divorciad{$g}",
];
$frases = [];
$frases[] = "{$nombre_completo}, de {$aspirante['edad']} años, "
    . "es " . ($civil_genero[$aspirante['estado_civil']] ?? $aspirante['estado_civil'])
    . " y reside en " . htmlspecialchars($aspirante['ciudad_origen'])
    . ($aspirante['estado_origen'] ? ", estado " . htmlspecialchars($aspirante['estado_origen']) : '') . ".";
$frases[] = "Es miembro de " . htmlspecialchars($aspirante['iglesia'])
    . ", bajo el pastorado de " . htmlspecialchars($aspirante['pastor'])
    . ", y lleva {$aspirante['anos_bautizado']} "
    . ($aspirante['anos_bautizado'] == 1 ? 'año' : 'años') . " bautizad{$g}.";
if (!empty($aspirante['compromiso_movilidad'])) {
    $frases[] = "Manifiesta disposición para movilizarse durante el itinerario del programa.";
}
$resumen = implode(' ', $frases);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ficha de Postulante — <?= htmlspecialchars($nombre_completo) ?></title>
<style>
    :root {
        --verde: #167a5e;
        --verde-dark: #0f5a45;
        --verde-light: #e8f5f0;
        --dorado: #cea237;
        --azul: #003d6b;
        --gris: #555;
        --linea: #e3e3e3;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Montserrat, Arial, sans-serif;
        color: #222;
        background: #f0f2f1;
        line-height: 1.5;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Barra de acciones (no se imprime) */
    .toolbar {
        position: sticky; top: 0; z-index: 10;
        background: var(--verde-dark);
        padding: .75rem 1.25rem;
        display: flex; gap: .6rem; justify-content: center;
    }
    .toolbar button, .toolbar a {
        font: inherit; font-size: .9rem; font-weight: 600;
        border: 0; border-radius: 6px; cursor: pointer;
        padding: .55rem 1.2rem; text-decoration: none;
        display: inline-flex; align-items: center; gap: .45rem;
    }
    .toolbar .btn-print { background: var(--dorado); color: #2b2200; }
    .toolbar .btn-back  { background: rgba(255,255,255,.15); color: #fff; }

    /* Hoja A4 */
    .hoja {
        background: #fff;
        max-width: 820px;
        margin: 1.5rem auto;
        box-shadow: 0 6px 24px rgba(0,0,0,.12);
        overflow: hidden;
    }

    /* Encabezado */
    .ficha-head {
        background: var(--verde);
        color: #fff;
        padding: 1.5rem 2rem;
        display: flex; align-items: center; gap: 1rem;
    }
    .ficha-head__logo { height: 56px; }
    .ficha-head__txt h1 { font-size: 1.15rem; letter-spacing: .5px; }
    .ficha-head__txt p { font-size: .8rem; opacity: .9; }
    .ficha-head__num {
        margin-left: auto; text-align: right;
        font-size: .78rem; opacity: .92;
    }

    /* Bloque identidad */
    .identidad {
        display: flex; gap: 1.5rem;
        padding: 1.75rem 2rem 1.25rem;
        border-bottom: 3px solid var(--verde-light);
    }
    .foto {
        width: 130px; height: 165px; flex: 0 0 130px;
        border-radius: 8px; object-fit: cover;
        border: 3px solid var(--verde-light);
        background: var(--verde-light);
    }
    .foto--vacia {
        display: flex; align-items: center; justify-content: center;
        color: var(--verde); font-size: 2.5rem;
    }
    .identidad__datos h2 {
        font-size: 1.55rem; color: var(--azul); line-height: 1.2;
    }
    .identidad__sub { color: var(--dorado); font-weight: 600; font-size: .92rem; margin-bottom: .6rem; }
    .chips { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .5rem; }
    .chip {
        background: var(--verde-light); color: var(--verde-dark);
        font-size: .76rem; font-weight: 600;
        padding: .25rem .65rem; border-radius: 20px;
    }
    .badge-estatus {
        display: inline-block; margin-top: .65rem;
        font-size: .78rem; font-weight: 700; color: #fff;
        background: var(--verde-dark); padding: .3rem .8rem; border-radius: 6px;
    }

    /* Resumen */
    .seccion { padding: 1.25rem 2rem; border-bottom: 1px solid var(--linea); }
    .seccion__titulo {
        font-size: .82rem; text-transform: uppercase; letter-spacing: 1px;
        color: var(--verde); font-weight: 700; margin-bottom: .7rem;
        display: flex; align-items: center; gap: .5rem;
    }
    .seccion__titulo::before {
        content: ''; width: 4px; height: 16px; background: var(--dorado); border-radius: 2px;
    }
    .resumen { font-size: .95rem; color: #333; text-align: justify; }

    /* Grid de datos */
    .datos-grid {
        display: grid; grid-template-columns: 1fr 1fr; gap: .65rem 2rem;
    }
    .dato { font-size: .88rem; }
    .dato__label { color: var(--gris); font-size: .74rem; text-transform: uppercase; letter-spacing: .5px; }
    .dato__val { font-weight: 600; color: #222; }

    /* Perfil vocacional */
    .dim { margin-bottom: .55rem; }
    .dim__top { display: flex; justify-content: space-between; font-size: .83rem; margin-bottom: .2rem; }
    .dim__bar { height: 8px; background: var(--verde-light); border-radius: 5px; overflow: hidden; }
    .dim__fill { height: 100%; background: var(--verde); border-radius: 5px; }
    .fortalezas { margin-top: .8rem; font-size: .85rem; }
    .fortalezas strong { color: var(--verde-dark); }

    /* Proceso */
    .proceso { display: grid; grid-template-columns: repeat(5, 1fr); gap: .5rem; }
    .etapa { text-align: center; font-size: .72rem; }
    .etapa__punto {
        width: 26px; height: 26px; border-radius: 50%; margin: 0 auto .35rem;
        display: flex; align-items: center; justify-content: center;
        background: var(--linea); color: #fff; font-weight: 700; font-size: .8rem;
    }
    .etapa--aprobado  .etapa__punto { background: var(--verde); }
    .etapa--en_proceso .etapa__punto { background: var(--dorado); }
    .etapa--rechazado .etapa__punto { background: #b53c3c; }
    .etapa__estado { color: var(--gris); }

    /* Pie */
    .ficha-pie {
        background: var(--verde-dark); color: #fff;
        padding: 1rem 2rem; font-size: .76rem;
        display: flex; justify-content: space-between; align-items: center; gap: 1rem;
    }
    .ficha-pie a { color: #fff; }

    @media print {
        body { background: #fff; }
        .toolbar { display: none; }
        .hoja { box-shadow: none; margin: 0; max-width: 100%; }
        .seccion, .identidad { page-break-inside: avoid; }
    }
    @media (max-width: 640px) {
        .datos-grid { grid-template-columns: 1fr; }
        .identidad { flex-direction: column; align-items: center; text-align: center; }
        .proceso { grid-template-columns: repeat(2, 1fr); }
    }
</style>
</head>
<body>

<div class="toolbar">
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <a class="btn-back" href="/admin/candidatos?ver=<?= $aspirante['id'] ?>">← Volver al detalle</a>
</div>

<div class="hoja">

    <!-- Encabezado institucional -->
    <div class="ficha-head">
        <img src="/public/assets/logos/logo-mi-w.png" alt="Misioneros Integrales" class="ficha-head__logo">
        <div class="ficha-head__txt">
            <h1>FICHA DE POSTULANTE</h1>
            <p>Programa de Formación Misioneros Integrales · CNBV / DIME</p>
        </div>
        <div class="ficha-head__num">
            Registro N.° <?= $aspirante['id'] ?><br>
            <?= date('d/m/Y', strtotime($aspirante['created_at'])) ?>
        </div>
    </div>

    <!-- Identidad -->
    <div class="identidad">
        <?php if ($foto_ruta): ?>
            <img src="<?= htmlspecialchars($foto_ruta) ?>" alt="Foto" class="foto">
        <?php else: ?>
            <div class="foto foto--vacia">👤</div>
        <?php endif; ?>
        <div class="identidad__datos">
            <h2><?= htmlspecialchars($nombre_completo) ?></h2>
            <div class="identidad__sub">
                <?= htmlspecialchars($aspirante['ciudad_origen']) ?><?= $aspirante['estado_origen'] ? ', ' . htmlspecialchars($aspirante['estado_origen']) : '' ?>
            </div>
            <div class="chips">
                <span class="chip"><?= $aspirante['edad'] ?> años</span>
                <span class="chip"><?= ucfirst($aspirante['genero']) ?></span>
                <span class="chip"><?= $civil[$aspirante['estado_civil']] ?? $aspirante['estado_civil'] ?></span>
                <span class="chip"><?= (int)$aspirante['hijos'] ?> hijo<?= $aspirante['hijos'] == 1 ? '' : 's' ?></span>
                <span class="chip"><?= $nivel[$aspirante['nivel_academico']] ?? $aspirante['nivel_academico'] ?></span>
            </div>
            <span class="badge-estatus"><?= ucfirst(str_replace('_', ' ', $aspirante['estatus'])) ?></span>
        </div>
    </div>

    <!-- Resumen de presentación -->
    <div class="seccion">
        <div class="seccion__titulo">Resumen de presentación</div>
        <p class="resumen"><?= $resumen ?></p>
    </div>

    <!-- Datos eclesiales -->
    <div class="seccion">
        <div class="seccion__titulo">Vida eclesial</div>
        <div class="datos-grid">
            <div class="dato"><div class="dato__label">Iglesia</div><div class="dato__val"><?= htmlspecialchars($aspirante['iglesia']) ?></div></div>
            <div class="dato"><div class="dato__label">Pastor</div><div class="dato__val"><?= htmlspecialchars($aspirante['pastor']) ?></div></div>
            <div class="dato"><div class="dato__label">Años bautizado</div><div class="dato__val"><?= $aspirante['anos_bautizado'] ?></div></div>
            <div class="dato"><div class="dato__label">Disposición a movilizarse</div><div class="dato__val"><?= !empty($aspirante['compromiso_movilidad']) ? 'Sí' : 'No' ?></div></div>
        </div>
    </div>

    <!-- Contacto -->
    <div class="seccion">
        <div class="seccion__titulo">Contacto</div>
        <div class="datos-grid">
            <div class="dato"><div class="dato__label">Cédula</div><div class="dato__val"><?= htmlspecialchars($aspirante['cedula']) ?></div></div>
            <div class="dato"><div class="dato__label">Teléfono</div><div class="dato__val"><?= htmlspecialchars($aspirante['telefono']) ?></div></div>
            <div class="dato"><div class="dato__label">Correo</div><div class="dato__val"><?= htmlspecialchars($aspirante['email'] ?? '—') ?></div></div>
            <div class="dato"><div class="dato__label">Teléfono del pastor</div><div class="dato__val"><?= htmlspecialchars($aspirante['telefono_pastor']) ?></div></div>
        </div>
    </div>

    <!-- Perfil vocacional -->
    <?php if ($scoring): ?>
    <div class="seccion">
        <div class="seccion__titulo">Perfil vocacional (test de aptitudes)</div>
        <?php foreach ($scoring['dimensiones'] as $dim): if ($dim['score'] === null) continue; ?>
        <div class="dim">
            <div class="dim__top">
                <span><?= htmlspecialchars($dim['nombre']) ?></span>
                <span><strong><?= $dim['score'] ?>%</strong></span>
            </div>
            <div class="dim__bar"><div class="dim__fill" style="width:<?= $dim['score'] ?>%"></div></div>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($scoring['fortalezas'])): ?>
        <div class="fortalezas">
            <strong>Fortalezas:</strong> <?= htmlspecialchars(implode(', ', $scoring['fortalezas'])) ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Estado del proceso -->
    <div class="seccion">
        <div class="seccion__titulo">Estado del proceso de selección</div>
        <div class="proceso">
            <?php $n = 1; foreach ($etapas_label as $clave => $label):
                $est = $flujo_idx[$clave]['estatus'] ?? 'pendiente'; ?>
            <div class="etapa etapa--<?= $est ?>">
                <div class="etapa__punto"><?= $est === 'aprobado' ? '✓' : $n ?></div>
                <div><?= $label ?></div>
                <div class="etapa__estado"><?= $estatus_label[$est] ?? $est ?></div>
            </div>
            <?php $n++; endforeach; ?>
        </div>
    </div>

    <!-- Pie -->
    <div class="ficha-pie">
        <span>misionerosintegrales.com · misionerosintegrales.cnbv@gmail.com</span>
        <span>Contacto: 0424-5886540 / 0424-5905392</span>
    </div>

</div>

</body>
</html>
