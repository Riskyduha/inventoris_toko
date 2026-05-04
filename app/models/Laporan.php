<?php

require_once __DIR__ . '/../config/database.php';

class Laporan {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureProfitSnapshotColumn();
        $this->ensureInventoryAlertColumns();
    }

    private function ensureProfitSnapshotColumn(): void {
        try {
            $this->conn->exec("ALTER TABLE detail_penjualan ADD COLUMN IF NOT EXISTS harga_beli_saat_transaksi NUMERIC(12,2) DEFAULT 0");
            $this->conn->exec("UPDATE detail_penjualan dp
                               SET harga_beli_saat_transaksi = COALESCE(b.harga_beli, 0)
                               FROM barang b
                               WHERE dp.id_barang = b.id_barang
                                 AND (dp.harga_beli_saat_transaksi IS NULL OR dp.harga_beli_saat_transaksi = 0)");
        } catch (Exception $e) {
            error_log('ensureProfitSnapshotColumn error: ' . $e->getMessage());
        }
    }

    private function ensureInventoryAlertColumns(): void {
        try {
            $this->conn->exec("ALTER TABLE barang ADD COLUMN IF NOT EXISTS stok_minimum INTEGER DEFAULT 0");
            $this->conn->exec("ALTER TABLE barang ADD COLUMN IF NOT EXISTS tanggal_expired DATE NULL");
        } catch (Exception $e) {
            error_log('ensureInventoryAlertColumns error: ' . $e->getMessage());
        }
    }

    public function getLaporanPembelian($start, $end) {
        $query = "SELECT 
                    p.tanggal,
                    dp.id_pembelian,
                    b.kode_barang,
                    b.nama_barang,
                    b.satuan,
                    dp.jumlah,
                    dp.harga_satuan,
                    dp.diskon,
                    dp.subtotal,
                    p.total_harga
                  FROM pembelian p
                  JOIN detail_pembelian dp ON p.id_pembelian = dp.id_pembelian
                  JOIN barang b ON dp.id_barang = b.id_barang
                  WHERE DATE(p.tanggal) BETWEEN :start AND :end
                  ORDER BY p.tanggal DESC, dp.id_detail DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLaporanPenjualan($start, $end) {
        $query = "SELECT 
                    p.tanggal,
                    dp.id_penjualan,
                    u.username,
                    b.kode_barang,
                    b.nama_barang,
                    b.satuan,
                    dp.jumlah,
                    dp.harga_satuan,
                    dp.diskon,
                    dp.subtotal as total_harga,
                    p.total_harga as total_header
                  FROM penjualan p
                  JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
                  JOIN barang b ON dp.id_barang = b.id_barang
                  LEFT JOIN users u ON p.id_user = u.id_user
                  WHERE DATE(p.tanggal) BETWEEN :start AND :end
                  ORDER BY p.tanggal DESC, dp.id_detail DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLaporanStok() {
        $query = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori ORDER BY b.nama_barang ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getStokTotals() {
        $query = "SELECT
                    COALESCE(SUM(b.harga_beli * b.stok), 0) as total_harga_beli,
                    COALESCE(SUM(b.harga_jual * b.stok), 0) as total_harga_jual,
                    COALESCE(SUM(b.stok), 0) as total_stok
                  FROM barang b";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getStokTotalsByKategori() {
        $query = "SELECT
                    k.id_kategori,
                    k.nama_kategori,
                    COALESCE(SUM(b.harga_beli * b.stok), 0) as total_harga_beli,
                    COALESCE(SUM(b.harga_jual * b.stok), 0) as total_harga_jual,
                    COALESCE(SUM(b.stok), 0) as total_stok
                  FROM barang b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  GROUP BY k.id_kategori, k.nama_kategori
                  ORDER BY k.nama_kategori ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLaporanStokRange($start, $end) {
        $query = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE DATE(b.updated_at) BETWEEN :start AND :end ORDER BY b.nama_barang ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll();
    }

        public function getLaporanKeuntungan($start, $end) {
                $query = "SELECT 
                                        p.id_penjualan,
                                        u.username,
                                        b.kode_barang,
                                        b.nama_barang,
                                        b.satuan,
                                        COALESCE(dp.jumlah, 0) as jumlah,
                                        COALESCE(dp.harga_beli_saat_transaksi, b.harga_beli, 0) as harga_beli,
                                        COALESCE(dp.harga_satuan, 0) as harga_jual,
                                        (COALESCE(dp.harga_satuan, 0) - COALESCE(dp.harga_beli_saat_transaksi, b.harga_beli, 0)) as keuntungan_per_unit,
                                        (COALESCE(dp.subtotal, (COALESCE(dp.harga_satuan, 0) * COALESCE(dp.jumlah, 0)) - COALESCE(dp.diskon, 0))
                                            - (COALESCE(dp.harga_beli_saat_transaksi, b.harga_beli, 0) * COALESCE(dp.jumlah, 0))) as keuntungan_total,
                                        p.tanggal
                                    FROM penjualan p
                                    JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
                                    JOIN barang b ON dp.id_barang = b.id_barang
                                    LEFT JOIN users u ON p.id_user = u.id_user
                                    WHERE DATE(p.tanggal) BETWEEN :start AND :end
                                    ORDER BY p.tanggal DESC, dp.id_detail DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getBarangStokMenipis($batas = 10) {
        $query = "SELECT id_barang, nama_barang, stok, satuan FROM barang WHERE stok <= :batas ORDER BY stok ASC LIMIT 10";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':batas', $batas, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalPembelian($start, $end) {
        $query = "SELECT SUM(dp.subtotal) as total 
                  FROM pembelian p
                  JOIN detail_pembelian dp ON p.id_pembelian = dp.id_pembelian
                  WHERE DATE(p.tanggal) BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalPenjualan($start, $end) {
        $query = "SELECT SUM(dp.subtotal) as total 
                  FROM penjualan p
                  JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
                  WHERE DATE(p.tanggal) BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

        public function getTotalKeuntungan($start, $end) {
                $query = "SELECT 
                                        COALESCE(SUM(
                                            (COALESCE(dp.subtotal, (COALESCE(dp.harga_satuan, 0) * COALESCE(dp.jumlah, 0)) - COALESCE(dp.diskon, 0))
                                                - (COALESCE(dp.harga_beli_saat_transaksi, b.harga_beli, 0) * COALESCE(dp.jumlah, 0)))
                                        ), 0) as total
                                    FROM penjualan p
                                    JOIN detail_penjualan dp ON p.id_penjualan = dp.id_penjualan
                                    JOIN barang b ON dp.id_barang = b.id_barang
                                    WHERE DATE(p.tanggal) BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getDashboardStats(int $days = 1) {
        $days = max(1, $days);
        $today = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        // Total Barang Terjual Hari Ini
        $queryBarangTerjual = "SELECT COALESCE(SUM(dp.jumlah), 0) as total 
                               FROM detail_penjualan dp
                               JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
                               WHERE DATE(p.tanggal) BETWEEN :start_date AND :today";
        $stmtBarangTerjual = $this->conn->prepare($queryBarangTerjual);
        $stmtBarangTerjual->bindParam(':start_date', $startDate);
        $stmtBarangTerjual->bindParam(':today', $today);
        $stmtBarangTerjual->execute();
        $barangTerjualHariIni = $stmtBarangTerjual->fetch()['total'] ?? 0;

        // Total Stok
        $queryStok = "SELECT SUM(stok) as total FROM barang";
        $stmtStok = $this->conn->prepare($queryStok);
        $stmtStok->execute();
        $totalStok = $stmtStok->fetch()['total'] ?? 0;

        // Total Nilai Persediaan (Harga Beli & Jual)
        $queryNilaiPersediaan = "SELECT
                        COALESCE(SUM(harga_beli * stok), 0) as total_harga_beli,
                        COALESCE(SUM(harga_jual * stok), 0) as total_harga_jual
                     FROM barang";
        $stmtNilaiPersediaan = $this->conn->prepare($queryNilaiPersediaan);
        $stmtNilaiPersediaan->execute();
        $nilaiPersediaan = $stmtNilaiPersediaan->fetch() ?: [];

        // Total Penjualan Hari Ini
        $queryPenjualan = "SELECT SUM(total_harga) as total FROM penjualan WHERE DATE(tanggal) BETWEEN :start_date AND :today";
        $stmtPenjualan = $this->conn->prepare($queryPenjualan);
        $stmtPenjualan->bindParam(':start_date', $startDate);
        $stmtPenjualan->bindParam(':today', $today);
        $stmtPenjualan->execute();
        $totalPenjualanHariIni = $stmtPenjualan->fetch()['total'] ?? 0;

        // Total Pembelian Hari Ini
        $queryPembelian = "SELECT SUM(total_harga) as total FROM pembelian WHERE DATE(tanggal) BETWEEN :start_date AND :today";
        $stmtPembelian = $this->conn->prepare($queryPembelian);
        $stmtPembelian->bindParam(':start_date', $startDate);
        $stmtPembelian->bindParam(':today', $today);
        $stmtPembelian->execute();
        $totalPembelianHariIni = $stmtPembelian->fetch()['total'] ?? 0;

        // Laba Bersih Hari Ini
        $queryLabaBersih = "SELECT COALESCE(SUM(((dp.harga_satuan - COALESCE(dp.harga_beli_saat_transaksi, b.harga_beli, 0)) * dp.jumlah) - COALESCE(dp.diskon, 0)), 0) as total
                            FROM detail_penjualan dp
                            JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
                            JOIN barang b ON dp.id_barang = b.id_barang
                            WHERE DATE(p.tanggal) BETWEEN :start_date AND :today";
        $stmtLabaBersih = $this->conn->prepare($queryLabaBersih);
        $stmtLabaBersih->bindParam(':start_date', $startDate);
        $stmtLabaBersih->bindParam(':today', $today);
        $stmtLabaBersih->execute();
        $labaBersihHariIni = $stmtLabaBersih->fetch()['total'] ?? 0;

        return [
            'barang_terjual_hari_ini' => $barangTerjualHariIni,
            'total_stok' => $totalStok,
            'total_harga_beli' => $nilaiPersediaan['total_harga_beli'] ?? 0,
            'total_harga_jual' => $nilaiPersediaan['total_harga_jual'] ?? 0,
            'penjualan_hari_ini' => $totalPenjualanHariIni,
            'pembelian_hari_ini' => $totalPembelianHariIni,
            'laba_bersih_hari_ini' => $labaBersihHariIni
        ];
    }

    public function getPriorityInventoryAlerts(): array {
        $high = [];
        $medium = [];

        $queryHigh = "SELECT id_barang, nama_barang, stok, satuan, stok_minimum, tanggal_expired,
                             CASE
                                 WHEN tanggal_expired IS NOT NULL AND tanggal_expired <= CURRENT_DATE + INTERVAL '7 day' THEN 'expired_critical'
                                 WHEN stok <= COALESCE(NULLIF(stok_minimum, 0), 3) THEN 'stok_kritis'
                                 ELSE 'lainnya'
                             END AS reason
                      FROM barang
                      WHERE (tanggal_expired IS NOT NULL AND tanggal_expired <= CURRENT_DATE + INTERVAL '7 day')
                         OR stok <= COALESCE(NULLIF(stok_minimum, 0), 3)
                      ORDER BY tanggal_expired ASC NULLS LAST, stok ASC
                      LIMIT 8";
        $stmtHigh = $this->conn->prepare($queryHigh);
        $stmtHigh->execute();
        $high = $stmtHigh->fetchAll();

        $queryMedium = "SELECT id_barang, nama_barang, stok, satuan, stok_minimum, tanggal_expired,
                               CASE
                                   WHEN tanggal_expired IS NOT NULL AND tanggal_expired <= CURRENT_DATE + INTERVAL '30 day' THEN 'expired_warning'
                                   WHEN stok <= GREATEST(COALESCE(NULLIF(stok_minimum, 0), 5), 5) THEN 'stok_menipis'
                                   ELSE 'lainnya'
                               END AS reason
                        FROM barang
                        WHERE ((tanggal_expired IS NOT NULL AND tanggal_expired > CURRENT_DATE + INTERVAL '7 day' AND tanggal_expired <= CURRENT_DATE + INTERVAL '30 day')
                           OR (stok > COALESCE(NULLIF(stok_minimum, 0), 3) AND stok <= GREATEST(COALESCE(NULLIF(stok_minimum, 0), 5), 5)))
                        ORDER BY tanggal_expired ASC NULLS LAST, stok ASC
                        LIMIT 8";
        $stmtMedium = $this->conn->prepare($queryMedium);
        $stmtMedium->execute();
        $medium = $stmtMedium->fetchAll();

        return [
            'high' => $high,
            'medium' => $medium
        ];
    }

    public function getBarangAkanExpired(int $months = 3, int $limit = 50): array {
        $months = max(1, $months);
        $limit = max(1, $limit);
        $today = date('Y-m-d');

        $query = "SELECT id_barang,
                         nama_barang,
                         stok,
                         satuan,
                         tanggal_expired,
                         (tanggal_expired - :today::date) AS sisa_hari
                  FROM barang
                  WHERE tanggal_expired IS NOT NULL
                    AND tanggal_expired <= (:today::date + (:months || ' month')::interval)
                  ORDER BY tanggal_expired ASC, nama_barang ASC
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':today', $today);
        $stmt->bindValue(':months', (string)$months, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public function getPenjualanTrend($days = 7) {
        $days = max(1, (int)$days);
        $today = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));

        $query = "SELECT DATE(tanggal) as tanggal, SUM(total_harga) as total
              FROM penjualan
              WHERE DATE(tanggal) BETWEEN :start_date AND :today
              GROUP BY DATE(tanggal)
              ORDER BY DATE(tanggal) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':today', $today);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
