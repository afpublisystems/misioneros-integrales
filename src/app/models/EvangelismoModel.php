<?php
/**
 * EvangelismoModel
 * Personas alcanzadas en campo. Cada una se registra una vez, de
 * uno de dos tipos:
 *   evangelizada → decisión de fe → discipulado
 *   contacto     → contacto espiritual: oración, consejo o aliento,
 *                  sin predicación. No cuenta como evangelizada.
 *
 * También guarda en `configuracion` el PIN del acceso público y la
 * sede donde está el equipo, si el admin la fija a mano.
 */

require_once APP_PATH . '/models/Model.php';

class EvangelismoModel extends Model {

    protected string $tabla = 'personas_alcanzadas';

    private const CLAVE_PIN  = 'evangelismo_pin';
    private const CLAVE_SEDE = 'evangelismo_sede';

    public const ACOMPANAMIENTOS = [
        'oracion' => 'Oración',
        'consejo' => 'Consejo',
        'aliento' => 'Palabra de aliento',
    ];

    public function registrar(array $d): int {
        return $this->insertar($d);
    }

    /**
     * Listado con filtros de sede, etapa y búsqueda libre
     */
    public function listar(array $filtros = []): array {
        $where  = ['1 = 1'];
        $params = [];

        if (!empty($filtros['sede_id'])) {
            $where[] = 'p.sede_id = :sede';
            $params[':sede'] = (int) $filtros['sede_id'];
        }
        if (!empty($filtros['sector_id'])) {
            $where[] = 'p.sector_id = :sector';
            $params[':sector'] = (int) $filtros['sector_id'];
        }
        $where[] = match ($filtros['etapa'] ?? '') {
            'evangelizada' => "p.tipo = 'evangelizada'",
            'decision'     => 'p.decision_fe = 1',
            'discipulado'  => 'p.discipulado = 1',
            'contacto'     => "p.tipo = 'contacto'",
            'visita'       => 'p.desea_visita = 1',
            default        => '1 = 1',
        };
        if (!empty($filtros['q'])) {
            $where[] = "(CONCAT_WS(' ', p.nombres, p.apellidos) LIKE :q1 OR p.telefono LIKE :q2 OR p.direccion LIKE :q3)";
            $like = '%' . $filtros['q'] . '%';
            $params[':q1'] = $params[':q2'] = $params[':q3'] = $like;
        }

        $stmt = $this->db->prepare("
            SELECT p.*,
                   s.nombre  AS sede_nombre,
                   sec.nombre AS sector_nombre,
                   (SELECT GROUP_CONCAT(a.leccion_id)
                      FROM discipulado_avance a WHERE a.persona_id = p.id) AS lecciones
            FROM personas_alcanzadas p
            LEFT JOIN sedes s           ON s.id   = p.sede_id
            LEFT JOIN sede_sectores sec ON sec.id = p.sector_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY p.fecha_contacto DESC, p.id DESC
        ");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Totales generales: lo que se muestra en /impacto y en el panel
     */
    public function totales(): array {
        $row = $this->db->query("
            SELECT COALESCE(SUM(tipo = 'evangelizada'), 0) AS evangelizados,
                   COALESCE(SUM(decision_fe), 0)           AS decisiones,
                   COALESCE(SUM(discipulado), 0)           AS discipulados,
                   COALESCE(SUM(tipo = 'contacto'), 0)     AS contactos
            FROM personas_alcanzadas
        ")->fetch();

        return array_map('intval', $row);
    }

    /**
     * Totales por sede, en el orden del itinerario
     */
    public function totalesPorSede(): array {
        return $this->db->query("
            SELECT COALESCE(s.nombre, 'Sin sede')   AS sede,
                   SUM(p.tipo = 'evangelizada')     AS evangelizados,
                   SUM(p.decision_fe)               AS decisiones,
                   SUM(p.discipulado)               AS discipulados,
                   SUM(p.tipo = 'contacto')         AS contactos
            FROM personas_alcanzadas p
            LEFT JOIN sedes s ON s.id = p.sede_id
            GROUP BY s.id, s.nombre, s.orden
            ORDER BY s.orden IS NULL, s.orden
        ")->fetchAll();
    }

    /** Ciudades activas del itinerario, las que se pueden elegir */
    public function sedes(): array {
        return $this->db->query(
            "SELECT id, nombre FROM sedes WHERE activa = 1 ORDER BY orden"
        )->fetchAll();
    }

    /**
     * Sede que viene marcada en los formularios: la que fijó el admin
     * o, si no fijó ninguna, la que toca hoy según el itinerario.
     */
    public function sedeActual(): ?int {
        $fija = (int) $this->config(self::CLAVE_SEDE);
        if ($fija && in_array($fija, array_column($this->sedes(), 'id'))) {
            return $fija;
        }
        return $this->sedeEnFecha(date('Y-m-d'));
    }

    /** Sede fijada a mano por el admin, o null si sigue el itinerario */
    public function sedeFija(): ?int {
        return (int) $this->config(self::CLAVE_SEDE) ?: null;
    }

    public function fijarSede(?int $sede_id): void {
        $this->guardarConfig(self::CLAVE_SEDE, $sede_id ? (string) $sede_id : null);
    }

    /**
     * Sede donde está el grupo en una fecha según el itinerario.
     * Si dos rangos se pisan gana el más corto: una semana en Los
     * Teques dentro de los meses de La Guaira marca Los Teques.
     */
    public function sedeEnFecha(string $fecha): ?int {
        $stmt = $this->db->prepare("
            SELECT id FROM sedes
            WHERE activa = 1 AND :f BETWEEN fecha_inicio AND fecha_fin
            ORDER BY DATEDIFF(fecha_fin, fecha_inicio), orden LIMIT 1
        ");
        $stmt->execute([':f' => $fecha]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }

    // ── Sectores de cada sede ─────────────────────────────────

    /** Sectores activos, para armar los selects del formulario */
    public function sectores(): array {
        return $this->db->query("
            SELECT id, sede_id, nombre FROM sede_sectores
            WHERE activo = 1 ORDER BY sede_id, orden, nombre
        ")->fetchAll();
    }

    /** Todos, incluidos los inactivos: es la lista que administra el admin */
    public function sectoresTodos(): array {
        return $this->db->query("
            SELECT sec.*, s.nombre AS sede_nombre,
                   (SELECT COUNT(*) FROM personas_alcanzadas p
                     WHERE p.sector_id = sec.id) AS registros
            FROM sede_sectores sec
            JOIN sedes s ON s.id = sec.sede_id
            ORDER BY s.orden, sec.orden, sec.nombre
        ")->fetchAll();
    }

    /** El nuevo va al final de los de su sede, no al principio */
    public function agregarSector(int $sede_id, string $nombre): bool {
        return $this->db->prepare("
            INSERT INTO sede_sectores (sede_id, nombre, orden)
            SELECT :s, :n, COALESCE(MAX(orden), 0) + 1
              FROM sede_sectores WHERE sede_id = :s2
            ON DUPLICATE KEY UPDATE activo = 1
        ")->execute([':s' => $sede_id, ':n' => $nombre, ':s2' => $sede_id]);
    }

    /**
     * Los sectores no se borran: se desactivan. Si se borraran, los
     * registros viejos perderían dónde fueron.
     */
    public function alternarSector(int $id): bool {
        return $this->db->prepare(
            "UPDATE sede_sectores SET activo = 1 - activo WHERE id = :id"
        )->execute([':id' => $id]);
    }

    public function sectorValido(int $id, int $sede_id): bool {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM sede_sectores WHERE id = :id AND sede_id = :s AND activo = 1"
        );
        $stmt->execute([':id' => $id, ':s' => $sede_id]);
        return (bool) $stmt->fetchColumn();
    }

    // ── Discipulado ───────────────────────────────────────────

    /** Lecciones de los niveles activos, en orden */
    public function lecciones(): array {
        return $this->db->query("
            SELECT l.id, l.numero, l.titulo, n.id AS nivel_id, n.nombre AS nivel
            FROM discipulado_lecciones l
            JOIN discipulado_niveles n ON n.id = l.nivel_id
            WHERE l.activa = 1 AND n.activo = 1
            ORDER BY n.orden, l.numero
        ")->fetchAll();
    }

    /**
     * Deja marcadas exactamente las lecciones que vinieron del
     * formulario. La fecha es la del día en que se marcó; si ya
     * estaba dada, se respeta la que tenía.
     */
    public function guardarAvance(int $persona_id, array $lecciones): void {
        $validas = array_column($this->lecciones(), 'id');
        $marcar  = array_values(array_intersect($validas, array_map('intval', $lecciones)));

        if ($marcar) {
            $in = implode(',', array_fill(0, count($marcar), '?'));
            $this->db->prepare(
                "DELETE FROM discipulado_avance WHERE persona_id = ? AND leccion_id NOT IN ({$in})"
            )->execute([$persona_id, ...$marcar]);

            $stmt = $this->db->prepare("
                INSERT IGNORE INTO discipulado_avance (persona_id, leccion_id, completada)
                VALUES (:p, :l, CURDATE())
            ");
            foreach ($marcar as $leccion_id) {
                $stmt->execute([':p' => $persona_id, ':l' => $leccion_id]);
            }
        } else {
            $this->db->prepare("DELETE FROM discipulado_avance WHERE persona_id = ?")
                     ->execute([$persona_id]);
        }
    }

    // ── PIN del acceso público ────────────────────────────────

    /** Hash del PIN vigente, o null si el acceso está desactivado */
    public function hashPin(): ?string {
        return $this->config(self::CLAVE_PIN);
    }

    public function guardarPin(?string $pin): void {
        $this->guardarConfig(self::CLAVE_PIN, $pin === null ? null : password_hash($pin, PASSWORD_DEFAULT));
    }

    // ── Tabla configuracion ───────────────────────────────────

    private function config(string $clave): ?string {
        $stmt = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = :c");
        $stmt->execute([':c' => $clave]);
        return $stmt->fetchColumn() ?: null;
    }

    private function guardarConfig(string $clave, ?string $valor): void {
        $this->db->prepare("
            INSERT INTO configuracion (clave, valor) VALUES (:c, :v)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ")->execute([':c' => $clave, ':v' => $valor]);
    }
}
