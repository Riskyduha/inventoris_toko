<?php ob_start(); ?>
<?php
$currentRole = strtolower(trim((string)($_SESSION['role'] ?? 'user')));
if ($currentRole === 'kasir') {
    $currentRole = 'user';
}
$isKasirView = ($currentRole === 'user');
$isInspeksi = ($currentRole === 'inspeksi');
$selectedPeriode = (string)($quickPeriod ?? '1');
$periodeLabel = $selectedPeriode === '7' ? '7 Hari Terakhir' : ($selectedPeriode === '30' ? '30 Hari Terakhir' : 'Hari Ini');
$normalizedRole = class_exists('PermissionGate')
    ? PermissionGate::normalizeRole((string)($_SESSION['role'] ?? 'kasir'))
    : (strtolower(trim((string)($_SESSION['role'] ?? 'kasir'))) === 'user' ? 'kasir' : strtolower(trim((string)($_SESSION['role'] ?? 'kasir'))));
$canViewLaporanStokMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'laporan.stok.view') : !$isInspeksi;
$canViewLaporanPenjualanMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'laporan.penjualan.view') : true;
$canViewLaporanPembelianMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'laporan.pembelian.view') : !$isInspeksi;
$canViewLaporanAnyMenu = $canViewLaporanStokMenu || $canViewLaporanPenjualanMenu || $canViewLaporanPembelianMenu;
$selectedChartDays = (string)($chartDaysParam ?? ($chartDays ?? 7));
$trendDays = (int)($chartDays ?? 7);
$allowedChartOptions = ['7', '14', '30', '60', '90', '180'];
if (!in_array($selectedChartDays, $allowedChartOptions, true)) {
    $selectedChartDays = (string)$trendDays;
}
$trendMap = [];
foreach (($trend ?? []) as $row) {
    $time = strtotime((string)($row['tanggal'] ?? ''));
    if ($time !== false) {
        $key = date('Y-m-d', $time);
        $trendMap[$key] = (float)($row['total'] ?? 0);
    }
}
$trendLabels = [];
$trendValues = [];
$trendDateKeys = [];
for ($i = $trendDays - 1; $i >= 0; $i--) {
    $dateKey = date('Y-m-d', strtotime("-{$i} days"));
    $trendDateKeys[] = $dateKey;
    $trendLabels[] = date('d M', strtotime($dateKey));
    $trendValues[] = (float)($trendMap[$dateKey] ?? 0);
}
$periodDaysForStats = (int)$selectedPeriode;
$periodDaysForStats = $periodDaysForStats > 0 ? $periodDaysForStats : 1;
$periodStartDate = date('Y-m-d', strtotime('-' . ($periodDaysForStats - 1) . ' days'));
$periodEndDate = date('Y-m-d');
$penjualanDrilldownUrl = '/laporan/penjualan?start=' . rawurlencode($periodStartDate) . '&end=' . rawurlencode($periodEndDate);
$keuntunganDrilldownUrl = '/laporan/keuntungan?start=' . rawurlencode($periodStartDate) . '&end=' . rawurlencode($periodEndDate);
?>

<?php if ($isKasirView): ?>
<div class="app-card p-4 sm:p-5 mb-6 app-reveal md:sticky md:top-20 md:z-30 backdrop-blur border border-slate-200/80">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dashboard Kasir</p>
            <p class="text-sm text-slate-600">Data kasir ditampilkan real-time untuk hari ini.</p>
        </div>
        <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 w-fit">
            Update terakhir: <?= htmlspecialchars($dashboard_last_updated ?? '-') ?> (<?= htmlspecialchars(date_default_timezone_get()) ?>)
        </span>
    </div>
</div>
<?php else: ?>
<div class="app-card p-4 sm:p-5 mb-6 app-reveal md:sticky md:top-20 md:z-30 backdrop-blur border border-slate-200/80">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Filter Dashboard</p>
            <p class="text-sm text-slate-600">Periode cepat untuk ringkasan penjualan dan laba</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="/laporan?periode=1&chart_days=<?= urlencode($selectedChartDays) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold <?= $selectedPeriode === '1' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">Hari Ini</a>
            <a href="/laporan?periode=7&chart_days=<?= urlencode($selectedChartDays) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold <?= $selectedPeriode === '7' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">7 Hari</a>
            <a href="/laporan?periode=30&chart_days=<?= urlencode($selectedChartDays) ?>" class="px-3 py-1.5 rounded-full text-xs font-semibold <?= $selectedPeriode === '30' ? 'bg-teal-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">30 Hari</a>
            <span class="ml-1 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                Update terakhir: <?= htmlspecialchars($dashboard_last_updated ?? '-') ?> (<?= htmlspecialchars(date_default_timezone_get()) ?>)
            </span>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($currentRole === 'user'): ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 app-reveal">
    <!-- Card Penjualan Hari Ini -->
    <div class="bg-gradient-to-br from-teal-700 to-teal-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-teal-100 text-sm font-semibold">Omzet Penjualan Hari Ini</p>
                <p class="text-2xl font-bold mt-2"><?= formatRupiah($statsToday['penjualan_hari_ini'] ?? 0) ?></p>
            </div>
            <div class="bg-teal-500 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-money-bill-wave text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Card Jumlah Barang Laku -->
    <div class="bg-gradient-to-br from-emerald-600 to-emerald-700 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-emerald-100 text-sm font-semibold">Jumlah Barang Laku Hari Ini</p>
                <p class="text-3xl font-bold mt-2"><?= number_format((float)($statsToday['barang_terjual_hari_ini'] ?? 0), 0, ',', '.') ?> <span class="text-base font-semibold text-emerald-100">item</span></p>
            </div>
            <div class="bg-emerald-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-box-open text-3xl"></i>
            </div>
        </div>
    </div>
</div>
<?php elseif ($isInspeksi): ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 app-reveal">
    <!-- Card Total Harga Beli -->
    <div class="bg-gradient-to-br from-teal-700 to-teal-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-teal-100 text-sm font-semibold">Nilai Modal Stok</p>
                <p class="text-2xl font-bold mt-2"><?= formatRupiah($stats['total_harga_beli']) ?></p>
            </div>
            <div class="bg-teal-500 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-receipt text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Card Total Harga Jual -->
    <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-semibold">Potensi Nilai Jual Stok</p>
                <p class="text-2xl font-bold mt-2"><?= formatRupiah($stats['total_harga_jual']) ?></p>
            </div>
            <div class="bg-green-500 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-tags text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Card Total Stok -->
    <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-semibold">Total Stok Barang</p>
                <p class="text-3xl font-bold mt-2"><?= $stats['total_stok'] ?></p>
            </div>
            <div class="bg-purple-500 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-cubes text-3xl"></i>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="mb-8 app-reveal">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 mb-4 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ringkasan Dashboard Admin</p>
                <h2 class="text-xl font-bold text-slate-800">Monitor penjualan dan laba <?= strtolower($periodeLabel) ?></h2>
            </div>
            <span class="inline-flex items-center gap-2 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 px-3 py-1.5">
                <i class="fas fa-circle text-[8px]"></i>
                Terupdate real-time
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <a href="<?= htmlspecialchars($penjualanDrilldownUrl) ?>" class="group rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm block hover:shadow-md hover:border-amber-300 transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Omzet Penjualan (<?= htmlspecialchars($periodeLabel) ?>)</p>
                    <p class="text-2xl font-black text-amber-800 mt-2"><?= formatRupiah($stats['penjualan_hari_ini']) ?></p>
                    <p class="text-xs text-slate-500 mt-2">Klik untuk buka detail transaksi</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700 group-hover:scale-105 transition">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
            </div>
        </a>

        <a href="<?= htmlspecialchars($keuntunganDrilldownUrl) ?>" class="group rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm block hover:shadow-md hover:border-emerald-300 transition">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Laba Bersih (<?= htmlspecialchars($periodeLabel) ?>)</p>
                    <p class="text-2xl font-black mt-2 <?= ($stats['laba_bersih_hari_ini'] ?? 0) >= 0 ? 'text-emerald-800' : 'text-red-700' ?>">
                        <?= formatRupiah($stats['laba_bersih_hari_ini'] ?? 0) ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-2">Klik untuk buka detail laba transaksi</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 group-hover:scale-105 transition">
                    <i class="fas fa-chart-line"></i>
                </span>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Nilai Modal Stok</p>
                    <p class="text-2xl font-black text-teal-800 mt-2"><?= formatRupiah($stats['total_harga_beli']) ?></p>
                    <p class="text-xs text-slate-500 mt-2">Nilai modal dari stok aktif</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                    <i class="fas fa-receipt"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-green-200 bg-gradient-to-br from-green-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Potensi Nilai Jual Stok</p>
                    <p class="text-2xl font-black text-green-800 mt-2"><?= formatRupiah($stats['total_harga_jual']) ?></p>
                    <p class="text-xs text-slate-500 mt-2">Total potensi nilai jual stok</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-green-100 text-green-700">
                    <i class="fas fa-tags"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">Total Stok Barang</p>
                    <p class="text-2xl font-black text-sky-800 mt-2"><?= number_format($stats['total_stok'], 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-500 mt-2">Total unit barang tersedia</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                    <i class="fas fa-cubes"></i>
                </span>
            </div>
        </div>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Visual Penjualan</p>
                <h3 class="text-lg font-bold text-slate-800">Grafik Penjualan <?= $trendDays ?> Hari Terakhir</h3>
            </div>
            <span class="inline-flex items-center gap-2 text-xs font-semibold rounded-full bg-amber-50 text-amber-700 px-3 py-1.5">
                <i class="fas fa-chart-area"></i>
                Interaktif
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <?php foreach ($allowedChartOptions as $opt): ?>
                <a href="/laporan?periode=<?= urlencode($selectedPeriode) ?>&chart_days=<?= urlencode($opt) ?>"
                   class="px-3 py-1.5 rounded-full text-xs font-semibold <?= $selectedChartDays === $opt ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    <?= htmlspecialchars($opt) ?> Hari
                </a>
            <?php endforeach; ?>
        </div>
        <div class="h-[280px] md:h-[320px]">
            <canvas id="adminSalesChart"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="app-card p-6 mb-8 app-reveal">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-rocket text-teal-600 mr-2"></i>Menu
    </h2>
    <div class="<?= $isInspeksi ? 'grid grid-cols-1 gap-4 max-w-xl mx-auto' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4' ?>">
        <?php if ($isInspeksi): ?>
        <a href="/barang" class="bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-boxes text-orange-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Kelola Stok Barang</p>
        </a>
        <?php if ($canViewLaporanAnyMenu): ?>
        <a href="<?= $canViewLaporanStokMenu ? '/laporan/stok' : ($canViewLaporanPenjualanMenu ? '/laporan/penjualan' : '/laporan/pembelian') ?>" class="bg-cyan-50 hover:bg-cyan-100 border-2 border-cyan-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-chart-line text-cyan-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Laporan</p>
        </a>
        <?php endif; ?>
        <?php elseif ($currentRole === 'user'): ?>
        <!-- Menu untuk User -->
        <a href="/penjualan/create" class="bg-teal-50 hover:bg-teal-100 border-2 border-teal-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-cash-register text-teal-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Penjualan</p>
        </a>
        <a href="/pembelian/create" class="bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-shopping-cart text-green-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Barang Masuk</p>
        </a>
        <a href="/barang" class="bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-boxes text-orange-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Lihat Stok Barang</p>
        </a>
        <a href="/laporan/penjualan" class="bg-cyan-50 hover:bg-cyan-100 border-2 border-cyan-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-chart-line text-cyan-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Laporan Penjualan</p>
        </a>
        <?php else: ?>
        <!-- Menu untuk Admin -->
        <a href="/penjualan/create" class="bg-teal-50 hover:bg-teal-100 border-2 border-teal-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-cash-register text-teal-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Penjualan</p>
        </a>
        <a href="/barang/create" class="bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-plus-circle text-blue-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Tambah Stok Barang</p>
        </a>
        <a href="/pembelian/create" class="bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-shopping-cart text-green-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Barang Masuk</p>
        </a>
        <a href="/laporan/stok" class="bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-boxes text-orange-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Cek Stok</p>
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="app-card p-6 mb-8 app-reveal">
    <h3 class="text-xl font-bold text-gray-800 mb-4">
        <i class="fas fa-calendar-times text-red-600 mr-2"></i>Reminder Expired < 3 Bulan
    </h3>
    <?php if (empty($barangAkanExpired)): ?>
        <div class="text-center py-6">
            <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
            <p class="text-gray-600">Tidak ada barang yang akan expired kurang dari 3 bulan ke depan.</p>
        </div>
    <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($barangAkanExpired as $item): ?>
                <?php
                    $sisaHari = (int)($item['sisa_hari'] ?? 0);
                    $statusClass = 'bg-amber-100 text-amber-700';
                    if ($sisaHari < 0) {
                        $statusClass = 'bg-red-100 text-red-700';
                    } elseif ($sisaHari <= 7) {
                        $statusClass = 'bg-red-100 text-red-700';
                    } elseif ($sisaHari <= 30) {
                        $statusClass = 'bg-amber-100 text-amber-700';
                    } else {
                        $statusClass = 'bg-orange-100 text-orange-700';
                    }
                    $statusText = $sisaHari < 0
                        ? 'Lewat ' . abs($sisaHari) . ' hari'
                        : 'Sisa ' . $sisaHari . ' hari';
                ?>
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-white">
                    <div>
                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_barang']) ?></p>
                        <p class="text-sm text-gray-600">
                            Expired: <?= !empty($item['tanggal_expired']) ? date('d M Y', strtotime($item['tanggal_expired'])) : '-' ?> |
                            Stok: <span class="font-semibold"><?= (int)$item['stok'] ?> <?= htmlspecialchars($item['satuan']) ?></span>
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold <?= $statusClass ?>"><?= htmlspecialchars($statusText) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($priorityAlerts['high'] ?? []) || !empty($priorityAlerts['medium'] ?? [])): ?>
<div class="app-card p-6 mb-8 app-reveal">
    <h3 class="text-xl font-bold text-gray-800 mb-4">
        <i class="fas fa-bell text-amber-600 mr-2"></i>Prioritas Inventori
    </h3>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-center justify-between mb-3">
                <p class="font-semibold text-red-700">Prioritas Tinggi</p>
                <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold"><?= count($priorityAlerts['high'] ?? []) ?> item</span>
            </div>
            <?php if (empty($priorityAlerts['high'])): ?>
                <p class="text-sm text-red-600">Tidak ada item prioritas tinggi.</p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($priorityAlerts['high'] as $row): ?>
                        <div class="rounded-lg bg-white border border-red-100 p-2 text-sm">
                            <p class="font-semibold text-slate-800"><?= htmlspecialchars($row['nama_barang']) ?></p>
                            <p class="text-xs text-slate-600">
                                Stok: <?= (int)($row['stok'] ?? 0) ?> <?= htmlspecialchars($row['satuan'] ?? '') ?> ·
                                <?= ($row['reason'] ?? '') === 'expired_critical' ? 'Kedaluwarsa <= 7 hari' : 'Stok kritis' ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-center justify-between mb-3">
                <p class="font-semibold text-amber-700">Prioritas Sedang</p>
                <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold"><?= count($priorityAlerts['medium'] ?? []) ?> item</span>
            </div>
            <?php if (empty($priorityAlerts['medium'])): ?>
                <p class="text-sm text-amber-700">Tidak ada item prioritas sedang.</p>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($priorityAlerts['medium'] as $row): ?>
                        <div class="rounded-lg bg-white border border-amber-100 p-2 text-sm">
                            <p class="font-semibold text-slate-800"><?= htmlspecialchars($row['nama_barang']) ?></p>
                            <p class="text-xs text-slate-600">
                                Stok: <?= (int)($row['stok'] ?? 0) ?> <?= htmlspecialchars($row['satuan'] ?? '') ?> ·
                                <?= ($row['reason'] ?? '') === 'expired_warning' ? 'Kedaluwarsa <= 30 hari' : 'Stok menipis' ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!$isKasirView && !$isInspeksi): ?>
<script>
(function () {
    const canvas = document.getElementById('adminSalesChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const rawLabels = <?= json_encode($trendLabels) ?>;
    const rawValues = <?= json_encode($trendValues) ?>;
    const rawDateKeys = <?= json_encode($trendDateKeys) ?>;

    const labels = Array.isArray(rawLabels) ? rawLabels.map((v) => String(v ?? '')) : [];
    const values = Array.isArray(rawValues) ? rawValues.map((v) => Number(v || 0)) : [];
    const dateKeys = Array.isArray(rawDateKeys) ? rawDateKeys.map((v) => String(v ?? '')) : [];
    const length = Math.min(labels.length, values.length, dateKeys.length);
    const safeLabels = labels.slice(0, length);
    const safeValues = values.slice(0, length);
    const safeDates = dateKeys.slice(0, length);

    const wrapper = canvas.parentElement;
    if (!wrapper) return;
    wrapper.classList.add('relative');

    const tooltip = document.createElement('div');
    tooltip.className = 'hidden pointer-events-none absolute z-20 rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-slate-100 shadow-lg';
    wrapper.appendChild(tooltip);

    const chartPadding = { top: 20, right: 16, bottom: 34, left: 62 };
    let points = [];
    let hoverIndex = -1;

    const rupiah = (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const shortRupiah = (value) => {
        const n = Number(value || 0);
        if (n >= 1000000) return 'Rp ' + (n / 1000000).toFixed(1) + ' jt';
        if (n >= 1000) return 'Rp ' + (n / 1000).toFixed(0) + ' rb';
        return 'Rp ' + Math.round(n);
    };

    const getSize = () => {
        const dpr = window.devicePixelRatio || 1;
        const width = Math.max(320, Math.floor(wrapper.clientWidth));
        const height = Math.max(220, Math.floor(wrapper.clientHeight));
        canvas.width = width * dpr;
        canvas.height = height * dpr;
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        return { width, height };
    };

    const clear = (width, height) => {
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, height);
    };

    const drawFallbackText = (width, height, text) => {
        clear(width, height);
        ctx.fillStyle = '#b45309';
        ctx.font = '600 13px sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(text, width / 2, height / 2);
    };

    const drawChart = () => {
        const { width, height } = getSize();

        if (safeValues.length === 0) {
            drawFallbackText(width, height, 'Belum ada data penjualan untuk ditampilkan');
            return;
        }

        const chartW = width - chartPadding.left - chartPadding.right;
        const chartH = height - chartPadding.top - chartPadding.bottom;
        if (chartW <= 20 || chartH <= 20) {
            drawFallbackText(width, height, 'Ukuran grafik terlalu kecil');
            return;
        }

        const maxValue = Math.max(1, ...safeValues);
        const minValue = 0;
        const range = Math.max(1, maxValue - minValue);

        clear(width, height);

        ctx.strokeStyle = 'rgba(148,163,184,0.18)';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = chartPadding.top + (chartH / 4) * i;
            ctx.beginPath();
            ctx.moveTo(chartPadding.left, y);
            ctx.lineTo(width - chartPadding.right, y);
            ctx.stroke();
        }

        ctx.fillStyle = '#64748b';
        ctx.font = '12px sans-serif';
        ctx.textAlign = 'right';
        for (let i = 0; i <= 4; i++) {
            const val = maxValue - (range / 4) * i;
            const y = chartPadding.top + (chartH / 4) * i + 4;
            ctx.fillText(shortRupiah(val), chartPadding.left - 8, y);
        }

        points = safeValues.map((val, idx) => {
            const x = safeValues.length === 1
                ? chartPadding.left + chartW / 2
                : chartPadding.left + (idx * chartW) / (safeValues.length - 1);
            const y = chartPadding.top + ((maxValue - val) / range) * chartH;
            return { x, y, value: val, label: safeLabels[idx], date: safeDates[idx], idx };
        });

        const xStep = Math.max(1, Math.ceil(safeLabels.length / 7));
        ctx.textAlign = 'center';
        for (let i = 0; i < points.length; i += xStep) {
            ctx.fillText(points[i].label, points[i].x, height - 10);
        }
        if ((points.length - 1) % xStep !== 0) {
            const last = points[points.length - 1];
            ctx.fillText(last.label, last.x, height - 10);
        }

        if (points.length > 1) {
            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 1; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.lineTo(points[points.length - 1].x, chartPadding.top + chartH);
            ctx.lineTo(points[0].x, chartPadding.top + chartH);
            ctx.closePath();
            const gradient = ctx.createLinearGradient(0, chartPadding.top, 0, chartPadding.top + chartH);
            gradient.addColorStop(0, 'rgba(15,118,110,0.24)');
            gradient.addColorStop(1, 'rgba(15,118,110,0.03)');
            ctx.fillStyle = gradient;
            ctx.fill();
        }

        ctx.beginPath();
        ctx.moveTo(points[0].x, points[0].y);
        for (let i = 1; i < points.length; i++) {
            ctx.lineTo(points[i].x, points[i].y);
        }
        ctx.strokeStyle = '#0f766e';
        ctx.lineWidth = 3;
        ctx.stroke();

        for (const p of points) {
            const isHover = p.idx === hoverIndex;
            ctx.beginPath();
            ctx.arc(p.x, p.y, isHover ? 6 : 4, 0, Math.PI * 2);
            ctx.fillStyle = '#0f766e';
            ctx.fill();
            ctx.lineWidth = 2;
            ctx.strokeStyle = '#ffffff';
            ctx.stroke();
        }
    };

    const findNearest = (x, y) => {
        if (!points.length) return -1;
        let best = -1;
        let bestDist = Infinity;
        points.forEach((p, idx) => {
            const dx = p.x - x;
            const dy = p.y - y;
            const dist = Math.sqrt((dx * dx) + (dy * dy));
            if (dist < bestDist) {
                bestDist = dist;
                best = idx;
            }
        });
        return bestDist <= 20 ? best : -1;
    };

    canvas.addEventListener('mousemove', (event) => {
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        const idx = findNearest(x, y);
        hoverIndex = idx;
        drawChart();
        if (idx < 0) {
            tooltip.classList.add('hidden');
            canvas.style.cursor = 'default';
            return;
        }
        const p = points[idx];
        tooltip.innerHTML = '<div class="font-bold">' + p.label + '</div><div>' + rupiah(p.value) + '</div>';
        tooltip.style.left = Math.min(rect.width - 150, p.x + 10) + 'px';
        tooltip.style.top = Math.max(8, p.y - 44) + 'px';
        tooltip.classList.remove('hidden');
        canvas.style.cursor = 'pointer';
    });

    canvas.addEventListener('mouseleave', () => {
        hoverIndex = -1;
        tooltip.classList.add('hidden');
        canvas.style.cursor = 'default';
        drawChart();
    });

    canvas.addEventListener('click', (event) => {
        const rect = canvas.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        const idx = findNearest(x, y);
        if (idx < 0) return;
        const selectedDate = points[idx]?.date;
        if (!selectedDate) return;
        window.location.href = '/laporan/penjualan?start=' + encodeURIComponent(selectedDate) + '&end=' + encodeURIComponent(selectedDate);
    });

    let resizeTimer = null;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(drawChart, 120);
    });

    drawChart();
})();
</script>
<?php endif; ?>

<?php 
$content = ob_get_clean();
$title = 'Dashboard - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
