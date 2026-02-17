<?php

class ApiController {
    private $barang;

    public function __construct() {
        require_once BASE_PATH . '/app/models/Barang.php';
        $this->barang = new Barang();
    }

    // Search barang by name with optional kategori filter
    public function searchBarang() {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        $kategori = $_GET['kategori'] ?? null;
        
        if (strlen($query) < 1) {
            echo json_encode(['results' => [], 'total' => 0]);
            return;
        }

        // Parse kategori if provided (handle 'all' value)
        $kategoriId = null;
        if ($kategori && $kategori !== 'all' && $kategori !== '') {
            $kategoriId = (int)$kategori;
        }

        $results = $this->barang->searchBarang($query, $kategoriId);
        echo json_encode([
            'results' => $results,
            'total' => count($results)
        ]);
    }

    // Create barang cepat (dipakai di form pembelian)
    public function createBarang() {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        $nama = trim($_POST['nama_barang'] ?? '');
        $idKategori = $_POST['id_kategori'] ?? null;
        $satuan = trim($_POST['satuan'] ?? 'pcs');
        $hargaBeli = $_POST['harga_beli'] ?? 0;
        $hargaJual = $_POST['harga_jual'] ?? 0;
        $stok = $_POST['stok'] ?? 0;

        if ($nama === '' || !$idKategori) {
            echo json_encode(['success' => false, 'message' => 'Nama dan kategori wajib diisi']);
            return;
        }

        $result = $this->barang->createAndReturn([
            'kode_barang' => $_POST['kode_barang'] ?? null,
            'nama_barang' => $nama,
            'id_kategori' => $idKategori,
            'satuan' => $satuan,
            'harga_beli' => $hargaBeli,
            'harga_jual' => $hargaJual,
            'stok' => $stok
        ]);

        if ($result['success']) {
            echo json_encode(['success' => true, 'barang' => $result['data']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan barang baru']);
        }
    }
}
