-- ─────────────────────────────────────────────────────────────
-- Migración 004 — Itinerario Ciclo 1 reprogramado
-- Motivo: el inicio se pospuso por la contingencia de los sismos
--         en La Guaira. Nuevo recorrido de 5 sedes (Sep 2026 – May 2027).
--         Los Teques, Maracay y Trujillo pasan al 2do ciclo de esta cohorte.
--
-- IMPORTANTE: ejecutar en producción solo si la galería aún no tiene
-- fotos/videos asociados a las sedes viejas (multimedia.sede_id).
-- Este UPDATE reescribe el significado de los id 1–5 y desactiva 6–7.
-- ─────────────────────────────────────────────────────────────

UPDATE sedes SET nombre='La Guaira',    estado='La Guaira',  mes='Septiembre-Diciembre', orden=1, fecha_inicio='2026-09-15', fecha_fin='2026-12-14', activa=1 WHERE id=1;
UPDATE sedes SET nombre='Barquisimeto', estado='Lara',       mes='Enero-Febrero',        orden=2, fecha_inicio='2027-01-19', fecha_fin='2027-02-15', activa=1 WHERE id=2;
UPDATE sedes SET nombre='Acarigua',     estado='Portuguesa', mes='Febrero-Marzo',        orden=3, fecha_inicio='2027-02-16', fecha_fin='2027-03-15', activa=1 WHERE id=3;
UPDATE sedes SET nombre='San Felipe',   estado='Yaracuy',    mes='Marzo-Abril',          orden=4, fecha_inicio='2027-03-16', fecha_fin='2027-04-12', activa=1 WHERE id=4;
UPDATE sedes SET nombre='Valencia',     estado='Carabobo',   mes='Abril-Mayo',           orden=5, fecha_inicio='2027-04-13', fecha_fin='2027-05-10', activa=1 WHERE id=5;

-- Sedes que este grupo hará en su 2do ciclo: fuera del Ciclo 1
UPDATE sedes SET activa=0 WHERE id IN (6, 7);
