<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
        <i class="fas fa-edit text-yellow-600"></i>
        Edit Barang Masuk
    </h2>
    <p class="text-gray-600 mb-6">Hanya menampilkan item yang sudah dibeli untuk diedit tanpa panel pencarian baru.</p>

    <form action="/pembelian/update/<?= $pembelian['id_pembelian'] ?>" method="POST" id="formPembelian" onsubmit="return validateForm()">
        <!-- Info Supplier -->
        <div class="mb-6">
            <label for="nama_pembeli" class="block text-gray-700 font-semibold mb-2">Nama Supplier</label>
            <input type="text" id="nama_pembeli" name="nama_pembeli" placeholder="Masukkan nama supplier..."
                   value="<?= htmlspecialchars($pembelian['nama_pembeli'] ?? '') ?>"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500">
        </div>

        <!-- Daftar Item Pembelian -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-700">Item Barang Masuk</h3>
                <span id="selected_count" class="text-sm text-gray-500"></span>
            </div>

            <div id="selected_container" class="space-y-4"></div>
            <p id="no_items_msg" class="text-gray-500 text-center py-3 hidden">Tidak ada item untuk diedit.</p>
        </div>

        <!-- Ringkasan Transaksi -->
        <?php 
            $totalItemsAwal = 0;
            $totalHargaAwal = 0;
            foreach ($details as $item) {
                $totalItemsAwal += (float)($item['jumlah'] ?? 0);
                $totalHargaAwal += ((float)($item['jumlah'] ?? 0) * (float)($item['harga_satuan'] ?? 0));
            }
        ?>
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-gray-600 text-sm">Total Item</p>
                    <p class="text-2xl font-bold text-blue-600" id="total_items"><?= $totalItemsAwal ?></p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm">Total Barang Masuk</p>
                    <p class="text-2xl font-bold text-green-600" id="total_display">Rp <?= number_format($totalHargaAwal, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-save mr-2"></i>Simpan Perubahan
            </button>
            <a href="/pembelian" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<script>
let itemIndex = 0;
const existingDetails = <?= json_encode($details) ?>;
const satuanList = <?= json_encode($satuanList ?? []) ?>;

function appendItem(detail) {
    const container = document.getElementById('selected_container');
    const noItemsMsg = document.getElementById('no_items_msg');

    const itemHtml = `
        <div class="bg-white border border-gray-200 rounded-lg p-4 selected-row" data-item-index="${itemIndex}">
            <div class="flex justify-between items-start mb-3">
                <div>
                    <p class="font-semibold text-gray-800">${detail.nama_barang}</p>
                    <p class="text-sm text-gray-600">${detail.satuan ? 'Satuan: ' + detail.satuan : ''}</p>
                </div>
                <button type="button" class="text-red-500 hover:text-red-700" onclick="removeItem(${itemIndex})" aria-label="Hapus item">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>

            <input type="hidden" name="items[${itemIndex}][id_barang]" value="${detail.id_barang}">
            <input type="hidden" name="items[${itemIndex}][diskon]" value="0">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Satuan</label>
                    <select name="items[${itemIndex}][satuan]" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm">
                        <option value="">-- Pilih Satuan --</option>
                        ${satuanList.map(sat => `<option value="${sat.nama_satuan}" ${sat.nama_satuan === detail.satuan ? 'selected' : ''}>${sat.nama_satuan}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Jumlah</label>
                    <input type="number" name="items[${itemIndex}][jumlah]" value="${detail.jumlah}" min="1" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"
                           onchange="hitungTotal()" onkeyup="hitungTotal()">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Harga Beli</label>
                    <input type="number" name="items[${itemIndex}][harga_satuan]" value="${detail.harga_satuan}" min="0" step="0.01" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"
                           onchange="hitungTotal()" onkeyup="hitungTotal()">
                </div>
                <div>
                    <label class="block text-xs text-gray-600 mb-1">Subtotal</label>
                    <div class="subtotal w-full px-3 py-2 bg-green-50 border border-green-200 rounded-lg text-sm font-bold text-green-700">
                        Rp ${parseInt(detail.subtotal || 0).toLocaleString('id-ID')}
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', itemHtml);
    noItemsMsg.classList.add('hidden');
    itemIndex++;
}

function removeItem(idx) {
    const row = document.querySelector(`[data-item-index="${idx}"]`);
    if (row) {
        row.remove();
        if (document.querySelectorAll('.selected-row').length === 0) {
            document.getElementById('no_items_msg').classList.remove('hidden');
        }
        hitungTotal();
    }
}

function hitungTotal() {
    let totalItems = 0;
    let totalHarga = 0;

    document.querySelectorAll('.selected-row').forEach(row => {
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        const harga = parseFloat(row.querySelector('input[name*="[harga_satuan]"]').value) || 0;
        const subtotal = jumlah * harga;

        totalItems += jumlah;
        totalHarga += subtotal;

        row.querySelector('.subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    });

    document.getElementById('total_items').textContent = totalItems;
    document.getElementById('total_display').textContent = 'Rp ' + totalHarga.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    document.getElementById('selected_count').textContent = document.querySelectorAll('.selected-row').length + ' item';
}

function validateForm() {
    const items = document.querySelectorAll('.selected-row');
    if (items.length === 0) {
        alert('Tidak ada item untuk disimpan.');
        return false;
    }

    for (const row of items) {
        const jumlah = parseFloat(row.querySelector('input[name*="[jumlah]"]').value) || 0;
        if (jumlah <= 0) {
            alert('Jumlah tiap item harus lebih dari 0.');
            return false;
        }
    }
    return true;
}

window.addEventListener('load', () => {
    if (existingDetails && existingDetails.length > 0) {
        existingDetails.forEach(detail => appendItem(detail));
    }
    if (document.querySelectorAll('.selected-row').length === 0) {
        document.getElementById('no_items_msg').classList.remove('hidden');
    }
    hitungTotal();
});
</script>

<?php 
$content = ob_get_clean();
$title = 'Edit Barang Masuk - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
