<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/InventoryBatch.php';
require_once __DIR__ . '/AuditTrail.php';

class Pembelian {
    private $conn;
    private $table = 'pembelian';
    private $detail_table = 'detail_pembelian';
    private $batchModel;
    private $auditTrail;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->batchModel = new InventoryBatch($this->conn);
        $this->auditTrail = new AuditTrail($this->conn);
    }

    public function getAll() {
        $query = "SELECT p.*, 
                  string_agg(DISTINCT b.nama_barang, ', ') as barang_list,
                  COUNT(dp.id_detail) as jumlah_item
                  FROM " . $this->table . " p
                  LEFT JOIN " . $this->detail_table . " dp ON p.id_pembelian = dp.id_pembelian
                  LEFT JOIN barang b ON dp.id_barang = b.id_barang
                  GROUP BY p.id_pembelian
                  ORDER BY p.tanggal DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT p.* FROM " . $this->table . " p WHERE p.id_pembelian = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getDetailById($id) {
        $query = "SELECT dp.*, b.nama_barang, b.satuan 
                  FROM " . $this->detail_table . " dp
                  JOIN barang b ON dp.id_barang = b.id_barang
                  WHERE dp.id_pembelian = :id
                  ORDER BY dp.id_detail ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getExportDetailAll() {
        $query = "SELECT
                    p.id_pembelian,
                    p.tanggal,
                    p.nama_pembeli,
                    p.total_harga,
                    p.kembalian,
                    dp.id_detail,
                    b.kode_barang,
                    b.nama_barang,
                    b.satuan,
                    dp.jumlah,
                    dp.harga_satuan,
                    COALESCE(dp.diskon, 0) as diskon,
                    COALESCE(dp.subtotal, 0) as subtotal
                  FROM pembelian p
                  JOIN detail_pembelian dp ON p.id_pembelian = dp.id_pembelian
                  JOIN barang b ON dp.id_barang = b.id_barang
                  ORDER BY p.tanggal DESC, p.id_pembelian DESC, dp.id_detail ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data) {
        try {
            $this->conn->beginTransaction();

            // Calculate total from items
            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['harga_satuan'] * $item['jumlah'];
                $total += $subtotal;
            }

            $uang_diberikan = $data['uang_diberikan'] ?? 0;
            $kembalian = $uang_diberikan - $total;

            // Insert pembelian header
            $query = "INSERT INTO " . $this->table . " 
                      (total_harga, uang_diberikan, kembalian, nama_pembeli, keterangan) 
                      VALUES (:total_harga, :uang_diberikan, :kembalian, :nama_pembeli, :keterangan)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':total_harga', $total);
            $stmt->bindParam(':uang_diberikan', $uang_diberikan);
            $stmt->bindParam(':kembalian', $kembalian);
            $stmt->bindParam(':nama_pembeli', $data['nama_pembeli']);
            $stmt->bindParam(':keterangan', $data['keterangan']);
            $stmt->execute();

            $id_pembelian = $this->conn->lastInsertId();

            // Insert detail items
            foreach ($data['items'] as $item) {
                $subtotal = $item['harga_satuan'] * $item['jumlah'];
                
                $queryDetail = "INSERT INTO " . $this->detail_table . " 
                               (id_pembelian, id_barang, jumlah, harga_satuan, diskon, subtotal)
                               VALUES (:id_pembelian, :id_barang, :jumlah, :harga_satuan, :diskon, :subtotal)";
                
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->bindParam(':id_pembelian', $id_pembelian);
                $stmtDetail->bindParam(':id_barang', $item['id_barang']);
                $stmtDetail->bindParam(':jumlah', $item['jumlah']);
                $stmtDetail->bindParam(':harga_satuan', $item['harga_satuan']);
                $diskon = 0;
                $stmtDetail->bindParam(':diskon', $diskon);
                $stmtDetail->bindParam(':subtotal', $subtotal);
                $stmtDetail->execute();
                $idDetailPembelian = (int)$this->conn->lastInsertId();

                $this->batchModel->createBatchFromPurchaseDetail(
                    (int)$id_pembelian,
                    $idDetailPembelian,
                    (int)$item['id_barang'],
                    (float)$item['jumlah'],
                    (float)$item['harga_satuan'],
                    null
                );

                // Update stok barang (tambah)
                $queryStok = "UPDATE barang
                              SET stok = stok + :jumlah,
                                  stok_updated_by = :updated_by,
                                  updated_at = NOW()
                              WHERE id_barang = :id_barang";
                $stmtStok = $this->conn->prepare($queryStok);
                $stmtStok->bindParam(':jumlah', $item['jumlah']);
                $stmtStok->bindParam(':id_barang', $item['id_barang']);
                $updatedBy = isset($data['id_user']) && $data['id_user'] !== '' ? (int)$data['id_user'] : null;
                $stmtStok->bindValue(':updated_by', $updatedBy, $updatedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtStok->execute();
            }

            $this->conn->commit();
            $this->auditTrail->log([
                'modul' => 'pembelian',
                'aksi' => 'create',
                'entitas' => 'pembelian',
                'id_entitas' => (string)$id_pembelian,
                'deskripsi' => 'Membuat transaksi pembelian baru',
                'data_baru' => ['header' => $data, 'total_harga' => $total],
                'id_user' => $data['id_user'] ?? null
            ]);
            return ['success' => true, 'message' => 'Pembelian berhasil', 'id' => $id_pembelian];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function update($id, $data) {
        try {
            $this->conn->beginTransaction();
            $headerLama = $this->getById($id);

            // Get old details
            $queryOld = "SELECT * FROM " . $this->detail_table . " WHERE id_pembelian = :id";
            $stmtOld = $this->conn->prepare($queryOld);
            $stmtOld->bindParam(':id', $id);
            $stmtOld->execute();
            $oldDetails = $stmtOld->fetchAll();

            // Restore old stock (reverse the addition)
            foreach ($oldDetails as $oldItem) {
                $queryRestore = "UPDATE barang
                                 SET stok = stok - :jumlah,
                                     stok_updated_by = :updated_by,
                                     updated_at = NOW()
                                 WHERE id_barang = :id_barang";
                $stmtRestore = $this->conn->prepare($queryRestore);
                $stmtRestore->bindParam(':jumlah', $oldItem['jumlah']);
                $stmtRestore->bindParam(':id_barang', $oldItem['id_barang']);
                $updatedBy = isset($data['id_user']) && $data['id_user'] !== '' ? (int)$data['id_user'] : null;
                $stmtRestore->bindValue(':updated_by', $updatedBy, $updatedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtRestore->execute();
            }

            if (!$this->batchModel->canModifyPurchase((int)$id)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Pembelian tidak bisa diubah karena batch modal sudah dipakai di transaksi penjualan.'];
            }

            // Calculate new total
            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = $item['harga_satuan'] * $item['jumlah'];
                $total += $subtotal;
            }

            $uang_diberikan = $data['uang_diberikan'] ?? 0;
            $kembalian = $uang_diberikan - $total;

            // Delete old details
            $queryDelete = "DELETE FROM " . $this->detail_table . " WHERE id_pembelian = :id";
            $stmtDelete = $this->conn->prepare($queryDelete);
            $stmtDelete->bindParam(':id', $id);
            $stmtDelete->execute();

            // Update pembelian header
            $query = "UPDATE " . $this->table . " 
                      SET total_harga = :total_harga,
                          uang_diberikan = :uang_diberikan,
                          kembalian = :kembalian,
                          nama_pembeli = :nama_pembeli,
                          keterangan = :keterangan
                      WHERE id_pembelian = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':total_harga', $total);
            $stmt->bindParam(':uang_diberikan', $uang_diberikan);
            $stmt->bindParam(':kembalian', $kembalian);
            $stmt->bindParam(':nama_pembeli', $data['nama_pembeli']);
            $stmt->bindParam(':keterangan', $data['keterangan']);
            $stmt->execute();

            // Insert new details
            $detailBaru = [];
            foreach ($data['items'] as $item) {
                $subtotal = $item['harga_satuan'] * $item['jumlah'];
                
                $queryDetail = "INSERT INTO " . $this->detail_table . " 
                               (id_pembelian, id_barang, jumlah, harga_satuan, diskon, subtotal)
                               VALUES (:id_pembelian, :id_barang, :jumlah, :harga_satuan, :diskon, :subtotal)";
                
                $stmtDetail = $this->conn->prepare($queryDetail);
                $stmtDetail->bindParam(':id_pembelian', $id);
                $stmtDetail->bindParam(':id_barang', $item['id_barang']);
                $stmtDetail->bindParam(':jumlah', $item['jumlah']);
                $stmtDetail->bindParam(':harga_satuan', $item['harga_satuan']);
                $diskon = 0;
                $stmtDetail->bindParam(':diskon', $diskon);
                $stmtDetail->bindParam(':subtotal', $subtotal);
                $stmtDetail->execute();
                $idDetailPembelian = (int)$this->conn->lastInsertId();
                $detailBaru[] = [
                    'id_detail' => $idDetailPembelian,
                    'id_barang' => (int)$item['id_barang'],
                    'jumlah' => (float)$item['jumlah'],
                    'harga_satuan' => (float)$item['harga_satuan']
                ];

                // Update stok barang (tambah)
                $queryStok = "UPDATE barang
                              SET stok = stok + :jumlah,
                                  stok_updated_by = :updated_by,
                                  updated_at = NOW()
                              WHERE id_barang = :id_barang";
                $stmtStok = $this->conn->prepare($queryStok);
                $stmtStok->bindParam(':jumlah', $item['jumlah']);
                $stmtStok->bindParam(':id_barang', $item['id_barang']);
                $updatedBy = isset($data['id_user']) && $data['id_user'] !== '' ? (int)$data['id_user'] : null;
                $stmtStok->bindValue(':updated_by', $updatedBy, $updatedBy === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtStok->execute();
            }

            $this->batchModel->replacePurchaseBatches((int)$id, $detailBaru, null);

            $this->conn->commit();
            $this->auditTrail->log([
                'modul' => 'pembelian',
                'aksi' => 'update',
                'entitas' => 'pembelian',
                'id_entitas' => (string)$id,
                'deskripsi' => 'Mengubah transaksi pembelian',
                'data_lama' => ['header' => $headerLama, 'detail' => $oldDetails],
                'data_baru' => ['header' => $data, 'total_harga' => $total],
                'id_user' => $data['id_user'] ?? null
            ]);
            return ['success' => true, 'message' => 'Pembelian berhasil diupdate'];
        } catch(Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    // Update hanya nama supplier tanpa mengubah detail dan stok
    public function updateSupplierOnly($id, $nama_pembeli) {
        try {
            $query = "UPDATE " . $this->table . " SET nama_pembeli = :nama_pembeli WHERE id_pembelian = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nama_pembeli', $nama_pembeli);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return ['success' => true, 'message' => 'Nama supplier berhasil diperbarui'];
        } catch(Exception $e) {
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }

    public function delete($id, $updatedBy = null) {
        try {
            $this->conn->beginTransaction();
            $headerLama = $this->getById($id);

            // Get details
            $queryDetails = "SELECT * FROM " . $this->detail_table . " WHERE id_pembelian = :id";
            $stmtDetails = $this->conn->prepare($queryDetails);
            $stmtDetails->bindParam(':id', $id);
            $stmtDetails->execute();
            $details = $stmtDetails->fetchAll();

            if (!$this->batchModel->canModifyPurchase((int)$id)) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Pembelian tidak bisa dihapus karena batch modal sudah dipakai di transaksi penjualan.'];
            }

            // Restore all stock (reverse the addition)
            foreach ($details as $detail) {
                $queryRestore = "UPDATE barang
                                 SET stok = stok - :jumlah,
                                     stok_updated_by = :updated_by,
                                     updated_at = NOW()
                                 WHERE id_barang = :id_barang";
                $stmtRestore = $this->conn->prepare($queryRestore);
                $stmtRestore->bindParam(':jumlah', $detail['jumlah']);
                $stmtRestore->bindParam(':id_barang', $detail['id_barang']);
                $updatedByValue = $updatedBy !== null && $updatedBy !== '' ? (int)$updatedBy : null;
                $stmtRestore->bindValue(':updated_by', $updatedByValue, $updatedByValue === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
                $stmtRestore->execute();
            }

            // Delete details first
            $queryDeleteDetail = "DELETE FROM " . $this->detail_table . " WHERE id_pembelian = :id";
            $stmtDeleteDetail = $this->conn->prepare($queryDeleteDetail);
            $stmtDeleteDetail->bindParam(':id', $id);
            $stmtDeleteDetail->execute();

            // Delete pembelian
            $query = "DELETE FROM " . $this->table . " WHERE id_pembelian = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $this->conn->commit();
            $this->auditTrail->log([
                'modul' => 'pembelian',
                'aksi' => 'delete',
                'entitas' => 'pembelian',
                'id_entitas' => (string)$id,
                'deskripsi' => 'Menghapus transaksi pembelian',
                'data_lama' => ['header' => $headerLama, 'detail' => $details],
                'id_user' => $updatedBy
            ]);
            return ['success' => true, 'message' => 'Pembelian berhasil dihapus'];
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
                  LEFT JOIN " . $this->detail_table . " dp ON p.id_pembelian = dp.id_pembelian
                  LEFT JOIN barang b ON dp.id_barang = b.id_barang
                  WHERE DATE(p.tanggal) BETWEEN :start AND :end
                  GROUP BY p.id_pembelian
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
