<?php

use PDO;
use PDOException;

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        // Production PostgreSQL connection from environment
        $host = $_ENV['DB_HOST'] ?? 'db.mveeovfztfczibtvkpas.supabase.co';
        $dbname = $_ENV['DB_NAME'] ?? 'postgres';
        $user = $_ENV['DB_USER'] ?? 'postgres';
        $pass = $_ENV['DB_PASS'] ?? '';
        $port = $_ENV['DB_PORT'] ?? '5432';
        $sslmode = $_ENV['DB_SSLMODE'] ?? 'require';

        try {
            $this->connection = new PDO(
                "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            if ($_ENV['APP_DEBUG'] === 'true') {
                error_log('[DB] Connected successfully to PostgreSQL: ' . $dbname . ' on ' . $host);
            }
        } catch (PDOException $exception) {
            error_log('[DB] PostgreSQL connection failed: ' . $exception->getMessage());
            
            // In production, don't expose database details
            throw new Exception("Database connection failed");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}