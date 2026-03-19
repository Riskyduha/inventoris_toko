<?php

require_once __DIR__ . '/../config/database.php';

class Penjualan {
    private $conn;
    private $table = 'penjualan';
    private $detail_table = 'detail_penjualan';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureTableStructure();
    }

    private function ensureTableStructure() {
        try {
            // Ensure ada_hutang column exists in penjualan table
            $checkColumn = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
                           WHERE TABLE_NAME = 'penjualan' AND COLUMN_NAME = 'ada_hutang'";
            $stmt = $this->conn->prepare($checkColumn);
            $stmt->execute();
            
            if ($stmt->rowCount() === 0) {
                // Add column if it doesn't exist
                $this->conn->exec("ALTER TABLE penjualan ADD COLUMN ada_hutang TINYINT DEFAULT 0");
            }
            
            // Ensure hutang table exists
            $createSql = "CREATE TABLE IF NOT EXISTS hutang (
                id_hutang INT AUTO_INCREMENT PRIMARY KEY,
                id_penjualan INT NOT NULL,
                nama_penghutang VARCHAR(100) NOT NULL,
                jumlah_hutang DECIMAL(12,2) NOT NULL,
                jatuh_tempo DATE NOT NULL,
                status ENUM('belum_lunas', 'lunas') DEFAULT 'belum_lunas',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (id_penjualan) REFERENCES penjualan(id_penjualan) ON DELETE CASCADE
            )";
            $this->conn->exec($createSql);
        } catch (Exception $e) {
            // Silently continue if there's an issue
            error_log('ensureTableStructure error: ' . $e->getMessage());
        }
    }

    public function getAll() {
        $query = "SELECT p.*, 
              string_agg(DISTINCT b.nama_barang, ', ') as barang_list,
              COUNT(dp.id_detail) as jumlah_item,
              MAX(h.id_hutang) as id_hutang,
              MAX(h.status) as hutang_status
              FROM " . $this->table . " p
              LEFT JOIN " . $this->detail_table . " dp ON p.id_penjualan = dp.id_penjualan
              LEFT JOIN barang b ON dp.id_barang = b.id_barang
              LEFT JOIN hutang h ON p.id_penjualan = h.id_penjualan
              GROUP BY p.id_penjualan
              ORDER BY p.tanggal DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllWithPagination($offset, $limit) {
        $query = "SELECT p.*, 
              string_agg(DISTINCT b.nama_barang, ', ') as barang_list,
              COUNT(dp.id_detail) as jumlah_item,
              MAX(h.id_hutang) as id_hutang,
              MAX(h.status) as hutang_status
              FROM " . $this->table . " p
              LEFT JOIN " . $this->detail_table . " dp ON p.id_penjualan = dp.id_penjualan
              LEFT JOIN barang b ON dp.id_barang = b.id_barang
              LEFT JOIN hutang h ON p.id_penjualan = h.id_penjualan
              GROUP BY p.id_penjualan
              ORDER BY p.tanggal DESC
              LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByDateRange($tanggal_awal, $tanggal_akhir) {
        $query = "SELECT p.*, 
              string_agg(DISTINCT b.nama_barang, ', ') as barang_list,
              COUNT(dp.id_detail) as jumlah_item,
              MAX(h.id_hutang) as id_hutang,
              MAX(h.status) as hutang_status
              FROM " . $this->table . " p
              LEFT JOIN " . $this->detail_table . " dp ON p.id_penjualan = dp.id_penjualan
              LEFT JOIN barang b ON dp.id_barang = b.id_barang
              LEFT JOIN hutang h ON p.id_penjualan = h.id_penjualan
              WHERE DATE(p.tanggal) >= :tanggal_awal AND DATE(p.tanggal) <= :tanggal_akhir
              GROUP BY p.id_penjualan
              ORDER BY p.tanggal DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal_awal', $tanggal_awal);
        $stmt->bindParam(':tanggal_akhir', $tanggal_akhir);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getByDateRangeWithPagination($tanggal_awal, $tanggal_akhir, $offset, $limit) {
        $query = "SELECT p.*, 
              string_agg(DISTINCT b.nama_barang, ', ') as barang_list,
              COUNT(dp.id_detail) as jumlah_item,
              MAX(h.id_hutang) as id_hutang,
              MAX(h.status) as hutang_status
              FROM " . $this->table . " p
              LEFT JOIN " . $this->detail_table . " dp ON p.id_penjualan = dp.id_penjualan
              LEFT JOIN barang b ON dp.id_barang = b.id_barang
              LEFT JOIN hutang h ON p.id_penjualan = h.id_penjualan
              WHERE DATE(p.tanggal) >= :tanggal_awal AND DATE(p.tanggal) <= :tanggal_akhir
              GROUP BY p.id_penjualan
              ORDER BY p.tanggal DESC
              LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tanggal_awal', $tanggal_awal);
        $stmt->bindParam(':tanggal_akhir', $tanggal_akhir);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT p.* FROM " . $this->table . " p WHERE p.id_penjualan = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getDetailById($id) {
        $query = "SELECT dp.*, b.nama_barang, b.kode_barang, b.satuan 
                  FROM " . $this->detail_table . " dp
                  JOIN barang b ON dp.id_barang = b.id_barang
                  WHERE dp.id_penjualan = :id
                  ORDER BY dp.id_detail ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();

            // Validate all items and check stock
            $total = 0;
            foreach ($data['items'] as $item) {
                $queryCheck = "SELECT stok FROM barang WHERE id_barang = :id_barang";
                $stmtCheck = $this->conn->prepare($queryCheck);
                $stmtCheck->bindParam(':id_barang', $item['id_barang']);
                $stmtCheck->execute();
                $barang = $stmtCheck->fetch();

                if (!$barang || $barang['stok'] < $item['jumlah']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Stok tidak mencukupi untuk salah satu barang'];
                }

                $subtotal = ($item['harga_satuan'] * $item['jumlah']) - $item['diskon'];
                $total += $subtotal;
            }

            $uang_diberikan = $data['uang_diberikan'] ?? 0;
            $kembalian = $uang_diberikan - $total;
            $ada_hutang = $data['ada_hutang'] ?? 0;

            // Gunakan tanggal manual jika tersedia, sisipkan waktu saat ini supaya tetap terbaca di laporan
            $tanggalInput = $data['tanggal'] ?? date('Y-m-d');
            $tanggal = date('Y-m-d H:i:s', strtotime($tanggalInput . ' ' . date('H:i:s')));

            // Jika ada hutang, gunakan nama penghutang sebagai nama pembeli
            $nama_pembeli = $data['nama_pembeli'] ?? '';
            if ($ada_hutang && isset($data['hutang']['nama_penghutang'])) {
                $nama_pembeli = $data['hutang']['nama_penghutang'];
            }

            // Insert penjualan header
            $query = "INSERT INTO " . $this->table . " 
                      (tanggal, total_harga, uang_diberikan, kembalian, nama_pembeli, ada_hutang, keterangan, id_user) 
                      VALUES (:tanggal, :total_harga, :uang_diberikan, :kembalian, :nama_pembeli, :ada_hutang, :keterangan, :id_user)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':total_harga', $total);
            $stmt->bindParam(':uang_diberikan', $uang_diberikan);
            $stmt->bindParam(':kembalian', $kembalian);
            $stmt->bindParam(':nama_pembeli', $nama_pembeli);
            $stmt->bindParam(':ada_hutang', $ada_hutang);
            $stmt->bindParam(':keterangan', $data['keterangan']);
            $stmt->bindParam(':id_user', $data['id_user']);
            $stmt->execute();

            $id_penjualan = $this->conn->lastInsertId();

            // Insert hutang if exists
            if ($ada_hutang && isset($data['hutang'])) {
                $queryHutang = "INSERT INTO hutang 
                               (id_penjualan, nama_penghutang, jumlah_hutang, jatuh_tempo, status)
                               VALUES (:id_penjualan, :nama_penghutang, :jumlah_hutang, :jatuh_tempo, 'belum_bayar')";
                
                $stmtHutang = $this->conn->prepare($queryHutang);
                $stmtHutang->bindParam(':id_penjualan', $id_penjualan);
                $stmtHutang->bindParam(':nama_penghutang', $data['hutang']['nama_penghutang']);
                $stmtHutang->bindParam(':jumlah_hutang', $data['hutang']['jumlah_hutang']);
                $stmtHutang->bindParam(':jatuh_tempo', $data['hutang']['jatuh_tempo']);
                $stmtHutang->execute();
            }

            // Insert detail items
            foreach ($data['items'] as $item) {
                $subtotal = ($item['harga_satuan'] * $item['jumlah']) - $item['diskon'];
                
                $queryDetail = "INSERT INTO " . $this->detail_table . " 
                               (id_penjualan, id_barang, jumlah, harga_satuan, diskon, subtotal)
                               VALUES (:id_penjualan, :id_barang, :jumlah, :harga_satuan, :diskon, :subtotal)";
                
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->bindParam(':id_penjualan', $id_penjualan);
                $stmtDetail->bindParam(':id_barang', $item['id_barang']);
                $stmtDetail->bindParam(':jumlah', $item['jumlah']);
                $stmtDetail->bindParam(':harga_satuan', $item['harga_satuan']);
                $stmtDetail->bindParam(':diskon', $item['diskon']);
                $stmtDetail->bindParam(':subtotal', $subtotal);
                $stmtDetail->execute();

                // Update stok barang (kurangi)
                $queryStok = "UPDATE barang SET stok = stok - :jumlah WHERE id_barang = :id_barang";
                $stmtStok = $this->conn->prepare($queryStok);
                $stmtStok->bindParam(':jumlah', $item['jumlah']);
                $stmtStok->bindParam(':id_barang', $item['id_barang']);
                $stmtStok->execute();
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Penjualan berhasil', 'id' => $id_penjualan];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function update($id, $data) {
        try {
            $this->conn->beginTransaction();

            // Get old details
            $queryOld = "SELECT * FROM " . $this->detail_table . " WHERE id_penjualan = :id";
            $stmtOld = $this->conn->prepare($queryOld);
            $stmtOld->bindParam(':id', $id);
            $stmtOld->execute();
            $oldDetails = $stmtOld->fetchAll();

            // Restore old stock
            foreach ($oldDetails as $oldItem) {
                $queryRestore = "UPDATE barang SET stok = stok + :jumlah WHERE id_barang = :id_barang";
                $stmtRestore = $this->conn->prepare($queryRestore);
                $stmtRestore->bindParam(':jumlah', $oldItem['jumlah']);
                $stmtRestore->bindParam(':id_barang', $oldItem['id_barang']);
                $stmtRestore->execute();
            }

            // Validate new items and check stock
            $total = 0;
            foreach ($data['items'] as $item) {
                $queryCheck = "SELECT stok FROM barang WHERE id_barang = :id_barang";
                $stmtCheck = $this->conn->prepare($queryCheck);
                $stmtCheck->bindParam(':id_barang', $item['id_barang']);
                $stmtCheck->execute();
                $barang = $stmtCheck->fetch();

                if (!$barang || $barang['stok'] < $item['jumlah']) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'Stok tidak mencukupi untuk salah satu barang'];
                }

                $subtotal = ($item['harga_satuan'] * $item['jumlah']) - $item['diskon'];
                $total += $subtotal;
            }

            $uang_diberikan = $data['uang_diberikan'] ?? 0;
            $kembalian = $uang_diberikan - $total;

            // Gunakan tanggal manual jika tersedia, sisipkan waktu saat ini supaya tetap terbaca di laporan
            $tanggalInput = $data['tanggal'] ?? date('Y-m-d');
            $tanggal = date('Y-m-d H:i:s', strtotime($tanggalInput . ' ' . date('H:i:s')));

            // Jika ada hutang, gunakan nama penghutang sebagai nama pembeli
            $nama_pembeli = $data['nama_pembeli'] ?? '';
            if (isset($data['ada_hutang']) && $data['ada_hutang'] && isset($data['hutang']['nama_penghutang'])) {
                $nama_pembeli = $data['hutang']['nama_penghutang'];
            }

            // Delete old details
            $queryDelete = "DELETE FROM " . $this->detail_table . " WHERE id_penjualan = :id";
            $stmtDelete = $this->conn->prepare($queryDelete);
            $stmtDelete->bindParam(':id', $id);
            $stmtDelete->execute();

            // Update penjualan header
            $query = "UPDATE " . $this->table . " 
                      SET total_harga = :total_harga,
                          uang_diberikan = :uang_diberikan,
                          kembalian = :kembalian,
                          tanggal = :tanggal,
                          nama_pembeli = :nama_pembeli,
                          keterangan = :keterangan
                      WHERE id_penjualan = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':total_harga', $total);
            $stmt->bindParam(':uang_diberikan', $uang_diberikan);
            $stmt->bindParam(':kembalian', $kembalian);
            $stmt->bindParam(':tanggal', $tanggal);
            $stmt->bindParam(':nama_pembeli', $nama_pembeli);
            $stmt->bindParam(':keterangan', $data['keterangan']);
            $stmt->execute();

            // Insert new details
            foreach ($data['items'] as $item) {
                $subtotal = ($item['harga_satuan'] * $item['jumlah']) - $item['diskon'];
                
                $queryDetail = "INSERT INTO " . $this->detail_table . " 
                               (id_penjualan, id_barang, jumlah, harga_satuan, diskon, subtotal)
                               VALUES (:id_penjualan, :id_barang, :jumlah, :harga_satuan, :diskon, :subtotal)";
                
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->bindParam(':id_penjualan', $id);
                $stmtDetail->bindParam(':id_barang', $item['id_barang']);
                $stmtDetail->bindParam(':jumlah', $item['jumlah']);
                $stmtDetail->bindParam(':harga_satuan', $item['harga_satuan']);
                $stmtDetail->bindParam(':diskon', $item['diskon']);
                $stmtDetail->bindParam(':subtotal', $subtotal);
                $stmtDetail->execute();

                // Update stok barang (kurangi)
                $queryStok = "UPDATE barang SET stok = stok - :jumlah WHERE id_barang = :id_barang";
                $stmtStok = $this->conn->prepare($queryStok);
                $stmtStok->bindParam(':jumlah', $item['jumlah']);
                $stmtStok->bindParam(':id_barang', $item['id_barang']);
                $stmtStok->execute();
            }

            // Handle hutang
            if (isset($data['ada_hutang']) && $data['ada_hutang'] && isset($data['hutang'])) {
                // Check if hutang record exists
                $queryCheckHutang = "SELECT id_hutang FROM hutang WHERE id_penjualan = :id_penjualan";
                $stmtCheckHutang = $this->conn->prepare($queryCheckHutang);
                $stmtCheckHutang->bindParam(':id_penjualan', $id);
                $stmtCheckHutang->execute();
                $hutangExists = $stmtCheckHutang->fetch();

                if ($hutangExists) {
                    // Update existing hutang
                    $queryUpdateHutang = "UPDATE hutang SET 
                                         nama_penghutang = :nama_penghutang,
                                         jumlah_hutang = :jumlah_hutang,
                                         jatuh_tempo = :jatuh_tempo
                                         WHERE id_penjualan = :id_penjualan";
                    $stmtUpdateHutang = $this->conn->prepare($queryUpdateHutang);
                    $stmtUpdateHutang->bindParam(':id_penjualan', $id);
                    $stmtUpdateHutang->bindParam(':nama_penghutang', $data['hutang']['nama_penghutang']);
                    $stmtUpdateHutang->bindParam(':jumlah_hutang', $data['hutang']['jumlah_hutang']);
                    $stmtUpdateHutang->bindParam(':jatuh_tempo', $data['hutang']['jatuh_tempo']);
                    $stmtUpdateHutang->execute();
                } else {
                    // Create new hutang
                    $queryCreateHutang = "INSERT INTO hutang (id_penjualan, nama_penghutang, jumlah_hutang, jatuh_tempo, status)
                                         VALUES (:id_penjualan, :nama_penghutang, :jumlah_hutang, :jatuh_tempo, 'belum_bayar')";
                    $stmtCreateHutang = $this->conn->prepare($queryCreateHutang);
                    $stmtCreateHutang->bindParam(':id_penjualan', $id);
                    $stmtCreateHutang->bindParam(':nama_penghutang', $data['hutang']['nama_penghutang']);
                    $stmtCreateHutang->bindParam(':jumlah_hutang', $data['hutang']['jumlah_hutang']);
                    $stmtCreateHutang->bindParam(':jatuh_tempo', $data['hutang']['jatuh_tempo']);
                    $stmtCreateHutang->execute();
                }
            } else {
                // Delete hutang if ada_hutang is not set
                $queryDeleteHutang = "DELETE FROM hutang WHERE id_penjualan = :id_penjualan";
                $stmtDeleteHutang = $this->conn->prepare($queryDeleteHutang);
                $stmtDeleteHutang->bindParam(':id_penjualan', $id);
                $stmtDeleteHutang->execute();
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Penjualan berhasil diupdate'];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function delete($id) {
        try {
            $this->conn->beginTransaction();

            // Get details
            $queryDetails = "SELECT * FROM " . $this->detail_table . " WHERE id_penjualan = :id";
            $stmtDetails = $this->conn->prepare($queryDetails);
            $stmtDetails->bindParam(':id', $id);
            $stmtDetails->execute();
            $details = $stmtDetails->fetchAll();

            // Restore all stock
            foreach ($details as $detail) {
                $queryRestore = "UPDATE barang SET stok = stok + :jumlah WHERE id_barang = :id_barang";
                $stmtRestore = $this->conn->prepare($queryRestore);
                $stmtRestore->bindParam(':jumlah', $detail['jumlah']);
                $stmtRestore->bindParam(':id_barang', $detail['id_barang']);
                $stmtRestore->execute();
            }

            // Delete details first
            $queryDeleteDetail = "DELETE FROM " . $this->detail_table . " WHERE id_penjualan = :id";
            $stmtDeleteDetail = $this->conn->prepare($queryDeleteDetail);
            $stmtDeleteDetail->bindParam(':id', $id);
            $stmtDeleteDetail->execute();

            // Delete penjualan
            $query = "DELETE FROM " . $this->table . " WHERE id_penjualan = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $this->conn->commit();
            return ['success' => true, 'message' => 'Penjualan berhasil dihapus'];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function getByPeriod($start, $end) {
        $query = "SELECT p.*, 
                  GROUP_CONCAT(DISTINCT b.nama_barang SEPARATOR ', ') as barang_list,
                  COUNT(dp.id_detail) as jumlah_item
                  FROM " . $this->table . " p
                  LEFT JOIN " . $this->detail_table . " dp ON p.id_penjualan = dp.id_penjualan
                  LEFT JOIN barang b ON dp.id_barang = b.id_barang
                  WHERE DATE(p.tanggal) BETWEEN :start AND :end
                  GROUP BY p.id_penjualan
                  ORDER BY p.tanggal DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getTotalByPeriod($start, $end) {
        $query = "SELECT SUM(total_harga) as total 
                  FROM " . $this->table . "
                  WHERE DATE(tanggal) BETWEEN :start AND :end";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }
}

