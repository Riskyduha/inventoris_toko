<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Tambah Penjualan Baru
    </h2>

    <form action="/penjualan/store" method="POST" id="formPenjualan" onsubmit="return validateForm()">

        <!-- Panel Cari & Pilihan Barang -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Panel Daftar Barang Tersedia -->
            <div class="lg:col-span-2 bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Daftar Barang Tersedia</h3>
                    <input type="text" id="search_barang_main" placeholder="🔍 Cari nama/kode barang..." autocomplete="off"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 mb-3">
                </div>
                <div id="barang_list" class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-96 overflow-y-auto">
                    <!-- Barang cards akan di-generate oleh JavaScript -->
                </div>
            </div>

            <!-- Panel Barang Dipilih -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-700">Barang Dipilih</h3>
                    <span id="selected_count" class="text-sm bg-blue-600 text-white px-2 py-1 rounded-full">0</span>
                </div>
                <div id="selected_container" class="space-y-2 max-h-80 overflow-y-auto"></div>
                <p id="no_items_msg" class="text-gray-500 text-center py-4 text-sm">Pilih barang dari daftar</p>
            </div>
        </div>

        <!-- Ringkasan Transaksi -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-gray-600 text-sm">Total Item</p>
                    <p class="text-2xl font-bold text-blue-600" id="total_items">0</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Subtotal</p>
                    <p class="text-2xl font-bold text-blue-600" id="subtotal_display">Rp 0</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Harga</p>
                    <p class="text-2xl font-bold text-green-600" id="total_display">Rp 0</p>
                </div>
            </div>
        </div>

        <!-- Uang & Kembalian -->
        <div id="payment_section" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="uang_diberikan" class="block text-gray-700 font-semibold mb-2">Uang Diberikan Konsumen *</label>
                <input type="number" id="uang_diberikan" name="uang_diberikan" min="0" step="0.01" value="0"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                       onchange="hitungKembalian()" onkeyup="hitungKembalian()">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Kembalian</label>
                <div class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-lg font-bold" id="kembalian_display">
                    Rp 0
                </div>
            </div>
        </div>

        <!-- Info Pembeli -->
        <div id="customer_section" class="mb-6">
            <label for="nama_pembeli" class="block text-gray-700 font-semibold mb-2">Nama Pembeli</label>
            <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Masukkan nama pembeli..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
        </div>

        <!-- Informasi Hutang -->
        <div class="mb-6">
            <div class="flex items-center mb-4">
                <input type="checkbox" id="ada_hutang" name="ada_hutang" onchange="toggleHutangFields()"
                       class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                <label for="ada_hutang" class="ml-2 text-gray-700 font-semibold">Ada Hutang?</label>
            </div>
            
            <div id="hutang_section" style="display:none;" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="nama_penghutang" class="block text-gray-700 font-semibold mb-2">Nama Yang Ngutang *</label>
                        <input type="text" id="nama_penghutang" name="nama_penghutang" placeholder="Siapa yang ngutang?"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label for="jatuh_tempo" class="block text-gray-700 font-semibold mb-2">Tanggal Jatuh Tempo *</label>
                        <input type="date" id="jatuh_tempo" name="jatuh_tempo"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label for="jumlah_hutang_display" class="block text-gray-700 font-semibold mb-2">Jumlah Hutang</label>
                    <div class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-lg font-bold text-green-600" id="jumlah_hutang_display">
                        Rp 0
                    </div>
                    <input type="hidden" id="jumlah_hutang" name="jumlah_hutang" value="0">
                </div>
            </div>
        </div>

        <!-- Buttons -->
<div class="flex flex-col sm:flex-row gap-3 w-full">
    <button
        type="submit"
        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition flex items-center justify-center gap-2">
        <i class="fas fa-save"></i>
        Simpan
    </button>

    <button
        type="button"
        onclick="printNota()"
        class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition flex items-center justify-center gap-2">
        <i class="fas fa-print"></i>
        Print Nota
    </button>

    <a
        href="/penjualan"
        class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition flex items-center justify-center gap-2">
        <i class="fas fa-arrow-left"></i>
        Kembali
    </a>
</div>
    </form>
</div>

<script>
let itemIndex = 0;
const allBarang = <?= json_encode($barang) ?>;
const notaConfig = Object.assign({
    nama_toko: 'UD. BERSAUDARA',
    alamat_toko: '',
    nomor_telepon: '',
    email_toko: '',
    footer_nota: '',
    lebar_kertas: 80,
    tampilkan_jam: 1,
    tampilkan_kode_barang: 1,
    tampilkan_satuan: 1,
    jumlah_diskon_terpisah: 0,
    custom_header_text: '',
    custom_footer_text: '',
    tampilkan_nama_pembeli: 1,
    tampilkan_info_hutang: 1
}, <?= json_encode($notaConfig ?? []) ?>);

console.log('DEBUG: Script loaded, barang count:', allBarang.length);

function renderBarangList(filterText = '') {
    const listDiv = document.getElementById('barang_list');
    if (!listDiv) return;
    
    const q = filterText.toLowerCase().trim();
    let filtered = [];
    
    for (let i = 0; i < allBarang.length; i++) {
        const b = allBarang[i];
        const nama = (b.nama_barang || '').toLowerCase().trim();
        const kode = (b.kode_barang || '').toLowerCase().trim();
        if (q === '' || nama.includes(q) || kode.includes(q)) {
            filtered.push(b);
        }
    }
    
    if (filtered.length === 0) {
        listDiv.innerHTML = '<div style="text-align:center;color:#999;padding:20px;grid-column:1/-1;">Tidak ada barang</div>';
        return;
    }
    
    let html = '';
    for (let i = 0; i < filtered.length; i++) {
        const item = filtered[i];
        const itemStr = JSON.stringify(item).replace(/"/g, '&quot;');
        html += '<div style="border:1px solid #ddd;border-radius:8px;padding:12px;background:#fff;cursor:pointer;" onclick="addItemFromBarang(' + itemStr + ')">';
        html += '<div style="font-weight:bold;margin-bottom:8px;display:flex;justify-content:space-between;"><div>' + item.nama_barang + '</div>';
        html += '<span style="font-size:11px;background:#e3f2fd;color:#1976d2;padding:2px 6px;border-radius:3px;font-family:monospace;">' + item.kode_barang + '</span></div>';
        html += '<div style="font-size:13px;color:#666;margin-bottom:8px;"><div>Stok: <strong>' + item.stok + '</strong> ' + item.satuan + '</div>';
        html += '<div style="color:#2e7d32;font-weight:bold;">Rp ' + parseInt(item.harga_jual).toLocaleString('id-ID') + '</div></div>';
        html += '<button type="button" style="width:100%;background:#1976d2;color:white;border:none;padding:6px;border-radius:4px;cursor:pointer;font-size:12px;">+ Tambah</button></div>';
    }
    listDiv.innerHTML = html;
}

function addItemFromBarang(barang) {
    const container = document.getElementById('selected_container');
    const noItemsMsg = document.getElementById('no_items_msg');
    
    let itemHtml = '<div style="border:1px solid #90caf9;background:#fff;border-radius:6px;padding:10px;" data-item-index="' + itemIndex + '" data-stok="' + barang.stok + '">';
    itemHtml += '<div style="font-weight:bold;font-size:13px;margin-bottom:6px;">' + barang.nama_barang + ' <span style="font-size:10px;background:#e3f2fd;color:#1976d2;padding:2px 6px;border-radius:2px;font-family:monospace;">' + barang.kode_barang + '</span></div>';
    itemHtml += '<div id="warning_' + itemIndex + '" style="display:none;background:#fff3cd;border:1px solid #ffc107;border-radius:3px;padding:6px;margin-bottom:6px;font-size:12px;color:#856404;"><i class="fas fa-exclamation-triangle"></i> Jumlah melebihi stok tersedia (' + barang.stok + ' ' + barang.satuan + ')</div>';
    itemHtml += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;"><div>';
    itemHtml += '<label style="display:block;color:#666;margin-bottom:4px;">Jumlah Barang</label>';
    itemHtml += '<input type="number" name="items[' + itemIndex + '][jumlah]" value="1" min="1" style="width:100%;padding:4px;border:1px solid #ddd;border-radius:3px;font-size:12px;" onchange="checkStokAndHitung(' + itemIndex + ')">';
    itemHtml += '</div><div><label style="display:block;color:#666;margin-bottom:4px;">Harga</label>';
    itemHtml += '<div style="padding:4px;background:#f5f5f5;border:1px solid #ddd;border-radius:3px;font-size:12px;">Rp ' + parseInt(barang.harga_jual).toLocaleString('id-ID') + '</div></div></div>';
    itemHtml += '<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;margin-top:8px;"><div>';
    itemHtml += '<label style="display:block;color:#666;margin-bottom:4px;">Satuan</label>';
    itemHtml += '<div style="padding:4px;background:#f5f5f5;border:1px solid #ddd;border-radius:3px;font-size:12px;">' + barang.satuan + '</div></div>';
    itemHtml += '<div><label style="display:block;color:#666;margin-bottom:4px;">Diskon (Rp)</label>';
    itemHtml += '<input type="number" name="items[' + itemIndex + '][diskon]" value="0" min="0" style="width:100%;padding:4px;border:1px solid #ddd;border-radius:3px;font-size:12px;" onchange="hitungTotal()">';
    itemHtml += '</div></div>';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][id_barang]" value="' + barang.id_barang + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][nama_barang]" value="' + barang.nama_barang + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][kode_barang]" value="' + barang.kode_barang + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][satuan]" value="' + barang.satuan + '">';
    itemHtml += '<input type="hidden" name="items[' + itemIndex + '][harga_satuan]" value="' + barang.harga_jual + '">';
    itemHtml += '<button type="button" style="width:100%;margin-top:6px;background:#ff6b6b;color:white;border:none;padding:4px;border-radius:3px;cursor:pointer;font-size:11px;" onclick="removeItem(' + itemIndex + ')">Hapus</button></div>';
    
    container.insertAdjacentHTML('beforeend', itemHtml);
    noItemsMsg.style.display = 'none';
    itemIndex++;
    updateSelectedCount();
    hitungTotal();
}

function checkStokAndHitung(idx) {
    const row = document.querySelector('[data-item-index="' + idx + '"]');
    if (!row) return;
    
    const stok = parseInt(row.getAttribute('data-stok')) || 0;
    const jumlahInput = row.querySelector('input[name*="[jumlah]"]');
    const jumlah = parseInt(jumlahInput.value) || 0;
    const warningDiv = document.getElementById('warning_' + idx);
    
    if (jumlah > stok) {
        warningDiv.style.display = 'block';
        jumlahInput.style.borderColor = '#ff9800';
        jumlahInput.style.borderWidth = '2px';
    } else {
        warningDiv.style.display = 'none';
        jumlahInput.style.borderColor = '#ddd';
        jumlahInput.style.borderWidth = '1px';
    }
    
    hitungTotal();
}

function removeItem(idx) {
    const row = document.querySelector('[data-item-index="' + idx + '"]');
    if (row) row.remove();
    const container = document.getElementById('selected_container');
    if (container.children.length === 0) {
        document.getElementById('no_items_msg').style.display = 'block';
    }
    updateSelectedCount();
    hitungTotal();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('[data-item-index]').length;
    document.getElementById('selected_count').textContent = count;
}

function hitungTotal() {
    let totalItems = 0;
    let totalHarga = 0;
    const rows = document.querySelectorAll('[data-item-index]');
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const diskon = parseFloat(row.querySelector('input[name*="[diskon]"]').value) || 0;
        totalItems += jumlah;
        totalHarga += (jumlah * harga) - diskon;
    }
    document.getElementById('total_items').textContent = totalItems;
    document.getElementById('total_display').textContent = 'Rp ' + Math.floor(totalHarga).toLocaleString('id-ID');
    document.getElementById('subtotal_display').textContent = 'Rp ' + Math.floor(totalHarga).toLocaleString('id-ID');
    
    // Update jumlah hutang jika ada hutang
    if (document.getElementById('ada_hutang').checked) {
        document.getElementById('jumlah_hutang').value = Math.floor(totalHarga);
        document.getElementById('jumlah_hutang_display').textContent = 'Rp ' + Math.floor(totalHarga).toLocaleString('id-ID');
    }
    
    hitungKembalian();
}

function hitungKembalian() {
    const uangDiberikan = parseFloat(document.getElementById('uang_diberikan').value) || 0;
    let totalHarga = 0;
    const rows = document.querySelectorAll('[data-item-index]');
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const diskon = parseFloat(row.querySelector('input[name*="[diskon]"]').value) || 0;
        totalHarga += (jumlah * harga) - diskon;
    }
    const kembalian = uangDiberikan - totalHarga;
    const display = document.getElementById('kembalian_display');
    display.textContent = 'Rp ' + Math.floor(kembalian).toLocaleString('id-ID');
    display.style.color = kembalian < 0 ? '#dc2626' : '#059669';
}

function validateForm() {
    const items = document.querySelectorAll('[data-item-index]');
    if (items.length === 0) {
        alert('Tambahkan minimal satu item!');
        return false;
    }
    
    let barangMelebihi = [];
    
    for (let i = 0; i < items.length; i++) {
        const row = items[i];
        const jumlah = parseInt(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const stok = parseInt(row.getAttribute('data-stok')) || 0;
        const namaBr = row.querySelector('div[style*="font-weight:bold"]')?.textContent.split(' ')[0] || 'Barang';
        
        if (!jumlah || jumlah < 1) {
            alert('Lengkapi semua item barang dan jumlah!');
            return false;
        }
        
        if (jumlah > stok) {
            barangMelebihi.push(`${namaBr} (jumlah: ${jumlah}, stok: ${stok})`);
        }
    }
    
    if (barangMelebihi.length > 0) {
        alert('Barang berikut melebihi stok tersedia:\n\n' + barangMelebihi.join('\n') + '\n\nPastikan jumlah tidak melebihi stok!');
        return false;
    }
    
    // Validasi hutang jika ada hutang
    if (document.getElementById('ada_hutang').checked) {
        const namaPenghutang = document.getElementById('nama_penghutang').value.trim();
        const jatuhTempo = document.getElementById('jatuh_tempo').value;
        if (!namaPenghutang || !jatuhTempo) {
            alert('Lengkapi nama yang ngutang dan tanggal jatuh tempo!');
            return false;
        }
    }
    
    return true;
}

function toggleHutangFields() {
    const hutangSection = document.getElementById('hutang_section');
    const paymentSection = document.getElementById('payment_section');
    const customerSection = document.getElementById('customer_section');
    const adaHutang = document.getElementById('ada_hutang').checked;
    const namaPembeli = document.getElementById('nama_pembeli');
    const namaPenghutang = document.getElementById('nama_penghutang');
    
    hutangSection.style.display = adaHutang ? 'block' : 'none';
    paymentSection.style.display = adaHutang ? 'none' : 'grid';
    customerSection.style.display = adaHutang ? 'none' : 'block';
    
    if (adaHutang) {
        // Copy nama pembeli ke nama penghutang saat hutang diaktifkan
        if (namaPembeli.value) {
            namaPenghutang.value = namaPembeli.value;
        }
        hitungTotal();
    } else {
        document.getElementById('jumlah_hutang').value = 0;
        document.getElementById('jumlah_hutang_display').textContent = 'Rp 0';
    }
}

function printNota() {
    const items = document.querySelectorAll('[data-item-index]');
    if (items.length === 0) {
        alert('Tambahkan minimal satu item untuk print nota!');
        return;
    }
    const cfg = notaConfig || {};
    const width = parseInt(cfg.lebar_kertas || 80, 10);
    const fontNota = cfg.font_nota || 'Arial';
    const escapeMap = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'};
    const escapeHtml = (text) => text ? String(text).replace(/[&<>"']/g, (c) => escapeMap[c] || c) : '';
    const formatRupiah = (value) => 'Rp ' + Math.floor(value).toLocaleString('id-ID');

    let html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print Nota</title><style>body{font-family:"' + fontNota + '", monospace;width:' + width + 'mm;margin:0;padding:10mm}.header{text-align:center;margin-bottom:8mm}.header h2{margin:0;font-size:14px}.header p{margin:3px 0;font-size:10px;color:#444}.header .muted{color:#777;font-size:9px}.header .custom{margin-top:4px;color:#333;font-size:9px;line-height:1.3}hr{margin:5px 0;border:none;border-top:1px solid #000}.info{font-size:10px;margin-bottom:6mm;line-height:1.4}table{width:100%;font-size:9px;border-collapse:collapse;margin-bottom:8mm}table thead tr{border-bottom:1px solid #000}table th{text-align:left;padding:3px 0;font-weight:bold}table td{padding:3px 0}table th:nth-child(2),table td:nth-child(2),table th:nth-child(3),table td:nth-child(3),table th:nth-child(4),table td:nth-child(4){text-align:right}table tbody tr{border-bottom:1px dotted #ccc}table .muted{color:#555;font-size:8px}.summary{font-size:10px;margin-bottom:6mm;line-height:1.6}.summary-row{display:flex;justify-content:space-between}.total-row{border-top:1px solid #000;padding-top:3px;font-weight:bold}.footer{text-align:center;font-size:9px;color:#444;margin-top:8mm;line-height:1.4}@media print{body{margin:0;padding:5mm}}</style></head><body>';
    html += '<div class="header"><h2>' + escapeHtml(cfg.nama_toko || 'UD. BERSAUDARA') + '</h2>';
    const alamat = escapeHtml(cfg.alamat_toko || '');
    const telp = escapeHtml(cfg.nomor_telepon || '');
    const email = escapeHtml(cfg.email_toko || '');
    if (alamat) html += '<p class="muted">' + alamat + '</p>';
    if (telp) html += '<p class="muted">Telp: ' + telp + '</p>';
    if (email) html += '<p class="muted">Email: ' + email + '</p>';
    const headerLines = (cfg.custom_header_text || '').split(/\n+/).map(l => l.trim()).filter(Boolean);
    if (headerLines.length) {
        html += '<div class="custom">';
        for (let i = 0; i < headerLines.length; i++) {
            html += '<div>' + escapeHtml(headerLines[i]) + '</div>';
        }
        html += '</div>';
    }
    html += '<hr></div>';
    const now = new Date();
    const tanggal = ('0' + now.getDate()).slice(-2) + '/' + ('0' + (now.getMonth() + 1)).slice(-2) + '/' + now.getFullYear();
    const waktu = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2);
    html += '<div class="info"><div><strong>Tanggal:</strong> ' + tanggal;
    if ((cfg.tampilkan_jam ?? 1) == 1) {
        html += ' ' + waktu;
    }
    html += '</div>';
    const pembeliNama = document.getElementById('nama_pembeli').value.trim();
    if ((cfg.tampilkan_nama_pembeli ?? 1) == 1 && pembeliNama) {
        html += '<div><strong>Pembeli:</strong> ' + escapeHtml(pembeliNama) + '</div>';
    }
    html += '</div><table><thead><tr><th>Item</th><th>Jumlah</th><th>Harga</th><th>Total</th></tr></thead><tbody>';
    let totalHarga = 0, totalQty = 0, totalDiskon = 0, totalBruto = 0;
    for (let i = 0; i < items.length; i++) {
        const row = items[i];
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const diskon = parseFloat(row.querySelector('input[name*="[diskon]"]').value) || 0;
        const nama = row.querySelector('input[name*="[nama_barang]"]').value || 'Item';
        const kode = row.querySelector('input[name*="[kode_barang]"]').value || '';
        const satuan = row.querySelector('input[name*="[satuan]"]').value || '';
        const bruto = jumlah * harga;
        const subtotal = bruto - diskon;
        totalQty += jumlah;
        totalHarga += subtotal;
        totalDiskon += diskon;
        totalBruto += bruto;
        let itemCell = '<div>' + escapeHtml(nama.substring(0, 22)) + '</div>';
        if ((cfg.tampilkan_kode_barang ?? 1) == 1 && kode) {
            itemCell += '<div class="muted">Kode: ' + escapeHtml(kode) + '</div>';
        }
        if ((cfg.tampilkan_satuan ?? 1) == 1 && satuan) {
            itemCell += '<div class="muted">Satuan: ' + escapeHtml(satuan) + '</div>';
        }
        html += '<tr><td>' + itemCell + '</td><td>' + jumlah + '</td><td>' + formatRupiah(harga) + '</td><td>' + formatRupiah(subtotal) + '</td></tr>';
    }
    const uangDiberikan = parseFloat(document.getElementById('uang_diberikan').value) || 0;
    const kembalian = uangDiberikan - totalHarga;
    html += '</tbody></table><div class="summary">';
    html += '<div class="summary-row"><span><strong>Total Item:</strong></span><span>' + totalQty + '</span></div>';
    if ((cfg.jumlah_diskon_terpisah ?? 0) == 1) {
        html += '<div class="summary-row"><span><strong>Subtotal:</strong></span><span>' + formatRupiah(totalBruto) + '</span></div>';
        html += '<div class="summary-row"><span><strong>Diskon:</strong></span><span>' + formatRupiah(totalDiskon) + '</span></div>';
    }
    html += '<div class="summary-row"><span><strong>Total Harga:</strong></span><span>' + formatRupiah(totalHarga) + '</span></div>';
    html += '<div class="summary-row"><span><strong>Uang Diberikan:</strong></span><span>' + formatRupiah(uangDiberikan) + '</span></div>';

    const adaHutang = document.getElementById('ada_hutang').checked;
    if (adaHutang && (cfg.tampilkan_info_hutang ?? 1) == 1) {
        const namaPenghutang = document.getElementById('nama_penghutang').value.trim();
        const jatuhTempo = document.getElementById('jatuh_tempo').value;
        const jumlahHutang = parseFloat(document.getElementById('jumlah_hutang').value) || totalHarga;
        if (namaPenghutang) {
            html += '<div class="summary-row"><span><strong>Nama Hutang:</strong></span><span>' + escapeHtml(namaPenghutang) + '</span></div>';
        }
        if (jatuhTempo) {
            html += '<div class="summary-row"><span><strong>Jatuh Tempo:</strong></span><span>' + escapeHtml(jatuhTempo) + '</span></div>';
        }
        html += '<div class="summary-row"><span><strong>Jumlah Hutang:</strong></span><span>' + formatRupiah(jumlahHutang) + '</span></div>';
    }

    html += '<div class="summary-row total-row"><span>Kembalian:</span><span>' + formatRupiah(kembalian) + '</span></div>';
    html += '</div>';

    const footerLines = (cfg.custom_footer_text || '').split(/\n+/).map(l => l.trim()).filter(Boolean);
    html += '<div class="footer">';
    if (cfg.footer_nota) {
        html += '<div>' + escapeHtml(cfg.footer_nota) + '</div>';
    }
    for (let i = 0; i < footerLines.length; i++) {
        html += '<div>' + escapeHtml(footerLines[i]) + '</div>';
    }
    html += '<div>' + escapeHtml(cfg.nama_toko || 'UD. BERSAUDARA') + '</div>';
    html += '</div></body></html>';
    const printWindow = window.open('', '', 'width=400,height=600');
    printWindow.document.write(html);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 250);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DEBUG: Init on DOMContentLoaded');
        renderBarangList();
        const searchInput = document.getElementById('search_barang_main');
        if (searchInput) {
            let searchTimeout = null;
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => renderBarangList(e.target.value), 300);
            });
        }
    });
} else {
    console.log('DEBUG: Init immediate');
    renderBarangList();
    const searchInput = document.getElementById('search_barang_main');
    if (searchInput) {
        let searchTimeout = null;
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => renderBarangList(e.target.value), 300);
        });
    }
}

// Sinkronisasi nama penghutang dengan nama pembeli
const namaPenghutangInput = document.getElementById('nama_penghutang');
const namaPembeliInput = document.getElementById('nama_pembeli');

if (namaPenghutangInput && namaPembeliInput) {
    namaPenghutangInput.addEventListener('input', function() {
        namaPembeliInput.value = this.value;
    });
}
</script>

<?php 
$content = ob_get_clean();
$title = 'Tambah Penjualan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>

