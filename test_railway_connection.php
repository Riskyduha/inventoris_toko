<?php
/**
 * Test Railway connection dari local
 * Run: php test_railway_connection.php
 * 
 * Sebelum jalankan, set env vars:
 * export RAILWAY_URL="https://your-app.railway.app"
 */

$railwayUrl = getenv('RAILWAY_URL');

if (!$railwayUrl) {
    echo "❌ ERROR: Set RAILWAY_URL environment variable dulu!" . PHP_EOL;
    echo "Usage: RAILWAY_URL='https://your-app.railway.app' php test_railway_connection.php" . PHP_EOL;
    exit(1);
}

echo "🔍 Testing Railway Connection" . PHP_EOL;
echo "URL: " . $railwayUrl . PHP_EOL;
echo "=============================" . PHP_EOL . PHP_EOL;

// Test 1: Basic connectivity
echo "[1] Testing basic connectivity..." . PHP_EOL;
$response = @file_get_contents($railwayUrl);
if ($response === false) {
    echo "❌ Cannot reach URL" . PHP_EOL;
    exit(1);
}
echo "✓ URL is reachable" . PHP_EOL . PHP_EOL;

// Test 2: Debug endpoint
echo "[2] Checking debug.php..." . PHP_EOL;
$debugUrl = rtrim($railwayUrl, '/') . '/debug.php';
$debugResponse = @file_get_contents($debugUrl);
if ($debugResponse === false) {
    echo "❌ debug.php not accessible" . PHP_EOL;
    echo "   Try: " . $debugUrl . PHP_EOL;
} else {
    echo "✓ debug.php accessible" . PHP_EOL;
    // Show first 500 chars
    echo substr($debugResponse, 0, 500) . "..." . PHP_EOL;
}

echo PHP_EOL;
echo "[3] Try login at:" . PHP_EOL;
echo "   " . rtrim($railwayUrl, '/') . "/login" . PHP_EOL;
echo PHP_EOL;
echo "Credentials:" . PHP_EOL;
echo "   admin / admin123" . PHP_EOL;
echo "   kasir / kasir123" . PHP_EOL;
?>
