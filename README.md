# 🌍 Misioneros Integrales
### Sistema Web de Gestión del Programa de Formación Misionera

> *"De la formación a la misión: Transforma, Multiplica e Impacta"*
> — Mateo 13:23

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Enabled-2496ED?style=flat-square&logo=docker&logoColor=white)](https://docker.com)
[![Arquitectura](https://img.shields.io/badge/Arquitectura-MVC-78B428?style=flat-square)](.)
[![Estado](https://img.shields.io/badge/Estado-En%20Desarrollo-F7941D?style=flat-square)](.)

---

## 📋 Descripción

Sistema web integral para la gestión del **Programa de Formación de Misioneros Integrales** de la CNBV/DIME. La plataforma centraliza el proceso de selección de aspirantes, el seguimiento del itinerario nacional de 8 meses y la rendición de cuentas pública del programa.

**Capacidad:** 40 participantes por ciclo  
**Duración:** Julio 2026 – Febrero 2027 (Ciclo 1)  
**Cobertura:** 7 sedes en Venezuela

---

## 🗺️ Itinerario del Programa

| # | Mes | Sede | Duración |
|---|-----|------|----------|
| 1 | Julio | Los Teques | Mes completo |
| 2 | Agosto | Maracay | Mes completo |
| 3 | Septiembre | San Felipe | Mes completo |
| 4 | Octubre | Valencia | Mes completo |
| 5 | Noviembre | Acarigua | Mes completo |
| 6 | Diciembre – Enero | Barquisimeto | 15 días c/u |
| 7 | Febrero | Trujillo | Mes completo |

---

## ✨ Módulos del Sistema

### 🌐 Nivel Público
- **Home** con hero banner y llamada a la acción
- **El Programa**: propósito, cronograma, ejes formativos
- **Requisitos**: perfil del aspirante y documentación
- **Galería**: álbumes por sede (fotos + videos YouTube/Vimeo)
- **Impacto**: dashboard público de estadísticas
- **Login/Registro** para candidatos

### 👤 Dashboard del Candidato
- Perfil personal y eclesial
- Seguimiento visual del proceso (Solicitud → Documentos → Test → Entrevista → Resultado)
- Carga de documentos (carta pastoral, título, etc.)

### ⚙️ Back-office Administrativo (DIME-CNBV)
- Panel de KPIs (candidatos totales, aprobados, pendientes)
- Gestión CRUD de candidatos con filtros
- Módulo de evaluación y entrevistas
- Logística itinerante (calendario + lista de 40 participantes)
- CMS de galería multimedia
- Editor de estadísticas públicas
- Exportación de reportes (Excel/PDF)

---

## 🛠️ Stack Tecnológico

```
├── Backend      →  PHP 8.2 (Apache)
├── Base de datos →  MySQL 8.0
├── Arquitectura →  MVC (PHP puro)
├── Entorno      →  Docker Desktop
├── Frontend     →  HTML + CSS (Montserrat)
└── Seguridad    →  PDO (prepared statements)
```

---

## 🎨 Identidad Visual

| Token | Color | Uso |
|-------|-------|-----|
| `--azul` | `#00528F` | Color principal, headers |
| `--verde` | `#78B428` | Acentos, éxito, naturaleza |
| `--naranja` | `#F7941D` | CTA, alertas, énfasis |
| Fuente | Montserrat | Toda la interfaz |

---

## 🚀 Instalación y Ejecución

### Requisitos previos
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) instalado y activo

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/afpublisystems/misioneros-integrales.git
cd misioneros-integrales

# 2. Levantar los contenedores
docker-compose up -d

# 3. Verificar que estén activos
docker ps

# 4. Abrir en el navegador
# http://localhost:8080
```

### Puertos
| Servicio | Puerto |
|----------|--------|
| Web (Apache/PHP) | `8080` |
| MySQL | `3306` (o `3307` si hay conflicto) |

---

## 📁 Estructura del Proyecto

```
misioneros-integrales/
├── docker-compose.yml
├── Dockerfile
├── PROJECT_CONTEXT.md          ← Contexto compartido (IA + equipo)
├── README.md
└── src/                        ← Raíz del código (→ /var/www/html)
    ├── index.php               ← Router principal
    ├── app/
    │   ├── controllers/
    │   ├── models/
    │   ├── views/
    │   │   ├── layouts/
    │   │   └── partials/
    │   └── config/
    │       └── db.php          ← Conexión PDO
    └── public/
        ├── css/
        ├── js/
        └── assets/
            ├── img/
            └── logos/          ← Logos CNBV y DIME
```

---

## 🗄️ Base de Datos

| Tabla | Descripción |
|-------|-------------|
| `sedes` | Ciudades del itinerario |
| `aspirantes` | Registro de candidatos |
| `usuarios` | Autenticación y roles |
| `flujo_proceso` | Estados del proceso de selección |
| `documentos` | Archivos subidos por aspirantes |
| `multimedia` | Galería por sede |
| `impacto_estadisticas` | Métricas del programa |
| `configuracion_impacto` | Contadores públicos (clave-valor) |

---

## 🔐 Reglas de Desarrollo

1. **PDO obligatorio** — Sin queries directas, sin `mysqli`
2. **Videos externos** — Solo embeds de YouTube/Vimeo
3. **MVC estricto** — Lógica en Modelos, presentación en Vistas
4. **Nomenclatura en español** para tablas y campos
5. **Ramas:** `main` (producción) · `develop` (desarrollo activo)

---

## 🤖 Flujo de Trabajo con IA

Este proyecto usa un modelo colaborativo con dos agentes IA:

| Agente | Rol | Responsabilidad |
|--------|-----|-----------------|
| **Gemini** | Arquitecto | Esquemas SQL, reglas de negocio, flujos de usuario |
| **Claude** | Ejecutor | Código PHP, implementación MVC, corrección de errores |

El archivo `PROJECT_CONTEXT.md` en la raíz del repo es la **memoria compartida** del proyecto. Ambos agentes lo leen al inicio de cada sesión para tener contexto completo.

---

## 📞 Contacto

**Organización:** CNBV / DIME — Misioneros Integrales  
**Desarrollo:** AFP Publi Systems  
**Correo:** misionerosintegrales.cnbv@gmail.com  
**Teléfono:** 0424-5886540 (José Ramos)

---

<div align="center">
  <sub>Construido con ❤️ para la misión en Venezuela</sub>
</div>