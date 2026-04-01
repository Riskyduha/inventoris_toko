<?php

function runDailyBackupIfDue(): void {
    try {
        $backupDir = BASE_PATH . '/database/backups';
        if (!is_dir($backupDir)) {
            @mkdir($backupDir, 0775, true);
        }

        $prefix = 'backup_daily_' . date('Ymd');
        $existing = glob($backupDir . '/' . $prefix . '*.sql');
        if (!empty($existing)) {
            return;
        }

        $env = function (string $key, string $default = ''): string {
            $val = getenv($key);
            if ($val !== false && $val !== '') {
                return (string)$val;
            }
            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return (string)$_ENV[$key];
            }
            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
                return (string)$_SERVER[$key];
            }
            $envFile = BASE_PATH . '/.env';
            if (file_exists($envFile)) {
                $parsed = parse_ini_file($envFile);
                if (is_array($parsed) && !empty($parsed[$key])) {
                    return (string)$parsed[$key];
                }
            }
            return $default;
        };

        $host = $env('DB_HOST', 'localhost');
        $port = $env('DB_PORT', '5432');
        $name = $env('DB_NAME', 'toko_inventori');
        $user = $env('DB_USER', 'postgres');
        $pass = $env('DB_PASS', 'password');

        $filename = sprintf('backup_daily_%s_%s.sql', date('Ymd'), date('His'));
        $fullpath = $backupDir . '/' . $filename;

        $cmd = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -d %s -F p -f %s 2>&1',
            escapeshellarg($pass),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            escapeshellarg($name),
            escapeshellarg($fullpath)
        );

        $output = [];
        $code = 0;
        @exec($cmd, $output, $code);
        if ($code !== 0) {
            @unlink($fullpath);
            error_log('daily backup failed: ' . implode("\n", $output));
        }
    } catch (Exception $e) {
        error_log('daily backup exception: ' . $e->getMessage());
    }
}
