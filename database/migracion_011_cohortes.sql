-- ============================================================
-- Migración 011: Cohortes y retrato público del participante
-- Misioneros Integrales — CNBV/DIME
--
-- Se abre la convocatoria de la 2da cohorte (inicio septiembre
-- 2027) y los nuevos postulantes no pueden mezclarse con los 14
-- que están cursando el Ciclo 1. De ahí la columna `cohorte`:
-- lo que ya existe es cohorte 1 y todo registro nuevo entra
-- como cohorte 2 por el DEFAULT.
--
-- `foto_publica` es el retrato que sale en la página de la
-- cohorte: los mismos 14, con el mismo encuadre y tamaño, en
-- public/assets/cohorte1/. No reemplaza a la foto del
-- expediente, que sigue en `documentos`.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE aspirantes
    ADD COLUMN cohorte TINYINT UNSIGNED NOT NULL DEFAULT 2
        COMMENT '1 = cohorte en curso; 2 = convocatoria septiembre 2027'
        AFTER estatus,
    ADD COLUMN foto_publica VARCHAR(120) NULL
        COMMENT 'Archivo en public/assets/cohorte<N>/'
        AFTER cohorte,
    ADD KEY idx_aspirantes_cohorte (cohorte);

-- Todo lo que ya estaba registrado es la 1era cohorte
UPDATE aspirantes SET cohorte = 1;

-- Retratos de los 14, por nombre de pila. Si alguno no coincide
-- se le asigna desde el panel, en la ficha del participante.
UPDATE aspirantes SET foto_publica = 'abraham.jpg'  WHERE cohorte = 1 AND nombres LIKE 'Abraham%';
UPDATE aspirantes SET foto_publica = 'adrian.jpg'   WHERE cohorte = 1 AND nombres LIKE 'Adri_n%';
UPDATE aspirantes SET foto_publica = 'anais.jpg'    WHERE cohorte = 1 AND (nombres LIKE '%Ana_s%' OR nombres LIKE 'Marianyeli%');
UPDATE aspirantes SET foto_publica = 'dairenis.jpg' WHERE cohorte = 1 AND nombres LIKE 'Dairenis%';
UPDATE aspirantes SET foto_publica = 'deilimar.jpg' WHERE cohorte = 1 AND nombres LIKE 'Deilimar%';
UPDATE aspirantes SET foto_publica = 'diosmely.jpg' WHERE cohorte = 1 AND nombres LIKE 'Diosmel%';
UPDATE aspirantes SET foto_publica = 'eric.jpg'     WHERE cohorte = 1 AND nombres LIKE 'Eric%';
UPDATE aspirantes SET foto_publica = 'jose.jpg'     WHERE cohorte = 1 AND nombres LIKE 'Jos_ %' AND apellidos LIKE 'Hern_ndez%';
UPDATE aspirantes SET foto_publica = 'josh.jpg'     WHERE cohorte = 1 AND nombres LIKE 'Josh%';
UPDATE aspirantes SET foto_publica = 'josue.jpg'    WHERE cohorte = 1 AND nombres LIKE 'Josu_%';
UPDATE aspirantes SET foto_publica = 'juan.jpg'     WHERE cohorte = 1 AND nombres LIKE 'Juan%';
UPDATE aspirantes SET foto_publica = 'meiver.jpg'   WHERE cohorte = 1 AND nombres LIKE 'Meiver%';
UPDATE aspirantes SET foto_publica = 'natasha.jpg'  WHERE cohorte = 1 AND nombres LIKE 'Natasha%';
UPDATE aspirantes SET foto_publica = 'samuel.jpg'   WHERE cohorte = 1 AND nombres LIKE 'Samuel%';

-- Revisar que los 14 quedaron con su retrato:
-- SELECT cedula, nombres, apellidos, foto_publica FROM aspirantes WHERE cohorte = 1 ORDER BY nombres;
