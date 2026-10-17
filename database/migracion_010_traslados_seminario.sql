-- ============================================================
-- Migración 010: Traslados de fondos y cuenta del Seminario
-- Misioneros Integrales — CNBV/DIME
--
-- Dos cosas que hasta ahora se llevaban por fuera:
--
-- 1. Las matrículas en bolívares caen en la cuenta de la CNBV.
--    Para sacar ese dinero hay que mandarle a la tesorería de la
--    Convención el listado de pagos (fecha, monto Bs, banco y
--    referencia) para que lo verifiquen y lo transfieran. Cada
--    envío queda aquí como una solicitud con su estado, y cada
--    ingreso sabe en qué solicitud se fue — así no se pide dos
--    veces el mismo pago.
--
-- 2. Del costo del ciclo, una parte se le paga al Seminario
--    Teológico Bautista de Venezuela por cada participante:
--    inscripción, carnet, ternanet y las mensualidades. Eso se
--    lleva como cargos por participante; cuando se le paga al
--    STBV, los cargos quedan amarrados al gasto correspondiente.
-- ============================================================

SET NAMES utf8mb4;

-- ── 1. Solicitudes de traslado a tesorería ──────────────────
CREATE TABLE IF NOT EXISTS solicitudes_traslado (
    id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo             VARCHAR(20)  NOT NULL COMMENT 'ST-2026-001',
    fecha              DATE         NOT NULL,
    concepto           VARCHAR(200) NOT NULL COMMENT 'Ej: Matrículas de septiembre',
    destino            VARCHAR(200) NULL COMMENT 'Cuenta a la que se pide el traslado',
    estatus            ENUM('enviada','verificada','trasladada','anulada')
                       NOT NULL DEFAULT 'enviada',
    creada_por         INT UNSIGNED NULL,
    fecha_verificacion DATETIME     NULL,
    fecha_traslado     DATETIME     NULL,
    notas              TEXT         NULL,
    created_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_solicitud_codigo (codigo),
    KEY idx_solicitud_estatus (estatus),
    KEY idx_solicitud_fecha (fecha),
    CONSTRAINT fk_solicitud_usuario FOREIGN KEY (creada_por)
        REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Un ingreso entra en una sola solicitud
ALTER TABLE ingresos
    ADD COLUMN solicitud_id INT UNSIGNED NULL
        COMMENT 'Solicitud de traslado en la que se reportó este pago'
        AFTER prestamo_id,
    ADD KEY fk_ingresos_solicitud (solicitud_id),
    ADD CONSTRAINT fk_ingresos_solicitud FOREIGN KEY (solicitud_id)
        REFERENCES solicitudes_traslado (id) ON DELETE SET NULL;

-- ── 2. Cargos del Seminario por participante ────────────────
CREATE TABLE IF NOT EXISTS seminario_cargos (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    aspirante_id INT UNSIGNED     NOT NULL,
    trimestre    TINYINT UNSIGNED NOT NULL DEFAULT 1,
    concepto     ENUM('inscripcion','carnet','ternanet','mensualidad','otro')
                 NOT NULL DEFAULT 'mensualidad',
    descripcion  VARCHAR(120)     NOT NULL,
    monto_usd    DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    estatus      ENUM('pendiente','pagado','exonerado') NOT NULL DEFAULT 'pendiente',
    gasto_id     INT UNSIGNED     NULL COMMENT 'Gasto con el que se le pagó al STBV',
    fecha_pago   DATE             NULL,
    orden        TINYINT UNSIGNED NOT NULL DEFAULT 0,
    notas        VARCHAR(255)     NULL,
    created_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_seminario_aspirante (aspirante_id),
    KEY idx_seminario_estatus (estatus),
    KEY fk_seminario_gasto (gasto_id),
    CONSTRAINT fk_seminario_aspirante FOREIGN KEY (aspirante_id)
        REFERENCES aspirantes (id) ON DELETE CASCADE,
    CONSTRAINT fk_seminario_gasto FOREIGN KEY (gasto_id)
        REFERENCES gastos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
