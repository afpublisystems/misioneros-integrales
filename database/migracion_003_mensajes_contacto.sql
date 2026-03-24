-- Migración 003: Tabla para mensajes del formulario de contacto público
-- Ejecutar: docker exec misioneros-integrales-db-1 mysql -uroot -psecret_password misioneros_integrales_db < database/migracion_003_mensajes_contacto.sql

CREATE TABLE IF NOT EXISTS mensajes_contacto (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(200) NOT NULL,
    email      VARCHAR(200) NOT NULL,
    telefono   VARCHAR(30)  NULL,
    asunto     VARCHAR(50)  NOT NULL,
    mensaje    TEXT         NOT NULL,
    leido      TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_leido (leido),
    INDEX idx_created (created_at)
);
