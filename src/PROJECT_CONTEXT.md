# PROJECT_CONTEXT.md
# Proyecto: Misioneros Integrales — Sistema Web CNBV/DIME
# Última actualización: 09/03/2026
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

## ITINERARIO CICLO 1 — Julio 2026 a Febrero 2027 (8 meses)

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
    ├── app/
    │   ├── config/db.php                   ✅ PDO Singleton (Database::getConnection())
    │   ├── controllers/
    │   │   ├── Controller.php              ✅ Clase base (render, redirigir, requireAuth)
    │   │   ├── PublicoController.php       ✅ 6 rutas públicas
    │   │   ├── AuthController.php          ✅ Login/Registro/Logout
    │   │   ├── CandidatoController.php     ✅ Dashboard/Perfil/Docs
    │   │   └── AdminController.php         ✅ Dashboard/Candidatos/Perfil/Estadísticas
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
    │       │   ├── dashboard.php           ✅
    │       │   ├── perfil.php              ✅ 4 tabs
    │       │   └── documentos.php          ✅ drag&drop
    │       ├── admin/
    │       │   ├── dashboard.php           ✅ KPIs + últimas postulaciones + por estado
    │       │   ├── candidatos.php          ✅ Tabla + filtros + modal cambio estatus
    │       │   ├── perfil.php              ✅ Cambiar datos personales y contraseña
    │       │   └── estadisticas.php        ⏳ Vista pendiente
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
| `flujo_proceso` | Estados 5 etapas por aspirante | ✅ |
| `documentos` | Archivos subidos | ✅ |
| `test_vocacional` | Respuestas encuesta 60 preguntas (JSON) | ✅ |
| `multimedia` | Galería por sede | ✅ |
| `impacto_estadisticas` | Contadores públicos editables | ✅ Con datos |

---

## RUTAS IMPLEMENTADAS (index.php)

```
GET:
  /                       → home
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
  /admin/estadisticas     → AdminController::estadisticas
  /admin/galeria          → AdminController::galeria (pendiente vista)
  /admin/perfil           → AdminController::perfil ✅ NUEVO

POST:
  /login                  → AuthController::login
  /registro               → AuthController::registro
  /candidato/perfil       → CandidatoController::guardarPerfil
  /candidato/documentos   → CandidatoController::subirDocumento
  /candidato/test         → CandidatoController::guardarTest
  /admin/candidatos       → AdminController::actualizarEstatus
  /admin/estadisticas     → AdminController::actualizarEstadisticas
  /admin/perfil           → AdminController::actualizarPerfil ✅ NUEVO
```

---

## PROCESO DE SELECCIÓN (5 etapas en flujo_proceso)

1. Solicitud Formal
2. Evaluación Documental
3. Test Vocacional (60 preguntas / 10 partes)
4. Entrevista Personal
5. Confirmación y Admisión

---

## USUARIOS EN BD (estado actual)

| id | Nombre | Email | Rol | Contraseña |
|----|--------|-------|-----|------------|
| 1 | José Ramos | misionerosintegrales.cnbv@gmail.com | admin | `password` (CAMBIAR) |
| 2 | Deilimar Pereira | deilipereira05@gmail.com | candidato | su clave |
| 3 | Admin CNBV | admin@misionerosintegrales.com | admin | incompleto |

> ⚠️ Cambiar contraseña del usuario 1 usando genhash.php

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

### Mi Perfil (`/admin/perfil`) ✅ NUEVO HOY
- Card 1: Cambiar nombre, apellido, email
- Card 2: Cambiar contraseña (actual + nueva + confirmar)
- Validaciones: email único, contraseña mínimo 8 caracteres, hash bcrypt
- Actualiza la sesión inmediatamente

---

## BUGS CORREGIDOS (historial)

| Bug | Causa | Fix |
|-----|-------|-----|
| `TypeError` en `calcularProgreso()` | PDO retorna `false` sin registro | `$aspirante = ... ?: null` |
| Sidebar desaparecía en perfil/docs | CSS solo en dashboard.php inline | Movido todo a `app.css` |
| Nombre no aparecía en sidebar | `session_regenerate_id()` antes de poblar `$_SESSION` | Invertir orden |
| Admin panel en blanco (pantalla verde) | Layout `main.php` envolvía en `<main>` aplastando grid | Crear `layouts/admin.php` separado |
| Vista admin no encontrada | Archivos se llamaban `admin_dashboard.php` en vez de `dashboard.php` | Renombrar archivos |

---

## ESTADO DE FASES

| Fase | Descripción | Estado |
|------|-------------|--------|
| FASE 1 | MVC base + Schema + Layout + Home v5 + Carrusel aliados | ✅ Completa |
| FASE 2 | Autenticación (login, registro, sesiones, roles) | ✅ Completa |
| FASE 3 | Módulo Candidato (dashboard, perfil 4 tabs, documentos drag&drop) | ✅ Completa |
| FASE 4 | Panel Admin (dashboard KPIs, gestión candidatos, mi perfil) | ✅ Completa* |
| FASE 5 | Páginas públicas (El Programa, Requisitos, Galería, Impacto, Contacto) | ⏳ Pendiente |
| FASE 6 | Test Vocacional (60 preguntas / 10 partes) | ⏳ Pendiente |
| FASE 7 | Vista detalle candidato en admin + cambio de etapas flujo | ⏳ Pendiente |
| FASE 8 | Vista estadísticas admin (editar contadores públicos) | ⏳ Pendiente |
| FASE 9 | Galería admin (multimedia por sede) | ⏳ Pendiente |

> *Fase 4 pendiente: vista estadísticas y galería admin

---

## PENDIENTES INMEDIATOS

1. **Cambiar contraseña** del usuario admin id=1 (actualmente `password`)
2. **Borrar** `src/diagnostico.php` si aún existe
3. **Vista estadísticas admin** (`src/app/views/admin/estadisticas.php`)
4. **Vista detalle candidato** en admin (`/admin/candidatos?ver=ID`)
5. **Fase 5** — Páginas públicas completas

---

## REGLAS DE ORO DEL PROYECTO

1. PDO obligatorio (Database::getConnection()) — sin mysqli directo
2. Videos → YouTube/Vimeo embed únicamente (sin subida directa)
3. MVC estricto: lógica en Modelos, presentación en Vistas
4. Nomenclatura en español para tablas y campos BD
5. Pregunta clave de movilidad en formulario de registro
6. Archivos individuales cuando son 1-2, ZIP solo si son muchos
7. Layout `admin.php` para vistas admin (sin navbar/footer público)
8. `require APP_PATH . '/views/...'` — no usar `__DIR__` en vistas
9. `display_errors` activado en `layouts/admin.php` — quitar en producción

---

## HOME v5 — SECCIONES

1. **Hero** — PNG transparente sobre overlay verde con imagen Unsplash, logos CNBV/DIME blancos
2. **Impacto Esperado** — 4 cards doradas (200+ misioneros, 200+ iglesias, 70+ microempresas, 21+ estados)
3. **Ejes Formativos** — 3 cards (Teológica, Habilidades, Prácticas)
4. **Estructura del Programa** — Semana típica + Timeline 8 meses con scroll horizontal
5. **Perfil + Inversión** — Requisitos + Beca 50% card verde
6. **CTA Final** — "Ve y haz discípulos..." Mateo 28:19
7. **Carrusel Aliados** — Scroll infinito CSS, pausa en hover, fade en bordes, 10 organizaciones

---

## NOTAS TÉCNICAS IMPORTANTES

- **`Database::getConnection()`** — nombre correcto del método Singleton (NO `getInstance()`)
- **Layout admin**: Las vistas admin usan `render('admin/vista', [...], 'admin')` como tercer parámetro
- **Migracion `nota_evaluador`**: YA aplicada en BD (no ejecutar de nuevo → error #1060 duplicado)
- **CSS admin**: Todo en `app.css` al final, sección "PANEL ADMIN"
- **Sesión**: `$_SESSION['usuario_nombre']`, `$_SESSION['usuario_email']`, `$_SESSION['usuario_rol']`, `$_SESSION['usuario_id']`
