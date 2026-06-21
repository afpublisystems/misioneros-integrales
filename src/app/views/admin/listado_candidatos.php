<?php
/**
 * Listado de Postulantes — hoja imprimible / exportable a PDF
 * Tabla con todos los candidatos (respeta el filtro y la búsqueda activos).
 * Se renderiza sin layout (renderParcial): documento HTML completo.
 */

$estatus_label = [
    'borrador'     => 'Borrador',     'enviada'   => 'Enviada',
    'en_revision'  => 'En revisión',  'aprobada'  => 'Aprobada',
    'rechazada'    => 'Rechazada',    'lista_espera' => 'Lista de espera',
];

// Título según filtro
$titulo_filtro = $filtro_estatus
    ? ('Estatus: ' . ($estatus_label[$filtro_estatus] ?? $filtro_estatus))
    : 'Todos los postulantes';

// Query string de búsqueda (para conservarla al cambiar el filtro)
$q_param = $busqueda ? '&q=' . urlencode($busqueda) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Listado de Postulantes — Misioneros Integrales</title>
<style>
    :root {
        --verde: #167a5e;
        --verde-dark: #0f5a45;
        --verde-light: #e8f5f0;
        --dorado: #cea237;
        --azul: #003d6b;
        --linea: #e3e3e3;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', Montserrat, Arial, sans-serif;
        color: #222; background: #f0f2f1; line-height: 1.45;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }

    /* Barra de acciones (no se imprime) */
    .toolbar {
        position: sticky; top: 0; z-index: 10;
        background: var(--verde-dark); padding: .75rem 1.25rem;
        display: flex; gap: .6rem; justify-content: center; align-items: center; flex-wrap: wrap;
    }
    .toolbar button, .toolbar a {
        font: inherit; font-size: .9rem; font-weight: 600;
        border: 0; border-radius: 6px; cursor: pointer;
        padding: .55rem 1.2rem; text-decoration: none;
        display: inline-flex; align-items: center; gap: .45rem;
    }
    .toolbar .btn-print { background: var(--dorado); color: #2b2200; }
    .toolbar .btn-back  { background: rgba(255,255,255,.15); color: #fff; }
    .toolbar__filtro { display: inline-flex; align-items: center; gap: .4rem; color: #fff; font-size: .85rem; }
    .toolbar__filtro select {
        font: inherit; font-size: .85rem; padding: .5rem .7rem;
        border: 0; border-radius: 6px; cursor: pointer; background: #fff; color: #222;
    }

    .hoja {
        background: #fff; max-width: 1100px; margin: 1.5rem auto;
        box-shadow: 0 6px 24px rgba(0,0,0,.12); padding: 1.5rem 1.75rem 2rem;
    }

    /* Encabezado */
    .head {
        display: flex; align-items: center; gap: 1rem;
        border-bottom: 3px solid var(--verde); padding-bottom: 1rem; margin-bottom: 1rem;
    }
    .head img.logo { height: 52px; }
    .head__txt h1 { font-size: 1.15rem; color: var(--azul); letter-spacing: .3px; }
    .head__txt p { font-size: .82rem; color: var(--verde); font-weight: 600; }
    .head__meta { margin-left: auto; text-align: right; font-size: .78rem; color: #666; }
    .head__meta strong { color: var(--verde-dark); font-size: 1.4rem; display: block; }

    /* Tabla */
    table { width: 100%; border-collapse: collapse; font-size: .8rem; }
    thead th {
        background: var(--verde); color: #fff; text-align: left;
        padding: .5rem .6rem; font-weight: 600; white-space: nowrap;
    }
    tbody td { padding: .45rem .6rem; border-bottom: 1px solid var(--linea); vertical-align: middle; }
    tbody tr:nth-child(even) { background: #fafbfa; }
    td.num { color: #999; text-align: center; }
    td.cedula { white-space: nowrap; }
    .nombre { font-weight: 600; color: var(--azul); }

    /* Foto */
    td.foto { width: 46px; padding: .3rem .4rem; }
    .foto-thumb {
        width: 40px; height: 50px; object-fit: cover; border-radius: 5px;
        border: 1px solid var(--verde-light); display: block;
    }
    .foto-vacia {
        width: 40px; height: 50px; border-radius: 5px; background: var(--verde-light);
        display: flex; align-items: center; justify-content: center;
        color: var(--verde); font-size: 1.1rem;
    }

    .vacio { text-align: center; padding: 2rem; color: #888; }

    .pie {
        margin-top: 1.25rem; padding-top: .75rem; border-top: 1px solid var(--linea);
        font-size: .72rem; color: #888; display: flex; justify-content: space-between; gap: 1rem;
    }

    @media print {
        @page { size: landscape; margin: 1cm; }
        body { background: #fff; }
        .toolbar { display: none; }
        .hoja { box-shadow: none; margin: 0; max-width: 100%; padding: 0; }
        thead { display: table-header-group; }
        tbody tr { page-break-inside: avoid; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <span class="toolbar__filtro">
        <label for="f-estatus">Filtrar:</label>
        <select id="f-estatus" onchange="location.href='/admin/candidatos?listado=1' + (this.value ? '&estatus='+this.value : '') + '<?= $q_param ?>'">
            <option value="">Todos los estatus</option>
            <?php foreach ($estatus_label as $val => $lab): ?>
            <option value="<?= $val ?>" <?= $filtro_estatus === $val ? 'selected' : '' ?>><?= $lab ?></option>
            <?php endforeach; ?>
        </select>
    </span>
    <button class="btn-print" onclick="window.print()">🖨 Imprimir / Guardar PDF</button>
    <a class="btn-back" href="/admin/candidatos<?= $filtro_estatus ? '?estatus='.urlencode($filtro_estatus) : '' ?>">← Volver</a>
</div>

<div class="hoja">

    <div class="head">
        <img src="/public/assets/logos/logo-mi-t.png" alt="Misioneros Integrales" class="logo">
        <div class="head__txt">
            <h1>LISTADO DE POSTULANTES</h1>
            <p>Programa de Formación Misioneros Integrales · CNBV / DIME</p>
        </div>
        <div class="head__meta">
            <strong><?= count($lista) ?></strong>
            <?= count($lista) == 1 ? 'postulante' : 'postulantes' ?><br>
            <?= htmlspecialchars($titulo_filtro) ?><br>
            <?= date('d/m/Y') ?>
        </div>
    </div>

    <?php if (empty($lista)): ?>
        <p class="vacio">No hay postulantes que coincidan con el filtro seleccionado.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Foto</th>
                <th>Nombre completo</th>
                <th>Cédula</th>
                <th>Edad</th>
                <th>Teléfono</th>
                <th>Ciudad / Estado</th>
                <th>Iglesia</th>
                <th>Pastor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lista as $i => $a):
                // Cédula: solo dígitos (quita V-, v, espacios, puntos)
                $cedula_num = preg_replace('/\D+/', '', $a['cedula'] ?? '');
                $foto = $a['foto_ruta'] ? '/' . ltrim($a['foto_ruta'], '/') : null;
            ?>
            <tr>
                <td class="num"><?= $i + 1 ?></td>
                <td class="foto">
                    <?php if ($foto): ?>
                        <img src="<?= htmlspecialchars($foto) ?>" alt="" class="foto-thumb">
                    <?php else: ?>
                        <div class="foto-vacia">👤</div>
                    <?php endif; ?>
                </td>
                <td class="nombre"><?= htmlspecialchars($a['nombres'] . ' ' . $a['apellidos']) ?></td>
                <td class="cedula"><?= htmlspecialchars($cedula_num ?: '—') ?></td>
                <td><?= $a['edad'] ?: '—' ?></td>
                <td><?= htmlspecialchars($a['telefono'] ?? '—') ?></td>
                <td>
                    <?= htmlspecialchars($a['ciudad_origen'] ?? '—') ?><?= $a['estado_origen'] ? ', ' . htmlspecialchars($a['estado_origen']) : '' ?>
                </td>
                <td><?= htmlspecialchars($a['iglesia'] ?? '—') ?></td>
                <td><?= htmlspecialchars($a['pastor'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="pie">
        <span>misionerosintegrales.com · misionerosintegrales.cnbv@gmail.com · 0424-5886540 / 0424-5905392</span>
        <span>Generado el <?= date('d/m/Y H:i') ?></span>
    </div>

</div>

</body>
</html>
