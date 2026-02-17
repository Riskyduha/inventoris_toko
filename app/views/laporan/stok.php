<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
        <i class="fas fa-boxes text-blue-600"></i>
        <span>Laporan Stok Barang</span>
    </h2>

    <!-- Filter -->
    <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Mulai</label>
                <input type="date" id="startDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Akhir</label>
                <input type="date" id="endDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2" onclick="filterStok()">
                    <i class="fas fa-search"></i>
                    <span>Filter</span>
                </button>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2" onclick="downloadStokPDF()">
                    <i class="fas fa-file-pdf"></i>
                    <span>Download PDF</span>
                </button>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2" onclick="downloadStokExcel()">
                    <i class="fas fa-file-excel"></i>
                    <span>Download Excel</span>
                </button>
            </div>
        </div>
    </form>

    <script>
    const kategoriNames = <?= json_encode((object)array_reduce($kategori ?? [], function ($carry, $row) {
        $key = (string)($row['id_kategori'] ?? '');
        if ($key !== '') {
            $carry[$key] = $row['nama_kategori'] ?? '-';
        }
        return $carry;
    }, [])) ?>;
    const totalsByKategori = <?= json_encode((object)array_reduce($totals_by_kategori ?? [], function ($carry, $row) {
        $key = (string)($row['id_kategori'] ?? '');
        if ($key !== '') {
            $carry[$key] = [
                'total_harga_beli' => (float)$row['total_harga_beli'],
                'total_harga_jual' => (float)$row['total_harga_jual'],
                'total_stok' => (int)$row['total_stok'],
            ];
        }
        return $carry;
    }, [])) ?>;
    const currentKategori = <?= json_encode($selected_kategori !== null ? (string)$selected_kategori : 'all') ?>;

    function filterStok() {
        // Reset filter jika diperlukan, atau implementasi filter tanggal
        // Untuk sekarang filter hanya menampilkan ulang halaman
        window.location.href = '/laporan/stok';
    }
    
    function downloadStokPDF() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        let url = '/laporan/stok/export?format=pdf';
        if (start) url += '&start=' + start;
        if (end) url += '&end=' + end;
        window.location.href = url;
    }

    function downloadStokExcel() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        let url = '/laporan/stok/export?format=excel';
        if (start) url += '&start=' + start;
        if (end) url += '&end=' + end;
        window.location.href = url;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                buttons.forEach(b => b.classList.remove('active', 'bg-blue-600', 'text-white'));
                buttons.forEach(b => b.classList.add('bg-gray-200', 'text-gray-700'));
                this.classList.add('active', 'bg-blue-600', 'text-white');
                this.classList.remove('bg-gray-200', 'text-gray-700');
                
                const filter = this.dataset.filter;
                document.querySelectorAll('tbody tr').forEach(row => {
                        const kategoriId = String(row.dataset.kategoriId || '');
                        row.style.display = (filter === 'all' || kategoriId === String(filter)) ? '' : 'none';
                });
                
                updateRowNumbers();
                updateKategoriSummary(filter);
            });
        });

        if (currentKategori !== 'all') {
            updateKategoriSummary(currentKategori);
        }
    });

    function updateRowNumbers() {
        const visibleRows = Array.from(document.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
        visibleRows.forEach((row, index) => {
            const noCell = row.querySelector('td:first-child');
            if (noCell) {
                noCell.textContent = index + 1;
            }
        });
    }

    function formatRupiah(num) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num || 0);
    }

    function updateKategoriSummary(katId) {
        const summaryEl = document.getElementById('kategori_summary');
        const nameEl = document.getElementById('kategori_name');
        const beliEl = document.getElementById('kategori_beli');
        const jualEl = document.getElementById('kategori_jual');
        const stokEl = document.getElementById('kategori_stok');
        if (!summaryEl || !nameEl || !beliEl || !jualEl || !stokEl) return;

        if (!katId || katId === 'all') {
            summaryEl.classList.add('hidden');
            return;
        }

        const data = totalsByKategori[katId] || { total_harga_beli: 0, total_harga_jual: 0, total_stok: 0 };
        nameEl.textContent = kategoriNames[katId] || '-';
        beliEl.textContent = formatRupiah(data.total_harga_beli || 0);
        jualEl.textContent = formatRupiah(data.total_harga_jual || 0);
        stokEl.textContent = (data.total_stok || 0).toLocaleString('id-ID');
        summaryEl.classList.remove('hidden');
    }

    </script>

    <!-- Kategori Filter -->
    <?php if (!empty($kategori)): ?>
    <div class="mb-6 pb-4 border-b">
        <p class="text-sm font-semibold text-gray-700 mb-3">Filter Kategori:</p>
        <div class="flex flex-wrap gap-2">
            <a href="/laporan/stok" class="px-4 py-2 rounded-lg font-semibold text-sm <?= empty($selected_kategori) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> filter-btn" data-filter="all">Semua</a>
            <?php foreach ($kategori as $kat): ?>
            <a href="/laporan/stok?kategori=<?= $kat['id_kategori'] ?>" class="px-4 py-2 rounded-lg font-semibold text-sm <?= (!empty($selected_kategori) && (int)$selected_kategori === (int)$kat['id_kategori']) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?> filter-btn" data-filter="<?= $kat['id_kategori'] ?>">
                <?= htmlspecialchars($kat['nama_kategori']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6 text-sm">
        <div class="border rounded-lg p-3 bg-gray-50">
            <p class="text-gray-600">Total Harga Beli</p>
            <p class="text-lg font-semibold text-blue-700" data-total="beli"><?= formatRupiah($totals['total_harga_beli'] ?? 0) ?></p>
        </div>
        <div class="border rounded-lg p-3 bg-gray-50">
            <p class="text-gray-600">Total Harga Jual</p>
            <p class="text-lg font-semibold text-green-700" data-total="jual"><?= formatRupiah($totals['total_harga_jual'] ?? 0) ?></p>
        </div>
        <div class="border rounded-lg p-3 bg-gray-50">
            <p class="text-gray-600">Total Stok</p>
            <p class="text-lg font-semibold text-purple-700" data-total="stok"><?= number_format((int)($totals['total_stok'] ?? 0), 0, ',', '.') ?></p>
        </div>
    </div>

    <div id="kategori_summary" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6 text-sm hidden">
        <div class="border rounded-lg p-3 bg-blue-50/50">
            <p class="text-gray-600">Total Harga Beli (Kategori: <span id="kategori_name">-</span>)</p>
            <p class="text-lg font-semibold text-blue-700" id="kategori_beli">Rp 0</p>
        </div>
        <div class="border rounded-lg p-3 bg-green-50/50">
            <p class="text-gray-600">Total Harga Jual (Kategori)</p>
            <p class="text-lg font-semibold text-green-700" id="kategori_jual">Rp 0</p>
        </div>
        <div class="border rounded-lg p-3 bg-purple-50/50">
            <p class="text-gray-600">Total Stok (Kategori)</p>
            <p class="text-lg font-semibold text-purple-700" id="kategori_stok">0</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full table-fixed">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-12">No</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-28">Kode Barang</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-64">Nama Barang</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 w-20">Satuan</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-28">Harga Beli</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-28">Harga Jual</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700 w-16">Stok</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700 w-24">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($stok)): ?>
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">Tidak ada data barang</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($stok as $index => $item): ?>
                        <tr class="hover:bg-gray-50" data-kategori-id="<?= $item['id_kategori'] ?>" data-beli="<?= (float)$item['harga_beli'] ?>" data-jual="<?= (float)$item['harga_jual'] ?>" data-stok="<?= (int)$item['stok'] ?>">
                            <td class="px-4 py-3"><?= (($current_page - 1) * 10) + $index + 1 ?></td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-700"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></td>
                            <td class="px-4 py-3 font-medium truncate max-w-xs" title="<?= htmlspecialchars($item['nama_barang']) ?>"><?= htmlspecialchars($item['nama_barang']) ?></td>
                            <td class="px-4 py-3 text-center text-gray-700 font-semibold">
                                <?= htmlspecialchars($item['satuan'] ?? '-') ?>
                            </td>
                            <td class="px-4 py-3"><?= formatRupiah($item['harga_beli']) ?></td>
                            <td class="px-4 py-3"><?= formatRupiah($item['harga_jual']) ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="<?= $item['stok'] <= 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> px-3 py-1 rounded-full text-sm font-semibold">
                                    <?= $item['stok'] ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($item['stok'] == 0): ?>
                                    <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Habis</span>
                                <?php elseif ($item['stok'] <= 5): ?>
                                    <span class="bg-red-400 text-white px-3 py-1 rounded-full text-xs font-semibold">Kritis</span>
                                <?php elseif ($item['stok'] <= 10): ?>
                                    <span class="bg-yellow-400 text-white px-3 py-1 rounded-full text-xs font-semibold">Rendah</span>
                                <?php else: ?>
                                    <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold">Aman</span>
                                <?php endif; ?>
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
            <a href="/laporan/stok?page=1<?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Pertama
            </a>
            <a href="/laporan/stok?page=<?= $current_page - 1 ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" 
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
                <a href="/laporan/stok?page=<?= $i ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" 
                   class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="/laporan/stok?page=<?= $current_page + 1 ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Berikutnya<i class="fas fa-chevron-right ml-1"></i>
            </a>
            <a href="/laporan/stok?page=<?= $total_pages ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Terakhir<i class="fas fa-chevron-right ml-1"></i>
            </a>
        <?php endif; ?>
    </div>
    <div class="text-center mt-3 text-sm text-gray-600">
        Halaman <?= $current_page ?> dari <?= $total_pages ?> (Total: <?= $total_items ?> barang)
    </div>
    <?php endif; ?>

    <!-- Legend -->
    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
        <h3 class="font-semibold text-gray-700 mb-3">Keterangan Status Stok:</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="flex items-center">
                <span class="bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold mr-2">Habis</span>
                <span class="text-sm text-gray-600">Stok = 0</span>
            </div>
            <div class="flex items-center">
                <span class="bg-red-400 text-white px-3 py-1 rounded-full text-xs font-semibold mr-2">Kritis</span>
                <span class="text-sm text-gray-600">Stok ≤ 5</span>
            </div>
            <div class="flex items-center">
                <span class="bg-yellow-400 text-white px-3 py-1 rounded-full text-xs font-semibold mr-2">Rendah</span>
                <span class="text-sm text-gray-600">Stok ≤ 10</span>
            </div>
            <div class="flex items-center">
                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold mr-2">Aman</span>
                <span class="text-sm text-gray-600">Stok > 10</span>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = 'Laporan Stok - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
