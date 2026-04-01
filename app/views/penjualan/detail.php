<?php ob_start(); ?>

<?php
$currentRole = strtolower(trim((string)($_SESSION['role'] ?? '')));
$isAdmin = ($currentRole === 'admin');
$totalItem = 0;
$totalDiskon = 0;
$totalLaba = 0;
foreach ($details as $d) {
    $totalItem += (float)($d['jumlah'] ?? 0);
    $totalDiskon += (float)($d['diskon'] ?? 0);
    $totalLaba += (float)($d['laba_item'] ?? 0);
}
?>

<div class="app-card p-5 sm:p-6 app-reveal">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 flex items-center gap-2">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                Detail Penjualan
            </h2>
            <p class="text-sm text-slate-500 mt-2">Informasi transaksi, ringkasan pembayaran, dan daftar item penjualan.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 font-semibold"><i class="fas fa-calendar-day mr-1"></i><?= date('d M Y', strtotime($penjualan['tanggal'])) ?></span>
            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold"><i class="fas fa-boxes mr-1"></i><?= number_format($totalItem, 0, ',', '.') ?> item</span>
            <span class="px-3 py-1 rounded-full <?= ($penjualan['kembalian'] ?? 0) >= 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?> font-semibold">
                <i class="fas <?= ($penjualan['kembalian'] ?? 0) >= 0 ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-1"></i>
                <?= ($penjualan['kembalian'] ?? 0) >= 0 ? 'Lunas/Tunai' : 'Kurang Bayar' ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-b from-slate-50 to-white p-4 xl:col-span-2">
            <h3 class="text-sm font-bold tracking-wide uppercase text-slate-600 mb-4">Informasi Transaksi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <p class="text-xs text-slate-500 mb-1">Tanggal</p>
                    <p class="font-semibold text-slate-800"><?= date('d/m/Y', strtotime($penjualan['tanggal'])) ?></p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3">
                    <p class="text-xs text-slate-500 mb-1">Waktu</p>
                    <p class="font-semibold text-slate-800"><?= date('H:i', strtotime($penjualan['tanggal'])) ?> WIB</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-3 md:col-span-2">
                    <p class="text-xs text-slate-500 mb-1">Nama Pembeli</p>
                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($penjualan['nama_pembeli'] ?? '-') ?></p>
                </div>
                <?php if (!empty($penjualan['keterangan'])): ?>
                <div class="rounded-xl border border-slate-200 bg-white p-3 md:col-span-2">
                    <p class="text-xs text-slate-500 mb-1">Keterangan</p>
                    <p class="text-slate-700"><?= htmlspecialchars($penjualan['keterangan']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-2xl border border-teal-200 bg-gradient-to-b from-teal-50 to-white p-4">
            <h3 class="text-sm font-bold tracking-wide uppercase text-teal-700 mb-4">Ringkasan Pembayaran</h3>
            <div class="space-y-3 text-sm">
                <div class="rounded-xl bg-white border border-teal-100 p-3 flex items-center justify-between">
                    <span class="text-slate-500">Total Harga</span>
                    <span class="text-lg font-extrabold text-teal-700"><?= formatRupiah($penjualan['total_harga']) ?></span>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 p-3 flex items-center justify-between">
                    <span class="text-slate-500">Uang Diberikan</span>
                    <span class="font-bold text-slate-800"><?= formatRupiah($penjualan['uang_diberikan']) ?></span>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 p-3 flex items-center justify-between">
                    <span class="text-slate-500">Kembalian</span>
                    <span class="text-lg font-extrabold <?= ($penjualan['kembalian'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-700' ?>">
                        <?= formatRupiah($penjualan['kembalian']) ?>
                    </span>
                </div>
                <div class="rounded-xl bg-white border border-slate-200 p-3 flex items-center justify-between">
                    <span class="text-slate-500">Total Diskon</span>
                    <span class="font-bold <?= $totalDiskon > 0 ? 'text-orange-600' : 'text-slate-700' ?>">
                        <?= $totalDiskon > 0 ? formatRupiah($totalDiskon) : '-' ?>
                    </span>
                </div>
                <?php if ($isAdmin): ?>
                <div class="rounded-xl bg-white border border-emerald-200 p-3 flex items-center justify-between">
                    <span class="text-slate-500">Laba Bersih Transaksi</span>
                    <span class="text-lg font-extrabold <?= $totalLaba >= 0 ? 'text-emerald-700' : 'text-red-700' ?>">
                        <?= formatRupiah($totalLaba) ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-bold text-slate-800">Item Penjualan</h3>
            <span class="text-xs px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold"><?= count($details) ?> jenis barang</span>
        </div>

        <div class="hidden md:block overflow-x-auto rounded-2xl border border-slate-200">
            <table class="min-w-full bg-white">
                <thead class="bg-slate-100/80">
                    <tr>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-600 w-12">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">Barang</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-600 w-28">Jumlah</th>
                        <?php if ($isAdmin): ?>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 w-36">Harga Beli</th>
                        <?php endif; ?>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 w-36">Harga</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 w-32">Diskon</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 w-40">Subtotal</th>
                        <?php if ($isAdmin): ?>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-600 w-40">Laba/Item</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($details as $index => $detail): ?>
                    <tr class="hover:bg-cyan-50/50 transition">
                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700"><?= $index + 1 ?></td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-slate-800"><?= htmlspecialchars($detail['nama_barang']) ?></p>
                            <p class="text-xs text-slate-500 font-mono mt-1"><?= htmlspecialchars($detail['kode_barang'] ?? '-') ?></p>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-slate-700"><?= number_format($detail['jumlah'], 0, ',', '.') ?> <?= htmlspecialchars($detail['satuan']) ?></td>
                        <?php if ($isAdmin): ?>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-700"><?= formatRupiah($detail['harga_beli_item'] ?? 0) ?></td>
                        <?php endif; ?>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-slate-700"><?= formatRupiah($detail['harga_satuan']) ?></td>
                        <td class="px-4 py-3 text-right text-sm font-semibold <?= ($detail['diskon'] ?? 0) > 0 ? 'text-orange-600' : 'text-slate-400' ?>">
                            <?= ($detail['diskon'] ?? 0) > 0 ? formatRupiah($detail['diskon']) : '-' ?>
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-emerald-700"><?= formatRupiah($detail['subtotal']) ?></td>
                        <?php if ($isAdmin): ?>
                        <td class="px-4 py-3 text-right text-sm font-bold <?= ($detail['laba_item'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-700' ?>">
                            <?= formatRupiah($detail['laba_item'] ?? 0) ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-3">
            <?php foreach ($details as $index => $detail): ?>
            <div class="rounded-xl border border-slate-200 bg-white p-3">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                        <p class="text-sm font-bold text-slate-800"><?= htmlspecialchars($detail['nama_barang']) ?></p>
                        <p class="text-[11px] text-slate-500 font-mono"><?= htmlspecialchars($detail['kode_barang'] ?? '-') ?></p>
                    </div>
                    <span class="text-xs font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-600">#<?= $index + 1 ?></span>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-lg bg-slate-50 border border-slate-200 p-2">
                        <p class="text-slate-500 mb-1">Jumlah</p>
                        <p class="font-semibold text-slate-700"><?= number_format($detail['jumlah'], 0, ',', '.') ?> <?= htmlspecialchars($detail['satuan']) ?></p>
                    </div>
                    <div class="rounded-lg bg-slate-50 border border-slate-200 p-2">
                        <p class="text-slate-500 mb-1">Harga</p>
                        <p class="font-semibold text-slate-700"><?= formatRupiah($detail['harga_satuan']) ?></p>
                    </div>
                    <div class="rounded-lg bg-slate-50 border border-slate-200 p-2">
                        <p class="text-slate-500 mb-1">Diskon</p>
                        <p class="font-semibold <?= ($detail['diskon'] ?? 0) > 0 ? 'text-orange-600' : 'text-slate-500' ?>">
                            <?= ($detail['diskon'] ?? 0) > 0 ? formatRupiah($detail['diskon']) : '-' ?>
                        </p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-2">
                        <p class="text-emerald-700 mb-1">Subtotal</p>
                        <p class="font-bold text-emerald-700"><?= formatRupiah($detail['subtotal']) ?></p>
                    </div>
                    <?php if ($isAdmin): ?>
                    <div class="rounded-lg bg-cyan-50 border border-cyan-200 p-2">
                        <p class="text-cyan-700 mb-1">Harga Beli</p>
                        <p class="font-semibold text-cyan-700"><?= formatRupiah($detail['harga_beli_item'] ?? 0) ?></p>
                    </div>
                    <div class="rounded-lg <?= ($detail['laba_item'] ?? 0) >= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' ?> border p-2">
                        <p class="<?= ($detail['laba_item'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-700' ?> mb-1">Laba/Item</p>
                        <p class="font-bold <?= ($detail['laba_item'] ?? 0) >= 0 ? 'text-emerald-700' : 'text-red-700' ?>"><?= formatRupiah($detail['laba_item'] ?? 0) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row flex-wrap gap-3">
        <a href="/penjualan/edit/<?= $penjualan['id_penjualan'] ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold transition">
            <i class="fas fa-edit"></i>
            Edit
        </a>
        <a href="/penjualan/delete/<?= $penjualan['id_penjualan'] ?>"
           onclick="return confirm('Yakin ingin menghapus penjualan ini? Stok akan dikembalikan.')"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold transition">
            <i class="fas fa-trash"></i>
            Hapus
        </a>
        <button type="button" onclick="printNotaDetail()" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition">
            <i class="fas fa-print"></i>
            Print Nota
        </button>
        <a href="/penjualan" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg app-btn-secondary font-semibold transition">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>
</div>

<div id="toast" class="hidden fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg"></div>

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

    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.className = 'fixed top-4 right-4 z-[60] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg';
        toast.classList.add(type === 'error' ? 'bg-red-100' : 'bg-emerald-100', type === 'error' ? 'text-red-700' : 'text-emerald-700', 'border', type === 'error' ? 'border-red-200' : 'border-emerald-200');
        toast.textContent = message;
        toast.classList.remove('hidden');
        setTimeout(() => toast.classList.add('hidden'), 2600);
    }

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
        const headerLines = (cfg.custom_header_text || '').split(/\n+/).map((l) => l.trim()).filter(Boolean);
        if (headerLines.length) {
            html += '<div class="custom">';
            for (let i = 0; i < headerLines.length; i++) {
                html += '<div>' + escapeHtml(headerLines[i]) + '</div>';
            }
            html += '</div>';
        }
        html += '<hr></div>';
        html += '<div class="info"><div><strong>Tanggal:</strong> ' + tanggal;
        if ((cfg.tampilkan_jam ?? 1) === 1) {
            html += ' ' + waktu;
        }
        html += '</div>';
        const pembeliNama = detailPenjualanData.nama_pembeli || '';
        if ((cfg.tampilkan_nama_pembeli ?? 1) === 1 && pembeliNama) {
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
            if ((cfg.tampilkan_kode_barang ?? 1) === 1 && kode) {
                itemCell += '<div class="muted">Kode: ' + escapeHtml(kode) + '</div>';
            }
            if ((cfg.tampilkan_satuan ?? 1) === 1 && satuan) {
                itemCell += '<div class="muted">Satuan: ' + escapeHtml(satuan) + '</div>';
            }
            html += '<tr><td>' + itemCell + '</td><td>' + jumlah + '</td><td>' + formatRupiah(harga) + '</td><td>' + formatRupiah(subtotal) + '</td></tr>';
        }

        const uangDiberikan = Number(detailPenjualanData.uang_diberikan) || 0;
        const kembalian = uangDiberikan - totalHarga;
        html += '</tbody></table><div class="summary">';
        html += '<div class="summary-row"><span><strong>Total Item:</strong></span><span>' + totalQty + '</span></div>';
        if ((cfg.jumlah_diskon_terpisah ?? 0) === 1) {
            html += '<div class="summary-row"><span><strong>Subtotal:</strong></span><span>' + formatRupiah(totalBruto) + '</span></div>';
            html += '<div class="summary-row"><span><strong>Diskon:</strong></span><span>' + formatRupiah(totalDiskon) + '</span></div>';
        }
        html += '<div class="summary-row"><span><strong>Total Harga:</strong></span><span>' + formatRupiah(totalHarga) + '</span></div>';
        html += '<div class="summary-row"><span><strong>Uang Diberikan:</strong></span><span>' + formatRupiah(uangDiberikan) + '</span></div>';
        html += '<div class="summary-row"><span><strong>Kembalian:</strong></span><span>' + formatRupiah(kembalian) + '</span></div>';

        if ((detailPenjualanData.ada_hutang ?? 0) === 1 && (cfg.tampilkan_info_hutang ?? 1) === 1) {
            if (detailPenjualanData.nama_pembeli) {
                html += '<div class="summary-row"><span><strong>Nama Hutang:</strong></span><span>' + escapeHtml(detailPenjualanData.nama_pembeli) + '</span></div>';
            }
        }

        html += '</div>';

        if (cfg.footer_nota) {
            html += '<div class="footer">' + escapeHtml(cfg.footer_nota) + '</div>';
        }

        html += '</body></html>';

        const w = window.open('', '_blank');
        if (!w) {
            showToast('Popup diblokir, izinkan popup untuk mencetak nota.', 'error');
            return;
        }
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
