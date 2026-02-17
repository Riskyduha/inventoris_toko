<?php

class ApiController {
    private $barang;

    public function __construct() {
        require_once BASE_PATH . '/app/models/Barang.php';
        $this->barang = new Barang();
    }

    // Search barang by name with optional kategori filter and pagination
    // If query is empty, returns all barang
    public function searchBarang() {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        $kategori = $_GET['kategori'] ?? null;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $itemsPerPage = 25;

        // Parse kategori if provided (handle 'all' value)
        $kategoriId = null;
        if ($kategori && $kategori !== 'all' && $kategori !== '') {
            $kategoriId = (int)$kategori;
        }

        // If query is empty, get all barang. Otherwise search by keyword
        if (strlen($query) < 1) {
            // Get all barang (not just search results)
            $allResults = $this->barang->getAll($kategoriId);
        } else {
            // Search by keyword
            $allResults = $this->barang->searchBarang($query, $kategoriId);
        }
        
        $totalResults = count($allResults);
        $totalPages = (int)ceil($totalResults / $itemsPerPage);
        
        // Apply pagination
        $offset = ($page - 1) * $itemsPerPage;
        $paginatedResults = array_slice($allResults, $offset, $itemsPerPage);
        
        echo json_encode([
            'results' => $paginatedResults,
            'total' => $totalResults,
            'page' => $page,
            'per_page' => $itemsPerPage,
            'total_pages' => $totalPages
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
