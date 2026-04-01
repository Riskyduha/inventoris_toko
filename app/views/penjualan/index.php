<?php ob_start(); ?>

<?php 
    $grandTotal = $summary_total_penjualan ?? 0;
    $totalItemTerjual = $summary_total_item ?? 0;
    $hutangBelumBayar = $summary_hutang_belum ?? 0;
    $totalTransaksi = $summary_total_transaksi ?? ($total_penjualan ?? 0);
    $totalTransaksiHalaman = count($penjualan);
    $totalLabaBersih = $summary_total_laba_bersih ?? 0;
    $showProfitAdmin = !empty($show_profit_admin);

    $paginationQuery = '';
    if (!empty($filter_tanggal_awal ?? '')) {
        $paginationQuery .= '&tanggal_awal=' . rawurlencode($filter_tanggal_awal);
    }
    if (!empty($filter_tanggal_akhir ?? '')) {
        $paginationQuery .= '&tanggal_akhir=' . rawurlencode($filter_tanggal_akhir);
    }
?>

<div class="app-card p-6 sm:p-8 app-reveal">
    <div class="mb-6 space-y-5">
        <div class="rounded-2xl border border-teal-100 bg-gradient-to-r from-teal-50 via-white to-amber-50 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-teal-100 text-teal-700 shadow-inner">
                        <i class="fas fa-cash-register text-xl"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Daftar Penjualan</h2>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <a href="/penjualan/create" class="inline-flex items-center justify-center gap-2 rounded-lg app-btn-primary px-4 py-2.5 text-sm font-semibold shadow">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Penjualan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Tanggal -->
        <div class="rounded-2xl border border-gray-200 bg-gray-50/80 p-5 sm:p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-600">Filter Periode</p>
                    <p class="text-sm text-gray-500">Sesuaikan rentang tanggal untuk melihat penjualan</p>
                </div>
            </div>
            <form method="GET" action="/penjualan" class="grid grid-cols-1 md:grid-cols-12 gap-3">
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Tanggal Mulai</label>
                    <div class="relative">
                        <i class="fas fa-calendar-day text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($filter_tanggal_awal ?? '') ?>" class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-700 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Tanggal Akhir</label>
                    <div class="relative">
                        <i class="fas fa-calendar-check text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($filter_tanggal_akhir ?? '') ?>" class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-700 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-100">
                    </div>
                </div>
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Aksi</label>
                    <div class="flex flex-col sm:flex-row flex-wrap gap-2">
                        <button type="submit" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg app-btn-primary px-4 py-2.5 text-sm font-semibold shadow">
                            <i class="fas fa-search"></i>
                            <span>Terapkan Filter</span>
                        </button>
                        <a href="/penjualan" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">
                            <i class="fas fa-rotate-left"></i>
                            <span>Reset</span>
                        </a>
                    </div>
                </div>
                <div class="md:col-span-12">
                    <div class="rounded-xl border border-slate-200 bg-white p-3 sm:p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Ekspor Data Penjualan</p>
                                <p class="text-xs text-slate-500">Unduh data sesuai filter tanggal yang sedang aktif.</p>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <a href="/penjualan/export?format=excel&tanggal_awal=<?= rawurlencode($filter_tanggal_awal ?? '') ?>&tanggal_akhir=<?= rawurlencode($filter_tanggal_akhir ?? '') ?>" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg border border-teal-200 bg-teal-50 px-4 py-2.5 text-sm font-semibold text-teal-700 transition hover:bg-teal-100 hover:border-teal-300">
                                    <i class="fas fa-file-excel"></i>
                                    <span>Unduh Excel Detail</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 <?= $showProfitAdmin ? 'xl:grid-cols-5' : 'xl:grid-cols-4' ?> gap-3 sm:gap-4">
            <div class="rounded-2xl border border-teal-200 bg-gradient-to-br from-teal-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-700 mb-3">
                    <i class="fas fa-wallet"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-teal-600">Total Penjualan</p>
                <p class="text-2xl font-bold text-teal-700"><?= formatRupiah($grandTotal) ?></p>
                <p class="text-xs text-gray-500 mt-1">Akumulasi periode terpilih</p>
            </div>
            <?php if ($showProfitAdmin): ?>
            <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 mb-3">
                    <i class="fas fa-chart-line"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">Laba Bersih</p>
                <p class="text-2xl font-bold <?= $totalLabaBersih >= 0 ? 'text-emerald-700' : 'text-red-600' ?>"><?= formatRupiah($totalLabaBersih) ?></p>
                <p class="text-xs text-gray-500 mt-1">Dari barang terjual di periode ini</p>
            </div>
            <?php endif; ?>
            <div class="rounded-2xl border border-cyan-200 bg-gradient-to-br from-cyan-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-cyan-100 text-cyan-700 mb-3">
                    <i class="fas fa-receipt"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-cyan-600">Jumlah Transaksi</p>
                <p class="text-2xl font-bold text-cyan-700"><?= number_format($totalTransaksi, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-1">Ditampilkan di halaman ini: <?= number_format($totalTransaksiHalaman, 0, ',', '.') ?> transaksi</p>
            </div>
            <div class="rounded-2xl border border-green-200 bg-gradient-to-br from-green-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 mb-3">
                    <i class="fas fa-boxes"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-green-500">Item Terjual</p>
                <p class="text-2xl font-bold text-green-700"><?= number_format($totalItemTerjual, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-1">Ringkasan periode terpilih</p>
            </div>
            <div class="rounded-2xl border border-orange-200 bg-gradient-to-br from-orange-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-orange-100 text-orange-600 mb-3">
                    <i class="fas fa-exclamation-circle"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-500">Belum Lunas</p>
                <p class="text-2xl font-bold <?= $hutangBelumBayar > 0 ? 'text-orange-600' : 'text-gray-500' ?>"><?= number_format($hutangBelumBayar, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-1">
                    <?= $hutangBelumBayar > 0 ? 'Transaksi menunggu pelunasan' : 'Seluruh transaksi lunas' ?>
                </p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-sm">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-100/80">
                <tr>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 w-12">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-44">Pembeli</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-56">Jumlah</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Total</th>
                    <?php if ($showProfitAdmin): ?>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Laba Bersih</th>
                    <?php endif; ?>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Kembalian</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($penjualan)): ?>
                    <tr>
                        <td colspan="<?= $showProfitAdmin ? '9' : '8' ?>" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada data penjualan</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($penjualan as $index => $item): ?>
                        <tr class="transition duration-200 <?= ($item['hutang_status'] ?? '') === 'belum_bayar' ? 'bg-orange-50/60 hover:bg-orange-100/80' : 'hover:bg-blue-50/70' ?>">
                            <td class="px-6 py-4 text-center text-sm font-semibold text-gray-700"><?= (($current_page - 1) * 10) + $index + 1 ?></td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-gray-800"><?= date('d M Y', strtotime($item['tanggal'])) ?></span>
                                    <span class="text-gray-500 text-xs"><?= date('H:i', strtotime($item['tanggal'])) ?> WIB</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-800"><?= htmlspecialchars(trim((string)($item['nama_pembeli'] ?? '')) !== '' ? $item['nama_pembeli'] : '-') ?></p>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center justify-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-700">
                                    <span><?= number_format((int)($item['jumlah_item'] ?? 0), 0, ',', '.') ?></span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right align-middle whitespace-nowrap">
                                <span class="inline-flex items-center justify-end gap-2 rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-600">
                                    <i class="fas fa-coins"></i>
                                    <span class="not-italic">
                                        <?= preg_replace('/^Rp\s*(\d+)/', '<span style="font-size:0.9em;display:inline;vertical-align:middle;">Rp</span> <span style="font-size:1.1em;display:inline;vertical-align:middle;">$1</span>', formatRupiah($item['total_harga'])) ?>
                                    </span>
                                </span>
                            </td>
                            <?php if ($showProfitAdmin): ?>
                            <td class="px-6 py-4 text-right align-middle whitespace-nowrap">
                                <?php $labaBersihRow = (float)($item['laba_bersih'] ?? 0); ?>
                                <span class="inline-flex items-center justify-end gap-2 rounded-full px-3 py-1 text-sm font-semibold <?= $labaBersihRow >= 0 ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' ?>">
                                    <i class="fas <?= $labaBersihRow >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?>"></i>
                                    <span class="not-italic">
                                        <?= preg_replace('/^Rp\s*(\d+)/', '<span style="font-size:0.9em;display:inline;vertical-align:middle;">Rp</span> <span style="font-size:1.1em;display:inline;vertical-align:middle;">$1</span>', formatRupiah($labaBersihRow)) ?>
                                    </span>
                                </span>
                            </td>
                            <?php endif; ?>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <span class="inline-flex items-center justify-end gap-2 rounded-full px-3 py-1 text-sm font-semibold <?= $item['kembalian'] >= 0 ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600' ?>">
                                    <i class="fas <?= $item['kembalian'] >= 0 ? 'fa-arrow-circle-up' : 'fa-arrow-circle-down' ?>"></i>
                                    <span class="not-italic">
                                        <?= preg_replace('/^Rp\s*(\d+)/', '<span style="font-size:0.9em;display:inline;vertical-align:middle;">Rp</span> <span style="font-size:1.1em;display:inline;vertical-align:middle;">$1</span>', formatRupiah($item['kembalian'])) ?>
                                    </span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if (empty($item['hutang_status']) || $item['hutang_status'] === null): ?>
                                    <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700">
                                        <i class="fas fa-check-circle"></i>Lunas
                                    </span>
                                <?php elseif ($item['hutang_status'] == 'belum_bayar'): ?>
                                    <a href="/hutang?filter=belum_bayar" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700 hover:bg-red-200 transition mb-1">
                                        <i class="fas fa-exclamation-circle"></i>Belum Bayar
                                    </a>
                                    <?php $agingHari = (int)($item['aging_hari'] ?? 0); ?>
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">
                                            <i class="fas fa-hourglass-half"></i><?= $agingHari > 0 ? $agingHari . ' hari lewat tempo' : 'Belum jatuh tempo' ?>
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 text-purple-700">
                                            <i class="fas fa-calendar-day"></i><?= !empty($item['jatuh_tempo']) ? date('d/m/Y', strtotime($item['jatuh_tempo'])) : '-' ?>
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                            <i class="fas fa-wallet"></i><?= formatRupiah((float)($item['jumlah_hutang'] ?? 0)) ?>
                                        </span>
                                    </div>
                                <?php elseif ($item['hutang_status'] == 'lunas'): ?>
                                    <a href="/hutang?filter=lunas" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 transition">
                                        <i class="fas fa-check-double"></i>Lunas
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="/penjualan/detail/<?= $item['id_penjualan'] ?>" class="inline-flex items-center justify-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-600 hover:text-white" title="Detail">
                                        <i class="fas fa-eye"></i><span>Detail</span>
                                    </a>
                                    <a href="/penjualan/edit/<?= $item['id_penjualan'] ?>" class="inline-flex items-center justify-center gap-1 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-1.5 text-xs font-semibold text-yellow-600 transition hover:bg-yellow-500 hover:text-white" title="Edit">
                                        <i class="fas fa-edit"></i><span>Edit</span>
                                    </a>
                                    <a href="/penjualan/delete/<?= $item['id_penjualan'] ?>" 
                                       onclick="return confirm('Yakin ingin menghapus penjualan ini? Stok akan dikembalikan.')" 
                                       class="inline-flex items-center justify-center gap-1 rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-600 hover:text-white" title="Hapus">
                                        <i class="fas fa-trash"></i><span>Hapus</span>
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
    <div class="flex flex-wrap justify-center items-center gap-2 mt-8">
        <?php if ($current_page > 1): ?>
            <a href="/penjualan?page=1<?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-blue-300 hover:text-blue-600">
                <i class="fas fa-angle-double-left"></i><span>Pertama</span>
            </a>
            <a href="/penjualan?page=<?= $current_page - 1 ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-blue-300 hover:text-blue-600">
                <i class="fas fa-chevron-left"></i><span>Sebelumnya</span>
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
                <span class="inline-flex items-center justify-center rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm"><?= $i ?></span>
            <?php else: ?>
                <a href="/penjualan?page=<?= $i ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
                   class="inline-flex items-center justify-center rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-blue-300 hover:text-blue-600">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($end_page < $total_pages): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="/penjualan?page=<?= $current_page + 1 ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-blue-300 hover:text-blue-600">
                <span>Berikutnya</span><i class="fas fa-chevron-right"></i>
            </a>
            <a href="/penjualan?page=<?= $total_pages ?><?= htmlspecialchars($paginationQuery, ENT_QUOTES, 'UTF-8') ?>" 
               class="inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 transition hover:border-blue-300 hover:text-blue-600">
                <span>Terakhir</span><i class="fas fa-angle-double-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <div class="mt-4 flex flex-col items-center gap-2 text-sm text-gray-600">
        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-4 py-1 font-medium text-gray-700">
            <i class="fas fa-info-circle text-blue-500"></i>
            <span>Halaman <?= $current_page ?> dari <?= $total_pages ?> · Total data <?= number_format($total_penjualan ?? 0, 0, ',', '.') ?> penjualan</span>
        </span>
        <span class="text-xs text-gray-500">Menampilkan <?= number_format($totalTransaksiHalaman, 0, ',', '.') ?> transaksi pada halaman ini</span>
    </div>
    <?php endif; ?>

<?php 
$content = ob_get_clean();
$title = 'Daftar Penjualan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
