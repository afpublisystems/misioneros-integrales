<?php
/**
 * MigracionesController
 * Aplica las migraciones de la carpeta database/ desde el panel.
 *
 * El hosting no siempre da acceso a phpMyAdmin, así que los .sql se
 * suben por FTP y se ejecutan desde acá. Solo admin.
 *
 * Dos resguardos que importan:
 *   - Solo se listan los archivos migracion_*.sql. schema_produccion.sql
 *     vive en la misma carpeta y trae DROP TABLE: si apareciera en la
 *     lista, un clic de más borraría la base.
 *   - Cada archivo se ejecuta de a uno y queda anotado en
 *     migraciones_aplicadas. Un ALTER TABLE repetido falla, así que
 *     lo ya aplicado no se vuelve a correr.
 */

require_once APP_PATH . '/controllers/Controller.php';

class MigracionesController extends Controller {

    private const CARPETA = '/database';
    private const PATRON  = '/^migracion_[a-z0-9_]+\.sql$/i';

    // ── GET /admin/migraciones ────────────────────────────────
    public function index(): void {
        $this->requireAuth('admin');
        $this->crearTabla();

        $this->render('admin/migraciones', [
            'titulo'      => 'Migraciones',
            'archivos'    => $this->archivos(),
            'carpeta'     => BASE_PATH . self::CARPETA,
            'hay_carpeta' => is_dir(BASE_PATH . self::CARPETA),
        ], 'admin');
    }

    // ── POST /admin/migraciones ───────────────────────────────
    public function aplicar(): void {
        $this->requireAuth('admin');
        $this->crearTabla();

        $volver  = '/admin/migraciones';
        $archivo = basename($_POST['archivo'] ?? '');
        $ruta    = BASE_PATH . self::CARPETA . '/' . $archivo;

        if (!preg_match(self::PATRON, $archivo) || !is_file($ruta)) {
            $this->flash('error', 'Ese archivo no está en la carpeta de migraciones.');
            $this->redirigir($volver);
        }

        $sql = (string) file_get_contents($ruta);

        // Marcar sin ejecutar: para las que ya estaban aplicadas de antes
        if (($_POST['accion'] ?? '') === 'marcar') {
            $this->anotar($archivo, sha1($sql));
            $this->flash('exito', $archivo . ' queda anotada como aplicada. No se ejecutó nada.');
            $this->redirigir($volver);
        }

        if ($this->yaAplicada($archivo)) {
            $this->flash('error', $archivo . ' ya figura como aplicada. Si de verdad hay que repetirla, quítala primero de la lista.');
            $this->redirigir($volver);
        }

        $db = Database::getConnection();

        // La conexión va con prepares nativos, que no admiten varias
        // sentencias de un saque. Un .sql son muchas, así que se emula
        // mientras dura la migración y se devuelve como estaba.
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        try {
            $stmt = $db->query($sql);
            do {
                // no hay nada que leer, solo recorrer los resultados
            } while ($stmt->nextRowset());

            $this->anotar($archivo, sha1($sql));
            $this->flash('exito', $archivo . ' se aplicó bien.');
        } catch (PDOException $e) {
            $_SESSION['migracion_error'] = $e->getMessage();
            $this->flash('error', $archivo . ' falló. El detalle está abajo; la migración no quedó anotada.');
        } finally {
            $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }

        $this->redirigir($volver);
    }

    // ── POST /admin/migraciones/olvidar ───────────────────────
    public function olvidar(): void {
        $this->requireAuth('admin');

        $archivo = basename($_POST['archivo'] ?? '');
        Database::getConnection()
            ->prepare("DELETE FROM migraciones_aplicadas WHERE archivo = :a")
            ->execute([':a' => $archivo]);

        $this->flash('exito', $archivo . ' vuelve a figurar como pendiente.');
        $this->redirigir('/admin/migraciones');
    }

    // ── Helpers ───────────────────────────────────────────────

    private function crearTabla(): void {
        Database::getConnection()->exec("
            CREATE TABLE IF NOT EXISTS migraciones_aplicadas (
                archivo     VARCHAR(190) NOT NULL,
                sha1        CHAR(40)     NOT NULL,
                usuario_id  INT UNSIGNED NULL,
                aplicada    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (archivo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    /**
     * Las migraciones de la carpeta, con su estado. El sha1 guardado
     * sirve para avisar si el archivo cambió después de aplicarse.
     */
    private function archivos(): array {
        $dir = BASE_PATH . self::CARPETA;
        if (!is_dir($dir)) {
            return [];
        }

        $aplicadas = [];
        foreach (Database::getConnection()->query("SELECT * FROM migraciones_aplicadas") as $fila) {
            $aplicadas[$fila['archivo']] = $fila;
        }

        $lista = [];
        foreach (scandir($dir) as $nombre) {
            if (!preg_match(self::PATRON, $nombre)) {
                continue;
            }
            $sha = sha1((string) file_get_contents($dir . '/' . $nombre));
            $ya  = $aplicadas[$nombre] ?? null;

            $lista[] = [
                'nombre'   => $nombre,
                'peso'     => filesize($dir . '/' . $nombre),
                'aplicada' => $ya['aplicada'] ?? null,
                'cambio'   => $ya !== null && $ya['sha1'] !== $sha,
            ];
        }
        sort($lista);
        return $lista;
    }

    private function yaAplicada(string $archivo): bool {
        $stmt = Database::getConnection()
            ->prepare("SELECT 1 FROM migraciones_aplicadas WHERE archivo = :a");
        $stmt->execute([':a' => $archivo]);
        return (bool) $stmt->fetchColumn();
    }

    private function anotar(string $archivo, string $sha1): void {
        Database::getConnection()->prepare("
            INSERT INTO migraciones_aplicadas (archivo, sha1, usuario_id)
            VALUES (:a, :s, :u)
            ON DUPLICATE KEY UPDATE sha1 = VALUES(sha1), aplicada = CURRENT_TIMESTAMP
        ")->execute([
            ':a' => $archivo,
            ':s' => $sha1,
            ':u' => (int) ($_SESSION['usuario_id'] ?? 0) ?: null,
        ]);
    }
}
