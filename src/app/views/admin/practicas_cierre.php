<?php
/**
 * Informe de cierre de un lugar de prácticas.
 * Mismo formato que el semanal — comparten los estilos — pero el
 * alcance es todo lo que se hizo en el lugar: el fruto, los alcances
 * desarrollados, el avance del discipulado y la evolución semanal.
 */
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
          'septiembre', 'octubre', 'noviembre', 'diciembre'];

$largo = fn(string $f) => (int) date('j', strtotime($f)) . ' de ' . $meses[(int) date('n', strtotime($f))]
                        . ' de ' . date('Y', strtotime($f));
$corto = fn(string $f) => (int) date('j', strtotime($f)) . '/' . (int) date('n', strtotime($f));
$cuenta = fn(int $n, string $uno, string $varios) => $n . ' ' . ($n === 1 ? $uno : $varios);

// Las actividades agrupadas por iglesia, para la narrativa del final
$por_iglesia_detalle = [];
foreach ($actividades as $a) {
    $por_iglesia_detalle[$a['iglesia']]['sector'] = $a['sector'];
    $por_iglesia_detalle[$a['iglesia']]['items'][] = $a;
}
?>

<?php include __DIR__ . '/../partials/informe_estilos.php'; ?>

<div class="hoja__acciones">
    <a href="/admin/practicas"><i class="fas fa-arrow-left"></i> Volver</a>
    <button class="primario" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
</div>

<div class="hoja inf">

    <div class="inf-head">
        <img src="/public/assets/logos/logo-mi-completo-w.png" alt="Misioneros Integrales">
        <div>
            <div class="t1">Prácticas misioneras · Informe de cierre</div>
            <h1><?= htmlspecialchars($lugar) ?></h1>
            <div class="t2">
                Del <?= $largo($desde) ?> al <?= $largo($hasta) ?> ·
                <?= $cuenta((int) count($semanas), 'semana', 'semanas') ?> ·
                <?= $cuenta((int) $totales['iglesias'], 'iglesia', 'iglesias') ?>
            </div>
        </div>
    </div>

    <div class="inf-kpis">
        <div class="inf-kpi"><b><?= $totales['evangelizados'] ?></b><span>personas evangelizadas</span></div>
        <div class="inf-kpi"><b><?= $totales['decisiones'] ?></b><span>decisiones de fe</span></div>
        <div class="inf-kpi"><b><?= $totales['discipulado'] ?></b><span>personas en discipulado</span></div>
        <div class="inf-kpi"><b><?= $totales['actividades'] ?></b><span>actividades realizadas</span></div>
    </div>

    <!-- Por iglesia ─────────────────────────────────────────── -->
    <h2>Lo alcanzado en cada iglesia</h2>
    <table>
        <thead>
            <tr>
                <th>Iglesia</th><th>Actividades</th><th>Evangelizados</th>
                <th>Decisiones</th><th>Discipulado</th><th>Promedio del culto</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($por_iglesia as $i): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($i['iglesia']) ?>
                    <?php if ($i['sector']): ?><span class="lugar"> · <?= htmlspecialchars($i['sector']) ?></span><?php endif; ?>
                </td>
                <td><?= $i['actividades'] ?></td>
                <td><?= $i['evangelizados'] ?></td>
                <td><?= $i['decisiones'] ?: '—' ?></td>
                <td><?= $i['discipulado'] ?></td>
                <td><?= $i['promedio_culto'] ?: '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td><?= $totales['actividades'] ?></td>
                <td><?= $totales['evangelizados'] ?></td>
                <td><?= $totales['decisiones'] ?></td>
                <td><?= $totales['discipulado'] ?></td>
                <td>—</td>
            </tr>
        </tfoot>
    </table>
    <p class="nota">
        El promedio del culto es solo de la comunidad: el equipo misionero no cuenta como
        crecimiento de la iglesia.
    </p>

    <!-- Discipulado por lección ─────────────────────────────── -->
    <h2>Avance del discipulado</h2>
    <?php if (!$lecciones): ?>
    <p class="nota">
        No se registró qué lección se dio en las actividades de este período. Al cargarla
        en cada célula, este cuadro se arma solo.
    </p>
    <?php else: ?>
    <table>
        <thead><tr><th>Nivel y lección</th><th>Veces que se dio</th><th>Personas</th></tr></thead>
        <tbody>
        <?php $nivel_previo = null; foreach ($lecciones as $l): ?>
            <?php if ($l['nivel'] !== $nivel_previo): $nivel_previo = $l['nivel']; ?>
            <tr><td colspan="3" style="background:#fbf8ef;font-weight:700"><?= htmlspecialchars($l['nivel']) ?></td></tr>
            <?php endif; ?>
            <tr>
                <td><?= $l['numero'] ?>. <?= htmlspecialchars($l['titulo']) ?></td>
                <td><?= $l['veces'] ?></td>
                <td><?= $l['personas'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="nota">
        «Personas» es cuántas recibieron esa lección, sumando todas las veces que se dio.
    </p>
    <?php endif; ?>

    <!-- Alcances desarrollados ──────────────────────────────── -->
    <h2>Alcances desarrollados</h2>
    <?php if (!$alcances): ?>
    <p class="nota">No se registraron alcances fuera del culto dominical en este período.</p>
    <?php else: ?>
    <table>
        <thead><tr><th>Tipo de actividad</th><th>Veces</th><th>Comunidad</th><th>Equipo</th></tr></thead>
        <tbody>
        <?php foreach ($alcances as $a): ?>
            <tr>
                <td><?= htmlspecialchars(PracticaModel::TIPOS[$a['tipo']] ?? $a['tipo']) ?></td>
                <td><?= $a['veces'] ?></td>
                <td><?= $a['comunidad'] ?></td>
                <td><?= $a['equipo'] ?: '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if ($organiza): ?>
    <h3 style="margin-top:14px">Reuniones por organización</h3>
    <table>
        <thead><tr><th>Organización</th><th>Encuentros</th><th>Asistencia</th></tr></thead>
        <tbody>
        <?php foreach ($organiza as $o): ?>
            <tr>
                <td><?= htmlspecialchars($o['etiqueta']) ?></td>
                <td><?= $o['veces'] ?></td>
                <td><?= $o['asistencia'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="nota">
        Una reunión dirigida a dos organizaciones cuenta en las dos, así que los encuentros
        no suman el total de actividades.
    </p>
    <?php endif; ?>

    <!-- Semana a semana ─────────────────────────────────────── -->
    <?php if (count($semanas) > 1): ?>
    <h2>Semana a semana</h2>
    <table>
        <thead>
            <tr>
                <th>Semana</th><th>Actividades</th><th>Culto dominical</th>
                <th>Evangelizados</th><th>Decisiones</th><th>Discipulado</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($semanas as $n => $s): ?>
            <tr>
                <td><?= $n + 1 ?>ª · <?= $corto($s['inicio']) ?> al <?= $corto($s['fin']) ?></td>
                <td><?= $s['actividades'] ?></td>
                <td><?= $s['culto'] ?: '—' ?></td>
                <td><?= $s['evangelizados'] ?></td>
                <td><?= $s['decisiones'] ?: '—' ?></td>
                <td><?= $s['discipulado'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Narrativa ───────────────────────────────────────────── -->
    <h2>Lo que se hizo en cada iglesia</h2>
    <?php foreach ($por_iglesia_detalle as $nombre => $datos): ?>
    <div class="inf-iglesia">
        <h3><?= htmlspecialchars($nombre) ?></h3>
        <?php if ($datos['sector']): ?>
        <div class="lugar"><?= htmlspecialchars($datos['sector']) ?> · <?= $cuenta(count($datos['items']), 'actividad', 'actividades') ?></div>
        <?php endif; ?>
        <ul>
        <?php foreach ($datos['items'] as $a): ?>
            <li>
                <b><?= $corto($a['fecha']) ?></b> ·
                <?= htmlspecialchars($a['descripcion'] ?: PracticaModel::TIPOS[$a['tipo']]) ?>
                <?php if ($a['tipo'] === 'organizacion' && $a['dirigida_a']): ?>
                    <?php $orgs = array_map(
                        fn($o) => PracticaModel::ORGANIZACIONES[$o] ?? $o,
                        array_filter(explode(',', (string) $a['dirigida_a']))
                    ); ?>
                    (<?= htmlspecialchars(mb_strtolower(implode(' y ', $orgs))) ?>)
                <?php endif; ?>
                <?php if ($a['comunidad'] > 0): ?>
                    — <?= $cuenta((int) $a['comunidad'], 'persona', 'personas') ?> de la comunidad
                <?php endif; ?>
                <?php
                $fruto = array_filter([
                    $a['evangelizados'] > 0 ? $cuenta((int) $a['evangelizados'], 'evangelizado', 'evangelizados') : null,
                    $a['decisiones'] > 0    ? $cuenta((int) $a['decisiones'], 'decisión', 'decisiones') : null,
                    $a['discipulado'] > 0   ? $cuenta((int) $a['discipulado'], 'en discipulado', 'en discipulado') : null,
                ]);
                ?>
                <?php if ($fruto): ?>
                    · <?= htmlspecialchars(implode(', ', $fruto)) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>

    <footer>
        Misioneros Integrales — CNBV / DIME · Coordinación: José y Yohanna de Ramos.
        Datos tomados de los reportes de cada grupo misionero.
    </footer>

</div>
