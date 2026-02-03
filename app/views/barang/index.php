<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">
            <i class="fas fa-box text-blue-600 mr-2"></i>Daftar Barang
        </h2>
        <a href="/barang/create" class="w-full sm:w-auto text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 sm:py-2 rounded transition">
            <i class="fas fa-plus mr-2"></i>Tambah Barang
        </a>
    </div>

    <?php if (!empty($kategori)): ?>
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <button class="px-3 py-2 rounded border text-xs sm:text-sm bg-blue-600 text-white" data-kat="all" onclick="filterKategori('all')">Semua</button>
        <?php foreach ($kategori as $kat): ?>
            <button class="px-3 py-2 rounded border text-xs sm:text-sm bg-gray-100 hover:bg-gray-200" data-kat="<?= $kat['id_kategori'] ?>" onclick="filterKategori('<?= $kat['id_kategori'] ?>')">
                <?= htmlspecialchars($kat['nama_kategori']) ?>
            </button>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="border-2 border-blue-300 rounded-lg p-3 sm:p-4 bg-blue-50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Harga Beli</p>
            <p class="text-lg sm:text-2xl font-bold text-blue-700" id="sum_beli">Rp 0</p>
        </div>
        <div class="border-2 border-green-300 rounded-lg p-3 sm:p-4 bg-green-50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Harga Jual</p>
            <p class="text-lg sm:text-2xl font-bold text-green-700" id="sum_jual">Rp 0</p>
        </div>
        <div class="border-2 border-purple-300 rounded-lg p-3 sm:p-4 bg-purple-50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Stok</p>
            <p class="text-lg sm:text-2xl font-bold text-purple-700" id="sum_stok">0</p>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-3">
        <?php if (empty($barang)): ?>
            <div class="text-center py-8 text-gray-400 italic">Tidak ada data barang</div>
        <?php else: ?>
            <?php foreach ($barang as $index => $item): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition" data-kategori="<?= $item['id_kategori'] ?>" data-beli="<?= $item['harga_beli'] ?>" data-jual="<?= $item['harga_jual'] ?>" data-stok="<?= $item['stok'] ?>">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="font-mono text-xs text-gray-500 mb-1"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></div>
                            <h3 class="font-bold text-gray-800 mb-1"><?= htmlspecialchars($item['nama_barang']) ?></h3>
                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs font-bold">
                                <?= htmlspecialchars($item['nama_kategori']) ?>
                            </span>
                        </div>
                        <span class="<?= $item['stok'] <= 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> px-3 py-1 rounded-full text-xs font-bold">
                            <?= $item['stok'] ?> <?= htmlspecialchars($item['satuan']) ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3 text-sm">
                        <div>
                            <div class="text-gray-500 text-xs">Harga Beli</div>
                            <div class="font-semibold text-gray-800"><?= formatRupiah($item['harga_beli']) ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-xs">Harga Jual</div>
                            <div class="font-semibold text-gray-800"><?= formatRupiah($item['harga_jual']) ?></div>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="/barang/edit/<?= $item['id_barang'] ?>" class="flex-1 text-center bg-yellow-100 text-yellow-700 hover:bg-yellow-600 hover:text-white py-2 rounded transition text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button onclick="confirmDelete(<?= $item['id_barang'] ?>)" class="flex-1 bg-red-100 text-red-700 hover:bg-red-600 hover:text-white py-2 rounded transition text-sm font-medium">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full border border-gray-300 rounded-lg">
            <thead class="bg-blue-100 border-b-2 border-blue-300">
                <tr>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-12">No</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-20">Kode</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-40">Nama Barang</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-28">Kategori</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Satuan</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Beli</th>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Jual</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Stok</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($barang)): ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada data barang</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($barang as $index => $item): ?>
                        <tr class="hover:bg-blue-50 transition duration-200" data-kategori="<?= $item['id_kategori'] ?>" data-beli="<?= $item['harga_beli'] ?>" data-jual="<?= $item['harga_jual'] ?>" data-stok="<?= $item['stok'] ?>">
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-700"><?= (($current_page - 1) * $items_per_page) + $index + 1 ?></td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-600"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></td>
                            <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($item['nama_barang']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold">
                                    <?= htmlspecialchars($item['nama_kategori']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 font-medium"><?= htmlspecialchars($item['satuan']) ?></td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800"><?= formatRupiah($item['harga_beli']) ?></td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800"><?= formatRupiah($item['harga_jual']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="<?= $item['stok'] <= 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> px-3 py-1 rounded-full text-xs font-bold">
                                    <?= $item['stok'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-3">
                                    <a href="/barang/edit/<?= $item['id_barang'] ?>" class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-600 hover:bg-yellow-600 hover:text-white rounded transition">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <a href="/barang/delete/<?= $item['id_barang'] ?>" 
                                       onclick="return confirm('Yakin ingin menghapus barang ini?')" 
                                       class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded transition">
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

    <?php if ($total_pages > 1): ?>
    <div class="flex justify-center items-center gap-2 mt-6">
        <?php if ($current_page > 1): ?>
            <a href="/barang?page=1" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Pertama
            </a>
            <a href="/barang?page=<?= $current_page - 1 ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Sebelumnya
            </a>
        <?php endif; ?>

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
                <a href="/barang?page=<?= $i ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end_page < $total_pages): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="/barang?page=<?= $current_page + 1 ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Berikutnya<i class="fas fa-chevron-right ml-1"></i>
            </a>
            <a href="/barang?page=<?= $total_pages ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Terakhir<i class="fas fa-chevron-right ml-1"></i>
            </a>
        <?php endif; ?>
    </div>
    <div class="text-center mt-3 text-sm text-gray-600">
        Halaman <?= $current_page ?> dari <?= $total_pages ?> (Total: <?= $total_items ?> barang)
    </div>
    <?php endif; ?>
</div>

<script>
const currentPage = <?= (int)$current_page ?>;
const itemsPerPage = <?= (int)$items_per_page ?>;

function filterKategori(katId) {
    const rows = document.querySelectorAll('tbody tr[data-kategori]');
    rows.forEach(row => {
        const match = katId === 'all' || row.getAttribute('data-kategori') === katId;
        row.style.display = match ? '' : 'none';
    });

    document.querySelectorAll('[data-kat]').forEach(btn => {
        const active = btn.getAttribute('data-kat') === katId;
        btn.classList.toggle('bg-blue-600', active);
        btn.classList.toggle('text-white', active);
        btn.classList.toggle('bg-gray-100', !active);
        btn.classList.toggle('text-gray-800', !active);
    });

    updateRowNumbers();
    updateSummary(katId);
}

function updateRowNumbers() {
    const visibleRows = Array.from(document.querySelectorAll('tbody tr[data-kategori]')).filter(row => row.style.display !== 'none');
    visibleRows.forEach((row, index) => {
        const noCell = row.querySelector('td:first-child');
        if (noCell) {
            noCell.textContent = ((currentPage - 1) * itemsPerPage) + index + 1;
        }
    });
}

function formatRupiah(num) {
    return 'Rp ' + (num || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function updateSummary(katId = 'all') {
    let sumBeli = 0;
    let sumJual = 0;
    let sumStok = 0;

    document.querySelectorAll('tbody tr[data-kategori]').forEach(row => {
        const match = katId === 'all' || row.getAttribute('data-kategori') === katId;
        if (!match) return;
        sumBeli += parseFloat(row.getAttribute('data-beli')) || 0;
        sumJual += parseFloat(row.getAttribute('data-jual')) || 0;
        sumStok += parseFloat(row.getAttribute('data-stok')) || 0;
    });

    document.getElementById('sum_beli').textContent = formatRupiah(sumBeli);
    document.getElementById('sum_jual').textContent = formatRupiah(sumJual);
    document.getElementById('sum_stok').textContent = (sumStok || 0).toLocaleString('id-ID');
}

// Init summary
updateSummary('all');
</script>

<?php 
$content = ob_get_clean();
$title = 'Daftar Barang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
