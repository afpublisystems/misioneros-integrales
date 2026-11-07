<?php
/**
 * Script TEMPORAL para aplicar las migraciones 015 y 016 (evangelismo:
 * visita, foto, sectores y discipulado) sin phpMyAdmin.
 * Usa la misma conexión del sistema, así que no hace falta entrar a la BD.
 *
 *   Ver qué falta:   https://misionerosintegrales.com/migrar-015-016-tmp.php?token=AF-evangelismo-2026
 *   Aplicar:         https://misionerosintegrales.com/migrar-015-016-tmp.php?token=AF-evangelismo-2026&aplicar=1
 *
 * Se puede correr más de una vez: lo que ya está hecho lo salta.
 * BORRARLO del servidor apenas termine.
 */

$TOKEN_ESPERADO = 'AF-evangelismo-2026';
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

    $hay_visita     = $hayColumna('personas_alcanzadas', 'desea_visita');
    $hay_foto       = $hayColumna('personas_alcanzadas', 'foto');
    $hay_idx_visita = $hayIndice('personas_alcanzadas', 'idx_personas_visita');
    $hay_sectores   = $hayTabla('sede_sectores');
    $hay_sector_id  = $hayColumna('personas_alcanzadas', 'sector_id');
    $hay_niveles    = $hayTabla('discipulado_niveles');
    $hay_lecciones  = $hayTabla('discipulado_lecciones');
    $hay_avance     = $hayTabla('discipulado_avance');

    if (!$aplicar) {
        $pasos[] = ['info', 'Base de datos: <code>' . htmlspecialchars((string) $db->query('SELECT DATABASE()')->fetchColumn()) . '</code>'];
        $pasos[] = ['info', 'Columna <code>personas_alcanzadas.desea_visita</code>: ' . ($hay_visita ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Columna <code>personas_alcanzadas.foto</code>: ' . ($hay_foto ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Índice <code>idx_personas_visita</code>: ' . ($hay_idx_visita ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Tabla <code>sede_sectores</code>: ' . ($hay_sectores ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Columna <code>personas_alcanzadas.sector_id</code>: ' . ($hay_sector_id ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Tabla <code>discipulado_niveles</code>: ' . ($hay_niveles ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Tabla <code>discipulado_lecciones</code>: ' . ($hay_lecciones ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Tabla <code>discipulado_avance</code>: ' . ($hay_avance ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Nada se ha tocado todavía. Agrega <code>&aplicar=1</code> a la dirección para ejecutar.'];
    } else {

        // ── 015: pedido de visita y foto ────────────────────
        if (!$hay_visita) {
            $db->exec("
                ALTER TABLE personas_alcanzadas
                    ADD COLUMN desea_visita TINYINT(1) NOT NULL DEFAULT 0
                        COMMENT 'Pidió que lo visiten. Independiente de la etapa'
                        AFTER discipulado
            ");
            $pasos[] = ['ok', 'Columna <code>personas_alcanzadas.desea_visita</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>desea_visita</code> ya existía, no se tocó.'];
        }

        if (!$hay_foto) {
            $db->exec("
                ALTER TABLE personas_alcanzadas
                    ADD COLUMN foto VARCHAR(255) NULL
                        COMMENT 'Nombre del archivo en uploads/evangelismo/'
                        AFTER desea_visita
            ");
            $pasos[] = ['ok', 'Columna <code>personas_alcanzadas.foto</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>foto</code> ya existía, no se tocó.'];
        }

        if (!$hay_idx_visita) {
            $db->exec("CREATE INDEX idx_personas_visita ON personas_alcanzadas (desea_visita)");
            $pasos[] = ['ok', 'Índice <code>idx_personas_visita</code> creado.'];
        } else {
            $pasos[] = ['salta', 'El índice <code>idx_personas_visita</code> ya existía.'];
        }

        // ── 016: sectores dentro de cada sede ───────────────
        $db->exec("
            CREATE TABLE IF NOT EXISTS sede_sectores (
                id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                sede_id     INT UNSIGNED NOT NULL,
                nombre      VARCHAR(120) NOT NULL,
                activo      TINYINT(1)   NOT NULL DEFAULT 1,
                orden       TINYINT UNSIGNED NOT NULL DEFAULT 0,
                created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_sector_sede (sede_id, nombre),
                CONSTRAINT fk_sector_sede FOREIGN KEY (sede_id)
                    REFERENCES sedes (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = [$hay_sectores ? 'salta' : 'ok', 'Tabla <code>sede_sectores</code> ' . ($hay_sectores ? 'ya existía.' : 'creada.')];

        if (!$hay_sector_id) {
            $db->exec("
                ALTER TABLE personas_alcanzadas
                    ADD COLUMN sector_id INT UNSIGNED NULL AFTER sede_id,
                    ADD KEY idx_personas_sector (sector_id),
                    ADD CONSTRAINT fk_personas_sector
                        FOREIGN KEY (sector_id) REFERENCES sede_sectores (id) ON DELETE SET NULL
            ");
            $pasos[] = ['ok', 'Columna <code>personas_alcanzadas.sector_id</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>sector_id</code> ya existía, no se tocó.'];
        }

        // Sede 1 = La Guaira. Los sectores donde están las iglesias.
        $db->exec("
            INSERT INTO sede_sectores (sede_id, nombre, orden) VALUES
                (1, 'Carayaca', 1),
                (1, 'Aguarito', 2),
                (1, 'Tirima',   3)
            ON DUPLICATE KEY UPDATE orden = VALUES(orden)
        ");
        $pasos[] = ['ok', 'Sectores de La Guaira listos: Carayaca, Aguarito y Tirima.'];

        // ── 016: discipulado ────────────────────────────────
        $db->exec("
            CREATE TABLE IF NOT EXISTS discipulado_niveles (
                id      TINYINT UNSIGNED NOT NULL,
                nombre  VARCHAR(100) NOT NULL,
                orden   TINYINT UNSIGNED NOT NULL DEFAULT 0,
                activo  TINYINT(1)   NOT NULL DEFAULT 1,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = [$hay_niveles ? 'salta' : 'ok', 'Tabla <code>discipulado_niveles</code> ' . ($hay_niveles ? 'ya existía.' : 'creada.')];

        $db->exec("
            CREATE TABLE IF NOT EXISTS discipulado_lecciones (
                id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nivel_id  TINYINT UNSIGNED NOT NULL,
                numero    TINYINT UNSIGNED NOT NULL,
                titulo    VARCHAR(150) NOT NULL,
                activa    TINYINT(1)   NOT NULL DEFAULT 1,
                PRIMARY KEY (id),
                UNIQUE KEY uq_leccion (nivel_id, numero),
                CONSTRAINT fk_leccion_nivel FOREIGN KEY (nivel_id)
                    REFERENCES discipulado_niveles (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = [$hay_lecciones ? 'salta' : 'ok', 'Tabla <code>discipulado_lecciones</code> ' . ($hay_lecciones ? 'ya existía.' : 'creada.')];

        $db->exec("
            CREATE TABLE IF NOT EXISTS discipulado_avance (
                persona_id  INT UNSIGNED NOT NULL,
                leccion_id  INT UNSIGNED NOT NULL,
                completada  DATE         NOT NULL,
                created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (persona_id, leccion_id),
                KEY idx_avance_leccion (leccion_id),
                CONSTRAINT fk_avance_persona FOREIGN KEY (persona_id)
                    REFERENCES personas_alcanzadas (id) ON DELETE CASCADE,
                CONSTRAINT fk_avance_leccion FOREIGN KEY (leccion_id)
                    REFERENCES discipulado_lecciones (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = [$hay_avance ? 'salta' : 'ok', 'Tabla <code>discipulado_avance</code> ' . ($hay_avance ? 'ya existía.' : 'creada.')];

        $db->exec("
            INSERT INTO discipulado_niveles (id, nombre, orden)
            VALUES (1, '1er Nivel de Discipulado \"Mi Identidad en Cristo\"', 1)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
        ");
        $db->exec("
            INSERT INTO discipulado_lecciones (nivel_id, numero, titulo) VALUES
                (1, 1, 'No soy yo, Cristo vive en mí'),
                (1, 2, 'Soy hijo de Dios'),
                (1, 3, 'Soy perdonado'),
                (1, 4, 'Soy heredero de la gloria de Dios'),
                (1, 5, 'Soy templo de Dios'),
                (1, 6, 'Soy escuchado y favorecido por Dios'),
                (1, 7, 'Soy sellado por el Espíritu Santo'),
                (1, 8, 'Soy miembro de la familia de Dios')
            ON DUPLICATE KEY UPDATE titulo = VALUES(titulo)
        ");
        $pasos[] = ['ok', 'Cargado el 1er Nivel de Discipulado con sus 8 lecciones.'];

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
    <title>Migraciones 015 y 016 — evangelismo</title>
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

<h1>Migraciones 015 y 016 — visita, foto, sectores y discipulado</h1>

<?php foreach ($pasos as [$tipo, $texto]): ?>
<div class="paso <?= $tipo ?>"><?= $texto ?></div>
<?php endforeach; ?>

<div class="aviso">
    <strong>Al terminar, borra este archivo del servidor.</strong>
    Mientras esté subido, cualquiera con el enlace puede ejecutarlo.
</div>

</body>
</html>
