<?php
// Migration Script - Jalankan sekali untuk create tables di Railway
require_once __DIR__ . '/../app/config/database.php';

echo "<!DOCTYPE html><html><head><title>Database Migration</title></head><body>";
echo "<h2>🔧 Database Migration untuk Railway</h2>";
echo "<pre>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed!");
    }
    
    echo "✓ Database connection successful\n\n";
    echo "📋 Creating tables...\n";
    echo str_repeat("-", 50) . "\n";
    
    // Read SQL file (with fallbacks)
    $sqlCandidates = [
        __DIR__ . '/../database/skema_postgresql.sql',
        dirname(__DIR__) . '/database/skema_postgresql.sql',
        '/app/database/skema_postgresql.sql',
    ];

    $sql = null;
    foreach ($sqlCandidates as $candidate) {
        if (file_exists($candidate)) {
            $sql = file_get_contents($candidate);
            break;
        }
    }

    if ($sql === null) {
        // Fallback embedded schema (PostgreSQL)
        $sql = <<<SQL
-- Database Schema untuk Sistem Inventori Toko - PostgreSQL
-- Catatan: CREATE DATABASE dan \c tidak dijalankan di Railway

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id_user SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'kasir' CHECK (role IN ('admin', 'manager', 'kasir', 'inspeksi')),
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori SERIAL PRIMARY KEY,
    nama_kategori VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Satuan
CREATE TABLE IF NOT EXISTS satuan (
    id_satuan SERIAL PRIMARY KEY,
    nama_satuan VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Barang
CREATE TABLE IF NOT EXISTS barang (
    id_barang SERIAL PRIMARY KEY,
    kode_barang VARCHAR(30) NOT NULL UNIQUE,
    nama_barang VARCHAR(100) NOT NULL,
    id_kategori INT NOT NULL REFERENCES kategori(id_kategori) ON DELETE RESTRICT,
    satuan VARCHAR(20) DEFAULT 'pcs',
    harga_beli NUMERIC(12,2) NOT NULL,
    harga_jual NUMERIC(12,2) NOT NULL,
    stok INT DEFAULT 0,
    stok_updated_by INT REFERENCES users(id_user) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pembelian
CREATE TABLE IF NOT EXISTS pembelian (
    id_pembelian SERIAL PRIMARY KEY,
    total_harga NUMERIC(12,2) NOT NULL,
    uang_diberikan NUMERIC(12,2) DEFAULT 0,
    kembalian NUMERIC(12,2) DEFAULT 0,
    nama_pembeli VARCHAR(100),
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Detail Pembelian
CREATE TABLE IF NOT EXISTS detail_pembelian (
    id_detail SERIAL PRIMARY KEY,
    id_pembelian INT NOT NULL REFERENCES pembelian(id_pembelian) ON DELETE CASCADE,
    id_barang INT NOT NULL REFERENCES barang(id_barang) ON DELETE CASCADE,
    jumlah INT NOT NULL,
    harga_satuan NUMERIC(12,2) NOT NULL,
    diskon NUMERIC(12,2) DEFAULT 0,
    subtotal NUMERIC(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Penjualan
CREATE TABLE IF NOT EXISTS penjualan (
    id_penjualan SERIAL PRIMARY KEY,
    total_harga NUMERIC(12,2) NOT NULL,
    uang_diberikan NUMERIC(12,2) DEFAULT 0,
    kembalian NUMERIC(12,2) DEFAULT 0,
    nama_pembeli VARCHAR(100),
    ada_hutang SMALLINT DEFAULT 0,
    id_user INT REFERENCES users(id_user) ON DELETE SET NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Detail Penjualan
CREATE TABLE IF NOT EXISTS detail_penjualan (
    id_detail SERIAL PRIMARY KEY,
    id_penjualan INT NOT NULL REFERENCES penjualan(id_penjualan) ON DELETE CASCADE,
    id_barang INT NOT NULL REFERENCES barang(id_barang) ON DELETE CASCADE,
    jumlah INT NOT NULL,
    harga_satuan NUMERIC(12,2) NOT NULL,
    diskon NUMERIC(12,2) DEFAULT 0,
    subtotal NUMERIC(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Hutang
CREATE TABLE IF NOT EXISTS hutang (
    id_hutang SERIAL PRIMARY KEY,
    id_penjualan INT NOT NULL REFERENCES penjualan(id_penjualan) ON DELETE CASCADE,
    nama_penghutang VARCHAR(100) NOT NULL,
    jumlah_hutang NUMERIC(12,2) NOT NULL,
    jatuh_tempo DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'belum_bayar' CHECK (status IN ('belum_bayar', 'lunas')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Konfigurasi Nota
CREATE TABLE IF NOT EXISTS konfigurasi_nota (
    id_config SERIAL PRIMARY KEY,
    nama_toko VARCHAR(100) DEFAULT 'UD. BERSAUDARA',
    alamat_toko TEXT,
    nomor_telepon VARCHAR(20),
    email_toko VARCHAR(50),
    footer_nota TEXT,
    lebar_kertas INT DEFAULT 80,
    tampilkan_jam SMALLINT DEFAULT 1,
    tampilkan_kode_barang SMALLINT DEFAULT 1,
    tampilkan_satuan SMALLINT DEFAULT 1,
    jumlah_diskon_terpisah SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data awal
INSERT INTO konfigurasi_nota (nama_toko, alamat_toko, nomor_telepon, footer_nota) VALUES
('UD. BERSAUDARA', 'Jalan Merdeka No. 123', '0812-3456-7890', 'Terima kasih atas pembelian Anda!');

INSERT INTO users (username, password, role, nama) VALUES
('admin', '$2y$12$DQTsuQb8XDQGfrKWyTCHQOlhrwWJW42PgSzFZk3D6B0rCTjgPwV8.', 'admin', 'Administrator'),
('kasir', '$2y$12$zdSxawivB3Ir8d3.htooEuSXLxo/d8JHTZP5j0GzUr.7mi/cXW8cC', 'kasir', 'Kasir Utama');

INSERT INTO kategori (nama_kategori) VALUES
('Makanan'),
('Minuman'),
('Pewangi'),
('Jajan'),
('Lainnya'),
('Rokok');

INSERT INTO satuan (nama_satuan) VALUES
('pcs'),
('kg'),
('liter'),
('botol'),
('bungkus'),
('box'),
('pack'),
('unit'),
('gram'),
('meter'),
('lusin');

INSERT INTO barang (kode_barang, nama_barang, id_kategori, satuan, harga_beli, harga_jual, stok) VALUES
('BRG-001', 'Beras Premium 5kg', 1, 'kg', 60000, 75000, 20),
('BRG-002', 'Minyak Goreng 2L', 1, 'liter', 25000, 32000, 30),
('BRG-003', 'Gula Pasir 1kg', 1, 'kg', 12000, 15000, 25),
('BRG-004', 'Telur Ayam 1kg', 1, 'kg', 28000, 35000, 40),
('BRG-005', 'Rokok Kretek Filter', 6, 'bungkus', 35000, 40000, 50),
('BRG-006', 'Rokok Putih Filter', 6, 'bungkus', 45000, 52000, 45),
('BRG-007', 'Sprite 1.5L', 2, 'botol', 12000, 18000, 60),
('BRG-008', 'Coca-Cola 1.5L', 2, 'botol', 12000, 18000, 55),
('BRG-009', 'Air Mineral 600ml', 2, 'botol', 3000, 5000, 100),
('BRG-010', 'Kue Kering Tradisional', 4, 'box', 15000, 22000, 15),
('BRG-011', 'Kerupuk Udang', 4, 'pack', 8000, 12000, 35),
('BRG-012', 'Permen Coklat', 4, 'pack', 5000, 8000, 50);
SQL;
    }
    
    // Execute SQL
    $conn->exec($sql);
    
    echo "\n✅ SUCCESS! All tables created!\n";
    echo str_repeat("-", 50) . "\n\n";
    
    // Check tables
    echo "📊 Verifying tables...\n";
    $stmt = $conn->query("
        SELECT table_name 
        FROM information_schema.tables 
        WHERE table_schema = 'public' 
        ORDER BY table_name
    ");
    
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "Found " . count($tables) . " tables:\n";
        foreach ($tables as $table) {
            echo "  • $table\n";
        }
    } else {
        echo "⚠️ No tables found!\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    echo "✅ Migration completed successfully!\n";
    echo "\n🎉 You can now access your application at:\n";
    echo "   <a href='/login'>/login</a>\n";
    echo "\n👤 Default credentials:\n";
    echo "   Admin: admin / admin123\n";
    echo "   Kasir: kasir / kasir123\n";
    
} catch (PDOException $e) {
    echo "\n❌ Database Error:\n";
    echo $e->getMessage() . "\n";
    exit;
} catch (Exception $e) {
    echo "\n❌ Error:\n";
    echo $e->getMessage() . "\n";
    exit;
}

echo "</pre></body></html>";
?>
