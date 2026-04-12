<?php

require_once __DIR__ . '/../config/database.php';

class KonfigurasiNota {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->bootstrap();
    }

    // Pastikan tabel konfigurasi ada dan terisi default
    private function bootstrap() {
        if (!$this->conn) {
            throw new Exception('Database connection not available');
        }

        $createSql = "CREATE TABLE IF NOT EXISTS konfigurasi_nota (
            id_config SERIAL PRIMARY KEY,
            nama_toko VARCHAR(100) NOT NULL DEFAULT 'UD. BERSAUDARA',
            alamat_toko TEXT,
            nomor_telepon VARCHAR(20),
            email_toko VARCHAR(50),
            footer_nota TEXT,
            lebar_kertas INT DEFAULT 80,
            margin_nota_atas NUMERIC(5,2) DEFAULT 1.50,
            margin_nota_kanan NUMERIC(5,2) DEFAULT 1.50,
            margin_nota_bawah NUMERIC(5,2) DEFAULT 1.50,
            margin_nota_kiri NUMERIC(5,2) DEFAULT 1.50,
            font_size_nota_body SMALLINT DEFAULT 10,
            font_size_nota_judul SMALLINT DEFAULT 14,
            font_size_nota_info SMALLINT DEFAULT 10,
            font_size_nota_tabel SMALLINT DEFAULT 10,
            font_size_nota_ringkasan SMALLINT DEFAULT 10,
            font_size_nota_footer SMALLINT DEFAULT 9,
            tampilkan_jam SMALLINT DEFAULT 1,
            tampilkan_kode_barang SMALLINT DEFAULT 1,
            tampilkan_satuan SMALLINT DEFAULT 1,
            jumlah_diskon_terpisah SMALLINT DEFAULT 0,
            custom_header_text TEXT,
            custom_footer_text TEXT,
            tampilkan_nama_pembeli SMALLINT DEFAULT 1,
            tampilkan_info_hutang SMALLINT DEFAULT 1,
            font_nota VARCHAR(50) DEFAULT 'Arial',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($createSql);

        // Tambah kolom baru jika tabel sudah ada
        $alterColumns = [
            "ADD COLUMN IF NOT EXISTS margin_nota_atas NUMERIC(5,2) DEFAULT 1.50",
            "ADD COLUMN IF NOT EXISTS margin_nota_kanan NUMERIC(5,2) DEFAULT 1.50",
            "ADD COLUMN IF NOT EXISTS margin_nota_bawah NUMERIC(5,2) DEFAULT 1.50",
            "ADD COLUMN IF NOT EXISTS margin_nota_kiri NUMERIC(5,2) DEFAULT 1.50",
            "ADD COLUMN IF NOT EXISTS font_size_nota_body SMALLINT DEFAULT 10",
            "ADD COLUMN IF NOT EXISTS font_size_nota_judul SMALLINT DEFAULT 14",
            "ADD COLUMN IF NOT EXISTS font_size_nota_info SMALLINT DEFAULT 10",
            "ADD COLUMN IF NOT EXISTS font_size_nota_tabel SMALLINT DEFAULT 10",
            "ADD COLUMN IF NOT EXISTS font_size_nota_ringkasan SMALLINT DEFAULT 10",
            "ADD COLUMN IF NOT EXISTS font_size_nota_footer SMALLINT DEFAULT 9",
            "ADD COLUMN IF NOT EXISTS custom_header_text TEXT",
            "ADD COLUMN IF NOT EXISTS custom_footer_text TEXT",
            "ADD COLUMN IF NOT EXISTS tampilkan_nama_pembeli SMALLINT DEFAULT 1",
            "ADD COLUMN IF NOT EXISTS tampilkan_info_hutang SMALLINT DEFAULT 1",
            "ADD COLUMN IF NOT EXISTS font_nota VARCHAR(50) DEFAULT 'Arial'"
        ];
        foreach ($alterColumns as $alter) {
            try {
                $this->conn->exec("ALTER TABLE konfigurasi_nota {$alter}");
            } catch (Exception $e) {
                // abaikan jika sudah ada
            }
        }

        $checkStmt = $this->conn->query("SELECT COUNT(*) AS cnt FROM konfigurasi_nota");
        $row = $checkStmt ? $checkStmt->fetch(PDO::FETCH_ASSOC) : ['cnt' => 0];
        if ((int)($row['cnt'] ?? 0) === 0) {
            $insertSql = "INSERT INTO konfigurasi_nota (nama_toko, alamat_toko, nomor_telepon, footer_nota)
                          VALUES ('UD. BERSAUDARA', 'Jalan Merdeka No. 123', '0812-3456-7890', 'Terima kasih atas pembelian Anda!')";
            $this->conn->exec($insertSql);
        }
    }

    public function getConfig() {
        $stmt = $this->conn->prepare("SELECT * FROM konfigurasi_nota LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateConfig($data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE konfigurasi_nota SET " . implode(', ', $fields) . " WHERE id_config = 1";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($values);
    }
}
?>
