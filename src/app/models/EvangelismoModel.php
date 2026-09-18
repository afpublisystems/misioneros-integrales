<?php
/**
 * EvangelismoModel
 * Personas alcanzadas en campo. Cada una se registra una vez y
 * avanza por etapas: evangelizada → decisión de fe → discipulado.
 *
 * También guarda el PIN del acceso público, en `configuracion`.
 */

require_once APP_PATH . '/models/Model.php';

class EvangelismoModel extends Model {

    protected string $tabla = 'personas_alcanzadas';

    private const CLAVE_PIN = 'evangelismo_pin';

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
        if (($filtros['etapa'] ?? '') === 'decision') {
            $where[] = 'p.decision_fe = 1';
        } elseif (($filtros['etapa'] ?? '') === 'discipulado') {
            $where[] = 'p.discipulado = 1';
        }
        if (!empty($filtros['q'])) {
            $where[] = "(CONCAT(p.nombres, ' ', p.apellidos) LIKE :q1 OR p.telefono LIKE :q2 OR p.direccion LIKE :q3)";
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
            SELECT COUNT(*)                        AS evangelizados,
                   COALESCE(SUM(decision_fe), 0)   AS decisiones,
                   COALESCE(SUM(discipulado), 0)   AS discipulados
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
                   COUNT(*)                         AS evangelizados,
                   SUM(p.decision_fe)               AS decisiones,
                   SUM(p.discipulado)               AS discipulados
            FROM personas_alcanzadas p
            LEFT JOIN sedes s ON s.id = p.sede_id
            GROUP BY s.id, s.nombre, s.orden
            ORDER BY s.orden IS NULL, s.orden
        ")->fetchAll();
    }

    public function sedes(): array {
        return $this->db->query(
            "SELECT id, nombre, fecha_inicio, fecha_fin FROM sedes WHERE activa = 1 ORDER BY orden"
        )->fetchAll();
    }

    /**
     * Sede donde está el grupo en una fecha, para preseleccionarla
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
        $stmt = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = :c");
        $stmt->execute([':c' => self::CLAVE_PIN]);
        return $stmt->fetchColumn() ?: null;
    }

    public function guardarPin(?string $pin): void {
        $this->db->prepare("
            INSERT INTO configuracion (clave, valor) VALUES (:c, :v)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ")->execute([
            ':c' => self::CLAVE_PIN,
            ':v' => $pin === null ? null : password_hash($pin, PASSWORD_DEFAULT),
        ]);
    }
}
