<?php

require_once __DIR__ . '/../config/database.php';

class Laporan {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
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
        $query = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori ORDER BY b.stok ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getLaporanStokRange($start, $end) {
        $query = "SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE DATE(b.updated_at) BETWEEN :start AND :end ORDER BY b.stok ASC";
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
                                        dp.jumlah,
                                        b.harga_beli,
                                        dp.harga_satuan as harga_jual,
                                        (dp.harga_satuan - b.harga_beli) as keuntungan_per_unit,
                                        ((dp.harga_satuan - b.harga_beli) * dp.jumlah - dp.diskon) as keuntungan_total,
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
                                        SUM((dp.harga_satuan - b.harga_beli) * dp.jumlah - dp.diskon) as total
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

    public function getDashboardStats() {
        // Total Barang Terjual Hari Ini
        $queryBarangTerjual = "SELECT COALESCE(SUM(dp.jumlah), 0) as total 
                               FROM detail_penjualan dp
                               JOIN penjualan p ON dp.id_penjualan = p.id_penjualan
                               WHERE DATE(p.tanggal) = CURRENT_DATE";
        $stmtBarangTerjual = $this->conn->prepare($queryBarangTerjual);
        $stmtBarangTerjual->execute();
        $barangTerjualHariIni = $stmtBarangTerjual->fetch()['total'] ?? 0;

        // Total Stok
        $queryStok = "SELECT SUM(stok) as total FROM barang";
        $stmtStok = $this->conn->prepare($queryStok);
        $stmtStok->execute();
        $totalStok = $stmtStok->fetch()['total'] ?? 0;

        // Total Penjualan Hari Ini
        $queryPenjualan = "SELECT SUM(total_harga) as total FROM penjualan WHERE DATE(tanggal) = CURRENT_DATE";
        $stmtPenjualan = $this->conn->prepare($queryPenjualan);
        $stmtPenjualan->execute();
        $totalPenjualanHariIni = $stmtPenjualan->fetch()['total'] ?? 0;

        // Total Pembelian Hari Ini
        $queryPembelian = "SELECT SUM(total_harga) as total FROM pembelian WHERE DATE(tanggal) = CURRENT_DATE";
        $stmtPembelian = $this->conn->prepare($queryPembelian);
        $stmtPembelian->execute();
        $totalPembelianHariIni = $stmtPembelian->fetch()['total'] ?? 0;

        return [
            'barang_terjual_hari_ini' => $barangTerjualHariIni,
            'total_stok' => $totalStok,
            'penjualan_hari_ini' => $totalPenjualanHariIni,
            'pembelian_hari_ini' => $totalPembelianHariIni
        ];
    }

    public function getPenjualanTrend($days = 7) {
        $query = "SELECT DATE(tanggal) as tanggal, SUM(total_harga) as total
              FROM penjualan
              WHERE tanggal >= (CURRENT_DATE - (:days * INTERVAL '1 day'))
              GROUP BY DATE(tanggal)
              ORDER BY DATE(tanggal) ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
