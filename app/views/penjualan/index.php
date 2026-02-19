<?php ob_start(); ?>

<?php 
    $grandTotal = 0;
    $totalItemTerjual = 0;
    $hutangBelumBayar = 0;
    foreach ($penjualan as $p) {
        $grandTotal += (float)($p['total_harga'] ?? 0);
        $totalItemTerjual += (int)($p['jumlah_item'] ?? 0);
        if (($p['hutang_status'] ?? '') === 'belum_bayar') {
            $hutangBelumBayar++;
        }
    }
    $totalTransaksiHalaman = count($penjualan);

    $paginationQuery = '';
    if (!empty($filter_tanggal_awal ?? '')) {
        $paginationQuery .= '&tanggal_awal=' . rawurlencode($filter_tanggal_awal);
    }
    if (!empty($filter_tanggal_akhir ?? '')) {
        $paginationQuery .= '&tanggal_akhir=' . rawurlencode($filter_tanggal_akhir);
    }
?>

<div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8">
    <div class="mb-6 space-y-5">
        <div class="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 via-white to-indigo-50 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-4">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 shadow-inner">
                        <i class="fas fa-cash-register text-xl"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Daftar Penjualan</h2>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <a href="/penjualan/create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 shadow">
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
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Tanggal Mulai</label>
                    <div class="relative">
                        <i class="fas fa-calendar-day text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($filter_tanggal_awal ?? '') ?>" class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <div class="md:col-span-5">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600 mb-2">Tanggal Akhir</label>
                    <div class="relative">
                        <i class="fas fa-calendar-check text-gray-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($filter_tanggal_akhir ?? '') ?>" class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <div class="md:col-span-2 flex flex-col sm:flex-row gap-2 items-stretch md:items-end">
                    <button type="submit" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 shadow">
                        <i class="fas fa-search"></i>
                        <span>Cari!</span>
                    </button>
                    <a href="/penjualan" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100">
                        <i class="fas fa-redo"></i>
                        <span>Reset</span>
                    </a>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
            <div class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 mb-3">
                    <i class="fas fa-wallet"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">Total Penjualan</p>
                <p class="text-2xl font-bold text-blue-700"><?= formatRupiah($grandTotal) ?></p>
                <p class="text-xs text-gray-500 mt-1">Akumulasi pada halaman ini</p>
            </div>
            <div class="rounded-2xl border border-indigo-200 bg-gradient-to-br from-indigo-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 mb-3">
                    <i class="fas fa-receipt"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-500">Jumlah Transaksi</p>
                <p class="text-2xl font-bold text-indigo-700"><?= number_format($totalTransaksiHalaman, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-1">Total data keseluruhan: <?= number_format($total_penjualan ?? 0, 0, ',', '.') ?></p>
            </div>
            <div class="rounded-2xl border border-green-200 bg-gradient-to-br from-green-50 to-white p-5 text-center shadow-sm">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 mb-3">
                    <i class="fas fa-boxes"></i>
                </span>
                <p class="text-xs font-semibold uppercase tracking-wide text-green-500">Item Terjual</p>
                <p class="text-2xl font-bold text-green-700"><?= number_format($totalItemTerjual, 0, ',', '.') ?></p>
                <p class="text-xs text-gray-500 mt-1">Ringkasan pada halaman ini</p>
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
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600 w-56">Item (Jumlah)</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Total</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Kembalian</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 w-32">Status</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-gray-600 w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($penjualan)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada data penjualan</td>
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
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($item['nama_pembeli'] ?? '') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center justify-center gap-2 rounded-full bg-gray-100 px-3 py-1.5 text-sm font-semibold text-gray-700">
                                    <i class="fas fa-box"></i>
                                    <span><?= number_format((int)($item['jumlah_item'] ?? 0), 0, ',', '.') ?> item</span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right align-middle">
                                <span class="inline-flex items-center justify-end gap-2 rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-600">
                                    <i class="fas fa-coins"></i>
                                    <span class="not-italic">
                                        <?= preg_replace('/^Rp\s*(\d+)/', '<span style="font-size:0.9em;display:inline;vertical-align:middle;">Rp</span> <span style="font-size:1.1em;display:inline;vertical-align:middle;">$1</span>', formatRupiah($item['total_harga'])) ?>
                                    </span>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
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
                                    <a href="/hutang?filter=belum_bayar" class="inline-flex items-center gap-1 px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700 hover:bg-red-200 transition">
                                        <i class="fas fa-exclamation-circle"></i>Belum Bayar
                                    </a>
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
