<?php $titulo = 'Iniciar Sesión'; ?>

<div class="auth-page">
    <!-- Panel izquierdo — decorativo -->
    <div class="auth-panel auth-panel--deco">
        <div class="auth-deco__overlay"></div>
        <div class="auth-deco__contenido">
            <img src="/public/assets/logos/logo-mi-completo-t.png"
                 alt="Misioneros Integrales" class="auth-deco__logo">
            <blockquote class="auth-deco__quote">
                <i class="fas fa-quote-left"></i>
                "De la formación a la misión: Transforma, Multiplica e Impacta"
                <cite>— Mateo 13:23</cite>
            </blockquote>
            <div class="auth-deco__orgs">
                <img src="/public/assets/logos/logo-cnbv-t.png" alt="CNBV">
                <img src="/public/assets/logos/logo-dime-t.png" alt="DIME">
            </div>
        </div>
    </div>

    <!-- Panel derecho — formulario -->
    <div class="auth-panel auth-panel--form">
        <div class="auth-form-wrap">

            <div class="auth-form__header">
                <h1>Bienvenido</h1>
                <p>Accede a tu cuenta para gestionar tu postulación</p>
            </div>

            <?php if (!empty($errores)): ?>
            <div class="alerta alerta--error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <?php foreach ($errores as $e): ?>
                        <p><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="auth-form" novalidate>
                <?= csrf_field() ?>

                <div class="form-grupo">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Correo electrónico
                    </label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($email ?? '') ?>"
                           placeholder="tu@correo.com"
                           autocomplete="email" required>
                </div>

                <div class="form-grupo">
                    <label for="password">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="input-pass-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Tu contraseña"
                               autocomplete="current-password" required>
                        <button type="button" class="toggle-pass" aria-label="Ver contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn--verde btn--block btn--lg">
                    <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                </button>

            </form>

            <div class="auth-form__footer">
                <p>¿No tienes cuenta aún?</p>
                <a href="/registro" class="btn btn--outline btn--block">
                    <i class="fas fa-user-plus"></i> Crear cuenta y postularme
                </a>
                <a href="/" class="auth-form__volver">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
            </div>

        </div>
    </div>
</div>

<style>
/* Layout de dos paneles */
.auth-page {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: calc(100vh - 73px);
}

/* Panel decorativo izquierdo */
.auth-panel--deco {
    position: relative;
    background-image: url('https://images.unsplash.com/photo-1474314243412-cd4a79f02e27?w=900&q=80');
    background-size: cover;
    background-position: center;
    display: flex; align-items: center; justify-content: center;
}
.auth-deco__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(145deg, rgba(8,60,43,0.95), rgba(22,122,94,0.88));
}
.auth-deco__contenido {
    position: relative; z-index: 2;
    padding: 3rem 2.5rem;
    display: flex; flex-direction: column;
    align-items: center; gap: 2rem; text-align: center;
}
.auth-deco__logo {
    max-width: 320px; width: 100%;
    filter: drop-shadow(0 4px 16px rgba(0,0,0,0.3));
}
.auth-deco__quote {
    color: rgba(255,255,255,0.9);
    font-size: 1rem; font-style: italic;
    border-left: 3px solid var(--dorado);
    padding-left: 1rem; text-align: left;
    line-height: 1.7; max-width: 340px;
}
.auth-deco__quote .fa-quote-left { color: var(--dorado); margin-right: 0.3rem; }
.auth-deco__quote cite {
    display: block; margin-top: 0.5rem;
    font-style: normal; font-weight: 700;
    color: var(--dorado); font-size: 0.85rem;
}
.auth-deco__orgs {
    display: flex; gap: 1.5rem; align-items: center;
}
.auth-deco__orgs img {
    height: 32px; width: auto;
    filter: brightness(0) invert(1); opacity: 0.75;
}

/* Panel formulario derecho */
.auth-panel--form {
    display: flex; align-items: center; justify-content: center;
    background: var(--crema); padding: 2rem;
}
.auth-form-wrap {
    width: 100%; max-width: 420px;
}
.auth-form__header { margin-bottom: 2rem; }
.auth-form__header h1 {
    font-size: 2rem; font-weight: 900;
    color: var(--verde); margin-bottom: 0.4rem;
}
.auth-form__header p { color: var(--gris); font-size: 0.95rem; }

.auth-form .form-grupo label {
    display: flex; align-items: center; gap: 0.4rem;
}
.auth-form .form-grupo label i { color: var(--verde); }

/* Wrapper de input con botón ver contraseña */
.input-pass-wrap { position: relative; }
.input-pass-wrap input { padding-right: 3rem; }
.toggle-pass {
    position: absolute; right: 0.75rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--gris); font-size: 1rem;
    transition: color 0.2s;
}
.toggle-pass:hover { color: var(--verde); }

.auth-form__footer {
    margin-top: 1.75rem;
    display: flex; flex-direction: column; gap: 0.75rem;
    text-align: center;
}
.auth-form__footer > p { font-size: 0.88rem; color: var(--gris); }
.auth-form__volver {
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    font-size: 0.83rem; color: var(--gris); margin-top: 0.5rem;
    transition: color 0.2s;
}
.auth-form__volver:hover { color: var(--verde); }

@media (max-width: 768px) {
    .auth-page { grid-template-columns: 1fr; }
    .auth-panel--deco { display: none; }
    .auth-panel--form { padding: 2rem 1.25rem; align-items: flex-start; padding-top: 3rem; }
}
</style>

<script>
// Toggle mostrar/ocultar contraseña
document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        const tipo  = input.type === 'password' ? 'text' : 'password';
        input.type  = tipo;
        btn.querySelector('i').className = tipo === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
});
</script>
