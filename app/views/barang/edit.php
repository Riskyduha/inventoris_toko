<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8 max-w-2xl mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-8 text-center">
        <i class="fas fa-edit text-yellow-600 mr-2"></i>Edit Barang
    </h2>

    <form action="/barang/update/<?= $barang['id_barang'] ?>" method="POST">
        <div class="mb-6">
            <label for="kode_barang" class="block text-gray-700 font-bold mb-2 text-sm">Kode Barang *</label>
            <input type="text" id="kode_barang" name="kode_barang" required
                   value="<?= htmlspecialchars($barang['kode_barang']) ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="nama_barang" class="block text-gray-700 font-bold mb-2 text-sm">Nama Barang *</label>
            <input type="text" id="nama_barang" name="nama_barang" required
                   value="<?= htmlspecialchars($barang['nama_barang']) ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="kategori" class="block text-gray-700 font-bold mb-2 text-sm">Kategori *</label>
            <select id="kategori" name="id_kategori" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($kategori as $kat): ?>
                    <option value="<?= $kat['id_kategori'] ?>" <?= $barang['id_kategori'] == $kat['id_kategori'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($kat['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="satuan" class="block text-gray-700 font-bold mb-2 text-sm">Satuan *</label>
            <select id="satuan" name="satuan" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">-- Pilih Satuan --</option>
                <?php foreach ($satuan as $sat): ?>
                    <option value="<?= htmlspecialchars($sat['nama_satuan']) ?>" <?= $barang['satuan'] == $sat['nama_satuan'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sat['nama_satuan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="harga_beli" class="block text-gray-700 font-bold mb-2 text-sm">Harga Beli</label>
            <input type="number" id="harga_beli" name="harga_beli" required min="0" step="0.01"
                   value="<?= $barang['harga_beli'] ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-6">
            <label for="harga_jual" class="block text-gray-700 font-bold mb-2 text-sm">Harga Jual</label>
            <input type="number" id="harga_jual" name="harga_jual" required min="0" step="0.01"
                   value="<?= $barang['harga_jual'] ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="mb-8">
            <label for="stok" class="block text-gray-700 font-bold mb-2 text-sm">Stok</label>
            <input type="number" id="stok" name="stok" required min="0"
                   value="<?= $barang['stok'] ?>"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>

        <div class="flex gap-4 justify-center">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg transition font-semibold">
                <i class="fas fa-save mr-2"></i>Update
            </button>
            <a href="/barang" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </form>
</div>

<?php 
$content = ob_get_clean();
$title = 'Edit Barang - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
