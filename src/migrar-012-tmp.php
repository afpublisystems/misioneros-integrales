<?php
/**
 * Script TEMPORAL para aplicar la migración 012 (presupuesto) sin phpMyAdmin.
 * Usa la misma conexión del sistema, así que no hace falta entrar a la BD.
 *
 *   Ver qué falta:   https://misionerosintegrales.com/migrar-012-tmp.php?token=AF-presupuesto-2026
 *   Aplicar:         https://misionerosintegrales.com/migrar-012-tmp.php?token=AF-presupuesto-2026&aplicar=1
 *
 * Se puede correr más de una vez: lo que ya está hecho lo salta.
 * BORRARLO del servidor apenas termine.
 */

$TOKEN_ESPERADO = 'AF-presupuesto-2026';
if (($_GET['token'] ?? '') !== $TOKEN_ESPERADO) {
    http_response_code(403);
    exit('No autorizado.');
}

require_once __DIR__ . '/app/config/db.php';

header('Content-Type: text/html; charset=utf-8');

$aplicar = isset($_GET['aplicar']);
$pasos   = [];

/** Fases del Ciclo 1: numero => [nombre, inicio, fin, semanas] */
$FASES = [
    1 => ['La Guaira — fase 1', '2026-09-15', '2026-10-19', 5],
    2 => ['La Guaira — fase 2', '2026-10-20', '2026-11-16', 4],
    3 => ['La Guaira — fase 3', '2026-11-17', '2026-12-14', 4],
    4 => ['Barquisimeto',       '2027-01-18', '2027-02-15', 4],
    5 => ['Acarigua',           '2027-02-16', '2027-03-15', 4],
    6 => ['San Felipe',         '2027-03-16', '2027-04-12', 4],
    7 => ['Valencia',           '2027-04-13', '2027-05-10', 4],
];

/** Rubro => [fase 1, cada una de las otras]. Igual que migracion_012_presupuesto.sql */
$MONTOS = [
    'alimentacion'           => [1797.00, 1437.60],
    'transporte'             => [ 517.26,  413.79],
    'seminario'              => [ 486.18,  388.97],
    'materiales'             => [ 120.70,   96.55],
    'coordinacion'           => [ 120.70,   96.55],
    'administrativo'         => [ 120.70,   96.55],
    'personal_apoyo'         => [ 362.04,  289.66],
    'viaticos_facilitadores' => [ 362.04,  289.66],
];

try {
    $db = Database::getConnection();

    $columnas = $db->query("
        SELECT COLUMN_NAME FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos'
    ")->fetchAll(PDO::FETCH_COLUMN);
    $tablas = $db->query("
        SELECT TABLE_NAME FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME IN ('presupuesto_fases', 'presupuesto_montos')
    ")->fetchAll(PDO::FETCH_COLUMN);

    $hay_cobertura = in_array('cobertura', $columnas, true);
    $hay_fases     = in_array('presupuesto_fases', $tablas, true);
    $hay_montos    = in_array('presupuesto_montos', $tablas, true);

    if (!$aplicar) {
        $pasos[] = ['info', 'Columna <code>gastos.cobertura</code>: ' . ($hay_cobertura ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Tabla <code>presupuesto_fases</code>: ' . ($hay_fases ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Tabla <code>presupuesto_montos</code>: ' . ($hay_montos ? 'ya existe' : 'falta')];
        $pasos[] = ['info', 'Nada se ha tocado todavía. Agrega <code>&aplicar=1</code> a la dirección para ejecutar.'];
    } else {
        if (!$hay_cobertura) {
            $db->exec("
                ALTER TABLE gastos
                    ADD COLUMN cobertura ENUM('presupuesto','adicional') NOT NULL DEFAULT 'presupuesto'
                        COMMENT 'adicional = gasto que el presupuesto no contemplaba'
                        AFTER categoria,
                    ADD COLUMN cobertura_motivo VARCHAR(200) NULL
                        COMMENT 'Por qué se pagó algo que no estaba previsto'
                        AFTER cobertura,
                    ADD KEY idx_gastos_cobertura (cobertura)
            ");
            $pasos[] = ['ok', 'Columnas <code>cobertura</code> y <code>cobertura_motivo</code> creadas. Todos los gastos quedaron como presupuestados.'];
        } else {
            $pasos[] = ['salta', 'La columna <code>gastos.cobertura</code> ya existía, no se tocó.'];
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS presupuesto_fases (
                id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                ciclo        TINYINT UNSIGNED NOT NULL DEFAULT 1,
                numero       TINYINT UNSIGNED NOT NULL,
                nombre       VARCHAR(100)     NOT NULL,
                fecha_inicio DATE             NOT NULL,
                fecha_fin    DATE             NOT NULL,
                semanas      TINYINT UNSIGNED NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_fase (ciclo, numero),
                KEY idx_fase_fechas (fecha_inicio, fecha_fin)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS presupuesto_montos (
                fase_id   INT UNSIGNED  NOT NULL,
                rubro     VARCHAR(40)   NOT NULL COMMENT 'Clave de PresupuestoModel::RUBROS',
                monto_usd DECIMAL(10,2) NOT NULL,
                PRIMARY KEY (fase_id, rubro),
                CONSTRAINT fk_montos_fase FOREIGN KEY (fase_id)
                    REFERENCES presupuesto_fases (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // INSERT IGNORE: si la fase o el monto ya estaban, se quedan como estén
        $ins_fase = $db->prepare("
            INSERT IGNORE INTO presupuesto_fases (ciclo, numero, nombre, fecha_inicio, fecha_fin, semanas)
            VALUES (1, :n, :nombre, :i, :f, :s)
        ");
        $ins_monto = $db->prepare("
            INSERT IGNORE INTO presupuesto_montos (fase_id, rubro, monto_usd) VALUES (:fase, :rubro, :monto)
        ");
        $id_fase = $db->prepare("SELECT id FROM presupuesto_fases WHERE ciclo = 1 AND numero = :n");

        $nuevas = $montos = 0;
        foreach ($FASES as $n => [$nombre, $inicio, $fin, $semanas]) {
            $ins_fase->execute([':n' => $n, ':nombre' => $nombre, ':i' => $inicio, ':f' => $fin, ':s' => $semanas]);
            $nuevas += $ins_fase->rowCount();

            $id_fase->execute([':n' => $n]);
            $fase = (int) $id_fase->fetchColumn();

            $renglones = [];
            foreach ($MONTOS as $rubro => [$primera, $resto]) {
                $renglones[$rubro] = $n === 1 ? $primera : $resto;
            }
            $renglones['hospedaje']    = in_array($n, [4, 5, 6], true) ? 700.00 : 0.00;
            $renglones['contingencia'] = $n === 1 ? 189.76 : (in_array($n, [4, 5, 6], true) ? 193.80 : 151.80);

            foreach ($renglones as $rubro => $monto) {
                $ins_monto->execute([':fase' => $fase, ':rubro' => $rubro, ':monto' => $monto]);
                $montos += $ins_monto->rowCount();
            }
        }
        $pasos[] = ['ok', "Presupuesto cargado: $nuevas fases y $montos montos nuevos."];
    }

    $resumen = ($hay_fases && $hay_montos) || $aplicar
        ? $db->query("
            SELECT f.numero, f.nombre, f.fecha_inicio, f.fecha_fin, COALESCE(SUM(m.monto_usd), 0) AS total
            FROM presupuesto_fases f
            LEFT JOIN presupuesto_montos m ON m.fase_id = f.id
            WHERE f.ciclo = 1
            GROUP BY f.id ORDER BY f.numero
        ")->fetchAll()
        : [];

} catch (Throwable $e) {
    $pasos[] = ['error', 'Falló: ' . htmlspecialchars($e->getMessage())];
    $resumen = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migración 012 — presupuesto</title>
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
        td.num { text-align: right; }
        .aviso { margin-top: 2rem; padding: 1rem; background: #fef9c3; border-radius: 8px; font-size: .9rem; }
        code { background: #f1f5f9; padding: .1rem .3rem; border-radius: 4px; }
    </style>
</head>
<body>

<h1>Migración 012 — presupuesto contra lo real</h1>

<?php foreach ($pasos as [$tipo, $texto]): ?>
<div class="paso <?= $tipo ?>"><?= $texto ?></div>
<?php endforeach; ?>

<?php if (!empty($resumen)): ?>
<table>
    <thead><tr><th>Fase</th><th>Nombre</th><th>Desde</th><th>Hasta</th><th>Presupuesto USD</th></tr></thead>
    <tbody>
    <?php $total = 0; foreach ($resumen as $f): $total += $f['total']; ?>
        <tr>
            <td><?= (int) $f['numero'] ?></td>
            <td><?= htmlspecialchars($f['nombre']) ?></td>
            <td><?= $f['fecha_inicio'] ?></td>
            <td><?= $f['fecha_fin'] ?></td>
            <td class="num"><?= number_format($f['total'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
        <tr><th colspan="4">Total (debe dar 25,869.16)</th><th class="num"><?= number_format($total, 2) ?></th></tr>
    </tbody>
</table>
<?php endif; ?>

<div class="aviso">
    <strong>Al terminar, borra este archivo del servidor.</strong>
    Mientras esté subido, cualquiera con el enlace puede ejecutarlo.
</div>

</body>
</html>
