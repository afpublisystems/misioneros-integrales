-- ============================================================
-- Migración 014: Prácticas misioneras en iglesias
-- Misioneros Integrales — CNBV/DIME
--
-- Los grupos reportan cada semana lo que hicieron en la iglesia
-- donde sirven: cultos, células, evangelismo, actividades. Llega
-- en números, no persona por persona, así que va en su propia
-- tabla y no en personas_alcanzadas (ahí cada registro es alguien
-- con nombre). Sumarlas contaría dos veces a quien esté en ambas.
--
-- Asistencia: `comunidad` es la gente de la iglesia o del sector;
-- `equipo` son misioneros, coordinadores y familia. Van aparte para
-- que el crecimiento de cada iglesia no salga inflado por nosotros.
-- adultos/jovenes/ninos son el desglose de `comunidad` cuando se
-- tomó; si viene, comunidad es su suma.
--
-- Al final se cargan los reportes de Carayaca hasta el 1/11/2026.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS practica_iglesias (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(120) NOT NULL,
    sector      VARCHAR(120) NULL,
    activa      TINYINT      NOT NULL DEFAULT 1,
    orden       TINYINT      NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS practica_actividades (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    iglesia_id      INT UNSIGNED NOT NULL,
    fecha           DATE         NOT NULL,
    tipo            ENUM('culto','celula','evangelismo','especial','apoyo') NOT NULL,
    descripcion     VARCHAR(255) NULL,
    adultos         SMALLINT UNSIGNED NULL,
    jovenes         SMALLINT UNSIGNED NULL,
    ninos           SMALLINT UNSIGNED NULL,
    comunidad       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    equipo          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    evangelizados   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    decisiones      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    discipulado     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    registrado_por  INT UNSIGNED NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_practica_fecha (fecha),
    KEY idx_practica_iglesia (iglesia_id),
    CONSTRAINT fk_practica_iglesia FOREIGN KEY (iglesia_id) REFERENCES practica_iglesias (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO practica_iglesias (id, nombre, sector, orden) VALUES
    (1, 'IB Casa de Dios',    'Aguarito', 1),
    (2, 'IB Gente de Dios',   'Tirima',   2),
    (3, 'IB Luz y Esperanza', 'Carayaca', 3)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- ── Reportes hasta el 1/11/2026 ──────────────────────────────
-- Gente de Dios mandó el evangelismo del 16 al 25/10 en un solo
-- número (acumulado 62/16/8 menos lo de la semana del 30/10).
-- En sus actividades de esas dos semanas no separó al equipo.

INSERT INTO practica_actividades
    (iglesia_id, fecha, tipo, descripcion, adultos, jovenes, ninos, comunidad, equipo, evangelizados, decisiones, discipulado)
VALUES
    (2, '2026-10-17', 'especial',    'Culto especial',                                              NULL, NULL, NULL, 23, 0,  0, 0, 0),
    (2, '2026-10-18', 'culto',       NULL,                                                          11,   3,    2,    16, 6,  0, 0, 0),
    (2, '2026-10-23', 'especial',    'Reunión sobre principios bautistas',                          NULL, NULL, NULL, 10, 0,  0, 0, 0),
    (2, '2026-10-24', 'especial',    'Actividad deportiva: futbolito y kickingball',                NULL, NULL, NULL, 87, 0,  0, 0, 0),
    (2, '2026-10-25', 'evangelismo', 'Evangelismo del 16 al 25 de octubre, sin desglose por semana', NULL, NULL, NULL, 0, 0, 52, 9, 1),
    (2, '2026-10-25', 'culto',       NULL,                                                          17,   2,    3,    22, 5,  0, 0, 0),

    (1, '2026-10-30', 'evangelismo', 'Evangelismo entre el viernes en la tarde y el sábado en la mañana', NULL, NULL, NULL, 0,  0,  7, 2, 0),
    (1, '2026-10-31', 'celula',      'Célula en una casa de la comunidad. 1.ª lección de Mi identidad en Cristo', NULL, NULL, NULL, 9, 6, 0, 0, 6),
    (1, '2026-11-01', 'culto',       NULL,                                                          6,    1,    0,    7,  4,  0, 0, 0),

    (2, '2026-10-31', 'especial',    'Reflexiones de esperanza con las damas: la historia del hijo pródigo', 8, 3, 0, 11, 0, 0, 0, 0),
    (2, '2026-10-31', 'evangelismo', NULL,                                                          NULL, NULL, NULL, 0,  0, 10, 7, 7),
    (2, '2026-11-01', 'culto',       NULL,                                                          15,   7,    2,    24, 6,  0, 0, 0),

    (3, '2026-10-30', 'apoyo',       'Vigilia regional de jóvenes de La Guaira en la IB Bethesda: enseñanza, oración, testimonios y cantos', NULL, NULL, NULL, 54, 0, 0, 0, 0),
    (3, '2026-10-31', 'celula',      'Culto en un hogar: oración, enseñanza y alabanza',             NULL, NULL, NULL, 15, 0,  0, 0, 0),
    (3, '2026-10-31', 'evangelismo', 'Evangelismo a 2 damas, que comenzaron con la 1.ª lección de Mi identidad en Cristo', NULL, NULL, NULL, 0, 0, 2, 0, 2),
    (3, '2026-11-01', 'culto',       NULL,                                                          14,   7,    3,    24, 7,  0, 0, 0);
