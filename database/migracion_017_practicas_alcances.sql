-- ============================================================
-- Migración 017: alcances y discipulado en las prácticas
-- Misioneros Integrales — CNBV/DIME
--
-- TIPOS DE ACTIVIDAD
-- Antes todo lo que no fuera culto, célula, evangelismo o apoyo caía
-- en 'especial', así que un taller, una vigilia y una reunión de damas
-- quedaban indistinguibles y no había forma de sumarlas. Ahora cada
-- alcance tiene su tipo y el informe de cierre puede decir cuántas
-- vigilias se hicieron y con cuánta asistencia.
--
-- A QUIÉN VA DIRIGIDA
-- Las reuniones por organización van con un solo tipo y la columna
-- `dirigida_a`, no con un tipo por cada grupo: una misma actividad
-- puede ser de damas y jóvenes a la vez, y el informe igual desglosa.
--
-- DISCIPULADO
-- `discipulado` ya decía cuántas personas; faltaba cuál lección se
-- dio. Con `leccion_id` el informe arma el avance por nivel sin que
-- nadie lo escriba a mano. Más adelante esto se enlaza con las
-- personas de `personas_alcanzadas`, que ya llevan su avance propio.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE practica_actividades
    MODIFY COLUMN tipo ENUM(
        'culto',
        'celula',
        'evangelismo',
        'taller',
        'deportiva',
        'vigilia',
        'organizacion',
        'apoyo',
        'especial'
    ) NOT NULL;

ALTER TABLE practica_actividades
    ADD COLUMN dirigida_a SET('damas','caballeros','jovenes','ninos','matrimonios') NULL
        COMMENT 'Solo en las reuniones por organización' AFTER descripcion,
    ADD COLUMN leccion_id INT UNSIGNED NULL
        COMMENT 'Lección de discipulado que se dio en la actividad' AFTER discipulado,
    ADD KEY idx_practica_leccion (leccion_id),
    ADD CONSTRAINT fk_practica_leccion
        FOREIGN KEY (leccion_id) REFERENCES discipulado_lecciones (id) ON DELETE SET NULL;
