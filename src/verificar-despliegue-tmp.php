<?php
/**
 * Verificador de despliegue. Recorre los archivos del sistema en el
 * servidor y publica una huella de cada uno, para compararla con la
 * copia local y saber qué falta subir y qué quedó viejo.
 *
 *   En el navegador:  https://misionerosintegrales.com/verificar-despliegue-tmp.php?token=AF-revision-2026
 *   Para comparar:    php tools/verificar-despliegue.php
 *
 * Publica nombres, tamaños y huellas — nunca el contenido de los archivos.
 * BORRARLO del servidor apenas termine la revisión.
 */

$TOKEN_ESPERADO = 'AF-revision-2026';
if (($_GET['token'] ?? '') !== $TOKEN_ESPERADO) {
    http_response_code(403);
    exit('No autorizado.');
}

/**
 * La huella va sobre el contenido con los saltos de línea emparejados:
 * si el FTP sube en modo texto cambia CRLF por LF y, sin esto, todos los
 * archivos saldrían distintos aunque estén iguales.
 */
function huella(string $ruta): string
{
    $c = (string) file_get_contents($ruta);
    return sha1(str_replace("\r\n", "\n", $c));
}

$base = __DIR__;

// Las carpetas que se recorren enteras y los sueltos que también importan.
$carpetas = ['app'];
$sueltos  = [
    'index.php',
    'public/css/app.css',
    'public/js/app.js',
    'public/caja.webmanifest',
    'database/.htaccess',
    'uploads/.htaccess',
    'uploads/evangelismo/.htaccess',
    'uploads/comprobantes/.htaccess',
];

$archivos = [];

foreach ($carpetas as $carpeta) {
    $raiz = $base . '/' . $carpeta;
    if (!is_dir($raiz)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($raiz, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($it as $archivo) {
        if (!$archivo->isFile()) {
            continue;
        }
        $rel = str_replace('\\', '/', substr($archivo->getPathname(), strlen($base) + 1));
        $archivos[$rel] = [
            'sha1'  => huella($archivo->getPathname()),
            'bytes' => $archivo->getSize(),
            'fecha' => date('Y-m-d H:i', $archivo->getMTime()),
        ];
    }
}

foreach ($sueltos as $rel) {
    $ruta = $base . '/' . $rel;
    if (!is_file($ruta)) {
        continue;
    }
    $archivos[$rel] = [
        'sha1'  => huella($ruta),
        'bytes' => filesize($ruta),
        'fecha' => date('Y-m-d H:i', filemtime($ruta)),
    ];
}

ksort($archivos);

// Carpetas que el sistema necesita poder escribir.
$carpetas_escritura = [];
foreach (['uploads', 'uploads/documentos', 'uploads/comprobantes',
          'uploads/evangelismo', 'public/uploads/galeria'] as $c) {
    $ruta = $base . '/' . $c;
    $carpetas_escritura[$c] = [
        'existe'   => is_dir($ruta),
        'escribir' => is_dir($ruta) && is_writable($ruta),
    ];
}

$salida = [
    'generado'  => date('Y-m-d H:i:s'),
    'php'       => PHP_VERSION,
    'archivos'  => $archivos,
    'carpetas'  => $carpetas_escritura,
];

if (($_GET['formato'] ?? '') === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    exit(json_encode($salida, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación del despliegue</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1000px; margin: 2rem auto;
               padding: 0 1.25rem; color: #1f2937; line-height: 1.5; }
        h1 { font-size: 1.4rem; }
        h2 { font-size: 1.05rem; margin-top: 2rem; }
        table { border-collapse: collapse; width: 100%; font-size: .84rem; }
        th { text-align: left; background: #1f2937; color: #f9fafb; padding: .5rem .6rem; }
        td { padding: .4rem .6rem; border-bottom: 1px solid #e5e7eb; }
        td.huella { font-family: ui-monospace, monospace; color: #6b7280; }
        .si { color: #166534; font-weight: 600; }
        .no { color: #991b1b; font-weight: 600; }
        .aviso { margin-top: 2rem; padding: 1rem; background: #fef9c3; border-radius: 8px; font-size: .9rem; }
        .resumen { padding: .8rem 1rem; background: #e0f2fe; color: #075985;
                   border-radius: 6px; font-size: .9rem; }
    </style>
</head>
<body>

<h1>Verificación del despliegue</h1>

<p class="resumen">
    <?= count($archivos) ?> archivos del sistema · PHP <?= PHP_VERSION ?> ·
    revisado el <?= date('d/m/Y \a \l\a\s H:i') ?>
</p>

<h2>Carpetas de subida</h2>
<table>
    <thead><tr><th>Carpeta</th><th>Existe</th><th>Se puede escribir</th></tr></thead>
    <tbody>
    <?php foreach ($carpetas_escritura as $c => $e): ?>
        <tr>
            <td><?= htmlspecialchars($c) ?></td>
            <td class="<?= $e['existe'] ? 'si' : 'no' ?>"><?= $e['existe'] ? 'sí' : 'no' ?></td>
            <td class="<?= $e['escribir'] ? 'si' : 'no' ?>"><?= $e['escribir'] ? 'sí' : 'no' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Archivos</h2>
<table>
    <thead><tr><th>Archivo</th><th>Subido</th><th>Tamaño</th><th>Huella</th></tr></thead>
    <tbody>
    <?php foreach ($archivos as $rel => $d): ?>
        <tr>
            <td><?= htmlspecialchars($rel) ?></td>
            <td><?= $d['fecha'] ?></td>
            <td><?= number_format($d['bytes']) ?></td>
            <td class="huella"><?= substr($d['sha1'], 0, 12) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<div class="aviso">
    <strong>Al terminar la revisión, borra este archivo del servidor.</strong>
    Mientras esté subido, cualquiera con el enlace puede ver la lista de archivos del sistema.
</div>

</body>
</html>
