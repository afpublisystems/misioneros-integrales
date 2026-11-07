-- ============================================================
-- Migración 015: visita solicitada y foto en personas alcanzadas
-- Misioneros Integrales — CNBV/DIME
--
-- `desea_visita` es independiente de la etapa: lo puede pedir tanto
-- quien escuchó el evangelio como un contacto espiritual, haya o no
-- tomado decisión. Por eso va en su propia columna y no dentro del
-- encadenado decision_fe → discipulado.
--
-- "Sin decisión" no necesita columna: es el evangelizado con
-- decision_fe = 0, que es lo que ya se guardaba. El cambio está en
-- el formulario, donde ahora se elige explícitamente en vez de
-- quedar como la casilla que nadie marcó.
--
-- `foto` guarda solo el nombre del archivo. Vive en
-- uploads/evangelismo/, que está cerrado por .htaccess: la imagen
-- se sirve por /admin/evangelismo/foto, que pide sesión. Son fotos
-- de personas identificadas con teléfono y dirección, no van por URL
-- pública como los comprobantes.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE personas_alcanzadas
    ADD COLUMN desea_visita TINYINT(1) NOT NULL DEFAULT 0
        COMMENT 'Pidió que lo visiten. Independiente de la etapa' AFTER discipulado,
    ADD COLUMN foto VARCHAR(255) NULL
        COMMENT 'Nombre del archivo en uploads/evangelismo/' AFTER desea_visita;

CREATE INDEX idx_personas_visita ON personas_alcanzadas (desea_visita);
