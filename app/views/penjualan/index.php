<?php ob_start(); ?>

<?php 
    $grandTotal = 0;
    foreach ($penjualan as $p) {
        $grandTotal += (float)($p['total_harga'] ?? 0);
    }

    $paginationQuery = '';
    if (!empty($filter_tanggal_awal ?? '')) {
        $paginationQuery .= '&tanggal_awal=' . rawurlencode($filter_tanggal_awal);
    }
    if (!empty($filter_tanggal_akhir ?? '')) {
        $paginationQuery .= '&tanggal_akhir=' . rawurlencode($filter_tanggal_akhir);
    }
?>

<div class="bg-white rounded-lg shadow-md p-6">
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <h2 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-cash-register text-blue-600 mr-2"></i>Daftar Penjualan
            </h2>
            <a href="/penjualan/create" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition text-sm font-semibold">
                <i class="fas fa-plus mr-2"></i>Tambah Penjualan
            </a>
        </div>

        <!-- Filter Tanggal -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4 mb-4">
            <form method="GET" action="/penjualan" class="flex flex-col md:flex-row md:items-center gap-3">
                <label class="font-semibold text-gray-700 text-sm flex items-center gap-2">
                    <i class="fas fa-calendar text-blue-600"></i>Filter Tanggal:
                </label>
                <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($filter_tanggal_awal ?? '') ?>" class="px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-gray-600 font-medium">-</span>
                <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($filter_tanggal_akhir ?? '') ?>" class="px-3 py-2 border border-gray-300 rounded text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition text-sm font-semibold">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
                <a href="/penjualan" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition text-sm font-semibold">
                    <i class="fas fa-redo mr-2"></i>Reset
                </a>
            </form>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 text-sm text-blue-700 font-semibold inline-block">
            Total Penjualan: <?= formatRupiah($grandTotal) ?>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-300 rounded-lg">
            <thead class="bg-blue-100 border-b-2 border-blue-300">
                <tr>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-12">No</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-32">Tanggal</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-40">Pembeli</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-48">Item (Jumlah)</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Total</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Kembalian</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-32">Status</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($penjualan)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada data penjualan</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($penjualan as $index => $item): ?>
                        <tr class="hover:bg-blue-50 transition duration-200 <?= ($item['hutang_status'] != 'tidak_ada') ? 'bg-orange-50' : '' ?>">
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-700"><?= (($current_page - 1) * 10) + $index + 1 ?></td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-semibold"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></span>
                                    <span class="text-gray-500 text-xs"><?= date('H:i', strtotime($item['tanggal'])) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($item['nama_pembeli'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-sm">
                                <div class="space-y-1">
                                    <p class="text-gray-800 font-medium"><?= $item['barang_list'] ?? '-' ?></p>
                                    <p class="text-gray-500 text-xs bg-gray-100 inline-block px-2 py-1 rounded"><i class="fas fa-box mr-1"></i><?= $item['jumlah_item'] ?> item(s)</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-lg text-blue-600"><?= formatRupiah($item['total_harga']) ?></span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold <?= $item['kembalian'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                <?= formatRupiah($item['kembalian']) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (empty($item['hutang_status']) || $item['hutang_status'] === null): ?>
                                    <span class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>Lunas
                                    </span>
                                <?php elseif ($item['hutang_status'] == 'belum_bayar'): ?>
                                    <a href="/hutang?filter=belum_bayar" class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 hover:bg-red-200 transition">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Belum Bayar
                                    </a>
                                <?php elseif ($item['hutang_status'] == 'lunas'): ?>
                                    <a href="/hutang?filter=lunas" class="inline-block px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                        <i class="fas fa-check-double mr-1"></i>Lunas
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-3">
                                    <a href="/penjualan/detail/<?= $item['id_penjualan'] ?>" class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white rounded transition" title="Detail">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                    <a href="/penjualan/edit/<?= $item['id_penjualan'] ?>" class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-600 hover:bg-yellow-600 hover:text-white rounded transition" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <a href="/penjualan/delete/<?= $item['id_penjualan'] ?>" 
                                       onclick="return confirm('Yakin ingin menghapus penjualan ini? Stok akan dikembalikan.')" 
                                       class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded transition" title="Hapus">
                                        <i class="fas fa-trash text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center items-center gap-2 mt-6">
        <?php if ($current_page > 1): ?>
            <a href="/penjualan?page=1<?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Pertama
            </a>
            <a href="/penjualan?page=<?= $current_page - 1 ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
            </a>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php 
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $current_page + 2);
        
        if ($start_page > 1): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>
        
        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
            <?php if ($i == $current_page): ?>
                <span class="px-3 py-2 rounded bg-blue-600 text-white text-sm font-semibold"><?= $i ?></span>
            <?php else: ?>
                <a href="/penjualan?page=<?= $i ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
                   class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="/penjualan?page=<?= $current_page + 1 ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Berikutnya<i class="fas fa-chevron-right ml-1"></i>
            </a>
            <a href="/penjualan?page=<?= $total_pages ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Terakhir<i class="fas fa-chevron-right ml-1"></i>
            </a>
        <?php endif; ?>
    </div>
    <div class="text-center mt-3 text-sm text-gray-600">
        Halaman <?= $current_page ?> dari <?= $total_pages ?> (Total: <?= $total_penjualan ?> penjualan)
    </div>
    <?php endif; ?>

<?php 
$content = ob_get_clean();
$title = 'Daftar Penjualan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
