<?php ob_start(); ?>

<div class="bg-white rounded-lg shadow-lg border border-gray-200 p-8">
    <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">
        <i class="fas fa-info-circle text-blue-600 mr-2"></i>Detail Penjualan
    </h2>

    <!-- Info Transaksi -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-gray-50 border border-gray-300 rounded-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b-2 border-gray-400">Informasi Transaksi</h3>
            <div class="space-y-5">
                <div class="flex justify-between items-start">
                    <span class="text-gray-600 text-sm font-medium">Tanggal:</span>
                    <span class="text-gray-800 font-bold text-right"><?= date('d/m/Y', strtotime($penjualan['tanggal'])) ?></span>
                </div>
                <div class="flex justify-between items-start border-t pt-4">
                    <span class="text-gray-600 text-sm font-medium">Waktu:</span>
                    <span class="text-gray-800 font-bold text-right"><?= date('H:i', strtotime($penjualan['tanggal'])) ?></span>
                </div>
                <div class="flex justify-between items-start border-t pt-4">
                    <span class="text-gray-600 text-sm font-medium">Nama Pembeli:</span>
                    <span class="text-gray-800 font-bold text-right"><?= htmlspecialchars($penjualan['nama_pembeli'] ?? '-') ?></span>
                </div>
                <?php if (!empty($penjualan['keterangan'])): ?>
                <div class="border-t pt-4">
                    <p class="text-gray-600 text-sm font-medium mb-2">Keterangan:</p>
                    <p class="text-gray-800 bg-white p-3 rounded border border-gray-200 text-sm"><?= htmlspecialchars($penjualan['keterangan']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-6">
            <h3 class="text-xl font-bold text-blue-900 mb-6 pb-3 border-b-2 border-blue-400">Ringkasan Pembayaran</h3>
            <div class="space-y-5">
                <div class="flex justify-between items-center">
                    <span class="text-gray-700 text-sm font-medium">Total Harga:</span>
                    <span class="text-2xl font-bold text-blue-600"><?= formatRupiah($penjualan['total_harga']) ?></span>
                </div>
                <div class="flex justify-between items-center border-t-2 border-blue-200 pt-4">
                    <span class="text-gray-700 text-sm font-medium">Uang Diberikan:</span>
                    <span class="text-xl font-bold text-gray-800"><?= formatRupiah($penjualan['uang_diberikan']) ?></span>
                </div>
                <div class="flex justify-between items-center border-t-2 border-blue-200 pt-4">
                    <span class="text-gray-700 text-sm font-medium">Kembalian:</span>
                    <span class="text-2xl font-bold <?= $penjualan['kembalian'] >= 0 ? 'text-green-600' : 'text-red-600' ?>"><?= formatRupiah($penjualan['kembalian']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Daftar Item -->
    <div class="mb-8">
        <h3 class="text-xl font-bold text-gray-800 mb-6 pb-3 border-b-2 border-gray-300">Item Penjualan</h3>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 rounded-lg">
                <thead class="bg-blue-100 border-b-2 border-blue-300">
                    <tr>
                        <th class="px-6 py-4 text-center text-sm font-bold w-12">No</th>
                        <th class="px-6 py-4 text-left text-sm font-bold w-20">Kode</th>
                        <th class="px-6 py-4 text-left text-sm font-bold w-40">Nama Barang</th>
                        <th class="px-6 py-4 text-center text-sm font-bold w-28">Jumlah</th>
                        <th class="px-6 py-4 text-right text-sm font-bold w-32">Harga Jual</th>
                        <th class="px-6 py-4 text-right text-sm font-bold w-28">Diskon</th>
                        <th class="px-6 py-4 text-right text-sm font-bold w-32">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($details as $index => $detail): ?>
                    <tr class="hover:bg-blue-50 transition duration-200">
                        <td class="px-6 py-4 text-center font-medium text-gray-800"><?= $index + 1 ?></td>
                        <td class="px-6 py-4 font-mono text-sm text-gray-600"><?= htmlspecialchars($detail['kode_barang'] ?? '-') ?></td>
                        <td class="px-6 py-4 font-semibold text-gray-800"><?= htmlspecialchars($detail['nama_barang']) ?></td>
                        <td class="px-6 py-4 text-center font-medium text-gray-800"><?= $detail['jumlah'] ?> <?= $detail['satuan'] ?></td>
                        <td class="px-6 py-4 text-right font-semibold text-gray-800"><?= formatRupiah($detail['harga_satuan']) ?></td>
                        <td class="px-6 py-4 text-right font-semibold <?= $detail['diskon'] > 0 ? 'text-red-600' : 'text-gray-500' ?>"><?= $detail['diskon'] > 0 ? formatRupiah($detail['diskon']) : '-' ?></td>
                        <td class="px-6 py-4 text-right font-bold text-green-600"><?= formatRupiah($detail['subtotal']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Buttons -->
    <div class="flex gap-4 justify-center flex-wrap">
        <a href="/penjualan/edit/<?= $penjualan['id_penjualan'] ?>" class="bg-yellow-600 hover:bg-yellow-700 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-edit mr-2"></i>Edit
        </a>
        <a href="/penjualan/delete/<?= $penjualan['id_penjualan'] ?>" 
           onclick="return confirm('Yakin ingin menghapus penjualan ini? Stok akan dikembalikan.')"
           class="bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-trash mr-2"></i>Hapus
        </a>
        <button type="button" onclick="printNotaDetail()" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-print mr-2"></i>Print Nota
        </button>
        <a href="/penjualan" class="bg-gray-500 hover:bg-gray-600 text-white px-8 py-3 rounded-lg transition font-semibold">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>
</div>

<script>
    const detailPenjualanData = <?= json_encode($penjualan) ?>;
    const detailItems = <?= json_encode($details) ?>;
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
        tampilkan_info_hutang: 1,
        font_nota: 'Arial'
    }, <?= json_encode($notaConfig ?? []) ?>);

    function printNotaDetail() {
        const cfg = notaConfig || {};
        const width = parseInt(cfg.lebar_kertas || 80, 10);
        const fontNota = cfg.font_nota || 'Arial';
        const escapeMap = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'};
        const escapeHtml = (text) => text ? String(text).replace(/[&<>"']/g, (c) => escapeMap[c] || c) : '';
        const formatRupiah = (value) => 'Rp ' + Math.floor(Number(value) || 0).toLocaleString('id-ID');

        const tanggalRaw = detailPenjualanData.tanggal || new Date().toISOString();
        const dateObj = new Date(tanggalRaw);
        const tanggal = ('0' + dateObj.getDate()).slice(-2) + '/' + ('0' + (dateObj.getMonth() + 1)).slice(-2) + '/' + dateObj.getFullYear();
        const waktu = ('0' + dateObj.getHours()).slice(-2) + ':' + ('0' + dateObj.getMinutes()).slice(-2);

        let html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Print Nota</title><style>@page{margin:1mm}body{font-family:"' + fontNota + '", monospace;width:' + width + 'mm;margin:0 auto;padding:2mm;font-size:13px}.header{text-align:center;margin-bottom:6mm}.header h2{margin:0;font-size:18px}.header p{margin:2px 0;font-size:12px;color:#444}.header .muted{color:#777;font-size:11px}.header .custom{margin-top:4px;color:#333;font-size:11px;line-height:1.3}hr{margin:4px 0;border:none;border-top:1px solid #000}.info{font-size:13px;margin-bottom:5mm;line-height:1.5}table{width:100%;font-size:13px;border-collapse:collapse;margin-bottom:6mm}table thead tr{border-bottom:1px solid #000}table th{text-align:left;padding:3px 0;font-weight:bold}table td{padding:3px 0}table th:nth-child(2),table td:nth-child(2),table th:nth-child(3),table td:nth-child(3),table th:nth-child(4),table td:nth-child(4){text-align:right}table tbody tr{border-bottom:1px dotted #ccc}table .muted{color:#555;font-size:11px}.summary{font-size:13px;margin-bottom:5mm;line-height:1.5}.summary-row{display:flex;justify-content:space-between}.total-row{border-top:1px solid #000;padding-top:3px;font-weight:bold}.footer{text-align:center;font-size:12px;color:#444;margin-top:6mm;line-height:1.4}@media print{body{margin:0 auto;padding:2mm}}</style></head><body>';
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
        html += '<div class="info"><div><strong>Tanggal:</strong> ' + tanggal;
        if ((cfg.tampilkan_jam ?? 1) == 1) {
            html += ' ' + waktu;
        }
        html += '</div>';
        const pembeliNama = detailPenjualanData.nama_pembeli || '';
        if ((cfg.tampilkan_nama_pembeli ?? 1) == 1 && pembeliNama) {
            html += '<div><strong>Pembeli:</strong> ' + escapeHtml(pembeliNama) + '</div>';
        }
        html += '</div><table><thead><tr><th>Item</th><th>Jumlah</th><th>Harga</th><th>Total</th></tr></thead><tbody>';

        let totalHarga = 0, totalQty = 0, totalDiskon = 0, totalBruto = 0;
        for (let i = 0; i < detailItems.length; i++) {
            const item = detailItems[i];
            const jumlah = Number(item.jumlah) || 0;
            const harga = Number(item.harga_satuan) || 0;
            const diskon = Number(item.diskon) || 0;
            const nama = item.nama_barang || 'Item';
            const kode = item.kode_barang || '';
            const satuan = item.satuan || '';
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

        const uangDiberikan = Number(detailPenjualanData.uang_diberikan) || 0;
        const kembalian = uangDiberikan - totalHarga;
        html += '</tbody></table><div class="summary">';
        html += '<div class="summary-row"><span><strong>Total Item:</strong></span><span>' + totalQty + '</span></div>';
        if ((cfg.jumlah_diskon_terpisah ?? 0) == 1) {
            html += '<div class="summary-row"><span><strong>Subtotal:</strong></span><span>' + formatRupiah(totalBruto) + '</span></div>';
            html += '<div class="summary-row"><span><strong>Diskon:</strong></span><span>' + formatRupiah(totalDiskon) + '</span></div>';
        }
        html += '<div class="summary-row"><span><strong>Total Harga:</strong></span><span>' + formatRupiah(totalHarga) + '</span></div>';
        html += '<div class="summary-row"><span><strong>Uang Diberikan:</strong></span><span>' + formatRupiah(uangDiberikan) + '</span></div>';
        html += '<div class="summary-row"><span><strong>Kembalian:</strong></span><span>' + formatRupiah(kembalian) + '</span></div>';

        if ((detailPenjualanData.ada_hutang ?? 0) == 1 && (cfg.tampilkan_info_hutang ?? 1) == 1) {
            if (detailPenjualanData.nama_pembeli) {
                html += '<div class="summary-row"><span><strong>Nama Hutang:</strong></span><span>' + escapeHtml(detailPenjualanData.nama_pembeli) + '</span></div>';
            }
            // Jika ada field tambahan hutang, bisa ditambahkan di sini
        }

        if (cfg.custom_footer_text) {
            html += '<div class="summary-row total-row" style="flex-direction:column;align-items:center;gap:4px;text-align:center;">';
            html += '<div>' + escapeHtml(cfg.custom_footer_text) + '</div>';
            html += '</div>';
        }
        html += '</div>'; // summary

        if (cfg.footer_nota) {
            html += '<div class="footer">' + escapeHtml(cfg.footer_nota) + '</div>';
        }

        html += '</body></html>';

        const w = window.open('', '_blank');
        if (!w) return alert('Popup diblokir, izinkan popup untuk mencetak nota.');
        w.document.open();
        w.document.write(html);
        w.document.close();
        w.focus();
        w.print();
    }
</script>

<?php 
$content = ob_get_clean();
$title = 'Detail Penjualan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
