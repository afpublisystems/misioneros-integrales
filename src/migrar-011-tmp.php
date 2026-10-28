<?php
/**
 * Script TEMPORAL para aplicar la migración 011 (cohortes) sin phpMyAdmin.
 * Usa la misma conexión del sistema, así que no hace falta entrar a la BD.
 *
 *   Ver qué falta:   https://misionerosintegrales.com/migrar-011-tmp.php?token=AF-cohortes-2026
 *   Aplicar:         https://misionerosintegrales.com/migrar-011-tmp.php?token=AF-cohortes-2026&aplicar=1
 *
 * Se puede correr más de una vez: lo que ya está hecho lo salta.
 * BORRARLO del servidor apenas termine.
 */

$TOKEN_ESPERADO = 'AF-cohortes-2026';
if (($_GET['token'] ?? '') !== $TOKEN_ESPERADO) {
    http_response_code(403);
    exit('No autorizado.');
}

require_once __DIR__ . '/app/config/db.php';

header('Content-Type: text/html; charset=utf-8');

$aplicar = isset($_GET['aplicar']);
$pasos   = [];

/** Retratos de la 1era cohorte, por nombre de pila */
$RETRATOS = [
    'abraham.jpg'  => "nombres LIKE 'Abraham%'",
    'adrian.jpg'   => "nombres LIKE 'Adri_n%'",
    'anais.jpg'    => "(nombres LIKE '%Ana_s%' OR nombres LIKE 'Marianyeli%')",
    'dairenis.jpg' => "nombres LIKE 'Dairenis%'",
    'deilimar.jpg' => "nombres LIKE 'Deilimar%'",
    'diosmely.jpg' => "nombres LIKE 'Diosmel%'",
    'eric.jpg'     => "nombres LIKE 'Eric%'",
    'jose.jpg'     => "nombres LIKE 'Jos_ %' AND apellidos LIKE 'Hern_ndez%'",
    'josh.jpg'     => "apellidos LIKE 'Galindo%'",   // Eliezer Josué
    'josue.jpg'    => "apellidos LIKE 'Heredia%'",   // Jonathan Josué
    'juan.jpg'     => "nombres LIKE 'Juan%'",
    'meiver.jpg'   => "nombres LIKE 'Meiver%'",
    'natasha.jpg'  => "nombres LIKE '%Natasha%'",    // Schneider Natasha
    'samuel.jpg'   => "nombres LIKE 'Samuel%'",
];

try {
    $db = Database::getConnection();

    $columnas = $db->query("
        SELECT COLUMN_NAME FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'aspirantes'
    ")->fetchAll(PDO::FETCH_COLUMN);

    $hay_cohorte = in_array('cohorte', $columnas, true);
    $hay_foto    = in_array('foto_publica', $columnas, true);

    if (!$aplicar) {
        $pasos[] = ['info', 'Columna <code>cohorte</code>: ' . ($hay_cohorte ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Columna <code>foto_publica</code>: ' . ($hay_foto ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Nada se ha tocado todavía. Agrega <code>&aplicar=1</code> a la dirección para ejecutar.'];
    } else {
        if (!$hay_cohorte) {
            $db->exec("
                ALTER TABLE aspirantes
                    ADD COLUMN cohorte TINYINT UNSIGNED NOT NULL DEFAULT 2
                        COMMENT '1 = cohorte en curso; 2 = convocatoria septiembre 2027'
                        AFTER estatus,
                    ADD KEY idx_aspirantes_cohorte (cohorte)
            ");
            // Solo al crearla: lo que ya estaba registrado es la 1era cohorte.
            // Si la columna ya existía, esto marcaría como cohorte 1 a los nuevos.
            $n = $db->exec("UPDATE aspirantes SET cohorte = 1");
            $pasos[] = ['ok', "Columna <code>cohorte</code> creada. $n postulaciones quedaron en la 1era cohorte."];
        } else {
            $pasos[] = ['salta', 'La columna <code>cohorte</code> ya existía, no se tocó.'];
        }

        if (!$hay_foto) {
            $db->exec("
                ALTER TABLE aspirantes
                    ADD COLUMN foto_publica VARCHAR(120) NULL
                        COMMENT 'Archivo en public/assets/cohorte<N>/'
                        AFTER cohorte
            ");
            $pasos[] = ['ok', 'Columna <code>foto_publica</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>foto_publica</code> ya existía, no se tocó.'];
        }

        $asignados = 0;
        foreach ($RETRATOS as $archivo => $condicion) {
            $sql = "UPDATE aspirantes SET foto_publica = :f WHERE cohorte = 1 AND ($condicion)";
            $stmt = $db->prepare($sql);
            $stmt->execute([':f' => $archivo]);
            $asignados += $stmt->rowCount();
        }
        $pasos[] = ['ok', "Retratos asignados: $asignados."];
    }

    $cohorte1 = $hay_cohorte || $aplicar
        ? $db->query("
            SELECT cedula, nombres, apellidos, estatus, foto_publica
            FROM aspirantes WHERE cohorte = 1 ORDER BY nombres
        ")->fetchAll()
        : [];

} catch (Throwable $e) {
    $pasos[] = ['error', 'Falló: ' . htmlspecialchars($e->getMessage())];
    $cohorte1 = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración 011 — cohortes</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 2rem auto;
               padding: 0 1.25rem; color: #1f2937; line-height: 1.6; }
        h1 { font-size: 1.4rem; }
        .paso { padding: .6rem .9rem; border-radius: 6px; margin-bottom: .5rem; font-size: .9rem; }
        .ok    { background: #dcfce7; color: #166534; }
        .salta { background: #f1f5f9; color: #475569; }
        .info  { background: #e0f2fe; color: #075985; }
        .error { background: #fee2e2; color: #991b1b; }
        table { border-collapse: collapse; width: 100%; margin-top: 1.5rem; font-size: .85rem; }
        th, td { border: 1px solid #e2e8f0; padding: .45rem .6rem; text-align: left; }
        th { background: #f8fafc; }
        .falta { color: #b91c1c; font-weight: 700; }
        .aviso { margin-top: 2rem; padding: 1rem; background: #fef9c3; border-radius: 8px; font-size: .9rem; }
        code { background: #f1f5f9; padding: .1rem .3rem; border-radius: 4px; }
    </style>
</head>
<body>

<h1>Migración 011 — cohortes y retratos</h1>

<?php foreach ($pasos as [$tipo, $texto]): ?>
<div class="paso <?= $tipo ?>"><?= $texto ?></div>
<?php endforeach; ?>

<?php if (!empty($cohorte1)): ?>
<h2 style="font-size:1.05rem;margin-top:2rem">1era cohorte — <?= count($cohorte1) ?> participantes</h2>
<table>
    <thead>
        <tr><th>Cédula</th><th>Nombre</th><th>Estatus</th><th>Retrato</th></tr>
    </thead>
    <tbody>
    <?php foreach ($cohorte1 as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['cedula']) ?></td>
            <td><?= htmlspecialchars($p['nombres'] . ' ' . $p['apellidos']) ?></td>
            <td><?= htmlspecialchars($p['estatus']) ?></td>
            <td>
                <?= $p['foto_publica']
                    ? htmlspecialchars($p['foto_publica'])
                    : '<span class="falta">sin retrato</span>' ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<p style="font-size:.85rem;color:#64748b">
    A quien le falte el retrato, se le asigna desde esta misma página agregando
    <code>&cedula=V-12345678&foto=natasha.jpg</code>… o se corrige después en la base de datos.
</p>
<?php endif; ?>

<?php
// Asignación suelta, por si algún nombre no coincidió con la plantilla
if ($aplicar && !empty($_GET['cedula']) && !empty($_GET['foto'])) {
    try {
        $stmt = $db->prepare("UPDATE aspirantes SET foto_publica = :f WHERE cedula = :c");
        $stmt->execute([':f' => $_GET['foto'], ':c' => $_GET['cedula']]);
        echo '<div class="paso ok">Retrato asignado a ' . htmlspecialchars($_GET['cedula'])
           . ' (' . $stmt->rowCount() . ' fila). Recarga sin esos dos datos para ver la tabla al día.</div>';
    } catch (Throwable $e) {
        echo '<div class="paso error">No se pudo asignar: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<div class="aviso">
    <strong>Al terminar, borra este archivo del servidor.</strong>
    Mientras esté subido, cualquiera con el enlace puede ejecutarlo.
</div>

</body>
</html>
