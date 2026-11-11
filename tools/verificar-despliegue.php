<?php
/**
 * Compara los archivos de src/ con los que están subidos en producción
 * y dice qué falta, qué quedó viejo y qué sobra.
 *
 * Antes de correrlo hay que subir src/verificar-despliegue-tmp.php
 * al servidor, y borrarlo al terminar la revisión.
 *
 * Acá no hay PHP de consola fuera de Docker, así que se corre en el
 * contenedor web, que ya tiene src/ montado en /var/www/html:
 *
 *   docker cp tools/verificar-despliegue.php misioneros-integrales-web-1:/tmp/v.php
 *   docker exec misioneros-integrales-web-1 php /tmp/v.php
 *
 * El primer argumento cambia la dirección a revisar y --src= la carpeta
 * local con la que se compara.
 */

$URL = null;
$SRC = null;
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--src=')) {
        $SRC = substr($arg, 6);
    } else {
        $URL = $arg;
    }
}

$URL ??= 'https://misionerosintegrales.com/verificar-despliegue-tmp.php?token=AF-revision-2026';
$SRC ??= is_dir(dirname(__DIR__) . '/src') ? dirname(__DIR__) . '/src' : '/var/www/html';

if (!is_dir($SRC)) {
    exit("No encuentro la carpeta local del sistema. Pásala con --src=\n");
}

$url = $URL . (str_contains($URL, '?') ? '&' : '?') . 'formato=json';

echo "Leyendo el servidor...\n";
$crudo = @file_get_contents($url);
if ($crudo === false) {
    exit("No se pudo leer $URL\nRevisa que el archivo esté subido y que el token sea el correcto.\n");
}

$remoto = json_decode($crudo, true);
if (!is_array($remoto) || !isset($remoto['archivos'])) {
    exit("El servidor no devolvió la lista esperada. Esto respondió:\n"
       . substr($crudo, 0, 400) . "\n");
}

// Los saltos de línea se emparejan de los dos lados: el FTP en modo texto
// cambia CRLF por LF y si no, todo saldría distinto estando igual.
$huella = fn(string $ruta) => sha1(str_replace("\r\n", "\n", (string) file_get_contents($ruta)));

$local    = [];
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

foreach ($carpetas as $carpeta) {
    $raiz = $SRC . '/' . $carpeta;
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
        $rel = str_replace('\\', '/', substr($archivo->getPathname(), strlen($SRC) + 1));
        $local[$rel] = $huella($archivo->getPathname());
    }
}
foreach ($sueltos as $rel) {
    $ruta = $SRC . '/' . $rel;
    if (is_file($ruta)) {
        $local[$rel] = $huella($ruta);
    }
}

ksort($local);

$faltan = $viejos = $sobran = [];
foreach ($local as $rel => $sha) {
    if (!isset($remoto['archivos'][$rel])) {
        $faltan[] = $rel;
    } elseif ($remoto['archivos'][$rel]['sha1'] !== $sha) {
        $viejos[$rel] = $remoto['archivos'][$rel]['fecha'];
    }
}
foreach (array_keys($remoto['archivos']) as $rel) {
    if (!isset($local[$rel])) {
        $sobran[] = $rel;
    }
}

$linea = str_repeat('─', 66);
echo "$linea\n";
echo "Servidor revisado el {$remoto['generado']} · PHP {$remoto['php']}\n";
echo count($local) . " archivos acá · " . count($remoto['archivos']) . " allá\n";
echo "$linea\n\n";

if ($faltan) {
    echo "SIN SUBIR (" . count($faltan) . ") — no están en el servidor:\n";
    foreach ($faltan as $rel) {
        echo "   $rel\n";
    }
    echo "\n";
}

if ($viejos) {
    echo "DESACTUALIZADOS (" . count($viejos) . ") — allá hay una versión distinta:\n";
    foreach ($viejos as $rel => $fecha) {
        echo "   $rel   (subido el $fecha)\n";
    }
    echo "\n";
}

if ($sobran) {
    echo "SOBRAN (" . count($sobran) . ") — están en el servidor y no acá:\n";
    foreach ($sobran as $rel) {
        echo "   $rel\n";
    }
    echo "\n";
}

$problemas = [];
foreach ($remoto['carpetas'] as $c => $e) {
    if (!$e['existe']) {
        $problemas[] = "   $c — no existe";
    } elseif (!$e['escribir']) {
        $problemas[] = "   $c — no se puede escribir";
    }
}
if ($problemas) {
    echo "CARPETAS CON PROBLEMAS:\n" . implode("\n", $problemas) . "\n\n";
}

if (!$faltan && !$viejos && !$sobran && !$problemas) {
    echo "Todo igual. No hay nada pendiente de subir.\n";
} else {
    echo "Al terminar de subir, vuelve a correr esto para confirmar.\n";
}
