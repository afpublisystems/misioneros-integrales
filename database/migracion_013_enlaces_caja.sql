-- ============================================================
-- Migración 013: Enlaces para cargar gastos sin contraseña
-- Misioneros Integrales — CNBV/DIME
--
-- Cada coordinador o responsable recibe un enlace propio
-- (/gastos?k=...) para cargar gastos desde el teléfono. No ve
-- nada más del sistema: solo carga y revisa lo suyo de la semana.
--
-- Del enlace se guarda solo el hash SHA-256: si se pierde, se
-- regenera desde el panel y el anterior deja de servir.
--
-- gastos.enlace_id dice por cuál enlace entró el gasto; esos
-- gastos llevan como registrado_por al admin que creó el enlace.
-- ============================================================

SET NAMES utf8mb4;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE gastos
    ADD COLUMN enlace_id INT UNSIGNED NULL
        COMMENT 'Enlace de caja por el que se cargó, si fue así'
        AFTER registrado_por,
    ADD KEY idx_gastos_enlace (enlace_id);
