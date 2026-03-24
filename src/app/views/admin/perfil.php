<?php $titulo = 'Mi Perfil'; ?>

<div class="admin-layout">
    <?php require APP_PATH . '/views/partials/admin_sidebar.php'; ?>

    <main class="admin-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="admin-header">
            <div>
                <h1>Mi Perfil</h1>
                <p>Actualiza tus datos de acceso al panel</p>
            </div>
        </div>

        <div class="perfil-grid">

            <!-- Card: Datos personales -->
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-user"></i> Datos de la cuenta</h2>
                </div>
                <div class="perfil-form">

                    <?php if (!empty($errores) && ($accion ?? '') === 'datos'): ?>
                    <div class="alerta alerta--error">
                        <?php foreach ($errores as $e): ?>
                            <div><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="/admin/perfil">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="datos">

                        <div class="form-fila">
                            <div class="form-grupo">
                                <label>Nombre</label>
                                <input type="text" name="nombre"
                                       value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>"
                                       required placeholder="Tu nombre">
                            </div>
                            <div class="form-grupo">
                                <label>Apellido</label>
                                <input type="text" name="apellido"
                                       value="<?= htmlspecialchars($usuario['apellido'] ?? '') ?>"
                                       required placeholder="Tu apellido">
                            </div>
                        </div>

                        <div class="form-grupo">
                            <label>Correo electrónico</label>
                            <input type="email" name="email"
                                   value="<?= htmlspecialchars($usuario['email'] ?? '') ?>"
                                   required placeholder="tu@correo.com">
                        </div>

                        <div class="form-grupo">
                            <label>Rol</label>
                            <input type="text" value="<?= ucfirst($usuario['rol'] ?? '') ?>" disabled>
                        </div>

                        <div class="perfil-form__footer">
                            <button type="submit" class="btn btn--verde">
                                <i class="fas fa-save"></i> Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Card: Cambiar contraseña -->
            <div class="admin-panel">
                <div class="admin-panel__header">
                    <h2><i class="fas fa-lock"></i> Cambiar contraseña</h2>
                </div>
                <div class="perfil-form">

                    <?php if (!empty($errores) && ($accion ?? '') === 'password'): ?>
                    <div class="alerta alerta--error">
                        <?php foreach ($errores as $e): ?>
                            <div><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="/admin/perfil">
                        <?= csrf_field() ?>
                        <input type="hidden" name="accion" value="password">

                        <div class="form-grupo">
                            <label>Contraseña actual</label>
                            <div class="input-pass">
                                <input type="password" name="password_actual" id="pass_actual"
                                       required placeholder="Tu contraseña actual">
                                <button type="button" onclick="togglePass('pass_actual', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-grupo">
                            <label>Nueva contraseña</label>
                            <div class="input-pass">
                                <input type="password" name="password_nueva" id="pass_nueva"
                                       required placeholder="Mínimo 8 caracteres">
                                <button type="button" onclick="togglePass('pass_nueva', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-grupo">
                            <label>Confirmar nueva contraseña</label>
                            <div class="input-pass">
                                <input type="password" name="password_confirma" id="pass_confirma"
                                       required placeholder="Repite la nueva contraseña">
                                <button type="button" onclick="togglePass('pass_confirma', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="pass-requisitos">
                            <i class="fas fa-info-circle"></i>
                            Mínimo 8 caracteres. Usa letras, números y símbolos para mayor seguridad.
                        </div>

                        <div class="perfil-form__footer">
                            <button type="submit" class="btn btn--verde">
                                <i class="fas fa-key"></i> Cambiar contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
