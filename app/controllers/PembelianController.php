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
                            'diskon' => 0,
                            'harga_jual' => isset($item['harga_jual']) && $item['harga_jual'] !== '' ? (float)$item['harga_jual'] : null,
                            'tanggal_expired' => trim((string)($item['tanggal_expired'] ?? ''))
                        ];
                    }
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'Tambahkan minimal satu barang';
                redirect('/pembelian/create');
            }

            // Perbarui atribut barang agar tetap relevan dengan pembelian terbaru
            foreach ($items as $item) {
                $updated = $this->barangModel->updateAtributDariPembelian(
                    (int)$item['id_barang'],
                    $item['satuan'],
                    $item['harga_jual'],
                    $item['tanggal_expired'] !== '' ? $item['tanggal_expired'] : null
                );
                if (!$updated) {
                    $_SESSION['error'] = 'Gagal memperbarui atribut barang untuk salah satu item pembelian';
                    redirect('/pembelian/create');
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
                            'satuan' => trim($item['satuan'] ?? ''),
                            'jumlah' => (int)$item['jumlah'],
                            'harga_satuan' => (float)$item['harga_satuan'],
                            'diskon' => 0,
                            'harga_jual' => isset($item['harga_jual']) && $item['harga_jual'] !== '' ? (float)$item['harga_jual'] : null,
                            'tanggal_expired' => trim((string)($item['tanggal_expired'] ?? ''))
                        ];
                    }
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'Tambahkan minimal satu barang';
                redirect('/pembelian/edit/' . $id);
            }

            foreach ($items as $item) {
                $updated = $this->barangModel->updateAtributDariPembelian(
                    (int)$item['id_barang'],
                    $item['satuan'],
                    $item['harga_jual'],
                    $item['tanggal_expired'] !== '' ? $item['tanggal_expired'] : null
                );
                if (!$updated) {
                    $_SESSION['error'] = 'Gagal memperbarui atribut barang untuk salah satu item pembelian';
                    redirect('/pembelian/edit/' . $id);
                }
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

    public function export() {
        $rows = $this->model->getExportDetailAll();
        $timestamp = date('Ymd_His');
        $filename = "pembelian_detail_{$timestamp}.xls";

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<table border="1">';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>ID Pembelian</th>';
        echo '<th>Tanggal</th>';
        echo '<th>Supplier</th>';
        echo '<th>Kode Barang</th>';
        echo '<th>Nama Barang</th>';
        echo '<th>Satuan</th>';
        echo '<th>Jumlah</th>';
        echo '<th>Harga Satuan</th>';
        echo '<th>Subtotal Item</th>';
        echo '</tr>';

        foreach ($rows as $idx => $r) {
            echo '<tr>';
            echo '<td>' . ($idx + 1) . '</td>';
            echo '<td>' . (int)($r['id_pembelian'] ?? 0) . '</td>';
            echo '<td>' . htmlspecialchars(date('Y-m-d H:i', strtotime((string)($r['tanggal'] ?? ''))), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['nama_pembeli'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['kode_barang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['nama_barang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['satuan'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . (float)($r['jumlah'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['harga_satuan'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['subtotal'] ?? 0) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
}
