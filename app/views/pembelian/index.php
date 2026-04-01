<?php ob_start(); ?>

<div class="app-card p-6 app-reveal">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <h2 class="text-2xl app-section-title">
            <i class="fas fa-shopping-cart text-teal-600 mr-2"></i>Daftar Barang Masuk
        </h2>
        <div class="flex flex-wrap gap-2">
            <a href="/pembelian/export?format=excel" class="inline-flex items-center gap-2 rounded-lg border border-teal-300 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-700 hover:bg-teal-100 transition">
                <i class="fas fa-file-excel"></i>Unduh Excel Detail
            </a>
            <a href="/pembelian/create" class="app-btn-primary px-4 py-2 font-semibold">
                <i class="fas fa-plus mr-2"></i>Input Barang Masuk
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-teal-100 rounded-xl overflow-hidden">
            <thead class="bg-teal-50 border-b-2 border-teal-100">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-12">No</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-32">Tanggal</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-40">Supplier</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-48">Item (Jumlah)</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Total</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($pembelian)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada data barang masuk</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pembelian as $index => $item): ?>
                        <tr class="hover:bg-teal-50/70 transition duration-200 border-b border-gray-200">
                            <td class="px-6 py-4 text-sm text-gray-700 font-medium text-center"><?= $index + 1 ?></td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <div class="flex flex-col">
                                    <span class="font-semibold"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></span>
                                    <span class="text-gray-500 text-xs"><?= date('H:i', strtotime($item['tanggal'])) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_pembeli'] ?: '-') ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="space-y-1">
                                    <p class="text-gray-800 font-medium"><?= htmlspecialchars($item['barang_list']) ?></p>
                                    <p class="text-gray-500 text-xs bg-teal-50 inline-block px-2 py-1 rounded">
                                        <i class="fas fa-box mr-1"></i><?= $item['jumlah_item'] ?> item(s)
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-lg text-teal-700">
                                    Rp <?= number_format($item['total_harga'], 0, ',', '.') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-3">
                                    <a href="/pembelian/detail/<?= $item['id_pembelian'] ?>" class="inline-flex items-center justify-center w-8 h-8 bg-teal-100 text-teal-700 hover:bg-teal-700 hover:text-white rounded transition" title="Lihat">
                                        <i class="fas fa-eye text-sm"></i>
                                    </a>
                                    <a href="/pembelian/edit/<?= $item['id_pembelian'] ?>" class="inline-flex items-center justify-center w-8 h-8 bg-amber-100 text-amber-700 hover:bg-amber-600 hover:text-white rounded transition" title="Edit">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <form action="/pembelian/delete/<?= $item['id_pembelian'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pembelian ini?');">
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded transition" title="Hapus">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = 'Daftar Barang Masuk - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
