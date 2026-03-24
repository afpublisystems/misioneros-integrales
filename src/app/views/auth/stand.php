<?php /* Página Stand — Registro en pantalla táctil / kiosk */ ?>

<!-- Panel izquierdo: branding -->
<div class="stand-brand">
    <img src="/public/assets/logos/logo-mi-completo-t.png"
         alt="Misioneros Integrales" class="stand-brand__logo">

    <div class="stand-brand__call">
        <h1>Únete a la misión</h1>
        <p>Programa de Formación<br>de Misioneros Integrales<br>
        <strong style="color:var(--dorado,#cea237)">Cohorte 2026 — CNBV / DIME</strong></p>
    </div>

    <!-- Contador de cupos disponibles -->
    <div class="stand-cupos <?= ($cupos['disponibles'] ?? 20) <= 5 ? 'stand-cupos--urgente' : '' ?>">
        <div class="stand-cupos__label"><i class="fas fa-users"></i> Cupos disponibles</div>
        <div class="stand-cupos__num" id="cuposNum">
            <?= htmlspecialchars((string)($cupos['disponibles'] ?? '—')) ?>
        </div>
        <div class="stand-cupos__sub" id="cuposSub">
            de <?= (int)($cupos['total'] ?? 20) ?> cupos totales
        </div>
        <div class="stand-cupos__bar">
            <div class="stand-cupos__bar-fill" id="cuposBar"
                 style="width:<?= round((($cupos['inscritos'] ?? 0) / max(1, $cupos['total'] ?? 20)) * 100) ?>%">
            </div>
        </div>
    </div>

    <!-- Instrucción QR / web -->
    <div class="stand-qr">
        <strong><i class="fas fa-globe"></i> También puedes registrarte desde tu teléfono</strong>
        <p>Visita <strong style="color:rgba(255,255,255,0.9)">misionerosintegrales.com/registro</strong><br>
        o escanea el código QR en el banner</p>
    </div>

    <!-- Logos institucionales -->
    <div class="stand-brand__orgs">
        <img src="/public/assets/logos/logo-cnbv-t.png" alt="CNBV">
        <img src="/public/assets/logos/logo-dime-t.png" alt="DIME">
    </div>
</div>

<!-- Panel derecho: formulario -->
<div class="stand-form-panel">
    <div class="stand-form-wrap">

        <div class="stand-form__header">
            <h2><i class="fas fa-user-plus" style="color:var(--verde,#167a5e)"></i> Regístrate aquí</h2>
            <p>Completa el formulario y continúa el proceso desde casa</p>
        </div>

        <?php if (!empty($errores)): ?>
        <div class="stand-alerta">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errores as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="/stand" class="stand-form" novalidate autocomplete="off">
            <?= csrf_field() ?>

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
                       placeholder="tu@correo.com" autocomplete="off" required>
            </div>

            <div class="form-row">
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
                </div>
                <div class="form-grupo">
                    <label for="password2"><i class="fas fa-lock"></i> Confirmar</label>
                    <div class="input-pass-wrap">
                        <input type="password" id="password2" name="password2"
                               placeholder="Repite la contraseña"
                               autocomplete="new-password" required>
                        <button type="button" class="toggle-pass" aria-label="Ver contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="stand-movilidad">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <strong>Requisito de movilidad:</strong>
                    El programa requiere trasladarse mensualmente (julio–febrero) a distintas ciudades.
                    ¿Tienes disponibilidad?
                </div>
            </div>

            <label class="stand-check">
                <input type="checkbox" name="acepta_terminos" value="1"
                       <?= isset($datos['acepta_terminos']) ? 'checked' : '' ?>>
                <span>Acepto los <a href="/requisitos" target="_blank">requisitos del programa</a>
                      y entiendo el compromiso de movilidad</span>
            </label>

            <button type="submit" class="stand-btn">
                <i class="fas fa-rocket"></i>
                Postularme ahora
            </button>

        </form>

        <div class="stand-ya-cuenta">
            ¿Ya tienes cuenta?
            <a href="/login"><i class="fas fa-sign-in-alt"></i> Iniciar sesión</a>
        </div>

    </div>
</div>
