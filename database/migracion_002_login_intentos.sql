-- Migración 002: tabla para rate limiting de login
-- Aplicar con: docker exec misioneros-integrales-db-1 mysql -uroot -psecret_password misioneros_integrales_db < /docker-entrypoint-initdb.d/migracion_002_login_intentos.sql

CREATE TABLE IF NOT EXISTS login_intentos (
    ip              VARCHAR(45)  NOT NULL PRIMARY KEY,
    intentos        TINYINT UNSIGNED DEFAULT 0,
    bloqueado_hasta DATETIME     NULL,
    updated_at      TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
