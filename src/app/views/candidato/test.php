<?php
/* ================================================================
   TEST VOCACIONAL — Wizard de 10 partes / 60 preguntas
   Layout: dashboard candidato (sidebar + main)
================================================================ */

$ya_completado  = !empty($test) && $test['completado'];
$tiene_progreso = !empty($test) && !$test['completado'];
$parte_ini      = (int)($parte_inicial ?? 1);
if ($parte_ini < 1 || $parte_ini > 10) $parte_ini = 1;

// Helpers de valor guardado
$r = fn($k)       => htmlspecialchars($respuestas[$k] ?? '');
$sel = fn($k, $v)  => (($respuestas[$k] ?? '') === $v)              ? 'checked' : '';
$selArr = fn($k, $v) => in_array($v, (array)($respuestas[$k] ?? [])) ? 'checked' : '';
$mx  = fn($min, $lvl) => (($respuestas['q22'][$min] ?? '') === $lvl) ? 'checked' : '';

// Partes info
$partes = [
    1  => ['titulo' => 'Llamado y Vocación Misionera',    'icono' => 'fa-star',          'rango' => 'Preguntas 1–6'],
    2  => ['titulo' => 'Formación Espiritual',             'icono' => 'fa-bible',         'rango' => 'Preguntas 7–13'],
    3  => ['titulo' => 'Vida en Comunidad y Relaciones',   'icono' => 'fa-users',         'rango' => 'Preguntas 14–20'],
    4  => ['titulo' => 'Preparación Práctica y Ministerial','icono' => 'fa-hands-helping','rango' => 'Preguntas 21–26'],
    5  => ['titulo' => 'Adaptabilidad e Interculturalidad','icono' => 'fa-globe-americas','rango' => 'Preguntas 27–32'],
    6  => ['titulo' => 'Sostenibilidad y Emprendimiento',  'icono' => 'fa-seedling',      'rango' => 'Preguntas 33–38'],
    7  => ['titulo' => 'Salud Emocional y Resiliencia',    'icono' => 'fa-heart',         'rango' => 'Preguntas 39–44'],
    8  => ['titulo' => 'Compromiso y Expectativas',        'icono' => 'fa-handshake',     'rango' => 'Preguntas 45–50'],
    9  => ['titulo' => 'Preguntas Situacionales',          'icono' => 'fa-lightbulb',     'rango' => 'Preguntas 51–55'],
    10 => ['titulo' => 'Compromiso Personal',              'icono' => 'fa-pen-nib',       'rango' => 'Preguntas 56–60'],
];
?>

<div class="dashboard-layout">

<!-- ====================================================
     SIDEBAR
===================================================== -->
<aside class="dash-sidebar">
    <div class="dash-sidebar__user">
        <div class="dash-sidebar__avatar">
            <?= mb_strtoupper(mb_substr($_SESSION['usuario_nombre'], 0, 1)) ?>
        </div>
        <div class="dash-sidebar__info">
            <strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
            <span class="badge badge--<?= $aspirante['estatus'] ?? 'borrador' ?>">
                <?= ucfirst(str_replace('_', ' ', $aspirante['estatus'] ?? 'borrador')) ?>
            </span>
        </div>
    </div>
    <nav class="dash-nav">
        <a href="/candidato/dashboard"  class="dash-nav__item"><i class="fas fa-tachometer-alt"></i> Mi Dashboard</a>
        <a href="/candidato/perfil"     class="dash-nav__item"><i class="fas fa-user-edit"></i> Mi Perfil</a>
        <a href="/candidato/documentos" class="dash-nav__item"><i class="fas fa-folder-open"></i> Documentos</a>
        <a href="/candidato/test"       class="dash-nav__item activo"><i class="fas fa-clipboard-list"></i> Test Vocacional</a>
        <a href="/logout"               class="dash-nav__item dash-nav__item--logout"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
    </nav>
</aside>

<!-- ====================================================
     MAIN
===================================================== -->
<main class="dash-main">

    <!-- Flash -->
    <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
        <i class="fas fa-<?= $_SESSION['flash']['tipo'] === 'exito' ? 'check-circle' : ($_SESSION['flash']['tipo'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
        <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
    </div>
    <?php unset($_SESSION['flash']); endif; ?>

    <!-- Header -->
    <div class="dash-header">
        <div>
            <h1>Test Vocacional</h1>
            <p>Encuesta de Perfil Vocacional y Aptitudes Misioneras · 60 preguntas / 10 partes</p>
        </div>
        <?php if ($ya_completado): ?>
        <span class="badge-estado completado"><i class="fas fa-check-circle"></i> Completado</span>
        <?php elseif ($tiene_progreso): ?>
        <span class="badge-estado en-progreso"><i class="fas fa-clock"></i> En progreso</span>
        <?php endif; ?>
    </div>

    <?php if ($ya_completado): ?>
    <!-- ================================================
         ESTADO: YA COMPLETADO
    ================================================= -->
    <div class="test-completado-card">
        <div class="test-completado-card__icono"><i class="fas fa-check-circle"></i></div>
        <h2>¡Test enviado exitosamente!</h2>
        <p>Has completado y enviado tu Encuesta de Perfil Vocacional el
            <strong><?= date('d/m/Y', strtotime($test['fecha_cierre'])) ?></strong>.
            El equipo coordinador del programa revisará tus respuestas y se comunicará contigo.</p>
        <div class="test-completado-card__acciones">
            <a href="/candidato/dashboard" class="btn btn--verde">
                <i class="fas fa-tachometer-alt"></i> Volver al Dashboard
            </a>
        </div>
        <p class="test-completado-card__nota">
            <i class="fas fa-info-circle"></i>
            Si necesitas realizar algún cambio, contacta a los coordinadores del programa.
        </p>
    </div>

    <?php else: ?>
    <!-- ================================================
         WIZARD: FORMULARIO
    ================================================= -->

    <!-- Instrucciones -->
    <div class="test-instrucciones">
        <i class="fas fa-info-circle"></i>
        <div>
            <strong>Instrucciones:</strong> Responde con sinceridad y reflexión. No hay respuestas correctas o incorrectas; buscamos conocerte mejor para acompañarte adecuadamente en tu proceso formativo. Puedes guardar tu progreso en cualquier momento y continuar después.
        </div>
    </div>

    <!-- Steps bar -->
    <div class="test-steps-bar">
        <?php foreach ($partes as $n => $p): ?>
        <div class="test-step" id="step-<?= $n ?>" title="Parte <?= $n ?>: <?= $p['titulo'] ?>">
            <div class="test-step__bubble"><?= $n ?></div>
            <div class="test-step__label"><?= ['I','II','III','IV','V','VI','VII','VIII','IX','X'][$n-1] ?></div>
        </div>
        <?php endforeach; ?>
        <div class="test-step-progress-line"><div class="test-step-progress-fill" id="step-fill"></div></div>
    </div>

    <!-- Formulario -->
    <form action="/candidato/test" method="POST" id="test-form" novalidate>
        <input type="hidden" name="completado"   id="campo-completado"   value="0">
        <input type="hidden" name="parte_actual" id="campo-parte-actual" value="<?= $parte_ini ?>">

        <?php
        // ====================================================
        // PARTE I: LLAMADO Y VOCACIÓN MISIONERA (Q1–Q6)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-1">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-star"></i> Parte I de X</span>
                <h2>Llamado y Vocación Misionera</h2>
                <p>Preguntas 1 – 6</p>
            </div>

            <!-- Q1 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">1</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cuándo comenzó a sentir el llamado al ministerio misionero?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'menos_6m' => 'Hace menos de 6 meses',
                            '6m_1a'    => 'Entre 6 meses y 1 año',
                            '1a_3a'    => 'Entre 1 y 3 años',
                            'mas_3a'   => 'Más de 3 años',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q1]" value="<?= $val ?>" <?= $sel('q1', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q2 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">2</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo describe su llamado misionero?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'impulso_constante'  => 'Un impulso constante y creciente',
                            'conviccion_clara'   => 'Una convicción clara después de un evento específico',
                            'proceso_gradual'    => 'Un proceso gradual de descubrimiento',
                            'discerniendo'       => 'Aún estoy discerniendo, pero siento atracción hacia las misiones',
                            'otro'               => 'Otro',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q2]" value="<?= $val ?>" <?= $sel('q2', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="respuestas[q2_otro]" value="<?= $r('q2_otro') ?>"
                           placeholder="Si seleccionó «Otro», especifique aquí..."
                           class="campo-texto-extra" style="margin-top:.5rem">
                </div>
            </div>

            <!-- Q3 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">3</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué eventos o experiencias confirmaron su llamado misionero?</label>
                    <textarea name="respuestas[q3]" rows="4" placeholder="Describa brevemente..."><?= $r('q3') ?></textarea>
                </div>
            </div>

            <!-- Q4 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">4</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Su pastor y/o líderes espirituales confirman su llamado?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'si_completamente'   => 'Sí, completamente',
                            'si_con_reservas'    => 'Sí, con algunas reservas',
                            'aun_evaluando'      => 'Aún lo están evaluando',
                            'no_conversado'      => 'No lo hemos conversado formalmente',
                            'no_seguros'         => 'No están seguros',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q4]" value="<?= $val ?>" <?= $sel('q4', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q5 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">5</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">En una escala del 1 al 10, ¿qué tan seguro está de que Dios le llama a las misiones?
                        <span class="escala-hint">( 1 = muy inseguro · 10 = completamente seguro )</span>
                    </label>
                    <div class="escala-10">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label class="escala-10__item">
                            <input type="radio" name="respuestas[q5]" value="<?= $i ?>" <?= $sel('q5', (string)$i) ?>>
                            <span><?= $i ?></span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Q6 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">6</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué aspectos del ministerio misionero le atraen más? <em>(seleccione hasta 3)</em></label>
                    <div class="opciones-check">
                        <?php foreach ([
                            'evangelismo'     => 'Evangelismo y predicación',
                            'discipulado'     => 'Discipulado y formación de líderes',
                            'plantacion'      => 'Plantación de iglesias',
                            'servicio_social' => 'Servicio social y comunitario',
                            'ninos_jovenes'   => 'Trabajo con niños y jóvenes',
                            'adoracion'       => 'Adoración y música',
                            'ensenanza'       => 'Enseñanza bíblica',
                            'compasion'       => 'Ministerio de compasión',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-check">
                            <input type="checkbox" name="respuestas[q6][]" value="<?= $val ?>" <?= $selArr('q6', $val) ?> data-max="3" data-grupo="q6">
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="respuestas[q6_otro]" value="<?= $r('q6_otro') ?>"
                           placeholder="Otro aspecto (opcional)..." class="campo-texto-extra" style="margin-top:.5rem">
                </div>
            </div>
        </div><!-- /parte-1 -->

        <?php
        // ====================================================
        // PARTE II: FORMACIÓN ESPIRITUAL (Q7–Q13)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-2">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-bible"></i> Parte II de X</span>
                <h2>Formación Espiritual</h2>
                <p>Preguntas 7 – 13</p>
            </div>

            <!-- Q7 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">7</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Con qué frecuencia tiene devocionales personales?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'diario'        => 'Diariamente',
                            '4_6_semana'    => '4–6 veces por semana',
                            '2_3_semana'    => '2–3 veces por semana',
                            '1_semana'      => 'Una vez por semana',
                            'ocasionalmente'=> 'Ocasionalmente',
                            'raramente'     => 'Raramente',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q7]" value="<?= $val ?>" <?= $sel('q7', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q8 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">8</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué prácticas espirituales cultiva regularmente? <em>(marque todas las que apliquen)</em></label>
                    <div class="opciones-check">
                        <?php foreach ([
                            'lectura_biblica'   => 'Lectura bíblica',
                            'oracion_individual'=> 'Oración individual',
                            'ayuno'             => 'Ayuno',
                            'memorizacion'      => 'Memorización de escrituras',
                            'oracion_intercesora'=> 'Oración intercesora',
                            'meditacion'        => 'Meditación en la Palabra',
                            'adoracion_personal'=> 'Adoración personal',
                            'journaling'        => 'Journaling / diario espiritual',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-check">
                            <input type="checkbox" name="respuestas[q8][]" value="<?= $val ?>" <?= $selArr('q8', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="respuestas[q8_otras]" value="<?= $r('q8_otras') ?>"
                           placeholder="Otras prácticas (opcional)..." class="campo-texto-extra" style="margin-top:.5rem">
                </div>
            </div>

            <!-- Q9 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">9</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Participa en un grupo pequeño, célula o discipulado?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'si_activamente'  => 'Sí, activamente',
                            'si_irregularmente'=> 'Sí, pero irregularmente',
                            'no_pero_antes'   => 'No actualmente, pero lo he hecho antes',
                            'nunca'           => 'Nunca he participado en uno',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q9]" value="<?= $val ?>" <?= $sel('q9', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q10 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">10</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Está siendo actualmente discipulado por alguien?</label>
                    <div class="opciones-radio">
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q10]" value="si" <?= $sel('q10', 'si') ?> data-toggle="q10-quien">
                            <span>Sí</span>
                        </label>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q10]" value="no_gustaria" <?= $sel('q10', 'no_gustaria') ?> data-toggle="q10-quien">
                            <span>No, pero me gustaría</span>
                        </label>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q10]" value="no_necesidad" <?= $sel('q10', 'no_necesidad') ?> data-toggle="q10-quien">
                            <span>No, no he sentido la necesidad</span>
                        </label>
                    </div>
                    <div id="q10-quien" class="campo-condicional" style="<?= ($respuestas['q10'] ?? '') === 'si' ? '' : 'display:none' ?>">
                        <input type="text" name="respuestas[q10_quien]" value="<?= $r('q10_quien') ?>"
                               placeholder="¿Por quién está siendo discipulado?" class="campo-texto-extra">
                    </div>
                </div>
            </div>

            <!-- Q11 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">11</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Está discipulando actualmente a alguien?</label>
                    <div class="opciones-radio">
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q11]" value="si" <?= $sel('q11', 'si') ?> data-toggle="q11-cuantos">
                            <span>Sí</span>
                        </label>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q11]" value="no_gustaria" <?= $sel('q11', 'no_gustaria') ?> data-toggle="q11-cuantos">
                            <span>No, pero me gustaría</span>
                        </label>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q11]" value="no_preparado" <?= $sel('q11', 'no_preparado') ?> data-toggle="q11-cuantos">
                            <span>No, no me siento preparado/a</span>
                        </label>
                    </div>
                    <div id="q11-cuantos" class="campo-condicional" style="<?= ($respuestas['q11'] ?? '') === 'si' ? '' : 'display:none' ?>">
                        <input type="number" name="respuestas[q11_cuantos]" value="<?= $r('q11_cuantos') ?>"
                               min="1" max="50" placeholder="¿A cuántas personas?" class="campo-texto-extra" style="max-width:200px">
                    </div>
                </div>
            </div>

            <!-- Q12 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">12</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">Describa su relación actual con Dios en una frase:</label>
                    <input type="text" name="respuestas[q12]" value="<?= $r('q12') ?>"
                           placeholder="Ej: «En comunión diaria y creciente...»" class="campo-texto-extra">
                </div>
            </div>

            <!-- Q13 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">13</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cuál es el mayor desafío en su vida espiritual actualmente?</label>
                    <textarea name="respuestas[q13]" rows="3" placeholder="Describa brevemente..."><?= $r('q13') ?></textarea>
                </div>
            </div>
        </div><!-- /parte-2 -->

        <?php
        // ====================================================
        // PARTE III: VIDA EN COMUNIDAD Y RELACIONES (Q14–Q20)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-3">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-users"></i> Parte III de X</span>
                <h2>Vida en Comunidad y Relaciones</h2>
                <p>Preguntas 14 – 20</p>
            </div>

            <!-- Q14 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">14</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo describiría su habilidad para trabajar en equipo?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'excelente' => 'Excelente — disfruto y lidero bien en equipos',
                            'buena'     => 'Buena — me adapto bien y contribuyo efectivamente',
                            'regular'   => 'Regular — a veces tengo dificultades pero estoy dispuesto/a a mejorar',
                            'desafiante'=> 'Desafiante — prefiero trabajar solo/a',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q14]" value="<?= $val ?>" <?= $sel('q14', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q15 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">15</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha vivido alguna vez en comunidad (internados, residencias estudiantiles, etc.)?</label>
                    <div class="opciones-radio">
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q15]" value="si" <?= $sel('q15', 'si') ?> data-toggle="q15-tiempo">
                            <span>Sí</span>
                        </label>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q15]" value="no_dispuesto" <?= $sel('q15', 'no_dispuesto') ?> data-toggle="q15-tiempo">
                            <span>No, pero estoy dispuesto/a a hacerlo</span>
                        </label>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q15]" value="no_preocupa" <?= $sel('q15', 'no_preocupa') ?> data-toggle="q15-tiempo">
                            <span>No, y me preocupa adaptarme</span>
                        </label>
                    </div>
                    <div id="q15-tiempo" class="campo-condicional" style="<?= ($respuestas['q15'] ?? '') === 'si' ? '' : 'display:none' ?>">
                        <input type="text" name="respuestas[q15_tiempo]" value="<?= $r('q15_tiempo') ?>"
                               placeholder="¿Por cuánto tiempo? (ej: 2 años)" class="campo-texto-extra">
                    </div>
                </div>
            </div>

            <!-- Q16 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">16</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo maneja los conflictos interpersonales?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'enfrenta_amor'    => 'Los enfrento directamente con amor y humildad',
                            'intenta_resolver' => 'Intento resolver pero a veces me cuesta',
                            'prefiere_evitar'  => 'Prefiero evitarlos y que se resuelvan solos',
                            'cuesta_mucho'     => 'Me cuesta mucho, necesito ayuda en esta área',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q16]" value="<?= $val ?>" <?= $sel('q16', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q17 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">17</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué tan bien recibe la corrección o crítica constructiva?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'muy_bien'     => 'Muy bien — la agradezco y aprendo de ella',
                            'bien'         => 'Bien — aunque a veces me duele, la proceso',
                            'regular'      => 'Regular — me cuesta pero lo intento',
                            'dificultad'   => 'Con dificultad — tiendo a ponerme a la defensiva',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q17]" value="<?= $val ?>" <?= $sel('q17', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q18 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">18</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">En una escala del 1 al 10, ¿qué tan flexible es ante cambios inesperados?
                        <span class="escala-hint">( 1 = muy inflexible · 10 = muy flexible )</span>
                    </label>
                    <div class="escala-10">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label class="escala-10__item">
                            <input type="radio" name="respuestas[q18]" value="<?= $i ?>" <?= $sel('q18', (string)$i) ?>>
                            <span><?= $i ?></span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Q19 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">19</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo describen sus amigos cercanos su personalidad?</label>
                    <textarea name="respuestas[q19]" rows="3" placeholder="Describa brevemente..."><?= $r('q19') ?></textarea>
                </div>
            </div>

            <!-- Q20 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">20</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Tiene algún conflicto pendiente con alguien en su iglesia o familia?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'no'              => 'No',
                            'si_trabajando'   => 'Sí, pero estoy trabajando en resolverlo',
                            'si_necesita'     => 'Sí, y necesito ayuda para resolverlo',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q20]" value="<?= $val ?>" <?= $sel('q20', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div><!-- /parte-3 -->

        <?php
        // ====================================================
        // PARTE IV: PREPARACIÓN PRÁCTICA Y MINISTERIAL (Q21–Q26)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-4">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-hands-helping"></i> Parte IV de X</span>
                <h2>Preparación Práctica y Ministerial</h2>
                <p>Preguntas 21 – 26</p>
            </div>

            <!-- Q21 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">21</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cuántas veces ha compartido el evangelio en el último mes?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'mas_10' => 'Más de 10 veces',
                            '5_10'   => '5–10 veces',
                            '2_4'    => '2–4 veces',
                            '1'      => '1 vez',
                            'ninguna'=> 'Ninguna',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q21]" value="<?= $val ?>" <?= $sel('q21', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q22: MATRIX -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">22</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué experiencia tiene en los siguientes ministerios? <em>(marque su nivel por área)</em></label>
                    <div class="tabla-matrix-wrapper">
                        <table class="tabla-matrix">
                            <thead>
                                <tr>
                                    <th>Ministerio</th>
                                    <th>Ninguna</th>
                                    <th>Básica</th>
                                    <th>Intermedia</th>
                                    <th>Avanzada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ministerios = [
                                    'evangelismo'   => 'Evangelismo',
                                    'discipulado'   => 'Discipulado',
                                    'ensenanza'     => 'Enseñanza bíblica',
                                    'predicacion'   => 'Predicación',
                                    'alabanza'      => 'Alabanza / música',
                                    'ninos'         => 'Trabajo con niños',
                                    'jovenes'       => 'Trabajo con jóvenes',
                                    'servicio'      => 'Servicio social',
                                    'liderazgo'     => 'Liderazgo de grupos',
                                ];
                                $niveles = ['ninguna', 'basica', 'intermedia', 'avanzada'];
                                foreach ($ministerios as $key => $nombre): ?>
                                <tr>
                                    <td><?= $nombre ?></td>
                                    <?php foreach ($niveles as $lvl): ?>
                                    <td class="matrix-cell">
                                        <input type="radio" name="respuestas[q22][<?= $key ?>]"
                                               value="<?= $lvl ?>" <?= $mx($key, $lvl) ?>>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Q23 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">23</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha predicado alguna vez?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'si_regularmente'   => 'Sí, regularmente (más de 10 veces)',
                            'si_ocasionalmente' => 'Sí, ocasionalmente (5–10 veces)',
                            'si_pocas'          => 'Sí, pocas veces (1–4 veces)',
                            'no_gustaria'       => 'No, pero me gustaría aprender',
                            'no_intimida'       => 'No, y me intimida',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q23]" value="<?= $val ?>" <?= $sel('q23', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q24 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">24</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha dirigido algún ministerio o proyecto en su iglesia?</label>
                    <div class="opciones-radio">
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q24]" value="si" <?= $sel('q24', 'si') ?> data-toggle="q24-desc">
                            <span>Sí</span>
                        </label>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q24]" value="no" <?= $sel('q24', 'no') ?> data-toggle="q24-desc">
                            <span>No</span>
                        </label>
                    </div>
                    <div id="q24-desc" class="campo-condicional" style="<?= ($respuestas['q24'] ?? '') === 'si' ? '' : 'display:none' ?>">
                        <textarea name="respuestas[q24_desc]" rows="2"
                                  placeholder="Describa brevemente el ministerio o proyecto..."><?= $r('q24_desc') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Q25 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">25</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha participado en algún viaje misionero?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'varios_viajes'  => 'Sí, varios viajes',
                            'un_viaje'       => 'Sí, un viaje',
                            'misiones_locales'=> 'No, pero he participado en misiones locales',
                            'primera_vez'    => 'No, esta sería mi primera experiencia misionera formal',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q25]" value="<?= $val ?>" <?= $sel('q25', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q26 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">26</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué dones espirituales cree que tiene? <em>(seleccione hasta 3 principales)</em></label>
                    <div class="opciones-check">
                        <?php foreach ([
                            'evangelismo'  => 'Evangelismo',
                            'ensenanza'    => 'Enseñanza',
                            'servicio'     => 'Servicio',
                            'liderazgo'    => 'Liderazgo',
                            'misericordia' => 'Misericordia / compasión',
                            'exhortacion'  => 'Exhortación',
                            'discernimiento'=> 'Discernimiento',
                            'fe'           => 'Fe',
                            'administracion'=> 'Administración',
                            'ayuda'        => 'Ayuda',
                            'hospitalidad' => 'Hospitalidad',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-check">
                            <input type="checkbox" name="respuestas[q26][]" value="<?= $val ?>" <?= $selArr('q26', $val) ?> data-max="3" data-grupo="q26">
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="respuestas[q26_otro]" value="<?= $r('q26_otro') ?>"
                           placeholder="Otro don (opcional)..." class="campo-texto-extra" style="margin-top:.5rem">
                </div>
            </div>
        </div><!-- /parte-4 -->

        <?php
        // ====================================================
        // PARTE V: ADAPTABILIDAD E INTERCULTURALIDAD (Q27–Q32)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-5">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-globe-americas"></i> Parte V de X</span>
                <h2>Adaptabilidad y Contexto Intercultural</h2>
                <p>Preguntas 27 – 32</p>
            </div>

            <!-- Q27 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">27</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha vivido o viajado fuera de su ciudad natal?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'vivido_otra_ciudad' => 'Sí, he vivido en otra(s) ciudad(es) por más de 6 meses',
                            'viajado_ciudades'   => 'Sí, he viajado a diferentes ciudades/regiones',
                            'poco'               => 'Muy poco, principalmente he estado en mi ciudad',
                            'nunca'              => 'No, nunca he salido de mi ciudad',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q27]" value="<?= $val ?>" <?= $sel('q27', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q28 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">28</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo se siente ante la posibilidad de servir en contextos culturalmente diferentes al suyo?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'muy_emocionado' => 'Muy emocionado/a — es parte de mi llamado',
                            'positivo'       => 'Positivo/a pero con algo de nerviosismo',
                            'neutral'        => 'Neutral — lo haría si es necesario',
                            'preocupado'     => 'Preocupado/a — preferiría servir en contextos similares al mío',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q28]" value="<?= $val ?>" <?= $sel('q28', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q29 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">29</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha tenido contacto significativo con personas de diferentes niveles socioeconómicos?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'frecuentemente'    => 'Sí, frecuentemente',
                            'ocasionalmente'    => 'Sí, ocasionalmente',
                            'poco_contacto'     => 'Poco contacto',
                            'casi_ninguno'      => 'No, casi ningún contacto',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q29]" value="<?= $val ?>" <?= $sel('q29', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q30 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">30</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué tan cómodo se siente sirviendo en comunidades vulnerables o de escasos recursos?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'muy_comodo'     => 'Muy cómodo/a — ya he servido en estos contextos',
                            'comodo'         => 'Cómodo/a — aunque no tengo mucha experiencia',
                            'poco_incomodo'  => 'Un poco incómodo/a pero dispuesto/a a aprender',
                            'muy_incomodo'   => 'Muy incómodo/a — me costaría adaptarme',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q30]" value="<?= $val ?>" <?= $sel('q30', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q31 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">31</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">En una escala del 1 al 10, ¿qué tan adaptable es a nuevos contextos y situaciones?
                        <span class="escala-hint">( 1 = muy poco adaptable · 10 = extremadamente adaptable )</span>
                    </label>
                    <div class="escala-10">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label class="escala-10__item">
                            <input type="radio" name="respuestas[q31]" value="<?= $i ?>" <?= $sel('q31', (string)$i) ?>>
                            <span><?= $i ?></span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Q32 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">32</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Estaría dispuesto a servir en zonas rurales o de difícil acceso?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'si_sin_problema'    => 'Sí, sin ningún problema',
                            'si_con_ansiedad'    => 'Sí, aunque me genera algo de ansiedad',
                            'depende'            => 'Depende de las condiciones específicas',
                            'prefiere_urbano'    => 'Preferiría servir en zonas urbanas',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q32]" value="<?= $val ?>" <?= $sel('q32', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div><!-- /parte-5 -->

        <?php
        // ====================================================
        // PARTE VI: SOSTENIBILIDAD Y EMPRENDIMIENTO (Q33–Q38)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-6">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-seedling"></i> Parte VI de X</span>
                <h2>Sostenibilidad y Emprendimiento</h2>
                <p>Preguntas 33 – 38</p>
            </div>

            <!-- Q33 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">33</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué experiencia tiene en actividades productivas o emprendimientos? <em>(marque todas las que apliquen)</em></label>
                    <div class="opciones-check">
                        <?php foreach ([
                            'propio_negocio'  => 'He tenido mi propio negocio / emprendimiento',
                            'negocio_familiar'=> 'He trabajado en negocios familiares',
                            'freelance'       => 'He hecho trabajos independientes (freelance)',
                            'cursos'          => 'He tomado cursos de emprendimiento',
                            'ninguna'         => 'Ninguna experiencia formal',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-check">
                            <input type="checkbox" name="respuestas[q33][]" value="<?= $val ?>" <?= $selArr('q33', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q34 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">34</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué habilidades prácticas / oficios posee actualmente? <em>(marque todas las que apliquen)</em></label>
                    <div class="opciones-check">
                        <?php foreach ([
                            'cocina'         => 'Cocina / repostería',
                            'costura'        => 'Costura / confección',
                            'carpinteria'    => 'Carpintería',
                            'electricidad'   => 'Electricidad',
                            'plomeria'       => 'Plomería',
                            'mecanica'       => 'Mecánica',
                            'diseno_grafico' => 'Diseño gráfico',
                            'informatica'    => 'Informática',
                            'artesania'      => 'Artesanía',
                            'agricultura'    => 'Agricultura',
                            'ventas'         => 'Ventas',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-check">
                            <input type="checkbox" name="respuestas[q34][]" value="<?= $val ?>" <?= $selArr('q34', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="respuestas[q34_otra]" value="<?= $r('q34_otra') ?>"
                           placeholder="Otra habilidad (opcional)..." class="campo-texto-extra" style="margin-top:.5rem">
                </div>
            </div>

            <!-- Q35 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">35</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo describiría su situación financiera personal?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'estable' => 'Estable — tengo ingresos regulares y ahorros',
                            'regular' => 'Regular — cubro mis necesidades básicas',
                            'limitada'=> 'Limitada — dependo de apoyo familiar',
                            'dificil' => 'Difícil — enfrento desafíos económicos constantes',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q35]" value="<?= $val ?>" <?= $sel('q35', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q36 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">36</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué tan importante es para usted aprender a autosostenerse económicamente en el ministerio?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'muy_importante'  => 'Muy importante — es parte esencial de mi visión misionera',
                            'importante'      => 'Importante — me gustaría tener esa habilidad',
                            'algo_importante' => 'Algo importante — aunque no es mi prioridad',
                            'poco_importante' => 'Poco importante — prefiero depender de soporte externo',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q36]" value="<?= $val ?>" <?= $sel('q36', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q37 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">37</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Estaría dispuesto a usar un emprendimiento como herramienta de evangelismo?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'si_excelente'    => 'Sí, me parece excelente estrategia',
                            'si_no_pensado'   => 'Sí, aunque no había pensado en ello',
                            'no_seguro'       => 'No estoy seguro/a cómo funcionaría',
                            'no_separar'      => 'No, prefiero separar el ministerio del trabajo',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q37]" value="<?= $val ?>" <?= $sel('q37', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q38 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">38</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué área de emprendimiento del programa le interesa más?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'panaderia'    => 'Panadería y repostería',
                            'textiles'     => 'Textiles y sublimación',
                            'mantenimiento'=> 'Mantenimiento y reparaciones',
                            'limpieza'     => 'Servicios de limpieza y estética',
                            'artesania'    => 'Artesanía y oficios tradicionales',
                            'otra'         => 'Otra',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q38]" value="<?= $val ?>" <?= $sel('q38', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="respuestas[q38_otra]" value="<?= $r('q38_otra') ?>"
                           placeholder="Especifique si seleccionó «Otra»..." class="campo-texto-extra" style="margin-top:.5rem">
                </div>
            </div>
        </div><!-- /parte-6 -->

        <?php
        // ====================================================
        // PARTE VII: SALUD EMOCIONAL Y RESILIENCIA (Q39–Q44)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-7">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-heart"></i> Parte VII de X</span>
                <h2>Salud Emocional y Resiliencia</h2>
                <p>Preguntas 39 – 44</p>
            </div>

            <!-- Q39 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">39</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo describiría su salud emocional actual?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'excelente'  => 'Excelente — me siento emocionalmente saludable',
                            'buena'      => 'Buena — generalmente estoy bien',
                            'regular'    => 'Regular — tengo altibajos pero los manejo',
                            'desafiante' => 'Desafiante — estoy trabajando en áreas difíciles',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q39]" value="<?= $val ?>" <?= $sel('q39', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q40 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">40</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha recibido consejería o acompañamiento psicológico alguna vez?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'si_actualmente' => 'Sí, actualmente',
                            'si_pasado'      => 'Sí, en el pasado',
                            'no_abierto'     => 'No, pero estaría abierto/a si fuera necesario',
                            'no_prefiero'    => 'No, y preferiría no hacerlo',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q40]" value="<?= $val ?>" <?= $sel('q40', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q41 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">41</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo maneja el estrés y la presión?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'muy_bien'   => 'Muy bien — tengo estrategias saludables',
                            'bien'       => 'Bien — aunque a veces me cuesta',
                            'regular'    => 'Regular — necesito mejorar en esta área',
                            'dificultad' => 'Con dificultad — el estrés me afecta significativamente',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q41]" value="<?= $val ?>" <?= $sel('q41', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q42 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">42</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Ha experimentado alguna de las siguientes situaciones en el último año? <em>(marque todas las que apliquen)</em></label>
                    <div class="opciones-check">
                        <?php foreach ([
                            'perdida_ser_querido'   => 'Pérdida de un ser querido',
                            'ruptura_relacion'      => 'Ruptura de relación significativa',
                            'problemas_familiares'  => 'Problemas familiares graves',
                            'problemas_salud'       => 'Problemas de salud serios',
                            'desempleo'             => 'Desempleo prolongado',
                            'crisis_financiera'     => 'Crisis financiera',
                            'crisis_fe'             => 'Crisis de fe',
                            'ninguna'               => 'Ninguna de las anteriores',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-check">
                            <input type="checkbox" name="respuestas[q42][]" value="<?= $val ?>" <?= $selArr('q42', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q43 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">43</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">En una escala del 1 al 10, ¿qué tan resiliente se considera ante las dificultades?
                        <span class="escala-hint">( 1 = muy poco resiliente · 10 = extremadamente resiliente )</span>
                    </label>
                    <div class="escala-10">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                        <label class="escala-10__item">
                            <input type="radio" name="respuestas[q43]" value="<?= $i ?>" <?= $sel('q43', (string)$i) ?>>
                            <span><?= $i ?></span>
                        </label>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Q44 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">44</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué hace cuando enfrenta dificultades o fracasos?</label>
                    <textarea name="respuestas[q44]" rows="4" placeholder="Describa brevemente su respuesta habitual ante las dificultades..."><?= $r('q44') ?></textarea>
                </div>
            </div>
        </div><!-- /parte-7 -->

        <?php
        // ====================================================
        // PARTE VIII: COMPROMISO Y EXPECTATIVAS (Q45–Q50)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-8">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-handshake"></i> Parte VIII de X</span>
                <h2>Compromiso y Expectativas</h2>
                <p>Preguntas 45 – 50</p>
            </div>

            <!-- Q45 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">45</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Está preparado para comprometerse con los 8 meses completos del primer ciclo?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'si_totalmente'       => 'Sí, totalmente comprometido/a',
                            'si_preocupaciones'   => 'Sí, aunque tengo algunas preocupaciones logísticas',
                            'probablemente'       => 'Probablemente, pero depende de algunas circunstancias',
                            'no_seguro'           => 'No estoy seguro/a',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q45]" value="<?= $val ?>" <?= $sel('q45', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q46 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">46</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué apoyo tiene de su familia para participar en este programa?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'apoyo_total'     => 'Apoyo total y entusiasta',
                            'apoyo_reservas'  => 'Apoyo, aunque con algunas preocupaciones',
                            'apoyo_neutral'   => 'Apoyo neutral — no se oponen pero tampoco están muy convencidos',
                            'poca_comprension'=> 'Poca o ninguna comprensión de mi decisión',
                            'oposicion'       => 'Oposición activa',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q46]" value="<?= $val ?>" <?= $sel('q46', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q47 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">47</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Tiene alguna responsabilidad familiar que pueda dificultar su participación?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'no'               => 'No',
                            'si_soluciones'    => 'Sí, pero hemos encontrado soluciones',
                            'si_trabajando'    => 'Sí, y estoy trabajando en resolverlas',
                            'si_no_sabe'       => 'Sí, y no sé cómo manejarlas',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q47]" value="<?= $val ?>" <?= $sel('q47', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q48 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">48</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cuáles son sus tres expectativas principales del programa?</label>
                    <div class="expectativas-grid">
                        <div class="expectativa-item">
                            <span class="expectativa-num">1.</span>
                            <input type="text" name="respuestas[q48_1]" value="<?= $r('q48_1') ?>" placeholder="Primera expectativa..." class="campo-texto-extra">
                        </div>
                        <div class="expectativa-item">
                            <span class="expectativa-num">2.</span>
                            <input type="text" name="respuestas[q48_2]" value="<?= $r('q48_2') ?>" placeholder="Segunda expectativa..." class="campo-texto-extra">
                        </div>
                        <div class="expectativa-item">
                            <span class="expectativa-num">3.</span>
                            <input type="text" name="respuestas[q48_3]" value="<?= $r('q48_3') ?>" placeholder="Tercera expectativa..." class="campo-texto-extra">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Q49 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">49</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué haría si enfrenta dificultades significativas durante el programa?</label>
                    <div class="opciones-check">
                        <?php foreach ([
                            'perseverar'   => 'Perseverar y buscar apoyo del equipo formativo',
                            'orar'         => 'Orar y confiar en que Dios me dará fuerzas',
                            'comunicar'    => 'Comunicar inmediatamente mis dificultades',
                            'evaluar'      => 'Evaluar si debo continuar',
                            'no_seguro'    => 'No estoy seguro/a',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-check">
                            <input type="checkbox" name="respuestas[q49][]" value="<?= $val ?>" <?= $selArr('q49', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Q50 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">50</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">Después de completar el programa, ¿cuál es su plan?</label>
                    <div class="opciones-radio">
                        <?php foreach ([
                            'misionero_tc'      => 'Servir como misionero/a a tiempo completo',
                            'plantar_iglesia'   => 'Plantar o revitalizar una iglesia',
                            'regresar_iglesia'  => 'Regresar a mi iglesia local con nuevas capacidades',
                            'estudiar_teologia' => 'Continuar estudiando teología',
                            'emprendimiento'    => 'Iniciar un emprendimiento misionero',
                            'no_claro'          => 'Aún no lo sé con claridad',
                            'otro'              => 'Otro',
                        ] as $val => $etiqueta): ?>
                        <label class="opcion-radio">
                            <input type="radio" name="respuestas[q50]" value="<?= $val ?>" <?= $sel('q50', $val) ?>>
                            <span><?= $etiqueta ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <input type="text" name="respuestas[q50_otro]" value="<?= $r('q50_otro') ?>"
                           placeholder="Si seleccionó «Otro», especifique..." class="campo-texto-extra" style="margin-top:.5rem">
                </div>
            </div>
        </div><!-- /parte-8 -->

        <?php
        // ====================================================
        // PARTE IX: PREGUNTAS SITUACIONALES (Q51–Q55)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-9">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-lightbulb"></i> Parte IX de X</span>
                <h2>Preguntas Situacionales</h2>
                <p>Preguntas 51 – 55</p>
            </div>
            <p class="test-parte__intro">Responda cada escenario describiendo cómo actuaría en esa situación concreta.</p>

            <!-- Q51 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">51</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">
                        <strong>Escenario 1:</strong> Durante el programa, su compañero/a de habitación tiene hábitos que le incomodan (desorden, ruido, horarios diferentes). ¿Cómo lo manejaría?
                    </label>
                    <textarea name="respuestas[q51]" rows="5" placeholder="Describa cómo respondería a esta situación..."><?= $r('q51') ?></textarea>
                </div>
            </div>

            <!-- Q52 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">52</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">
                        <strong>Escenario 2:</strong> Lo envían a una comunidad donde las personas no están interesadas en el evangelio y lo rechazan. ¿Cuál sería su respuesta?
                    </label>
                    <textarea name="respuestas[q52]" rows="5" placeholder="Describa cómo respondería a esta situación..."><?= $r('q52') ?></textarea>
                </div>
            </div>

            <!-- Q53 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">53</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">
                        <strong>Escenario 3:</strong> Su emprendimiento productivo no genera los ingresos esperados y se siente desanimado/a. ¿Qué haría?
                    </label>
                    <textarea name="respuestas[q53]" rows="5" placeholder="Describa cómo respondería a esta situación..."><?= $r('q53') ?></textarea>
                </div>
            </div>

            <!-- Q54 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">54</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">
                        <strong>Escenario 4:</strong> Observa que un compañero/a está violando las normas del programa. ¿Cómo actuaría?
                    </label>
                    <textarea name="respuestas[q54]" rows="5" placeholder="Describa cómo respondería a esta situación..."><?= $r('q54') ?></textarea>
                </div>
            </div>

            <!-- Q55 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">55</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">
                        <strong>Escenario 5:</strong> Siente que su formación académica no es tan sólida como la de otros participantes. ¿Cómo lo abordaría?
                    </label>
                    <textarea name="respuestas[q55]" rows="5" placeholder="Describa cómo respondería a esta situación..."><?= $r('q55') ?></textarea>
                </div>
            </div>
        </div><!-- /parte-9 -->

        <?php
        // ====================================================
        // PARTE X: COMPROMISO PERSONAL (Q56–Q60)
        // ====================================================
        ?>
        <div class="test-parte" id="parte-10">
            <div class="test-parte__header">
                <span class="test-parte__badge"><i class="fas fa-pen-nib"></i> Parte X de X</span>
                <h2>Compromiso Personal</h2>
                <p>Preguntas 56 – 60</p>
            </div>
            <p class="test-parte__intro">Esta es la última parte del test. Tómese el tiempo necesario para reflexionar profundamente antes de responder.</p>

            <!-- Q56 -->
            <div class="test-pregunta test-pregunta--destacada">
                <p class="test-pregunta__num">56</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">Escriba una declaración personal de compromiso con el programa <em>(mínimo 100 palabras)</em>:</label>
                    <p class="test-pregunta__hint">«Me comprometo a...»</p>
                    <textarea name="respuestas[q56]" rows="8"
                              placeholder="Me comprometo a..."
                              data-min-words="100"
                              id="q56-textarea"><?= $r('q56') ?></textarea>
                    <div class="contador-palabras">
                        <span id="q56-contador">0</span> palabras
                        <span id="q56-min-aviso" class="aviso-min" style="display:none">— mínimo 100 palabras requerido</span>
                    </div>
                </div>
            </div>

            <!-- Q57 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">57</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Qué sacrificios está dispuesto/a a hacer para completar este programa?</label>
                    <textarea name="respuestas[q57]" rows="4" placeholder="Reflexione y describa los sacrificios que está dispuesto/a a hacer..."><?= $r('q57') ?></textarea>
                </div>
            </div>

            <!-- Q58 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">58</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Cómo describiría en una frase su visión misionera personal?</label>
                    <input type="text" name="respuestas[q58]" value="<?= $r('q58') ?>"
                           placeholder="Mi visión misionera en una frase..." class="campo-texto-extra">
                </div>
            </div>

            <!-- Q59 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">59</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">En 10 años, ¿dónde se ve sirviendo y qué impacto espera haber generado?</label>
                    <textarea name="respuestas[q59]" rows="5" placeholder="Comparta su visión a largo plazo..."><?= $r('q59') ?></textarea>
                </div>
            </div>

            <!-- Q60 -->
            <div class="test-pregunta">
                <p class="test-pregunta__num">60</p>
                <div class="test-pregunta__body">
                    <label class="test-pregunta__label">¿Hay algo más que quiera compartir que no se haya preguntado en esta encuesta?</label>
                    <textarea name="respuestas[q60]" rows="4" placeholder="Comparta libremente cualquier información adicional relevante (opcional)..."><?= $r('q60') ?></textarea>
                </div>
            </div>

            <!-- Declaración final -->
            <div class="declaracion-final">
                <i class="fas fa-shield-alt"></i>
                <p>Declaro que he respondido esta encuesta con honestidad y reflexión, comprendiendo que la información proporcionada será utilizada para evaluar mi idoneidad para el Programa de Formación de Misioneros Integrales.</p>
            </div>
        </div><!-- /parte-10 -->

        <!-- ====================================================
             NAVEGACIÓN DEL WIZARD
        ===================================================== -->
        <div class="test-nav" id="test-nav">
            <div class="test-nav__left">
                <button type="button" class="btn btn--outline" id="btn-anterior" onclick="anteriorParte()">
                    <i class="fas fa-chevron-left"></i> Anterior
                </button>
            </div>
            <div class="test-nav__center">
                <button type="submit" class="btn btn--outline-verde" id="btn-guardar">
                    <i class="fas fa-save"></i> Guardar progreso
                </button>
            </div>
            <div class="test-nav__right">
                <button type="button" class="btn btn--verde" id="btn-siguiente" onclick="siguienteParte()">
                    Siguiente <i class="fas fa-chevron-right"></i>
                </button>
                <button type="submit" class="btn btn--dorado" id="btn-enviar" style="display:none" onclick="enviarTest(event)">
                    <i class="fas fa-paper-plane"></i> Enviar Test
                </button>
            </div>
        </div>

    </form>
    <?php endif; ?>

</main>
</div>

<style>
/* ================================================================
   DASHBOARD LAYOUT (shared from dashboard.php)
================================================================ */
.dashboard-layout { display: grid; grid-template-columns: 260px 1fr; min-height: calc(100vh - 73px); }
.dash-sidebar { background: var(--verde-dark); color: var(--blanco); padding: 2rem 0; display: flex; flex-direction: column; gap: 0; }
.dash-sidebar__user { display: flex; align-items: center; gap: .75rem; padding: 0 1.5rem 1.75rem; border-bottom: 1px solid rgba(255,255,255,.1); margin-bottom: 1rem; }
.dash-sidebar__avatar { width: 44px; height: 44px; border-radius: 50%; background: var(--dorado); color: var(--verde-dark); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: 900; flex-shrink: 0; }
.dash-sidebar__info { display: flex; flex-direction: column; gap: .25rem; }
.dash-sidebar__info strong { font-size: .88rem; line-height: 1.3; }
.dash-nav { display: flex; flex-direction: column; }
.dash-nav__item { display: flex; align-items: center; gap: .75rem; padding: .85rem 1.5rem; font-size: .88rem; font-weight: 600; color: rgba(255,255,255,.75); transition: all .2s; border-left: 3px solid transparent; }
.dash-nav__item:hover, .dash-nav__item.activo { background: rgba(255,255,255,.08); color: var(--blanco); border-left-color: var(--dorado); }
.dash-nav__item--logout { margin-top: 1rem; padding-top: 1rem; color: rgba(255,255,255,.4); border-top: 1px solid rgba(255,255,255,.08); }
.dash-nav__item--logout:hover { color: #f87171; background: rgba(220,38,38,.1); border-left-color: #f87171; }
.dash-main { background: var(--gris-claro); padding: 2rem 2.5rem; overflow-y: auto; }
.dash-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.dash-header h1 { font-size: 1.7rem; font-weight: 900; color: var(--verde); }
.dash-header p   { color: var(--gris); font-size: .88rem; margin-top: .2rem; }

/* ================================================================
   BADGE ESTADO
================================================================ */
.badge-estado { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem 1rem; border-radius: 999px; font-size: .8rem; font-weight: 700; }
.badge-estado.completado { background: #dcfce7; color: #15803d; }
.badge-estado.en-progreso { background: #fef9c3; color: #854d0e; }

/* ================================================================
   COMPLETADO CARD
================================================================ */
.test-completado-card { background: var(--blanco); border-radius: var(--radio-lg); padding: 3.5rem 2.5rem; text-align: center; max-width: 600px; margin: 0 auto; box-shadow: var(--sombra); }
.test-completado-card__icono { font-size: 4rem; color: var(--verde); margin-bottom: 1.25rem; }
.test-completado-card h2 { font-size: 1.5rem; font-weight: 900; color: var(--verde); margin-bottom: .75rem; }
.test-completado-card p  { color: var(--gris-dark); line-height: 1.7; margin-bottom: 1.5rem; }
.test-completado-card__acciones { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
.test-completado-card__nota { font-size: .8rem; color: var(--gris); margin-top: 1.5rem; margin-bottom: 0; display: flex; align-items: center; gap: .4rem; justify-content: center; }

/* ================================================================
   INSTRUCCIONES
================================================================ */
.test-instrucciones { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--radio); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; gap: .75rem; align-items: flex-start; font-size: .875rem; color: #1e40af; line-height: 1.6; }
.test-instrucciones i { flex-shrink: 0; margin-top: .15rem; }

/* ================================================================
   STEPS BAR
================================================================ */
.test-steps-bar { display: flex; align-items: center; gap: 0; margin-bottom: 2rem; position: relative; }
.test-step { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; z-index: 1; cursor: pointer; }
.test-step__bubble { width: 32px; height: 32px; border-radius: 50%; background: #e5e7eb; color: #6b7280; font-size: .78rem; font-weight: 800; display: flex; align-items: center; justify-content: center; transition: all .3s; border: 2px solid #e5e7eb; }
.test-step__label { font-size: .6rem; font-weight: 700; color: #9ca3af; margin-top: .25rem; letter-spacing: .5px; }
.test-step.activo   .test-step__bubble { background: var(--verde); color: white; border-color: var(--verde); transform: scale(1.15); }
.test-step.activo   .test-step__label  { color: var(--verde); }
.test-step.completo .test-step__bubble { background: var(--verde-light); color: var(--verde-dark); border-color: var(--verde-light); }
.test-step.completo .test-step__label  { color: var(--verde-mid); }
.test-step-progress-line { position: absolute; top: 16px; left: 5%; right: 5%; height: 2px; background: #e5e7eb; z-index: 0; }
.test-step-progress-fill { height: 100%; background: var(--verde-light); width: 0%; transition: width .4s ease; }

/* ================================================================
   PARTE
================================================================ */
.test-parte { display: none; }
.test-parte.activa { display: block; animation: fadeSlide .3s ease; }
@keyframes fadeSlide { from { opacity:0; transform: translateY(8px); } to { opacity:1; transform: translateY(0); } }

.test-parte__header { background: linear-gradient(135deg, var(--verde-dark), var(--verde)); color: white; border-radius: var(--radio-lg); padding: 1.75rem 2rem; margin-bottom: 1.5rem; }
.test-parte__badge { display: inline-flex; align-items: center; gap: .4rem; background: rgba(255,255,255,.2); border-radius: 999px; padding: .25rem .75rem; font-size: .75rem; font-weight: 700; margin-bottom: .75rem; }
.test-parte__header h2 { font-size: 1.4rem; font-weight: 900; margin-bottom: .25rem; }
.test-parte__header p  { font-size: .85rem; opacity: .8; }
.test-parte__intro { background: var(--blanco); border-left: 4px solid var(--dorado); padding: 1rem 1.25rem; border-radius: 0 var(--radio) var(--radio) 0; margin-bottom: 1.5rem; font-size: .9rem; color: var(--gris-dark); line-height: 1.6; }

/* ================================================================
   PREGUNTA
================================================================ */
.test-pregunta { background: var(--blanco); border-radius: var(--radio-lg); padding: 1.5rem; margin-bottom: 1rem; box-shadow: var(--sombra); display: flex; gap: 1rem; align-items: flex-start; }
.test-pregunta--destacada { border: 2px solid var(--dorado); }
.test-pregunta__num { width: 28px; height: 28px; border-radius: 50%; background: var(--verde); color: white; font-size: .78rem; font-weight: 900; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: .15rem; }
.test-pregunta__body { flex: 1; min-width: 0; }
.test-pregunta__label { display: block; font-size: .92rem; font-weight: 700; color: var(--gris-dark); margin-bottom: .85rem; line-height: 1.5; }
.test-pregunta__label em { font-weight: 400; color: var(--gris); font-style: normal; }
.test-pregunta__label strong { color: var(--verde-dark); }
.test-pregunta__hint { font-size: .82rem; color: var(--gris); margin-bottom: .5rem; margin-top: -.4rem; font-style: italic; }

/* ================================================================
   OPCIONES RADIO / CHECK
================================================================ */
.opciones-radio, .opciones-check { display: flex; flex-direction: column; gap: .5rem; }
.opcion-radio, .opcion-check { display: flex; align-items: flex-start; gap: .6rem; padding: .6rem .85rem; border-radius: var(--radio); border: 1.5px solid #e5e7eb; cursor: pointer; transition: all .2s; font-size: .875rem; color: var(--gris-dark); }
.opcion-radio:hover, .opcion-check:hover { border-color: var(--verde-light); background: #f0fdf4; }
.opcion-radio input, .opcion-check input { flex-shrink: 0; accent-color: var(--verde); margin-top: .15rem; width: 16px; height: 16px; }
.opcion-radio:has(input:checked), .opcion-check:has(input:checked) { border-color: var(--verde); background: #f0fdf4; color: var(--verde-dark); font-weight: 600; }

/* ================================================================
   ESCALA 1-10
================================================================ */
.escala-hint { font-size: .78rem; color: var(--gris); font-weight: 400; display: block; margin-top: .2rem; }
.escala-10 { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .5rem; }
.escala-10__item { display: flex; }
.escala-10__item input { display: none; }
.escala-10__item span { width: 40px; height: 40px; border-radius: var(--radio); border: 2px solid #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: .88rem; font-weight: 700; cursor: pointer; transition: all .2s; color: var(--gris); }
.escala-10__item input:checked + span { background: var(--verde); border-color: var(--verde); color: white; transform: scale(1.12); }
.escala-10__item span:hover { border-color: var(--verde-light); background: #f0fdf4; }

/* ================================================================
   CAMPOS TEXTO
================================================================ */
.campo-texto-extra, textarea, input[type="text"], input[type="number"] {
    width: 100%; padding: .6rem .85rem; border: 1.5px solid #e5e7eb; border-radius: var(--radio);
    font-family: inherit; font-size: .875rem; color: var(--gris-dark); background: var(--blanco);
    transition: border-color .2s; box-sizing: border-box;
}
.campo-texto-extra:focus, textarea:focus, input[type="text"]:focus, input[type="number"]:focus {
    outline: none; border-color: var(--verde); box-shadow: 0 0 0 3px rgba(34,197,94,.1);
}
textarea { resize: vertical; min-height: 80px; }

.campo-condicional { margin-top: .5rem; padding-top: .5rem; }

/* ================================================================
   MATRIX Q22
================================================================ */
.tabla-matrix-wrapper { overflow-x: auto; margin-top: .5rem; }
.tabla-matrix { width: 100%; border-collapse: collapse; font-size: .85rem; }
.tabla-matrix th { background: var(--verde-dark); color: white; padding: .6rem 1rem; text-align: center; font-weight: 700; }
.tabla-matrix th:first-child { text-align: left; }
.tabla-matrix td { padding: .55rem 1rem; border-bottom: 1px solid #f3f4f6; }
.tabla-matrix tr:nth-child(even) td { background: #f9fafb; }
.tabla-matrix tr:hover td { background: #f0fdf4; }
.tabla-matrix .matrix-cell { text-align: center; }
.tabla-matrix .matrix-cell input { accent-color: var(--verde); width: 16px; height: 16px; cursor: pointer; }

/* ================================================================
   EXPECTATIVAS Q48
================================================================ */
.expectativas-grid { display: flex; flex-direction: column; gap: .75rem; }
.expectativa-item { display: flex; align-items: center; gap: .75rem; }
.expectativa-num { font-weight: 800; color: var(--verde); font-size: 1rem; flex-shrink: 0; width: 1.5rem; }

/* ================================================================
   CONTADOR PALABRAS Q56
================================================================ */
.contador-palabras { font-size: .78rem; color: var(--gris); margin-top: .4rem; display: flex; align-items: center; gap: .4rem; }
.contador-palabras #q56-contador { font-weight: 700; color: var(--verde-dark); }
.aviso-min { color: #dc2626; }

/* ================================================================
   DECLARACIÓN FINAL
================================================================ */
.declaracion-final { background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radio-lg); padding: 1.25rem 1.5rem; margin-top: 1.5rem; display: flex; gap: 1rem; align-items: flex-start; font-size: .875rem; color: #78350f; line-height: 1.7; }
.declaracion-final i { color: var(--dorado); font-size: 1.25rem; flex-shrink: 0; margin-top: .1rem; }

/* ================================================================
   NAVEGACIÓN
================================================================ */
.test-nav { background: var(--blanco); border-radius: var(--radio-lg); padding: 1.25rem 1.5rem; margin-top: 1.5rem; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--sombra); gap: 1rem; flex-wrap: wrap; }
.test-nav__left, .test-nav__right, .test-nav__center { display: flex; gap: .75rem; align-items: center; }

/* Botones extra */
.btn--outline-verde { background: transparent; border: 2px solid var(--verde); color: var(--verde); font-weight: 700; }
.btn--outline-verde:hover { background: var(--verde); color: white; }
.btn--dorado { background: var(--dorado); color: var(--verde-dark); font-weight: 700; }
.btn--dorado:hover { background: var(--dorado-dark); }

@media (max-width: 1024px) { .dashboard-layout { grid-template-columns: 1fr; } .dash-sidebar { display: none; } .dash-main { padding: 1.5rem 1rem; } }
@media (max-width: 640px) {
    .escala-10 { gap: .25rem; }
    .escala-10__item span { width: 32px; height: 32px; font-size: .78rem; }
    .test-nav { flex-direction: column; }
    .test-nav__left, .test-nav__center, .test-nav__right { width: 100%; justify-content: center; }
    .tabla-matrix td, .tabla-matrix th { padding: .4rem .5rem; font-size: .78rem; }
}
</style>

<script>
(function() {
    const TOTAL = 10;
    let parteActual = <?= $parte_ini ?>;

    // ── Init ──────────────────────────────────────────────────
    function init() {
        mostrarParte(parteActual);
        actualizarSteps();
        actualizarNav();
        initCondicionales();
        initCheckboxMax();
        initContadorPalabras();
    }

    // ── Mostrar/ocultar partes ────────────────────────────────
    window.mostrarParte = function(n) {
        document.querySelectorAll('.test-parte').forEach(p => p.classList.remove('activa'));
        const el = document.getElementById('parte-' + n);
        if (el) { el.classList.add('activa'); el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
        parteActual = n;
        document.getElementById('campo-parte-actual').value = n;
        actualizarSteps();
        actualizarNav();
    };

    window.anteriorParte = function() { if (parteActual > 1) mostrarParte(parteActual - 1); };
    window.siguienteParte = function() { if (parteActual < TOTAL) mostrarParte(parteActual + 1); };

    // ── Steps ─────────────────────────────────────────────────
    function actualizarSteps() {
        document.querySelectorAll('.test-step').forEach((s, i) => {
            const n = i + 1;
            s.classList.remove('activo', 'completo');
            if (n === parteActual) s.classList.add('activo');
            else if (n < parteActual) s.classList.add('completo');
        });
        // Línea de progreso
        const fill = document.getElementById('step-fill');
        if (fill) fill.style.width = ((parteActual - 1) / (TOTAL - 1) * 100) + '%';
    }

    // Clic en step para navegar
    document.querySelectorAll('.test-step').forEach((s, i) => {
        s.addEventListener('click', () => mostrarParte(i + 1));
    });

    // ── Navegación botones ────────────────────────────────────
    function actualizarNav() {
        const btnAnt = document.getElementById('btn-anterior');
        const btnSig = document.getElementById('btn-siguiente');
        const btnEnv = document.getElementById('btn-enviar');
        if (btnAnt) btnAnt.style.display = parteActual === 1 ? 'none' : '';
        if (btnSig) btnSig.style.display = parteActual === TOTAL ? 'none' : '';
        if (btnEnv) btnEnv.style.display = parteActual === TOTAL ? '' : 'none';
    }

    // ── Envío final ───────────────────────────────────────────
    window.enviarTest = function(e) {
        // Validar Q56 (mínimo 100 palabras)
        const ta = document.getElementById('q56-textarea');
        if (ta) {
            const palabras = contarPalabras(ta.value);
            if (palabras < 100) {
                e.preventDefault();
                document.getElementById('q56-min-aviso').style.display = 'inline';
                ta.focus();
                ta.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
        }
        if (!confirm('¿Estás seguro de que deseas enviar el test vocacional?\n\nUna vez enviado no podrás modificar tus respuestas.')) {
            e.preventDefault();
            return false;
        }
        document.getElementById('campo-completado').value = '1';
        return true;
    };

    // ── Campos condicionales ──────────────────────────────────
    function initCondicionales() {
        document.querySelectorAll('[data-toggle]').forEach(input => {
            input.addEventListener('change', function() {
                const targetId = this.getAttribute('data-toggle');
                const target = document.getElementById(targetId);
                if (!target) return;
                // Buscar todos los radios del mismo name
                const nombre = this.name;
                const mostrar = this.checked && this.value === getValorQueAbre(targetId);
                target.style.display = mostrar ? '' : 'none';
            });
        });
    }

    function getValorQueAbre(targetId) {
        // Map de id de div → valor que lo muestra
        const mapa = {
            'q10-quien':  'si',
            'q11-cuantos':'si',
            'q15-tiempo': 'si',
            'q24-desc':   'si',
        };
        return mapa[targetId] || 'si';
    }

    // ── Límite de checkboxes ──────────────────────────────────
    function initCheckboxMax() {
        document.querySelectorAll('[data-max]').forEach(cb => {
            cb.addEventListener('change', function() {
                const grupo = this.getAttribute('data-grupo');
                const max   = parseInt(this.getAttribute('data-max'));
                const todos = document.querySelectorAll('[data-grupo="' + grupo + '"]:checked');
                if (todos.length > max) {
                    this.checked = false;
                    // pequeño feedback visual
                    const label = this.closest('label');
                    if (label) { label.style.animation = 'none'; label.style.opacity = '.5'; setTimeout(() => label.style.opacity = '', 300); }
                }
            });
        });
    }

    // ── Contador palabras Q56 ─────────────────────────────────
    function contarPalabras(texto) {
        return texto.trim() === '' ? 0 : texto.trim().split(/\s+/).length;
    }

    function initContadorPalabras() {
        const ta = document.getElementById('q56-textarea');
        const cont = document.getElementById('q56-contador');
        const aviso = document.getElementById('q56-min-aviso');
        if (!ta || !cont) return;
        function actualizar() {
            const n = contarPalabras(ta.value);
            cont.textContent = n;
            cont.style.color = n >= 100 ? 'var(--verde)' : '#dc2626';
            if (aviso) aviso.style.display = n < 100 && n > 0 ? 'inline' : 'none';
        }
        ta.addEventListener('input', actualizar);
        actualizar();
    }

    // Arranca
    init();
})();
</script>
