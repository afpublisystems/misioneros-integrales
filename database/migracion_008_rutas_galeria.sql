-- ============================================================
-- Migración 008: Rutas de las fotos de la galería
--
-- Las fotos se guardan en public/uploads/galeria/, pero se
-- registraban como /uploads/galeria/..., que da 404: esa carpeta
-- no existe en la raíz. Esto les agrega el /public que faltaba.
--
-- Se puede correr más de una vez: solo toca las que siguen mal.
-- ============================================================

UPDATE multimedia
SET url       = CONCAT('/public', url),
    thumb_url = IF(thumb_url LIKE '/uploads/galeria/%', CONCAT('/public', thumb_url), thumb_url)
WHERE tipo = 'foto'
  AND url LIKE '/uploads/galeria/%';
