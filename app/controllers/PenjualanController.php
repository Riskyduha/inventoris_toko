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

    public function index() {
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
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;
        
        if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
            $total_penjualan = count($this->model->getByDateRange($tanggal_awal, $tanggal_akhir));
            $penjualan = $this->model->getByDateRangeWithPagination($tanggal_awal, $tanggal_akhir, $offset, $items_per_page);
        } else {
            $total_penjualan = count($this->model->getAll());
            $penjualan = $this->model->getAllWithPagination($offset, $items_per_page);
        }
        
        $total_pages = ceil($total_penjualan / $items_per_page);
        $current_page = $page;
        
        $filter_tanggal_awal = $tanggal_awal;
        $filter_tanggal_akhir = $tanggal_akhir;
        $is_default_penjualan_today = !$hasCustomFilter;

        require_once __DIR__ . '/../views/penjualan/index.php';
    }

    public function create() {
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

            $data = [
                'items' => $items,
                'uang_diberikan' => (float)($_POST['uang_diberikan'] ?? 0),
                'nama_pembeli' => $_POST['nama_pembeli'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? '',
                'id_user' => $_SESSION['user_id'] ?? null
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
        require_once __DIR__ . '/../views/penjualan/detail.php';
    }

    public function edit($id) {
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

            $data = [
                'items' => $items,
                'uang_diberikan' => (float)($_POST['uang_diberikan'] ?? 0),
                'nama_pembeli' => $_POST['nama_pembeli'] ?? '',
                'keterangan' => $_POST['keterangan'] ?? ''
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
        $result = $this->model->delete($id);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
        redirect('/penjualan');
    }
}

