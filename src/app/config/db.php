<?php
/**
 * Configuración de Conexión a Base de Datos
 * Programa de Formación de Misioneros Integrales - CNBV/DIME
 * 
 * Patrón: Singleton PDO
 * Host: db (servicio Docker)
 */

class Database {
    // Lee de variables de entorno Docker; los valores hardcodeados son solo fallback para dev local.
    // En producción definir: DB_HOST, DB_NAME, DB_USER, DB_PASS en docker-compose.yml (environment:)
    private static string $host     = '';
    private static string $dbname   = '';
    private static string $user     = '';
    private static string $password = '';
    private static string $charset  = 'utf8mb4';

    private static function init(): void {
        // En producción (Webempresa): reemplaza los valores de fallback con los datos reales de cPanel.
        // En Docker local: las variables de entorno se inyectan desde docker-compose.yml.
        self::$host     = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
        self::$dbname   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'hosting63201us_misioneros';
        self::$user     = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'hosting63201us_admin';
        self::$password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: 'Admin123*';
    }

    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            self::init();
            $dsn = "mysql:host=" . self::$host
                 . ";dbname=" . self::$dbname
                 . ";charset=" . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$user, self::$password, $options);
                self::$instance->exec("SET NAMES utf8mb4");
            } catch (PDOException $e) {
                if (defined('APP_DEV') && APP_DEV) {
                    die('DB Error: ' . $e->getMessage());
                }
                error_log('DB connection failed: ' . $e->getMessage());
                http_response_code(503);
                die('El servicio no está disponible temporalmente. Intenta más tarde.');
            }
        }

        return self::$instance;
    }

    private function __clone() {}
}
