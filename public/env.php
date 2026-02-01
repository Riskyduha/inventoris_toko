<?php
// Diagnostic env check (only when APP_DEBUG=true)
$getEnv = function($key, $default = null) {
    $val = getenv($key);
    if ($val !== false && $val !== '') {
        return $val;
    }
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    return $default;
};

if ($getEnv('APP_DEBUG') !== 'true') {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

$mask = function($value) {
    if ($value === null || $value === '') {
        return '(empty)';
    }
    $len = strlen($value);
    if ($len <= 4) {
        return str_repeat('*', $len);
    }
    return substr($value, 0, 2) . str_repeat('*', $len - 4) . substr($value, -2);
};

$vars = [
    'DATABASE_URL',
    'RAILWAY_DATABASE_URL',
    'DB_HOST',
    'DB_NAME',
    'DB_USER',
    'DB_PASS',
    'DB_PORT',
    'PGHOST',
    'PGDATABASE',
    'PGUSER',
    'PGPASSWORD',
    'PGPORT',
];

header('Content-Type: text/plain');
foreach ($vars as $k) {
    $v = $getEnv($k);
    if ($k === 'DB_PASS' || $k === 'PGPASSWORD' || $k === 'DATABASE_URL' || $k === 'RAILWAY_DATABASE_URL') {
        echo $k . '=' . $mask($v) . "\n";
    } else {
        echo $k . '=' . ($v !== null && $v !== '' ? $v : '(empty)') . "\n";
    }
}
