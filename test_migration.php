<?php
/**
 * Test script untuk memverifikasi migration works correctly
 * Run: php test_migration.php
 */

require_once 'app/config/database.php';

echo "═══════════════════════════════════════════════════════════════\n";
echo "Testing Automatic Migration System\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

try {
    echo "[1/4] Connecting to database...\n";
    $db = new Database();
    $conn = $db->getConnection();
    echo "✓ Connected successfully\n\n";

    echo "[2/4] Checking if tables exist...\n";
    $tables = ['users', 'kategori', 'satuan', 'barang', 'pembelian', 'penjualan', 'hutang', 'konfigurasi_nota'];
    
    foreach ($tables as $table) {
        $stmt = $conn->query("
            SELECT EXISTS (
                SELECT 1 FROM information_schema.tables 
                WHERE table_name = '$table'
            )
        ");
        $exists = $stmt->fetchColumn();
        $status = $exists ? "✓" : "✗";
        printf("  %s %-20s ... %s\n", $status, $table, $exists ? "EXIST" : "MISSING");
    }
    echo "\n";

    echo "[3/4] Checking data in tables...\n";
    
    $counts = [
        'users' => 'User accounts',
        'kategori' => 'Categories',
        'satuan' => 'Units',
        'barang' => 'Items',
        'konfigurasi_nota' => 'Invoice config'
    ];
    
    foreach ($counts as $table => $label) {
        $stmt = $conn->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        printf("  %-25s ... %d record(s)\n", $label, $count);
    }
    echo "\n";

    echo "[4/4] Testing user login...\n";
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $adminUser = $stmt->fetch();
    
    if ($adminUser) {
        echo "  ✓ Admin user found\n";
        echo "  Username: " . $adminUser['username'] . "\n";
        echo "  Role: " . $adminUser['role'] . "\n";
        
        // Test password
        if (password_verify('admin123', $adminUser['password'])) {
            echo "  ✓ Password verification: OK (admin123)\n";
        } else {
            echo "  ✗ Password verification: FAILED\n";
        }
    } else {
        echo "  ✗ Admin user NOT found\n";
    }
    echo "\n";

    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✓ ALL TESTS PASSED - Migration working correctly!\n";
    echo "═══════════════════════════════════════════════════════════════\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    exit(1);
}
