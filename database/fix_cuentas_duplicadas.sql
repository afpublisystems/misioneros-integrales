-- ============================================================
-- Arreglo: cuentas duplicadas
--
-- La migración 005 no tenía índice único en cuentas.nombre, así
-- que correr los seeds dos veces duplica las cinco cuentas y el
-- selector "Entró a / Salió de" muestra cada una repetida.
--
-- Este script reapunta los movimientos a la cuenta que se queda,
-- borra las repetidas y agrega el índice para que no vuelva a
-- pasar. Es seguro correrlo aunque no haya duplicados.
-- ============================================================

SET NAMES utf8mb4;

-- ── 1. Cómo está antes ──────────────────────────────────────
SELECT nombre, COUNT(*) AS repeticiones
FROM cuentas GROUP BY nombre HAVING COUNT(*) > 1;

-- ── 2. Reapuntar los movimientos a la cuenta que sobrevive ──
-- (la de menor id de cada nombre)
UPDATE ingresos i
JOIN cuentas c ON c.id = i.cuenta_id
JOIN (SELECT nombre, MIN(id) AS id_ok FROM cuentas GROUP BY nombre) k
     ON k.nombre = c.nombre
SET i.cuenta_id = k.id_ok
WHERE i.cuenta_id <> k.id_ok;

UPDATE gastos g
JOIN cuentas c ON c.id = g.cuenta_id
JOIN (SELECT nombre, MIN(id) AS id_ok FROM cuentas GROUP BY nombre) k
     ON k.nombre = c.nombre
SET g.cuenta_id = k.id_ok
WHERE g.cuenta_id <> k.id_ok;

-- ── 3. Borrar las repetidas ─────────────────────────────────
DELETE c FROM cuentas c
JOIN (SELECT nombre, MIN(id) AS id_ok FROM cuentas GROUP BY nombre) k
     ON k.nombre = c.nombre
WHERE c.id <> k.id_ok;

-- ── 4. Que no vuelva a pasar ────────────────────────────────
-- MySQL no acepta ADD KEY IF NOT EXISTS, así que se verifica antes
SET @ya_existe := (
    SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE()
      AND table_name   = 'cuentas'
      AND index_name   = 'uq_cuenta_nombre'
);
SET @sql := IF(@ya_existe = 0,
    'ALTER TABLE cuentas ADD UNIQUE KEY uq_cuenta_nombre (nombre)',
    'DO 0');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ── 5. Cómo quedó ───────────────────────────────────────────
SELECT id, nombre, tipo, moneda FROM cuentas ORDER BY orden, id;
