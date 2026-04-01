<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-shopping-cart text-blue-600 mr-2"></i>Detail Barang Masuk
        </h2>
        <span class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold text-lg">#<?= $pembelian['id_pembelian'] ?></span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Informasi Transaksi -->
        <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b-2 border-gray-400">Informasi Transaksi</h3>
            
            <div class="space-y-5">
                <div class="flex justify-between items-start">
                    <span class="text-gray-600 text-sm font-medium">Tanggal:</span>
                    <span class="text-gray-800 font-bold text-right"><?= date('d/m/Y', strtotime($pembelian['tanggal'])) ?></span>
                </div>
                <div class="flex justify-between items-start border-t pt-4">
                    <span class="text-gray-600 text-sm font-medium">Waktu:</span>
                    <span class="text-gray-800 font-bold text-right"><?= date('H:i', strtotime($pembelian['tanggal'])) ?></span>
                </div>
                <div class="flex justify-between items-start border-t pt-4">
                    <span class="text-gray-600 text-sm font-medium">Supplier/Penjual:</span>
                    <span class="text-gray-800 font-bold text-right"><?= $pembelian['nama_pembeli'] ?: 'Tidak ada' ?></span>
                </div>
                
                <?php if ($pembelian['keterangan']): ?>
                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm font-medium mb-2">Keterangan:</p>
                    <p class="text-gray-800 bg-white p-3 rounded border border-gray-200 text-sm"><?= nl2br(htmlspecialchars($pembelian['keterangan'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ringkasan Pembayaran -->
        <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6">
            <h3 class="text-xl font-bold text-blue-900 mb-6 pb-3 border-b-2 border-blue-400">Total Barang Masuk</h3>
            
            <div class="space-y-5">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700 text-sm font-medium">Total:</span>
                    <span class="text-3xl font-bold text-green-600">Rp <?= number_format($pembelian['total_harga'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Item Barang Masuk -->
    <div class="overflow-x-auto mb-8">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Item Barang Masuk</h3>
        <table class="w-full border border-gray-300 rounded-lg">
            <thead class="bg-blue-100 border-b-2 border-blue-300">
                <tr>
                    <th class="px-6 py-4 text-center text-gray-800 font-bold w-12">No</th>
                    <th class="px-6 py-4 text-left text-gray-800 font-bold w-40">Nama Barang</th>
                    <th class="px-6 py-4 text-center text-gray-800 font-bold w-20">Jumlah</th>
                    <th class="px-6 py-4 text-center text-gray-800 font-bold w-20">Satuan</th>
                    <th class="px-6 py-4 text-right text-gray-800 font-bold w-32">Harga/Unit</th>
                    <th class="px-6 py-4 text-right text-gray-800 font-bold w-32">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($details as $index => $item): ?>
                <tr class="hover:bg-blue-50 transition duration-200">
                    <td class="px-6 py-4 text-center font-medium text-gray-800"><?= $index + 1 ?></td>
                    <td class="px-6 py-4 font-semibold text-gray-800"><?= htmlspecialchars($item['nama_barang']) ?></td>
                    <td class="px-6 py-4 text-center font-medium text-gray-800">
                        <?= $item['jumlah'] ?>
                    </td>
                    <td class="px-6 py-4 text-center font-semibold text-gray-800">
                        <?= htmlspecialchars($item['satuan']) ?>
                    </td>
                    <td class="px-6 py-4 text-right font-semibold text-gray-800">
                        Rp <?= number_format($item['harga_satuan'], 0, ',', '.') ?>
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-green-600">
                        Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Aksi -->
    <div class="flex gap-4 justify-center mt-8">
        <a href="/pembelian/edit/<?= $pembelian['id_pembelian'] ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-edit mr-2"></i>Edit
        </a>
        <form action="/pembelian/delete/<?= $pembelian['id_pembelian'] ?>" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg transition font-semibold">
                <i class="fas fa-trash mr-2"></i>Hapus
            </button>
        </form>
        <a href="/pembelian" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = 'Detail Barang Masuk - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
