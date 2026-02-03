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
        $items_per_page = 25;
        $offset = ($page - 1) * $items_per_page;

        $total_items = $this->model->countAll();
        $total_pages = (int)ceil($total_items / $items_per_page);
        $current_page = $page;
        $barang = $this->model->getAllWithPagination($offset, $items_per_page);
        $kategori = $this->model->getAllKategori();
        require_once __DIR__ . '/../views/barang/index.php';
    }

    public function create() {
        $kategori = $this->model->getAllKategori();
        $satuan = $this->model->getAllSatuan();
        require_once __DIR__ . '/../views/barang/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'kode_barang' => $_POST['kode_barang'],
                'nama_barang' => $_POST['nama_barang'],
                'id_kategori' => $_POST['id_kategori'],
                'satuan' => $_POST['satuan'] ?? 'pcs',
                'harga_beli' => $_POST['harga_beli'],
                'harga_jual' => $_POST['harga_jual'],
                'stok' => $_POST['stok']
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
            $data = [
                'kode_barang' => $_POST['kode_barang'],
                'nama_barang' => $_POST['nama_barang'],
                'id_kategori' => $_POST['id_kategori'],
                'satuan' => $_POST['satuan'] ?? 'pcs',
                'harga_beli' => $_POST['harga_beli'],
                'harga_jual' => $_POST['harga_jual'],
                'stok' => $_POST['stok']
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
}
