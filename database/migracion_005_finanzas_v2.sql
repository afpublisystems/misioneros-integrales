-- ============================================================
-- Migración 005: Módulo de Finanzas v2
-- Misioneros Integrales — CNBV/DIME
--
-- Reemplaza el diseño de la 004 (que solo contemplaba cuotas de
-- estudiantes y gastos genéricos) por un libro completo de
-- ingresos y egresos con fondos etiquetados, préstamos, cuentas
-- y becas por participante.
--
-- Costo base del ciclo: USD 1.500 por participante.
-- La beca (50% para la 1ª cohorte, 100% en casos puntuales) se
-- registra por participante y define cuánto paga realmente.
-- ============================================================

SET NAMES utf8mb4;

-- ── 1. Beca y costo base por participante ───────────────────
ALTER TABLE aspirantes
    ADD COLUMN costo_base_usd DECIMAL(10,2) NOT NULL DEFAULT 1500.00
        COMMENT 'Costo real del ciclo antes de beca',
    ADD COLUMN beca_pct       DECIMAL(5,2)  NOT NULL DEFAULT 50.00
        COMMENT '50 = beca CNBV estándar, 100 = exonerado total',
    ADD COLUMN beca_notas     VARCHAR(255)  NULL
        COMMENT 'Quién aprobó la beca y por qué';

-- ── 2. Cuentas: dónde está físicamente el dinero ────────────
CREATE TABLE IF NOT EXISTS cuentas (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre     VARCHAR(100) NOT NULL,
    tipo       ENUM('zelle','pago_movil','transferencia','efectivo','otro') NOT NULL,
    moneda     ENUM('USD','VES') NOT NULL DEFAULT 'USD',
    titular    VARCHAR(150) NULL,
    detalle    VARCHAR(200) NULL COMMENT 'Banco, correo Zelle, teléfono',
    activa     TINYINT      NOT NULL DEFAULT 1,
    orden      TINYINT      NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_cuenta_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cuentas (nombre, tipo, moneda, orden) VALUES
    ('Zelle',            'zelle',         'USD', 1),
    ('Efectivo USD',     'efectivo',      'USD', 2),
    ('Pago Móvil',       'pago_movil',    'VES', 3),
    ('Transferencia Bs', 'transferencia', 'VES', 4),
    ('Efectivo Bs',      'efectivo',      'VES', 5);

-- ── 3. Fondos: para qué está destinado el dinero ────────────
-- Permite responder "de lo que entró para franelas, cuánto se gastó".
CREATE TABLE IF NOT EXISTS fondos (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NULL,
    tipo        ENUM('general','especifico') NOT NULL DEFAULT 'especifico'
                COMMENT 'general = caja común; especifico = dinero etiquetado',
    activo      TINYINT      NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_fondo_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO fondos (nombre, descripcion, tipo) VALUES
    ('Operativo general',  'Caja común del programa: alimentación, transporte, hospedaje', 'general'),
    ('Matrículas',         'Aportes de los participantes por su cuota del ciclo',          'general'),
    ('Aportes CNBV',       'Recursos girados por la CNBV para personal y facilitadores',   'especifico'),
    ('Franelas cohorte 1', 'Uniformes de los participantes',                               'especifico');

-- ── 4. Cuotas del participante ──────────────────────────────
CREATE TABLE IF NOT EXISTS cuotas_estudiantes (
    id                  INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    aspirante_id        INT UNSIGNED     NOT NULL,
    cuota_numero        TINYINT UNSIGNED NOT NULL,
    monto_esperado_usd  DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    monto_acumulado_usd DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    estatus             ENUM('pendiente','parcial','completada','exonerada')
                        NOT NULL DEFAULT 'pendiente',
    fecha_vencimiento   DATE             NULL,
    created_at          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_aspirante_cuota (aspirante_id, cuota_numero),
    CONSTRAINT fk_cuotas_aspirante FOREIGN KEY (aspirante_id)
        REFERENCES aspirantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Si la 004 ya se aplicó, el ENUM viejo no tiene 'exonerada'
ALTER TABLE cuotas_estudiantes
    MODIFY COLUMN estatus ENUM('pendiente','parcial','completada','exonerada')
    NOT NULL DEFAULT 'pendiente';

-- ── 5. Préstamos recibidos ──────────────────────────────────
-- Plata que entró pero hay que devolver. No es superávit.
CREATE TABLE IF NOT EXISTS prestamos (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    prestamista      VARCHAR(150)  NOT NULL,
    telefono         VARCHAR(30)   NULL,
    concepto         VARCHAR(200)  NOT NULL COMMENT 'Para qué se pidió',
    fondo_id         INT UNSIGNED  NULL,
    monto_usd        DECIMAL(10,2) NOT NULL,
    fecha_prestamo   DATE          NOT NULL,
    fecha_compromiso DATE          NULL COMMENT 'Cuándo se prometió devolver',
    estatus          ENUM('activo','pagado','condonado') NOT NULL DEFAULT 'activo',
    notas            TEXT          NULL,
    registrado_por   INT UNSIGNED  NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_prestamos_estatus (estatus)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Ingresos: todo lo que entra, venga de donde venga ─────
CREATE TABLE IF NOT EXISTS ingresos (
    id                 INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    fecha              DATE          NOT NULL,
    origen             ENUM('matricula','aporte_cnbv','donacion','prestamo','reintegro','otro') NOT NULL,
    aspirante_id       INT UNSIGNED  NULL COMMENT 'Solo si origen = matricula',
    prestamo_id        INT UNSIGNED  NULL COMMENT 'Solo si origen = prestamo',
    fondo_id           INT UNSIGNED  NULL,
    cuenta_id          INT UNSIGNED  NULL,
    aportante          VARCHAR(150)  NULL COMMENT 'Quién dio el dinero',
    concepto           VARCHAR(200)  NOT NULL,
    monto_usd          DECIMAL(10,2) NOT NULL COMMENT 'Siempre en USD; los Bs se convierten con la tasa',
    monto_ves          DECIMAL(14,2) NULL,
    tasa_cambio        DECIMAL(12,4) NULL,
    metodo_pago        ENUM('transferencia','zelle','pago_movil','efectivo','otro') NOT NULL DEFAULT 'efectivo',
    banco_origen       VARCHAR(100)  NULL,
    referencia         VARCHAR(100)  NULL,
    comprobante_ruta   VARCHAR(500)  NULL,
    estatus            ENUM('pendiente','confirmado','rechazado') NOT NULL DEFAULT 'confirmado',
    registrado_via     ENUM('admin','candidato') NOT NULL DEFAULT 'admin',
    registrado_por     INT UNSIGNED  NULL,
    confirmado_por     INT UNSIGNED  NULL,
    fecha_confirmacion DATETIME      NULL,
    notas              TEXT          NULL,
    notas_admin        TEXT          NULL,
    created_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ingresos_origen  (origen),
    KEY idx_ingresos_estatus (estatus),
    KEY idx_ingresos_fecha   (fecha),
    CONSTRAINT fk_ingresos_aspirante FOREIGN KEY (aspirante_id) REFERENCES aspirantes(id) ON DELETE SET NULL,
    CONSTRAINT fk_ingresos_prestamo  FOREIGN KEY (prestamo_id)  REFERENCES prestamos(id)  ON DELETE SET NULL,
    CONSTRAINT fk_ingresos_fondo     FOREIGN KEY (fondo_id)     REFERENCES fondos(id)     ON DELETE SET NULL,
    CONSTRAINT fk_ingresos_cuenta    FOREIGN KEY (cuenta_id)    REFERENCES cuentas(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 7. Gastos ───────────────────────────────────────────────
-- categoria es VARCHAR y no ENUM: los rubros del programa cambian
-- entre ciclos y no queremos un ALTER TABLE cada vez.
CREATE TABLE IF NOT EXISTS gastos (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    fecha_gasto      DATE          NOT NULL,
    concepto         VARCHAR(200)  NOT NULL,
    categoria        VARCHAR(40)   NOT NULL,
    fondo_id         INT UNSIGNED  NULL,
    cuenta_id        INT UNSIGNED  NULL,
    prestamo_id      INT UNSIGNED  NULL COMMENT 'Si es la devolución de un préstamo',
    beneficiario     VARCHAR(150)  NULL COMMENT 'A quién se le pagó',
    monto_usd        DECIMAL(10,2) NOT NULL,
    monto_ves        DECIMAL(14,2) NULL,
    tasa_cambio      DECIMAL(12,4) NULL,
    metodo_pago      ENUM('transferencia','zelle','pago_movil','efectivo','otro') NOT NULL DEFAULT 'efectivo',
    referencia       VARCHAR(100)  NULL,
    comprobante_ruta VARCHAR(500)  NULL,
    registrado_por   INT UNSIGNED  NOT NULL,
    notas            TEXT          NULL,
    created_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_gastos_fecha     (fecha_gasto),
    KEY idx_gastos_categoria (categoria),
    KEY idx_gastos_prestamo  (prestamo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTA DE APLICACIÓN
-- Si la migración 004 llegó a correr en algún entorno, esas
-- tablas quedaron vacías (el panel nunca funcionó). Antes de
-- correr esta migración ahí, elimínalas:
--   DROP TABLE IF EXISTS abonos;
--   DROP TABLE IF EXISTS gastos;
--   DROP TABLE IF EXISTS cuotas_estudiantes;
-- ============================================================
