# PROJECT_CONTEXT.md
# Proyecto: Misioneros Integrales — Sistema Web CNBV/DIME
# Última actualización: 21/06/2026 (v6)
# Roles: Gemini = Arquitecto | Claude = Ejecutor (código PHP/MVC/CSS)

---

## INFORMACIÓN DEL PROGRAMA

- **Nombre:** Programa de Formación Misioneros Integrales
- **Organización:** CNBV / DIME (Convención Nacional Bautista de Venezuela)
- **Lema:** "De la formación a la misión: Transforma, Multiplica e Impacta" — Mateo 13:23
- **Dominio objetivo:** misionerosintegrales.com
- **Contacto principal:** José Ramos 0424-5886540 / Yohanna de Ramos 0424-5905392
- **Email:** misionerosintegrales.cnbv@gmail.com
- **Capacidad:** 40 participantes por ciclo
- **Perfil aspirante:** 18-40 años, bautizado +1 año, sin cargas que limiten movilidad
- **Certificación:** Licenciatura en Teología — Mención Misiones (al completar 3 ciclos)

---

## ITINERARIO CICLO 1 — VIGENTE (reprogramado) — Sep 2026 a May 2027 (8 meses)

> ⚠️ **Reprogramado por la contingencia.** El arranque estaba previsto para julio 2026, pero
> se pospuso por la situación tras el doble sismo en Venezuela. El Ciclo 1 de esta primera cohorte
> arranca con 3 meses en La Guaira para acompañar a las iglesias de la zona (fortalecimiento y
> apertura de obras nuevas). Los Teques, Maracay y Trujillo salen del Ciclo 1 de este grupo y se
> ejecutan en su **2do ciclo**. Para cohortes de próximos años esas 3 ciudades vuelven al Ciclo 1.

| # | Fechas | Ciudad | Estado | Nota |
|---|--------|--------|--------|------|
| 1 | 15-09 → 14-12-2026 | La Guaira | La Guaira | 3 meses · apoyo a iglesias (15-09/12-10, 14-10/16-11, 17-11/14-12) |
| 2 | 19-01 → 15-02-2027 | Barquisimeto | Lara | — |
| 3 | 16-02 → 15-03-2027 | Acarigua | Portuguesa | — |
| 4 | 16-03 → 12-04-2027 | San Felipe | Yaracuy | — |
| 5 | 13-04 → 10-05-2027 | Valencia | Carabobo | Cierre de ciclo |

### Itinerario ORIGINAL (previo a la contingencia — archivado)

| # | Mes | Ciudad | Estado | Sede |
|---|-----|--------|--------|------|
| 1 | Julio | Los Teques | Miranda | STBV |
| 2 | Agosto | Maracay | Aragua | — |
| 3 | Septiembre | San Felipe | Yaracuy | — |
| 4 | Octubre | Valencia | Carabobo | CBCC |
| 5 | Noviembre | Acarigua | Portuguesa | — |
| 6 | Dic–Ene | Barquisimeto | Lara | 15 días c/u |
| 7 | Febrero | Trujillo | Trujillo | — |

---

## STACK TECNOLÓGICO

| Componente | Tecnología |
|------------|------------|
| Entorno local | Docker Desktop (Windows) |
| Backend | PHP 8.2-apache |
| Base de datos | MySQL 8.0 |
| Arquitectura | MVC (PHP puro, sin framework) |
| Frontend | HTML + CSS (Montserrat) + Font Awesome 6.5 |
| Puerto web | localhost:8080 |
| Puerto MySQL | 3308 externo / 3306 interno |
| phpMyAdmin | localhost:8081 |
| Ruta local | C:\xampp\htdocs\misioneros-integrales\ |
| Carpeta fuente | ./src/ (mapeada a /var/www/html) |
| Repo GitHub | https://github.com/afpublisystems/misioneros-integrales |

---

## PALETA DE COLORES OFICIAL

| Variable CSS | Hex | Uso |
|---|---|---|
| `--verde` | `#167a5e` | Color principal |
| `--verde-dark` | `#0f5a45` | Footer, hover, sidebar admin |
| `--verde-light` | `#e8f5f0` | Fondos suaves |
| `--dorado` | `#cea237` | Títulos destacados, acentos |
| `--naranja` | `#F7941D` | CTA principal |
| `--azul` | `#003d6b` | Texto oscuro, CNBV |

---

## LOGOS — `src/public/assets/logos/`

| Archivo | Descripción | Uso |
|---------|-------------|-----|
| `logo-mi-completo-t.png` | Logo completo colores originales, fondo transparente | Hero |
| `logo-mi-completo-w.png` | Logo completo todo blanco | Carrusel aliados, footer fondos oscuros |
| `logo-mi-t.png` | Solo isotipo, colores originales | Navbar fondo claro |
| `logo-mi-w.png` | Solo isotipo, todo blanco | Navbar fondo verde, sidebar admin |
| `logo-cnbv-t.png` | CNBV sin fondo blanco | Login, footer |
| `logo-dime-t.png` | DIME sin fondo blanco | Login, footer |

> Generados con Pillow desde `propuesta_logo.png` (1996×1001px, fondo negro removido)

---

## ESTRUCTURA DE CARPETAS

```
C:\xampp\htdocs\misioneros-integrales\
├── docker-compose.yml
├── Dockerfile (PHP 8.2 + pdo_mysql + mod_rewrite)
├── database/
│   ├── schema.sql                          ✅ 8 tablas + datos iniciales
│   └── migracion_001_nota_evaluador.sql    ✅ YA APLICADA
└── src/
    ├── .htaccess                           ✅ Router MVC
    ├── index.php                           ✅ Router principal con tabla de rutas
    ├── PROJECT_CONTEXT.md                  ✅ Este archivo
    ├── app/
    │   ├── config/db.php                   ✅ PDO Singleton (Database::getConnection())
    │   ├── controllers/
    │   │   ├── Controller.php              ✅ Clase base (render, redirigir, requireAuth)
    │   │   ├── PublicoController.php       ✅ 6 rutas públicas
    │   │   ├── AuthController.php          ✅ Login/Registro/Logout
    │   │   ├── CandidatoController.php     ✅ Dashboard/Perfil/Docs/Test + auto-flujo
    │   │   └── AdminController.php         ✅ Dashboard/Candidatos/Perfil/Estadísticas/Flujo/Test
    │   ├── models/
    │   │   ├── Model.php                   ✅ CRUD genérico
    │   │   ├── UsuarioModel.php            ✅ Auth bcrypt + cambiarPassword()
    │   │   ├── AspiranteModel.php          ✅ Perfil candidato
    │   │   └── DocumentoModel.php          ✅ Archivos subidos
    │   └── views/
    │       ├── layouts/
    │       │   ├── main.php                ✅ Layout público (navbar + footer)
    │       │   └── admin.php               ✅ Layout admin (sin navbar público)
    │       ├── partials/
    │       │   ├── navbar.php              ✅ Navbar público (cambia según rol)
    │       │   ├── footer.php              ✅ Footer con link "Acceso Administradores"
    │       │   └── admin_sidebar.php       ✅ Sidebar del panel admin
    │       ├── publico/
    │       │   └── home.php                ✅ Home v5 + carrusel aliados
    │       ├── auth/
    │       │   ├── login.php               ✅
    │       │   └── registro.php            ✅
    │       ├── candidato/
    │       │   ├── dashboard.php           ✅ Progress bar 5 etapas
    │       │   ├── perfil.php              ✅ 4 tabs
    │       │   ├── documentos.php          ✅ drag&drop
    │       │   └── test.php               ✅ Wizard 60 preguntas / 10 partes
    │       ├── admin/
    │       │   ├── dashboard.php           ✅ KPIs + últimas postulaciones + por estado
    │       │   ├── candidatos.php          ✅ Tabla + filtros + modal cambio estatus
    │       │   ├── ver_candidato.php       ✅ Detalle + flujo editable + docs + test + botón ficha
    │       │   ├── ver_test.php            ✅ 60 respuestas test vocacional admin
    │       │   ├── ficha_candidato.php     ✅ v6 — ficha imprimible/PDF de un postulante (sin layout)
    │       │   ├── listado_candidatos.php  ✅ v6 — listado imprimible/PDF de todos (sin layout)
    │       │   ├── perfil.php              ✅ Cambiar datos personales y contraseña
    │       │   └── estadisticas.php        ✅ Vista editable contadores públicos
    │       └── errors/
    │           ├── 404.php                 ✅
    │           └── en_construccion.php     ✅
    └── public/
        ├── css/app.css                     ✅ Estilos globales + candidato + admin
        ├── js/app.js                       ✅
        ├── assets/logos/                   ✅ 6 logos procesados
        └── uploads/documentos/             ✅ (crear manualmente si no existe)
```

---

## BASE DE DATOS — 8 tablas

| Tabla | Propósito | Estado |
|-------|-----------|--------|
| `sedes` | Ciudades del itinerario | ✅ Con datos |
| `usuarios` | Auth y roles (admin/evaluador/candidato) | ✅ Con datos |
| `aspirantes` | Registro completo candidatos + `nota_evaluador` | ✅ Migrada |
| `flujo_proceso` | Estados 5 etapas por aspirante (editables desde admin) | ✅ |
| `documentos` | Archivos subidos | ✅ |
| `test_vocacional` | Respuestas encuesta 60 preguntas (JSON) | ✅ |
| `multimedia` | Galería por sede | ✅ tabla creada, vista admin pendiente |
| `mensajes_contacto` | Mensajes del formulario público de contacto | ✅ Migración 003 aplicada |
| `impacto_estadisticas` | Contadores públicos editables | ✅ Con datos |

### Schema flujo_proceso (importante)
```sql
CREATE TABLE IF NOT EXISTS flujo_proceso (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aspirante_id    INT UNSIGNED NOT NULL,
    etapa           ENUM('solicitud_formal','evaluacion_documental','test_vocacional',
                         'entrevista_personal','confirmacion_admision') NOT NULL,
    estatus         ENUM('pendiente','en_proceso','aprobado','rechazado') DEFAULT 'pendiente',
    fecha_inicio    DATETIME NULL,
    fecha_cierre    DATETIME NULL,
    evaluador_id    INT UNSIGNED NULL,
    notas           TEXT NULL,
    UNIQUE KEY unique_aspirante_etapa (aspirante_id, etapa)
);
```

### Schema test_vocacional (importante)
```sql
CREATE TABLE IF NOT EXISTS test_vocacional (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aspirante_id    INT UNSIGNED NOT NULL UNIQUE,
    respuestas      JSON NULL,         -- 60 respuestas codificadas
    puntaje_total   DECIMAL(5,2) NULL, -- reservado para futuro scoring
    completado      TINYINT(1) DEFAULT 0,
    fecha_inicio    DATETIME NULL,
    fecha_cierre    DATETIME NULL
);
```

### Schema aspirantes (columnas clave — diferente a nombre obvio)
```
anos_bautizado, nivel_academico, titulo_bachiller (TINYINT),
compromiso_movilidad (TINYINT), detalle_impedimento
```
> ⚠️ NO existen: `anos_en_iglesia`, `nivel_educativo`, `ocupacion`, `disponibilidad_movilidad`

---

## BASE DE DATOS — Tabla colaboradores (nueva)

```sql
CREATE TABLE colaboradores (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(200) NOT NULL,
    organizacion VARCHAR(200) NULL,
    email        VARCHAR(200) NOT NULL,
    tipo         ENUM('economico','especie','servicios','voluntariado','otro') DEFAULT 'otro',
    mensaje      TEXT NULL,
    aprobado     TINYINT(1) DEFAULT 0,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## RUTAS IMPLEMENTADAS (index.php)

```
GET:
  /                       → PublicoController::home
  /programa               → en construcción
  /requisitos             → en construcción
  /galeria                → en construcción
  /impacto                → en construcción
  /contacto               → en construcción
  /login                  → AuthController::loginForm
  /registro               → AuthController::registroForm
  /logout                 → AuthController::logout
  /candidato/dashboard    → CandidatoController::dashboard
  /candidato/perfil       → CandidatoController::perfil
  /candidato/documentos   → CandidatoController::documentos
  /candidato/test         → CandidatoController::test
  /admin                  → AdminController::dashboard
  /admin/candidatos       → AdminController::candidatos
    ?ver=ID               → AdminController::verCandidato()  (delegado internamente)
    ?ver=ID&test=1        → AdminController::verTest()       (delegado internamente)
    ?ver=ID&ficha=1       → AdminController::fichaCandidato()    (v6, ficha imprimible)
    ?listado=1            → AdminController::listadoCandidatos() (v6, listado imprimible)
    ?exportar=1           → AdminController::exportarCSV()
  /admin/estadisticas     → AdminController::estadisticas
  /admin/galeria          → AdminController::galeria  ✅
    ?sede=ID              → muestra ítems de la sede
  /admin/perfil           → AdminController::perfil

POST:
  /login                  → AuthController::login
  /registro               → AuthController::registro
  /candidato/perfil       → CandidatoController::guardarPerfil
  /candidato/documentos   → CandidatoController::subirDocumento
  /candidato/test         → CandidatoController::guardarTest (+ auto-upsert flujo)
  /candidato/postular     → CandidatoController::enviarPostulacion  ✅ NUEVO
  /admin/candidatos       → AdminController::actualizarEstatus
    accion=flujo          → AdminController::actualizarFlujo() (delegado internamente)
    accion=verificar_doc  → AdminController::verificarDocumento()  ✅ NUEVO
    ?exportar=1           → AdminController::exportarCSV()  ✅ NUEVO (GET)
  /admin/estadisticas     → AdminController::actualizarEstadisticas
  /admin/perfil           → AdminController::actualizarPerfil
  /contacto               → PublicoController::enviarContacto  ✅ NUEVO
  /colaborar              → PublicoController::registrarColaborador
```

---

## PROCESO DE SELECCIÓN — 5 etapas (flujo_proceso)

| Clave BD | Label visible | Automático |
|----------|---------------|------------|
| `solicitud_formal` | Solicitud Formal | ✅ Se marca aprobado cuando candidato envía test |
| `evaluacion_documental` | Evaluación Documental | Manual por admin |
| `test_vocacional` | Test Vocacional | ✅ Se marca en_proceso cuando candidato envía test |
| `entrevista_personal` | Entrevista Personal | Manual por admin |
| `confirmacion_admision` | Confirmación de Admisión | Manual por admin |

**Estatus posibles por etapa:** `pendiente` → `en_proceso` → `aprobado` / `rechazado`

---

## USUARIOS EN BD (estado actual)

| id | Nombre | Email | Rol | Contraseña |
|----|--------|-------|-----|------------|
| 1 | José Ramos | misionerosintegrales.cnbv@gmail.com | admin | `password` (CAMBIAR) |
| 2 | Deilimar Pereira | deilipereira05@gmail.com | candidato | su clave |
| 3 | Admin CNBV | admin@misionerosintegrales.com | admin | incompleto |

> ⚠️ Cambiar contraseña del usuario 1 usando genhash.php
> 📌 **Producción (17/06/2026):** el único usuario con rol `admin` es `jfer22@gmail.com`. La tabla de arriba refleja el estado local/histórico, no producción.

---

## COMPORTAMIENTO DEL NAVBAR (según sesión)

| Estado | Botones mostrados |
|--------|------------------|
| Sin sesión | "Iniciar Sesión" + "Postularme" |
| `candidato` | "Mi Postulación" + "Salir" |
| `admin` o `evaluador` | "Admin" (botón azul) + "Salir" |

## ACCESO AL PANEL ADMIN

- URL: `localhost:8080/login` (mismo login para todos los roles)
- El sistema redirige automáticamente según rol:
  - `candidato` → `/candidato/dashboard`
  - `admin/evaluador` → `/admin`
- Link discreto en footer: "🔒 Acceso Administradores" → `/login`

**Reset de clave olvidada (admin):**
1. Subir `reset-admin-tmp.php` a la raíz del sitio por FTP
2. Listar admins: `?token=AF-reset-2026`
3. Fijar nueva clave: `?token=AF-reset-2026&email=EMAIL&nueva=CLAVE` (mínimo 8 caracteres)
4. **Borrar el archivo del hosting** — es puerta de entrada si se deja
- El login usa `password_verify` contra `usuarios.password` (hash bcrypt), por eso el reset genera el hash con `password_hash()`

---

## PANEL ADMIN — Funcionalidades implementadas

### Dashboard (`/admin`)
- 6 KPIs: Total, Enviadas, En revisión, Aprobados, Rechazados, Meta (40)
- Tabla "Últimas 5 Postulaciones" con avatar, iglesia, estado, estatus
- Gráfico de barras "Distribución por Estado"
- Sidebar con: Dashboard, Candidatos, Estadísticas, Galería, Mi Perfil, Ver sitio público, Cerrar sesión

### Candidatos (`/admin/candidatos`)
- Filtros tabs por estatus con contadores
- Barra de búsqueda (nombre, cédula, iglesia)
- Tabla con: avatar, nombre, email, cédula, iglesia, estado, edad, movilidad, estatus, fecha
- Modal para cambio rápido de estatus + nota del evaluador
- Badges de colores por estatus
- Botón "Exportar CSV" — descarga candidatos filtrados por estatus activo (17 columnas, BOM UTF-8, separador `;` para Excel en español)

### Ver Candidato (`/admin/candidatos?ver=ID`)
- Columna izquierda: datos personales, contacto, eclesiales, académico/movilidad
- Columna derecha:
  - **Flujo del Proceso** (5 etapas con botón "editar" ✏️ por cada una)
    - Modal para cambiar estatus (pendiente/en_proceso/aprobado/rechazado) + nota
    - INSERT ... ON DUPLICATE KEY UPDATE (upsert)
    - Fecha inicio automática al salir de pendiente; fecha cierre al aprobar/rechazar
  - **Test Vocacional** — badge de estado + fechas + botón "Ver respuestas"
  - **Documentos** — lista con tamaño, tipo, fecha, verificado + botón "Verificar/Verificado" por documento
    - POST `accion=verificar_doc` + `doc_id` → toggle campo `verificado` en tabla `documentos`
    - **Subir/reemplazar documento como admin** (v5): formulario `enctype="multipart/form-data"` con `accion=subir_doc`, `aspirante_id` y `_redirect` → `AdminController::subirDocumentoAdmin()`. Si el aspirante ya tiene ese tipo de documento, lo reemplaza (usa `DocumentoModel::porTipo()`)
  - **Meta** — email cuenta, último acceso, fechas registro/actualización, nota evaluador
- Modal cambio estatus general del aspirante (tabla `aspirantes.estatus`)

### Ver Test Vocacional (`/admin/candidatos?ver=ID&test=1`)
- Las 60 respuestas del candidato organizadas en 10 secciones colapsadas visualmente
- Cada tipo de respuesta con render específico:
  - **Radio** → etiqueta legible del valor
  - **Checkbox** → tags verdes con opciones seleccionadas
  - **Texto / textarea** → bloque cursiva con borde izquierdo verde
  - **Escala 1–10** → badge circular con color gradiente (rojo→amarillo→verde)
  - **Matriz Q22** → tabla de 9 ministerios × 4 niveles con checkmarks
  - **Q48 expectativas** → lista numerada
  - **Q56 declaración** → bloque especial + contador de palabras (mínimo 100)
- Badge estado (completado/en progreso) + fechas en header

### Mi Perfil (`/admin/perfil`)
- Card 1: Cambiar nombre, apellido, email
- Card 2: Cambiar contraseña (actual + nueva + confirmar)
- Validaciones: email único, contraseña mínimo 8 caracteres, hash bcrypt
- Actualiza la sesión inmediatamente

### Estadísticas (`/admin/estadisticas`)
- Editar contadores públicos de impacto (200+ misioneros, etc.)
- Solo rol `admin` puede editar (evaluadores tienen acceso de solo lectura)

---

## MÓDULO CANDIDATO — Funcionalidades implementadas

### Dashboard (`/candidato/dashboard`)
- Progress bar con 5 etapas del proceso de selección
- Estado actual del aspirante por etapa (desde `flujo_proceso` en `calcularProgreso()`)
- Aviso de plazo ("Plazo: 30 jun. 2026") en el header
- Card "Próximo paso contextual" — 10 estados posibles según flujo + estatus del aspirante
- Card "Checklist de postulación" — visible solo si estatus=`borrador`; lista 6 campos perfil + 4 docs requeridos
- Botón "Enviar postulación" → POST `/candidato/postular` (solo si todos los requisitos se cumplen)
- `CandidatoController::enviarPostulacion()` — valida requisitos, cambia estatus a `enviada`, registra etapa 1 en `flujo_proceso`
- `CandidatoController::validarRequisitos()` — verifica 6 campos perfil + 4 docs (cedula, titulo_bachiller, carta_pastoral, foto_reciente)
- `CandidatoController::proximoPaso()` — 10 estados contextuales, devuelve [texto, clase CSS]
- `calcularProgreso()` — actualizado para usar datos reales de `flujo_proceso` (antes devolvía todo `pendiente`)

### Perfil (`/candidato/perfil`)
- 4 tabs: Datos Personales, Eclesiales, Académico, Movilidad
- Guarda en tabla `aspirantes` via `AspiranteModel::guardarPerfil()`

### Documentos (`/candidato/documentos`)
- Drag & drop para subir archivos (PDF, JPG, PNG — máx 5 MB)
- Tipos: cedula, titulo_bachiller, carta_pastoral, foto_reciente, otros
- Nombre seguro: `{aspirante_id}_{tipo}_{timestamp}.{ext}`
- Guardado en `public/uploads/documentos/` y en tabla `documentos`

### Test Vocacional (`/candidato/test`)
- Wizard de 10 partes / 60 preguntas (ENCUESTA DE PERFIL VOCACIONAL Y APTITUDES MISIONERAS)
- Respuestas guardadas como JSON en `test_vocacional.respuestas`
- "Guardar progreso" → guarda sin completar (puede continuar después)
- "Enviar Test" → `completado=1` + auto-upsert en `flujo_proceso`:
  - `solicitud_formal` → aprobado
  - `test_vocacional` → en_proceso
- El test no se puede reeditar una vez enviado

---

## TEST VOCACIONAL — Estructura de las 10 partes

| Parte | Título | Preguntas | Tipos de campo |
|-------|--------|-----------|----------------|
| I | Llamado y Vocación Misionera | Q1–Q6 | radio, textarea, checkbox (max 3), escala 1-10 |
| II | Formación Espiritual | Q7–Q13 | radio, checkbox, condicional, text |
| III | Vida en Comunidad y Relaciones | Q14–Q20 | radio, escala 1-10, textarea, condicional |
| IV | Preparación Práctica y Ministerial | Q21–Q26 | radio, matriz 9×4, condicional, checkbox |
| V | Adaptabilidad e Interculturalidad | Q27–Q32 | radio, escala 1-10 |
| VI | Sostenibilidad y Emprendimiento | Q33–Q38 | checkbox, radio |
| VII | Salud Emocional y Resiliencia | Q39–Q44 | radio, checkbox, escala 1-10, textarea |
| VIII | Compromiso y Expectativas | Q45–Q50 | radio, text ×3, checkbox |
| IX | Preguntas Situacionales | Q51–Q55 | textarea ×5 (escenarios) |
| X | Compromiso Personal | Q56–Q60 | textarea (min 100 palabras), text, textarea ×3 |

### Claves de respuestas en JSON (selección)
- `q1` a `q60` — claves principales
- `q6[]`, `q8[]`, `q26[]`, `q33[]`, `q34[]`, `q42[]`, `q49[]` — arrays (checkboxes)
- `q22[ministerio]` — array asociativo (matriz): keys: evangelismo, discipulado, ensenanza, predicacion, alabanza, ninos, jovenes, servicio, liderazgo; values: ninguna/basica/intermedia/avanzada
- Campos adicionales: `q2_otro`, `q6_otro`, `q8_otras`, `q10_quien`, `q11_cuantos`, `q15_tiempo`, `q24_desc`, `q26_otro`, `q34_otra`, `q38_otra`, `q48_1/2/3`, `q50_otro`

---

## ESTADO DE FASES

| Fase | Descripción | Estado |
|------|-------------|--------|
| FASE 1 | MVC base + Schema + Layout + Home v5 + Carrusel aliados | ✅ Completa |
| FASE 2 | Autenticación (login, registro, sesiones, roles) | ✅ Completa |
| FASE 3 | Módulo Candidato (dashboard, perfil 4 tabs, documentos drag&drop) | ✅ Completa |
| FASE 4 | Panel Admin (dashboard KPIs, gestión candidatos, mi perfil, estadísticas) | ✅ Completa |
| FASE 5 | Páginas públicas (El Programa, Requisitos, Galería, Impacto, Contacto) | ✅ Completa |
| FASE 6 | Test Vocacional (60 preguntas / 10 partes + vista admin completa) | ✅ Completa |
| FASE 7 | Gestión de flujo del proceso (admin edita etapas) + auto-upsert al enviar test | ✅ Completa |
| FASE 8 | Vista estadísticas admin (editar contadores públicos) | ✅ Completa |
| FASE 9 | Galería admin (fotos/videos por sede, drag&drop, thumbnail YouTube auto) | ✅ Completa |
| FASE 10 | Sección pública "Colaboradores" (home + tabla BD + controller) | ✅ Completa |
| FASE 11 | Refactoring y seguridad (flash helper, safeRedirect, MIME check, índices BD) | ✅ Completa |
| FASE 12 | Humanización de textos (todas las páginas públicas + dashboard candidato) | ✅ Completa |
| FASE 13 | Flujo postulación candidato (checklist + enviar + validar requisitos + flujo_proceso) | ✅ Completa |
| FASE 14 | Mejoras UX admin (verificar docs, exportar CSV, etapas selección horizontal, countdown home, WhatsApp flotante) | ✅ Completa |

---

## PENDIENTES INMEDIATOS

1. **Cambiar contraseña** del usuario admin id=1 (actualmente `password`) — email: jfer22@gmail.com — usar `src/encrypt.php`
2. **Eliminar** `src/encrypt.php` antes de subir a producción (riesgo de seguridad) — `src/test_db.php` ya eliminado

---

## REGLAS DE ORO DEL PROYECTO

1. PDO obligatorio (`Database::getConnection()`) — sin mysqli directo
2. Videos → YouTube/Vimeo embed únicamente (sin subida directa)
3. MVC estricto: lógica en Modelos, presentación en Vistas
4. Nomenclatura en español para tablas y campos BD
5. Pregunta clave de movilidad en formulario de registro
6. Archivos individuales cuando son 1-2, ZIP solo si son muchos
7. Layout `admin.php` para vistas admin — tercer param en `render(..., ..., 'admin')`
8. `require APP_PATH . '/views/...'` — no usar `__DIR__` en vistas
9. `display_errors` activado en `layouts/admin.php` — quitar en producción
10. INSERT ... ON DUPLICATE KEY UPDATE para upsert de `flujo_proceso` (UNIQUE por aspirante+etapa)
11. Usar `$this->flash('tipo', 'msg')` en lugar de `$_SESSION['flash'] = [...]` directo
12. Usar `$this->safeRedirect($url, $fallback)` para todo redirect con input del usuario
13. Validar MIME real con `finfo_file()` en uploads — extensión sola NO es suficiente
14. Nombres de archivo únicos: `bin2hex(random_bytes(8))` — NO usar `time() + random_int`
15. Textos y copys: aplicar principios del humanizador (`~/.claude/skills/humanizer/SKILL.md`) — sin rule-of-three, sin adjetivos inflados, sin em dash excesivo, lenguaje directo y concreto

---

## CONTROLLER BASE — Helpers disponibles (Controller.php)

| Método | Firma | Descripción |
|--------|-------|-------------|
| `render()` | `render(vista, datos, layout='main')` | Renderiza vista en layout |
| `redirigir()` | `redirigir(url)` | Redirect + exit |
| `requireAuth()` | `requireAuth(rol=null)` | Verifica sesión activa + rol único |
| `requireAnyRole()` | `requireAnyRole([roles])` | Verifica sesión activa + cualquiera de los roles |
| `flash()` | `flash(tipo, msg)` | Escribe `$_SESSION['flash']` (usar SIEMPRE este) |
| `safeRedirect()` | `safeRedirect(url, fallback)` | Valida que URL sea interna (anti open-redirect) |
| `post()` | `post(campo, default='')` | POST saneado con htmlspecialchars |
| `get()` | `get(campo, default='')` | GET saneado con htmlspecialchars |
| `json()` | `json(datos, codigo=200)` | Respuesta JSON + exit |

## SECCIÓN COLABORADORES (home pública)

- **Ancla:** `#colabora` en home.php
- **4 tipos de colaboración:** Apoyo Económico, Donación en Especie, Servicios Profesionales, Voluntariado
- **Formulario POST** → `/colaborar` → `PublicoController::registrarColaborador()`
- **Tabla BD:** `colaboradores` (nombre, organizacion, email, tipo ENUM, mensaje, aprobado, created_at)
- **Flash session key:** `$_SESSION['flash_colabora']` (separada del flash global del admin)
- **Vista admin `/admin/colaboradores`:** implementada — tabla filtrable + KPIs + aprobar/eliminar + modal

## GALERÍA ADMIN — Notas técnicas

- **Ruta:** GET/POST `/admin/galeria`
- **Sede seleccionada:** `?sede=ID` — los datos de sede se obtienen de `$sedes` (array ya cargado) sin query extra
- **Tipos:** `foto` (upload a `/public/uploads/galeria/`) o `video` (URL YouTube/Vimeo)
- **Thumbnail YouTube auto:** `https://img.youtube.com/vi/{VIDEO_ID}/mqdefault.jpg`
- **Nombre de archivo:** `gal_{sede_id}_{bin2hex(random_bytes(8))}.{ext}` — collision-proof
- **Validación upload:** MIME real via `finfo_file()` + extensión + tamaño ≤ 5MB
- **Acciones:** subir, eliminar (borra archivo físico si foto), toggle_activo, toggle_destacado

## NOTAS TÉCNICAS IMPORTANTES

- **✅ Servidor producción PHP 8.4**: Webempresa corre PHP 8.4. Soporta `match()`, union types, named arguments, `str_contains()`, etc. Sin restricciones de versión.
- **`Database::getConnection()`** — nombre correcto del método Singleton (NO `getInstance()`)
- **Layout admin**: Las vistas admin usan `render('admin/vista', [...], 'admin')` como tercer parámetro
- **Migración `nota_evaluador`**: YA aplicada en BD (no ejecutar de nuevo → error #1060 duplicado)
- **CSS admin**: Todo en `app.css` al final, sección "PANEL ADMIN"
- **Sesión**: `$_SESSION['usuario_nombre']`, `$_SESSION['usuario_email']`, `$_SESSION['usuario_rol']`, `$_SESSION['usuario_id']`
- **Delegación en candidatos()**: Si `?ver=ID` → `verCandidato()`; si `?ver=ID&test=1` → `verTest()`
- **Delegación en actualizarEstatus()**: Si `accion=flujo` POST → `actualizarFlujo()`
- **flujo_proceso upsert**: Usar siempre INSERT ... ON DUPLICATE KEY UPDATE (no UPDATE sólo)
- **Test JSON keys**: Las respuestas de checkboxes son arrays (ej: `$respuestas['q6']` es array, no string)
- **Docker exec MySQL**: Para ejecutar SQL directo: `docker exec misioneros-integrales-db-1 mysql -uroot -psecret_password misioneros_integrales_db -e "SQL..."`

---

## BUGS CORREGIDOS (historial)

| Bug | Causa | Fix |
|-----|-------|-----|
| `$_SESSION->flash()` en AdminController | sed sustituyó mal | Reemplazado con `$this->flash()` |
| Redundant SELECT en `galeria()` | Query extra para obtener sede ya cargada | `array_filter($sedes, ...)` — sin query extra |
| Colisión en nombres de archivo | `time() + random_int` insuficiente | `bin2hex(random_bytes(8))` |
| Open redirect en `_redirect` POST | Sin validación del valor | `$this->safeRedirect()` — solo rutas internas |
| Upload MIME bypass | Solo verificaba extensión del nombre | `finfo_file()` para MIME real del archivo |



| Bug | Causa | Fix |
|-----|-------|-----|
| `TypeError` en `calcularProgreso()` | PDO retorna `false` sin registro | `$aspirante = ... ?: null` |
| Sidebar desaparecía en perfil/docs | CSS solo en dashboard.php inline | Movido todo a `app.css` |
| Nombre no aparecía en sidebar | `session_regenerate_id()` antes de poblar `$_SESSION` | Invertir orden |
| Admin panel en blanco (pantalla verde) | Layout `main.php` envolvía en `<main>` aplastando grid | Crear `layouts/admin.php` separado |
| Vista admin no encontrada | Archivos se llamaban `admin_dashboard.php` en vez de `dashboard.php` | Renombrar archivos |
| `verCandidato()` siempre cargaba ID=0 | Leía `$_GET['id']` pero URL usa `?ver=ID` | Cambiado a `$_GET['ver']` |
| Columnas inexistentes en aspirantes | Nombres incorrectos: `anos_en_iglesia`, `nivel_educativo`, etc. | Ver schema real arriba |

---

## HOME v5 — SECCIONES

1. **Hero** — PNG transparente sobre overlay verde con imagen Unsplash, logos CNBV/DIME blancos
2. **Countdown band** — Cuenta regresiva JS a `2026-06-30T23:59:59` (días/horas/min/seg), se oculta al llegar a cero
3. **Impacto Esperado** — 4 cards doradas (200+ misioneros, 200+ iglesias, 70+ microempresas, 21+ estados)
4. **Ejes Formativos** — 3 cards (Teológica, Habilidades, Prácticas)
5. **Estructura del Programa** — Semana típica + Timeline 8 meses con scroll horizontal
6. **Perfil + Inversión** — Requisitos + Beca 50% card verde
7. **CTA Final** — "Ve y haz discípulos..." Mateo 28:19
8. **Carrusel Aliados** — Scroll infinito CSS, pausa en hover, fade en bordes, 10 organizaciones

## FOOTER — Características

- 4 columnas: Brand (logo + lema), Programa (links), Contacto (directivos + logos CNBV/DIME), CTA postulación
- Link discreto "Acceso Administradores" → `/login` (opacidad baja, visible en hover)
- **Botón flotante WhatsApp** (`position:fixed; bottom:1.5rem; right:1.5rem`) → `wa.me/584245886540` con mensaje pre-llenado

## REQUISITOS — Etapas de Selección

- Reemplazó la línea vertical (max-width 680px) con un **grid horizontal de 5 columnas**
- `.proceso-grid` > `.proceso-card` con `__conector` entre tarjetas, `__num`, `__icono`
- Responsive: 5 cols desktop → 3 cols tablet → 2 cols mobile
- Nueva sección "¿Cómo es el proceso?" antes del CTA final (pasos con estimados de tiempo)

---

## REGISTRO DE CAMBIOS EN PRODUCCIÓN

### v7 — 21/07/2026 — Itinerario Ciclo 1 reprogramado por la contingencia (sismos)

**Contexto:** el arranque de julio 2026 se pospuso por el doble sismo en Venezuela. El Ciclo 1 de
la primera cohorte se reajustó a 5 sedes (Sep 2026 – May 2027), empezando con 3 meses en La Guaira
para acompañar a las iglesias de la zona. Los Teques, Maracay y Trujillo pasan al 2do ciclo de este
grupo. Ver tabla al inicio (itinerario vigente + original archivado).

| Archivo | Cambio |
|---------|--------|
| `views/publico/programa.php` | Timeline reescrito a 5 paradas (La Guaira → Barquisimeto → Acarigua → San Felipe → Valencia) + nota de reprogramación (`.itinerario-nota`). Fechas, "Julio 2026 → Sep 2026 – May 2027", "7 → 5 ciudades", inscripciones "Abiertas" |
| `views/publico/home.php` | Countdown regresivo (vencido, target 30-jun-2026) eliminado; banda queda como aviso "Postulaciones abiertas". Popup: inicio Sep 2026, stat "Duración 8 meses" (antes "Cierre convocatoria" con días restantes). Stat "7 → 5 ciudades" |
| `views/publico/impacto.php` | Array `$sedes_itinerario` y "Estados recorridos" a las 5 sedes/estados nuevos; "7 → 5 ciudades"; "julio → septiembre 2026" |
| `views/publico/galeria.php` | "7 → 5 sedes", lista de sedes y fechas nuevas |
| `views/publico/requisitos.php` | "7 → 5 sedes", fechas Sep 2026 – May 2027, paso "Confirmación" Sep 2026 |
| `views/publico/pensum.php`, `partials/footer.php` | "julio → septiembre 2026" |
| `views/candidato/perfil.php` | Ruta-mini de movilidad y pregunta clave a las 5 sedes / Sep 2026 – May 2027 |
| `views/candidato/dashboard.php`, `dashboard_candidato.php`, `admin/dashboard.php` | Encabezado "Ciclo 1 · Julio → Septiembre 2026" |
| `database/schema.sql` | Seed `sedes` reescrito a las 5 sedes nuevas |
| `database/migracion_004_itinerario_reprogramado.sql` | **Nueva** — UPDATE de la tabla `sedes` en producción (ejecutar solo si la galería no tiene fotos ligadas a sedes) |

**Decisiones de enfoque:**
- El sitio público muestra el itinerario vigente + una nota honesta de la reprogramación (no se ocultó el cambio). El histórico completo queda solo en este doc, no en el sitio.
- Countdown eliminado porque apuntaba a una fecha vencida; convocatoria queda como "abiertas" sin fecha.



**Cambios de funcionalidad:**

| Archivo | Cambio |
|---------|--------|
| `controllers/AdminController.php` | Nuevo método `fichaCandidato()` — ficha imprimible/PDF de un postulante (foto, resumen de presentación autogenerado con concordancia de género, datos eclesiales/contacto, perfil vocacional si el test está completo, estado del proceso). Se entra por `?ver=ID&ficha=1` (delegación en `candidatos()`, igual patrón que `?test=1`). Renderiza con `renderParcial()` (documento HTML completo sin layout admin) |
| `controllers/AdminController.php` | Nuevo método `listadoCandidatos()` — listado imprimible/PDF de todos los postulantes. Se entra por `?listado=1`. Respeta filtro de estatus y búsqueda. La foto de cada uno se trae con subconsulta correlacionada (`foto_personal`, sin N+1). Columnas: #, foto, nombre, cédula (solo dígitos vía `preg_replace('/\D+/','',...)`, en una línea), edad, teléfono, ciudad/estado, iglesia, pastor. Filtro de estatus propio en la barra (dropdown que conserva la búsqueda) |
| `views/admin/ficha_candidato.php` | **Nueva** — hoja A4 vertical, CSS de impresión, paleta oficial, botón "Imprimir / Guardar PDF" |
| `views/admin/listado_candidatos.php` | **Nueva** — hoja apaisada (`@page { size: landscape }`), botón imprimir, filtro de estatus, miniaturas de foto |
| `views/admin/ver_candidato.php` | Botón "Generar ficha" en el encabezado (abre `?ver=ID&ficha=1` en pestaña nueva) |
| `views/admin/candidatos.php` | Botón "Imprimir listado" junto a "Exportar CSV" (conserva filtro y búsqueda activos) |

**Bug corregido (afectaba producción):**

| Archivo | Bug | Causa | Fix |
|---------|-----|-------|-----|
| `controllers/AdminController.php` | Error fatal `PDOException HY093: Invalid parameter number` al usar el buscador (lupa) de `/admin/candidatos` | El placeholder nombrado `:q` se repetía 4 veces en el `LIKE`, y la conexión usa `PDO::ATTR_EMULATE_PREPARES => false` (no permite reusar un placeholder) | Placeholders únicos `:q1`–`:q4` con el mismo valor. Mismo fix aplicado en `candidatos()` y `listadoCandidatos()` |

**Notas técnicas:**
- Las fotos en los reportes salen de la tabla `documentos` (tipo `foto_personal`, `mime_type LIKE 'image/%'`); URL servida desde `/uploads/documentos/...` (la columna `ruta` se guarda sin slash inicial y la vista antepone `/`)
- Los logos en los reportes usan `/public/assets/logos/` (`logo-mi-w.png` blanco en la ficha sobre fondo verde, `logo-mi-t.png` a color en el listado)
- Sin cambios en base de datos. Archivos a subir por FTP: `AdminController.php`, `ver_candidato.php`, `candidatos.php` (reemplazo) + `ficha_candidato.php`, `listado_candidatos.php` (nuevos)

### v5 — 17/06/2026 — Popup de convocatoria, subida de documentos por admin y reset de clave

**Cambios de funcionalidad:**

| Archivo | Cambio |
|---------|--------|
| `views/publico/home.php` | Popup de convocatoria: eliminado el bloque "Cupos disponibles" y su `fetch('/api/cupos')`. Quedan solo Inicio y Cierre de convocatoria |
| `views/publico/home.php` | Fix móvil: se bloquea el scroll de la página de fondo mientras el popup está abierto (clase `body.popup-abierto` con `overflow:hidden`, añadida/quitada en JS al abrir/cerrar). En móvil la tarjeta queda centrada con `max-height:90vh` y scroll interno, ya no descuadra la página al cerrar |
| `controllers/AdminController.php` | Nuevo método `subirDocumentoAdmin()` — sube/reemplaza documentos de un candidato desde el panel admin |
| `models/DocumentoModel.php` | Nuevo método `porTipo($aspirante_id, $tipo)` — busca documento existente de un tipo para reemplazarlo |
| `views/admin/ver_candidato.php` | Formulario de subida de documento por admin en el bloque Documentos |

**Acceso admin / reset de clave:**
- Admin de producción: `jfer22@gmail.com` (rol `admin`), tabla `usuarios`, columna `password` (hash bcrypt vía `password_hash`/`password_verify`)
- Procedimiento de reset olvidado: script temporal `reset-admin-tmp.php` (token + rol admin/evaluador) que fija un nuevo hash. Se sube a la raíz, se ejecuta por navegador y **se borra del hosting** después. NO se versiona en git

**Notas de despliegue:**
- Archivos temporales `reset-admin-tmp.php` y `reset-clave-tmp.php` quedan fuera de git y deben borrarse del hosting tras usarse
- Commits: `0940da1` (popup home) y `3b2b10c` (subida documentos admin) en `main`

### v4 — 31/03/2026 — Correcciones producción post-deploy

**Bugs corregidos:**

| Archivo | Bug | Causa | Fix |
|---------|-----|-------|-----|
| `CandidatoController.php` | HTTP 500 al subir documentos | Path incorrecto `public/uploads/documentos/` — no coincidía con URL `/uploads/documentos/` | Cambiado a `BASE_PATH . '/uploads/documentos/'` |
| `DocumentoModel.php` | HTTP 500 en POST documentos | Union type `int|bool` — Eliminado como precaución (innecesario, PHP 8.4 lo soporta; cambio inofensivo) |
| `AspiranteModel.php` | Potencial HTTP 500 en guardar perfil | Union type `int|bool` — Eliminado como precaución (innecesario, PHP 8.4 lo soporta; cambio inofensivo) |
| `CandidatoController.php` | HTTP 500 al subir documentos | `match()` — Reemplazado con switch/case como precaución (innecesario, PHP 8.4 lo soporta; cambio inofensivo) |
| `AdminController.php` | Potencial HTTP 500 en colaboradores | `match()` — Reemplazado con if/else como precaución (innecesario, PHP 8.4) |
| `AuthController.php` | Potencial HTTP 500 en login | `match()` — Reemplazado con if/else como precaución (innecesario, PHP 8.4) |

**Infraestructura creada en producción:**
- Carpeta `misionerosintegrales.com/uploads/` creada en cPanel
- Carpeta `misionerosintegrales.com/uploads/documentos/` creada en cPanel
- Archivo `uploads/.htaccess` subido (bloquea ejecución de scripts en uploads)

**Regla añadida a REGLAS DE ORO:**
- El servidor de producción (Webempresa) corre PHP 8.4. Sintaxis moderna (match, union types, str_contains, etc.) completamente soportada.
