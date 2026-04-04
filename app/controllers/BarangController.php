<?php

require_once __DIR__ . '/../models/Barang.php';
require_once __DIR__ . '/../helpers/format.php';

class BarangController {
    private $model;

    public function __construct() {
        $this->model = new Barang();
    }

    public function index() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $kategori_param = $_GET['kategori'] ?? 'all';
        $selected_kategori = ($kategori_param !== 'all' && $kategori_param !== '') ? (int)$kategori_param : null;
        $items_per_page = 25;
        $offset = ($page - 1) * $items_per_page;

        $total_items = $this->model->countAll($selected_kategori);
        $total_pages = (int)ceil($total_items / $items_per_page);
        $current_page = $page;
        $barang = $this->model->getAllWithPagination($offset, $items_per_page, $selected_kategori);
        $kategori = $this->model->getAllKategori();
        $totals = $this->model->getTotals();
        $totals_by_kategori = $this->model->getTotalsByKategori();
        require_once __DIR__ . '/../views/barang/index.php';
    }

    public function create() {
        $kategori = $this->model->getAllKategori();
        $satuan = $this->model->getAllSatuan();
        require_once __DIR__ . '/../views/barang/create.php';
    }

    private function parseNumberInput($value): ?float {
        $raw = trim((string)$value);
        if ($raw === '') {
            return null;
        }
        $normalized = preg_replace('/[^\d]/', '', $raw);
        if ($normalized === null || $normalized === '') {
            return null;
        }
        return (float)$normalized;
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kodeBarang = trim($_POST['kode_barang'] ?? '');
            if ($kodeBarang === '') {
                $_SESSION['error'] = 'Kode barang wajib diisi.';
                redirect('/barang/create');
            }

            if ($this->model->existsByKode($kodeBarang)) {
                $_SESSION['error'] = 'Kode barang sudah digunakan, silakan gunakan kode lain.';
                redirect('/barang/create');
            }

            $hargaBeli = $this->parseNumberInput($_POST['harga_beli'] ?? null);
            $hargaJual = $this->parseNumberInput($_POST['harga_jual'] ?? null);
            if ($hargaBeli === null || $hargaJual === null) {
                $_SESSION['error'] = 'Harga beli dan harga jual wajib diisi.';
                redirect('/barang/create');
            }
            if ($hargaBeli >= $hargaJual) {
                $_SESSION['error'] = 'Harga beli harus lebih kecil dari harga jual.';
                redirect('/barang/create');
            }

            $data = [
                'kode_barang' => $kodeBarang,
                'nama_barang' => $_POST['nama_barang'],
                'id_kategori' => $_POST['id_kategori'],
                'satuan' => $_POST['satuan'] ?? 'pcs',
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'stok' => $_POST['stok'],
                'tanggal_expired' => trim((string)($_POST['tanggal_expired'] ?? '')),
                'stok_updated_by' => $_SESSION['user_id'] ?? null
            ];

            if ($this->model->create($data)) {
                $_SESSION['success'] = 'Barang berhasil ditambahkan';
                redirect('/barang');
            } else {
                $_SESSION['error'] = 'Gagal menambahkan barang';
                redirect('/barang/create');
            }
        }
    }

    public function edit($id) {
        $barang = $this->model->getById($id);
        if (!$barang) {
            $_SESSION['error'] = 'Barang tidak ditemukan';
            redirect('/barang');
        }
        $kategori = $this->model->getAllKategori();
        $satuan = $this->model->getAllSatuan();
        require_once __DIR__ . '/../views/barang/edit.php';
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kodeBarang = trim($_POST['kode_barang'] ?? '');
            if ($kodeBarang === '') {
                $_SESSION['error'] = 'Kode barang wajib diisi.';
                redirect('/barang/edit/' . $id);
            }

            if ($this->model->existsByKode($kodeBarang, $id)) {
                $_SESSION['error'] = 'Kode barang sudah digunakan oleh barang lain.';
                redirect('/barang/edit/' . $id);
            }

            $hargaBeli = $this->parseNumberInput($_POST['harga_beli'] ?? null);
            $hargaJual = $this->parseNumberInput($_POST['harga_jual'] ?? null);
            if ($hargaBeli === null || $hargaJual === null) {
                $_SESSION['error'] = 'Harga beli dan harga jual wajib diisi.';
                redirect('/barang/edit/' . $id);
            }
            if ($hargaBeli >= $hargaJual) {
                $_SESSION['error'] = 'Harga beli harus lebih kecil dari harga jual.';
                redirect('/barang/edit/' . $id);
            }

            $data = [
                'kode_barang' => $kodeBarang,
                'nama_barang' => $_POST['nama_barang'],
                'id_kategori' => $_POST['id_kategori'],
                'satuan' => $_POST['satuan'] ?? 'pcs',
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaJual,
                'stok' => $_POST['stok'],
                'tanggal_expired' => trim((string)($_POST['tanggal_expired'] ?? '')),
                'stok_updated_by' => $_SESSION['user_id'] ?? null
            ];

            if ($this->model->update($id, $data)) {
                $_SESSION['success'] = 'Barang berhasil diperbarui';
                redirect('/barang');
            } else {
                $_SESSION['error'] = 'Gagal memperbarui barang';
                redirect('/barang/edit/' . $id);
            }
        }
    }

    public function delete($id) {
        if ($this->model->delete($id)) {
            $_SESSION['success'] = 'Barang berhasil dihapus';
        } else {
            $_SESSION['error'] = 'Gagal menghapus barang';
        }
        redirect('/barang');
    }

    public function exportExcel() {
        // Export seluruh stok barang, tidak dibatasi filter kategori
        $barang = $this->model->getAll(null);
        if (!empty($barang)) {
            usort($barang, function ($a, $b) {
                $katA = $a['nama_kategori'] ?? '';
                $katB = $b['nama_kategori'] ?? '';
                $cmpKat = strcmp($katA, $katB);
                if ($cmpKat !== 0) {
                    return $cmpKat;
                }
                return strcmp($a['nama_barang'] ?? '', $b['nama_barang'] ?? '');
            });
        }

        $filename = 'stok-barang-' . date('Y-m-d') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>Kode Barang</th>';
        echo '<th>Nama Barang</th>';
        echo '<th>Kategori</th>';
        echo '<th>Satuan</th>';
        echo '<th>Harga Beli</th>';
        echo '<th>Harga Jual</th>';
        echo '<th>Stok</th>';
        echo '<th>Tanggal Expired</th>';
        echo '</tr>';

        $no = 1;
        foreach ($barang as $item) {
            echo '<tr>';
            echo '<td>' . $no++ . '</td>';
            echo '<td>' . htmlspecialchars((string)($item['kode_barang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($item['nama_barang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($item['nama_kategori'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($item['satuan'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . (float)($item['harga_beli'] ?? 0) . '</td>';
            echo '<td>' . (float)($item['harga_jual'] ?? 0) . '</td>';
            echo '<td>' . (float)($item['stok'] ?? 0) . '</td>';
            echo '<td>' . (!empty($item['tanggal_expired']) ? htmlspecialchars(date('Y-m-d', strtotime((string)$item['tanggal_expired'])), ENT_QUOTES, 'UTF-8') : '-') . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
}
