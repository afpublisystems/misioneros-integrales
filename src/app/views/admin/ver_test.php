<?php
/* ================================================================
   ADMIN — VER TEST VOCACIONAL
   Muestra las 60 respuestas del candidato organizadas por parte
================================================================ */

// Helper: obtener valor legible de una respuesta
$r = fn(string $k): string => htmlspecialchars((string)($respuestas[$k] ?? ''));
$rv = fn(string $k): string => (string)($respuestas[$k] ?? '');

// Mapas de etiquetas para opciones de radio
$opciones = [
    // Q1
    'q1' => [
        'menos_6m' => 'Hace menos de 6 meses',
        '6m_1a'    => 'Entre 6 meses y 1 año',
        '1a_3a'    => 'Entre 1 y 3 años',
        'mas_3a'   => 'Más de 3 años',
    ],
    // Q2
    'q2' => [
        'impulso_constante' => 'Un impulso constante y creciente',
        'conviccion_clara'  => 'Una convicción clara después de un evento específico',
        'proceso_gradual'   => 'Un proceso gradual de descubrimiento',
        'discerniendo'      => 'Aún estoy discerniendo, pero siento atracción hacia las misiones',
        'otro'              => 'Otro',
    ],
    // Q4
    'q4' => [
        'si_completamente' => 'Sí, completamente',
        'si_con_reservas'  => 'Sí, con algunas reservas',
        'aun_evaluando'    => 'Aún lo están evaluando',
        'no_conversado'    => 'No lo hemos conversado formalmente',
        'no_seguros'       => 'No están seguros',
    ],
    // Q6 (checkbox)
    'q6' => [
        'evangelismo'     => 'Evangelismo y predicación',
        'discipulado'     => 'Discipulado y formación de líderes',
        'plantacion'      => 'Plantación de iglesias',
        'servicio_social' => 'Servicio social y comunitario',
        'ninos_jovenes'   => 'Trabajo con niños y jóvenes',
        'adoracion'       => 'Adoración y música',
        'ensenanza'       => 'Enseñanza bíblica',
        'compasion'       => 'Ministerio de compasión',
    ],
    // Q7
    'q7' => [
        'diario'         => 'Diariamente',
        '4_6_semana'     => '4–6 veces por semana',
        '2_3_semana'     => '2–3 veces por semana',
        '1_semana'       => 'Una vez por semana',
        'ocasionalmente' => 'Ocasionalmente',
        'raramente'      => 'Raramente',
    ],
    // Q8 (checkbox)
    'q8' => [
        'lectura_biblica'    => 'Lectura bíblica',
        'oracion_individual' => 'Oración individual',
        'ayuno'              => 'Ayuno',
        'memorizacion'       => 'Memorización de escrituras',
        'oracion_intercesora'=> 'Oración intercesora',
        'meditacion'         => 'Meditación en la Palabra',
        'adoracion_personal' => 'Adoración personal',
        'journaling'         => 'Journaling / diario espiritual',
    ],
    // Q9
    'q9' => [
        'si_activamente'   => 'Sí, activamente',
        'si_irregularmente'=> 'Sí, pero irregularmente',
        'no_pero_antes'    => 'No actualmente, pero lo he hecho antes',
        'nunca'            => 'Nunca he participado en uno',
    ],
    // Q10
    'q10' => [
        'si'           => 'Sí',
        'no_gustaria'  => 'No, pero me gustaría',
        'no_necesidad' => 'No, no he sentido la necesidad',
    ],
    // Q11
    'q11' => [
        'si'           => 'Sí',
        'no_gustaria'  => 'No, pero me gustaría',
        'no_preparado' => 'No, no me siento preparado/a',
    ],
    // Q14
    'q14' => [
        'excelente'  => 'Excelente — disfruto y lidero bien en equipos',
        'buena'      => 'Buena — me adapto bien y contribuyo efectivamente',
        'regular'    => 'Regular — a veces tengo dificultades pero estoy dispuesto/a a mejorar',
        'desafiante' => 'Desafiante — prefiero trabajar solo/a',
    ],
    // Q15
    'q15' => [
        'si'           => 'Sí',
        'no_dispuesto' => 'No, pero estoy dispuesto/a a hacerlo',
        'no_preocupa'  => 'No, y me preocupa adaptarme',
    ],
    // Q16
    'q16' => [
        'enfrenta_amor'    => 'Los enfrento directamente con amor y humildad',
        'intenta_resolver' => 'Intento resolver pero a veces me cuesta',
        'prefiere_evitar'  => 'Prefiero evitarlos y que se resuelvan solos',
        'cuesta_mucho'     => 'Me cuesta mucho, necesito ayuda en esta área',
    ],
    // Q17
    'q17' => [
        'muy_bien'   => 'Muy bien — la agradezco y aprendo de ella',
        'bien'       => 'Bien — aunque a veces me duele, la proceso',
        'regular'    => 'Regular — me cuesta pero lo intento',
        'dificultad' => 'Con dificultad — tiendo a ponerme a la defensiva',
    ],
    // Q20
    'q20' => [
        'no'            => 'No',
        'si_trabajando' => 'Sí, pero estoy trabajando en resolverlo',
        'si_necesita'   => 'Sí, y necesito ayuda para resolverlo',
    ],
    // Q21
    'q21' => [
        'mas_10' => 'Más de 10 veces',
        '5_10'   => '5–10 veces',
        '2_4'    => '2–4 veces',
        '1'      => '1 vez',
        'ninguna'=> 'Ninguna',
    ],
    // Q23
    'q23' => [
        'si_regularmente'   => 'Sí, regularmente (más de 10 veces)',
        'si_ocasionalmente' => 'Sí, ocasionalmente (5–10 veces)',
        'si_pocas'          => 'Sí, pocas veces (1–4 veces)',
        'no_gustaria'       => 'No, pero me gustaría aprender',
        'no_intimida'       => 'No, y me intimida',
    ],
    // Q24
    'q24' => ['si' => 'Sí', 'no' => 'No'],
    // Q25
    'q25' => [
        'varios_viajes'   => 'Sí, varios viajes',
        'un_viaje'        => 'Sí, un viaje',
        'misiones_locales'=> 'No, pero he participado en misiones locales',
        'primera_vez'     => 'No, esta sería mi primera experiencia misionera formal',
    ],
    // Q26 (checkbox)
    'q26' => [
        'evangelismo'    => 'Evangelismo',
        'ensenanza'      => 'Enseñanza',
        'servicio'       => 'Servicio',
        'liderazgo'      => 'Liderazgo',
        'misericordia'   => 'Misericordia / compasión',
        'exhortacion'    => 'Exhortación',
        'discernimiento' => 'Discernimiento',
        'fe'             => 'Fe',
        'administracion' => 'Administración',
        'ayuda'          => 'Ayuda',
        'hospitalidad'   => 'Hospitalidad',
    ],
    // Q27
    'q27' => [
        'vivido_otra_ciudad' => 'Sí, he vivido en otra(s) ciudad(es) por más de 6 meses',
        'viajado_ciudades'   => 'Sí, he viajado a diferentes ciudades/regiones',
        'poco'               => 'Muy poco, principalmente he estado en mi ciudad',
        'nunca'              => 'No, nunca he salido de mi ciudad',
    ],
    // Q28
    'q28' => [
        'muy_emocionado' => 'Muy emocionado/a — es parte de mi llamado',
        'positivo'       => 'Positivo/a pero con algo de nerviosismo',
        'neutral'        => 'Neutral — lo haría si es necesario',
        'preocupado'     => 'Preocupado/a — preferiría servir en contextos similares al mío',
    ],
    // Q29
    'q29' => [
        'frecuentemente'  => 'Sí, frecuentemente',
        'ocasionalmente'  => 'Sí, ocasionalmente',
        'poco_contacto'   => 'Poco contacto',
        'casi_ninguno'    => 'No, casi ningún contacto',
    ],
    // Q30
    'q30' => [
        'muy_comodo'    => 'Muy cómodo/a — ya he servido en estos contextos',
        'comodo'        => 'Cómodo/a — aunque no tengo mucha experiencia',
        'poco_incomodo' => 'Un poco incómodo/a pero dispuesto/a a aprender',
        'muy_incomodo'  => 'Muy incómodo/a — me costaría adaptarme',
    ],
    // Q32
    'q32' => [
        'si_sin_problema' => 'Sí, sin ningún problema',
        'si_con_ansiedad' => 'Sí, aunque me genera algo de ansiedad',
        'depende'         => 'Depende de las condiciones específicas',
        'prefiere_urbano' => 'Preferiría servir en zonas urbanas',
    ],
    // Q33 (checkbox)
    'q33' => [
        'propio_negocio'   => 'He tenido mi propio negocio / emprendimiento',
        'negocio_familiar' => 'He trabajado en negocios familiares',
        'freelance'        => 'He hecho trabajos independientes (freelance)',
        'cursos'           => 'He tomado cursos de emprendimiento',
        'ninguna'          => 'Ninguna experiencia formal',
    ],
    // Q34 (checkbox)
    'q34' => [
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
    ],
    // Q35
    'q35' => [
        'estable' => 'Estable — tengo ingresos regulares y ahorros',
        'regular' => 'Regular — cubro mis necesidades básicas',
        'limitada'=> 'Limitada — dependo de apoyo familiar',
        'dificil' => 'Difícil — enfrento desafíos económicos constantes',
    ],
    // Q36
    'q36' => [
        'muy_importante'  => 'Muy importante — es parte esencial de mi visión misionera',
        'importante'      => 'Importante — me gustaría tener esa habilidad',
        'algo_importante' => 'Algo importante — aunque no es mi prioridad',
        'poco_importante' => 'Poco importante — prefiero depender de soporte externo',
    ],
    // Q37
    'q37' => [
        'si_excelente'  => 'Sí, me parece excelente estrategia',
        'si_no_pensado' => 'Sí, aunque no había pensado en ello',
        'no_seguro'     => 'No estoy seguro/a cómo funcionaría',
        'no_separar'    => 'No, prefiero separar el ministerio del trabajo',
    ],
    // Q38
    'q38' => [
        'panaderia'     => 'Panadería y repostería',
        'textiles'      => 'Textiles y sublimación',
        'mantenimiento' => 'Mantenimiento y reparaciones',
        'limpieza'      => 'Servicios de limpieza y estética',
        'artesania'     => 'Artesanía y oficios tradicionales',
        'otra'          => 'Otra',
    ],
    // Q39
    'q39' => [
        'excelente'  => 'Excelente — me siento emocionalmente saludable',
        'buena'      => 'Buena — generalmente estoy bien',
        'regular'    => 'Regular — tengo altibajos pero los manejo',
        'desafiante' => 'Desafiante — estoy trabajando en áreas difíciles',
    ],
    // Q40
    'q40' => [
        'si_actualmente' => 'Sí, actualmente',
        'si_pasado'      => 'Sí, en el pasado',
        'no_abierto'     => 'No, pero estaría abierto/a si fuera necesario',
        'no_prefiero'    => 'No, y preferiría no hacerlo',
    ],
    // Q41
    'q41' => [
        'muy_bien'   => 'Muy bien — tengo estrategias saludables',
        'bien'       => 'Bien — aunque a veces me cuesta',
        'regular'    => 'Regular — necesito mejorar en esta área',
        'dificultad' => 'Con dificultad — el estrés me afecta significativamente',
    ],
    // Q42 (checkbox)
    'q42' => [
        'perdida_ser_querido'  => 'Pérdida de un ser querido',
        'ruptura_relacion'     => 'Ruptura de relación significativa',
        'problemas_familiares' => 'Problemas familiares graves',
        'problemas_salud'      => 'Problemas de salud serios',
        'desempleo'            => 'Desempleo prolongado',
        'crisis_financiera'    => 'Crisis financiera',
        'crisis_fe'            => 'Crisis de fe',
        'ninguna'              => 'Ninguna de las anteriores',
    ],
    // Q45
    'q45' => [
        'si_totalmente'     => 'Sí, totalmente comprometido/a',
        'si_preocupaciones' => 'Sí, aunque tengo algunas preocupaciones logísticas',
        'probablemente'     => 'Probablemente, pero depende de algunas circunstancias',
        'no_seguro'         => 'No estoy seguro/a',
    ],
    // Q46
    'q46' => [
        'apoyo_total'      => 'Apoyo total y entusiasta',
        'apoyo_reservas'   => 'Apoyo, aunque con algunas preocupaciones',
        'apoyo_neutral'    => 'Apoyo neutral — no se oponen pero tampoco están muy convencidos',
        'poca_comprension' => 'Poca o ninguna comprensión de mi decisión',
        'oposicion'        => 'Oposición activa',
    ],
    // Q47
    'q47' => [
        'no'            => 'No',
        'si_soluciones' => 'Sí, pero hemos encontrado soluciones',
        'si_trabajando' => 'Sí, y estoy trabajando en resolverlas',
        'si_no_sabe'    => 'Sí, y no sé cómo manejarlas',
    ],
    // Q49 (checkbox)
    'q49' => [
        'perseverar' => 'Perseverar y buscar apoyo del equipo formativo',
        'orar'       => 'Orar y confiar en que Dios me dará fuerzas',
        'comunicar'  => 'Comunicar inmediatamente mis dificultades',
        'evaluar'    => 'Evaluar si debo continuar',
        'no_seguro'  => 'No estoy seguro/a',
    ],
    // Q50
    'q50' => [
        'misionero_tc'      => 'Servir como misionero/a a tiempo completo',
        'plantar_iglesia'   => 'Plantar o revitalizar una iglesia',
        'regresar_iglesia'  => 'Regresar a mi iglesia local con nuevas capacidades',
        'estudiar_teologia' => 'Continuar estudiando teología',
        'emprendimiento'    => 'Iniciar un emprendimiento misionero',
        'no_claro'          => 'Aún no lo sé con claridad',
        'otro'              => 'Otro',
    ],
];

// Helper: label desde mapa de opciones (radio)
$label = function(string $key, string $val) use ($opciones): string {
    if (empty($val)) return '<em class="sin-resp">Sin respuesta</em>';
    return htmlspecialchars($opciones[$key][$val] ?? $val);
};

// Helper: etiquetas de checkboxes
$labels = function(string $key, mixed $vals) use ($opciones): string {
    if (empty($vals)) return '<em class="sin-resp">Sin respuesta</em>';
    $arr = (array)$vals;
    $out = [];
    foreach ($arr as $v) {
        $out[] = '<span class="tag-resp">' . htmlspecialchars($opciones[$key][$v] ?? $v) . '</span>';
    }
    return implode(' ', $out);
};

// Helper: texto libre
$txt = function(string $val): string {
    if (empty(trim($val))) return '<em class="sin-resp">Sin respuesta</em>';
    return '<span class="resp-texto">' . nl2br(htmlspecialchars($val)) . '</span>';
};

// Helper: escala 1–10
$escala = function(string $val): string {
    if ($val === '') return '<em class="sin-resp">Sin respuesta</em>';
    $n = (int)$val;
    return "<span class=\"escala-badge escala-badge--{$n}\">{$n} / 10</span>";
};

// Ministerios Q22
$ministerios_labels = [
    'evangelismo' => 'Evangelismo',
    'discipulado' => 'Discipulado',
    'ensenanza'   => 'Enseñanza bíblica',
    'predicacion' => 'Predicación',
    'alabanza'    => 'Alabanza / música',
    'ninos'       => 'Trabajo con niños',
    'jovenes'     => 'Trabajo con jóvenes',
    'servicio'    => 'Servicio social',
    'liderazgo'   => 'Liderazgo de grupos',
];
$niveles_labels = ['ninguna' => 'Ninguna', 'basica' => 'Básica', 'intermedia' => 'Intermedia', 'avanzada' => 'Avanzada'];
?>

<div class="admin-layout">
    <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>

    <main class="admin-main">

        <?php if (!empty($_SESSION['flash'])): ?>
        <div class="alerta alerta--<?= $_SESSION['flash']['tipo'] ?>">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); endif; ?>

        <!-- Header -->
        <div class="admin-header">
            <div style="display:flex; align-items:center; gap:1rem;">
                <a href="/admin/candidatos?ver=<?= $aspirante['id'] ?>" class="btn btn--outline btn--sm">
                    <i class="fas fa-arrow-left"></i> Volver al perfil
                </a>
                <div>
                    <h1><i class="fas fa-clipboard-list" style="color:var(--verde); margin-right:.4rem;"></i>
                        Test Vocacional</h1>
                    <p><?= htmlspecialchars($aspirante['nombres'] . ' ' . $aspirante['apellidos']) ?>
                        &nbsp;·&nbsp; Cédula <?= htmlspecialchars($aspirante['cedula'] ?? '—') ?>
                    </p>
                </div>
            </div>
            <?php if ($test): ?>
            <div style="text-align:right; font-size:.82rem; color:var(--gris);">
                <?php if ($test['completado']): ?>
                    <span class="badge-vt completado"><i class="fas fa-check-circle"></i> Completado</span>
                    <?php if ($test['fecha_cierre']): ?>
                    <div style="margin-top:.3rem;">Enviado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($test['fecha_cierre'])) ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="badge-vt en-progreso"><i class="fas fa-clock"></i> En progreso</span>
                    <?php if ($test['fecha_inicio']): ?>
                    <div style="margin-top:.3rem;">Iniciado el <?= date('d/m/Y', strtotime($test['fecha_inicio'])) ?></div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!$test): ?>
        <!-- Sin test -->
        <div class="admin-panel" style="text-align:center; padding:3rem;">
            <i class="fas fa-clipboard" style="font-size:3rem; color:#cbd5e1; margin-bottom:1rem;"></i>
            <h3 style="color:var(--gris-dark);">El candidato no ha iniciado el test vocacional</h3>
            <p style="color:var(--gris); margin-top:.5rem;">Cuando el candidato comience a responder el cuestionario, las respuestas aparecerán aquí.</p>
        </div>

        <?php else: ?>

        <!-- ===================================================
             PARTE I: LLAMADO Y VOCACIÓN MISIONERA (Q1–Q6)
        ==================================================== -->
        <div class="vt-parte" id="vtp-1">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-star"></i> Parte I</span>
                <div>
                    <h2>Llamado y Vocación Misionera</h2>
                    <p>Preguntas 1 – 6</p>
                </div>
            </div>

            <?php /* Q1 */ ?>
            <div class="vt-pregunta">
                <div class="vt-pregunta__num">1</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Cuándo comenzó a sentir el llamado al ministerio misionero?</p>
                    <div class="vt-resp"><?= $label('q1', $rv('q1')) ?></div>
                </div>
            </div>

            <?php /* Q2 */ ?>
            <div class="vt-pregunta">
                <div class="vt-pregunta__num">2</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Cómo describe su llamado misionero?</p>
                    <div class="vt-resp">
                        <?= $label('q2', $rv('q2')) ?>
                        <?php if ($rv('q2') === 'otro' && $rv('q2_otro')): ?>
                            <div class="vt-extra"><?= $txt($rv('q2_otro')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php /* Q3 */ ?>
            <div class="vt-pregunta">
                <div class="vt-pregunta__num">3</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Qué eventos o experiencias confirmaron su llamado misionero?</p>
                    <div class="vt-resp"><?= $txt($rv('q3')) ?></div>
                </div>
            </div>

            <?php /* Q4 */ ?>
            <div class="vt-pregunta">
                <div class="vt-pregunta__num">4</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Su pastor y/o líderes espirituales confirman su llamado?</p>
                    <div class="vt-resp"><?= $label('q4', $rv('q4')) ?></div>
                </div>
            </div>

            <?php /* Q5 */ ?>
            <div class="vt-pregunta">
                <div class="vt-pregunta__num">5</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Seguridad en el llamado misionero <em>(escala 1–10)</em></p>
                    <div class="vt-resp"><?= $escala($rv('q5')) ?></div>
                </div>
            </div>

            <?php /* Q6 */ ?>
            <div class="vt-pregunta">
                <div class="vt-pregunta__num">6</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Aspectos del ministerio misionero que más le atraen <em>(hasta 3)</em></p>
                    <div class="vt-resp">
                        <?= $labels('q6', $respuestas['q6'] ?? []) ?>
                        <?php if ($rv('q6_otro')): ?>
                            <div class="vt-extra">Otro: <?= htmlspecialchars($rv('q6_otro')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE II: FORMACIÓN ESPIRITUAL (Q7–Q13)
        ==================================================== -->
        <div class="vt-parte" id="vtp-2">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-bible"></i> Parte II</span>
                <div>
                    <h2>Formación Espiritual</h2>
                    <p>Preguntas 7 – 13</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">7</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Frecuencia de devocionales personales</p>
                    <div class="vt-resp"><?= $label('q7', $rv('q7')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">8</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Prácticas espirituales que cultiva regularmente</p>
                    <div class="vt-resp">
                        <?= $labels('q8', $respuestas['q8'] ?? []) ?>
                        <?php if ($rv('q8_otras')): ?>
                            <div class="vt-extra">Otras: <?= htmlspecialchars($rv('q8_otras')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">9</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Participa en un grupo pequeño, célula o discipulado?</p>
                    <div class="vt-resp"><?= $label('q9', $rv('q9')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">10</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Está siendo actualmente discipulado por alguien?</p>
                    <div class="vt-resp">
                        <?= $label('q10', $rv('q10')) ?>
                        <?php if ($rv('q10') === 'si' && $rv('q10_quien')): ?>
                            <div class="vt-extra">Por quién: <?= htmlspecialchars($rv('q10_quien')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">11</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Está discipulando actualmente a alguien?</p>
                    <div class="vt-resp">
                        <?= $label('q11', $rv('q11')) ?>
                        <?php if ($rv('q11') === 'si' && $rv('q11_cuantos')): ?>
                            <div class="vt-extra">Cantidad: <?= htmlspecialchars($rv('q11_cuantos')) ?> persona(s)</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">12</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Descripción de su relación actual con Dios</p>
                    <div class="vt-resp"><?= $txt($rv('q12')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">13</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Mayor desafío en su vida espiritual actualmente</p>
                    <div class="vt-resp"><?= $txt($rv('q13')) ?></div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE III: VIDA EN COMUNIDAD Y RELACIONES (Q14–Q20)
        ==================================================== -->
        <div class="vt-parte" id="vtp-3">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-users"></i> Parte III</span>
                <div>
                    <h2>Vida en Comunidad y Relaciones</h2>
                    <p>Preguntas 14 – 20</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">14</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Habilidad para trabajar en equipo</p>
                    <div class="vt-resp"><?= $label('q14', $rv('q14')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">15</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Ha vivido alguna vez en comunidad?</p>
                    <div class="vt-resp">
                        <?= $label('q15', $rv('q15')) ?>
                        <?php if ($rv('q15') === 'si' && $rv('q15_tiempo')): ?>
                            <div class="vt-extra">Tiempo: <?= htmlspecialchars($rv('q15_tiempo')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">16</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Manejo de conflictos interpersonales</p>
                    <div class="vt-resp"><?= $label('q16', $rv('q16')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">17</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Recepción de corrección o crítica constructiva</p>
                    <div class="vt-resp"><?= $label('q17', $rv('q17')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">18</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Flexibilidad ante cambios inesperados <em>(escala 1–10)</em></p>
                    <div class="vt-resp"><?= $escala($rv('q18')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">19</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Cómo describen sus amigos cercanos su personalidad?</p>
                    <div class="vt-resp"><?= $txt($rv('q19')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">20</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Tiene algún conflicto pendiente en su iglesia o familia?</p>
                    <div class="vt-resp"><?= $label('q20', $rv('q20')) ?></div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE IV: PREPARACIÓN PRÁCTICA Y MINISTERIAL (Q21–Q26)
        ==================================================== -->
        <div class="vt-parte" id="vtp-4">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-hands-helping"></i> Parte IV</span>
                <div>
                    <h2>Preparación Práctica y Ministerial</h2>
                    <p>Preguntas 21 – 26</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">21</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Veces que compartió el evangelio en el último mes</p>
                    <div class="vt-resp"><?= $label('q21', $rv('q21')) ?></div>
                </div>
            </div>

            <?php /* Q22: Matrix de ministerios */ ?>
            <div class="vt-pregunta">
                <div class="vt-pregunta__num">22</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Experiencia en diferentes ministerios</p>
                    <?php $q22 = $respuestas['q22'] ?? []; ?>
                    <?php if (empty($q22)): ?>
                        <div class="vt-resp"><em class="sin-resp">Sin respuesta</em></div>
                    <?php else: ?>
                    <div class="vt-matrix-wrap">
                        <table class="vt-matrix">
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
                                <?php foreach ($ministerios_labels as $key => $nombre): ?>
                                <?php $nivel = $q22[$key] ?? ''; ?>
                                <tr>
                                    <td><?= $nombre ?></td>
                                    <?php foreach (['ninguna','basica','intermedia','avanzada'] as $lvl): ?>
                                    <td class="vt-matrix__cell">
                                        <?php if ($nivel === $lvl): ?>
                                            <span class="vt-matrix__marca vt-matrix__marca--<?= $lvl ?>">
                                                <i class="fas fa-check"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="vt-matrix__vacio">·</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">23</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Ha predicado alguna vez?</p>
                    <div class="vt-resp"><?= $label('q23', $rv('q23')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">24</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Ha dirigido algún ministerio o proyecto en su iglesia?</p>
                    <div class="vt-resp">
                        <?= $label('q24', $rv('q24')) ?>
                        <?php if ($rv('q24') === 'si' && $rv('q24_desc')): ?>
                            <div class="vt-extra"><?= $txt($rv('q24_desc')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">25</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Ha participado en algún viaje misionero?</p>
                    <div class="vt-resp"><?= $label('q25', $rv('q25')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">26</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Dones espirituales principales <em>(hasta 3)</em></p>
                    <div class="vt-resp">
                        <?= $labels('q26', $respuestas['q26'] ?? []) ?>
                        <?php if ($rv('q26_otro')): ?>
                            <div class="vt-extra">Otro: <?= htmlspecialchars($rv('q26_otro')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE V: ADAPTABILIDAD E INTERCULTURALIDAD (Q27–Q32)
        ==================================================== -->
        <div class="vt-parte" id="vtp-5">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-globe-americas"></i> Parte V</span>
                <div>
                    <h2>Adaptabilidad y Contexto Intercultural</h2>
                    <p>Preguntas 27 – 32</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">27</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Ha vivido o viajado fuera de su ciudad natal?</p>
                    <div class="vt-resp"><?= $label('q27', $rv('q27')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">28</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Disposición para servir en contextos culturalmente diferentes</p>
                    <div class="vt-resp"><?= $label('q28', $rv('q28')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">29</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Contacto con personas de diferentes niveles socioeconómicos</p>
                    <div class="vt-resp"><?= $label('q29', $rv('q29')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">30</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Comodidad sirviendo en comunidades vulnerables o de escasos recursos</p>
                    <div class="vt-resp"><?= $label('q30', $rv('q30')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">31</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Adaptabilidad a nuevos contextos <em>(escala 1–10)</em></p>
                    <div class="vt-resp"><?= $escala($rv('q31')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">32</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Estaría dispuesto a servir en zonas rurales o de difícil acceso?</p>
                    <div class="vt-resp"><?= $label('q32', $rv('q32')) ?></div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE VI: SOSTENIBILIDAD Y EMPRENDIMIENTO (Q33–Q38)
        ==================================================== -->
        <div class="vt-parte" id="vtp-6">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-seedling"></i> Parte VI</span>
                <div>
                    <h2>Sostenibilidad y Emprendimiento</h2>
                    <p>Preguntas 33 – 38</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">33</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Experiencia en actividades productivas o emprendimientos</p>
                    <div class="vt-resp"><?= $labels('q33', $respuestas['q33'] ?? []) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">34</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Habilidades prácticas / oficios que posee</p>
                    <div class="vt-resp">
                        <?= $labels('q34', $respuestas['q34'] ?? []) ?>
                        <?php if ($rv('q34_otra')): ?>
                            <div class="vt-extra">Otra: <?= htmlspecialchars($rv('q34_otra')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">35</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Situación financiera personal</p>
                    <div class="vt-resp"><?= $label('q35', $rv('q35')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">36</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Importancia de aprender a autosostenerse económicamente en el ministerio</p>
                    <div class="vt-resp"><?= $label('q36', $rv('q36')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">37</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Usaría un emprendimiento como herramienta de evangelismo?</p>
                    <div class="vt-resp"><?= $label('q37', $rv('q37')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">38</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Área de emprendimiento del programa de mayor interés</p>
                    <div class="vt-resp">
                        <?= $label('q38', $rv('q38')) ?>
                        <?php if ($rv('q38') === 'otra' && $rv('q38_otra')): ?>
                            <div class="vt-extra"><?= htmlspecialchars($rv('q38_otra')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE VII: SALUD EMOCIONAL Y RESILIENCIA (Q39–Q44)
        ==================================================== -->
        <div class="vt-parte" id="vtp-7">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-heart"></i> Parte VII</span>
                <div>
                    <h2>Salud Emocional y Resiliencia</h2>
                    <p>Preguntas 39 – 44</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">39</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Salud emocional actual</p>
                    <div class="vt-resp"><?= $label('q39', $rv('q39')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">40</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Ha recibido consejería o acompañamiento psicológico?</p>
                    <div class="vt-resp"><?= $label('q40', $rv('q40')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">41</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Manejo del estrés y la presión</p>
                    <div class="vt-resp"><?= $label('q41', $rv('q41')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">42</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Situaciones significativas experimentadas en el último año</p>
                    <div class="vt-resp"><?= $labels('q42', $respuestas['q42'] ?? []) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">43</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Nivel de resiliencia ante las dificultades <em>(escala 1–10)</em></p>
                    <div class="vt-resp"><?= $escala($rv('q43')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">44</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Qué hace cuando enfrenta dificultades o fracasos?</p>
                    <div class="vt-resp"><?= $txt($rv('q44')) ?></div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE VIII: COMPROMISO Y EXPECTATIVAS (Q45–Q50)
        ==================================================== -->
        <div class="vt-parte" id="vtp-8">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-handshake"></i> Parte VIII</span>
                <div>
                    <h2>Compromiso y Expectativas</h2>
                    <p>Preguntas 45 – 50</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">45</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Disposición para comprometerse los 8 meses completos del primer ciclo</p>
                    <div class="vt-resp"><?= $label('q45', $rv('q45')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">46</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Apoyo familiar para participar en el programa</p>
                    <div class="vt-resp"><?= $label('q46', $rv('q46')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">47</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Tiene responsabilidades familiares que puedan dificultar su participación?</p>
                    <div class="vt-resp"><?= $label('q47', $rv('q47')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">48</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Tres expectativas principales del programa</p>
                    <div class="vt-resp">
                        <?php
                        $exp = array_filter([
                            $rv('q48_1'),
                            $rv('q48_2'),
                            $rv('q48_3'),
                        ]);
                        if (empty($exp)):
                        ?>
                            <em class="sin-resp">Sin respuesta</em>
                        <?php else: ?>
                            <ol class="vt-lista-num">
                                <?php foreach ($exp as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ol>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">49</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">¿Qué haría ante dificultades significativas durante el programa?</p>
                    <div class="vt-resp"><?= $labels('q49', $respuestas['q49'] ?? []) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">50</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Plan después de completar el programa</p>
                    <div class="vt-resp">
                        <?= $label('q50', $rv('q50')) ?>
                        <?php if ($rv('q50') === 'otro' && $rv('q50_otro')): ?>
                            <div class="vt-extra"><?= htmlspecialchars($rv('q50_otro')) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE IX: PREGUNTAS SITUACIONALES (Q51–Q55)
        ==================================================== -->
        <div class="vt-parte" id="vtp-9">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-lightbulb"></i> Parte IX</span>
                <div>
                    <h2>Preguntas Situacionales</h2>
                    <p>Preguntas 51 – 55</p>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">51</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label"><strong>Escenario 1:</strong> Compañero/a de habitación con hábitos incómodos. ¿Cómo lo manejaría?</p>
                    <div class="vt-resp"><?= $txt($rv('q51')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">52</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label"><strong>Escenario 2:</strong> Comunidad que rechaza el evangelio. ¿Cuál sería su respuesta?</p>
                    <div class="vt-resp"><?= $txt($rv('q52')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">53</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label"><strong>Escenario 3:</strong> Emprendimiento que no genera ingresos esperados. ¿Qué haría?</p>
                    <div class="vt-resp"><?= $txt($rv('q53')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">54</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label"><strong>Escenario 4:</strong> Compañero/a que viola normas del programa. ¿Cómo actuaría?</p>
                    <div class="vt-resp"><?= $txt($rv('q54')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">55</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label"><strong>Escenario 5:</strong> Formación académica menos sólida que otros participantes. ¿Cómo lo abordaría?</p>
                    <div class="vt-resp"><?= $txt($rv('q55')) ?></div>
                </div>
            </div>
        </div>

        <!-- ===================================================
             PARTE X: COMPROMISO PERSONAL (Q56–Q60)
        ==================================================== -->
        <div class="vt-parte" id="vtp-10">
            <div class="vt-parte__header">
                <span class="vt-parte__num"><i class="fas fa-pen-nib"></i> Parte X</span>
                <div>
                    <h2>Compromiso Personal</h2>
                    <p>Preguntas 56 – 60</p>
                </div>
            </div>

            <div class="vt-pregunta vt-pregunta--destacada">
                <div class="vt-pregunta__num">56</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Declaración personal de compromiso con el programa <em>(mínimo 100 palabras)</em></p>
                    <div class="vt-resp">
                        <?php
                        $decl = $rv('q56');
                        if ($decl): ?>
                            <div class="vt-declaracion"><?= nl2br(htmlspecialchars($decl)) ?></div>
                            <?php
                            $palabras = str_word_count(strip_tags($decl));
                            ?>
                            <div class="vt-contador-palabras">
                                <i class="fas fa-file-word"></i>
                                <?= $palabras ?> palabra<?= $palabras !== 1 ? 's' : '' ?>
                                <?php if ($palabras < 100): ?>
                                    <span style="color:#ef4444; margin-left:.5rem;">(menos del mínimo requerido)</span>
                                <?php else: ?>
                                    <span style="color:var(--verde); margin-left:.5rem;"><i class="fas fa-check"></i> Cumple el mínimo</span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <em class="sin-resp">Sin respuesta</em>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">57</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Sacrificios dispuesto/a a hacer para completar el programa</p>
                    <div class="vt-resp"><?= $txt($rv('q57')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">58</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Visión misionera personal en una frase</p>
                    <div class="vt-resp"><?= $txt($rv('q58')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">59</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">En 10 años, ¿dónde se ve sirviendo y qué impacto espera haber generado?</p>
                    <div class="vt-resp"><?= $txt($rv('q59')) ?></div>
                </div>
            </div>

            <div class="vt-pregunta">
                <div class="vt-pregunta__num">60</div>
                <div class="vt-pregunta__cuerpo">
                    <p class="vt-pregunta__label">Información adicional que quiera compartir</p>
                    <div class="vt-resp">
                        <?php if ($rv('q60')): ?>
                            <?= $txt($rv('q60')) ?>
                        <?php else: ?>
                            <em class="sin-resp">No agregó información adicional</em>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón volver al final -->
        <div style="margin-top:2rem; text-align:center;">
            <a href="/admin/candidatos?ver=<?= $aspirante['id'] ?>" class="btn btn--outline">
                <i class="fas fa-arrow-left"></i> Volver al perfil del candidato
            </a>
        </div>

        <?php endif; // fin $test ?>

    </main>
</div>

<style>
/* ============================================================
   VER TEST VOCACIONAL — ESTILOS
============================================================ */

/* Parte contenedor */
.vt-parte {
    background: var(--blanco);
    border-radius: var(--radio-lg);
    box-shadow: var(--sombra);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

/* Header de parte */
.vt-parte__header {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: linear-gradient(135deg, var(--verde-dark), var(--verde));
    color: white;
    padding: 1.25rem 1.75rem;
}
.vt-parte__num {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(255,255,255,.2);
    border-radius: 999px;
    padding: .3rem .9rem;
    font-size: .78rem;
    font-weight: 800;
    white-space: nowrap;
    flex-shrink: 0;
}
.vt-parte__header h2 {
    font-size: 1.1rem;
    font-weight: 800;
    margin: 0;
    color: white;
}
.vt-parte__header p {
    font-size: .8rem;
    opacity: .8;
    margin: .15rem 0 0;
}

/* Pregunta */
.vt-pregunta {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    padding: 1rem 1.75rem;
    border-bottom: 1px solid #f1f5f9;
}
.vt-pregunta:last-child { border-bottom: none; }
.vt-pregunta--destacada { background: #fffbeb; border-left: 4px solid var(--dorado); }

.vt-pregunta__num {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--verde);
    color: white;
    font-size: .75rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: .1rem;
}
.vt-pregunta__cuerpo { flex: 1; min-width: 0; }
.vt-pregunta__label {
    font-size: .85rem;
    font-weight: 700;
    color: var(--gris-dark);
    margin: 0 0 .5rem;
    line-height: 1.5;
}
.vt-pregunta__label em { font-weight: 400; color: var(--gris); font-style: normal; }
.vt-pregunta__label strong { color: var(--verde-dark); }

/* Respuesta contenedor */
.vt-resp {
    font-size: .875rem;
    color: #1e293b;
    line-height: 1.6;
}
.vt-extra {
    font-size: .8rem;
    color: var(--gris);
    margin-top: .4rem;
    padding: .35rem .6rem;
    background: #f8fafc;
    border-left: 3px solid var(--dorado);
    border-radius: 0 .25rem .25rem 0;
}
.sin-resp {
    color: #94a3b8;
    font-style: italic;
    font-size: .85rem;
}
.resp-texto {
    display: block;
    background: #f8fafc;
    border-left: 3px solid var(--verde-light);
    padding: .6rem .85rem;
    border-radius: 0 .35rem .35rem 0;
    color: var(--gris-dark);
    font-style: italic;
    line-height: 1.7;
}

/* Tags (checkboxes) */
.tag-resp {
    display: inline-flex;
    align-items: center;
    background: #f0fdf4;
    border: 1px solid var(--verde-light);
    color: var(--verde-dark);
    border-radius: 999px;
    padding: .2rem .7rem;
    font-size: .78rem;
    font-weight: 600;
    margin: .2rem .2rem 0 0;
}

/* Escala badge */
.escala-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 50%;
    font-size: 1.1rem;
    font-weight: 900;
    color: white;
    background: var(--verde);
}
.escala-badge--1, .escala-badge--2 { background: #ef4444; }
.escala-badge--3, .escala-badge--4 { background: #f97316; }
.escala-badge--5, .escala-badge--6 { background: #eab308; color: #1e293b; }
.escala-badge--7, .escala-badge--8 { background: #84cc16; color: #1e293b; }
.escala-badge--9, .escala-badge--10 { background: var(--verde); }

/* Matrix Q22 */
.vt-matrix-wrap { overflow-x: auto; margin-top: .5rem; }
.vt-matrix {
    width: 100%;
    border-collapse: collapse;
    font-size: .82rem;
}
.vt-matrix th, .vt-matrix td {
    padding: .5rem .75rem;
    text-align: center;
    border: 1px solid #e5e7eb;
}
.vt-matrix th:first-child, .vt-matrix td:first-child { text-align: left; font-weight: 600; color: var(--gris-dark); }
.vt-matrix th { background: #f8fafc; font-weight: 700; font-size: .75rem; color: var(--gris); text-transform: uppercase; letter-spacing: .03em; }
.vt-matrix__cell { }
.vt-matrix__marca {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: .7rem;
    color: white;
    background: #94a3b8;
}
.vt-matrix__marca--basica { background: #84cc16; color: #1e293b; }
.vt-matrix__marca--intermedia { background: var(--dorado); color: #1e293b; }
.vt-matrix__marca--avanzada { background: var(--verde); }
.vt-matrix__vacio { color: #e5e7eb; }
.vt-matrix tr:hover { background: #f8fafc; }

/* Declaración Q56 */
.vt-declaracion {
    background: #f0fdf4;
    border: 1px solid var(--verde-light);
    border-radius: var(--radio);
    padding: 1rem 1.25rem;
    font-size: .875rem;
    color: var(--gris-dark);
    line-height: 1.8;
    font-style: italic;
}
.vt-contador-palabras {
    margin-top: .5rem;
    font-size: .78rem;
    color: var(--gris);
    display: flex;
    align-items: center;
    gap: .35rem;
}

/* Lista numerada (Q48) */
.vt-lista-num {
    margin: 0;
    padding-left: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.vt-lista-num li { font-size: .875rem; color: var(--gris-dark); }

/* Badge estado test en header */
.badge-vt {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .35rem .9rem;
    border-radius: 999px;
    font-size: .8rem;
    font-weight: 700;
}
.badge-vt.completado { background: #dcfce7; color: #15803d; }
.badge-vt.en-progreso { background: #fef9c3; color: #854d0e; }
</style>
