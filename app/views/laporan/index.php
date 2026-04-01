<?php ob_start(); ?>
<?php
$currentRole = strtolower(trim((string)($_SESSION['role'] ?? 'user')));
if ($currentRole === 'kasir') {
    $currentRole = 'user';
}
$isInspeksi = ($currentRole === 'inspeksi');
?>

<?php if ($currentRole === 'user'): ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8 app-reveal">
    <!-- Card Penjualan Hari Ini -->
    <div class="bg-gradient-to-br from-teal-700 to-teal-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-teal-100 text-sm font-semibold">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold mt-2"><?= formatRupiah($stats['penjualan_hari_ini']) ?></p>
            </div>
            <div class="bg-teal-500 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-money-bill-wave text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Card Pembelian Hari Ini -->
    <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-amber-100 text-sm font-semibold">Pembelian Hari Ini</p>
                <p class="text-2xl font-bold mt-2"><?= formatRupiah($stats['pembelian_hari_ini']) ?></p>
            </div>
            <div class="bg-amber-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-shopping-cart text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Card Total Stok -->
    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-green-100 text-sm font-semibold">Total Stok</p>
                <p class="text-3xl font-bold mt-2"><?= $stats['total_stok'] ?></p>
            </div>
            <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-cubes text-3xl"></i>
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
                <p class="text-teal-100 text-sm font-semibold">Total Harga Beli</p>
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
                <p class="text-green-100 text-sm font-semibold">Total Harga Jual</p>
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
                <p class="text-purple-100 text-sm font-semibold">Total Stok</p>
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
                <h2 class="text-xl font-bold text-slate-800">Monitor penjualan dan laba hari ini</h2>
            </div>
            <span class="inline-flex items-center gap-2 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 px-3 py-1.5">
                <i class="fas fa-circle text-[8px]"></i>
                Terupdate real-time
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Penjualan Hari Ini</p>
                    <p class="text-2xl font-black text-amber-800 mt-2"><?= formatRupiah($stats['penjualan_hari_ini']) ?></p>
                    <p class="text-xs text-slate-500 mt-2">Omzet transaksi tanggal hari ini</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Laba Bersih Hari Ini</p>
                    <p class="text-2xl font-black mt-2 <?= ($stats['laba_bersih_hari_ini'] ?? 0) >= 0 ? 'text-emerald-800' : 'text-red-700' ?>">
                        <?= formatRupiah($stats['laba_bersih_hari_ini'] ?? 0) ?>
                    </p>
                    <p class="text-xs text-slate-500 mt-2">Setelah hitung modal dan diskon item</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <i class="fas fa-chart-line"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-sky-200 bg-gradient-to-br from-sky-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">Total Stok</p>
                    <p class="text-2xl font-black text-sky-800 mt-2"><?= number_format($stats['total_stok'], 0, ',', '.') ?></p>
                    <p class="text-xs text-slate-500 mt-2">Total unit barang tersedia</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                    <i class="fas fa-cubes"></i>
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 to-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-teal-700">Total Harga Beli</p>
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
                    <p class="text-xs font-semibold uppercase tracking-wide text-green-700">Total Harga Jual</p>
                    <p class="text-2xl font-black text-green-800 mt-2"><?= formatRupiah($stats['total_harga_jual']) ?></p>
                    <p class="text-xs text-slate-500 mt-2">Potensi nilai jual stok aktif</p>
                </div>
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-green-100 text-green-700">
                    <i class="fas fa-tags"></i>
                </span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Quick Actions -->
<div class="app-card p-6 mb-8 app-reveal">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-rocket text-teal-600 mr-2"></i>Menu
    </h2>
    <div class="<?= $isInspeksi ? 'grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-3xl mx-auto' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4' ?>">
        <?php if ($isInspeksi): ?>
        <a href="/barang" class="bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-boxes text-orange-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Kelola Daftar Barang</p>
        </a>
        <a href="/laporan/stok" class="bg-cyan-50 hover:bg-cyan-100 border-2 border-cyan-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-chart-line text-cyan-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Laporan Stok</p>
        </a>
        <?php elseif ($currentRole === 'user'): ?>
        <!-- Menu untuk User -->
        <a href="/penjualan/create" class="bg-teal-50 hover:bg-teal-100 border-2 border-teal-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-cash-register text-teal-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Penjualan</p>
        </a>
        <a href="/pembelian/create" class="bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-shopping-cart text-green-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Pembelian</p>
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
            <p class="font-semibold text-gray-700">Tambah Barang</p>
        </a>
        <a href="/pembelian/create" class="bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-shopping-cart text-green-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Pembelian</p>
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
                    if ($sisaHari <= 7) {
                        $statusClass = 'bg-red-100 text-red-700';
                    } elseif ($sisaHari <= 30) {
                        $statusClass = 'bg-amber-100 text-amber-700';
                    } else {
                        $statusClass = 'bg-orange-100 text-orange-700';
                    }
                    $statusText = 'Sisa ' . $sisaHari . ' hari';
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

<!-- Laporan Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 app-reveal">
    <div class="app-card p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-file-alt text-teal-600 mr-2"></i>Laporan
        </h3>
        <div class="space-y-3">
            <?php if ($currentRole === 'user'): ?>
            <!-- Laporan untuk User -->
            <a href="/laporan/penjualan" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                <span class="font-medium text-gray-700">Laporan Penjualan</span>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </a>
            <?php else: ?>
            <!-- Laporan untuk Admin -->
            <a href="/laporan/pembelian" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                <span class="font-medium text-gray-700">Laporan Pembelian</span>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </a>
            <a href="/laporan/penjualan" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                <span class="font-medium text-gray-700">Laporan Penjualan</span>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </a>
            <a href="/laporan/stok" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                <span class="font-medium text-gray-700">Laporan Stok</span>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </a>
            <a href="/laporan/keuntungan" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                <span class="font-medium text-gray-700">Laporan Keuntungan</span>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="app-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">
                <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>Reminder Stok Menipis
            </h3>
            <span class="text-xs text-gray-500">Barang dengan stok ≤ 10</span>
        </div>
        <?php if (empty($barangMenipis)): ?>
            <div class="text-center py-6">
                <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                <p class="text-gray-600">Semua barang stoknya cukup, tidak ada yang menipis.</p>
            </div>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($barangMenipis as $item): ?>
                    <div class="flex items-center justify-between p-3 bg-red-50 border-l-4 border-red-500 rounded">
                        <div>
                            <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_barang']) ?></p>
                            <p class="text-sm text-gray-600">Stok: <span class="font-bold text-red-600"><?= $item['stok'] ?></span> <?= htmlspecialchars($item['satuan']) ?></p>
                        </div>
                        <?php if ($currentRole === 'admin'): ?>
                            <a href="/barang/edit/<?= $item['id_barang'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </a>
                        <?php else: ?>
                            <a href="/barang" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                <i class="fas fa-eye mr-1"></i>Lihat
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = 'Dashboard - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
