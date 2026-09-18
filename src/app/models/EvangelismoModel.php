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
        $where[] = match ($filtros['etapa'] ?? '') {
            'evangelizada' => "p.tipo = 'evangelizada'",
            'decision'     => 'p.decision_fe = 1',
            'discipulado'  => 'p.discipulado = 1',
            'contacto'     => "p.tipo = 'contacto'",
            default        => '1 = 1',
        };
        if (!empty($filtros['q'])) {
            $where[] = "(CONCAT_WS(' ', p.nombres, p.apellidos) LIKE :q1 OR p.telefono LIKE :q2 OR p.direccion LIKE :q3)";
            $like = '%' . $filtros['q'] . '%';
            $params[':q1'] = $params[':q2'] = $params[':q3'] = $like;
        }

        $stmt = $this->db->prepare("
            SELECT p.*, s.nombre AS sede_nombre
            FROM personas_alcanzadas p
            LEFT JOIN sedes s ON s.id = p.sede_id
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

    /**
     * Sedes que se pueden elegir: las del itinerario y, aparte, las
     * inactivas que no repiten nombre (lugares como Los Teques, donde
     * el grupo evangeliza sin que sea una sede del ciclo).
     */
    public function sedes(): array {
        return $this->db->query("
            SELECT id, nombre, activa FROM sedes
            WHERE activa = 1
               OR nombre NOT IN (SELECT nombre FROM sedes WHERE activa = 1)
            ORDER BY activa DESC, orden
        ")->fetchAll();
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
     * Sede donde está el grupo en una fecha según el itinerario
     */
    public function sedeEnFecha(string $fecha): ?int {
        $stmt = $this->db->prepare("
            SELECT id FROM sedes
            WHERE activa = 1 AND :f BETWEEN fecha_inicio AND fecha_fin
            ORDER BY orden LIMIT 1
        ");
        $stmt->execute([':f' => $fecha]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
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
