<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-3 sm:p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-800">
            <i class="fas fa-box text-blue-600 mr-2"></i>Daftar Barang
        </h2>
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            <div class="w-full sm:w-72">
                <input
                    id="searchBarang"
                    type="text"
                    placeholder="Cari nama/kode/kategori/satuan..."
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
            </div>
            <a href="/barang/export<?= !empty($selected_kategori) ? '?kategori=' . (int)$selected_kategori : '' ?>" class="w-full sm:w-auto text-center bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 sm:py-2 rounded transition">
                <i class="fas fa-file-excel mr-2"></i>Download Excel
            </a>
            <a href="/barang/create" class="w-full sm:w-auto text-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 sm:py-2 rounded transition">
                <i class="fas fa-plus mr-2"></i>Tambah Barang
            </a>
        </div>
    </div>

    <?php if (!empty($kategori)): ?>
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="/barang" class="px-3 py-2 rounded border text-xs sm:text-sm <?= empty($selected_kategori) ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>" data-kat="all">Semua</a>
        <?php foreach ($kategori as $kat): ?>
            <a href="/barang?kategori=<?= $kat['id_kategori'] ?>" class="px-3 py-2 rounded border text-xs sm:text-sm <?= (!empty($selected_kategori) && (int)$selected_kategori === (int)$kat['id_kategori']) ? 'bg-blue-600 text-white' : 'bg-gray-100 hover:bg-gray-200' ?>" data-kat="<?= $kat['id_kategori'] ?>">
                <?= htmlspecialchars($kat['nama_kategori']) ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
    <div class="grid grid-cols-1 sm:grid-cols-1 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="border-2 border-purple-300 rounded-lg p-3 sm:p-4 bg-purple-50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Stok</p>
            <p class="text-lg sm:text-2xl font-bold text-purple-700" id="sum_stok"><?= number_format((int)($totals['total_stok'] ?? 0), 0, ',', '.') ?></p>
        </div>
    </div>

    <div id="kategori_summary" class="grid grid-cols-1 sm:grid-cols-1 gap-3 sm:gap-4 mb-4 sm:mb-6 hidden">
        <div class="border border-purple-200 rounded-lg p-3 sm:p-4 bg-purple-50/50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Stok (Kategori: <span id="kategori_name">-</span>)</p>
            <p class="text-base sm:text-xl font-bold text-purple-700" id="kategori_stok">0</p>
        </div>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
        <div class="border-2 border-blue-300 rounded-lg p-3 sm:p-4 bg-blue-50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Harga Beli</p>
            <p class="text-lg sm:text-2xl font-bold text-blue-700" id="sum_beli"><?= formatRupiah($totals['total_harga_beli'] ?? 0) ?></p>
        </div>
        <div class="border-2 border-green-300 rounded-lg p-3 sm:p-4 bg-green-50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Harga Jual</p>
            <p class="text-lg sm:text-2xl font-bold text-green-700" id="sum_jual"><?= formatRupiah($totals['total_harga_jual'] ?? 0) ?></p>
        </div>
        <div class="border-2 border-purple-300 rounded-lg p-3 sm:p-4 bg-purple-50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Stok</p>
            <p class="text-lg sm:text-2xl font-bold text-purple-700" id="sum_stok"><?= number_format((int)($totals['total_stok'] ?? 0), 0, ',', '.') ?></p>
        </div>
    </div>

    <div id="kategori_summary" class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6 hidden">
        <div class="border border-blue-200 rounded-lg p-3 sm:p-4 bg-blue-50/50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Harga Beli (Kategori: <span id="kategori_name">-</span>)</p>
            <p class="text-base sm:text-xl font-bold text-blue-700" id="kategori_beli">Rp 0</p>
        </div>
        <div class="border border-green-200 rounded-lg p-3 sm:p-4 bg-green-50/50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Harga Jual (Kategori)</p>
            <p class="text-base sm:text-xl font-bold text-green-700" id="kategori_jual">Rp 0</p>
        </div>
        <div class="border border-purple-200 rounded-lg p-3 sm:p-4 bg-purple-50/50 text-center">
            <p class="text-gray-600 text-xs sm:text-sm font-medium mb-1 sm:mb-2">Total Stok (Kategori)</p>
            <p class="text-base sm:text-xl font-bold text-purple-700" id="kategori_stok">0</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="flex justify-between items-center mb-4">
        <div class="text-sm text-gray-600">
            Menampilkan <span id="visible_count">0</span> dari <span id="total_count">0</span> barang (halaman ini)
        </div>
        <div id="search_info_container" class="hidden">
            <button onclick="clearSearch()" class="text-xs bg-gray-300 hover:bg-gray-400 text-gray-800 px-2 py-1 rounded transition">
                <i class="fas fa-times mr-1"></i>Clear Search
            </button>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-3">
        <?php if (empty($barang)): ?>
            <div class="text-center py-8 text-gray-400 italic">Tidak ada data barang</div>
        <?php else: ?>
            <?php foreach ($barang as $index => $item): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition" data-item="barang-card" data-kategori="<?= $item['id_kategori'] ?>" data-beli="<?= $item['harga_beli'] ?>" data-jual="<?= $item['harga_jual'] ?>" data-stok="<?= $item['stok'] ?>" data-search="<?= htmlspecialchars(strtolower(trim(($item['kode_barang'] ?? '') . ' ' . ($item['nama_barang'] ?? '') . ' ' . ($item['nama_kategori'] ?? '') . ' ' . ($item['satuan'] ?? '')))) ?>">
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
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Beli</th>
                    <?php endif; ?>
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
                        <tr class="hover:bg-blue-50 transition duration-200" data-item="barang-row" data-kategori="<?= $item['id_kategori'] ?>" data-beli="<?= $item['harga_beli'] ?>" data-jual="<?= $item['harga_jual'] ?>" data-stok="<?= $item['stok'] ?>" data-search="<?= htmlspecialchars(strtolower(trim(($item['kode_barang'] ?? '') . ' ' . ($item['nama_barang'] ?? '') . ' ' . ($item['nama_kategori'] ?? '') . ' ' . ($item['satuan'] ?? '')))) ?>">
                            <td class="px-6 py-4 text-center text-sm font-medium text-gray-700"><?= (($current_page - 1) * $items_per_page) + $index + 1 ?></td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-600"><?= htmlspecialchars($item['kode_barang'] ?? '-') ?></td>
                            <td class="px-6 py-4 font-medium text-gray-800"><?= htmlspecialchars($item['nama_barang']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold">
                                    <?= htmlspecialchars($item['nama_kategori']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 font-medium"><?= htmlspecialchars($item['satuan']) ?></td>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800"><?= formatRupiah($item['harga_beli']) ?></td>
                            <?php endif; ?>
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

function filterKategori(katId) {
    currentKategori = String(katId);
    applyFilters();

}

function applyFilters() {
    const query = (currentQuery || '').trim().toLowerCase();

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
    if (searchInfoContainer) {
        if (query.length > 0) {
            searchInfoContainer.classList.remove('hidden');
        } else {
            searchInfoContainer.classList.add('hidden');
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
    return 'Rp ' + (num || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function updateKategoriSummary() {
    const summaryEl = document.getElementById('kategori_summary');
    const nameEl = document.getElementById('kategori_name');
    const stokEl = document.getElementById('kategori_stok');
    const beliEl = document.getElementById('kategori_beli');
    const jualEl = document.getElementById('kategori_jual');
    if (!summaryEl || !nameEl || !stokEl) return;

    if (currentKategori === 'all') {
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

    nameEl.textContent = kategoriNames[currentKategori] || '-';
    if (beliEl) beliEl.textContent = formatRupiah(totalBeli);
    if (jualEl) jualEl.textContent = formatRupiah(totalJual);
    stokEl.textContent = (totalStok || 0).toLocaleString('id-ID');
    summaryEl.classList.remove('hidden');
}

const searchInput = document.getElementById('searchBarang');
if (searchInput) {
    searchInput.addEventListener('input', debounce(async (e) => {
        currentQuery = e.target.value || '';
        
        if (currentQuery.trim().length === 0) {
            // Jika search kosong, kembali ke tampilan normal dengan filter kategori
            applyFilters();
            return;
        }
        
        // Lakukan AJAX search ke endpoint /api/search-barang
        try {
            const kategoriParam = currentKategori && currentKategori !== 'all' ? `&kategori=${currentKategori}` : '';
            const response = await fetch(`/api/search-barang?q=${encodeURIComponent(currentQuery)}${kategoriParam}`);
            const data = await response.json();
            
            // Render hasil search
            renderSearchResults(data.results || []);
        } catch (error) {
            console.error('Search error:', error);
        }
    }, 300));
}

// Debounce utility untuk mengurangi API calls
function debounce(func, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => func.apply(this, args), delay);
    };
}

// Render hasil search ke halaman
function renderSearchResults(results) {
    const container = document.querySelector('.block.md\\:hidden.space-y-3');
    const tableContainer = document.querySelector('.hidden.md\\:block.overflow-x-auto');
    
    if (!container && !tableContainer) return;
        
        // Update counter
        const visibleEl = document.getElementById('visible_count');
        const totalEl = document.getElementById('total_count');
        if (visibleEl) visibleEl.textContent = results.length.toLocaleString('id-ID');
        if (totalEl) totalEl.textContent = results.length.toLocaleString('id-ID');
            container.innerHTML = results.map((item, index) => `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="font-mono text-xs text-gray-500 mb-1">${htmlSpecialChars(item.kode_barang || '-')}</div>
                            <h3 class="font-bold text-gray-800 mb-1">${htmlSpecialChars(item.nama_barang)}</h3>
                            <span class="inline-block bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full text-xs font-bold">
                                ${htmlSpecialChars(item.nama_kategori || '-')}
                            </span>
                        </div>
                        <span class="${item.stok <= 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'} px-3 py-1 rounded-full text-xs font-bold">
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
                    <div class="flex gap-2">
                        <a href="/barang/edit/${item.id_barang}" class="flex-1 text-center bg-yellow-100 text-yellow-700 hover:bg-yellow-600 hover:text-white py-2 rounded transition text-sm font-medium">
                            <i class="fas fa-edit mr-1"></i>Edit
                        </a>
                        <button onclick="confirmDelete(${item.id_barang})" class="flex-1 bg-red-100 text-red-700 hover:bg-red-600 hover:text-white py-2 rounded transition text-sm font-medium">
                            <i class="fas fa-trash mr-1"></i>Hapus
                        </button>
                    </div>
                </div>
            `).join('');
        }
    }
    
    // Desktop table view
    if (tableContainer) {
        if (results.length === 0) {
            tableContainer.innerHTML = '<div class="text-center py-8 text-gray-400 italic">Tidak ada barang yang cocok dengan pencarian</div>';
        } else {
            const tableHTML = `
                <table class="w-full border border-gray-300 rounded-lg">
                    <thead class="bg-blue-100 border-b-2 border-blue-300">
                        <tr>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-12">No</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-20">Kode</th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 w-40">Nama Barang</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-28">Kategori</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Satuan</th>
                            ${userRole === 'admin' ? `<th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Beli</th>` : ''}
                            <th class="px-6 py-4 text-right text-sm font-bold text-gray-800 w-32">Harga Jual</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Stok</th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        ${results.map((item, index) => `
                            <tr class="hover:bg-blue-50 transition duration-200">
                                <td class="px-6 py-4 text-center text-sm font-medium text-gray-700">${index + 1}</td>
                                <td class="px-6 py-4 font-mono text-sm text-gray-600">${htmlSpecialChars(item.kode_barang || '-')}</td>
                                <td class="px-6 py-4 font-medium text-gray-800">${htmlSpecialChars(item.nama_barang)}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold">
                                        ${htmlSpecialChars(item.nama_kategori || '-')}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700 font-medium">${htmlSpecialChars(item.satuan || 'pcs')}</td>
                                ${userRole === 'admin' ? `<td class="px-6 py-4 text-right font-semibold text-gray-800">${formatRupiah(item.harga_beli)}</td>` : ''}
                                <td class="px-6 py-4 text-right font-semibold text-gray-800">${formatRupiah(item.harga_jual)}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="${item.stok <= 10 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'} px-3 py-1 rounded-full text-xs font-bold">
                                        ${item.stok}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center gap-3">
                                        <a href="/barang/edit/${item.id_barang}" class="inline-flex items-center justify-center w-8 h-8 bg-yellow-100 text-yellow-600 hover:bg-yellow-600 hover:text-white rounded transition">
                                            <i class="fas fa-edit text-sm"></i>
                                        </a>
                                        <a href="/barang/delete/${item.id_barang}" 
                                           onclick="return confirm('Yakin ingin menghapus barang ini?')" 
                                           class="inline-flex items-center justify-center w-8 h-8 bg-red-100 text-red-600 hover:bg-red-600 hover:text-white rounded transition">
                                            <i class="fas fa-trash text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
            tableContainer.innerHTML = tableHTML;
        }
    }
    
    // Update kontrol pagination - sembunyikan saat search
    const paginationDiv = document.querySelector('.flex.justify-center.items-center.gap-2.mt-6');
    if (paginationDiv) {
        paginationDiv.style.display = 'none';
    }
    
    // Tampilkan tombol clear search
    const searchInfoContainer = document.getElementById('search_info_container');
    if (searchInfoContainer) {
        searchInfoContainer.classList.remove('hidden');
    }
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

// Clear search dan reload halaman normal
function clearSearch() {
    const searchInput = document.getElementById('searchBarang');
    if (searchInput) {
        searchInput.value = '';
        currentQuery = '';
    }
    
    // Reload halaman untuk kembali ke normal view (pagination normal)
    const url = new URL(window.location);
    url.searchParams.delete('search'); // Jika ada param search, hapus
    window.location.href = url.toString();
}

// Init summary & counts
applyFilters();
</script>

<?php 
$content = ob_get_clean();
$title = 'Daftar Barang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
