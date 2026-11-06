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
        'culto'       => 'Culto dominical',
        'celula'      => 'Célula o culto en hogar',
        'evangelismo' => 'Evangelismo',
        'especial'    => 'Actividad especial',
        'apoyo'       => 'Apoyo a otra iglesia',
    ];

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
