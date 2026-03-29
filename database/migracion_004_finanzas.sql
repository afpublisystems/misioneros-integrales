-- ============================================================
-- Migración 004: Módulo de Finanzas
-- Misioneros Integrales - CNBV/DIME
-- Cohorte 2026: $750 USD / 7 cuotas mensuales (~$107.14/mes)
-- ============================================================

-- ── 1. Cuotas por estudiante (7 registros al aprobar) ───────
CREATE TABLE IF NOT EXISTS cuotas_estudiantes (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    aspirante_id        INT UNSIGNED    NOT NULL,
    cuota_numero        TINYINT UNSIGNED NOT NULL COMMENT '1 a 7',
    monto_esperado_usd  DECIMAL(10,2)   NOT NULL DEFAULT 107.14,
    monto_acumulado_usd DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    estatus             ENUM('pendiente','parcial','completada') NOT NULL DEFAULT 'pendiente',
    fecha_vencimiento   DATE            NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_aspirante_cuota (aspirante_id, cuota_numero),
    CONSTRAINT fk_cuotas_aspirante FOREIGN KEY (aspirante_id)
        REFERENCES aspirantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Abonos / comprobantes enviados por el estudiante ──────
CREATE TABLE IF NOT EXISTS abonos (
    id                    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    cuota_id              INT UNSIGNED    NOT NULL,
    aspirante_id          INT UNSIGNED    NOT NULL COMMENT 'Desnorm. para queries rápidas',
    monto_declarado_usd   DECIMAL(10,2)   NOT NULL,
    monto_declarado_ves   DECIMAL(14,2)   NULL,
    tasa_cambio           DECIMAL(10,2)   NULL     COMMENT 'Tasa BCV al momento del pago',
    metodo_pago           ENUM('transferencia','zelle','pago_movil','efectivo') NOT NULL,
    banco_origen          VARCHAR(100)    NULL,
    referencia            VARCHAR(100)    NULL,
    comprobante_ruta      VARCHAR(500)    NULL,
    estatus               ENUM('pendiente','confirmado','rechazado') NOT NULL DEFAULT 'pendiente',
    fecha_pago_declarado  DATE            NOT NULL,
    fecha_confirmacion    DATETIME        NULL,
    confirmado_por        INT UNSIGNED    NULL     COMMENT 'FK usuarios (admin)',
    notas_admin           TEXT            NULL,
    created_at            TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_abonos_cuota     FOREIGN KEY (cuota_id)     REFERENCES cuotas_estudiantes(id) ON DELETE CASCADE,
    CONSTRAINT fk_abonos_aspirante FOREIGN KEY (aspirante_id) REFERENCES aspirantes(id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Gastos operativos del programa (solo admin) ───────────
CREATE TABLE IF NOT EXISTS gastos (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    concepto         VARCHAR(200)    NOT NULL,
    categoria        ENUM('logistica','comunicacion','materiales','operativo','otro') NOT NULL,
    monto_usd        DECIMAL(10,2)   NULL,
    monto_ves        DECIMAL(14,2)   NULL,
    metodo_pago      ENUM('transferencia','zelle','pago_movil','efectivo','otro') NOT NULL,
    referencia       VARCHAR(100)    NULL,
    comprobante_ruta VARCHAR(500)    NULL,
    fecha_gasto      DATE            NOT NULL,
    registrado_por   INT UNSIGNED    NOT NULL COMMENT 'FK usuarios',
    notas            TEXT            NULL,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
