<?php
$ruta_actual = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<nav class="navbar">
    <div class="navbar__container">

        <!-- Logo -->
        <a href="/" class="navbar__logo">
            <img src="/public/assets/logos/logo-mi-t.png" alt="Misioneros Integrales"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
            <div class="navbar__logo-text" style="display:none">
                <span class="navbar__logo-nombre">Misioneros</span>
                <span class="navbar__logo-sub">Integrales</span>
            </div>
        </a>

        <!-- Menú principal -->
        <ul class="navbar__menu" id="navbar-menu">
            <li><a href="/"           class="<?= $ruta_actual === '/'           ? 'activo' : '' ?>">Inicio</a></li>
            <li><a href="/programa"   class="<?= $ruta_actual === '/programa'   ? 'activo' : '' ?>">El Programa</a></li>
            <li><a href="/requisitos" class="<?= $ruta_actual === '/requisitos' ? 'activo' : '' ?>">Requisitos</a></li>
            <li><a href="/galeria"    class="<?= $ruta_actual === '/galeria'    ? 'activo' : '' ?>">Galería</a></li>
            <li><a href="/impacto"    class="<?= $ruta_actual === '/impacto'    ? 'activo' : '' ?>">Impacto</a></li>
            <li><a href="/contacto"   class="<?= $ruta_actual === '/contacto'   ? 'activo' : '' ?>">Contacto</a></li>
        </ul>

        <!-- Botones de sesión -->
        <div class="navbar__acciones">
            <?php if (!empty($_SESSION['usuario_id'])): ?>
                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                    <a href="/admin" class="btn btn--secundario btn--sm">
                        <i class="fas fa-cog"></i> Admin
                    </a>
                <?php else: ?>
                    <a href="/candidato/dashboard" class="btn btn--secundario btn--sm">
                        <i class="fas fa-user"></i> Mi Postulación
                    </a>
                <?php endif; ?>
                <a href="/logout" class="btn btn--outline btn--sm">Salir</a>
            <?php else: ?>
                <a href="/login"   class="btn btn--outline btn--sm">Iniciar Sesión</a>
                <a href="/registro" class="btn btn--primario btn--sm">Postularme</a>
            <?php endif; ?>
        </div>

        <!-- Hamburger móvil -->
        <button class="navbar__toggle" id="navbar-toggle" aria-label="Menú">
            <span></span><span></span><span></span>
        </button>

    </div>
</nav>
