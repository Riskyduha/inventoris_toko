<?php ob_start(); ?>

<style>
.filter-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.9rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid #d1d5db;
    background-color: #ffffff;
    color: #374151;
    transition: all 0.2s ease-in-out;
}

.filter-pill:hover {
    background-color: #f3f4f6;
}

.filter-pill-active {
    border-color: #2563eb;
    background-color: #2563eb;
    color: #ffffff;
    box-shadow: 0 10px 25px -15px rgba(37, 99, 235, 0.9);
}

.filter-pill-active:hover {
    background-color: #1d4ed8;
}
</style>

<div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 space-y-5 sm:space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-100 text-blue-600">
                <i class="fas fa-box"></i>
            </span>
            <span>Daftar Barang</span>
        </h2>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <a href="/barang/export<?= !empty($selected_kategori) ? '?kategori=' . (int)$selected_kategori : '' ?>" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg border border-green-500 bg-white px-4 py-2.5 text-sm font-semibold text-green-600 transition hover:bg-green-50">
                <i class="fas fa-file-excel"></i>
                <span>Download Excel</span>
            </a>
            <a href="/barang/create" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-plus"></i>
                <span>Tambah Barang</span>
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input
                    id="searchBarang"
                    type="text"
                    placeholder="Cari nama, kode, kategori, atau satuan barang..."
                    class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-700 placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                />
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <button 
                    id="searchBtn"
                    onclick="triggerSearch()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                    <span>Cari</span>
                </button>
                <button
                    onclick="clearSearch()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100"
                >
                    <i class="fas fa-undo"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    </div>

    <?php if (!empty($kategori)): ?>
    <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-3">Filter Kategori</p>
        <div class="flex flex-wrap items-center gap-2">
            <button onclick="filterByKategori('all')" data-kategori-button="true" data-kategori-id="all" class="filter-pill filter-pill-active" id="kat-all">Semua</button>
            <?php foreach ($kategori as $kat): ?>
                <button onclick="filterByKategori('<?= $kat['id_kategori'] ?>')" data-kategori-button="true" data-kategori-id="<?= $kat['id_kategori'] ?>" class="filter-pill" id="kat-<?= $kat['id_kategori'] ?>">
                    <?= htmlspecialchars($kat['nama_kategori']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
    <div class="grid grid-cols-1 gap-3 sm:gap-4">
        <div class="rounded-xl border border-purple-200 bg-gradient-to-br from-purple-50 to-white p-4 sm:p-5 text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-purple-600 mb-3">
                <i class="fas fa-layer-group"></i>
            </span>
            <p class="text-xs font-semibold uppercase tracking-wide text-purple-500">Total Stok</p>
            <p class="text-2xl font-bold text-purple-700" id="sum_stok"><?= number_format((int)($totals['total_stok'] ?? 0), 0, ',', '.') ?></p>
        </div>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-4 sm:p-5 text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 mb-3">
                <i class="fas fa-money-bill-wave"></i>
            </span>
            <p class="text-2xl font-bold text-blue-700" id="sum_beli"><?= formatRupiah($totals['total_harga_beli'] ?? 0) ?></p>
        </div>
        <div class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-white p-4 sm:p-5 text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 mb-3">
                <i class="fas fa-tags"></i>
            </span>
            <p class="text-xs font-semibold uppercase tracking-wide text-green-500">Total Harga Jual</p>
            <p class="text-2xl font-bold text-green-700" id="sum_jual"><?= formatRupiah($totals['total_harga_jual'] ?? 0) ?></p>
        </div>
        <div class="rounded-xl border border-purple-200 bg-gradient-to-br from-purple-50 to-white p-4 sm:p-5 text-center">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 text-purple-600 mb-3">
                <i class="fas fa-boxes"></i>
            </span>
            <p class="text-xs font-semibold uppercase tracking-wide text-purple-500">Total Stok</p>
            <p class="text-2xl font-bold text-purple-700" id="sum_stok"><?= number_format((int)($totals['total_stok'] ?? 0), 0, ',', '.') ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div id="kategori_summary" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 hidden">
        <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-white p-4 sm:p-5 text-center">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-600 mb-3">
                <i class="fas fa-money-bill-wave"></i>
            </span>
            <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">Total Harga Beli</p>
            <p class="text-sm text-gray-500">Kategori: <span id="kategori_name" class="font-semibold text-gray-700">-</span></p>
            <p class="mt-2 text-xl font-bold text-blue-700" id="kategori_beli">Rp 0</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-white p-4 sm:p-5 text-center">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-green-100 text-green-600 mb-3">
                <i class="fas fa-tags"></i>
            </span>
            <p class="text-xs font-semibold uppercase tracking-wide text-green-500">Total Harga Jual</p>
            <p class="text-sm text-gray-500">Kategori: <span id="kategori_name2" class="font-semibold text-gray-700">-</span></p>
            <p class="mt-2 text-xl font-bold text-green-700" id="kategori_jual">Rp 0</p>
        </div>
        <div class="rounded-xl border border-purple-200 bg-gradient-to-br from-purple-50 to-white p-4 sm:p-5 text-center">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-purple-100 text-purple-600 mb-3">
                <i class="fas fa-layer-group"></i>
            </span>
            <p class="text-xs font-semibold uppercase tracking-wide text-purple-500">Total Stok</p>
            <p class="text-sm text-gray-500">Kategori: <span id="kategori_name3" class="font-semibold text-gray-700">-</span></p>
            <p class="mt-2 text-xl font-bold text-purple-700" id="kategori_stok">0</p>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div class="text-sm text-gray-600">
            Menampilkan <span id="visible_count">0</span> dari <span id="total_count">0</span> barang (halaman ini)
        </div>
        <div id="search_info_container" class="hidden text-sm text-blue-600 font-medium">
            <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1">
                <i class="fas fa-info-circle"></i>
                <span>Hasil pencarian untuk: <strong id="search_term_display"></strong></span>
            </span>
        </div>
    </div>

    <!-- Search Results View (Hidden by default) -->
    <div id="search_results_container" class="hidden">
        <div id="search_results_mobile" class="block md:hidden space-y-3"></div>
        <div id="search_results_table" class="hidden md:block overflow-x-auto"></div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-3" data-view="mobile-container">
        <div id="search_results_mobile" class="hidden space-y-3"></div>
        <div id="normal_barang_mobile">
        <?php if (empty($barang)): ?>
            <div class="text-center py-8 text-gray-400 italic">Tidak ada data barang</div>
        <?php else: ?>
            <?php foreach ($barang as $index => $item): ?>
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md" data-item="barang-card" data-kategori="<?= $item['id_kategori'] ?>" data-beli="<?= $item['harga_beli'] ?>" data-jual="<?= $item['harga_jual'] ?>" data-stok="<?= $item['stok'] ?>" data-search="<?= htmlspecialchars(strtolower(trim(($item['kode_barang'] ?? '') . ' ' . ($item['nama_barang'] ?? '') . ' ' . ($item['nama_kategori'] ?? '') . ' ' . ($item['satuan'] ?? '') . ' ' . (!empty($item['updated_at']) ? date('Y-m-d H:i', strtotime($item['updated_at'])) : '')))) ?>" data-updated="<?= htmlspecialchars($item['updated_at'] ?? '') ?>">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="font-mono text-xs text-gray-500 mb-1"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></div>
                            <h3 class="font-bold text-gray-800 mb-1"><?= htmlspecialchars($item['nama_barang']) ?></h3>
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                <?= htmlspecialchars($item['nama_kategori']) ?>
                            </span>
                        </div>
                        <span class="<?= $item['stok'] <= 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?> inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold">
                            <?= $item['stok'] ?> <?= htmlspecialchars($item['satuan']) ?>
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3 text-sm">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <div>
                            <div class="text-gray-500 text-xs">Harga Beli</div>
                            <div class="font-semibold text-gray-800"><?= formatRupiah($item['harga_beli']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div>
                            <div class="text-gray-500 text-xs">Harga Jual</div>
                            <div class="font-semibold text-gray-800"><?= formatRupiah($item['harga_jual']) ?></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-center text-center text-xs text-gray-500 mb-3">
                        <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-2.5 py-1">
                            <i class="fas fa-clock text-blue-500"></i>
                            <span><?= !empty($item['updated_at']) ? formatTanggal($item['updated_at']) : '-' ?></span>
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <a href="/barang/edit/<?= $item['id_barang'] ?>" class="flex-1 rounded-lg bg-yellow-100 py-2 text-center text-sm font-semibold text-yellow-700 transition hover:bg-yellow-500 hover:text-white">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button onclick="confirmDelete(<?= $item['id_barang'] ?>)" class="flex-1 rounded-lg bg-red-100 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-600 hover:text-white">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto" data-view="desktop-container">
        <table class="w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <thead class="bg-blue-50 border-b border-blue-200">
                <tr>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-12">No</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-20">Kode</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-800" style="min-width: 12rem;">Nama Barang</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-28">Kategori</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Satuan</th>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Beli</th>
                    <?php endif; ?>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Jual</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Stok</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-40">Update Terakhir</th>
                    <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($barang)): ?>
                    <tr>
                        <td colspan="<?= (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 10 : 9 ?>" class="px-6 py-8 text-center text-gray-400 italic">Tidak ada data barang</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($barang as $index => $item): ?>
                        <tr class="transition duration-200 hover:bg-blue-50/70" data-item="barang-row" data-kategori="<?= $item['id_kategori'] ?>" data-beli="<?= $item['harga_beli'] ?>" data-jual="<?= $item['harga_jual'] ?>" data-stok="<?= $item['stok'] ?>" data-search="<?= htmlspecialchars(strtolower(trim(($item['kode_barang'] ?? '') . ' ' . ($item['nama_barang'] ?? '') . ' ' . ($item['nama_kategori'] ?? '') . ' ' . ($item['satuan'] ?? '') . ' ' . (!empty($item['updated_at']) ? date('Y-m-d H:i', strtotime($item['updated_at'])) : '')))) ?>" data-updated="<?= htmlspecialchars($item['updated_at'] ?? '') ?>">
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-700"><?= (($current_page - 1) * $items_per_page) + $index + 1 ?></td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-600 whitespace-nowrap"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></td>
                            <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">
                                <?= htmlspecialchars($item['nama_barang']) ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                    <?= htmlspecialchars($item['nama_kategori']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 font-medium"><?= htmlspecialchars($item['satuan']) ?></td>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap"><?= formatRupiah($item['harga_beli']) ?></td>
                            <?php endif; ?>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap"><?= formatRupiah($item['harga_jual']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="<?= $item['stok'] <= 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?> inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold">
                                    <?= $item['stok'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <i class="fas fa-clock text-blue-500"></i>
                                    <span><?= !empty($item['updated_at']) ? formatTanggal($item['updated_at']) : '-' ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="/barang/edit/<?= $item['id_barang'] ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-yellow-100 text-yellow-700 transition hover:bg-yellow-500 hover:text-white">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <a href="/barang/delete/<?= $item['id_barang'] ?>" 
                                       onclick="return confirm('Yakin ingin menghapus barang ini?')" 
                                       class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-700 transition hover:bg-red-600 hover:text-white">
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
            <a href="/barang?page=1<?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Pertama
            </a>
            <a href="/barang?page=<?= $current_page - 1 ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
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
                <a href="/barang?page=<?= $i ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                    <?= $i ?>
                </a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($end_page < $total_pages): ?>
            <span class="text-gray-600">...</span>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="/barang?page=<?= $current_page + 1 ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Berikutnya<i class="fas fa-chevron-right ml-1"></i>
            </a>
            <a href="/barang?page=<?= $total_pages ?><?= !empty($selected_kategori) ? '&kategori=' . (int)$selected_kategori : '' ?>" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
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

const currentPage = <?= (int)$current_page ?>;
const itemsPerPage = <?= (int)$items_per_page ?>;
let currentKategori = <?= json_encode($selected_kategori !== null ? (string)$selected_kategori : 'all') ?>;
const userRole = <?= json_encode($_SESSION['role'] ?? 'kasir') ?>;
let currentQuery = '';
let currentSearchPage = 1;
let currentSearchTotal = 0;
let currentSearchTotalPages = 0;

function setActiveKategoriButton(katId) {
    const normalizedId = String(katId);
    document.querySelectorAll('[data-kategori-button="true"]').forEach(btn => {
        const btnId = String(btn.getAttribute('data-kategori-id'));
        if (btnId === normalizedId) {
            btn.classList.add('filter-pill-active');
        } else {
            btn.classList.remove('filter-pill-active');
        }
    });
}

function filterKategori(katId) {
    currentKategori = String(katId);
    applyFilters();

}

function applyFilters() {
    const query = (currentQuery || '').trim().toLowerCase();
    
    // Jika search kosong, sembunyikan search results dan tampilkan normal view
    if (query.length === 0) {
        const searchContainer = document.getElementById('search_results_container');
        const mobileContainer = document.querySelector('[data-view="mobile-container"]');
        const desktopContainer = document.querySelector('[data-view="desktop-container"]');
        
        if (searchContainer) searchContainer.classList.add('hidden');
        if (mobileContainer) mobileContainer.style.display = '';
        if (desktopContainer) desktopContainer.style.display = '';
        
        const paginationDiv = document.querySelector('.flex.justify-center.items-center.gap-2.mt-6');
        if (paginationDiv) paginationDiv.style.display = '';
        
        const searchInfoContainer = document.getElementById('search_info_container');
        if (searchInfoContainer) searchInfoContainer.classList.add('hidden');
    }

    document.querySelectorAll('[data-item="barang-row"]').forEach(row => {
        const matchKat = currentKategori === 'all' || String(row.getAttribute('data-kategori')) === currentKategori;
        const searchData = (row.getAttribute('data-search') || '').toLowerCase();
        const matchSearch = !query || searchData.includes(query);
        row.style.display = (matchKat && matchSearch) ? '' : 'none';
    });

    document.querySelectorAll('[data-item="barang-card"]').forEach(card => {
        const matchKat = currentKategori === 'all' || String(card.getAttribute('data-kategori')) === currentKategori;
        const searchData = (card.getAttribute('data-search') || '').toLowerCase();
        const matchSearch = !query || searchData.includes(query);
        card.style.display = (matchKat && matchSearch) ? '' : 'none';
    });

    updateRowNumbers();
    updateVisibleCount();
    updateKategoriSummary();
    
    // Show/hide pagination
    const paginationDiv = document.querySelector('.flex.justify-center.items-center.gap-2.mt-6');
    if (paginationDiv) {
        paginationDiv.style.display = (query.length === 0) ? '' : 'none';
    }
    
    // Show/hide search info
    const searchInfoContainer = document.getElementById('search_info_container');
    const searchTermDisplay = document.getElementById('search_term_display');
    if (searchInfoContainer) {
        if (query.length > 0) {
            searchInfoContainer.classList.remove('hidden');
            if (searchTermDisplay) {
                searchTermDisplay.textContent = currentQuery;
            }
        } else {
            searchInfoContainer.classList.add('hidden');
            if (searchTermDisplay) {
                searchTermDisplay.textContent = '';
            }
        }
    }
}

function updateRowNumbers() {
    const visibleRows = Array.from(document.querySelectorAll('tbody tr[data-item="barang-row"]')).filter(row => row.style.display !== 'none');
    visibleRows.forEach((row, index) => {
        const noCell = row.querySelector('td:first-child');
        if (noCell) {
            noCell.textContent = ((currentPage - 1) * itemsPerPage) + index + 1;
        }
    });
}

function updateVisibleCount() {
    const totalRows = document.querySelectorAll('tbody tr[data-item="barang-row"]').length;
    const visibleRows = Array.from(document.querySelectorAll('tbody tr[data-item="barang-row"]')).filter(row => row.style.display !== 'none').length;
    const visibleEl = document.getElementById('visible_count');
    const totalEl = document.getElementById('total_count');
    if (visibleEl) visibleEl.textContent = visibleRows.toLocaleString('id-ID');
    if (totalEl) totalEl.textContent = totalRows.toLocaleString('id-ID');
}

function formatRupiah(num) {
    const value = Number(num) || 0;
    return 'Rp ' + value.toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function formatDateDisplay(value) {
    if (!value) return '-';
    let normalized = value;
    if (typeof normalized === 'string' && normalized.includes(' ')) {
        normalized = normalized.replace(' ', 'T');
    }
    const parsed = new Date(normalized);
    if (Number.isNaN(parsed.getTime())) {
        return value;
    }
    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    }).format(parsed);
}

function updateKategoriSummary() {
    const summaryEl = document.getElementById('kategori_summary');
    const nameEl = document.getElementById('kategori_name');
    const nameEl2 = document.getElementById('kategori_name2');
    const nameEl3 = document.getElementById('kategori_name3');
    const stokEl = document.getElementById('kategori_stok');
    const beliEl = document.getElementById('kategori_beli');
    const jualEl = document.getElementById('kategori_jual');
    if (!summaryEl || !nameEl || !stokEl) return;

    if (currentKategori === 'all') {
        nameEl.textContent = '-';
        if (nameEl2) nameEl2.textContent = '-';
        if (nameEl3) nameEl3.textContent = '-';
        summaryEl.classList.add('hidden');
        return;
    }

    let totalBeli = 0;
    let totalJual = 0;
    let totalStok = 0;

    if (currentQuery) {
        const rows = Array.from(document.querySelectorAll('tbody tr[data-item="barang-row"]')).filter(row => row.style.display !== 'none');
        rows.forEach(row => {
            const hargaBeli = parseFloat(row.getAttribute('data-beli')) || 0;
            const hargaJual = parseFloat(row.getAttribute('data-jual')) || 0;
            const stok = parseFloat(row.getAttribute('data-stok')) || 0;
            totalBeli += hargaBeli * stok;
            totalJual += hargaJual * stok;
            totalStok += stok;
        });
    } else if (totalsByKategori[currentKategori]) {
        const data = totalsByKategori[currentKategori];
        totalBeli = data.total_harga_beli || 0;
        totalJual = data.total_harga_jual || 0;
        totalStok = data.total_stok || 0;
    }

    const kategoriLabel = kategoriNames[currentKategori] || '-';
    nameEl.textContent = kategoriLabel;
    if (nameEl2) nameEl2.textContent = kategoriLabel;
    if (nameEl3) nameEl3.textContent = kategoriLabel;
    if (beliEl) beliEl.textContent = formatRupiah(totalBeli);
    if (jualEl) jualEl.textContent = formatRupiah(totalJual);
    stokEl.textContent = (totalStok || 0).toLocaleString('id-ID');
    summaryEl.classList.remove('hidden');
}

// Search with pagination support
async function performSearch(query, page = 1) {
    if (!query || query.trim().length === 0) {
        applyFilters();
        return;
    }

    try {
        const kategoriParam = currentKategori && currentKategori !== 'all' ? `&kategori=${currentKategori}` : '';
        const url = `/api/search-barang?q=${encodeURIComponent(query)}&page=${page}${kategoriParam}`;
        console.log('Fetching search:', url);
        const response = await fetch(url);
        console.log('Response status:', response.status);
        const rawBody = await response.text();

        if (!response.ok) {
            console.error('Search request failed:', response.status, rawBody);

            if (currentKategori && currentKategori !== 'all') {
                currentSearchPage = 1;
                currentSearchTotal = 0;
                currentSearchTotalPages = 0;

                renderSearchResults([], {
                    results: [],
                    total: 0,
                    page: 1,
                    per_page: itemsPerPage,
                    total_pages: 0
                });

                const kategoriSummaryEl = document.getElementById('kategori_summary');
                if (kategoriSummaryEl) kategoriSummaryEl.classList.add('hidden');
                return;
            }

            const message = rawBody && rawBody.trim().length > 0
                ? rawBody.trim()
                : `Server error (${response.status})`;
            throw new Error(message);
        }

        let data;
        if (rawBody && rawBody.trim().length > 0) {
            try {
                data = JSON.parse(rawBody);
            } catch (parseError) {
                console.error('Gagal mem-parsing respon pencarian:', parseError, rawBody);
                throw new Error('Respons server tidak valid. Silakan coba lagi.');
            }
        } else {
            data = {
                results: [],
                total: 0,
                page: 1,
                per_page: itemsPerPage,
                total_pages: 0
            };
        }

        console.log('Search results:', data);
        
        currentSearchPage = data.page || 1;
        currentSearchTotal = data.total || 0;
        currentSearchTotalPages = data.total_pages || 0;
        
        renderSearchResults(data.results || [], data);
    } catch (error) {
        console.error('Search error:', error);
        alert('Error saat mencari: ' + (error && error.message ? error.message : 'Terjadi kesalahan tak terduga.'));
    }
}

// Trigger search function
function triggerSearch() {
    const searchInput = document.getElementById('searchBarang');
    if (!searchInput) return;
    
    currentQuery = searchInput.value || '';
    console.log('Searching for:', currentQuery);
    
    if (currentQuery.trim().length === 0) {
        loadAllBarang(1);
        return;
    }
    
    performSearch(currentQuery, 1);
}

const searchInput = document.getElementById('searchBarang');
if (searchInput) {
    console.log('Search input found:', searchInput);
    
    // Support Enter key to search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            triggerSearch();
        }
    });
} else {
    console.error('Search input element not found! Check if element with id="searchBarang" exists');
}

// Render hasil search ke halaman
function renderSearchResults(results, apiResponse = {}) {
    const searchContainer = document.getElementById('search_results_container');
    const mobileResults = document.getElementById('search_results_mobile');
    const tableResults = document.getElementById('search_results_table');
    const mobileContainer = document.querySelector('[data-view="mobile-container"]');
    const desktopContainer = document.querySelector('[data-view="desktop-container"]');
    
    if (!searchContainer || !mobileResults || !tableResults) return;
    
    const currentPage = apiResponse.page || 1;
    const totalResults = apiResponse.total || 0;
    const totalPages = apiResponse.total_pages || 0;
    
    // Update counter
    const visibleEl = document.getElementById('visible_count');
    const totalEl = document.getElementById('total_count');
    if (visibleEl) visibleEl.textContent = results.length.toLocaleString('id-ID');
    if (totalEl) totalEl.textContent = totalResults.toLocaleString('id-ID');

    const searchInfoContainer = document.getElementById('search_info_container');
    const searchTermDisplay = document.getElementById('search_term_display');
    if (searchInfoContainer) {
        if ((currentQuery || '').trim().length > 0) {
            searchInfoContainer.classList.remove('hidden');
            if (searchTermDisplay) {
                searchTermDisplay.textContent = currentQuery;
            }
        } else {
            searchInfoContainer.classList.add('hidden');
            if (searchTermDisplay) {
                searchTermDisplay.textContent = '';
            }
        }
    }

    const kategoriLabel = currentKategori && currentKategori !== 'all'
        ? htmlSpecialChars(kategoriNames[currentKategori] || '-')
        : null;
    const noResultsText = kategoriLabel
        ? `Tidak ada barang di kategori ${kategoriLabel} yang cocok dengan pencarian ini`
        : 'Tidak ada barang yang cocok dengan pencarian';
    
    // Render mobile
    if (results.length === 0) {
        mobileResults.innerHTML = `<div class="text-center py-8 text-gray-400 italic">${noResultsText}</div>`;
    } else {
        mobileResults.innerHTML = results.map((item, index) => `
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="font-mono text-xs text-gray-500 mb-1">${htmlSpecialChars(item.kode_barang || '-')}</div>
                            <h3 class="font-bold text-gray-800 mb-1">${htmlSpecialChars(item.nama_barang)}</h3>
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                ${htmlSpecialChars(item.nama_kategori || '-')}
                            </span>
                        </div>
                        <span class="${item.stok <= 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'} inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold">
                            ${item.stok} ${htmlSpecialChars(item.satuan || 'pcs')}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mb-3 text-sm">
                        ${userRole === 'admin' ? `
                        <div>
                            <div class="text-gray-500 text-xs">Harga Beli</div>
                            <div class="font-semibold text-gray-800">${formatRupiah(item.harga_beli)}</div>
                        </div>
                        ` : ''}
                        <div>
                            <div class="text-gray-500 text-xs">Harga Jual</div>
                            <div class="font-semibold text-gray-800">${formatRupiah(item.harga_jual)}</div>
                        </div>
                    </div>
                        <div class="flex items-center justify-center text-center text-xs text-gray-500 mb-3">
                            <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-2.5 py-1">
                            <i class="fas fa-clock text-blue-500"></i>
                            <span>${formatDateDisplay(item.updated_at)}</span>
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <a href="/barang/edit/${item.id_barang}" class="flex-1 rounded-lg bg-yellow-100 py-2 text-center text-sm font-semibold text-yellow-700 transition hover:bg-yellow-500 hover:text-white">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button onclick="confirmDelete(${item.id_barang})" class="flex-1 rounded-lg bg-red-100 py-2 text-sm font-semibold text-red-700 transition hover:bg-red-600 hover:text-white">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </div>
                </div>
            `).join('');
    }
    
    // Render desktop
    if (results.length === 0) {
        tableResults.innerHTML = `<div class="text-center py-8 text-gray-400 italic">${noResultsText}</div>`;
    } else {
        tableResults.innerHTML = `
            <table class="w-full rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <thead class="bg-blue-50 border-b border-blue-200">
                    <tr>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-12">No</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-20">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-800" style="min-width: 12rem;">Nama Barang</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-28">Kategori</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Satuan</th>
                        ${userRole === 'admin' ? `<th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Beli</th>` : ''}
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Jual</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Stok</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-36">Update Terakhir</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    ${results.map((item, index) => `
                        <tr class="transition duration-200 hover:bg-blue-50/70">
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-700">${index + 1}</td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-600 whitespace-nowrap">${htmlSpecialChars(item.kode_barang || '-')}</td>
                            <td class="px-6 py-4 font-medium text-gray-800 whitespace-nowrap">${htmlSpecialChars(item.nama_barang)}</td>
                            <td class="px-6 py-4 text-center"><span class="inline-flex items-center justify-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">${htmlSpecialChars(item.nama_kategori || '-')}</span></td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 font-medium">${htmlSpecialChars(item.satuan || 'pcs')}</td>
                            ${userRole === 'admin' ? `<td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap">${formatRupiah(item.harga_beli)}</td>` : ''}
                            <td class="px-6 py-4 text-right font-semibold text-gray-800 whitespace-nowrap">${formatRupiah(item.harga_jual)}</td>
                            <td class="px-6 py-4 text-center"><span class="${item.stok <= 10 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'} inline-flex items-center justify-center rounded-full px-3 py-1 text-xs font-semibold">${item.stok}</span></td>
                            <td class="px-6 py-4 text-sm text-gray-600 text-center">
                                <div class="inline-flex items-center gap-2">
                                    <i class="fas fa-clock text-blue-500"></i>
                                    <span>${formatDateDisplay(item.updated_at)}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="/barang/edit/${item.id_barang}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-yellow-100 text-yellow-700 transition hover:bg-yellow-500 hover:text-white">
                                        <i class="fas fa-edit text-sm"></i>
                                    </a>
                                    <a href="/barang/delete/${item.id_barang}" onclick="return confirm('Yakin ingin menghapus barang ini?')" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-red-100 text-red-700 transition hover:bg-red-600 hover:text-white">
                                        <i class="fas fa-trash text-sm"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }
    
    // Hide normal view, show search results
    if (mobileContainer) mobileContainer.style.display = 'none';
    if (desktopContainer) desktopContainer.style.display = 'none';
    searchContainer.classList.remove('hidden');
    
    // Hide pagination
    const paginationDiv = document.querySelector('.flex.justify-center.items-center.gap-2.mt-6');
    if (paginationDiv) paginationDiv.style.display = 'none';
    
    // Generate pagination controls for search results if needed
    if (totalPages > 1) {
        const trimmedQuery = (currentQuery || '').trim();
        const escapedQuery = (currentQuery || '').replace(/'/g, "\\'");
        const handlerPrefix = trimmedQuery.length > 0
            ? `performSearch('${escapedQuery}', `
            : 'loadAllBarang(';

        let paginationHtml = '<div class="flex justify-center items-center gap-2 mt-6" id="search_pagination">';
        
        if (currentPage > 1) {
            paginationHtml += `<button onclick="${handlerPrefix}1)" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-left mr-1"></i>Pertama
            </button>`;
            paginationHtml += `<button onclick="${handlerPrefix}${currentPage - 1})" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                <i class="fas fa-chevron-up mr-1"></i>Sebelumnya
            </button>`;
        }
        
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            paginationHtml += '<span class="text-gray-500">...</span>';
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                paginationHtml += `<button class="px-3 py-2 rounded bg-blue-600 text-white font-semibold text-sm">${i}</button>`;
            } else {
                paginationHtml += `<button onclick="${handlerPrefix}${i})" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">${i}</button>`;
            }
        }
        
        if (endPage < totalPages) {
            paginationHtml += '<span class="text-gray-500">...</span>';
        }
        
        if (currentPage < totalPages) {
            paginationHtml += `<button onclick="${handlerPrefix}${currentPage + 1})" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Selanjutnya <i class="fas fa-chevron-down ml-1"></i>
            </button>`;
            paginationHtml += `<button onclick="${handlerPrefix}${totalPages})" class="px-3 py-2 rounded border border-gray-300 text-gray-700 hover:bg-gray-100 transition text-sm font-semibold">
                Terakhir <i class="fas fa-chevron-right ml-1"></i>
            </button>`;
        }
        
        paginationHtml += '</div>';
        
        const existingPagination = document.getElementById('search_pagination');
        if (existingPagination) {
            existingPagination.remove();
        }
        searchContainer.insertAdjacentHTML('afterend', paginationHtml);
    } else {
        const existingPagination = document.getElementById('search_pagination');
        if (existingPagination) {
            existingPagination.remove();
        }
    }
    
    // Search info badge already handled earlier
}

// Helper untuk escape HTML
function htmlSpecialChars(str) {
    if (!str) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return str.replace(/[&<>"']/g, m => map[m]);
}

// Clear search dan reload semua barang
function clearSearch() {
    const searchInput = document.getElementById('searchBarang');
    if (searchInput) {
        searchInput.value = '';
        currentQuery = '';
    }
    
    // Hide kategori summary
    const kategoriSummary = document.getElementById('kategori_summary');
    if (kategoriSummary) {
        kategoriSummary.classList.add('hidden');
    }
    
    // Reset kategori filter to all
    currentKategori = 'all';
    setActiveKategoriButton('all');

    const searchInfoContainer = document.getElementById('search_info_container');
    const searchTermDisplay = document.getElementById('search_term_display');
    if (searchInfoContainer) searchInfoContainer.classList.add('hidden');
    if (searchTermDisplay) searchTermDisplay.textContent = '';
    
    // Load all barang again
    loadAllBarang(1);
}

// Init summary & counts dan load all barang di search results
applyFilters();
setActiveKategoriButton(currentKategori);

// Load semua barang (page 1) di search results container on initial load
document.addEventListener('DOMContentLoaded', function() {
    // Delay sedikit untuk memastikan semua elemen sudah ready
    setTimeout(function() {
        loadAllBarang(1);
    }, 100);
});

// Load all barang dengan pagination
async function loadAllBarang(page = 1) {
    try {
        const kategoriParam = currentKategori && currentKategori !== 'all' ? `&kategori=${currentKategori}` : '';
        const url = `/api/search-barang?q=&page=${page}${kategoriParam}`;
        console.log('Loading all barang:', url);
        const response = await fetch(url);
        const data = await response.json();
        console.log('All barang loaded:', data);
        
        currentQuery = ''; // Clear search query to mark non-search mode
        renderSearchResults(data.results || [], data);
    } catch (error) {
        console.error('Error loading barang:', error);
    }
}

// Filter barang by kategori
async function filterByKategori(kategoriId) {
    // Update currentKategori variable
    currentKategori = String(kategoriId);
    currentQuery = ''; // Clear search query
    
    setActiveKategoriButton(kategoriId);
    
    // Load barang
    try {
        const kategoriParam = kategoriId !== 'all' ? `&kategori=${kategoriId}` : '';
        const url = `/api/search-barang?q=&page=1${kategoriParam}`;
        console.log('Loading kategori:', url);
        const response = await fetch(url);
        const data = await response.json();
        console.log('Kategori filtered:', data);
        
        renderSearchResults(data.results || [], data);
        
        // Show kategori summary if not 'all'
        const kategoriSummary = document.getElementById('kategori_summary');
        if (kategoriId !== 'all' && kategoriSummary) {
            const kategoriKey = String(kategoriId);
            const kategoriData = totalsByKategori && Object.prototype.hasOwnProperty.call(totalsByKategori, kategoriKey)
                ? totalsByKategori[kategoriKey]
                : null;
            const kategoriLabel = kategoriNames && Object.prototype.hasOwnProperty.call(kategoriNames, kategoriKey)
                ? kategoriNames[kategoriKey]
                : ((data.results && data.results[0] && data.results[0].nama_kategori) ? data.results[0].nama_kategori : '-');

            const beliValue = kategoriData ? kategoriData.total_harga_beli : (data.results || []).reduce((sum, barang) => {
                return sum + ((parseFloat(barang.harga_beli) || 0) * (parseInt(barang.stok) || 0));
            }, 0);
            const jualValue = kategoriData ? kategoriData.total_harga_jual : (data.results || []).reduce((sum, barang) => {
                return sum + ((parseFloat(barang.harga_jual) || 0) * (parseInt(barang.stok) || 0));
            }, 0);
            const stokValue = kategoriData ? kategoriData.total_stok : (data.results || []).reduce((sum, barang) => {
                return sum + (parseInt(barang.stok) || 0);
            }, 0);

            const nameEl = document.getElementById('kategori_name');
            const nameEl2 = document.getElementById('kategori_name2');
            const nameEl3 = document.getElementById('kategori_name3');
            const beliEl = document.getElementById('kategori_beli');
            const jualEl = document.getElementById('kategori_jual');
            const stokEl = document.getElementById('kategori_stok');

            if (nameEl) nameEl.textContent = kategoriLabel;
            if (nameEl2) nameEl2.textContent = kategoriLabel;
            if (nameEl3) nameEl3.textContent = kategoriLabel;
            if (beliEl) beliEl.textContent = formatRupiah(beliValue);
            if (jualEl) jualEl.textContent = formatRupiah(jualValue);
            if (stokEl) stokEl.textContent = (stokValue || 0).toLocaleString('id-ID');

            kategoriSummary.classList.remove('hidden');
        } else if (kategoriId === 'all' && kategoriSummary) {
            kategoriSummary.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error filtering barang:', error);
    }
}
</script>

<?php 
$content = ob_get_clean();
$title = 'Daftar Barang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
