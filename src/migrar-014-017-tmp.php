<?php
/**
 * Script TEMPORAL para poner la base al día con las migraciones 014 a 017
 * sin phpMyAdmin: prácticas misioneras, visita y foto en evangelismo,
 * sectores por sede, discipulado y los alcances de las prácticas.
 *
 *   Ver qué falta:   https://misionerosintegrales.com/migrar-014-017-tmp.php?token=AF-practicas-2026
 *   Aplicar:         https://misionerosintegrales.com/migrar-014-017-tmp.php?token=AF-practicas-2026&aplicar=1
 *
 * Aplica solo lo que falte y en orden, así que da igual si alguna de las
 * cuatro ya estaba. Se puede correr más de una vez.
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

    // 014
    $hay_iglesias   = $hayTabla('practica_iglesias');
    $hay_actividad  = $hayTabla('practica_actividades');
    // 015
    $hay_visita     = $hayColumna('personas_alcanzadas', 'desea_visita');
    $hay_foto       = $hayColumna('personas_alcanzadas', 'foto');
    $hay_idx_visita = $hayIndice('personas_alcanzadas', 'idx_personas_visita');
    // 016
    $hay_sectores   = $hayTabla('sede_sectores');
    $hay_sector_id  = $hayColumna('personas_alcanzadas', 'sector_id');
    $hay_niveles    = $hayTabla('discipulado_niveles');
    $hay_lecciones  = $hayTabla('discipulado_lecciones');
    $hay_avance     = $hayTabla('discipulado_avance');
    // 017
    $tipo_actual    = $hay_actividad ? $tipoDeColumna('practica_actividades', 'tipo') : '';
    $hay_tipos      = str_contains($tipo_actual, "'taller'");
    $hay_dirigida   = $hayColumna('practica_actividades', 'dirigida_a');
    $hay_leccion    = $hayColumna('practica_actividades', 'leccion_id');
    $hay_idx_lec    = $hayIndice('practica_actividades', 'idx_practica_leccion');
    $hay_fk_lec     = $hayLlave('practica_actividades', 'fk_practica_leccion');

    $si = fn(bool $b) => $b ? 'ya existe' : '<strong>falta</strong>';

    if (!$aplicar) {
        $pasos[] = ['info', 'Base de datos: <code>' . htmlspecialchars((string) $db->query('SELECT DATABASE()')->fetchColumn()) . '</code>'];

        $pasos[] = ['titulo', '014 — Prácticas misioneras'];
        $pasos[] = ['info', 'Tabla <code>practica_iglesias</code>: ' . $si($hay_iglesias)];
        $pasos[] = ['info', 'Tabla <code>practica_actividades</code>: ' . $si($hay_actividad)];
        if ($hay_actividad) {
            $n = (int) $db->query('SELECT COUNT(*) FROM practica_actividades')->fetchColumn();
            $pasos[] = ['info', "Actividades cargadas hoy: <strong>$n</strong>"];
        }

        $pasos[] = ['titulo', '015 — Pedido de visita y foto'];
        $pasos[] = ['info', 'Columna <code>personas_alcanzadas.desea_visita</code>: ' . $si($hay_visita)];
        $pasos[] = ['info', 'Columna <code>personas_alcanzadas.foto</code>: ' . $si($hay_foto)];
        $pasos[] = ['info', 'Índice <code>idx_personas_visita</code>: ' . $si($hay_idx_visita)];

        $pasos[] = ['titulo', '016 — Sectores y discipulado'];
        $pasos[] = ['info', 'Tabla <code>sede_sectores</code>: ' . $si($hay_sectores)];
        $pasos[] = ['info', 'Columna <code>personas_alcanzadas.sector_id</code>: ' . $si($hay_sector_id)];
        $pasos[] = ['info', 'Tabla <code>discipulado_niveles</code>: ' . $si($hay_niveles)];
        $pasos[] = ['info', 'Tabla <code>discipulado_lecciones</code>: ' . $si($hay_lecciones)];
        $pasos[] = ['info', 'Tabla <code>discipulado_avance</code>: ' . $si($hay_avance)];

        $pasos[] = ['titulo', '017 — Alcances y lección en las prácticas'];
        $pasos[] = ['info', 'Tipos de actividad: ' . ($hay_tipos ? 'ya están los nuevos' : '<strong>falta agregarlos</strong>')
                          . ($tipo_actual ? ' — hoy es <code>' . htmlspecialchars($tipo_actual) . '</code>' : '')];
        $pasos[] = ['info', 'Columna <code>practica_actividades.dirigida_a</code>: ' . $si($hay_dirigida)];
        $pasos[] = ['info', 'Columna <code>practica_actividades.leccion_id</code>: ' . $si($hay_leccion)];
        $pasos[] = ['info', 'Índice <code>idx_practica_leccion</code>: ' . $si($hay_idx_lec)];
        $pasos[] = ['info', 'Llave <code>fk_practica_leccion</code>: ' . $si($hay_fk_lec)];

        $pasos[] = ['info', 'Nada se ha tocado todavía. Agrega <code>&aplicar=1</code> a la dirección para ejecutar.'];
    } else {

        // ── 014: prácticas misioneras ───────────────────────
        $pasos[] = ['titulo', '014 — Prácticas misioneras'];

        $db->exec("
            CREATE TABLE IF NOT EXISTS practica_iglesias (
                id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre      VARCHAR(120) NOT NULL,
                sector      VARCHAR(120) NULL,
                activa      TINYINT      NOT NULL DEFAULT 1,
                orden       TINYINT      NOT NULL DEFAULT 0,
                created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = [$hay_iglesias ? 'salta' : 'ok', 'Tabla <code>practica_iglesias</code> ' . ($hay_iglesias ? 'ya existía.' : 'creada.')];

        $db->exec("
            CREATE TABLE IF NOT EXISTS practica_actividades (
                id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
                iglesia_id      INT UNSIGNED NOT NULL,
                fecha           DATE         NOT NULL,
                tipo            ENUM('culto','celula','evangelismo','especial','apoyo') NOT NULL,
                descripcion     VARCHAR(255) NULL,
                adultos         SMALLINT UNSIGNED NULL,
                jovenes         SMALLINT UNSIGNED NULL,
                ninos           SMALLINT UNSIGNED NULL,
                comunidad       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                equipo          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                evangelizados   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                decisiones      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                discipulado     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                registrado_por  INT UNSIGNED NULL,
                created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_practica_fecha (fecha),
                KEY idx_practica_iglesia (iglesia_id),
                CONSTRAINT fk_practica_iglesia FOREIGN KEY (iglesia_id) REFERENCES practica_iglesias (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = [$hay_actividad ? 'salta' : 'ok', 'Tabla <code>practica_actividades</code> ' . ($hay_actividad ? 'ya existía.' : 'creada.')];

        $db->exec("
            INSERT INTO practica_iglesias (id, nombre, sector, orden) VALUES
                (1, 'IB Casa de Dios',    'Aguarito', 1),
                (2, 'IB Gente de Dios',   'Tirima',   2),
                (3, 'IB Luz y Esperanza', 'Carayaca', 3)
            ON DUPLICATE KEY UPDATE nombre = VALUES(nombre)
        ");
        $pasos[] = ['ok', 'Las 3 iglesias de Carayaca listas.'];

        // Los reportes de Carayaca solo si la tabla está vacía: el INSERT no
        // tiene con qué reconocer un duplicado y cargaría todo dos veces.
        $cuantas = (int) $db->query('SELECT COUNT(*) FROM practica_actividades')->fetchColumn();
        if ($cuantas === 0) {
            $db->exec("
                INSERT INTO practica_actividades
                    (iglesia_id, fecha, tipo, descripcion, adultos, jovenes, ninos, comunidad, equipo, evangelizados, decisiones, discipulado)
                VALUES
                    (2, '2026-10-17', 'especial',    'Culto especial',                                              NULL, NULL, NULL, 23, 0,  0, 0, 0),
                    (2, '2026-10-18', 'culto',       NULL,                                                          11,   3,    2,    16, 6,  0, 0, 0),
                    (2, '2026-10-23', 'especial',    'Reunión sobre principios bautistas',                          NULL, NULL, NULL, 10, 0,  0, 0, 0),
                    (2, '2026-10-24', 'especial',    'Actividad deportiva: futbolito y kickingball',                NULL, NULL, NULL, 87, 0,  0, 0, 0),
                    (2, '2026-10-25', 'evangelismo', 'Evangelismo del 16 al 25 de octubre, sin desglose por semana', NULL, NULL, NULL, 0, 0, 52, 9, 1),
                    (2, '2026-10-25', 'culto',       NULL,                                                          17,   2,    3,    22, 5,  0, 0, 0),

                    (1, '2026-10-30', 'evangelismo', 'Evangelismo entre el viernes en la tarde y el sábado en la mañana', NULL, NULL, NULL, 0,  0,  7, 2, 0),
                    (1, '2026-10-31', 'celula',      'Célula en una casa de la comunidad. 1.ª lección de Mi identidad en Cristo', NULL, NULL, NULL, 9, 6, 0, 0, 6),
                    (1, '2026-11-01', 'culto',       NULL,                                                          6,    1,    0,    7,  4,  0, 0, 0),

                    (2, '2026-10-31', 'especial',    'Reflexiones de esperanza con las damas: la historia del hijo pródigo', 8, 3, 0, 11, 0, 0, 0, 0),
                    (2, '2026-10-31', 'evangelismo', NULL,                                                          NULL, NULL, NULL, 0,  0, 10, 7, 7),
                    (2, '2026-11-01', 'culto',       NULL,                                                          15,   7,    2,    24, 6,  0, 0, 0),

                    (3, '2026-10-30', 'apoyo',       'Vigilia regional de jóvenes de La Guaira en la IB Bethesda: enseñanza, oración, testimonios y cantos', NULL, NULL, NULL, 54, 0, 0, 0, 0),
                    (3, '2026-10-31', 'celula',      'Culto en un hogar: oración, enseñanza y alabanza',             NULL, NULL, NULL, 15, 0,  0, 0, 0),
                    (3, '2026-10-31', 'evangelismo', 'Evangelismo a 2 damas, que comenzaron con la 1.ª lección de Mi identidad en Cristo', NULL, NULL, NULL, 0, 0, 2, 0, 2),
                    (3, '2026-11-01', 'culto',       NULL,                                                          14,   7,    3,    24, 7,  0, 0, 0)
            ");
            $pasos[] = ['ok', 'Cargados los 16 reportes de Carayaca hasta el 1/11/2026.'];
        } else {
            $pasos[] = ['salta', "La tabla ya tenía $cuantas actividades, no se cargaron los reportes de Carayaca."];
        }

        // ── 015: pedido de visita y foto ────────────────────
        $pasos[] = ['titulo', '015 — Pedido de visita y foto'];

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
        $pasos[] = ['titulo', '016 — Sectores y discipulado'];

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

        // ── 017: alcances y lección en las prácticas ────────
        $pasos[] = ['titulo', '017 — Alcances y lección en las prácticas'];

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

        if (!$hay_idx_lec) {
            $db->exec("CREATE INDEX idx_practica_leccion ON practica_actividades (leccion_id)");
            $pasos[] = ['ok', 'Índice <code>idx_practica_leccion</code> creado.'];
        } else {
            $pasos[] = ['salta', 'El índice <code>idx_practica_leccion</code> ya existía.'];
        }

        if (!$hay_fk_lec) {
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
    $pasos[] = ['info', 'Lo que alcanzó a hacer quedó aplicado. Corrige y vuelve a correrlo: '
                      . 'retoma donde se quedó.'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migraciones 014 a 017 — prácticas, evangelismo y discipulado</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 2rem auto;
               padding: 0 1.25rem; color: #1f2937; line-height: 1.6; }
        h1 { font-size: 1.4rem; }
        .paso { padding: .6rem .9rem; border-radius: 6px; margin-bottom: .5rem; font-size: .9rem; }
        .ok     { background: #dcfce7; color: #166534; }
        .salta  { background: #f1f5f9; color: #475569; }
        .info   { background: #e0f2fe; color: #075985; }
        .error  { background: #fee2e2; color: #991b1b; }
        .titulo { background: #1f2937; color: #f9fafb; font-weight: 700;
                  margin-top: 1.4rem; letter-spacing: .02em; }
        .aviso  { margin-top: 2rem; padding: 1rem; background: #fef9c3; border-radius: 8px; font-size: .9rem; }
        code { background: rgba(0,0,0,.06); padding: .1rem .3rem; border-radius: 4px; }
        .titulo code { background: rgba(255,255,255,.15); }
    </style>
</head>
<body>

<h1>Migraciones 014 a 017 — prácticas, evangelismo y discipulado</h1>

<?php foreach ($pasos as [$tipo, $texto]): ?>
<div class="paso <?= $tipo ?>"><?= $texto ?></div>
<?php endforeach; ?>

<div class="aviso">
    <strong>Al terminar, borra este archivo del servidor.</strong>
    Mientras esté subido, cualquiera con el enlace puede ejecutarlo.
</div>

</body>
</html>
