-- ============================================================
-- Migración 009: Actividad de cada foto o video de la galería
--
-- Hasta ahora cada ítem solo tenía ciudad. Mientras el grupo está
-- meses en una misma sede, todo caía en el mismo montón. Con la
-- actividad (devocional, oficios, bienestar...) la galería se puede
-- filtrar por lo que se hace en el día a día.
--
-- Los valores válidos están en Controller::ACTIVIDADES_GALERIA.
-- Lo que ya estaba subido queda sin actividad y se ve en "Todas".
-- ============================================================

ALTER TABLE multimedia
    ADD COLUMN actividad VARCHAR(20) NULL AFTER tipo,
    ADD KEY idx_multimedia_actividad (actividad);
