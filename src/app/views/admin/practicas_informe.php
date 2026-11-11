<?php
/**
 * Informe semanal de prácticas — hoja para entregar a la DIME/CNBV.
 * Se imprime o se guarda en PDF desde el navegador.
 */
$dias  = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
          'septiembre', 'octubre', 'noviembre', 'diciembre'];
$t     = $resumen['total'];

// Rango en palabras: los días del primero al último con actividad
$fechas = array_column($actividades, 'fecha');
$desde  = $fechas ? min($fechas) : $lunes;
$hasta  = $fechas ? max($fechas) : $domingo;
$largo  = fn(string $f) => mb_strtolower($dias[(int) date('w', strtotime($f))]) . ' ' . (int) date('j', strtotime($f))
                         . ' de ' . $meses[(int) date('n', strtotime($f))];

$cuenta = fn(int $n, string $uno, string $varios) => $n . ' ' . ($n === 1 ? $uno : $varios);
$lista  = function (array $p): string {
    $ultimo = array_pop($p);
    return $p ? implode(', ', $p) . ' y ' . $ultimo : $ultimo;
};

// Una línea por actividad, armada con lo que se reportó
$linea = function (array $a) use ($dias, $cuenta, $lista): string {
    $partes = [($a['descripcion'] ? htmlspecialchars($a['descripcion']) : PracticaModel::TIPOS[$a['tipo']]) . '.'];

    if ($a['comunidad'] > 0) {
        // En el apoyo a otra iglesia la gente no es de la comunidad que se atiende
        $txt = 'Asistieron ' . $cuenta((int) $a['comunidad'], 'persona', 'personas') . ($a['tipo'] === 'apoyo' ? '' : ' de la comunidad');
        if ($a['adultos'] !== null) {
            $edades = array_filter([
                $a['adultos'] > 0 ? $cuenta((int) $a['adultos'], 'adulto', 'adultos') : null,
                $a['jovenes'] > 0 ? $cuenta((int) $a['jovenes'], 'joven', 'jóvenes') : null,
                $a['ninos']   > 0 ? $cuenta((int) $a['ninos'], 'niño', 'niños') : null,
            ]);
            $txt .= ' (' . $lista($edades) . ')';
        }
        if ($a['equipo'] > 0) {
            $txt .= ' y ' . $a['equipo'] . ' del equipo';
        }
        $partes[] = $txt . '.';
    }

    $fruto = array_filter([
        $a['evangelizados'] > 0 ? $cuenta((int) $a['evangelizados'], 'evangelizado', 'evangelizados') : null,
        $a['decisiones'] > 0    ? $cuenta((int) $a['decisiones'], 'decisión de fe', 'decisiones de fe') : null,
        $a['discipulado'] > 0   ? $cuenta((int) $a['discipulado'], 'persona en discipulado', 'personas en discipulado') : null,
    ]);
    if ($fruto) {
        $partes[] = 'Hubo ' . $lista($fruto) . '.';
    }

    return '<b>' . $dias[(int) date('w', strtotime($a['fecha']))] . ' ' . (int) date('j', strtotime($a['fecha'])) . ':</b> '
         . implode(' ', $partes);
};
?>

<?php include __DIR__ . '/../partials/informe_estilos.php'; ?>

<div class="hoja__acciones">
    <a href="/admin/practicas?semana=<?= $lunes ?>"><i class="fas fa-arrow-left"></i> Volver</a>
    <button class="primario" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
</div>

<div class="hoja inf">

    <div class="inf-head">
        <img src="/public/assets/logos/logo-mi-completo-w.png" alt="Misioneros Integrales">
        <div>
            <div class="t1">Prácticas misioneras<?= $numero ? ' · Semana ' . $numero : '' ?></div>
            <h1>Informe semanal · Carayaca, La Guaira</h1>
            <div class="t2">
                <?= $desde === $hasta ? 'Del ' . $largo($desde) : 'Del ' . $largo($desde) . ' al ' . $largo($hasta) ?>
                de <?= date('Y', strtotime($hasta)) ?>
            </div>
        </div>
    </div>

    <?php if (empty($actividades)): ?>
    <p style="margin-top:1.5rem">No hay actividades cargadas en esta semana.</p>
    <?php else: ?>

    <div class="inf-kpis">
        <div class="inf-kpi"><b><?= $t['comunidad'] ?></b><span>personas de la comunidad en los cultos del domingo</span></div>
        <div class="inf-kpi"><b><?= $t['evangelizados'] ?></b><span>personas evangelizadas</span></div>
        <div class="inf-kpi"><b><?= $t['discipulado'] ?></b><span>personas en discipulado</span></div>
        <div class="inf-kpi"><b><?= $t['decisiones'] ?></b><span>decisiones de fe</span></div>
    </div>

    <h2>Culto dominical</h2>
    <table>
        <thead><tr><th>Iglesia</th><th>Niños</th><th>Jóvenes</th><th>Adultos</th><th>Comunidad</th><th>Equipo</th><th>Total</th></tr></thead>
        <tbody>
        <?php foreach ($resumen['iglesias'] as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nombre'] . ($r['sector'] ? ' · ' . $r['sector'] : '')) ?></td>
                <?php if (!$r['hubo_culto']): ?>
                <td colspan="6" style="text-align:center;color:#7a847f">No se reportó culto</td>
                <?php else: ?>
                <td><?= $r['desglose'] ? $r['ninos'] : '—' ?></td>
                <td><?= $r['desglose'] ? $r['jovenes'] : '—' ?></td>
                <td><?= $r['desglose'] ? $r['adultos'] : '—' ?></td>
                <td><?= $r['comunidad'] ?></td>
                <td><?= $r['equipo'] ?></td>
                <td><?= $r['comunidad'] + $r['equipo'] ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td><?= $t['desglose'] ? $t['ninos'] : '—' ?></td>
                <td><?= $t['desglose'] ? $t['jovenes'] : '—' ?></td>
                <td><?= $t['desglose'] ? $t['adultos'] : '—' ?></td>
                <td><?= $t['comunidad'] ?></td>
                <td><?= $t['equipo'] ?></td>
                <td><?= $t['comunidad'] + $t['equipo'] ?></td>
            </tr>
        </tfoot>
    </table>
    <p class="nota">"Equipo" son los misioneros integrales, los coordinadores y su familia. No se cuentan como asistencia de la iglesia.</p>

    <h2>Evangelismo y discipulado de la semana</h2>
    <table>
        <thead><tr><th>Iglesia</th><th>Evangelizados</th><th>Decisiones de fe</th><th>En discipulado</th></tr></thead>
        <tbody>
        <?php foreach ($resumen['iglesias'] as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['nombre']) ?></td>
                <td><?= $r['evangelizados'] ?: '—' ?></td>
                <td><?= $r['decisiones'] ?: '—' ?></td>
                <td><?= $r['discipulado'] ?: '—' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td>Total</td><td><?= $t['evangelizados'] ?></td><td><?= $t['decisiones'] ?></td><td><?= $t['discipulado'] ?></td></tr></tfoot>
    </table>
    <p class="nota">Una raya indica que el grupo no reportó ese dato en la semana.</p>

    <h2>Lo que se hizo en cada iglesia</h2>
    <?php foreach ($resumen['iglesias'] as $r): if (!$r['actividades']) continue; ?>
    <div class="inf-iglesia">
        <h3><?= htmlspecialchars($r['nombre']) ?></h3>
        <?php if ($r['sector']): ?><div class="lugar"><?= htmlspecialchars($r['sector']) ?></div><?php endif; ?>
        <ul>
            <?php foreach ($r['actividades'] as $a): ?>
            <li><?= $linea($a) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endforeach; ?>

    <?php endif; ?>

    <footer>
        Misioneros Integrales · CNBV / DIME · Datos tomados de los reportes de cada grupo misionero.
    </footer>
</div>
