<?php ob_start(); ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Card Total Barang Terjual Hari Ini -->
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-100 text-sm font-semibold">Total Barang Terjual Hari Ini</p>
                <p class="text-3xl font-bold mt-2"><?= $stats['barang_terjual_hari_ini'] ?></p>
            </div>
            <div class="bg-blue-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-shopping-bag text-3xl"></i>
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

    <!-- Card Penjualan Hari Ini -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-purple-100 text-sm font-semibold">Penjualan Hari Ini</p>
                <p class="text-2xl font-bold mt-2"><?= formatRupiah($stats['penjualan_hari_ini']) ?></p>
            </div>
            <div class="bg-purple-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-money-bill-wave text-3xl"></i>
            </div>
        </div>
    </div>

    <!-- Card Pembelian Hari Ini -->
    <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-orange-100 text-sm font-semibold">Pembelian Hari Ini</p>
                <p class="text-2xl font-bold mt-2"><?= formatRupiah($stats['pembelian_hari_ini']) ?></p>
            </div>
            <div class="bg-orange-400 bg-opacity-30 rounded-full p-4">
                <i class="fas fa-shopping-cart text-3xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="bg-white rounded-lg shadow-md p-6 mb-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        <i class="fas fa-rocket text-blue-600 mr-2"></i>Menu
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'kasir'): ?>
        <!-- Menu untuk Kasir -->
        <a href="/penjualan/create" class="bg-purple-50 hover:bg-purple-100 border-2 border-purple-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-cash-register text-purple-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Penjualan</p>
        </a>
        <a href="/pembelian/create" class="bg-green-50 hover:bg-green-100 border-2 border-green-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-shopping-cart text-green-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Input Pembelian</p>
        </a>
        <a href="/laporan/stok" class="bg-orange-50 hover:bg-orange-100 border-2 border-orange-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-boxes text-orange-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Cek Stok</p>
        </a>
        <a href="/setting/kategori-satuan" class="bg-indigo-50 hover:bg-indigo-100 border-2 border-indigo-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-tags text-indigo-600 text-3xl mb-2"></i>
            <p class="font-semibold text-gray-700">Edit Kategori</p>
        </a>
        <?php else: ?>
        <!-- Menu untuk Admin -->
        <a href="/penjualan/create" class="bg-purple-50 hover:bg-purple-100 border-2 border-purple-200 rounded-lg p-4 text-center transition">
            <i class="fas fa-cash-register text-purple-600 text-3xl mb-2"></i>
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

<!-- Laporan Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-4">
            <i class="fas fa-file-alt text-blue-600 mr-2"></i>Laporan
        </h3>
        <div class="space-y-3">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'kasir'): ?>
            <!-- Laporan untuk Kasir -->
            <a href="/laporan/penjualan" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                <span class="font-medium text-gray-700">Laporan Penjualan</span>
                <i class="fas fa-arrow-right text-gray-400"></i>
            </a>
            <a href="/laporan/pembelian" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition">
                <span class="font-medium text-gray-700">Laporan Pembelian</span>
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

    <div class="bg-white rounded-lg shadow-md p-6">
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
                        <a href="/barang/edit/<?= $item['id_barang'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
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
