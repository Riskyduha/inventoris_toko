<?php

function seedIfNeeded(PDO $conn): void
{
    $getEnv = function(string $key, ?string $default = null): ?string {
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

    $appEnv = strtolower((string)$getEnv('APP_ENV', 'development'));
    $isProduction = ($appEnv === 'production');
    $force = (strtolower((string)$getEnv('SEED_FORCE', 'false')) === 'true');
    error_log("Seed check started (APP_ENV=$appEnv, FORCE=" . ($force ? 'true' : 'false') . ")");

    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM users");
        $userCount = (int)$stmt->fetchColumn();
        error_log("Users table exists, current count: " . $userCount);
    } catch (Exception $e) {
        error_log("ERROR in seed: Users table not ready - " . $e->getMessage());
        return; // Table not ready
    }

    $conn->beginTransaction();

    try {
        // konfigurasi_nota (insert once)
        $stmt = $conn->query("SELECT COUNT(*) FROM konfigurasi_nota");
        $notaCount = (int)$stmt->fetchColumn();
        if ($notaCount === 0) {
            $stmt = $conn->prepare("INSERT INTO konfigurasi_nota (nama_toko, alamat_toko, nomor_telepon, footer_nota) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                'UD. BERSAUDARA',
                'Jalan Merdeka No. 123',
                '0812-3456-7890',
                'Terima kasih atas pembelian Anda!'
            ]);
        }

        // users
        // Production: password wajib dari .env (no hardcoded default)
        // Development: fallback default tetap tersedia agar local setup mudah
        $adminPassword = (string)$getEnv('SEED_ADMIN_PASSWORD', $isProduction ? null : 'admin123');
        $kasirPassword = (string)$getEnv('SEED_KASIR_PASSWORD', $isProduction ? null : 'kasir123');

        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $adminExists = (int)$stmt->fetchColumn() > 0;
        $stmt->execute(['kasir']);
        $kasirExists = (int)$stmt->fetchColumn() > 0;

        if (!$adminExists) {
            if ($adminPassword === '') {
                error_log("Skipped admin seed: SEED_ADMIN_PASSWORD is empty");
            } else {
                $stmtInsert = $conn->prepare("INSERT INTO users (username, password, role, nama) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([
                    'admin',
                    password_hash($adminPassword, PASSWORD_DEFAULT),
                    'admin',
                    'Administrator'
                ]);
                error_log("Created admin user");
            }
        } else {
            // Security: never overwrite existing password automatically
            error_log("Admin user already exists, skip password reset");
        }

        if (!$kasirExists) {
            if ($kasirPassword === '') {
                error_log("Skipped kasir seed: SEED_KASIR_PASSWORD is empty");
            } else {
                $stmtInsert = $conn->prepare("INSERT INTO users (username, password, role, nama) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([
                    'kasir',
                    password_hash($kasirPassword, PASSWORD_DEFAULT),
                    'kasir',
                    'Kasir Utama'
                ]);
                error_log("Created kasir user");
            }
        } else {
            // Security: never overwrite existing password automatically
            error_log("Kasir user already exists, skip password reset");
        }

        // kategori (insert if empty)
        $stmt = $conn->query("SELECT COUNT(*) FROM kategori");
        $kategoriCount = (int)$stmt->fetchColumn();
        if ($kategoriCount === 0) {
            $kategori = ['Makanan', 'Minuman', 'Pewangi', 'Jajan', 'Lainnya', 'Rokok'];
            $stmt = $conn->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
            foreach ($kategori as $kat) {
                $stmt->execute([$kat]);
            }
        }

        // satuan (insert if empty)
        $stmt = $conn->query("SELECT COUNT(*) FROM satuan");
        $satuanCount = (int)$stmt->fetchColumn();
        if ($satuanCount === 0) {
            $satuan = ['pcs', 'kg', 'liter', 'botol', 'bungkus', 'box', 'pack', 'unit', 'gram', 'meter', 'lusin'];
            $stmt = $conn->prepare("INSERT INTO satuan (nama_satuan) VALUES (?)");
            foreach ($satuan as $sat) {
                $stmt->execute([$sat]);
            }
        }

        // barang (seed awal) - insert if empty
        $stmt = $conn->query("SELECT COUNT(*) FROM barang");
        $barangCount = (int)$stmt->fetchColumn();
        if ($barangCount === 0) {
            $barang = [
                ['BRG-001', 'Beras Premium 5kg', 1, 'kg', 60000, 75000, 20],
                ['BRG-002', 'Minyak Goreng 2L', 1, 'liter', 25000, 32000, 30],
                ['BRG-003', 'Gula Pasir 1kg', 1, 'kg', 12000, 15000, 25],
                ['BRG-004', 'Telur Ayam 1kg', 1, 'kg', 28000, 35000, 40],
                ['BRG-005', 'Teh Celup', 2, 'box', 7000, 10000, 50],
                ['BRG-006', 'Kopi Bubuk 200g', 2, 'bungkus', 12000, 15000, 40],
                ['BRG-007', 'Susu UHT 1L', 2, 'liter', 16000, 19000, 30],
                ['BRG-008', 'Detergen Bubuk 1kg', 3, 'kg', 18000, 22000, 25],
                ['BRG-009', 'Sabun Mandi', 3, 'pcs', 3000, 5000, 100],
                ['BRG-010', 'Snack Kentang', 4, 'bungkus', 6000, 8000, 60],
                ['BRG-011', 'Permen', 4, 'pack', 4000, 6000, 80],
                ['BRG-012', 'Rokok Filter', 6, 'bungkus', 20000, 25000, 45]
            ];
            $stmt = $conn->prepare("INSERT INTO barang (kode_barang, nama_barang, id_kategori, satuan, harga_beli, harga_jual, stok) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($barang as $b) {
                $stmt->execute($b);
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        error_log("ERROR in seed: Transaction failed - " . $e->getMessage());
        $conn->rollBack();
    }
    
    error_log("Seed check completed");
}
