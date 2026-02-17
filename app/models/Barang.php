<?php

require_once __DIR__ . '/../config/database.php';

class Barang {
    private $conn;
    private $table = 'barang';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Generate incremental kode barang (BRG-001, BRG-002, ...)
    public function generateKodeBarang() {
        $query = "SELECT COALESCE(MAX(id_barang) + 1, 1) as next_id FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch();
        $nextId = isset($row['next_id']) ? (int)$row['next_id'] : 1;
        return 'BRG-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
    }

    public function getAll($kategoriId = null) {
        $where = '';
        if (!empty($kategoriId)) {
            $where = 'WHERE b.id_kategori = :id_kategori';
        }

        $query = "SELECT b.*, k.nama_kategori FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  $where
                  ORDER BY b.created_at DESC, b.id_barang DESC";
        $stmt = $this->conn->prepare($query);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Trim spasi di nama_barang dan kode_barang
        foreach ($results as &$row) {
            $row['nama_barang'] = trim($row['nama_barang']);
            $row['kode_barang'] = trim($row['kode_barang']);
        }
        return $results;
    }

    public function getAllWithPagination($offset, $limit, $kategoriId = null) {
        $where = '';
        if (!empty($kategoriId)) {
            $where = 'WHERE b.id_kategori = :id_kategori';
        }

        $query = "SELECT b.*, k.nama_kategori FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  $where
                  ORDER BY b.created_at DESC, b.id_barang DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach ($results as &$row) {
            $row['nama_barang'] = trim($row['nama_barang']);
            $row['kode_barang'] = trim($row['kode_barang']);
        }
        return $results;
    }

    public function countAll($kategoriId = null) {
        $where = '';
        if (!empty($kategoriId)) {
            $where = 'WHERE id_kategori = :id_kategori';
        }

        $query = "SELECT COUNT(*) as total FROM " . $this->table . " $where";
        $stmt = $this->conn->prepare($query);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    public function getTotals() {
                $query = "SELECT
                                        COALESCE(SUM(harga_beli * stok), 0) as total_harga_beli,
                                        COALESCE(SUM(harga_jual * stok), 0) as total_harga_jual,
                                        COALESCE(SUM(stok), 0) as total_stok
                                    FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getTotalsByKategori() {
        $query = "SELECT
                    k.id_kategori,
                    k.nama_kategori,
                    COALESCE(SUM(b.harga_beli * b.stok), 0) as total_harga_beli,
                    COALESCE(SUM(b.harga_jual * b.stok), 0) as total_harga_jual,
                    COALESCE(SUM(b.stok), 0) as total_stok
                  FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  GROUP BY k.id_kategori, k.nama_kategori
                  ORDER BY k.nama_kategori ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT b.*, k.nama_kategori FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  WHERE b.id_barang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function create($data) {
        $kodeBarang = !empty($data['kode_barang']) ? $data['kode_barang'] : $this->generateKodeBarang();
        $query = "INSERT INTO " . $this->table . " 
                  (kode_barang, nama_barang, id_kategori, satuan, harga_beli, harga_jual, stok) 
                  VALUES (:kode_barang, :nama_barang, :id_kategori, :satuan, :harga_beli, :harga_jual, :stok)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode_barang', $kodeBarang);
        $stmt->bindParam(':nama_barang', $data['nama_barang']);
        $stmt->bindParam(':id_kategori', $data['id_kategori']);
        $stmt->bindParam(':satuan', $data['satuan']);
        $stmt->bindParam(':harga_beli', $data['harga_beli']);
        $stmt->bindParam(':harga_jual', $data['harga_jual']);
        $stmt->bindParam(':stok', $data['stok']);
        
        return $stmt->execute();
    }

    public function createAndReturn($data) {
        $kodeBarang = !empty($data['kode_barang']) ? $data['kode_barang'] : $this->generateKodeBarang();
        $query = "INSERT INTO " . $this->table . " 
                  (kode_barang, nama_barang, id_kategori, satuan, harga_beli, harga_jual, stok) 
                  VALUES (:kode_barang, :nama_barang, :id_kategori, :satuan, :harga_beli, :harga_jual, :stok)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':kode_barang', $kodeBarang);
        $stmt->bindParam(':nama_barang', $data['nama_barang']);
        $stmt->bindParam(':id_kategori', $data['id_kategori']);
        $stmt->bindParam(':satuan', $data['satuan']);
        $stmt->bindParam(':harga_beli', $data['harga_beli']);
        $stmt->bindParam(':harga_jual', $data['harga_jual']);
        $stmt->bindParam(':stok', $data['stok']);

        if ($stmt->execute()) {
            $newId = $this->conn->lastInsertId();
            $row = $this->getById($newId);
            return ['success' => true, 'data' => $row];
        }

        return ['success' => false];
    }

    public function update($id, $data) {
        $kodeBarang = !empty($data['kode_barang']) ? $data['kode_barang'] : $this->generateKodeBarang();
        $query = "UPDATE " . $this->table . " 
                  SET kode_barang = :kode_barang,
                      nama_barang = :nama_barang,
                      id_kategori = :id_kategori,
                      satuan = :satuan, 
                      harga_beli = :harga_beli, 
                      harga_jual = :harga_jual, 
                      stok = :stok 
                  WHERE id_barang = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':kode_barang', $kodeBarang);
        $stmt->bindParam(':nama_barang', $data['nama_barang']);
        $stmt->bindParam(':id_kategori', $data['id_kategori']);
        $stmt->bindParam(':satuan', $data['satuan']);
        $stmt->bindParam(':harga_beli', $data['harga_beli']);
        $stmt->bindParam(':harga_jual', $data['harga_jual']);
        $stmt->bindParam(':stok', $data['stok']);
        
        return $stmt->execute();
    }

    public function updateSatuanBarang($id, $satuan) {
        $query = "UPDATE " . $this->table . " SET satuan = :satuan WHERE id_barang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':satuan', $satuan);
        return $stmt->execute();
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id_barang = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateStok($id, $jumlah) {
        $query = "UPDATE " . $this->table . " 
                  SET stok = stok + :jumlah 
                  WHERE id_barang = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':jumlah', $jumlah);
        
        return $stmt->execute();
    }

    public function getStokRendah($batas = 10) {
        $query = "SELECT * FROM " . $this->table . " WHERE stok <= :batas ORDER BY stok ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':batas', $batas);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchBarang($keyword, $kategoriId = null) {
        $where = "WHERE (nama_barang ILIKE :keyword OR kode_barang ILIKE :keyword)";
        if (!empty($kategoriId)) {
            $where .= " AND id_kategori = :id_kategori";
        }
        
        $query = "SELECT b.*, k.nama_kategori 
                  FROM " . $this->table . " b
                  LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
                  " . $where . " 
                  ORDER BY b.nama_barang ASC";
        
        $stmt = $this->conn->prepare($query);
        $keyword = '%' . $keyword . '%';
        $stmt->bindParam(':keyword', $keyword);
        if (!empty($kategoriId)) {
            $stmt->bindParam(':id_kategori', $kategoriId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $results = $stmt->fetchAll();
        
        // Trim spasi
        foreach ($results as &$row) {
            $row['nama_barang'] = trim($row['nama_barang']);
            $row['kode_barang'] = trim($row['kode_barang']);
        }
        return $results;
    }

    public function getAllKategori() {
        $query = "SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllSatuan() {
        $query = "SELECT id_satuan, nama_satuan FROM satuan ORDER BY nama_satuan ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addKategori($nama_kategori) {
        $query = "INSERT INTO kategori (nama_kategori) VALUES (:nama_kategori)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        return $stmt->execute();
    }

    public function updateKategori($id, $nama_kategori) {
        $query = "UPDATE kategori SET nama_kategori = :nama_kategori WHERE id_kategori = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama_kategori', $nama_kategori);
        return $stmt->execute();
    }

    public function deleteKategori($id) {
        $query = "DELETE FROM kategori WHERE id_kategori = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; // likely blocked by FK usage
        }
    }

    public function addSatuan($nama_satuan) {
        $query = "INSERT INTO satuan (nama_satuan) VALUES (:nama_satuan)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama_satuan', $nama_satuan);
        return $stmt->execute();
    }

    public function updateSatuan($id, $nama_satuan) {
        $query = "UPDATE satuan SET nama_satuan = :nama_satuan WHERE id_satuan = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nama_satuan', $nama_satuan);
        return $stmt->execute();
    }

    public function deleteSatuan($id) {
        $query = "DELETE FROM satuan WHERE id_satuan = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        try {
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; // likely blocked by FK usage
        }
    }
}
