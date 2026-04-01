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
        $tanggalExpired = trim((string)($_POST['tanggal_expired'] ?? ''));

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
            'stok' => $stok,
            'tanggal_expired' => $tanggalExpired,
            'stok_updated_by' => $_SESSION['user_id'] ?? null
        ]);

        if ($result['success']) {
            echo json_encode(['success' => true, 'barang' => $result['data']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan barang baru']);
        }
    }

    // Notifikasi operasional realtime untuk navbar (stok/expired/hutang)
    public function operationalAlerts() {
        header('Content-Type: application/json');

        try {
            require_once BASE_PATH . '/app/models/Laporan.php';
            require_once BASE_PATH . '/app/models/Hutang.php';

            $laporanModel = new Laporan();
            $hutangModel = new Hutang();

            $priorityAlerts = $laporanModel->getPriorityInventoryAlerts();
            $high = $priorityAlerts['high'] ?? [];
            $medium = $priorityAlerts['medium'] ?? [];

            $role = strtolower(trim((string)($_SESSION['role'] ?? 'kasir')));
            if ($role === 'user') {
                $role = 'kasir';
            }
            $canSeeHutang = $role !== 'inspeksi';

            $today = new DateTimeImmutable('today');
            $overdueCount = 0;
            $dueSoonCount = 0;
            $hutangBelumBayar = $canSeeHutang ? ($hutangModel->getBelumBayar() ?: []) : [];

            foreach ($hutangBelumBayar as $row) {
                $jatuhTempoRaw = (string)($row['jatuh_tempo'] ?? '');
                if ($jatuhTempoRaw === '') {
                    continue;
                }
                $jatuhTempo = DateTimeImmutable::createFromFormat('Y-m-d', substr($jatuhTempoRaw, 0, 10));
                if (!$jatuhTempo) {
                    continue;
                }
                $diffDays = (int)$today->diff($jatuhTempo)->format('%r%a');
                if ($diffDays < 0) {
                    $overdueCount++;
                } elseif ($diffDays <= 3) {
                    $dueSoonCount++;
                }
            }

            $items = [];
            $pushItem = function (array $item) use (&$items) {
                if (count($items) < 7) {
                    $items[] = $item;
                }
            };

            if (count($high) > 0) {
                $pushItem([
                    'level' => 'high',
                    'title' => 'Prioritas Tinggi Inventori',
                    'message' => count($high) . ' item perlu ditangani segera',
                    'link' => '/laporan'
                ]);
            }
            if (count($medium) > 0) {
                $pushItem([
                    'level' => 'medium',
                    'title' => 'Prioritas Sedang Inventori',
                    'message' => count($medium) . ' item butuh perhatian',
                    'link' => '/laporan'
                ]);
            }
            if ($canSeeHutang && $overdueCount > 0) {
                $pushItem([
                    'level' => 'high',
                    'title' => 'Hutang Lewat Jatuh Tempo',
                    'message' => $overdueCount . ' hutang sudah lewat tempo',
                    'link' => '/hutang?filter=jatuh_tempo'
                ]);
            }
            if ($canSeeHutang && $dueSoonCount > 0) {
                $pushItem([
                    'level' => 'medium',
                    'title' => 'Hutang Jatuh Tempo <= 3 Hari',
                    'message' => $dueSoonCount . ' hutang mendekati jatuh tempo',
                    'link' => '/hutang?filter=belum_bayar'
                ]);
            }

            $summary = [
                'high_priority_count' => count($high),
                'medium_priority_count' => count($medium),
                'overdue_hutang_count' => $overdueCount,
                'due_soon_hutang_count' => $dueSoonCount,
                'total_critical' => count($high) + $overdueCount,
                'total_alert' => count($high) + count($medium) + $overdueCount + $dueSoonCount
            ];
            $signature = md5(json_encode([
                'summary' => $summary,
                'items' => array_map(fn($i) => $i['title'] . '|' . $i['message'], $items)
            ]));

            echo json_encode([
                'success' => true,
                'generated_at' => date('Y-m-d H:i:s'),
                'can_see_hutang' => $canSeeHutang,
                'summary' => $summary,
                'items' => $items,
                'signature' => $signature
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Gagal memuat notifikasi operasional'
            ]);
        }
    }
}
