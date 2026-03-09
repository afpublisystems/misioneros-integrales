-- ============================================================
-- SCHEMA: misioneros_integrales_db
-- Programa de Formación de Misioneros Integrales - CNBV/DIME
-- Versión: 1.0 | Fecha: 2026-03-08
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- TABLA: sedes
-- Ciudades del itinerario con fechas y estado
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sedes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    estado      VARCHAR(100) NOT NULL,
    mes         VARCHAR(50)  NOT NULL COMMENT 'Ej: Julio, Agosto, Diciembre-Enero',
    orden       TINYINT UNSIGNED NOT NULL COMMENT 'Orden en el itinerario (1-7)',
    fecha_inicio DATE NULL,
    fecha_fin    DATE NULL,
    descripcion  TEXT NULL,
    activa       TINYINT NOT NULL DEFAULT 1,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos iniciales del itinerario
INSERT INTO sedes (nombre, estado, mes, orden, fecha_inicio, fecha_fin) VALUES
('Los Teques',   'Miranda',   'Julio',            1, '2026-07-01', '2026-07-31'),
('Maracay',      'Aragua',    'Agosto',            2, '2026-08-01', '2026-08-31'),
('San Felipe',   'Yaracuy',   'Septiembre',        3, '2026-09-01', '2026-09-30'),
('Valencia',     'Carabobo',  'Octubre',           4, '2026-10-01', '2026-10-31'),
('Acarigua',     'Portuguesa','Noviembre',         5, '2026-11-01', '2026-11-30'),
('Barquisimeto', 'Lara',      'Diciembre-Enero',   6, '2026-12-15', '2027-01-15'),
('Trujillo',     'Trujillo',  'Febrero',           7, '2027-02-01', '2027-02-28');

-- ------------------------------------------------------------
-- TABLA: usuarios
-- Autenticación de admins, evaluadores y candidatos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(100) NOT NULL,
    apellido     VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt',
    rol          ENUM('admin','evaluador','candidato') NOT NULL DEFAULT 'candidato',
    activo       TINYINT NOT NULL DEFAULT 1,
    ultimo_acceso DATETIME NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin por defecto (password: Admin2026* — cambiar en producción)
INSERT INTO usuarios (nombre, apellido, email, password, rol) VALUES
('José', 'Ramos', 'misionerosintegrales.cnbv@gmail.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ------------------------------------------------------------
-- TABLA: aspirantes
-- Registro completo del candidato al programa
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS aspirantes (
    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id          INT UNSIGNED NULL COMMENT 'FK a usuarios si ya creó cuenta',
    -- Datos personales
    nombres             VARCHAR(100) NOT NULL,
    apellidos           VARCHAR(100) NOT NULL,
    cedula              VARCHAR(20)  NOT NULL UNIQUE,
    fecha_nacimiento    DATE         NOT NULL,
    edad                TINYINT UNSIGNED NOT NULL,
    genero              ENUM('masculino','femenino','otro') NOT NULL,
    estado_civil        ENUM('soltero','casado','viudo','divorciado') NOT NULL,
    hijos               TINYINT UNSIGNED NOT NULL DEFAULT 0,
    -- Contacto
    telefono            VARCHAR(20) NOT NULL,
    email               VARCHAR(150) NOT NULL,
    ciudad_origen       VARCHAR(100) NOT NULL,
    estado_origen       VARCHAR(100) NOT NULL,
    direccion           TEXT NULL,
    -- Datos eclesiales
    iglesia             VARCHAR(150) NOT NULL,
    pastor              VARCHAR(150) NOT NULL,
    telefono_pastor     VARCHAR(20)  NOT NULL,
    anos_bautizado      TINYINT UNSIGNED NOT NULL COMMENT 'Debe ser >= 1',
    -- Datos académicos
    nivel_academico     ENUM('bachiller','tecnico','universitario','postgrado') NOT NULL,
    titulo_bachiller    TINYINT NOT NULL DEFAULT 0,
    -- Filtro de movilidad (pregunta clave del protocolo)
    compromiso_movilidad TINYINT NOT NULL DEFAULT 0 COMMENT '1=puede movilizarse, 0=tiene impedimento',
    detalle_impedimento  TEXT NULL,
    -- Control
    estatus             ENUM('borrador','enviada','en_revision','aprobada','rechazada','lista_espera') 
                        NOT NULL DEFAULT 'borrador',
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLA: flujo_proceso
-- Seguimiento de las 5 etapas del proceso de selección
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS flujo_proceso (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aspirante_id    INT UNSIGNED NOT NULL,
    etapa           ENUM(
                        'solicitud_formal',
                        'evaluacion_documental',
                        'test_vocacional',
                        'entrevista_personal',
                        'confirmacion_admision'
                    ) NOT NULL,
    estatus         ENUM('pendiente','en_proceso','aprobado','rechazado') 
                    NOT NULL DEFAULT 'pendiente',
    fecha_inicio    DATETIME NULL,
    fecha_cierre    DATETIME NULL,
    evaluador_id    INT UNSIGNED NULL COMMENT 'Usuario evaluador asignado',
    notas           TEXT NULL COMMENT 'Notas internas del evaluador',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aspirante_id)  REFERENCES aspirantes(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluador_id)  REFERENCES usuarios(id) ON DELETE SET NULL,
    UNIQUE KEY unique_aspirante_etapa (aspirante_id, etapa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLA: documentos
-- Archivos subidos por los aspirantes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS documentos (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aspirante_id    INT UNSIGNED NOT NULL,
    tipo            ENUM(
                        'carta_motivacion',
                        'titulo_bachiller',
                        'carta_pastoral',
                        'cedula_identidad',
                        'foto_personal',
                        'otro'
                    ) NOT NULL,
    nombre_archivo  VARCHAR(255) NOT NULL,
    ruta            VARCHAR(500) NOT NULL COMMENT 'Ruta relativa en servidor',
    mime_type       VARCHAR(100) NOT NULL,
    tamanio_kb      INT UNSIGNED NOT NULL,
    verificado      TINYINT NOT NULL DEFAULT 0,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (aspirante_id) REFERENCES aspirantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLA: test_vocacional
-- Respuestas de la Encuesta de Perfil Vocacional (60 preguntas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS test_vocacional (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aspirante_id    INT UNSIGNED NOT NULL UNIQUE,
    respuestas      JSON NOT NULL COMMENT 'JSON con todas las respuestas {pregunta_id: respuesta}',
    puntaje_total   DECIMAL(5,2) NULL COMMENT 'Puntaje calculado por el sistema',
    completado      TINYINT NOT NULL DEFAULT 0,
    fecha_inicio    DATETIME NULL,
    fecha_cierre    DATETIME NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aspirante_id) REFERENCES aspirantes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLA: multimedia
-- Galería de fotos y videos por sede
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS multimedia (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sede_id     INT UNSIGNED NOT NULL,
    titulo      VARCHAR(200) NOT NULL,
    descripcion TEXT NULL,
    tipo        ENUM('foto','video') NOT NULL DEFAULT 'foto',
    url         VARCHAR(500) NOT NULL COMMENT 'URL de foto o embed de YouTube/Vimeo',
    thumb_url   VARCHAR(500) NULL COMMENT 'Miniatura para galería',
    destacado   TINYINT NOT NULL DEFAULT 0,
    orden       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    activo      TINYINT NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sede_id) REFERENCES sedes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- TABLA: impacto_estadisticas
-- Contadores de impacto editables desde el panel admin
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS impacto_estadisticas (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clave       VARCHAR(100) NOT NULL UNIQUE COMMENT 'Identificador único del contador',
    etiqueta    VARCHAR(150) NOT NULL COMMENT 'Texto visible al público',
    valor       INT UNSIGNED NOT NULL DEFAULT 0,
    icono       VARCHAR(50)  NULL COMMENT 'Clase de ícono (ej: fa-church)',
    orden       TINYINT UNSIGNED NOT NULL DEFAULT 0,
    activo      TINYINT NOT NULL DEFAULT 1,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estadísticas iniciales del programa
INSERT INTO impacto_estadisticas (clave, etiqueta, valor, icono, orden) VALUES
('misioneros_capacitados', 'Misioneros Capacitados', 0,  'fa-user-graduate', 1),
('iglesias_plantadas',     'Iglesias Plantadas',      0,  'fa-church',        2),
('microempresas',          'Microempresas Misioneras', 0,  'fa-briefcase',     3),
('estados_alcanzados',     'Estados Alcanzados',       0,  'fa-map-marker-alt',4);

SET FOREIGN_KEY_CHECKS = 1;
