<?php ob_start(); ?>

<div class="app-card p-6 app-reveal">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-chart-bar text-blue-600 mr-2"></i>Laporan Barang Masuk
    </h2>

    <!-- Filter -->
    <form method="GET" class="mb-6 rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50/95 to-white/95 p-4 sm:p-5 sticky top-20 z-20 backdrop-blur shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Mulai</label>
                <input type="date" id="startDate" name="start" value="<?= $start ?>" 
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Tanggal Akhir</label>
                <input type="date" id="endDate" name="end" value="<?= $end ?>" 
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full app-btn-primary px-4 py-2.5 text-sm font-semibold">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
            <div class="flex items-end">
                <button type="button" class="w-full rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100 flex items-center justify-center gap-2" onclick="downloadPembelianExcel()">
                    <i class="fas fa-file-excel"></i>
                    <span>Download Excel</span>
                </button>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" onclick="setQuickDateRange(1)">Hari Ini</button>
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" onclick="setQuickDateRange(7)">7 Hari</button>
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" onclick="setQuickDateRange(30)">30 Hari</button>
            <button type="button" class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200" onclick="clearQuickDateRange()">Reset</button>
        </div>
    </form>

    <script>
    function formatDateInput(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function setQuickDateRange(days) {
        const startEl = document.getElementById('startDate');
        const endEl = document.getElementById('endDate');
        if (!startEl || !endEl) return;
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - (Number(days) - 1));
        startEl.value = formatDateInput(start);
        endEl.value = formatDateInput(end);
    }

    function clearQuickDateRange() {
        const startEl = document.getElementById('startDate');
        const endEl = document.getElementById('endDate');
        if (startEl) startEl.value = '';
        if (endEl) endEl.value = '';
    }

    function downloadPembelianExcel() {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        let url = '/laporan/pembelian/export?format=excel';
        if (start) url += '&start=' + start;
        if (end) url += '&end=' + end;
        window.location.href = url;
    }
    </script>

    <!-- Summary -->
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
        <p class="text-sm text-gray-600">Total Barang Masuk Periode Ini</p>
        <p class="text-2xl font-bold text-green-600"><?= formatRupiah($total) ?></p>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">No</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Kode Barang</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Barang</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tanggal</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Jam</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Jumlah</th>
                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Satuan</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Harga Satuan</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Total</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($pembelian)): ?>
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-gray-500">Tidak ada data barang masuk pada periode ini</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pembelian as $index => $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3"><?= (($current_page - 1) * 10) + $index + 1 ?></td>
                            <td class="px-4 py-3 font-mono text-sm text-gray-700"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></td>
                            <td class="px-4 py-3 font-medium"><?= htmlspecialchars($item['nama_barang']) ?></td>
                            <td class="px-4 py-3"><?= date('d/m/Y', strtotime($item['tanggal'])) ?></td>
                            <td class="px-4 py-3 text-gray-600"><?= date('H:i', strtotime($item['tanggal'])) ?></td>
                            <td class="px-4 py-3 text-center font-semibold"><?= $item['jumlah'] ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= htmlspecialchars($item['satuan'] ?? '-') ?></td>
                            <td class="px-4 py-3 text-right"><?= formatRupiah($item['harga_satuan']) ?></td>
                            <td class="px-4 py-3 text-right font-semibold text-green-600"><?= formatRupiah($item['total_harga']) ?></td>
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
            <a href="/laporan/pembelian?page=1&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Pertama
            </a>
            <a href="/laporan/pembelian?page=<?= $current_page - 1 ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
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
                <a href="/laporan/pembelian?page=<?= $i ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
                   class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="/laporan/pembelian?page=<?= $current_page + 1 ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Berikutnya<i class="fas fa-chevron-right ml-1"></i>
            </a>
            <a href="/laporan/pembelian?page=<?= $total_pages ?>&start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>" 
               class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Terakhir<i class="fas fa-chevron-right ml-1"></i>
            </a>
        <?php endif; ?>
    </div>
    <div class="text-center mt-3 text-sm text-gray-600">
        Halaman <?= $current_page ?> dari <?= $total_pages ?> (Total: <?= $total_items ?> barang masuk)
    </div>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean();
$title = 'Laporan Barang Masuk - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
