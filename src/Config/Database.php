<?php

namespace App\Config;

use PDO;
use PDOException;

/**
 * Database – PDO singleton factory.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Return the shared PDO connection, creating it on first call.
     *
     * @throws PDOException on connection failure
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    /**
     * Reset the singleton (useful in unit tests).
     */
    public static function reset(): void
    {
        self::$instance = null;
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private static function createConnection(): PDO
    {
        $host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: 'localhost');
        $port = defined('DB_PORT') ? DB_PORT : (int)(getenv('DB_PORT') ?: 3306);
        $user = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'root');
        $pass = defined('DB_PASS') ? DB_PASS : (getenv('DB_PASS') ?: '');
        $name = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: 'compilerhub');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        return new PDO($dsn, $user, $pass, $options);
    }
}
