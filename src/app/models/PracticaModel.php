<?php
/**
 * PracticaModel
 * Lo que cada grupo reporta de su semana en la iglesia donde sirve.
 * Llega en números por actividad; ver migración 014.
 */

require_once APP_PATH . '/models/Model.php';

class PracticaModel extends Model {

    protected string $tabla = 'practica_actividades';

    public const TIPOS = [
        'culto'        => 'Culto dominical',
        'celula'       => 'Célula o culto en hogar',
        'evangelismo'  => 'Evangelismo',
        'taller'       => 'Taller o capacitación',
        'deportiva'    => 'Actividad deportiva',
        'vigilia'      => 'Vigilia',
        'organizacion' => 'Reunión por organización',
        'apoyo'        => 'Apoyo a otra iglesia',
        'especial'     => 'Otra actividad',
    ];

    /** A quién va dirigida una reunión por organización */
    public const ORGANIZACIONES = [
        'damas'       => 'Damas',
        'caballeros'  => 'Caballeros',
        'jovenes'     => 'Jóvenes',
        'ninos'       => 'Niños',
        'matrimonios' => 'Matrimonios',
    ];

    /**
     * Los alcances: todo lo que no es el culto del domingo ni la
     * salida de evangelismo. Es lo que el informe de cierre resume
     * como «qué se desarrolló en el lugar».
     */
    public const ALCANCES = ['taller', 'deportiva', 'vigilia', 'organizacion', 'apoyo', 'especial', 'celula'];

    public function iglesias(): array {
        return $this->db->query(
            "SELECT id, nombre, sector FROM practica_iglesias WHERE activa = 1 ORDER BY orden, nombre"
        )->fetchAll();
    }

    /** Actividades entre dos fechas, con el nombre de la iglesia */
    public function entre(string $desde, string $hasta): array {
        $stmt = $this->db->prepare("
            SELECT a.*, i.nombre AS iglesia, i.sector
            FROM practica_actividades a
            JOIN practica_iglesias i ON i.id = a.iglesia_id
            WHERE a.fecha BETWEEN :d AND :h
            ORDER BY a.fecha, i.orden, a.id
        ");
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll();
    }

    /** Primera y última fecha con actividades, o null si no hay ninguna */
    public function rango(): ?array {
        $r = $this->db->query("SELECT MIN(fecha) AS desde, MAX(fecha) AS hasta FROM practica_actividades")->fetch();
        return $r['desde'] ? $r : null;
    }

    /**
     * Acumulado por iglesia. El promedio del domingo es solo de la
     * comunidad: el equipo no cuenta como crecimiento de la iglesia.
     */
    public function acumuladoPorIglesia(): array {
        return $this->db->query("
            SELECT i.nombre AS iglesia, i.sector,
                   COUNT(a.id)                                   AS actividades,
                   COALESCE(SUM(a.evangelizados), 0)             AS evangelizados,
                   COALESCE(SUM(a.decisiones), 0)                AS decisiones,
                   COALESCE(SUM(a.discipulado), 0)               AS discipulado,
                   ROUND(AVG(IF(a.tipo = 'culto', a.comunidad, NULL))) AS promedio_culto
            FROM practica_iglesias i
            LEFT JOIN practica_actividades a ON a.iglesia_id = i.id
            WHERE i.activa = 1
            GROUP BY i.id, i.nombre, i.sector, i.orden
            ORDER BY i.orden
        ")->fetchAll();
    }

    // ── Informe de cierre de un lugar ─────────────────────────

    /** Resumen por iglesia dentro de un rango */
    public function porIglesiaEntre(string $desde, string $hasta): array {
        return $this->consulta("
            SELECT i.nombre AS iglesia, i.sector,
                   COUNT(a.id)                                         AS actividades,
                   COALESCE(SUM(a.evangelizados), 0)                   AS evangelizados,
                   COALESCE(SUM(a.decisiones), 0)                      AS decisiones,
                   COALESCE(SUM(a.discipulado), 0)                     AS discipulado,
                   ROUND(AVG(IF(a.tipo = 'culto', a.comunidad, NULL))) AS promedio_culto,
                   COALESCE(SUM(IF(a.tipo = 'culto', 0, a.comunidad)), 0) AS asistencia_alcances
            FROM practica_iglesias i
            LEFT JOIN practica_actividades a
                   ON a.iglesia_id = i.id AND a.fecha BETWEEN :d AND :h
            WHERE i.activa = 1
            GROUP BY i.id, i.nombre, i.sector, i.orden
            ORDER BY i.orden
        ", [':d' => $desde, ':h' => $hasta]);
    }

    /**
     * Los alcances agrupados por tipo: cuántos se hicieron y a cuánta
     * gente llegaron. El culto dominical y las salidas de evangelismo
     * quedan fuera porque tienen su propio cuadro en el informe.
     */
    public function alcancesEntre(string $desde, string $hasta): array {
        $en = "'" . implode("','", self::ALCANCES) . "'";

        return $this->consulta("
            SELECT a.tipo,
                   COUNT(*)                            AS veces,
                   COALESCE(SUM(a.comunidad), 0)       AS comunidad,
                   COALESCE(SUM(a.equipo), 0)          AS equipo,
                   GROUP_CONCAT(DISTINCT a.dirigida_a) AS dirigidas
            FROM practica_actividades a
            WHERE a.fecha BETWEEN :d AND :h AND a.tipo IN ({$en})
            GROUP BY a.tipo
            ORDER BY veces DESC, comunidad DESC
        ", [':d' => $desde, ':h' => $hasta]);
    }

    /** Reuniones por organización, abiertas por a quién fueron dirigidas */
    public function organizacionesEntre(string $desde, string $hasta): array {
        $filas = $this->consulta("
            SELECT a.dirigida_a, a.comunidad, a.equipo
            FROM practica_actividades a
            WHERE a.fecha BETWEEN :d AND :h
              AND a.tipo = 'organizacion' AND a.dirigida_a IS NOT NULL
        ", [':d' => $desde, ':h' => $hasta]);

        // Una reunión puede ser de damas y jóvenes a la vez: cuenta en
        // las dos, y así se lee «3 encuentros con damas», no 3 filas sueltas
        $out = [];
        foreach (self::ORGANIZACIONES as $clave => $etiqueta) {
            $out[$clave] = ['etiqueta' => $etiqueta, 'veces' => 0, 'asistencia' => 0];
        }
        foreach ($filas as $f) {
            foreach (array_filter(explode(',', (string) $f['dirigida_a'])) as $org) {
                if (!isset($out[$org])) { continue; }
                $out[$org]['veces']++;
                $out[$org]['asistencia'] += (int) $f['comunidad'];
            }
        }
        return array_filter($out, fn($o) => $o['veces'] > 0);
    }

    /** Avance del discipulado por lección dentro del rango */
    public function discipuladoPorLeccion(string $desde, string $hasta): array {
        return $this->consulta("
            SELECT n.nombre AS nivel, l.numero, l.titulo,
                   COUNT(a.id)                     AS veces,
                   COALESCE(SUM(a.discipulado), 0) AS personas
            FROM practica_actividades a
            JOIN discipulado_lecciones l ON l.id = a.leccion_id
            JOIN discipulado_niveles   n ON n.id = l.nivel_id
            WHERE a.fecha BETWEEN :d AND :h
            GROUP BY l.id, n.nombre, l.numero, l.titulo
            ORDER BY n.orden, l.numero
        ", [':d' => $desde, ':h' => $hasta]);
    }

    /** Semana a semana dentro del rango, para ver cómo evolucionó */
    public function porSemanaEntre(string $desde, string $hasta): array {
        return $this->consulta("
            SELECT YEARWEEK(a.fecha, 1)                            AS semana,
                   MIN(a.fecha)                                    AS inicio,
                   MAX(a.fecha)                                    AS fin,
                   COUNT(*)                                        AS actividades,
                   COALESCE(SUM(IF(a.tipo = 'culto', a.comunidad, 0)), 0) AS culto,
                   COALESCE(SUM(a.evangelizados), 0)               AS evangelizados,
                   COALESCE(SUM(a.decisiones), 0)                  AS decisiones,
                   COALESCE(SUM(a.discipulado), 0)                 AS discipulado
            FROM practica_actividades a
            WHERE a.fecha BETWEEN :d AND :h
            GROUP BY YEARWEEK(a.fecha, 1)
            ORDER BY semana
        ", [':d' => $desde, ':h' => $hasta]);
    }

    /** Totales del rango, los que encabezan el informe */
    public function totalesEntre(string $desde, string $hasta): array {
        $row = $this->consulta("
            SELECT COUNT(*)                                        AS actividades,
                   COUNT(DISTINCT a.iglesia_id)                    AS iglesias,
                   COALESCE(SUM(a.evangelizados), 0)               AS evangelizados,
                   COALESCE(SUM(a.decisiones), 0)                  AS decisiones,
                   COALESCE(SUM(a.discipulado), 0)                 AS discipulado,
                   COALESCE(SUM(IF(a.tipo = 'culto', a.comunidad, 0)), 0) AS culto,
                   COALESCE(SUM(a.comunidad), 0)                   AS comunidad
            FROM practica_actividades a
            WHERE a.fecha BETWEEN :d AND :h
        ", [':d' => $desde, ':h' => $hasta])[0] ?? [];

        return array_map('intval', $row ?: []);
    }

    private function consulta(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Totales para /impacto */
    public function totales(): array {
        $row = $this->db->query("
            SELECT COUNT(DISTINCT iglesia_id)        AS iglesias,
                   COALESCE(SUM(evangelizados), 0)   AS evangelizados,
                   COALESCE(SUM(decisiones), 0)      AS decisiones,
                   COALESCE(SUM(discipulado), 0)     AS discipulado
            FROM practica_actividades
        ")->fetch();

        return array_map('intval', $row);
    }
}
