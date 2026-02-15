<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-chart-line text-blue-600 mr-2"></i>Laporan Penjualan
    </h2>

    <!-- Filter -->
    <form method="GET" class="mb-6 bg-gray-50 p-4 rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Mulai</label>
                <input type="date" id="startDate" name="start" value="<?= $start ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Akhir</label>
                <input type="date" id="endDate" name="end" value="<?= $end ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2" onclick="downloadPenjualanPDF()">
                    <i class="fas fa-file-pdf"></i>
                    <span>Download PDF</span>
                </button>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition flex items-center justify-center gap-2" onclick="downloadPenjualanExcel()">
                    <i class="fas fa-file-excel"></i>
                    <span>Download Excel</span>
                </button>
            </div>
        </div>
    </form>

    <script>
    function downloadPenjualanPDF() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        let url = '/laporan/penjualan/export?format=pdf';
        if (start) url += '&start=' + start;
        if (end) url += '&end=' + end;
        window.location.href = url;
    }

    function downloadPenjualanExcel() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        let url = '/laporan/penjualan/export?format=excel';
        if (start) url += '&start=' + start;
        if (end) url += '&end=' + end;
        window.location.href = url;
    }
    </script>

    <!-- Summary -->
    <?php 
        $totalQty = 0;
        foreach ($penjualan as $row) {
            $totalQty += (float)($row['jumlah'] ?? 0);
        }
    ?>
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <p class="text-sm text-gray-600">Total Penjualan Periode Ini</p>
                <p class="text-2xl font-bold text-blue-600"><?= formatRupiah($total) ?></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Jumlah Barang Terjual</p>
                <p class="text-2xl font-bold text-blue-600"><?= number_format($totalQty, 0, ',', '.') ?></p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">No</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Pengguna</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Kode Barang</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Barang</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Jam</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Jumlah</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Satuan</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Harga Jual</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($penjualan)): ?>
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-gray-500">Tidak ada data penjualan pada periode ini</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($penjualan as $index => $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= (($current_page - 1) * 10) + $index + 1 ?></td>
                            <td class="px-4 py-3 font-medium text-blue-600"><?= htmlspecialchars($item['username'] ?? '-') ?></td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-700"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($item['nama_barang']) ?></td>
                            <td class="px-4 py-3"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= date('H:i', strtotime($item['tanggal'])) ?></td>
                            <td class="px-4 py-3 text-center font-semibold"><?= $item['jumlah'] ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= htmlspecialchars($item['satuan'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-right"><?= formatRupiah($item['harga_satuan']) ?></td>
                            <td class="px-4 py-3 text-right font-semibold text-blue-600"><?= formatRupiah($item['total_harga']) ?></td>
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
            <a href="/laporan/penjualan?page=1&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Pertama
            </a>
            <a href="/laporan/penjualan?page=<?= $current_page - 1 ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
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
                <a href="/laporan/penjualan?page=<?= $i ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
                   class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="/laporan/penjualan?page=<?= $current_page + 1 ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Berikutnya<i class="fas fa-chevron-right ml-1"></i>
            </a>
            <a href="/laporan/penjualan?page=<?= $total_pages ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Terakhir<i class="fas fa-chevron-right ml-1"></i>
            </a>
        <?php endif; ?>
    </div>
    <div class="text-center mt-3 text-sm text-gray-600">
        Halaman <?= $current_page ?> dari <?= $total_pages ?> (Total: <?= $total_items ?> penjualan)
    </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean();
$title = 'Laporan Penjualan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
