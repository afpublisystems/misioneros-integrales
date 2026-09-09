-- ============================================================
-- Migración 006: Anulación de movimientos
--
-- En vez de borrar, un movimiento se anula: se queda en la base
-- pero deja de contar en los totales, y guarda quién lo anuló,
-- cuándo y por qué. Así el historial no miente.
--
-- Cada tabla tiene una sola columna que decide si el movimiento
-- cuenta o no, para no repartir esa decisión en varios campos.
-- ============================================================

SET NAMES utf8mb4;

-- ── 1. Ingresos ─────────────────────────────────────────────
-- 'rechazado' es cuando el comprobante del participante no era
-- válido; 'anulado' es cuando el registro se cargó por error.
-- Son cosas distintas y conviene poder distinguirlas.
ALTER TABLE ingresos
    MODIFY COLUMN estatus ENUM('pendiente','confirmado','rechazado','anulado')
    NOT NULL DEFAULT 'confirmado';

ALTER TABLE ingresos
    ADD COLUMN anulado_en       DATETIME     NULL AFTER notas_admin,
    ADD COLUMN anulado_por      INT UNSIGNED NULL AFTER anulado_en,
    ADD COLUMN motivo_anulacion VARCHAR(255) NULL AFTER anulado_por;

-- ── 2. Gastos ───────────────────────────────────────────────
ALTER TABLE gastos
    ADD COLUMN estatus          ENUM('activo','anulado') NOT NULL DEFAULT 'activo' AFTER notas,
    ADD COLUMN anulado_en       DATETIME     NULL AFTER estatus,
    ADD COLUMN anulado_por      INT UNSIGNED NULL AFTER anulado_en,
    ADD COLUMN motivo_anulacion VARCHAR(255) NULL AFTER anulado_por;

ALTER TABLE gastos ADD KEY idx_gastos_estatus (estatus);

-- ── 3. Préstamos ────────────────────────────────────────────
ALTER TABLE prestamos
    MODIFY COLUMN estatus ENUM('activo','pagado','condonado','anulado')
    NOT NULL DEFAULT 'activo';

ALTER TABLE prestamos
    ADD COLUMN anulado_en       DATETIME     NULL AFTER notas,
    ADD COLUMN anulado_por      INT UNSIGNED NULL AFTER anulado_en,
    ADD COLUMN motivo_anulacion VARCHAR(255) NULL AFTER anulado_por;

-- ── Comprobación ────────────────────────────────────────────
SELECT 'ingresos'  AS tabla, COUNT(*) AS anulados FROM ingresos  WHERE estatus = 'anulado'
UNION ALL
SELECT 'gastos',    COUNT(*) FROM gastos    WHERE estatus = 'anulado'
UNION ALL
SELECT 'prestamos', COUNT(*) FROM prestamos WHERE estatus = 'anulado';
