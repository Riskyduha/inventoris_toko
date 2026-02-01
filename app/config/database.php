<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $conn;

    public function __construct() {
        // Load dari environment (Railway) lalu fallback ke .env atau default
        $getEnv = function($key, $default = null) {
            $val = getenv($key);
            if ($val === false || $val === '') {
                return $default;
            }
            return $val;
        };

        $envHost = $getEnv('DB_HOST');
        $envName = $getEnv('DB_NAME');
        $envUser = $getEnv('DB_USER');
        $envPass = $getEnv('DB_PASS');
        $envPort = $getEnv('DB_PORT');

        // Railway: support DATABASE_URL or PG* variables
        $databaseUrl = $getEnv('DATABASE_URL') ?: $getEnv('RAILWAY_DATABASE_URL');
        $pgHost = $getEnv('PGHOST');
        $pgName = $getEnv('PGDATABASE');
        $pgUser = $getEnv('PGUSER');
        $pgPass = $getEnv('PGPASSWORD');
        $pgPort = $getEnv('PGPORT');

        if ($databaseUrl) {
            $parts = parse_url($databaseUrl);
            if ($parts !== false) {
                $this->host = $parts['host'] ?? 'localhost';
                $this->port = isset($parts['port']) ? (string)$parts['port'] : '5432';
                $this->db_name = isset($parts['path']) ? ltrim($parts['path'], '/') : 'toko_inventori';
                $this->username = $parts['user'] ?? 'postgres';
                $this->password = $parts['pass'] ?? 'password';
                return;
            }
        }

        if ($pgHost || $pgName || $pgUser || $pgPass || $pgPort) {
            $this->host = $pgHost ?? 'localhost';
            $this->db_name = $pgName ?? 'toko_inventori';
            $this->username = $pgUser ?? 'postgres';
            $this->password = $pgPass ?? 'password';
            $this->port = $pgPort ?? '5432';
            return;
        }

        if ($envHost || $envName || $envUser || $envPass || $envPort) {
            $this->host = $envHost ?? 'localhost';
            $this->db_name = $envName ?? 'toko_inventori';
            $this->username = $envUser ?? 'postgres';
            $this->password = $envPass ?? 'password';
            $this->port = $envPort ?? '5432';
            return;
        }

        // Load dari .env jika ada, atau gunakan default
        $envFile = dirname(__DIR__, 2) . '/.env';
        
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            $this->host = $env['DB_HOST'] ?? 'localhost';
            $this->db_name = $env['DB_NAME'] ?? 'toko_inventori';
            $this->username = $env['DB_USER'] ?? 'postgres';
            $this->password = $env['DB_PASS'] ?? 'password';
            $this->port = $env['DB_PORT'] ?? '5432';
        } else {
            // Fallback untuk development
            $this->host = 'localhost';
            $this->db_name = 'toko_inventori';
            $this->username = 'postgres';
            $this->password = 'password';
            $this->port = '5432';
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            // PostgreSQL connection string
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->exec("SET NAMES 'UTF8'");

            // Run migration to create tables if needed
            require_once __DIR__ . '/migrate.php';
            runMigration($this->conn);

            // Seed initial data if needed
            require_once __DIR__ . '/seed.php';
            seedIfNeeded($this->conn);
        } catch(PDOException $exception) {
            // Jangan tampilkan detail error di production
            if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
                echo "Connection error: " . $exception->getMessage();
            } else {
                error_log("Database connection error: " . $exception->getMessage());
                die("Database connection failed. Please contact administrator.");
            }
        }

        return $this->conn;
    }
}
