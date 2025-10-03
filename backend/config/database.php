<?php

class Database {
    private $connection;
    private static $instance = null;

    private function __construct() {
        // Priority 1: Check for Supabase configuration via SUPABASE_DB_PASSWORD
        if (isset($_ENV['SUPABASE_DB_PASSWORD']) && !empty($_ENV['SUPABASE_DB_PASSWORD'])) {
            // Use Supabase environment variables
            $host = 'db.gtvcakkgqlpfdivpejmi.supabase.co';
            $dbname = 'postgres';
            $user = 'postgres';
            $pass = $_ENV['SUPABASE_DB_PASSWORD'];
            $port = '5432';
            $sslmode = 'require';
        } else if (isset($_ENV['FORCE_DISCRETE_DB_CONFIG']) && $_ENV['FORCE_DISCRETE_DB_CONFIG'] === 'true' && isset($_ENV['DB_HOST']) && !empty($_ENV['DB_HOST'])) {
            // Priority 2: For SuperHosting deployment, use discrete DB_* variables if DB_HOST is set
            $host = $_ENV['DB_HOST'];
            $dbname = $_ENV['DB_NAME'] ?? 'postgres';
            $user = $_ENV['DB_USER'] ?? 'postgres';
            $pass = $_ENV['DB_PASS'] ?? '';
            $port = $_ENV['DB_PORT'] ?? '5432';
            $sslmode = $_ENV['DB_SSLMODE'] ?? 'require';
        } else if (isset($_ENV['DATABASE_URL']) && !empty($_ENV['DATABASE_URL'])) {
            // Parse DATABASE_URL if available (Replit style)
            $dbUrl = parse_url($_ENV['DATABASE_URL']);
            $host = $dbUrl['host'] ?? 'localhost';
            $port = $dbUrl['port'] ?? '5432';
            $dbname = ltrim($dbUrl['path'] ?? '/postgres', '/');
            $user = $dbUrl['user'] ?? 'postgres';
            $pass = $dbUrl['pass'] ?? '';
            $sslmode = 'require';
        } else {
            // Final fallback to PG* environment variables
            $host = $_ENV['PGHOST'] ?? 'localhost';
            $dbname = $_ENV['PGDATABASE'] ?? 'postgres';
            $user = $_ENV['PGUSER'] ?? 'postgres';
            $pass = $_ENV['PGPASSWORD'] ?? '';
            $port = $_ENV['PGPORT'] ?? '5432';
            $sslmode = 'require';
        }

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
            
            if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
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