<?php

require_once __DIR__ . '/../models/Penjualan.php';
require_once __DIR__ . '/../models/Barang.php';
require_once __DIR__ . '/../models/KonfigurasiNota.php';
require_once __DIR__ . '/../helpers/format.php';

class PenjualanController {
    private $model;
    private $barangModel;
    private $notaConfigModel;

    public function __construct() {
        $this->model = new Penjualan();
        $this->barangModel = new Barang();
        $this->notaConfigModel = new KonfigurasiNota();
    }

    private function ensureTransactionAccess(): void {
        $role = strtolower(trim((string)($_SESSION['role'] ?? '')));
        if ($role === 'inspeksi') {
            $_SESSION['error'] = 'Role inspeksi tidak diizinkan melakukan transaksi penjualan.';
            redirect('/barang');
        }
    }

    public function index() {
        $normalizedRole = class_exists('PermissionGate')
            ? PermissionGate::normalizeRole((string)($_SESSION['role'] ?? 'kasir'))
            : strtolower(trim((string)($_SESSION['role'] ?? 'kasir')));
        if ($normalizedRole === 'kasir') {
            redirect('/penjualan/create');
        }

        $tanggal_awal_input = isset($_GET['tanggal_awal']) ? trim($_GET['tanggal_awal']) : '';
        $tanggal_akhir_input = isset($_GET['tanggal_akhir']) ? trim($_GET['tanggal_akhir']) : '';
        $hasCustomFilter = ($tanggal_awal_input !== '' || $tanggal_akhir_input !== '');

        $tanggal_awal = $tanggal_awal_input;
        $tanggal_akhir = $tanggal_akhir_input;

        if ($tanggal_awal !== '' && $tanggal_akhir === '') {
            $tanggal_akhir = $tanggal_awal;
        } elseif ($tanggal_awal === '' && $tanggal_akhir !== '') {
            $tanggal_awal = $tanggal_akhir;
        }

        if (!$hasCustomFilter) {
            $today = date('Y-m-d');
            $tanggal_awal = $today;
            $tanggal_akhir = $today;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $items_per_page = 25;
        $offset = ($page - 1) * $items_per_page;
        
        if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
            $penjualanSummary = $this->model->getByDateRange($tanggal_awal, $tanggal_akhir);
            $penjualan = $this->model->getByDateRangeWithPagination($tanggal_awal, $tanggal_akhir, $offset, $items_per_page);
        } else {
            $penjualanSummary = $this->model->getAll();
            $penjualan = $this->model->getAllWithPagination($offset, $items_per_page);
        }
        
        $total_penjualan = count($penjualanSummary);
        $summary_total_penjualan = 0;
        $summary_total_item = 0;
        $summary_hutang_belum = 0;
        $summary_total_laba_bersih = 0;
        foreach ($penjualanSummary as $summaryRow) {
            $summary_total_penjualan += (float)($summaryRow['total_harga'] ?? 0);
            $summary_total_item += (int)($summaryRow['jumlah_item'] ?? 0);
            $summary_total_laba_bersih += (float)($summaryRow['laba_bersih'] ?? 0);
            if (($summaryRow['hutang_status'] ?? '') === 'belum_bayar') {
                $summary_hutang_belum++;
            }
        }
        $summary_total_transaksi = $total_penjualan;
        $normalizedRole = class_exists('PermissionGate')
            ? PermissionGate::normalizeRole((string)($_SESSION['role'] ?? 'kasir'))
            : strtolower((string)($_SESSION['role'] ?? 'kasir'));
        $show_profit_admin = class_exists('PermissionGate')
            ? PermissionGate::allows($normalizedRole, 'laporan.keuntungan.view')
            : ($normalizedRole === 'admin');
        
        $total_pages = ceil($total_penjualan / $items_per_page);
        $current_page = $page;
        
        $filter_tanggal_awal = $tanggal_awal;
        $filter_tanggal_akhir = $tanggal_akhir;
        $is_default_penjualan_today = !$hasCustomFilter;

        require_once __DIR__ . '/../views/penjualan/index.php';
    }

    public function create() {
        $this->ensureTransactionAccess();
        $barang = $this->barangModel->getAll();
        $notaConfig = $this->notaConfigModel->getConfig();
        
        // Debug: Log jumlah barang
        error_log('DEBUG: Total barang di create: ' . count($barang));
        if (count($barang) > 0) {
            error_log('DEBUG: Sample barang: ' . json_encode($barang[0]));
        }
        
        require_once __DIR__ . '/../views/penjualan/create.php';
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
                            'jumlah' => (int)$item['jumlah'],
                            'harga_satuan' => (float)$item['harga_satuan'],
                            'diskon' => (float)($item['diskon'] ?? 0)
                        ];
                    }
                }
            }

            if (empty($items)) {
                $_SESSION['error'] = 'Tambahkan minimal satu barang';
                redirect('/penjualan/create');
            }

            // Gunakan tanggal input manual, fallback ke hari ini
            $tanggal_input = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
            if ($tanggal_input === '') {
                $tanggal_input = date('Y-m-d');
            }

            $data = [
                'items' => $items,
                'uang_diberikan' => (float)($_POST['uang_diberikan'] ?? 0),
                'nama_pembeli' => $_POST['nama_pembeli'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? '',
                'id_user' => $_SESSION['user_id'] ?? null,
                'tanggal' => $tanggal_input
            ];

            // Handle hutang if exists
            $ada_hutang = isset($_POST['ada_hutang']) && $_POST['ada_hutang'] ? 1 : 0;
            $data['ada_hutang'] = $ada_hutang;
            
            if ($ada_hutang) {
                $data['hutang'] = [
                    'nama_penghutang' => $_POST['nama_penghutang'] ?? '',
                    'jumlah_hutang' => (float)($_POST['jumlah_hutang'] ?? 0),
                    'jatuh_tempo' => $_POST['jatuh_tempo'] ?? ''
                ];
            }

            $result = $this->model->create($data);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                redirect('/penjualan');
            } else {
                $_SESSION['error'] = $result['message'];
                redirect('/penjualan/create');
            }
        }
    }

    public function detail($id) {
        $penjualan = $this->model->getById($id);
        if (!$penjualan) {
            $_SESSION['error'] = 'Data penjualan tidak ditemukan';
            redirect('/penjualan');
        }
        $details = $this->model->getDetailById($id);
        $notaConfig = $this->notaConfigModel->getConfig();
        require_once __DIR__ . '/../views/penjualan/detail.php';
    }

    public function edit($id) {
        $this->ensureTransactionAccess();
        $penjualan = $this->model->getById($id);
        if (!$penjualan) {
            $_SESSION['error'] = 'Data penjualan tidak ditemukan';
            redirect('/penjualan');
        }
        $details = $this->model->getDetailById($id);
        $barang = $this->barangModel->getAll();
        $notaConfig = $this->notaConfigModel->getConfig();
        
        // Get hutang data if exists
        require_once __DIR__ . '/../models/Hutang.php';
        $hutangModel = new Hutang();
        $hutangData = null;
        // Query hutang by penjualan id
        $allHutang = $hutangModel->getAll();
        foreach ($allHutang as $h) {
            if ((int)$h['id_penjualan'] === (int)$id) {
                $hutangData = $h;
                break;
            }
        }
        
        require_once __DIR__ . '/../views/penjualan/edit.php';
    }

    public function update($id) {
        $this->ensureTransactionAccess();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Parse multiple items from form
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
                redirect('/penjualan/edit/' . $id);
            }

            // Gunakan tanggal input manual, fallback ke hari ini
            $tanggal_input = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
            if ($tanggal_input === '') {
                $tanggal_input = date('Y-m-d');
            }

            $data = [
                'items' => $items,
                'uang_diberikan' => (float)($_POST['uang_diberikan'] ?? 0),
                'nama_pembeli' => $_POST['nama_pembeli'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? '',
                'id_user' => $_SESSION['user_id'] ?? null,
                'tanggal' => $tanggal_input
            ];

            // Handle hutang if exists
            $ada_hutang = isset($_POST['ada_hutang']) && $_POST['ada_hutang'] ? 1 : 0;
            $data['ada_hutang'] = $ada_hutang;
            
            if ($ada_hutang) {
                $data['hutang'] = [
                    'nama_penghutang' => $_POST['nama_penghutang'] ?? '',
                    'jumlah_hutang' => (float)($_POST['jumlah_hutang'] ?? 0),
                    'jatuh_tempo' => $_POST['jatuh_tempo'] ?? ''
                ];
            }

            $result = $this->model->update($id, $data);
            
            if ($result['success']) {
                $_SESSION['success'] = $result['message'];
                redirect('/penjualan');
            } else {
                $_SESSION['error'] = $result['message'];
                redirect('/penjualan/edit/' . $id);
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
        redirect('/penjualan');
    }

    public function export() {
        // Sesuai kebutuhan: export hanya Excel detail
        $tanggalAwal = trim((string)($_GET['tanggal_awal'] ?? date('Y-m-d')));
        $tanggalAkhir = trim((string)($_GET['tanggal_akhir'] ?? $tanggalAwal));

        if ($tanggalAwal === '' || $tanggalAkhir === '') {
            $tanggalAwal = date('Y-m-d');
            $tanggalAkhir = date('Y-m-d');
        }

        $rows = $this->model->getExportDetailByDateRange($tanggalAwal, $tanggalAkhir);
        $timestamp = date('Ymd_His');
        $filenameBase = "penjualan_{$tanggalAwal}_{$tanggalAkhir}_{$timestamp}";
        $filename = $filenameBase . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<table border="1">';
        echo '<tr>';
        echo '<th>No</th>';
        echo '<th>ID Penjualan</th>';
        echo '<th>Tanggal</th>';
        echo '<th>Pembeli</th>';
        echo '<th>Kode Barang</th>';
        echo '<th>Nama Barang</th>';
        echo '<th>Satuan</th>';
        echo '<th>Jumlah</th>';
        echo '<th>Harga Satuan</th>';
        echo '<th>Diskon Item</th>';
        echo '<th>Subtotal Item</th>';
        echo '<th>Harga Beli Item</th>';
        echo '<th>Laba Item</th>';
        echo '<th>Total Transaksi</th>';
        echo '<th>Kembalian</th>';
        echo '<th>Status Hutang</th>';
        echo '<th>Jumlah Hutang</th>';
        echo '<th>Jatuh Tempo</th>';
        echo '<th>Aging (Hari)</th>';
        echo '</tr>';

        foreach ($rows as $idx => $r) {
            echo '<tr>';
            echo '<td>' . ($idx + 1) . '</td>';
            echo '<td>' . (int)($r['id_penjualan'] ?? 0) . '</td>';
            echo '<td>' . htmlspecialchars(date('Y-m-d H:i', strtotime((string)($r['tanggal'] ?? ''))), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['nama_pembeli'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['kode_barang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['nama_barang'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['satuan'] ?? ''), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . (float)($r['jumlah'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['harga_satuan'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['diskon'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['subtotal'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['harga_beli_item'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['laba_item'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['total_harga'] ?? 0) . '</td>';
            echo '<td>' . (float)($r['kembalian'] ?? 0) . '</td>';
            echo '<td>' . htmlspecialchars((string)($r['hutang_status'] ?? 'lunas'), ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . (float)($r['jumlah_hutang'] ?? 0) . '</td>';
            echo '<td>' . (!empty($r['jatuh_tempo']) ? htmlspecialchars(date('Y-m-d', strtotime((string)$r['jatuh_tempo'])), ENT_QUOTES, 'UTF-8') : '-') . '</td>';
            echo '<td>' . (int)($r['aging_hari'] ?? 0) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }
}
