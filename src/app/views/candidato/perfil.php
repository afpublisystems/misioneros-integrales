<?php $titulo = 'Mi Perfil'; ?>

<div class="dashboard-layout">

    <!-- Sidebar -->
    <aside class="dash-sidebar">
        <div class="dash-sidebar__user">
            <div class="dash-sidebar__avatar">
                <?= mb_strtoupper(mb_substr($_SESSION['usuario_nombre'] ?: $_SESSION['usuario_email'], 0, 1)) ?>
            </div>
            <div class="dash-sidebar__info">
                <strong><?= htmlspecialchars($_SESSION['usuario_nombre'] ?: $_SESSION['usuario_email']) ?></strong>
                <span class="badge badge--<?= $aspirante['estatus'] ?? 'borrador' ?>">
                    <?= ucfirst(str_replace('_', ' ', $aspirante['estatus'] ?? 'borrador')) ?>
                </span>
            </div>
        </div>
        <nav class="dash-nav">
            <a href="/candidato/dashboard"  class="dash-nav__item"><i class="fas fa-tachometer-alt"></i> Mi Dashboard</a>
            <a href="/candidato/perfil"     class="dash-nav__item activo"><i class="fas fa-user-edit"></i> Mi Perfil</a>
            <a href="/candidato/documentos" class="dash-nav__item"><i class="fas fa-folder-open"></i> Documentos</a>
            <a href="/candidato/test"       class="dash-nav__item"><i class="fas fa-clipboard-list"></i> Test Vocacional</a>
            <a href="/logout" class="dash-nav__item dash-nav__item--logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </nav>
    </aside>

    <!-- Main -->
    <main class="dash-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : 'exclamation-circle' ?>"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <div class="dash-header">
            <div>
                <h1>Mi Perfil</h1>
                <p>Completa todos los campos para avanzar en tu postulación</p>
            </div>
            <!-- Indicador de completitud -->
            <div class="perfil-pct" id="perfil-pct">
                <svg viewBox="0 0 36 36" class="perfil-pct__svg">
                    <path class="perfil-pct__bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    <path class="perfil-pct__fill" id="pct-circle" stroke-dasharray="0, 100"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                </svg>
                <span id="pct-num">0%</span>
            </div>
        </div>

        <!-- Tabs de sección -->
        <div class="perfil-tabs" role="tablist">
            <button class="perfil-tab activo" data-tab="personal"  role="tab"><i class="fas fa-user"></i> Personal</button>
            <button class="perfil-tab"        data-tab="eclesial"  role="tab"><i class="fas fa-church"></i> Eclesial</button>
            <button class="perfil-tab"        data-tab="academico" role="tab"><i class="fas fa-graduation-cap"></i> Académico</button>
            <button class="perfil-tab"        data-tab="movilidad" role="tab"><i class="fas fa-map-marker-alt"></i> Movilidad</button>
        </div>

        <form method="POST" action="/candidato/perfil" id="form-perfil" novalidate>

            <!-- ── TAB 1: DATOS PERSONALES ──────────────────────── -->
            <div class="perfil-panel activo" id="tab-personal">
                <div class="form-card">
                    <h2 class="form-card__titulo"><i class="fas fa-user"></i> Datos Personales</h2>

                    <div class="form-grid-2">
                        <div class="form-grupo">
                            <label for="nombres">Nombres <span class="req">*</span></label>
                            <input type="text" id="nombres" name="nombres" required
                                   value="<?= htmlspecialchars($aspirante['nombres'] ?? '') ?>"
                                   placeholder="Tus nombres completos">
                        </div>
                        <div class="form-grupo">
                            <label for="apellidos">Apellidos <span class="req">*</span></label>
                            <input type="text" id="apellidos" name="apellidos" required
                                   value="<?= htmlspecialchars($aspirante['apellidos'] ?? '') ?>"
                                   placeholder="Tus apellidos completos">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-grupo">
                            <label for="cedula">Cédula de identidad <span class="req">*</span></label>
                            <input type="text" id="cedula" name="cedula" required
                                   value="<?= htmlspecialchars($aspirante['cedula'] ?? '') ?>"
                                   placeholder="V-12345678">
                        </div>
                        <div class="form-grupo">
                            <label for="fecha_nacimiento">Fecha de nacimiento <span class="req">*</span></label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                                   value="<?= htmlspecialchars($aspirante['fecha_nacimiento'] ?? '') ?>">
                        </div>
                        <div class="form-grupo">
                            <label for="edad">Edad</label>
                            <input type="number" id="edad" name="edad" min="18" max="40" readonly
                                   value="<?= htmlspecialchars($aspirante['edad'] ?? '') ?>"
                                   placeholder="Se calcula automáticamente">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-grupo">
                            <label for="genero">Género <span class="req">*</span></label>
                            <select id="genero" name="genero" required>
                                <option value="">Selecciona...</option>
                                <?php foreach (['masculino' => 'Masculino', 'femenino' => 'Femenino'] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($aspirante['genero'] ?? '') === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-grupo">
                            <label for="estado_civil">Estado civil <span class="req">*</span></label>
                            <select id="estado_civil" name="estado_civil" required>
                                <option value="">Selecciona...</option>
                                <?php foreach ([
                                    'soltero'  => 'Soltero(a)',
                                    'casado'   => 'Casado(a)',
                                    'viudo'    => 'Viudo(a)',
                                    'divorciado' => 'Divorciado(a)',
                                ] as $v => $l): ?>
                                <option value="<?= $v ?>" <?= ($aspirante['estado_civil'] ?? '') === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-grupo">
                            <label for="hijos">Número de hijos</label>
                            <input type="number" id="hijos" name="hijos" min="0" max="20"
                                   value="<?= htmlspecialchars($aspirante['hijos'] ?? '0') ?>">
                        </div>
                        <div class="form-grupo">
                            <label for="telefono">Teléfono / WhatsApp <span class="req">*</span></label>
                            <input type="tel" id="telefono" name="telefono" required
                                   value="<?= htmlspecialchars($aspirante['telefono'] ?? '') ?>"
                                   placeholder="04XX-XXXXXXX">
                        </div>
                        <div class="form-grupo">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" name="email"
                                   value="<?= htmlspecialchars($aspirante['email'] ?? $_SESSION['usuario_email'] ?? '') ?>"
                                   placeholder="tu@correo.com">
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-grupo">
                            <label for="estado_origen">Estado <span class="req">*</span></label>
                            <select id="estado_origen" name="estado_origen" required>
                                <option value="">Selecciona...</option>
                                <?php
                                $estados = ['Amazonas','Anzoátegui','Apure','Aragua','Barinas','Bolívar',
                                    'Carabobo','Cojedes','Delta Amacuro','Falcón','Guárico','Lara',
                                    'Mérida','Miranda','Monagas','Nueva Esparta','Portuguesa','Sucre',
                                    'Táchira','Trujillo','Vargas','Yaracuy','Zulia',
                                    'Distrito Capital'];
                                foreach ($estados as $e):
                                ?>
                                <option value="<?= $e ?>" <?= ($aspirante['estado_origen'] ?? '') === $e ? 'selected' : '' ?>>
                                    <?= $e ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-grupo">
                            <label for="ciudad_origen">Ciudad / Municipio <span class="req">*</span></label>
                            <input type="text" id="ciudad_origen" name="ciudad_origen" required
                                   value="<?= htmlspecialchars($aspirante['ciudad_origen'] ?? '') ?>"
                                   placeholder="Tu ciudad">
                        </div>
                        <div class="form-grupo">
                            <label for="direccion">Dirección</label>
                            <input type="text" id="direccion" name="direccion"
                                   value="<?= htmlspecialchars($aspirante['direccion'] ?? '') ?>"
                                   placeholder="Dirección de habitación">
                        </div>
                    </div>

                    <div class="tab-nav-btns">
                        <span></span>
                        <button type="button" class="btn btn--verde" onclick="cambiarTab('eclesial')">
                            Siguiente: Datos Eclesiales <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── TAB 2: DATOS ECLESIALES ──────────────────────── -->
            <div class="perfil-panel" id="tab-eclesial">
                <div class="form-card">
                    <h2 class="form-card__titulo"><i class="fas fa-church"></i> Datos Eclesiales</h2>

                    <div class="aviso-info">
                        <i class="fas fa-info-circle"></i>
                        <div>El candidato debe ser miembro bautizado hace <strong>al menos 1 año</strong>
                        en una iglesia bautista venezolana.</div>
                    </div>

                    <div class="form-grupo">
                        <label for="iglesia">Nombre de la Iglesia <span class="req">*</span></label>
                        <input type="text" id="iglesia" name="iglesia" required
                               value="<?= htmlspecialchars($aspirante['iglesia'] ?? '') ?>"
                               placeholder="Iglesia Bautista...">
                    </div>

                    <div class="form-grid-2">
                        <div class="form-grupo">
                            <label for="pastor">Nombre del Pastor <span class="req">*</span></label>
                            <input type="text" id="pastor" name="pastor" required
                                   value="<?= htmlspecialchars($aspirante['pastor'] ?? '') ?>"
                                   placeholder="Nombre completo del pastor">
                        </div>
                        <div class="form-grupo">
                            <label for="telefono_pastor">Teléfono del Pastor <span class="req">*</span></label>
                            <input type="tel" id="telefono_pastor" name="telefono_pastor" required
                                   value="<?= htmlspecialchars($aspirante['telefono_pastor'] ?? '') ?>"
                                   placeholder="04XX-XXXXXXX">
                        </div>
                    </div>

                    <div class="form-grupo">
                        <label for="anos_bautizado">¿Hace cuántos años fue bautizado(a)? <span class="req">*</span></label>
                        <select id="anos_bautizado" name="anos_bautizado" required>
                            <option value="">Selecciona...</option>
                            <?php for ($i = 1; $i <= 30; $i++): ?>
                            <option value="<?= $i ?>" <?= ($aspirante['anos_bautizado'] ?? 0) == $i ? 'selected' : '' ?>>
                                <?= $i ?> <?= $i === 1 ? 'año' : 'años' ?>
                            </option>
                            <?php endfor; ?>
                            <option value="31" <?= ($aspirante['anos_bautizado'] ?? 0) >= 31 ? 'selected' : '' ?>>
                                Más de 30 años
                            </option>
                        </select>
                        <?php if (($aspirante['anos_bautizado'] ?? 0) < 1): ?>
                        <small class="form-ayuda form-ayuda--advertencia">
                            <i class="fas fa-exclamation-triangle"></i>
                            Requisito mínimo: 1 año de bautizado
                        </small>
                        <?php endif; ?>
                    </div>

                    <div class="tab-nav-btns">
                        <button type="button" class="btn btn--outline" onclick="cambiarTab('personal')">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn--verde" onclick="cambiarTab('academico')">
                            Siguiente: Datos Académicos <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── TAB 3: DATOS ACADÉMICOS ──────────────────────── -->
            <div class="perfil-panel" id="tab-academico">
                <div class="form-card">
                    <h2 class="form-card__titulo"><i class="fas fa-graduation-cap"></i> Formación Académica</h2>

                    <div class="form-grupo">
                        <label for="nivel_academico">Nivel académico alcanzado <span class="req">*</span></label>
                        <select id="nivel_academico" name="nivel_academico" required>
                            <option value="">Selecciona...</option>
                            <?php foreach ([
                                'bachiller'          => 'Bachiller',
                                'tecnico_superior'   => 'Técnico Superior Universitario (TSU)',
                                'universitario'      => 'Universitario / Licenciado',
                                'postgrado'          => 'Postgrado / Especialización',
                                'maestria'           => 'Maestría',
                                'doctorado'          => 'Doctorado',
                            ] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($aspirante['nivel_academico'] ?? '') === $v ? 'selected' : '' ?>>
                                <?= $l ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Requisito: Título de Bachiller -->
                    <div class="form-grupo form-grupo--check">
                        <label class="check-label">
                            <input type="checkbox" name="titulo_bachiller" value="1"
                                   <?= !empty($aspirante['titulo_bachiller']) ? 'checked' : '' ?>>
                            <span>
                                <strong>Poseo el título de Bachiller</strong> (requisito obligatorio del programa)
                            </span>
                        </label>
                    </div>

                    <div class="aviso-info aviso-info--dorado">
                        <i class="fas fa-graduation-cap"></i>
                        <div>
                            <strong>Nota:</strong> Al completar los 3 ciclos del programa recibirás la
                            <strong>Certificación Ministerial — Mención Misiones (CNBV)</strong>.
                            El título de Bachiller es requisito de ingreso indispensable.
                        </div>
                    </div>

                    <div class="tab-nav-btns">
                        <button type="button" class="btn btn--outline" onclick="cambiarTab('eclesial')">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button type="button" class="btn btn--verde" onclick="cambiarTab('movilidad')">
                            Siguiente: Movilidad <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── TAB 4: MOVILIDAD (PREGUNTA CLAVE) ───────────── -->
            <div class="perfil-panel" id="tab-movilidad">
                <div class="form-card">
                    <h2 class="form-card__titulo"><i class="fas fa-map-marker-alt"></i> Compromiso de Movilidad</h2>

                    <div class="movilidad-banner">
                        <i class="fas fa-route movilidad-banner__icono"></i>
                        <div>
                            <h3>Requisito fundamental del programa</h3>
                            <p>El programa recorre <strong>7 ciudades</strong> en 8 meses
                            (Julio 2026 – Febrero 2027). El candidato debe poder trasladarse
                            mensualmente sin impedimentos.</p>
                        </div>
                    </div>

                    <!-- Ruta visual -->
                    <div class="ruta-mini">
                        <?php
                        $paradas = [
                            ['ciudad' => 'Los Teques', 'mes' => 'Jul'],
                            ['ciudad' => 'Maracay',    'mes' => 'Ago'],
                            ['ciudad' => 'San Felipe', 'mes' => 'Sep'],
                            ['ciudad' => 'Valencia',   'mes' => 'Oct'],
                            ['ciudad' => 'Acarigua',   'mes' => 'Nov'],
                            ['ciudad' => 'Barquisimeto','mes' => 'Dic-Ene'],
                            ['ciudad' => 'Trujillo',   'mes' => 'Feb'],
                        ];
                        foreach ($paradas as $i => $p): ?>
                        <div class="ruta-mini__parada">
                            <div class="ruta-mini__punto"></div>
                            <div class="ruta-mini__ciudad"><?= $p['ciudad'] ?></div>
                            <div class="ruta-mini__mes"><?= $p['mes'] ?></div>
                        </div>
                        <?php if ($i < count($paradas) - 1): ?>
                        <div class="ruta-mini__linea"></div>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- PREGUNTA CLAVE DEL PROTOCOLO -->
                    <div class="pregunta-clave">
                        <div class="pregunta-clave__label">
                            <i class="fas fa-exclamation-triangle"></i>
                            Pregunta determinante
                        </div>
                        <p class="pregunta-clave__texto">
                            ¿Tienes algún compromiso ineludible que te impida trasladarte mensualmente
                            entre julio 2026 y febrero 2027?
                        </p>
                        <div class="pregunta-clave__opciones">
                            <label class="radio-label radio-label--verde">
                                <input type="radio" name="compromiso_movilidad" value="1"
                                       <?= !empty($aspirante['compromiso_movilidad']) ? 'checked' : '' ?>>
                                <span><i class="fas fa-check-circle"></i> No tengo impedimentos — Puedo movilizarme</span>
                            </label>
                            <label class="radio-label radio-label--rojo">
                                <input type="radio" name="compromiso_movilidad" value="0"
                                       <?= isset($aspirante['compromiso_movilidad']) && !$aspirante['compromiso_movilidad'] ? 'checked' : '' ?>>
                                <span><i class="fas fa-times-circle"></i> Tengo un compromiso que limita mi movilidad</span>
                            </label>
                        </div>
                    </div>

                    <!-- Campo condicional: detallar el impedimento -->
                    <div class="form-grupo" id="impedimento-wrap"
                         style="<?= (isset($aspirante['compromiso_movilidad']) && !$aspirante['compromiso_movilidad']) ? '' : 'display:none' ?>">
                        <label for="detalle_impedimento">Describe el compromiso o impedimento</label>
                        <textarea id="detalle_impedimento" name="detalle_impedimento" rows="3"
                                  placeholder="Explica brevemente cuál es el impedimento..."><?= htmlspecialchars($aspirante['detalle_impedimento'] ?? '') ?></textarea>
                        <small class="form-ayuda form-ayuda--advertencia">
                            <i class="fas fa-info-circle"></i>
                            El comité evaluará si el impedimento es subsanable.
                        </small>
                    </div>

                    <div class="tab-nav-btns">
                        <button type="button" class="btn btn--outline" onclick="cambiarTab('academico')">
                            <i class="fas fa-arrow-left"></i> Anterior
                        </button>
                        <button type="submit" class="btn btn--verde btn--lg">
                            <i class="fas fa-save"></i> Guardar perfil
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </main>
</div>

<script>
// ── Tabs ───────────────────────────────────────────────────
function cambiarTab(nombre) {
    document.querySelectorAll('.perfil-tab, .perfil-panel').forEach(el => el.classList.remove('activo'));
    document.querySelector(`[data-tab="${nombre}"]`).classList.add('activo');
    document.getElementById(`tab-${nombre}`).classList.add('activo');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
document.querySelectorAll('.perfil-tab').forEach(btn => {
    btn.addEventListener('click', () => cambiarTab(btn.dataset.tab));
});

// ── Calcular edad desde fecha nacimiento ───────────────────
document.getElementById('fecha_nacimiento')?.addEventListener('change', function() {
    const hoy  = new Date();
    const nac  = new Date(this.value);
    let edad   = hoy.getFullYear() - nac.getFullYear();
    const mes  = hoy.getMonth() - nac.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) edad--;
    document.getElementById('edad').value = edad >= 0 ? edad : '';
});

// ── Mostrar/ocultar campo impedimento ─────────────────────
document.querySelectorAll('[name="compromiso_movilidad"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const wrap = document.getElementById('impedimento-wrap');
        wrap.style.display = this.value === '0' ? 'block' : 'none';
    });
});

// ── Indicador de completitud ──────────────────────────────
function calcularPct() {
    const requeridos = document.querySelectorAll('[required]');
    let llenos = 0;
    requeridos.forEach(el => {
        if (el.value && el.value.trim()) llenos++;
    });
    const pct = Math.round((llenos / requeridos.length) * 100);
    document.getElementById('pct-num').textContent = pct + '%';
    document.getElementById('pct-circle')
            .setAttribute('stroke-dasharray', `${pct}, 100`);
}
document.querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('input', calcularPct);
    el.addEventListener('change', calcularPct);
});
calcularPct(); // Inicial
</script>
