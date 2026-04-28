# Misioneros Integrales — Sistema Web CNBV/DIME

## Qué es este proyecto
Sistema web para el programa de candidatos misioneros de CNBV/DIME.
Gestiona registro de candidatos, subida de documentos y evaluación de postulantes.

## Ubicaciones
- **Código fuente:** `src/` (trabajar siempre dentro de esta carpeta)
- **Contexto técnico completo:** `src/PROJECT_CONTEXT.md` (leer antes de cambios grandes)
- **Repo GitHub:** https://github.com/afpublisystems/misioneros-integrales
- **Producción:** misionerosintegrales.com
- **Hosting:** Webempresa cPanel → `public_html/misionerosintegrales.com/`
- **Usuario hosting:** hosting63201us (`/home2/hosting63201us/`)
- **Deploy:** FTP manual o File Manager cPanel (Wepanel)

## Stack
- PHP procedimental + PDO (patrón MVC sin framework)
- MySQL (PDO, singleton `Database::getConnection()`)
- Bootstrap 5 + CSS propio (Montserrat, Font Awesome 6.5)
- **PHP producción: 8.4** — se pueden usar match(), union types, str_contains(), named arguments

## Entorno local
- Docker Desktop (mismo código en Docker y XAMPP)
- Puerto web: 8080 | Puerto MySQL: 3308 | phpMyAdmin: 8081
- Auto-detección: `getenv('DB_HOST') ?: 'localhost'`

## BD
- Nombre: `misioneros_integrales_db`
- Conexión: `Database::getConnection()` (Singleton PDO)

## Estructura (producción)
```
public_html/misionerosintegrales.com/
├── index.php          ← router principal, define BASE_PATH
├── app/
│   ├── controllers/   ← PublicoController, AuthController, CandidatoController, AdminController
│   ├── models/
│   ├── views/
│   └── config/db.php
├── public/
│   ├── assets/
│   ├── css/app.css
│   └── uploads/galeria/
├── uploads/           ← documentos de candidatos (NO ejecutar scripts aquí)
│   ├── .htaccess      ← bloquea ejecución de PHP
│   └── documentos/
└── database/
```

## Rutas principales
| Ruta | Controlador |
|------|-------------|
| `/` | PublicoController |
| `/login`, `/registro` | AuthController |
| `/candidato/dashboard` | CandidatoController |
| `/candidato/documentos` | CandidatoController |
| `/candidato/test` | CandidatoController |
| `/admin` | AdminController |

## Contactos del proyecto
- José Ramos: 0424-5886540
- Yohanna de Ramos: 0424-5905392
- Email proyecto: misionerosintegrales.cnbv@gmail.com

## Reglas de este proyecto
- Siempre usar PDO prepared statements — nunca concatenar SQL
- Los cambios locales se suben por FTP; verificar acceso al Wepanel antes de empezar
- Carpeta uploads/ tiene .htaccess que bloquea PHP — no mover ni eliminar ese archivo
- El mkdir de uploads/documentos/ es automático en el código; no crear manualmente en producción
