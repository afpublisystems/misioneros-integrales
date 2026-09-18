-- ============================================================
-- Migración 007: Registro de evangelismo
--
-- Cada persona alcanzada en campo se registra una sola vez y se
-- le marca si tomó decisión de fe y si está en discipulado. Así
-- los totales salen de la misma lista sin contar a nadie dos veces.
--
-- La carga la hace el admin o cualquiera que tenga el PIN de
-- acceso público (/evangelismo). El PIN vive en `configuracion`,
-- guardado con password_hash, nunca en texto plano.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS personas_alcanzadas (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombres         VARCHAR(100) NOT NULL,
    apellidos       VARCHAR(100) NOT NULL,
    edad            TINYINT UNSIGNED NULL,
    telefono        VARCHAR(30)  NULL,
    direccion       VARCHAR(255) NULL,
    sede_id         INT UNSIGNED NULL,
    fecha_contacto  DATE         NOT NULL,
    decision_fe     TINYINT(1)   NOT NULL DEFAULT 0,
    discipulado     TINYINT(1)   NOT NULL DEFAULT 0,
    responsable     VARCHAR(150) NULL COMMENT 'Quién hizo el contacto',
    notas           TEXT         NULL,
    registrado_via  ENUM('admin','pin') NOT NULL DEFAULT 'admin',
    registrado_por  INT UNSIGNED NULL COMMENT 'usuarios.id cuando carga el admin',
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_personas_sede (sede_id),
    KEY idx_personas_fecha (fecha_contacto),
    CONSTRAINT fk_personas_sede FOREIGN KEY (sede_id) REFERENCES sedes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS configuracion (
    clave       VARCHAR(60)  NOT NULL,
    valor       VARCHAR(255) NULL,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
