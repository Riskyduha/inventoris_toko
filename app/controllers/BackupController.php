<?php

class BackupController {
    private $backupDir;

    public function __construct() {
        $this->backupDir = BASE_PATH . '/database/backups';
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0775, true);
        }
    }

    private function env(string $key, string $default = ''): string {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return (string)$value;
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string)$_ENV[$key];
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return (string)$_SERVER[$key];
        }
        $envFile = BASE_PATH . '/.env';
        if (file_exists($envFile)) {
            $env = parse_ini_file($envFile);
            if (is_array($env) && !empty($env[$key])) {
                return (string)$env[$key];
            }
        }
        return $default;
    }

    private function dbConfig(): array {
        return [
            'host' => $this->env('DB_HOST', 'localhost'),
            'port' => $this->env('DB_PORT', '5432'),
            'name' => $this->env('DB_NAME', 'toko_inventori'),
            'user' => $this->env('DB_USER', 'postgres'),
            'pass' => $this->env('DB_PASS', 'password')
        ];
    }

    private function runBackup(string $label = 'manual'): array {
        $cfg = $this->dbConfig();
        $timestamp = date('Ymd_His');
        $filename = sprintf('backup_%s_%s.sql', $label, $timestamp);
        $fullpath = $this->backupDir . '/' . $filename;

        $cmd = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s -F p -f %s 2>&1',
            escapeshellarg($cfg['pass']),
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['port']),
            escapeshellarg($cfg['user']),
            escapeshellarg($cfg['name']),
            escapeshellarg($fullpath)
        );

        $output = [];
        $code = 0;
        @exec($cmd, $output, $code);

        if ($code !== 0 || !file_exists($fullpath)) {
            return ['success' => false, 'message' => 'Backup gagal: ' . implode("\n", $output)];
        }

        return ['success' => true, 'file' => $filename, 'path' => $fullpath];
    }

    private function ensureDailyBackup(): void {
        $todayPrefix = 'backup_daily_' . date('Ymd');
        $files = glob($this->backupDir . '/' . $todayPrefix . '*.sql');
        if (!empty($files)) {
            return;
        }
        $this->runBackup('daily');
    }

    public function index() {
        $this->ensureDailyBackup();

        $files = glob($this->backupDir . '/backup_*.sql') ?: [];
        rsort($files);

        $backups = array_map(function ($path) {
            return [
                'name' => basename($path),
                'size' => filesize($path) ?: 0,
                'modified' => date('Y-m-d H:i:s', filemtime($path) ?: time())
            ];
        }, $files);

        require_once BASE_PATH . '/app/views/backup/index.php';
    }

    public function create() {
        $result = $this->runBackup('manual');
        if ($result['success']) {
            $_SESSION['success'] = 'Backup berhasil dibuat: ' . $result['file'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        header('Location: /backup');
        exit;
    }

    public function download() {
        $file = basename((string)($_GET['file'] ?? ''));
        if ($file === '') {
            $_SESSION['error'] = 'File backup tidak valid.';
            header('Location: /backup');
            exit;
        }

        $path = realpath($this->backupDir . '/' . $file);
        $base = realpath($this->backupDir);
        if ($path === false || $base === false || strpos($path, $base) !== 0 || !is_file($path)) {
            $_SESSION['error'] = 'File backup tidak ditemukan.';
            header('Location: /backup');
            exit;
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function restore() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /backup');
            exit;
        }

        $file = basename((string)($_POST['file'] ?? ''));
        if ($file === '') {
            $_SESSION['error'] = 'Pilih file backup terlebih dahulu.';
            header('Location: /backup');
            exit;
        }

        $path = realpath($this->backupDir . '/' . $file);
        $base = realpath($this->backupDir);
        if ($path === false || $base === false || strpos($path, $base) !== 0 || !is_file($path)) {
            $_SESSION['error'] = 'File backup tidak valid.';
            header('Location: /backup');
            exit;
        }

        $cfg = $this->dbConfig();
        $cmd = sprintf(
            'PGPASSWORD=%s psql -h %s -p %s -U %s -d %s -f %s 2>&1',
            escapeshellarg($cfg['pass']),
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['port']),
            escapeshellarg($cfg['user']),
            escapeshellarg($cfg['name']),
            escapeshellarg($path)
        );

        $output = [];
        $code = 0;
        @exec($cmd, $output, $code);

        if ($code !== 0) {
            $_SESSION['error'] = 'Restore gagal: ' . implode("\n", $output);
        } else {
            $_SESSION['success'] = 'Restore berhasil dari file ' . $file;
        }

        header('Location: /backup');
        exit;
    }
}
