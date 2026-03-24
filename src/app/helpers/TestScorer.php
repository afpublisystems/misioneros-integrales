<?php
/**
 * TestScorer — Calcula el perfil vocacional orientacional del candidato.
 *
 * IMPORTANTE: Este puntaje es REFERENCIAL, no determinante para la admisión.
 * El equipo evaluador toma la decisión final con base en el perfil completo.
 *
 * 8 dimensiones scoreable (partes 1-8). Partes 9-10 son textareas abiertas.
 * Cada dimensión devuelve un valor de 0 a 100.
 */
class TestScorer {

    /**
     * Calcula el perfil completo a partir de las respuestas decodificadas.
     * @param array $r  Array de respuestas (ya decodificado del JSON)
     * @return array [
     *   'dimensiones' => [ ['clave'=>, 'nombre'=>, 'icono'=>, 'score'=>0-100], ... ],
     *   'puntaje_total' => float (promedio de dimensiones scoreable),
     *   'fortalezas'   => [ top 3 nombres ],
     *   'por_mejorar'  => [ bottom 2 nombres ],
     * ]
     */
    public static function calcular(array $r): array {
        $dims = [
            self::dim1_llamado($r),
            self::dim2_espiritual($r),
            self::dim3_comunidad($r),
            self::dim4_ministerial($r),
            self::dim5_adaptabilidad($r),
            self::dim6_sostenibilidad($r),
            self::dim7_salud_emocional($r),
            self::dim8_compromiso($r),
        ];

        $scores = array_column($dims, 'score');
        $total  = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;

        // Ordenar para fortalezas y áreas de crecimiento
        $ordenadas = $dims;
        usort($ordenadas, fn($a, $b) => $b['score'] <=> $a['score']);

        $fortalezas  = array_slice(array_column($ordenadas, 'nombre'), 0, 3);
        $por_mejorar = array_slice(array_column(array_reverse($ordenadas), 'nombre'), 0, 2);

        return [
            'dimensiones'   => $dims,
            'puntaje_total' => $total,
            'fortalezas'    => $fortalezas,
            'por_mejorar'   => $por_mejorar,
        ];
    }

    // ─────────────────────────────────────────────────
    // DIM 1 · Llamado y Vocación Misionera (Q1, Q4, Q5)
    // max raw = 4 + 4 + 10 = 18
    // ─────────────────────────────────────────────────
    private static function dim1_llamado(array $r): array {
        $mapa_q1 = ['menos_6m'=>1,'6m_1a'=>2,'1a_3a'=>3,'mas_3a'=>4];
        $mapa_q4 = ['no_seguros'=>0,'no_conversado'=>1,'aun_evaluando'=>2,'si_con_reservas'=>3,'si_completamente'=>4];

        $pts  = ($mapa_q1[$r['q1'] ?? ''] ?? 0)
              + ($mapa_q4[$r['q4'] ?? ''] ?? 0)
              + (int)($r['q5'] ?? 0);

        return self::dim('llamado', 'Llamado y Vocación', 'fa-star', $pts, 18);
    }

    // ─────────────────────────────────────────────────
    // DIM 2 · Formación Espiritual (Q7, Q8, Q9, Q10, Q11)
    // max raw = 5 + 5 + 3 + 2 + 2 = 17
    // ─────────────────────────────────────────────────
    private static function dim2_espiritual(array $r): array {
        $mapa_q7 = ['raramente'=>0,'ocasionalmente'=>1,'1_semana'=>2,'2_3_semana'=>3,'4_6_semana'=>4,'diario'=>5];
        $mapa_q9 = ['nunca'=>0,'no_pero_antes'=>1,'si_irregularmente'=>2,'si_activamente'=>3];
        $mapa_q10= ['no_necesidad'=>0,'no_gustaria'=>1,'si'=>2];
        $mapa_q11= ['no_preparado'=>0,'no_gustaria'=>1,'si'=>2];

        $q8_count = is_array($r['q8'] ?? null) ? min(count($r['q8']), 5) : 0;

        $pts = ($mapa_q7[$r['q7'] ?? ''] ?? 0)
             + $q8_count
             + ($mapa_q9[$r['q9'] ?? ''] ?? 0)
             + ($mapa_q10[$r['q10'] ?? ''] ?? 0)
             + ($mapa_q11[$r['q11'] ?? ''] ?? 0);

        return self::dim('espiritual', 'Formación Espiritual', 'fa-bible', $pts, 17);
    }

    // ─────────────────────────────────────────────────
    // DIM 3 · Vida en Comunidad (Q14, Q15, Q16, Q17, Q18, Q20)
    // max raw = 3 + 3 + 3 + 3 + 10 + 2 = 24
    // ─────────────────────────────────────────────────
    private static function dim3_comunidad(array $r): array {
        $mapa_q14 = ['desafiante'=>0,'regular'=>1,'buena'=>2,'excelente'=>3];
        $mapa_q15 = ['no_preocupa'=>0,'no_dispuesto'=>2,'si'=>3];
        $mapa_q16 = ['cuesta_mucho'=>0,'prefiere_evitar'=>1,'intenta_resolver'=>2,'enfrenta_amor'=>3];
        $mapa_q17 = ['dificultad'=>0,'regular'=>1,'bien'=>2,'muy_bien'=>3];
        $mapa_q20 = ['si_necesita'=>0,'si_trabajando'=>1,'no'=>2];

        $pts = ($mapa_q14[$r['q14'] ?? ''] ?? 0)
             + ($mapa_q15[$r['q15'] ?? ''] ?? 0)
             + ($mapa_q16[$r['q16'] ?? ''] ?? 0)
             + ($mapa_q17[$r['q17'] ?? ''] ?? 0)
             + (int)($r['q18'] ?? 0)
             + ($mapa_q20[$r['q20'] ?? ''] ?? 0);

        return self::dim('comunidad', 'Vida en Comunidad', 'fa-users', $pts, 24);
    }

    // ─────────────────────────────────────────────────
    // DIM 4 · Preparación Ministerial (Q21, Q22, Q23, Q24, Q25)
    // max raw = 4 + 4 + 4 + 2 + 3 = 17
    // ─────────────────────────────────────────────────
    private static function dim4_ministerial(array $r): array {
        $mapa_q21 = ['ninguna'=>0,'1'=>1,'2_4'=>2,'5_10'=>3,'mas_10'=>4];
        $mapa_q23 = ['no_intimida'=>0,'no_gustaria'=>1,'si_pocas'=>2,'si_ocasionalmente'=>3,'si_regularmente'=>4];
        $mapa_q25 = ['primera_vez'=>0,'misiones_locales'=>1,'un_viaje'=>2,'varios_viajes'=>3];

        // Q22 matrix: promedio de 9 ministerios (0-3 cada uno), normalizar a 0-4
        $q22 = is_array($r['q22'] ?? null) ? $r['q22'] : [];
        $niv = ['ninguna'=>0,'basica'=>1,'intermedia'=>2,'avanzada'=>3];
        $ministerios = ['evangelismo','discipulado','ensenanza','predicacion','alabanza','ninos','jovenes','servicio','liderazgo'];
        $q22_sum = 0;
        foreach ($ministerios as $min) {
            $q22_sum += $niv[$q22[$min] ?? 'ninguna'] ?? 0;
        }
        $q22_pts = round($q22_sum / 27 * 4, 2); // normalizar a 0-4

        $q24_pts = ($r['q24'] ?? '') === 'si' ? 2 : 0;

        $pts = ($mapa_q21[$r['q21'] ?? ''] ?? 0)
             + $q22_pts
             + ($mapa_q23[$r['q23'] ?? ''] ?? 0)
             + $q24_pts
             + ($mapa_q25[$r['q25'] ?? ''] ?? 0);

        return self::dim('ministerial', 'Preparación Ministerial', 'fa-hands-helping', $pts, 17);
    }

    // ─────────────────────────────────────────────────
    // DIM 5 · Adaptabilidad e Interculturalidad (Q27-Q32)
    // max raw = 3 + 3 + 3 + 3 + 10 + 3 = 25
    // ─────────────────────────────────────────────────
    private static function dim5_adaptabilidad(array $r): array {
        $mapa_q27 = ['nunca'=>0,'poco'=>1,'viajado_ciudades'=>2,'vivido_otra_ciudad'=>3];
        $mapa_q28 = ['preocupado'=>0,'neutral'=>1,'positivo'=>2,'muy_emocionado'=>3];
        $mapa_q29 = ['casi_ninguno'=>0,'poco_contacto'=>1,'ocasionalmente'=>2,'frecuentemente'=>3];
        $mapa_q30 = ['muy_incomodo'=>0,'poco_incomodo'=>1,'comodo'=>2,'muy_comodo'=>3];
        $mapa_q32 = ['prefiere_urbano'=>0,'depende'=>1,'si_con_ansiedad'=>2,'si_sin_problema'=>3];

        $pts = ($mapa_q27[$r['q27'] ?? ''] ?? 0)
             + ($mapa_q28[$r['q28'] ?? ''] ?? 0)
             + ($mapa_q29[$r['q29'] ?? ''] ?? 0)
             + ($mapa_q30[$r['q30'] ?? ''] ?? 0)
             + (int)($r['q31'] ?? 0)
             + ($mapa_q32[$r['q32'] ?? ''] ?? 0);

        return self::dim('adaptabilidad', 'Adaptabilidad', 'fa-globe-americas', $pts, 25);
    }

    // ─────────────────────────────────────────────────
    // DIM 6 · Sostenibilidad y Emprendimiento (Q33, Q34, Q36, Q37)
    // max raw = 4 + 4 + 3 + 3 = 14
    // ─────────────────────────────────────────────────
    private static function dim6_sostenibilidad(array $r): array {
        $mapa_q36 = ['poco_importante'=>0,'algo_importante'=>1,'importante'=>2,'muy_importante'=>3];
        $mapa_q37 = ['no_separar'=>0,'no_seguro'=>1,'si_no_pensado'=>2,'si_excelente'=>3];

        // Q33: checkboxes — si tiene 'ninguna', 0; si no, contar (max 4)
        $q33 = is_array($r['q33'] ?? null) ? $r['q33'] : [];
        $q33_pts = in_array('ninguna', $q33) ? 0 : min(count($q33), 4);

        // Q34: habilidades — contar (max 4)
        $q34 = is_array($r['q34'] ?? null) ? $r['q34'] : [];
        $q34_pts = min(count($q34), 4);

        $pts = $q33_pts
             + $q34_pts
             + ($mapa_q36[$r['q36'] ?? ''] ?? 0)
             + ($mapa_q37[$r['q37'] ?? ''] ?? 0);

        return self::dim('sostenibilidad', 'Sostenibilidad', 'fa-seedling', $pts, 14);
    }

    // ─────────────────────────────────────────────────
    // DIM 7 · Salud Emocional y Resiliencia (Q39, Q40, Q41, Q43)
    // max raw = 3 + 2 + 3 + 10 = 18
    // ─────────────────────────────────────────────────
    private static function dim7_salud_emocional(array $r): array {
        $mapa_q39 = ['desafiante'=>0,'regular'=>1,'buena'=>2,'excelente'=>3];
        // Q40: apertura a consejería (no_prefiero = más cerrado = 0; resto = abierto = 2)
        $mapa_q40 = ['no_prefiero'=>0,'si_actualmente'=>2,'si_pasado'=>2,'no_abierto'=>2];
        $mapa_q41 = ['dificultad'=>0,'regular'=>1,'bien'=>2,'muy_bien'=>3];

        $pts = ($mapa_q39[$r['q39'] ?? ''] ?? 0)
             + ($mapa_q40[$r['q40'] ?? ''] ?? 0)
             + ($mapa_q41[$r['q41'] ?? ''] ?? 0)
             + (int)($r['q43'] ?? 0);

        return self::dim('salud_emocional', 'Salud Emocional', 'fa-heart', $pts, 18);
    }

    // ─────────────────────────────────────────────────
    // DIM 8 · Compromiso y Expectativas (Q45, Q46, Q47)
    // max raw = 3 + 4 + 3 = 10
    // ─────────────────────────────────────────────────
    private static function dim8_compromiso(array $r): array {
        $mapa_q45 = ['no_seguro'=>0,'probablemente'=>1,'si_preocupaciones'=>2,'si_totalmente'=>3];
        $mapa_q46 = ['oposicion'=>0,'poca_comprension'=>1,'apoyo_neutral'=>2,'apoyo_reservas'=>3,'apoyo_total'=>4];
        $mapa_q47 = ['si_no_sabe'=>0,'si_trabajando'=>1,'si_soluciones'=>2,'no'=>3];

        $pts = ($mapa_q45[$r['q45'] ?? ''] ?? 0)
             + ($mapa_q46[$r['q46'] ?? ''] ?? 0)
             + ($mapa_q47[$r['q47'] ?? ''] ?? 0);

        return self::dim('compromiso', 'Compromiso', 'fa-handshake', $pts, 10);
    }

    // ─────────────────────────────────────────────────
    // Helper: construye el array de dimensión normalizado a 0-100
    // ─────────────────────────────────────────────────
    private static function dim(string $clave, string $nombre, string $icono, float $pts, float $max): array {
        $score = $max > 0 ? round(min($pts, $max) / $max * 100) : 0;
        return [
            'clave'  => $clave,
            'nombre' => $nombre,
            'icono'  => $icono,
            'score'  => $score,
        ];
    }
}
