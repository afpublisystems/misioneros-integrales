<?php $titulo = 'Crear cuenta — Postularme'; ?>

<div class="auth-page">
    <!-- Panel decorativo -->
    <div class="auth-panel auth-panel--deco">
        <div class="auth-deco__overlay"></div>
        <div class="auth-deco__contenido">
            <img src="/public/assets/logos/logo-mi-completo-t.png"
                 alt="Misioneros Integrales" class="auth-deco__logo">

            <div class="reg-pasos">
                <h3><i class="fas fa-map-signs"></i> Proceso de Postulación</h3>
                <div class="reg-paso reg-paso--activo">
                    <div class="reg-paso__num">1</div>
                    <div>
                        <strong>Crear cuenta</strong>
                        <span>Datos de acceso al sistema</span>
                    </div>
                </div>
                <div class="reg-paso">
                    <div class="reg-paso__num">2</div>
                    <div>
                        <strong>Completar perfil</strong>
                        <span>Datos personales y eclesiales</span>
                    </div>
                </div>
                <div class="reg-paso">
                    <div class="reg-paso__num">3</div>
                    <div>
                        <strong>Subir documentos</strong>
                        <span>Título bachiller y carta pastoral</span>
                    </div>
                </div>
                <div class="reg-paso">
                    <div class="reg-paso__num">4</div>
                    <div>
                        <strong>Test vocacional</strong>
                        <span>Encuesta de 60 preguntas</span>
                    </div>
                </div>
                <div class="reg-paso">
                    <div class="reg-paso__num">5</div>
                    <div>
                        <strong>Entrevista</strong>
                        <span>Con el comité evaluador</span>
                    </div>
                </div>
            </div>

            <div class="auth-deco__orgs">
                <img src="/public/assets/logos/logo-cnbv-t.png" alt="CNBV">
                <img src="/public/assets/logos/logo-dime-t.png" alt="DIME">
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="auth-panel auth-panel--form">
        <div class="auth-form-wrap">

            <div class="auth-form__header">
                <h1>Crear mi cuenta</h1>
                <p>Primer paso para postularte al programa. Solo toma 2 minutos.</p>
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

            <form method="POST" action="/registro" class="auth-form" novalidate>

                <div class="form-row">
                    <div class="form-grupo">
                        <label for="nombre"><i class="fas fa-user"></i> Nombre</label>
                        <input type="text" id="nombre" name="nombre"
                               value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>"
                               placeholder="Tu nombre" required>
                    </div>
                    <div class="form-grupo">
                        <label for="apellido"><i class="fas fa-user"></i> Apellido</label>
                        <input type="text" id="apellido" name="apellido"
                               value="<?= htmlspecialchars($datos['apellido'] ?? '') ?>"
                               placeholder="Tu apellido" required>
                    </div>
                </div>

                <div class="form-grupo">
                    <label for="email"><i class="fas fa-envelope"></i> Correo electrónico</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($datos['email'] ?? '') ?>"
                           placeholder="tu@correo.com"
                           autocomplete="email" required>
                </div>

                <div class="form-grupo">
                    <label for="password"><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="input-pass-wrap">
                        <input type="password" id="password" name="password"
                               placeholder="Mínimo 8 caracteres"
                               autocomplete="new-password" required>
                        <button type="button" class="toggle-pass" aria-label="Ver contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="pass-strength" id="pass-strength"></div>
                </div>

                <div class="form-grupo">
                    <label for="password2"><i class="fas fa-lock"></i> Confirmar contraseña</label>
                    <div class="input-pass-wrap">
                        <input type="password" id="password2" name="password2"
                               placeholder="Repite tu contraseña"
                               autocomplete="new-password" required>
                        <button type="button" class="toggle-pass" aria-label="Ver contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Advertencia de movilidad — pregunta clave del protocolo -->
                <div class="movilidad-aviso">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Requisito de movilidad:</strong>
                        Este programa requiere trasladarse mensualmente entre julio y febrero
                        a diferentes ciudades de Venezuela. ¿Tienes disponibilidad total?
                    </div>
                </div>

                <div class="form-grupo form-grupo--check">
                    <label class="check-label">
                        <input type="checkbox" name="acepta_terminos" value="1"
                               <?= isset($datos['acepta_terminos']) ? 'checked' : '' ?>>
                        <span>Acepto los <a href="/requisitos" target="_blank">requisitos del programa</a>
                              y entiendo el compromiso de movilidad.</span>
                    </label>
                </div>

                <button type="submit" class="btn btn--verde btn--block btn--lg">
                    <i class="fas fa-user-plus"></i> Crear cuenta y continuar
                </button>

            </form>

            <div class="auth-form__footer">
                <p>¿Ya tienes cuenta?</p>
                <a href="/login" class="btn btn--outline btn--block">
                    <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                </a>
                <a href="/" class="auth-form__volver">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
            </div>

        </div>
    </div>
</div>

<style>
/* Reutiliza estilos de login.php — solo añade los específicos */
.auth-page {
    display: grid; grid-template-columns: 1fr 1fr;
    min-height: calc(100vh - 73px);
}
.auth-panel--deco {
    position: relative;
    background-image: url('https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=900&q=80');
    background-size: cover; background-position: center;
    display: flex; align-items: center; justify-content: center;
}
.auth-deco__overlay {
    position: absolute; inset: 0;
    background: linear-gradient(145deg, rgba(8,60,43,0.95), rgba(22,122,94,0.88));
}
.auth-deco__contenido {
    position: relative; z-index: 2; padding: 2.5rem 2rem;
    display: flex; flex-direction: column;
    align-items: center; gap: 1.75rem; text-align: center; width: 100%;
}
.auth-deco__logo { max-width: 280px; width: 100%; filter: drop-shadow(0 4px 16px rgba(0,0,0,0.3)); }
.auth-deco__orgs { display: flex; gap: 1.5rem; align-items: center; }
.auth-deco__orgs img { height: 28px; filter: brightness(0) invert(1); opacity: 0.7; }

/* Pasos del proceso */
.reg-pasos {
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 16px; padding: 1.5rem;
    width: 100%; text-align: left;
}
.reg-pasos h3 {
    color: var(--dorado); font-size: 0.82rem;
    text-transform: uppercase; letter-spacing: 1px;
    font-weight: 700; margin-bottom: 1.25rem;
    display: flex; align-items: center; gap: 0.5rem;
}
.reg-paso {
    display: flex; align-items: center; gap: 0.85rem;
    padding: 0.6rem 0; opacity: 0.5;
    transition: opacity 0.3s;
}
.reg-paso--activo { opacity: 1; }
.reg-paso__num {
    width: 28px; height: 28px; border-radius: 50%;
    background: rgba(255,255,255,0.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 800; color: var(--blanco);
    flex-shrink: 0;
}
.reg-paso--activo .reg-paso__num { background: var(--dorado); color: var(--verde-dark); }
.reg-paso div { display: flex; flex-direction: column; }
.reg-paso strong { font-size: 0.85rem; color: var(--blanco); }
.reg-paso span   { font-size: 0.72rem; color: rgba(255,255,255,0.6); }

/* Formulario */
.auth-panel--form {
    display: flex; align-items: center; justify-content: center;
    background: var(--crema); padding: 2rem; overflow-y: auto;
}
.auth-form-wrap { width: 100%; max-width: 440px; }
.auth-form__header { margin-bottom: 1.75rem; }
.auth-form__header h1 { font-size: 1.8rem; font-weight: 900; color: var(--verde); margin-bottom: 0.4rem; }
.auth-form__header p  { color: var(--gris); font-size: 0.9rem; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.auth-form .form-grupo label {
    display: flex; align-items: center; gap: 0.4rem;
}
.auth-form .form-grupo label i { color: var(--verde); }

.input-pass-wrap { position: relative; }
.input-pass-wrap input { padding-right: 3rem; }
.toggle-pass {
    position: absolute; right: 0.75rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: var(--gris); transition: color 0.2s;
}
.toggle-pass:hover { color: var(--verde); }

/* Indicador fuerza de contraseña */
.pass-strength {
    height: 4px; border-radius: 2px; margin-top: 0.4rem;
    background: #e5e7eb; overflow: hidden; position: relative;
}
.pass-strength::after {
    content: ''; position: absolute; left: 0; top: 0; height: 100%;
    width: var(--strength, 0%);
    background: var(--strength-color, #dc2626);
    border-radius: 2px; transition: width 0.4s, background 0.4s;
}

/* Aviso movilidad */
.movilidad-aviso {
    display: flex; gap: 0.75rem; align-items: flex-start;
    background: var(--dorado-light);
    border: 1px solid rgba(206,162,55,0.4);
    border-left: 4px solid var(--dorado);
    border-radius: var(--radio); padding: 0.85rem 1rem;
    font-size: 0.83rem; color: var(--gris-dark);
    margin-bottom: 1rem;
}
.movilidad-aviso i { color: var(--dorado); flex-shrink: 0; margin-top: 2px; font-size: 1rem; }
.movilidad-aviso strong { color: var(--dorado-dark); }

/* Checkbox */
.form-grupo--check { margin-bottom: 1rem; }
.check-label {
    display: flex; align-items: flex-start; gap: 0.6rem;
    font-size: 0.85rem; color: var(--gris-dark); cursor: pointer;
}
.check-label input[type="checkbox"] {
    width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px;
    accent-color: var(--verde);
}
.check-label a { color: var(--verde); font-weight: 600; }

.auth-form__footer {
    margin-top: 1.5rem;
    display: flex; flex-direction: column; gap: 0.75rem; text-align: center;
}
.auth-form__footer > p { font-size: 0.88rem; color: var(--gris); }
.auth-form__volver {
    display: flex; align-items: center; justify-content: center; gap: 0.4rem;
    font-size: 0.83rem; color: var(--gris); margin-top: 0.25rem; transition: color 0.2s;
}
.auth-form__volver:hover { color: var(--verde); }

@media (max-width: 768px) {
    .auth-page { grid-template-columns: 1fr; }
    .auth-panel--deco { display: none; }
    .auth-panel--form { padding: 2rem 1.25rem; align-items: flex-start; padding-top: 2rem; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<script>
// Toggle ver contraseña
document.querySelectorAll('.toggle-pass').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        input.type  = input.type === 'password' ? 'text' : 'password';
        btn.querySelector('i').className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    });
});

// Indicador de fuerza de contraseña
const passInput  = document.getElementById('password');
const strengthEl = document.getElementById('pass-strength');

passInput?.addEventListener('input', () => {
    const v = passInput.value;
    let score = 0;
    if (v.length >= 8)              score++;
    if (/[A-Z]/.test(v))            score++;
    if (/[0-9]/.test(v))            score++;
    if (/[^A-Za-z0-9]/.test(v))     score++;

    const map = {
        0: ['0%',   '#dc2626'],
        1: ['25%',  '#dc2626'],
        2: ['50%',  '#f59e0b'],
        3: ['75%',  '#167a5e'],
        4: ['100%', '#15803d'],
    };
    const [w, c] = map[score];
    strengthEl.style.setProperty('--strength', w);
    strengthEl.style.setProperty('--strength-color', c);
});
</script>
