<?php ob_start(); ?>

<div class="app-card p-5 sm:p-6 app-reveal">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700">
                    <i class="fas fa-plus-circle"></i>
                </span>
                Input Barang Masuk
            </h2>
            <p class="text-sm text-slate-500 mt-2">Pilih stok barang, atur jumlah, harga beli, harga jual, lalu simpan barang masuk.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="px-3 py-1 rounded-full bg-teal-100 text-teal-700 font-semibold"><i class="fas fa-list-check mr-1"></i>1. Pilih Barang</span>
            <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold"><i class="fas fa-sliders mr-1"></i>2. Atur Detail</span>
            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold"><i class="fas fa-floppy-disk mr-1"></i>3. Simpan</span>
        </div>
    </div>

    <form action="/pembelian/store" method="POST" id="formPembelian" onsubmit="return validateForm()">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 rounded-2xl border border-teal-100 bg-gradient-to-b from-teal-50/60 to-white p-4">
                <div class="mb-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                        <h3 class="text-lg font-bold text-slate-700">Daftar Stok Barang Tersedia</h3>
                        <div class="flex items-center gap-2">
                            <span id="barang_count_info" class="text-xs px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-600">0 barang</span>
                            <button type="button" class="app-btn-primary px-3 py-2 text-sm font-semibold" onclick="openAddBarangModal()">
                                <i class="fas fa-plus mr-1"></i>Stok Barang Baru
                            </button>
                        </div>
                    </div>
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" id="search_barang_main" placeholder="Cari nama atau kode barang..." autocomplete="off"
                               class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                    </div>
                </div>
                <div id="barang_list" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-[28rem] overflow-y-auto pr-1"></div>
            </div>

            <div class="rounded-2xl border border-blue-200 bg-blue-50/60 p-4 h-fit lg:sticky lg:top-24">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-bold text-slate-700">Stok Barang Dipilih</h3>
                    <span id="selected_count" class="text-xs bg-blue-600 text-white px-2.5 py-1 rounded-full font-semibold">0 item</span>
                </div>
                <div id="selected_container" class="space-y-2 max-h-[24rem] overflow-y-auto pr-1"></div>
                <p id="no_items_msg" class="text-slate-500 text-center py-5 text-sm border border-dashed border-slate-300 rounded-xl bg-white/70">
                    Belum ada barang dipilih
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
            <div class="rounded-xl border border-cyan-200 bg-cyan-50 p-4 text-center">
                <p class="text-xs uppercase tracking-wide font-semibold text-cyan-700">Total Unit</p>
                <p class="text-2xl font-extrabold text-cyan-800" id="total_items">0</p>
            </div>
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-center">
                <p class="text-xs uppercase tracking-wide font-semibold text-blue-700">Subtotal</p>
                <p class="text-2xl font-extrabold text-blue-800" id="subtotal_display">Rp 0</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                <p class="text-xs uppercase tracking-wide font-semibold text-emerald-700">Total Harga</p>
                <p class="text-2xl font-extrabold text-emerald-800" id="total_display">Rp 0</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="nama_pembeli" class="block text-slate-700 font-semibold mb-2">Nama Supplier</label>
                <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Contoh: CV Sumber Makmur"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
            <div>
                <label for="keterangan" class="block text-slate-700 font-semibold mb-2">Catatan (Opsional)</label>
                <input type="text" id="keterangan" name="keterangan" placeholder="Catatan transaksi pembelian"
                       class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" class="app-btn-primary px-6 py-3 font-semibold inline-flex items-center justify-center gap-2">
                <i class="fas fa-save"></i>Simpan Barang Masuk
            </button>
            <a href="/pembelian" class="app-btn-secondary px-6 py-3 font-semibold inline-flex items-center justify-center gap-2">
                <i class="fas fa-arrow-left"></i>Kembali
            </a>
        </div>
    </form>
</div>

<div id="toast" class="hidden fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg"></div>

<!-- Modal Tambah Stok Barang Baru -->
<div id="modal_add_barang" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white rounded-2xl shadow-lg w-full max-w-lg p-6 relative">
        <button class="absolute top-3 right-3 text-slate-500 hover:text-slate-700" onclick="closeAddBarangModal()">
            <i class="fas fa-times"></i>
        </button>
        <h3 class="text-xl font-bold text-slate-800 mb-4">Tambah Stok Barang Baru</h3>
        <form id="form_add_barang" onsubmit="submitAddBarang(event)">
            <div class="mb-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Kode Barang</label>
                <input type="text" name="kode_barang" placeholder="Misal: BRG-013"
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-teal-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Nama Barang</label>
                <input type="text" name="nama_barang" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-teal-500">
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori</label>
                <select name="id_kategori" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-teal-500">
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach (($kategori ?? []) as $kat): ?>
                        <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Satuan</label>
                <select name="satuan" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-teal-500">
                    <option value="pcs">pcs</option>
                    <?php foreach (($satuanList ?? []) as $sat): ?>
                        <option value="<?= htmlspecialchars($sat['nama_satuan']) ?>"><?= htmlspecialchars($sat['nama_satuan']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Beli</label>
                    <input type="number" name="harga_beli" min="0" step="0.01" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Harga Jual</label>
                    <input type="number" name="harga_jual" min="0" step="0.01" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-teal-500">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal Expired (Opsional)</label>
                <input type="date" name="tanggal_expired" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:border-teal-500">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="app-btn-primary px-5 py-2">Simpan & Tambah</button>
                <button type="button" class="app-btn-secondary px-4 py-2" onclick="closeAddBarangModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
let itemIndex = 0;
const allBarang = <?= json_encode($barang) ?>;

function formatRupiah(value) {
    const number = Number(value) || 0;
    return 'Rp ' + Math.floor(number).toLocaleString('id-ID');
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.className = 'fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg';
    toast.classList.add(type === 'error' ? 'bg-red-100' : 'bg-emerald-100', type === 'error' ? 'text-red-700' : 'text-emerald-700', 'border', type === 'error' ? 'border-red-200' : 'border-emerald-200');
    toast.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 2600);
}

function createBarangCard(item) {
    const itemStr = JSON.stringify(item).replace(/"/g, '&quot;');
    return `
        <div class="border border-slate-200 rounded-xl p-3 bg-white hover:border-teal-300 hover:shadow-md transition cursor-pointer" onclick='addItemFromBarang(${itemStr})'>
            <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                    <p class="font-semibold text-slate-800 leading-tight">${item.nama_barang}</p>
                    <p class="text-xs text-slate-500 mt-1">Kategori: <strong>${item.nama_kategori || '-'}</strong></p>
                </div>
                <span class="text-[11px] bg-teal-100 text-teal-700 px-2 py-1 rounded font-mono">${item.kode_barang}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-slate-600">
                <span>Stok: <strong>${item.stok}</strong> ${item.satuan}</span>
                <span class="font-semibold text-emerald-700">${formatRupiah(item.harga_beli)}</span>
            </div>
            <button type="button" class="mt-3 w-full rounded-lg bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold py-1.5">
                + Pilih Barang
            </button>
        </div>
    `;
}

function renderBarangList(filterText = '') {
    const listDiv = document.getElementById('barang_list');
    const info = document.getElementById('barang_count_info');
    if (!listDiv) return;

    const q = filterText.toLowerCase().trim();
    const filtered = allBarang.filter((b) => {
        const nama = (b.nama_barang || '').toLowerCase();
        const kode = (b.kode_barang || '').toLowerCase();
        return q === '' || nama.includes(q) || kode.includes(q);
    });

    if (info) info.textContent = `${filtered.length} barang`;

    if (filtered.length === 0) {
        listDiv.innerHTML = `
            <div class="col-span-full text-center border border-dashed border-slate-300 rounded-xl p-6 bg-white">
                <p class="text-slate-500 mb-3">Stok barang tidak ditemukan</p>
                <button type="button" class="app-btn-primary px-4 py-2 text-sm" onclick="openAddBarangModal()">
                    <i class="fas fa-plus mr-1"></i>Tambah Stok Barang Baru
                </button>
            </div>
        `;
        return;
    }

    listDiv.innerHTML = filtered.map(createBarangCard).join('');
}

function updateItemSubtotal(idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (!row) return;
    const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
    const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
    const subtotal = jumlah * harga;
    const subtotalEl = row.querySelector('.subtotal-item');
    if (subtotalEl) subtotalEl.textContent = formatRupiah(subtotal < 0 ? 0 : subtotal);
}

function addItemFromBarang(barang) {
    const container = document.getElementById('selected_container');
    const noItemsMsg = document.getElementById('no_items_msg');

    const existing = document.querySelector(`[data-barang-id="${barang.id_barang}"]`);
    if (existing) {
        const qtyInput = existing.querySelector('input[name*="[jumlah]"]');
        qtyInput.value = (parseInt(qtyInput.value, 10) || 0) + 1;
        updateItemSubtotal(existing.getAttribute('data-item-index'));
        hitungTotal();
        showToast('Jumlah barang ditambah');
        return;
    }

    const idx = itemIndex;
    const itemHtml = `
        <div class="border border-blue-200 bg-white rounded-xl p-3" data-barang-id="${barang.id_barang}" data-item-index="${idx}">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div>
                    <p class="font-semibold text-slate-800 text-sm">${barang.nama_barang}</p>
                    <p class="text-[11px] text-slate-500">${barang.kode_barang} • ${barang.satuan}</p>
                </div>
                <button type="button" onclick="removeItem(${idx})" class="text-xs text-red-600 hover:text-red-700 font-semibold">
                    <i class="fas fa-trash mr-1"></i>Hapus
                </button>
            </div>

            <div class="grid grid-cols-2 gap-2 mb-2 text-xs">
                <div>
                    <label class="block text-slate-500 mb-1">Jumlah</label>
                    <div class="flex items-center border border-slate-300 rounded-lg overflow-hidden">
                        <button type="button" class="px-2 py-1.5 bg-slate-100" onclick="adjustQty(${idx}, -1)">-</button>
                        <input type="number" name="items[${idx}][jumlah]" value="1" min="1" class="w-full text-center py-1.5 outline-none" onchange="onItemChange(${idx})">
                        <button type="button" class="px-2 py-1.5 bg-slate-100" onclick="adjustQty(${idx}, 1)">+</button>
                    </div>
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Satuan</label>
                    <input type="text" name="items[${idx}][satuan]" value="${barang.satuan || 'pcs'}" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg" onchange="hitungTotal()">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Harga Beli</label>
                    <input type="number" name="items[${idx}][harga_satuan]" value="${barang.harga_beli}" min="0" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg" onchange="onItemChange(${idx})">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Harga Jual</label>
                    <input type="number" name="items[${idx}][harga_jual]" value="${barang.harga_jual || 0}" min="0" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg" onchange="onItemChange(${idx})">
                </div>
                <div>
                    <label class="block text-slate-500 mb-1">Tanggal Expired (Opsional)</label>
                    <input type="date" name="items[${idx}][tanggal_expired]" value="${barang.tanggal_expired ? String(barang.tanggal_expired).substring(0, 10) : ''}" class="w-full px-2 py-1.5 border border-slate-300 rounded-lg" onchange="onItemChange(${idx})">
                </div>
            </div>

            <div class="text-right text-xs text-slate-500">
                Subtotal: <span class="subtotal-item font-bold text-emerald-700">${formatRupiah(barang.harga_beli)}</span>
            </div>

            <input type="hidden" name="items[${idx}][id_barang]" value="${barang.id_barang}">
        </div>
    `;

    container.insertAdjacentHTML('beforeend', itemHtml);
    noItemsMsg.style.display = 'none';
    itemIndex++;
    hitungTotal();
    showToast('Stok barang ditambahkan');
}

function adjustQty(idx, delta) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (!row) return;
    const input = row.querySelector('input[name*="[jumlah]"]');
    const next = Math.max(1, (parseInt(input.value, 10) || 1) + delta);
    input.value = next;
    onItemChange(idx);
}

function onItemChange(idx) {
    updateItemSubtotal(idx);
    hitungTotal();
}

function removeItem(idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (row) row.remove();

    const container = document.getElementById('selected_container');
    if (container.children.length === 0) {
        document.getElementById('no_items_msg').style.display = 'block';
    }
    hitungTotal();
}

function hitungTotal() {
    let totalItems = 0;
    let totalHarga = 0;
    const rows = document.querySelectorAll('[data-barang-id]');

    rows.forEach((row) => {
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const hargaJual = parseFloat(row.querySelector('input[name*="[harga_jual]"]').value) || 0;
        totalItems += jumlah;
        totalHarga += (jumlah * harga);
        if (hargaJual < 0) {
            row.querySelector('input[name*="[harga_jual]"]').value = '0';
        }
    });

    document.getElementById('total_items').textContent = totalItems.toLocaleString('id-ID');
    document.getElementById('subtotal_display').textContent = formatRupiah(totalHarga);
    document.getElementById('total_display').textContent = formatRupiah(totalHarga);
    document.getElementById('selected_count').textContent = rows.length + ' item';
}

function validateForm() {
    const items = document.querySelectorAll('[data-barang-id]');
    if (items.length === 0) {
        showToast('Tambahkan minimal satu barang', 'error');
        return false;
    }

    let isValid = true;
    items.forEach((row) => {
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const hargaJual = parseFloat(row.querySelector('input[name*="[harga_jual]"]').value) || 0;
        if (jumlah < 1 || harga < 0 || hargaJual < 0) isValid = false;
    });

    if (!isValid) {
        showToast('Periksa jumlah, harga beli, dan harga jual setiap item', 'error');
        return false;
    }

    return true;
}

function openAddBarangModal() {
    document.getElementById('modal_add_barang').classList.remove('hidden');
}

function closeAddBarangModal() {
    document.getElementById('modal_add_barang').classList.add('hidden');
}

async function submitAddBarang(event) {
    event.preventDefault();
    const form = document.getElementById('form_add_barang');
    const formData = new FormData(form);
    formData.append('stok', '1');

    try {
        const resp = await fetch('/api/barang/store', { method: 'POST', body: formData });
        const data = await resp.json();

        if (!data.success) {
            showToast(data.message || 'Gagal menambahkan barang baru', 'error');
            return;
        }

        const barangBaru = data.barang;
        allBarang.push(barangBaru);
        addItemFromBarang(barangBaru);
        form.reset();
        closeAddBarangModal();
        document.getElementById('search_barang_main').value = '';
        renderBarangList('');
    } catch (err) {
        console.error(err);
        showToast('Terjadi kesalahan saat menambahkan barang', 'error');
    }
}

document.getElementById('search_barang_main').addEventListener('input', function(e) {
    renderBarangList(e.target.value);
});

renderBarangList('');
</script>

<?php
$content = ob_get_clean();
$title = 'Input Barang Masuk - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
