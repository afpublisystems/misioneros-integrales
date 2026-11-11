<?php
/**
 * Script TEMPORAL para aplicar la migración 017 (tipos de alcance,
 * organización a la que va dirigida y lección de discipulado en las
 * actividades de prácticas) sin phpMyAdmin.
 *
 *   Ver qué falta:   https://misionerosintegrales.com/migrar-017-tmp.php?token=AF-practicas-2026
 *   Aplicar:         https://misionerosintegrales.com/migrar-017-tmp.php?token=AF-practicas-2026&aplicar=1
 *
 * Necesita la 016 aplicada antes: la lección se amarra a
 * discipulado_lecciones. El script lo verifica y avisa.
 *
 * Se puede correr más de una vez: lo que ya está hecho lo salta.
 * BORRARLO del servidor apenas termine.
 */

$TOKEN_ESPERADO = 'AF-practicas-2026';
if (($_GET['token'] ?? '') !== $TOKEN_ESPERADO) {
    http_response_code(403);
    exit('No autorizado.');
}

require_once __DIR__ . '/app/config/db.php';

header('Content-Type: text/html; charset=utf-8');

$aplicar = isset($_GET['aplicar']);
$pasos   = [];

try {
    $db = Database::getConnection();

    $hayTabla = function (string $tabla) use ($db): bool {
        $s = $db->prepare("SELECT COUNT(*) FROM information_schema.TABLES
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $s->execute([$tabla]);
        return (bool) $s->fetchColumn();
    };
    $hayColumna = function (string $tabla, string $columna) use ($db): bool {
        $s = $db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $s->execute([$tabla, $columna]);
        return (bool) $s->fetchColumn();
    };
    $hayIndice = function (string $tabla, string $indice) use ($db): bool {
        $s = $db->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
        $s->execute([$tabla, $indice]);
        return (bool) $s->fetchColumn();
    };
    $hayLlave = function (string $tabla, string $llave) use ($db): bool {
        $s = $db->prepare("SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
                              AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'");
        $s->execute([$tabla, $llave]);
        return (bool) $s->fetchColumn();
    };
    $tipoDeColumna = function (string $tabla, string $columna) use ($db): string {
        $s = $db->prepare("SELECT COLUMN_TYPE FROM information_schema.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $s->execute([$tabla, $columna]);
        return (string) $s->fetchColumn();
    };

    $hay_lecciones = $hayTabla('discipulado_lecciones');
    $tipo_actual   = $tipoDeColumna('practica_actividades', 'tipo');
    $hay_tipos     = str_contains($tipo_actual, "'taller'");
    $hay_dirigida  = $hayColumna('practica_actividades', 'dirigida_a');
    $hay_leccion   = $hayColumna('practica_actividades', 'leccion_id');
    $hay_idx       = $hayIndice('practica_actividades', 'idx_practica_leccion');
    $hay_fk        = $hayLlave('practica_actividades', 'fk_practica_leccion');

    if (!$hay_lecciones) {
        $pasos[] = ['error', 'Falta la tabla <code>discipulado_lecciones</code>. '
                           . 'Corre primero <code>migrar-015-016-tmp.php</code> y después vuelve acá.'];
    } elseif (!$aplicar) {
        $pasos[] = ['info', 'Base de datos: <code>' . htmlspecialchars((string) $db->query('SELECT DATABASE()')->fetchColumn()) . '</code>'];
        $pasos[] = ['info', 'Tipos de actividad: ' . ($hay_tipos ? 'ya están los nuevos' : 'falta agregarlos')
                          . ' — hoy es <code>' . htmlspecialchars($tipo_actual) . '</code>'];
        $pasos[] = ['info', 'Columna <code>practica_actividades.dirigida_a</code>: ' . ($hay_dirigida ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Columna <code>practica_actividades.leccion_id</code>: ' . ($hay_leccion ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Índice <code>idx_practica_leccion</code>: ' . ($hay_idx ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Llave <code>fk_practica_leccion</code>: ' . ($hay_fk ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Nada se ha tocado todavía. Agrega <code>&aplicar=1</code> a la dirección para ejecutar.'];
    } else {

        // ── Tipos de alcance ────────────────────────────────
        if (!$hay_tipos) {
            $db->exec("
                ALTER TABLE practica_actividades
                    MODIFY COLUMN tipo ENUM(
                        'culto','celula','evangelismo','taller','deportiva',
                        'vigilia','organizacion','apoyo','especial'
                    ) NOT NULL
            ");
            $pasos[] = ['ok', 'Tipos de actividad ampliados: taller, deportiva, vigilia y reunión por organización.'];
        } else {
            $pasos[] = ['salta', 'Los tipos nuevos ya estaban, no se tocó la columna.'];
        }

        // ── A quién va dirigida la reunión ──────────────────
        if (!$hay_dirigida) {
            $db->exec("
                ALTER TABLE practica_actividades
                    ADD COLUMN dirigida_a SET('damas','caballeros','jovenes','ninos','matrimonios') NULL
                        COMMENT 'Solo en las reuniones por organización'
                        AFTER descripcion
            ");
            $pasos[] = ['ok', 'Columna <code>practica_actividades.dirigida_a</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>dirigida_a</code> ya existía, no se tocó.'];
        }

        // ── Lección de discipulado que se dio ───────────────
        if (!$hay_leccion) {
            $db->exec("
                ALTER TABLE practica_actividades
                    ADD COLUMN leccion_id INT UNSIGNED NULL
                        COMMENT 'Lección de discipulado que se dio en la actividad'
                        AFTER discipulado
            ");
            $pasos[] = ['ok', 'Columna <code>practica_actividades.leccion_id</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>leccion_id</code> ya existía, no se tocó.'];
        }

        if (!$hay_idx) {
            $db->exec("CREATE INDEX idx_practica_leccion ON practica_actividades (leccion_id)");
            $pasos[] = ['ok', 'Índice <code>idx_practica_leccion</code> creado.'];
        } else {
            $pasos[] = ['salta', 'El índice <code>idx_practica_leccion</code> ya existía.'];
        }

        if (!$hay_fk) {
            $db->exec("
                ALTER TABLE practica_actividades
                    ADD CONSTRAINT fk_practica_leccion
                        FOREIGN KEY (leccion_id) REFERENCES discipulado_lecciones (id)
                        ON DELETE SET NULL
            ");
            $pasos[] = ['ok', 'Llave <code>fk_practica_leccion</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La llave <code>fk_practica_leccion</code> ya existía.'];
        }

        $pasos[] = ['info', 'Listo. Ahora borra este archivo del servidor.'];
    }
} catch (Throwable $e) {
    $pasos[] = ['error', 'Falló: ' . htmlspecialchars($e->getMessage())];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración 017 — prácticas misioneras</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 2rem auto;
               padding: 0 1.25rem; color: #1f2937; line-height: 1.6; }
        h1 { font-size: 1.4rem; }
        .paso { padding: .6rem .9rem; border-radius: 6px; margin-bottom: .5rem; font-size: .9rem; }
        .ok    { background: #dcfce7; color: #166534; }
        .salta { background: #f1f5f9; color: #475569; }
        .info  { background: #e0f2fe; color: #075985; }
        .error { background: #fee2e2; color: #991b1b; }
        .aviso { margin-top: 2rem; padding: 1rem; background: #fef9c3; border-radius: 8px; font-size: .9rem; }
        code { background: #f1f5f9; padding: .1rem .3rem; border-radius: 4px; }
    </style>
</head>
<body>

<h1>Migración 017 — alcances y discipulado en las prácticas</h1>

<?php foreach ($pasos as [$tipo, $texto]): ?>
<div class="paso <?= $tipo ?>"><?= $texto ?></div>
<?php endforeach; ?>

<div class="aviso">
    <strong>Al terminar, borra este archivo del servidor.</strong>
    Mientras esté subido, cualquiera con el enlace puede ejecutarlo.
</div>

</body>
</html>
