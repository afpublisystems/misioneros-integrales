<aside class="admin-sidebar">
    <div class="admin-sidebar__logo">
        <img src="/public/assets/logos/logo-mi-t.png" alt="MI">
        <div>
            <strong>Admin</strong>
            <span><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? '') ?></span>
        </div>
    </div>

    <nav class="admin-nav">
        <?php $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>

        <a href="/admin" class="admin-nav__item <?= $uri === '/admin' ? 'activo' : '' ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="/admin/candidatos" class="admin-nav__item <?= str_starts_with($uri, '/admin/candidatos') ? 'activo' : '' ?>">
            <i class="fas fa-users"></i> Candidatos
        </a>
        <a href="/admin/estadisticas" class="admin-nav__item <?= $uri === '/admin/estadisticas' ? 'activo' : '' ?>">
            <i class="fas fa-chart-bar"></i> Estadísticas
        </a>
        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <a href="/admin/galeria" class="admin-nav__item <?= $uri === '/admin/galeria' ? 'activo' : '' ?>">
            <i class="fas fa-images"></i> Galería
        </a>
        <a href="/admin/colaboradores" class="admin-nav__item <?= str_starts_with($uri, '/admin/colaboradores') ? 'activo' : '' ?>">
            <i class="fas fa-handshake"></i> Colaboradores
        </a>
        <?php endif; ?>

        <a href="/admin/perfil" class="admin-nav__item <?= str_starts_with($uri, '/admin/perfil') ? 'activo' : '' ?>">
            <i class="fas fa-user-cog"></i> Mi Perfil
        </a>
        <div class="admin-nav__sep"></div>
        <a href="/" class="admin-nav__item" target="_blank">
            <i class="fas fa-globe"></i> Ver sitio público
        </a>
        <a href="/logout" class="admin-nav__item admin-nav__item--logout">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
    </nav>

    <div class="admin-sidebar__rol">
        <i class="fas fa-shield-alt"></i>
        <?= ucfirst($_SESSION['usuario_rol'] ?? '') ?>
    </div>
</aside>
