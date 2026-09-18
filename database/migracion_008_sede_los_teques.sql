-- ============================================================
-- Migración 008: Los Teques como lugar de evangelismo
--
-- Mientras se espera entrar a La Guaira, el grupo se aloja en
-- Los Teques y evangeliza allí. No es una sede del itinerario del
-- Ciclo 1, así que entra inactiva: no aparece en la galería
-- pública, pero sí se puede elegir al registrar personas.
--
-- No hace nada si ya existe una sede llamada Los Teques.
-- ============================================================

SET NAMES utf8mb4;

INSERT INTO sedes (nombre, estado, mes, orden, activa)
SELECT 'Los Teques', 'Miranda', 'Septiembre 2026', 8, 0
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM sedes WHERE nombre = 'Los Teques');
