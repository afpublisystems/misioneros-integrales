-- ============================================================
-- Migración 012: Presupuesto del Ciclo 1 contra lo gastado
-- Misioneros Integrales — CNBV/DIME
--
-- El plan era que de viernes a domingo las iglesias anfitrionas
-- cubrieran la comida y los traslados internos. En la práctica
-- de Carayaca no fue así y el programa lo pagó todo. Para el
-- informe hace falta separar esa plata de lo que sí estaba
-- previsto, por eso:
--
--   gastos.cobertura         'presupuesto' = estaba en el plan
--                            'adicional'   = no estaba (lo iban a
--                                            cubrir las iglesias, etc.)
--   gastos.cobertura_motivo  por qué se tuvo que pagar
--
-- Y el presupuesto aprobado, fase por fase, para compararlo:
--   presupuesto_fases   las 7 fases del ciclo con sus fechas
--   presupuesto_montos  lo presupuestado por rubro en cada fase
--
-- Los montos son la hoja «Resumen Mensual» de
-- Presupuesto-Ciclo1-MisionerosIntegrales-CORREGIDO.xlsx
-- (12 participantes). El redondeo a centavos va en la fase 1
-- para que cada rubro cierre exacto con el total del ciclo.
--
-- A qué fase pertenece un gasto lo dice su fecha. Lo que cae
-- fuera (antes del 15/09 o en la pausa decembrina) sale aparte.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE gastos
    ADD COLUMN cobertura ENUM('presupuesto','adicional') NOT NULL DEFAULT 'presupuesto'
        COMMENT 'adicional = gasto que el presupuesto no contemplaba'
        AFTER categoria,
    ADD COLUMN cobertura_motivo VARCHAR(200) NULL
        COMMENT 'Por qué se pagó algo que no estaba previsto'
        AFTER cobertura,
    ADD KEY idx_gastos_cobertura (cobertura);

CREATE TABLE IF NOT EXISTS presupuesto_fases (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    ciclo        TINYINT UNSIGNED NOT NULL DEFAULT 1,
    numero       TINYINT UNSIGNED NOT NULL,
    nombre       VARCHAR(100)     NOT NULL,
    fecha_inicio DATE             NOT NULL,
    fecha_fin    DATE             NOT NULL,
    semanas      TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_fase (ciclo, numero),
    KEY idx_fase_fechas (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS presupuesto_montos (
    fase_id   INT UNSIGNED  NOT NULL,
    rubro     VARCHAR(40)   NOT NULL COMMENT 'Clave de PresupuestoModel::RUBROS',
    monto_usd DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (fase_id, rubro),
    CONSTRAINT fk_montos_fase FOREIGN KEY (fase_id)
        REFERENCES presupuesto_fases (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO presupuesto_fases (ciclo, numero, nombre, fecha_inicio, fecha_fin, semanas) VALUES
    (1, 1, 'La Guaira — fase 1', '2026-09-15', '2026-10-19', 5),
    (1, 2, 'La Guaira — fase 2', '2026-10-20', '2026-11-16', 4),
    (1, 3, 'La Guaira — fase 3', '2026-11-17', '2026-12-14', 4),
    (1, 4, 'Barquisimeto',       '2027-01-18', '2027-02-15', 4),
    (1, 5, 'Acarigua',           '2027-02-16', '2027-03-15', 4),
    (1, 6, 'San Felipe',         '2027-03-16', '2027-04-12', 4),
    (1, 7, 'Valencia',           '2027-04-13', '2027-05-10', 4);

-- Fase 1 (5 semanas) y las otras seis (4 semanas cada una).
-- Hospedaje solo se alquila en las fases 4, 5 y 6.
INSERT INTO presupuesto_montos (fase_id, rubro, monto_usd)
SELECT f.id, m.rubro,
       CASE WHEN f.numero = 1 THEN m.fase1 ELSE m.resto END
FROM presupuesto_fases f
JOIN (
    SELECT 'alimentacion' AS rubro, 1797.00 AS fase1, 1437.60 AS resto UNION ALL
    SELECT 'transporte',              517.26,  413.79 UNION ALL
    SELECT 'seminario',               486.18,  388.97 UNION ALL
    SELECT 'materiales',              120.70,   96.55 UNION ALL
    SELECT 'coordinacion',            120.70,   96.55 UNION ALL
    SELECT 'administrativo',          120.70,   96.55 UNION ALL
    SELECT 'personal_apoyo',          362.04,  289.66 UNION ALL
    SELECT 'viaticos_facilitadores',  362.04,  289.66
) m
WHERE f.ciclo = 1;

INSERT INTO presupuesto_montos (fase_id, rubro, monto_usd)
SELECT id, 'hospedaje', IF(numero IN (4, 5, 6), 700.00, 0.00)
FROM presupuesto_fases WHERE ciclo = 1;

-- La contingencia es 6% del Bloque A de cada fase
INSERT INTO presupuesto_montos (fase_id, rubro, monto_usd)
SELECT id,
       'contingencia',
       CASE WHEN numero = 1 THEN 189.76
            WHEN numero IN (4, 5, 6) THEN 193.80
            ELSE 151.80 END
FROM presupuesto_fases WHERE ciclo = 1;

-- Comprobación: debe dar 25.869,16
-- SELECT SUM(monto_usd) FROM presupuesto_montos;
