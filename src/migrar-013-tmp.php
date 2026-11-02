<?php
/**
 * Script TEMPORAL para aplicar la migración 013 (enlaces de caja) sin phpMyAdmin.
 * Usa la misma conexión del sistema, así que no hace falta entrar a la BD.
 *
 *   Ver qué falta:   https://misionerosintegrales.com/migrar-013-tmp.php?token=AF-enlaces-2026
 *   Aplicar:         https://misionerosintegrales.com/migrar-013-tmp.php?token=AF-enlaces-2026&aplicar=1
 *
 * Se puede correr más de una vez: lo que ya está hecho lo salta.
 * BORRARLO del servidor apenas termine.
 */

$TOKEN_ESPERADO = 'AF-enlaces-2026';
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

    $hay_tabla = (bool) $db->query("
        SELECT COUNT(*) FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'enlaces_caja'
    ")->fetchColumn();
    $hay_columna = (bool) $db->query("
        SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos' AND COLUMN_NAME = 'enlace_id'
    ")->fetchColumn();

    if (!$aplicar) {
        $pasos[] = ['info', 'Tabla <code>enlaces_caja</code>: ' . ($hay_tabla ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Columna <code>gastos.enlace_id</code>: ' . ($hay_columna ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Nada se ha tocado todavía. Agrega <code>&aplicar=1</code> a la dirección para ejecutar.'];
    } else {
        $db->exec("
            CREATE TABLE IF NOT EXISTS enlaces_caja (
                id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre      VARCHAR(100) NOT NULL COMMENT 'Quién lo usa: sale en cada gasto que cargue',
                token_hash  CHAR(64)     NOT NULL COMMENT 'SHA-256 del token; el token no se guarda',
                activo      TINYINT      NOT NULL DEFAULT 1,
                creado_por  INT UNSIGNED NOT NULL,
                ultimo_uso  DATETIME     NULL,
                created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_enlace_token (token_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $pasos[] = [$hay_tabla ? 'salta' : 'ok', 'Tabla <code>enlaces_caja</code> ' . ($hay_tabla ? 'ya existía.' : 'creada.')];

        if (!$hay_columna) {
            $db->exec("
                ALTER TABLE gastos
                    ADD COLUMN enlace_id INT UNSIGNED NULL
                        COMMENT 'Enlace de caja por el que se cargó, si fue así'
                        AFTER registrado_por,
                    ADD KEY idx_gastos_enlace (enlace_id)
            ");
            $pasos[] = ['ok', 'Columna <code>gastos.enlace_id</code> creada.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>gastos.enlace_id</code> ya existía, no se tocó.'];
        }
    }
} catch (Throwable $e) {
    $pasos[] = ['error', 'Falló: ' . htmlspecialchars($e->getMessage())];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración 013 — enlaces de caja</title>
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

<h1>Migración 013 — enlaces para cargar gastos</h1>

<?php foreach ($pasos as [$tipo, $texto]): ?>
<div class="paso <?= $tipo ?>"><?= $texto ?></div>
<?php endforeach; ?>

<div class="aviso">
    <strong>Al terminar, borra este archivo del servidor.</strong>
    Mientras esté subido, cualquiera con el enlace puede ejecutarlo.
</div>

</body>
</html>
