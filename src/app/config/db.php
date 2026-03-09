<?php
/**
 * Configuración de Conexión a Base de Datos
 * Programa de Formación de Misioneros Integrales - CNBV/DIME
 * 
 * Patrón: Singleton PDO
 * Host: db (servicio Docker)
 */

class Database {
    private static string $host     = 'db';
    private static string $dbname   = 'misioneros_integrales_db';
    private static string $user     = 'root';
    private static string $password = 'secret_password';
    private static string $charset  = 'utf8mb4';

    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
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
                die('ERROR REAL: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    private function __clone() {}
}
