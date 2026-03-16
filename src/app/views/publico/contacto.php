<?php
/**
 * Vista: /contacto
 * Descripción: Página de Contacto — Misioneros Integrales CNBV/DIME
 * Layout: main.php
 */
?>

<!-- ═══════════════════════════════════════════════
     HERO
═══════════════════════════════════════════════ -->
<section class="con-hero">
    <div class="con-hero__overlay"></div>
    <div class="con-hero__content">
        <span class="con-hero__etiqueta">CNBV · DIME · Venezuela</span>
        <h1 class="con-hero__titulo">Contáctanos</h1>
        <p class="con-hero__sub">¿Tienes preguntas sobre el programa? Estamos para ayudarte.</p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     CONTENIDO PRINCIPAL
═══════════════════════════════════════════════ -->
<section class="seccion">
    <div class="container">
        <div class="contacto-layout">

            <!-- Información de contacto ─────────────────── -->
            <div class="contacto-info">

                <div class="admin-panel" style="padding:0;">
                    <div class="admin-panel__header" style="padding:1.25rem 1.5rem;">
                        <h2><i class="fas fa-address-book"></i> Información de Contacto</h2>
                    </div>

                    <div class="con-info-lista">

                        <!-- Directivos CNBV -->
                        <div class="con-info-seccion">Directivos CNBV</div>

                        <div class="con-info-item">
                            <div class="con-info-item__icono con-info-item__icono--verde">
                                <i class="fas fa-crown"></i>
                            </div>
                            <div class="con-info-item__datos">
                                <strong>Presidente CNBV</strong>
                                <span>Ysac Eleazar Bermúdez</span>
                            </div>
                        </div>

                        <div class="con-info-item">
                            <div class="con-info-item__icono con-info-item__icono--verde">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="con-info-item__datos">
                                <strong>Secretario Ejecutivo CNBV</strong>
                                <span>Elier Romero</span>
                            </div>
                        </div>

                        <!-- Coordinadores del Programa -->
                        <div class="con-info-seccion" style="margin-top:0.5rem;">Coordinadores del Programa</div>

                        <div class="con-info-item">
                            <div class="con-info-item__icono con-info-item__icono--dorado">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="con-info-item__datos">
                                <strong>Director DIME</strong>
                                <span>David Silva</span>
                            </div>
                        </div>

                        <div class="con-info-item">
                            <div class="con-info-item__icono con-info-item__icono--naranja">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="con-info-item__datos">
                                <strong>Coordinador del Programa</strong>
                                <span>José F. Ramos</span>
                                <a href="tel:+584245886540">
                                    <i class="fas fa-phone"></i> 0424-588-6540
                                </a>
                            </div>
                        </div>

                        <div class="con-info-item">
                            <div class="con-info-item__icono con-info-item__icono--naranja">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div class="con-info-item__datos">
                                <strong>Coordinadora del Programa</strong>
                                <span>Yohanna de Ramos</span>
                                <a href="tel:+584245905392">
                                    <i class="fas fa-phone"></i> 0424-590-5392
                                </a>
                            </div>
                        </div>

                        <div class="con-info-item">
                            <div class="con-info-item__icono" style="background:#f0fdf4;color:#16a34a;">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="con-info-item__datos">
                                <strong>Correo Electrónico</strong>
                                <a href="mailto:misionerosintegrales.cnbv@gmail.com">
                                    misionerosintegrales.cnbv@gmail.com
                                </a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- WhatsApp directo (solo coordinadores tienen teléfono) -->
                <div class="con-whatsapp-card">
                    <div class="con-whatsapp-card__icono"><i class="fab fa-whatsapp"></i></div>
                    <div>
                        <strong>¿Prefieres WhatsApp?</strong>
                        <p>Contáctanos directamente con los coordinadores del programa</p>
                    </div>
                    <div class="con-whatsapp-card__btns">
                        <a href="https://wa.me/584245886540?text=Hola,%20tengo%20una%20consulta%20sobre%20el%20programa%20Misioneros%20Integrales"
                           target="_blank" rel="noopener" class="btn-whatsapp">
                            <i class="fab fa-whatsapp"></i> José Ramos
                        </a>
                        <a href="https://wa.me/584245905392?text=Hola,%20tengo%20una%20consulta%20sobre%20el%20programa%20Misioneros%20Integrales"
                           target="_blank" rel="noopener" class="btn-whatsapp">
                            <i class="fab fa-whatsapp"></i> Yohanna Ramos
                        </a>
                    </div>
                </div>

                <!-- Organizaciones -->
                <div class="con-orgs">
                    <p class="con-orgs__titulo">Un programa de:</p>
                    <div class="con-orgs__logos">
                        <div class="con-org-logo">
                            <img src="/public/assets/logos/logo-cnbv-t.png" alt="CNBV"
                                 onerror="this.parentElement.innerHTML='<span class=\'org-text\'>CNBV</span>'">
                            <span>Convención Nacional Bautista<br>de Venezuela</span>
                        </div>
                        <div class="con-org-sep">·</div>
                        <div class="con-org-logo">
                            <img src="/public/assets/logos/logo-dime-t.png" alt="DIME"
                                 onerror="this.parentElement.innerHTML='<span class=\'org-text\'>DIME</span>'">
                            <span>Dirección de Misiones<br>y Evangelización</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Formulario de contacto ──────────────────── -->
            <div class="contacto-form-wrap">

                <?php if (!empty($enviado)): ?>
                <div class="con-exito">
                    <div class="con-exito__icono"><i class="fas fa-check-circle"></i></div>
                    <h3>¡Mensaje enviado!</h3>
                    <p>Gracias por contactarnos. Te responderemos a la brevedad.</p>
                    <a href="/contacto" class="btn btn--verde">Enviar otro mensaje</a>
                </div>
                <?php else: ?>

                <form class="con-form" method="POST" action="/contacto" novalidate>

                    <h2 class="con-form__titulo">
                        <i class="fas fa-paper-plane"></i> Envíanos un mensaje
                    </h2>
                    <p class="con-form__sub">Responderemos en un máximo de 48 horas hábiles.</p>

                    <?php if (!empty($errores)): ?>
                    <div class="alerta alerta--error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($errores[0]) ?>
                    </div>
                    <?php endif; ?>

                    <div class="form-fila">
                        <div class="form-grupo">
                            <label for="nombre">
                                <i class="fas fa-user"></i> Nombre completo <span class="req">*</span>
                            </label>
                            <input type="text" id="nombre" name="nombre"
                                   value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                                   placeholder="Ej: Juan González" required>
                        </div>
                        <div class="form-grupo">
                            <label for="email">
                                <i class="fas fa-envelope"></i> Correo electrónico <span class="req">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="tu@correo.com" required>
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label for="telefono">
                            <i class="fas fa-phone"></i> Teléfono / WhatsApp
                        </label>
                        <input type="tel" id="telefono" name="telefono"
                               value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                               placeholder="Ej: 0424-5551234">
                    </div>

                    <div class="form-grupo">
                        <label for="asunto">
                            <i class="fas fa-tag"></i> Asunto <span class="req">*</span>
                        </label>
                        <select id="asunto" name="asunto" required>
                            <option value="">— Selecciona un tema —</option>
                            <?php
                            $asuntos = [
                                'postulacion'  => 'Consulta sobre postulación',
                                'requisitos'   => 'Dudas sobre requisitos',
                                'documentos'   => 'Problemas con documentos',
                                'fechas'       => 'Fechas y cronograma',
                                'iglesia'      => 'Información para mi iglesia',
                                'otro'         => 'Otro tema',
                            ];
                            foreach ($asuntos as $val => $lab):
                                $sel = ($_POST['asunto'] ?? '') === $val ? 'selected' : '';
                            ?>
                            <option value="<?= $val ?>" <?= $sel ?>><?= $lab ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-grupo">
                        <label for="mensaje">
                            <i class="fas fa-comment-alt"></i> Mensaje <span class="req">*</span>
                        </label>
                        <textarea id="mensaje" name="mensaje" rows="5"
                                  placeholder="Escribe tu consulta aquí..." required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
                    </div>

                    <div class="con-form__footer">
                        <p class="con-form__privacidad">
                            <i class="fas fa-lock"></i>
                            Tus datos son confidenciales y solo serán usados para responderte.
                        </p>
                        <button type="submit" class="btn btn--verde btn--lg">
                            <i class="fas fa-paper-plane"></i> Enviar mensaje
                        </button>
                    </div>

                </form>
                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════
     ¿YA TIENES CUENTA? — Acceso rápido
═══════════════════════════════════════════════ -->
<section class="seccion seccion--crema" style="padding-top:2rem;padding-bottom:2rem;">
    <div class="container">
        <div class="con-acceso-rapido">
            <div class="con-acceso-rapido__texto">
                <h3>¿Ya iniciaste tu postulación?</h3>
                <p>Ingresa a tu panel para revisar el estado de tu postulación y seguir completando tu perfil.</p>
            </div>
            <div class="con-acceso-rapido__btns">
                <a href="/login" class="btn btn--verde">
                    <i class="fas fa-sign-in-alt"></i> Iniciar sesión
                </a>
                <a href="/registro" class="btn btn--outline">
                    <i class="fas fa-user-plus"></i> Crear cuenta
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* ── Hero ─────────────────────────────────────────────── */
.con-hero {
    position: relative; min-height: 40vh;
    background: linear-gradient(135deg, var(--verde-dark) 0%, var(--verde) 100%);
    display: flex; align-items: center; justify-content: center; text-align: center;
    overflow: hidden;
}
.con-hero__overlay {
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='20' cy='20' r='3'/%3E%3C/g%3E%3C/svg%3E");
}
.con-hero__content { position: relative; z-index: 1; padding: 4rem 1.5rem; }
.con-hero__etiqueta {
    display: inline-block; font-size: 0.75rem; font-weight: 700;
    letter-spacing: 0.15em; color: var(--dorado); text-transform: uppercase;
    background: rgba(255,255,255,0.1); padding: 0.3rem 1rem; border-radius: 2rem; margin-bottom: 1.25rem;
}
.con-hero__titulo {
    font-size: clamp(2rem, 5vw, 3.5rem); font-weight: 900; color: white; margin-bottom: 0.75rem;
}
.con-hero__sub { color: rgba(255,255,255,0.85); font-size: 1.05rem; }

/* ── Layout ───────────────────────────────────────────── */
.contacto-layout {
    display: grid; grid-template-columns: 400px 1fr;
    gap: 2rem; align-items: start;
}
@media(max-width:900px) { .contacto-layout { grid-template-columns: 1fr; } }

/* ── Info lista ───────────────────────────────────────── */
.con-info-lista { padding: 0.5rem 0; }
.con-info-item {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9;
}
.con-info-item:last-child { border-bottom: none; }
.con-info-item__icono {
    width: 2.75rem; height: 2.75rem; border-radius: 0.6rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.con-info-item__icono--verde   { background: var(--verde-light);  color: var(--verde); }
.con-info-item__icono--dorado  { background: var(--dorado-light); color: var(--dorado-dark); }
.con-info-item__icono--naranja { background: #fff7ed; color: var(--naranja); }
.con-info-item__datos { display: flex; flex-direction: column; gap: 0.1rem; }
.con-info-item__datos strong { font-size: 0.78rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em; }
.con-info-item__datos span { font-size: 0.9rem; color: #1e293b; }
.con-info-item__datos a { font-size: 0.88rem; color: var(--verde); font-weight: 600; }
.con-info-item__datos a i { margin-right: 0.3rem; }
.con-info-seccion {
    padding: 0.4rem 1.5rem;
    font-size: 0.68rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--verde); background: var(--verde-light);
    border-top: 1px solid #e2e8f0;
}

/* ── WhatsApp ─────────────────────────────────────────── */
.con-whatsapp-card {
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 0.75rem;
    padding: 1.25rem; margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem;
}
.con-whatsapp-card__icono { font-size: 1.75rem; color: #16a34a; }
.con-whatsapp-card strong { display: block; font-size: 0.95rem; color: #15803d; }
.con-whatsapp-card p { font-size: 0.82rem; color: #4b5563; margin: 0; }
.con-whatsapp-card__btns { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.btn-whatsapp {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: #16a34a; color: white; padding: 0.55rem 1.1rem;
    border-radius: 0.5rem; font-weight: 600; font-size: 0.85rem;
    text-decoration: none; transition: background 0.2s;
}
.btn-whatsapp:hover { background: #15803d; }
.btn-whatsapp i { font-size: 1rem; }

/* ── Orgs ─────────────────────────────────────────────── */
.con-orgs { margin-top: 1rem; padding: 1rem; background: white; border-radius: 0.75rem; box-shadow: 0 1px 6px rgba(0,0,0,0.05); }
.con-orgs__titulo { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem; }
.con-orgs__logos { display: flex; align-items: center; gap: 1.5rem; flex-wrap: wrap; }
.con-org-logo { display: flex; flex-direction: column; align-items: center; gap: 0.35rem; text-align: center; }
.con-org-logo img { height: 38px; width: auto; filter: drop-shadow(0 1px 3px rgba(0,0,0,0.15)); }
.con-org-logo span { font-size: 0.7rem; color: #64748b; line-height: 1.3; }
.con-org-sep { font-size: 1.5rem; color: #d1d5db; }
.org-text { font-weight: 900; color: var(--verde); font-size: 1.1rem; }

/* ── Formulario ───────────────────────────────────────── */
.con-form {
    background: white; border-radius: 1rem; padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
}
.con-form__titulo { font-size: 1.3rem; font-weight: 800; color: var(--verde-dark); margin-bottom: 0.35rem; }
.con-form__titulo i { color: var(--verde); margin-right: 0.4rem; }
.con-form__sub { font-size: 0.85rem; color: #6b7280; margin-bottom: 1.5rem; }
.form-fila { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media(max-width:640px) { .form-fila { grid-template-columns: 1fr; } }
.con-form__footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1.25rem; flex-wrap: wrap; }
.con-form__privacidad { font-size: 0.75rem; color: #94a3b8; display: flex; align-items: center; gap: 0.4rem; margin: 0; }
.con-form__privacidad i { color: var(--verde); }
.req { color: #ef4444; }

/* ── Éxito ────────────────────────────────────────────── */
.con-exito {
    background: white; border-radius: 1rem; padding: 3rem 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.07); text-align: center;
}
.con-exito__icono { font-size: 4rem; color: var(--verde); margin-bottom: 1rem; }
.con-exito h3 { font-size: 1.5rem; color: var(--verde-dark); margin-bottom: 0.5rem; }
.con-exito p { color: #6b7280; margin-bottom: 1.5rem; }

/* ── Acceso rápido ────────────────────────────────────── */
.con-acceso-rapido {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1.5rem; flex-wrap: wrap;
    background: white; border-radius: 1rem; padding: 1.5rem 2rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-left: 4px solid var(--verde);
}
.con-acceso-rapido__texto h3 { font-size: 1.05rem; font-weight: 700; color: var(--verde-dark); margin-bottom: 0.25rem; }
.con-acceso-rapido__texto p  { font-size: 0.85rem; color: #6b7280; }
.con-acceso-rapido__btns { display: flex; gap: 0.75rem; }
</style>
