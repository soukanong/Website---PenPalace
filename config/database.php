<?php
/**
 * Database connection handler
 * Establishes connection to MariaDB/MySQL database
 */

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $host = "localhost";
        $db = "penpalace_db";
        $user = "root";
        $pass = "";

        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND =>
                        "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                ]
            );
        } catch (PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // Prevent cloning of the instance
    private function __clone()
    {
    }

    // Prevent unserialize of the instance
    public function __wakeup()
    {
    }
}
