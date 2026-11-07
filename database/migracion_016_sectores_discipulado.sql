-- ============================================================
-- Migración 016: sectores por sede y avance del discipulado
-- Misioneros Integrales — CNBV/DIME
--
-- SECTORES
-- La sede es la parada del itinerario (La Guaira) y dentro de ella
-- el equipo trabaja en varios sectores a la vez (Carayaca, Aguarito,
-- Tirima). Por eso el sector va en cada persona y no en la sede, y
-- sale de un catálogo y no de un campo libre: escrito a mano, al
-- tiempo quedan "Carayaca" y "carayaca" contando por separado.
-- Los sectores de La Guaira salen de las iglesias de prácticas.
--
-- DISCIPULADO
-- Los niveles y sus lecciones viven en tablas, no en el código, para
-- que el 2do nivel se cargue sin tocar nada. `discipulado_avance`
-- guarda la fecha en que se dio cada lección; si la persona sale de
-- discipulado el avance queda, no se borra, porque es historia.
-- ============================================================

SET NAMES utf8mb4;

-- ── Sectores ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS sede_sectores (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    sede_id     INT UNSIGNED NOT NULL,
    nombre      VARCHAR(120) NOT NULL,
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    orden       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sector_sede (sede_id, nombre),
    CONSTRAINT fk_sector_sede FOREIGN KEY (sede_id) REFERENCES sedes (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE personas_alcanzadas
    ADD COLUMN sector_id INT UNSIGNED NULL AFTER sede_id,
    ADD KEY idx_personas_sector (sector_id),
    ADD CONSTRAINT fk_personas_sector
        FOREIGN KEY (sector_id) REFERENCES sede_sectores (id) ON DELETE SET NULL;

-- Sede 1 = La Guaira. Los sectores donde están las iglesias.
INSERT INTO sede_sectores (sede_id, nombre, orden) VALUES
    (1, 'Carayaca', 1),
    (1, 'Aguarito', 2),
    (1, 'Tirima',   3)
ON DUPLICATE KEY UPDATE orden = VALUES(orden);

-- ── Discipulado ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS discipulado_niveles (
    id      TINYINT UNSIGNED NOT NULL,
    nombre  VARCHAR(100) NOT NULL,
    orden   TINYINT UNSIGNED NOT NULL DEFAULT 0,
    activo  TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS discipulado_lecciones (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nivel_id  TINYINT UNSIGNED NOT NULL,
    numero    TINYINT UNSIGNED NOT NULL,
    titulo    VARCHAR(150) NOT NULL,
    activa    TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_leccion (nivel_id, numero),
    CONSTRAINT fk_leccion_nivel FOREIGN KEY (nivel_id) REFERENCES discipulado_niveles (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS discipulado_avance (
    persona_id  INT UNSIGNED NOT NULL,
    leccion_id  INT UNSIGNED NOT NULL,
    completada  DATE         NOT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (persona_id, leccion_id),
    KEY idx_avance_leccion (leccion_id),
    CONSTRAINT fk_avance_persona FOREIGN KEY (persona_id) REFERENCES personas_alcanzadas (id) ON DELETE CASCADE,
    CONSTRAINT fk_avance_leccion FOREIGN KEY (leccion_id) REFERENCES discipulado_lecciones (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO discipulado_niveles (id, nombre, orden) VALUES
    (1, '1er Nivel de Discipulado "Mi Identidad en Cristo"', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO discipulado_lecciones (nivel_id, numero, titulo) VALUES
    (1, 1, 'No soy yo, Cristo vive en mí'),
    (1, 2, 'Soy hijo de Dios'),
    (1, 3, 'Soy perdonado'),
    (1, 4, 'Soy heredero de la gloria de Dios'),
    (1, 5, 'Soy templo de Dios'),
    (1, 6, 'Soy escuchado y favorecido por Dios'),
    (1, 7, 'Soy sellado por el Espíritu Santo'),
    (1, 8, 'Soy miembro de la familia de Dios')
ON DUPLICATE KEY UPDATE titulo = VALUES(titulo);
