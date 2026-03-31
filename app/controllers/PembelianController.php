<?php

require_once __DIR__ . '/../models/Pembelian.php';
require_once __DIR__ . '/../models/Barang.php';
require_once __DIR__ . '/../helpers/format.php';

class PembelianController {
    private $model;
    private $barangModel;

    public function __construct() {
        $this->model = new Pembelian();
        $this->barangModel = new Barang();
    }

    private function ensureTransactionAccess(): void {
        $role = strtolower(trim((string)($_SESSION['role'] ?? '')));
        if ($role === 'inspeksi') {
            $_SESSION['error'] = 'Role inspeksi tidak diizinkan melakukan transaksi pembelian.';
            redirect('/barang');
        }
    }

    public function index() {
        $pembelian = $this->model->getAll();
        require_once __DIR__ . '/../views/pembelian/index.php';
    }

    public function create() {
        $this->ensureTransactionAccess();
        $barang = $this->barangModel->getAll();
        $kategori = $this->barangModel->getAllKategori();
        $satuanList = $this->barangModel->getAllSatuan();
        require_once __DIR__ . '/../views/pembelian/create.php';
    }

    public function store() {
        $this->ensureTransactionAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Parse multiple items from form
            $items = [];
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $index => $item) {
                    if (!empty($item['id_barang'])) {
                        $items[] = [
                            'id_barang' => $item['id_barang'],
                            'satuan' => trim($item['satuan'] ?? ''),
                            'jumlah' => (int)$item['jumlah'],
                            'harga_satuan' => (float)$item['harga_satuan'],
                            'diskon' => (float)($item['diskon'] ?? 0)
                        ];
                    }
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'Tambahkan minimal satu barang';
                redirect('/pembelian/create');
            }

            // Perbarui satuan barang bila diubah saat edit pembelian
            foreach ($items as $item) {
                if ($item['satuan'] !== '') {
                    $updated = $this->barangModel->updateSatuanBarang($item['id_barang'], $item['satuan']);
                    if (!$updated) {
                        $_SESSION['error'] = 'Gagal memperbarui satuan untuk salah satu barang';
                        redirect('/pembelian/create');
                    }
                }
            }

            $data = [
                'items' => $items,
                'uang_diberikan' => (float)($_POST['uang_diberikan'] ?? 0),
                'nama_pembeli' => $_POST['nama_pembeli'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? '',
                'id_user' => $_SESSION['user_id'] ?? null
            ];

            $result = $this->model->create($data);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                redirect('/pembelian');
            } else {
                $_SESSION['error'] = $result['message'];
                redirect('/pembelian/create');
            }
        }
    }

    public function detail($id) {
        $pembelian = $this->model->getById($id);
        if (!$pembelian) {
            $_SESSION['error'] = 'Data pembelian tidak ditemukan';
            redirect('/pembelian');
        }
        $details = $this->model->getDetailById($id);
        require_once __DIR__ . '/../views/pembelian/detail.php';
    }

    public function edit($id) {
        $this->ensureTransactionAccess();
        $pembelian = $this->model->getById($id);
        if (!$pembelian) {
            $_SESSION['error'] = 'Data pembelian tidak ditemukan';
            redirect('/pembelian');
        }
        $details = $this->model->getDetailById($id);
        $barang = $this->barangModel->getAll();
        $satuanList = $this->barangModel->getAllSatuan();
        $kategori = $this->barangModel->getAllKategori();
        require_once __DIR__ . '/../views/pembelian/edit.php';
    }

    public function update($id) {
        $this->ensureTransactionAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $items = [];
            if (isset($_POST['items'])) {
                foreach ($_POST['items'] as $index => $item) {
                    if (!empty($item['id_barang'])) {
                        $items[] = [
                            'id_barang' => $item['id_barang'],
                            'jumlah' => (int)$item['jumlah'],
                            'harga_satuan' => (float)$item['harga_satuan'],
                            'diskon' => (float)($item['diskon'] ?? 0)
                        ];
                    }
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'Tambahkan minimal satu barang';
                redirect('/pembelian/edit/' . $id);
            }

            $data = [
                'items' => $items,
                'uang_diberikan' => 0,
                'nama_pembeli' => $_POST['nama_pembeli'] ?? '',
                'keterangan' => '',
                'id_user' => $_SESSION['user_id'] ?? null
            ];

            $result = $this->model->update($id, $data);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                redirect('/pembelian');
            } else {
                $_SESSION['error'] = $result['message'];
                redirect('/pembelian/edit/' . $id);
            }
        }
    }

    public function delete($id) {
        $this->ensureTransactionAccess();
        $result = $this->model->delete($id, $_SESSION['user_id'] ?? null);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        redirect('/pembelian');
    }
}
