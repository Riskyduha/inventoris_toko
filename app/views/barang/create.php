<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">
        <i class="fas fa-plus-circle text-blue-600 mr-2"></i>Tambah Barang Baru
    </h2>

    <form action="/barang/store" method="POST" id="formBarangCreate">
        <div class="mb-6">
            <label for="kode_barang" class="block text-gray-700 font-bold mb-2 text-sm">Kode Barang *</label>
            <input type="text" id="kode_barang" name="kode_barang" required
                   placeholder="Misal: BRG-001"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="nama_barang" class="block text-gray-700 font-bold mb-2 text-sm">Nama Barang *</label>
            <input type="text" id="nama_barang" name="nama_barang" required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="kategori" class="block text-gray-700 font-bold mb-2 text-sm">Kategori *</label>
            <select id="kategori" name="id_kategori" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori as $kat): ?>
                    <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="satuan" class="block text-gray-700 font-bold mb-2 text-sm">Satuan *</label>
            <select id="satuan" name="satuan" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Satuan --</option>
                <?php foreach ($satuan as $sat): ?>
                    <option value="<?= htmlspecialchars($sat['nama_satuan']) ?>"><?= htmlspecialchars($sat['nama_satuan']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="harga_beli" class="block text-gray-700 font-bold mb-2 text-sm">Harga Beli</label>
            <input type="text" id="harga_beli" name="harga_beli" required inputmode="numeric" autocomplete="off" data-price-input
                   placeholder="Contoh: 23.000"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="harga_jual" class="block text-gray-700 font-bold mb-2 text-sm">Harga Jual</label>
            <input type="text" id="harga_jual" name="harga_jual" required inputmode="numeric" autocomplete="off" data-price-input
                   placeholder="Contoh: 25.000"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        <p id="price_notice" class="hidden -mt-2 mb-6 text-sm text-red-600 font-semibold">
            Harga beli harus lebih kecil dari harga jual.
        </p>

        <div class="mb-8">
            <label for="stok" class="block text-gray-700 font-bold mb-2 text-sm">Stok Awal</label>
            <input type="number" id="stok" name="stok" required min="0" value="0"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-8">
            <label for="tanggal_expired" class="block text-gray-700 font-bold mb-2 text-sm">Tanggal Expired</label>
            <input type="date" id="tanggal_expired" name="tanggal_expired"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <p class="text-xs text-gray-500 mt-1">Kosongkan jika barang tidak memiliki tanggal kedaluwarsa.</p>
        </div>

        <div class="flex gap-4 justify-center">
            <button type="submit" class="app-btn-primary px-8 py-3 font-semibold" data-loading-text="Menyimpan...">
                <i class="fas fa-save mr-2"></i>Simpan
            </button>
            <a href="/barang" class="app-btn-secondary px-8 py-3 font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<script>
function toDigitOnly(value) {
    return String(value ?? '').replace(/[^\d]/g, '');
}

function formatThousandID(value) {
    const digits = toDigitOnly(value);
    if (!digits) return '';
    return parseInt(digits, 10).toLocaleString('id-ID');
}

function bindPriceInputFormatting(formId) {
    const form = document.getElementById(formId);
    if (!form) return;

    const priceInputs = form.querySelectorAll('[data-price-input]');
    const hargaBeliInput = form.querySelector('#harga_beli');
    const hargaJualInput = form.querySelector('#harga_jual');
    const validatePricePair = () => {
        const beli = parseInt(toDigitOnly(hargaBeliInput?.value || ''), 10) || 0;
        const jual = parseInt(toDigitOnly(hargaJualInput?.value || ''), 10) || 0;
        const invalid = beli > 0 && jual > 0 && beli >= jual;
        const notice = form.querySelector('#price_notice');
        [hargaBeliInput, hargaJualInput].forEach((el) => {
            if (!el) return;
            el.classList.toggle('border-red-400', invalid);
            el.classList.toggle('ring-2', invalid);
            el.classList.toggle('ring-red-100', invalid);
        });
        if (notice) {
            notice.classList.toggle('hidden', !invalid);
        }
        return !invalid;
    };

    priceInputs.forEach((input) => {
        input.addEventListener('input', () => {
            input.value = formatThousandID(input.value);
            validatePricePair();
        });
    });

    form.addEventListener('submit', (event) => {
        if (!validatePricePair()) {
            event.preventDefault();
            if (hargaBeliInput) hargaBeliInput.focus();
            return;
        }
        priceInputs.forEach((input) => {
            input.value = toDigitOnly(input.value) || '0';
        });
    });
}

bindPriceInputFormatting('formBarangCreate');
</script>

<?php 
$content = ob_get_clean();
$title = 'Tambah Barang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
