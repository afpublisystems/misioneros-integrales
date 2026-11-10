<?php
/**
 * Hoja de un informe de rendición de cuentas.
 * Sale con el layout de impresión: sin panel, lista para imprimir o
 * guardar en PDF. El mismo archivo pinta los seis informes porque
 * comparten encabezado, pie y estilos de tabla.
 */
$td  = 'padding:.4rem .5rem;border:1px solid #e2e8f0';
$th  = 'padding:.45rem .5rem;border:1px solid #e2e8f0;background:#f1f5f9;text-align:left';
$num = $td . ';text-align:right;white-space:nowrap';
$thn = $th . ';text-align:right';

$usd = fn($v) => '$' . number_format((float) $v, 2);
$dia = fn($f) => $f ? date('d/m/Y', strtotime($f)) : '—';

// Un movimiento en bolívares se ve mejor con su monto original al lado:
// si el USD quedó en cero, es lo único que dice cuánto entró de verdad.
$bs = function ($m) {
    if (empty($m['monto_ves'])) { return ''; }
    $t = !empty($m['tasa_cambio']) ? ' · tasa ' . number_format((float) $m['tasa_cambio'], 2, ',', '.') : '';
    return 'Bs ' . number_format((float) $m['monto_ves'], 2, ',', '.') . $t;
};
?>

<div class="hoja__acciones">
    <a href="/admin/finanzas/reportes"><i class="fas fa-arrow-left"></i> Volver</a>
    <a href="/admin/finanzas/reporte/csv?<?= htmlspecialchars($query) ?>">
        <i class="fas fa-file-csv"></i> Descargar CSV
    </a>
    <button class="primario" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
</div>

<div class="hoja">

    <header style="border-bottom:2px solid #167a5e;padding-bottom:1rem;margin-bottom:1.5rem">
        <h1 style="margin:0;font-size:1.25rem;color:#0f172a"><?= htmlspecialchars($encabezado[1]) ?></h1>
        <p style="margin:.35rem 0 0;color:#475569">
            Programa de Formación de Misioneros Integrales — CNBV/DIME<br>
            Rendición de cuentas · Emitido el <?= date('d/m/Y') ?>
            <?php if (!empty($filtros['desde']) || !empty($filtros['hasta'])): ?>
            <br>Período: <?= $dia($filtros['desde'] ?: null) ?> al <?= $dia($filtros['hasta'] ?: null) ?>
            <?php endif; ?>
        </p>
    </header>

<?php // ══════════════════════════════════════════ RESUMEN GERENCIAL
if ($tipo === 'gerencial'): ?>

    <p style="margin-bottom:1.25rem;line-height:1.6">
        Movimiento del programa
        <?php if ($periodo['desde']): ?>
        entre el <?= $dia($periodo['desde']) ?> y el <?= $dia($periodo['hasta']) ?>.
        <?php else: ?>
        hasta la fecha.
        <?php endif; ?>
        Todo está consolidado en dólares; lo que entró o salió en bolívares se convirtió
        con la tasa del día de cada movimiento.
    </p>

    <h2 style="font-size:1rem;margin:0 0 .6rem">De dónde vino la plata</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:1.5rem">
        <thead><tr><th style="<?= $th ?>">Origen</th><th style="<?= $thn ?>">Movimientos</th><th style="<?= $thn ?>">USD</th></tr></thead>
        <tbody>
        <?php foreach ($origenes as $clave => $etiqueta): $o = $por_origen[$clave] ?? null; ?>
            <?php if ($o): ?>
            <tr>
                <td style="<?= $td ?>"><?= htmlspecialchars($etiqueta) ?></td>
                <td style="<?= $num ?>"><?= $o['cantidad'] ?></td>
                <td style="<?= $num ?>"><?= $usd($o['total']) ?></td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Total recibido</td>
                <td style="<?= $num ?>"></td>
                <td style="<?= $num ?>"><?= $usd($caja['ingresos']) ?></td>
            </tr>
        </tbody>
    </table>

    <h2 style="font-size:1rem;margin:0 0 .6rem">En qué se fue</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:1.5rem">
        <thead><tr><th style="<?= $th ?>">Rubro</th><th style="<?= $thn ?>">Movimientos</th><th style="<?= $thn ?>">USD</th></tr></thead>
        <tbody>
        <?php foreach ($por_categoria as $c): ?>
            <tr>
                <td style="<?= $td ?>"><?= htmlspecialchars($categorias[$c['categoria']] ?? $c['categoria']) ?></td>
                <td style="<?= $num ?>"><?= $c['cantidad'] ?></td>
                <td style="<?= $num ?>"><?= $usd($c['total']) ?></td>
            </tr>
        <?php endforeach; ?>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Total gastado</td>
                <td style="<?= $num ?>"></td>
                <td style="<?= $num ?>"><?= $usd($caja['gastos']) ?></td>
            </tr>
        </tbody>
    </table>

    <h2 style="font-size:1rem;margin:0 0 .6rem">Cómo queda la caja</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:1.5rem">
        <tbody>
            <tr><td style="<?= $td ?>">Recibido menos gastado</td><td style="<?= $num ?>"><?= $usd($caja['saldo']) ?></td></tr>
            <tr><td style="<?= $td ?>">Menos lo que se debe por préstamos</td><td style="<?= $num ?>">− <?= $usd($prestamos['saldo']) ?></td></tr>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Disponible propio del programa</td>
                <td style="<?= $num ?>"><?= $usd($disponible) ?></td>
            </tr>
        </tbody>
    </table>
    <p style="font-size:.78rem;color:#64748b;margin:-1rem 0 1.5rem;line-height:1.5">
        El disponible propio descuenta la deuda: tener plata en caja que es prestada no es
        plata del programa.
    </p>

    <h2 style="font-size:1rem;margin:0 0 .6rem">Matrículas de los participantes</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:1.5rem">
        <tbody>
            <tr><td style="<?= $td ?>">Participantes</td><td style="<?= $num ?>"><?= $matriculas['participantes'] ?></td></tr>
            <tr><td style="<?= $td ?>">Costo del ciclo sin becas</td><td style="<?= $num ?>"><?= $usd($matriculas['costo_total']) ?></td></tr>
            <tr><td style="<?= $td ?>">Cubierto por becas</td><td style="<?= $num ?>"><?= $usd($matriculas['becado']) ?></td></tr>
            <tr><td style="<?= $td ?>">A cobrar</td><td style="<?= $num ?>"><?= $usd($matriculas['esperado']) ?></td></tr>
            <tr><td style="<?= $td ?>">Cobrado</td><td style="<?= $num ?>"><?= $usd($matriculas['cobrado']) ?></td></tr>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Por cobrar</td>
                <td style="<?= $num ?>"><?= $usd($matriculas['por_cobrar']) ?></td>
            </tr>
        </tbody>
    </table>

    <h2 style="font-size:1rem;margin:0 0 .6rem">Cuenta del Seminario (STBV)</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:1.5rem">
        <tbody>
            <tr><td style="<?= $td ?>">Total de cargos</td><td style="<?= $num ?>"><?= $usd($seminario['total']) ?></td></tr>
            <tr><td style="<?= $td ?>">Pagado al Seminario</td><td style="<?= $num ?>"><?= $usd($seminario['pagado']) ?></td></tr>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Pendiente</td>
                <td style="<?= $num ?>"><?= $usd($seminario['pendiente']) ?></td>
            </tr>
        </tbody>
    </table>

    <h2 style="font-size:1rem;margin:0 0 .6rem">Préstamos</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:1.5rem">
        <tbody>
            <tr><td style="<?= $td ?>">Recibido en préstamos</td><td style="<?= $num ?>"><?= $usd($prestamos['recibido']) ?></td></tr>
            <tr><td style="<?= $td ?>">Devuelto</td><td style="<?= $num ?>"><?= $usd($prestamos['devuelto']) ?></td></tr>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Pendiente de devolver (<?= $prestamos['activos'] ?> abierto<?= $prestamos['activos'] == 1 ? '' : 's' ?>)</td>
                <td style="<?= $num ?>"><?= $usd($prestamos['saldo']) ?></td>
            </tr>
        </tbody>
    </table>

    <?php if ($fondos): ?>
    <h2 style="font-size:1rem;margin:0 0 .6rem">Fondos con destino</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem">
        <thead><tr><th style="<?= $th ?>">Fondo</th><th style="<?= $thn ?>">Entró</th><th style="<?= $thn ?>">Se gastó</th><th style="<?= $thn ?>">Queda</th></tr></thead>
        <tbody>
        <?php foreach ($fondos as $f): ?>
            <tr>
                <td style="<?= $td ?>"><?= htmlspecialchars($f['nombre']) ?></td>
                <td style="<?= $num ?>"><?= $usd($f['entradas']) ?></td>
                <td style="<?= $num ?>"><?= $usd($f['salidas']) ?></td>
                <td style="<?= $num ?>"><?= $usd((float) $f['entradas'] - (float) $f['salidas']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

<?php // ══════════════════════════════════════════════ PRÉSTAMOS
elseif ($tipo === 'prestamos'): ?>

    <p style="margin-bottom:1rem;line-height:1.6">
        <?= $estados[$filtros['estatus']] ?? 'Todos' ?>.
        Debajo de cada préstamo van las devoluciones que se le han hecho.
    </p>

    <?php if (!$prestamos): ?>
    <p style="padding:2rem;text-align:center;color:#94a3b8">No hay préstamos con ese filtro.</p>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:.8rem;margin-bottom:1.25rem">
        <thead>
            <tr>
                <th style="<?= $th ?>">Prestamista</th>
                <th style="<?= $th ?>">Concepto</th>
                <th style="<?= $th ?>">Recibido</th>
                <th style="<?= $th ?>">Compromiso</th>
                <th style="<?= $th ?>">Estado</th>
                <th style="<?= $thn ?>">Monto</th>
                <th style="<?= $thn ?>">Devuelto</th>
                <th style="<?= $thn ?>">Saldo</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($prestamos as $p): $suyas = $devoluciones[(int) $p['id']] ?? []; ?>
            <tr<?= $p['estatus'] === 'anulado' ? ' style="opacity:.55"' : '' ?>>
                <td style="<?= $td ?>">
                    <strong><?= htmlspecialchars($p['prestamista']) ?></strong>
                    <?php if ($p['telefono']): ?><br><span style="color:#64748b"><?= htmlspecialchars($p['telefono']) ?></span><?php endif; ?>
                </td>
                <td style="<?= $td ?>"><?= htmlspecialchars($p['concepto']) ?></td>
                <td style="<?= $td ?>;white-space:nowrap"><?= $dia($p['fecha_prestamo']) ?></td>
                <td style="<?= $td ?>;white-space:nowrap"><?= $dia($p['fecha_compromiso']) ?></td>
                <td style="<?= $td ?>">
                    <?php // El saldo manda: un préstamo sin saldo está cancelado,
                          // aunque la fila todavía diga activo ?>
                    <?= $p['estatus'] === 'anulado' ? 'Anulado'
                        : ($p['saldo_usd'] > 0.001 ? 'Pendiente' : 'Cancelado') ?>
                </td>
                <td style="<?= $num ?>"><?= $usd($p['monto_usd']) ?></td>
                <td style="<?= $num ?>"><?= $usd($p['devuelto_usd']) ?></td>
                <td style="<?= $num ?>"><strong><?= $usd($p['saldo_usd']) ?></strong></td>
            </tr>
            <?php foreach ($suyas as $d): ?>
            <tr style="background:#fafafa;font-size:.75rem;color:#475569">
                <td style="<?= $td ?>"></td>
                <td style="<?= $td ?>" colspan="4">
                    ↳ Devolución del <?= $dia($d['fecha_gasto']) ?> — <?= htmlspecialchars($d['concepto']) ?>
                    <?php if ($d['referencia']): ?> · ref. <?= htmlspecialchars($d['referencia']) ?><?php endif; ?>
                </td>
                <td style="<?= $num ?>"></td>
                <td style="<?= $num ?>">− <?= $usd($d['monto_usd']) ?></td>
                <td style="<?= $num ?>"></td>
            </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:.85rem;max-width:420px;margin-left:auto">
        <tbody>
            <tr><td style="<?= $td ?>">Total recibido en préstamos</td><td style="<?= $num ?>"><?= $usd($totales['recibido']) ?></td></tr>
            <tr><td style="<?= $td ?>">Total devuelto</td><td style="<?= $num ?>"><?= $usd($totales['devuelto']) ?></td></tr>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Pendiente de devolver</td>
                <td style="<?= $num ?>"><?= $usd($totales['saldo']) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

<?php // ═════════════════════════════════════════════ MATRÍCULAS
elseif ($tipo === 'matriculas'): ?>

    <p style="margin-bottom:1rem;line-height:1.6">
        Lo que le toca pagar a cada participante según su beca, lo que ha abonado y lo que
        falta. Debajo de cada uno van sus pagos, uno por uno.
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:.8rem;margin-bottom:1.25rem">
        <thead>
            <tr>
                <th style="<?= $th ?>">Participante</th>
                <th style="<?= $th ?>">Cédula</th>
                <th style="<?= $thn ?>">Beca</th>
                <th style="<?= $thn ?>">A pagar</th>
                <th style="<?= $thn ?>">Pagado</th>
                <th style="<?= $thn ?>">Saldo</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($estudiantes as $e):
            $saldo = (float) $e['total_a_pagar'] - (float) $e['total_pagado'];
            $suyos = $pagos[(int) $e['aspirante_id']] ?? [];
        ?>
            <tr>
                <td style="<?= $td ?>"><strong><?= htmlspecialchars($e['nombre']) ?></strong></td>
                <td style="<?= $td ?>"><?= htmlspecialchars($e['cedula'] ?? '') ?></td>
                <td style="<?= $num ?>"><?= (float) $e['beca_pct'] ?>%</td>
                <td style="<?= $num ?>"><?= $usd($e['total_a_pagar']) ?></td>
                <td style="<?= $num ?>"><?= $usd($e['total_pagado']) ?></td>
                <td style="<?= $num ?>"><strong><?= $usd($saldo) ?></strong></td>
            </tr>
            <?php foreach ($suyos as $p): ?>
            <tr style="background:#fafafa;font-size:.75rem;color:#475569">
                <td style="<?= $td ?>"></td>
                <td style="<?= $td ?>" colspan="3">
                    ↳ <?= $dia($p['fecha']) ?> — <?= htmlspecialchars($p['metodo_pago']) ?>
                    <?php if ($p['banco_origen']): ?> · <?= htmlspecialchars($p['banco_origen']) ?><?php endif; ?>
                    <?php if ($p['referencia']): ?> · ref. <?= htmlspecialchars($p['referencia']) ?><?php endif; ?>
                    <?php if ($p['estatus'] === 'pendiente'): ?>
                    <strong style="color:#92400e"> (por confirmar, no suma)</strong>
                    <?php endif; ?>
                    <?php if ($bs($p)): ?>
                    <br><span style="color:#64748b"><?= $bs($p) ?></span>
                    <?php endif; ?>
                </td>
                <td style="<?= $num ?>"><?= $usd($p['monto_usd']) ?></td>
                <td style="<?= $num ?>"></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$suyos): ?>
            <tr style="background:#fafafa;font-size:.75rem;color:#94a3b8">
                <td style="<?= $td ?>"></td>
                <td style="<?= $td ?>" colspan="5">↳ Todavía no ha abonado nada</td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <table style="width:100%;border-collapse:collapse;font-size:.85rem;max-width:420px;margin-left:auto">
        <tbody>
            <tr><td style="<?= $td ?>">Costo sin becas</td><td style="<?= $num ?>"><?= $usd($totales['costo_total']) ?></td></tr>
            <tr><td style="<?= $td ?>">Cubierto por becas</td><td style="<?= $num ?>"><?= $usd($totales['becado']) ?></td></tr>
            <tr><td style="<?= $td ?>">A cobrar</td><td style="<?= $num ?>"><?= $usd($totales['esperado']) ?></td></tr>
            <tr><td style="<?= $td ?>">Cobrado</td><td style="<?= $num ?>"><?= $usd($totales['cobrado']) ?></td></tr>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Por cobrar</td>
                <td style="<?= $num ?>"><?= $usd($totales['por_cobrar']) ?></td>
            </tr>
        </tbody>
    </table>

<?php // ══════════════════════════════════════════════ SEMINARIO
elseif ($tipo === 'seminario'): ?>

    <p style="margin-bottom:1rem;line-height:1.6">
        Lo que le corresponde al Seminario Teológico Bautista de Venezuela por cada
        participante. Esta plata sale de la matrícula que el participante ya paga, no es
        una deuda nueva para él.
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:.8rem;margin-bottom:1.5rem">
        <thead>
            <tr>
                <th style="<?= $th ?>">Participante</th>
                <th style="<?= $th ?>">Cargo</th>
                <th style="<?= $th ?>">Trim.</th>
                <th style="<?= $th ?>">Estado</th>
                <th style="<?= $th ?>">Pagado el</th>
                <th style="<?= $thn ?>">USD</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($estudiantes as $e): $suyos = $cargos[(int) $e['aspirante_id']] ?? []; ?>
            <?php if (!$suyos): ?>
            <tr>
                <td style="<?= $td ?>"><strong><?= htmlspecialchars($e['nombre']) ?></strong></td>
                <td style="<?= $td ?>" colspan="5"><span style="color:#94a3b8">Sin cargos generados</span></td>
            </tr>
            <?php endif; ?>
            <?php foreach ($suyos as $i => $c): ?>
            <tr>
                <td style="<?= $td ?>"><?= $i === 0 ? '<strong>' . htmlspecialchars($e['nombre']) . '</strong>' : '' ?></td>
                <td style="<?= $td ?>"><?= htmlspecialchars($c['descripcion'] ?: $c['concepto']) ?></td>
                <td style="<?= $td ?>"><?= $c['trimestre'] ?></td>
                <td style="<?= $td ?>"><?= ucfirst($c['estatus']) ?></td>
                <td style="<?= $td ?>;white-space:nowrap">
                    <?= $dia($c['fecha_pago']) ?>
                    <?php if ($c['referencia']): ?><br><span style="font-size:.72rem;color:#64748b">ref. <?= htmlspecialchars($c['referencia']) ?></span><?php endif; ?>
                </td>
                <td style="<?= $num ?>"><?= $usd($c['monto_usd']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($pagos): ?>
    <h2 style="font-size:1rem;margin:0 0 .6rem">Transferencias hechas al Seminario</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.8rem;margin-bottom:1.25rem">
        <thead>
            <tr>
                <th style="<?= $th ?>">Fecha</th><th style="<?= $th ?>">Concepto</th>
                <th style="<?= $th ?>">Referencia</th><th style="<?= $thn ?>">Cargos</th>
                <th style="<?= $thn ?>">Participantes</th><th style="<?= $thn ?>">USD</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pagos as $p): ?>
            <tr>
                <td style="<?= $td ?>;white-space:nowrap"><?= $dia($p['fecha_gasto']) ?></td>
                <td style="<?= $td ?>"><?= htmlspecialchars($p['concepto']) ?></td>
                <td style="<?= $td ?>"><?= htmlspecialchars($p['referencia'] ?? '') ?></td>
                <td style="<?= $num ?>"><?= $p['cargos'] ?></td>
                <td style="<?= $num ?>"><?= $p['participantes'] ?></td>
                <td style="<?= $num ?>"><?= $usd($p['monto_usd']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <table style="width:100%;border-collapse:collapse;font-size:.85rem;max-width:420px;margin-left:auto">
        <tbody>
            <tr><td style="<?= $td ?>">Total de cargos</td><td style="<?= $num ?>"><?= $usd($totales['total']) ?></td></tr>
            <tr><td style="<?= $td ?>">Pagado</td><td style="<?= $num ?>"><?= $usd($totales['pagado']) ?></td></tr>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Pendiente</td>
                <td style="<?= $num ?>"><?= $usd($totales['pendiente']) ?></td>
            </tr>
        </tbody>
    </table>

<?php // ═════════════════════════════════════════════════ GASTOS
elseif ($tipo === 'gastos'): ?>

    <?php $total_gastos = array_sum(array_column($rubros, 'total')); ?>

    <p style="margin-bottom:1rem;line-height:1.6">
        <?php if ($filtros['categoria']): ?>
        Solo el rubro <strong><?= htmlspecialchars($categorias[$filtros['categoria']]) ?></strong>.
        <?php else: ?>
        Todos los rubros del período.
        <?php endif; ?>
        <?php if ($filtros['cobertura'] === 'adicional'): ?>
        Solo lo que no estaba en el presupuesto, con su motivo.
        <?php elseif ($filtros['cobertura'] === 'presupuesto'): ?>
        Solo lo que estaba presupuestado.
        <?php endif; ?>
    </p>

    <h2 style="font-size:1rem;margin:0 0 .6rem">Por rubro</h2>
    <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:1.5rem">
        <thead>
            <tr>
                <th style="<?= $th ?>">Rubro</th><th style="<?= $thn ?>">Movimientos</th>
                <th style="<?= $thn ?>">Fuera de presupuesto</th><th style="<?= $thn ?>">Total USD</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rubros as $r): ?>
            <tr>
                <td style="<?= $td ?>"><?= htmlspecialchars($categorias[$r['categoria']] ?? $r['categoria']) ?></td>
                <td style="<?= $num ?>"><?= $r['cantidad'] ?></td>
                <td style="<?= $num ?>"><?= (float) $r['adicional'] ? $usd($r['adicional']) : '—' ?></td>
                <td style="<?= $num ?>"><?= $usd($r['total']) ?></td>
            </tr>
        <?php endforeach; ?>
            <tr style="font-weight:700;background:#f8fafc">
                <td style="<?= $td ?>">Total</td><td style="<?= $num ?>"></td><td style="<?= $num ?>"></td>
                <td style="<?= $num ?>"><?= $usd($total_gastos) ?></td>
            </tr>
        </tbody>
    </table>

    <h2 style="font-size:1rem;margin:0 0 .6rem">Detalle</h2>
    <?php if (!$detalle): ?>
    <p style="padding:2rem;text-align:center;color:#94a3b8">No hay gastos con esos filtros.</p>
    <?php else: ?>
    <table style="width:100%;border-collapse:collapse;font-size:.76rem">
        <thead>
            <tr>
                <th style="<?= $th ?>">Fecha</th><th style="<?= $th ?>">Concepto</th>
                <th style="<?= $th ?>">Rubro</th><th style="<?= $th ?>">Beneficiario</th>
                <th style="<?= $th ?>">Referencia</th><th style="<?= $thn ?>">USD</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($detalle as $g): ?>
            <tr>
                <td style="<?= $td ?>;white-space:nowrap"><?= $dia($g['fecha_gasto']) ?></td>
                <td style="<?= $td ?>">
                    <?= htmlspecialchars($g['concepto']) ?>
                    <?php if ($g['cobertura'] === 'adicional'): ?>
                    <br><span style="font-size:.7rem;color:#92400e">Fuera de presupuesto<?= $g['cobertura_motivo'] ? ': ' . htmlspecialchars($g['cobertura_motivo']) : '' ?></span>
                    <?php endif; ?>
                </td>
                <td style="<?= $td ?>"><?= htmlspecialchars($categorias[$g['categoria']] ?? $g['categoria']) ?></td>
                <td style="<?= $td ?>"><?= htmlspecialchars($g['beneficiario'] ?? '') ?></td>
                <td style="<?= $td ?>">
                    <?= htmlspecialchars($g['referencia'] ?? '') ?>
                    <?php if ($bs($g)): ?>
                    <br><span style="font-size:.7rem;color:#64748b"><?= $bs($g) ?></span>
                    <?php endif; ?>
                </td>
                <td style="<?= $num ?>"><?= $usd($g['monto_usd']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

<?php // ═════════════════════════════════════════════════ FONDOS
elseif ($tipo === 'fondos'): ?>

    <p style="margin-bottom:1.25rem;line-height:1.6">
        Plata que entró para un fin concreto, con los gastos que salieron de ella. Es lo que
        responde por los aportes girados para un destino, como los traslados de las prácticas.
    </p>

    <?php if (!$fondos): ?>
    <p style="padding:2rem;text-align:center;color:#94a3b8">Todavía no hay fondos creados.</p>
    <?php endif; ?>

    <?php foreach ($fondos as $f): $saldo = (float) $f['entradas'] - (float) $f['salidas']; ?>
    <section style="margin-bottom:2rem">
        <h2 style="font-size:1rem;margin:0 0 .3rem"><?= htmlspecialchars($f['nombre']) ?></h2>
        <?php if ($f['descripcion']): ?>
        <p style="margin:0 0 .6rem;color:#64748b;font-size:.8rem"><?= htmlspecialchars($f['descripcion']) ?></p>
        <?php endif; ?>

        <table style="width:100%;border-collapse:collapse;font-size:.82rem;margin-bottom:.75rem;max-width:420px">
            <tbody>
                <tr><td style="<?= $td ?>">Entró</td><td style="<?= $num ?>"><?= $usd($f['entradas']) ?></td></tr>
                <tr><td style="<?= $td ?>">Se gastó</td><td style="<?= $num ?>"><?= $usd($f['salidas']) ?></td></tr>
                <tr style="font-weight:700;background:#f8fafc">
                    <td style="<?= $td ?>">Queda</td><td style="<?= $num ?>"><?= $usd($saldo) ?></td>
                </tr>
            </tbody>
        </table>

        <?php $movs = $movimientos[$f['id']] ?? []; ?>
        <?php if ($movs): ?>
        <table style="width:100%;border-collapse:collapse;font-size:.76rem">
            <thead>
                <tr>
                    <th style="<?= $th ?>">Fecha</th><th style="<?= $th ?>">Movimiento</th>
                    <th style="<?= $th ?>">Concepto</th><th style="<?= $th ?>">Contraparte</th>
                    <th style="<?= $thn ?>">USD</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($movs as $m): ?>
                <tr>
                    <td style="<?= $td ?>;white-space:nowrap"><?= $dia($m['fecha']) ?></td>
                    <td style="<?= $td ?>"><?= $m['tipo'] === 'ingreso' ? 'Entrada' : 'Salida' ?></td>
                    <td style="<?= $td ?>">
                        <?= htmlspecialchars($m['concepto']) ?>
                        <?php if ($m['rubro']): ?>
                        <span style="color:#64748b">· <?= htmlspecialchars($categorias[$m['rubro']] ?? $m['rubro']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="<?= $td ?>"><?= htmlspecialchars($m['contraparte']) ?></td>
                    <td style="<?= $num ?>"><?= ($m['tipo'] === 'ingreso' ? '' : '− ') . $usd($m['monto_usd']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif ($uno): ?>
        <p style="padding:1.5rem;text-align:center;color:#94a3b8">Este fondo todavía no tiene movimientos.</p>
        <?php endif; ?>
    </section>
    <?php endforeach; ?>

<?php endif; ?>

    <footer style="margin-top:2.5rem;padding-top:1rem;border-top:1px solid #e2e8f0;font-size:.75rem;color:#64748b;line-height:1.6">
        Los movimientos anulados no entran en ningún total de este informe; quedan registrados
        en el sistema con su motivo.<br>
        Programa de Formación de Misioneros Integrales — CNBV/DIME ·
        misionerosintegrales.cnbv@gmail.com
    </footer>

</div>
