<?php
/**
 * Solicitud de traslado de fondos — hoja para tesorería.
 * Se imprime o se guarda en PDF desde el navegador.
 */
$estatus_texto = TrasladoModel::ESTATUS[$solicitud['estatus']] ?? $solicitud['estatus'];
?>

<div class="hoja__acciones">
    <a href="/admin/finanzas/traslados"><i class="fas fa-arrow-left"></i> Volver</a>
    <a href="/admin/finanzas/exportar?tipo=traslado&id=<?= (int) $solicitud['id'] ?>">
        <i class="fas fa-file-csv"></i> Descargar CSV
    </a>
    <button class="primario" onclick="window.print()"><i class="fas fa-print"></i> Imprimir / PDF</button>
</div>

<div class="hoja">

    <header style="border-bottom:2px solid #167a5e;padding-bottom:1rem;margin-bottom:1.5rem">
        <h1 style="margin:0;font-size:1.25rem;color:#0f172a">Solicitud de traslado de fondos</h1>
        <p style="margin:.35rem 0 0;color:#475569">
            Programa Misioneros Integrales — CNBV/DIME<br>
            Dirigida a la Tesorería de la Convención Nacional Bautista de Venezuela
        </p>
    </header>

    <table style="width:100%;margin-bottom:1.5rem;border-collapse:collapse">
        <tr>
            <td style="padding:.2rem 0"><strong>Solicitud:</strong> <?= htmlspecialchars($solicitud['codigo']) ?></td>
            <td style="padding:.2rem 0"><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($solicitud['fecha'])) ?></td>
        </tr>
        <tr>
            <td style="padding:.2rem 0"><strong>Concepto:</strong> <?= htmlspecialchars($solicitud['concepto']) ?></td>
            <td style="padding:.2rem 0"><strong>Estado:</strong> <?= htmlspecialchars($estatus_texto) ?></td>
        </tr>
        <?php if (!empty($solicitud['destino'])): ?>
        <tr>
            <td colspan="2" style="padding:.2rem 0"><strong>Trasladar a:</strong> <?= htmlspecialchars($solicitud['destino']) ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <p style="margin-bottom:1rem;line-height:1.6">
        A continuación el detalle de los pagos recibidos en la cuenta de la Convención por concepto
        de matrícula del programa, para su verificación y posterior traslado.
    </p>

    <table style="width:100%;border-collapse:collapse;font-size:.8rem">
        <thead>
            <tr style="background:#f1f5f9;text-align:left">
                <th style="padding:.5rem;border:1px solid #e2e8f0">#</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0">Fecha</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0">Participante</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0">Cédula</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0">Banco origen</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0">Referencia</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0;text-align:right">Monto Bs</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0;text-align:right">Tasa</th>
                <th style="padding:.5rem;border:1px solid #e2e8f0;text-align:right">Monto USD</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pagos as $i => $p): ?>
            <tr<?= $p['estatus'] === 'anulado' ? ' style="opacity:.5;text-decoration:line-through"' : '' ?>>
                <td style="padding:.45rem;border:1px solid #e2e8f0"><?= $i + 1 ?></td>
                <td style="padding:.45rem;border:1px solid #e2e8f0;white-space:nowrap"><?= date('d/m/Y', strtotime($p['fecha'])) ?></td>
                <td style="padding:.45rem;border:1px solid #e2e8f0"><?= htmlspecialchars($p['de_quien'] ?? '—') ?></td>
                <td style="padding:.45rem;border:1px solid #e2e8f0"><?= htmlspecialchars($p['cedula'] ?? '—') ?></td>
                <td style="padding:.45rem;border:1px solid #e2e8f0"><?= htmlspecialchars($p['banco_origen'] ?: '—') ?></td>
                <td style="padding:.45rem;border:1px solid #e2e8f0"><?= htmlspecialchars($p['referencia'] ?: '—') ?></td>
                <td style="padding:.45rem;border:1px solid #e2e8f0;text-align:right;white-space:nowrap">
                    <?= $p['monto_ves'] ? number_format($p['monto_ves'], 2) : '—' ?>
                </td>
                <td style="padding:.45rem;border:1px solid #e2e8f0;text-align:right">
                    <?= $p['tasa_cambio'] ? number_format($p['tasa_cambio'], 2) : '—' ?>
                </td>
                <td style="padding:.45rem;border:1px solid #e2e8f0;text-align:right;white-space:nowrap">
                    <?= number_format($p['monto_usd'], 2) ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background:#f8fafc;font-weight:700">
                <td colspan="6" style="padding:.55rem;border:1px solid #e2e8f0;text-align:right">TOTAL</td>
                <td style="padding:.55rem;border:1px solid #e2e8f0;text-align:right">Bs <?= number_format($solicitud['total_ves'], 2) ?></td>
                <td style="border:1px solid #e2e8f0"></td>
                <td style="padding:.55rem;border:1px solid #e2e8f0;text-align:right">$<?= number_format($solicitud['total_usd'], 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($solicitud['notas'])): ?>
    <p style="margin-top:1.25rem"><strong>Observaciones:</strong> <?= nl2br(htmlspecialchars($solicitud['notas'])) ?></p>
    <?php endif; ?>

    <div style="margin-top:3.5rem;display:flex;gap:3rem;justify-content:space-between">
        <div style="flex:1;text-align:center">
            <div style="border-top:1px solid #94a3b8;padding-top:.4rem">
                <?= htmlspecialchars($solicitud['creada_por_nombre'] ?? '') ?><br>
                <small style="color:#64748b">Coordinación Misioneros Integrales</small>
            </div>
        </div>
        <div style="flex:1;text-align:center">
            <div style="border-top:1px solid #94a3b8;padding-top:.4rem">
                <small style="color:#64748b">Tesorería CNBV — recibido y verificado</small>
            </div>
        </div>
    </div>

    <p style="margin-top:2rem;color:#94a3b8;font-size:.7rem">
        Generado el <?= date('d/m/Y \a \l\a\s H:i') ?> desde el sistema de Misioneros Integrales.
    </p>
</div>
