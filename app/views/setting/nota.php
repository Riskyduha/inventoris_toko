<?php ob_start(); ?>

<div class="space-y-6 app-reveal">
    <div class="app-card p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengaturan Nota</p>
                <h2 class="text-2xl font-bold text-slate-800 mt-1 flex items-center gap-2">
                    <i class="fas fa-receipt text-teal-600"></i>
                    Konfigurasi Format Nota
                </h2>
                <p class="text-slate-600 mt-2">Atur tampilan nota penjualan agar konsisten dan nyaman dibaca pelanggan.</p>
            </div>
            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold">
                <i class="fas fa-bolt"></i>
                Preview realtime
            </span>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="app-card border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-check-circle"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="app-card border border-red-200 bg-red-50 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i><?= $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form action="/setting/nota" method="POST" class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-7 space-y-6">
            <section class="app-card p-5 sm:p-6 border border-slate-200">
                <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-store text-blue-600"></i>Informasi Toko
                </h3>

                <div class="space-y-4">
                    <div>
                        <label for="nama_toko" class="block text-sm font-semibold text-slate-700 mb-2">Nama Toko <span class="text-red-600">*</span></label>
                        <input type="text" id="nama_toko" name="nama_toko" value="<?= htmlspecialchars($config['nama_toko'] ?? 'UD. BERSAUDARA') ?>" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500" required>
                        <p class="text-xs text-slate-500 mt-1">Ditampilkan di bagian paling atas nota.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nomor_telepon" class="block text-sm font-semibold text-slate-700 mb-2">Nomor Telepon</label>
                            <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?= htmlspecialchars($config['nomor_telepon'] ?? '') ?>" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                        <div>
                            <label for="email_toko" class="block text-sm font-semibold text-slate-700 mb-2">Email Toko</label>
                            <input type="email" id="email_toko" name="email_toko" value="<?= htmlspecialchars($config['email_toko'] ?? '') ?>" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500">
                        </div>
                    </div>

                    <div>
                        <label for="alamat_toko" class="block text-sm font-semibold text-slate-700 mb-2">Alamat Toko</label>
                        <textarea id="alamat_toko" name="alamat_toko" rows="3" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500"><?= htmlspecialchars($config['alamat_toko'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>

            <section class="app-card p-5 sm:p-6 border border-slate-200">
                <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-amber-600"></i>Format Nota
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="lebar_kertas" class="block text-sm font-semibold text-slate-700 mb-2">Lebar Kertas <span class="text-red-600">*</span></label>
                        <select id="lebar_kertas" name="lebar_kertas" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="40" <?= ($config['lebar_kertas'] ?? 80) == 40 ? 'selected' : '' ?>>40 mm (Label Printer)</option>
                            <option value="58" <?= ($config['lebar_kertas'] ?? 80) == 58 ? 'selected' : '' ?>>58 mm (Thermal Standard)</option>
                            <option value="76" <?= ($config['lebar_kertas'] ?? 80) == 76 ? 'selected' : '' ?>>76 mm (Thermal)</option>
                            <option value="80" <?= ($config['lebar_kertas'] ?? 80) == 80 ? 'selected' : '' ?>>80 mm (Thermal Wide)</option>
                            <option value="100" <?= ($config['lebar_kertas'] ?? 80) == 100 ? 'selected' : '' ?>>100 mm</option>
                            <option value="105" <?= ($config['lebar_kertas'] ?? 80) == 105 ? 'selected' : '' ?>>105 mm (A6)</option>
                            <option value="148" <?= ($config['lebar_kertas'] ?? 80) == 148 ? 'selected' : '' ?>>148 mm (A5)</option>
                            <option value="210" <?= ($config['lebar_kertas'] ?? 80) == 210 ? 'selected' : '' ?>>210 mm (A4)</option>
                        </select>
                    </div>

                    <div>
                        <label for="font_nota" class="block text-sm font-semibold text-slate-700 mb-2">Jenis Font <span class="text-red-600">*</span></label>
                        <select id="font_nota" name="font_nota" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500">
                            <option value="Arial" <?= ($config['font_nota'] ?? 'Arial') == 'Arial' ? 'selected' : '' ?>>Arial</option>
                            <option value="Times New Roman" <?= ($config['font_nota'] ?? 'Arial') == 'Times New Roman' ? 'selected' : '' ?>>Times New Roman</option>
                            <option value="Calibri" <?= ($config['font_nota'] ?? 'Arial') == 'Calibri' ? 'selected' : '' ?>>Calibri</option>
                            <option value="Georgia" <?= ($config['font_nota'] ?? 'Arial') == 'Georgia' ? 'selected' : '' ?>>Georgia</option>
                            <option value="Courier New" <?= ($config['font_nota'] ?? 'Arial') == 'Courier New' ? 'selected' : '' ?>>Courier New</option>
                            <option value="Verdana" <?= ($config['font_nota'] ?? 'Arial') == 'Verdana' ? 'selected' : '' ?>>Verdana</option>
                            <option value="Tahoma" <?= ($config['font_nota'] ?? 'Arial') == 'Tahoma' ? 'selected' : '' ?>>Tahoma</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="app-card p-5 sm:p-6 border border-slate-200">
                <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-align-center text-purple-600"></i>Konten Tambahan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="custom_header_text" class="block text-sm font-semibold text-slate-700 mb-2">Header Tambahan</label>
                        <textarea id="custom_header_text" name="custom_header_text" rows="3" placeholder="Contoh: Selamat berbelanja" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500"><?= htmlspecialchars($config['custom_header_text'] ?? '') ?></textarea>
                    </div>
                    <div>
                        <label for="footer_nota" class="block text-sm font-semibold text-slate-700 mb-2">Footer Nota</label>
                        <textarea id="footer_nota" name="footer_nota" rows="3" maxlength="200" placeholder="Contoh: Terima kasih atas pembelian Anda" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500"><?= htmlspecialchars($config['footer_nota'] ?? '') ?></textarea>
                        <p class="text-xs text-slate-500 mt-1"><span id="footerCounter">0</span>/200 karakter</p>
                    </div>
                </div>
            </section>

            <section class="app-card p-5 sm:p-6 border border-slate-200">
                <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-eye text-emerald-600"></i>Opsi Tampilan
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-teal-300 hover:bg-teal-50/40 transition cursor-pointer">
                        <input type="checkbox" id="tampilkan_jam" name="tampilkan_jam" <?= ($config['tampilkan_jam'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-teal-600 rounded">
                        <span class="text-sm font-medium text-slate-700">Tampilkan Jam</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-teal-300 hover:bg-teal-50/40 transition cursor-pointer">
                        <input type="checkbox" name="tampilkan_kode_barang" <?= ($config['tampilkan_kode_barang'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-teal-600 rounded">
                        <span class="text-sm font-medium text-slate-700">Tampilkan Kode Barang</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-teal-300 hover:bg-teal-50/40 transition cursor-pointer">
                        <input type="checkbox" name="tampilkan_satuan" <?= ($config['tampilkan_satuan'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-teal-600 rounded">
                        <span class="text-sm font-medium text-slate-700">Tampilkan Satuan Barang</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-teal-300 hover:bg-teal-50/40 transition cursor-pointer">
                        <input type="checkbox" name="tampilkan_nama_pembeli" <?= ($config['tampilkan_nama_pembeli'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-teal-600 rounded">
                        <span class="text-sm font-medium text-slate-700">Tampilkan Nama Pembeli</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-teal-300 hover:bg-teal-50/40 transition cursor-pointer">
                        <input type="checkbox" name="jumlah_diskon_terpisah" <?= ($config['jumlah_diskon_terpisah'] ?? 0) ? 'checked' : '' ?> class="w-4 h-4 text-teal-600 rounded">
                        <span class="text-sm font-medium text-slate-700">Diskon Terpisah</span>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-teal-300 hover:bg-teal-50/40 transition cursor-pointer">
                        <input type="checkbox" name="tampilkan_info_hutang" <?= ($config['tampilkan_info_hutang'] ?? 1) ? 'checked' : '' ?> class="w-4 h-4 text-teal-600 rounded">
                        <span class="text-sm font-medium text-slate-700">Tampilkan Info Hutang</span>
                    </label>
                </div>
            </section>

            <div class="app-card p-4 border border-slate-200 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-end">
                <a href="/setting/kategori-satuan" class="inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 transition font-semibold">
                    <i class="fas fa-arrow-left"></i>Kembali
                </a>
                <button type="submit" class="inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white transition font-semibold shadow-sm">
                    <i class="fas fa-save"></i>Simpan Konfigurasi
                </button>
            </div>
        </div>

        <aside class="xl:col-span-5">
            <div class="app-card p-4 sm:p-5 border border-slate-200 sticky top-6">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="fas fa-eye text-teal-600"></i>Preview Nota
                    </h3>
                    <span class="text-[11px] font-semibold px-2 py-1 rounded-full bg-slate-100 text-slate-600" id="paperLabel"><?= (int)($config['lebar_kertas'] ?? 80) ?> mm</span>
                </div>

                <div class="bg-slate-100 border border-slate-200 rounded-xl p-3">
                    <div id="notaPreview" style="font-family:<?= htmlspecialchars($config['font_nota'] ?? 'Arial') ?>, monospace; width:<?= (int)($config['lebar_kertas'] ?? 80) ?>mm; max-width:100%; background:#fff; border:1px dashed #334155; padding:4mm; margin:0 auto; font-size:9px; line-height:1.35; color:#0f172a;">
                        <div style="text-align:center; border-bottom:1px solid #0f172a; padding-bottom:2mm; margin-bottom:2mm;">
                            <div id="prevNamaToko" style="font-size:11px; font-weight:700;"><?= htmlspecialchars($config['nama_toko'] ?? 'UD. BERSAUDARA') ?></div>
                            <div id="prevAlamat" style="font-size:8px; color:#475569; margin-top:1mm;"><?= nl2br(htmlspecialchars($config['alamat_toko'] ?? 'Alamat toko')) ?></div>
                            <div id="prevTelp" style="font-size:8px; color:#475569;"><?= htmlspecialchars($config['nomor_telepon'] ?? '08xxxxxxxxxx') ?></div>
                            <div id="prevEmail" style="font-size:8px; color:#475569;"><?= htmlspecialchars($config['email_toko'] ?? 'email@toko.com') ?></div>
                        </div>

                        <div id="prevHeader" style="text-align:center; font-size:8px; margin-bottom:2mm; color:#334155;"><?= nl2br(htmlspecialchars($config['custom_header_text'] ?? 'Terima kasih sudah berbelanja')) ?></div>

                        <div style="display:flex; justify-content:space-between; font-size:8px; border-bottom:1px solid #cbd5e1; padding-bottom:1mm; margin-bottom:1.5mm;">
                            <span>27/01/2026 <span id="prevJam" style="display:<?= ($config['tampilkan_jam'] ?? 1) ? 'inline' : 'none' ?>;">14:30</span></span>
                            <span>No: INV-001</span>
                        </div>

                        <table style="width:100%; border-collapse:collapse; font-size:8px;">
                            <thead>
                                <tr style="border-bottom:1px solid #0f172a;">
                                    <th style="text-align:left; padding:1px 0;">Item</th>
                                    <th style="text-align:right; padding:1px 0;">Qty</th>
                                    <th style="text-align:right; padding:1px 0;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding:1px 0;">Contoh Barang</td>
                                    <td style="text-align:right; padding:1px 0;">2</td>
                                    <td style="text-align:right; padding:1px 0;">20.000</td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="margin-top:2mm; border-top:1px solid #0f172a; padding-top:1.5mm; text-align:right; font-weight:700;">Total: 20.000</div>
                        <div id="prevFooter" style="margin-top:2.5mm; border-top:1px solid #cbd5e1; padding-top:1.5mm; text-align:center; font-size:8px; color:#475569;"><?= nl2br(htmlspecialchars($config['footer_nota'] ?? 'Terima kasih atas pembelian Anda')) ?></div>
                    </div>
                </div>

                <p class="text-xs text-slate-500 mt-3">Preview akan menyesuaikan otomatis saat Anda mengubah form di kiri.</p>
            </div>
        </aside>
    </form>
</div>

<script>
(function() {
    const fields = {
        nama_toko: document.getElementById('nama_toko'),
        nomor_telepon: document.getElementById('nomor_telepon'),
        email_toko: document.getElementById('email_toko'),
        alamat_toko: document.getElementById('alamat_toko'),
        custom_header_text: document.getElementById('custom_header_text'),
        footer_nota: document.getElementById('footer_nota'),
        font_nota: document.getElementById('font_nota'),
        lebar_kertas: document.getElementById('lebar_kertas'),
        tampilkan_jam: document.getElementById('tampilkan_jam')
    };

    const preview = {
        root: document.getElementById('notaPreview'),
        paperLabel: document.getElementById('paperLabel'),
        nama: document.getElementById('prevNamaToko'),
        telp: document.getElementById('prevTelp'),
        email: document.getElementById('prevEmail'),
        alamat: document.getElementById('prevAlamat'),
        header: document.getElementById('prevHeader'),
        footer: document.getElementById('prevFooter'),
        jam: document.getElementById('prevJam'),
        footerCounter: document.getElementById('footerCounter')
    };

    function nl2brSafe(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML.replace(/\n/g, '<br>');
    }

    function setOrDefault(target, value, fallback) {
        if (!target) return;
        const content = (value || '').trim();
        target.innerHTML = nl2brSafe(content || fallback);
    }

    function syncPreview() {
        setOrDefault(preview.nama, fields.nama_toko?.value, 'UD. BERSAUDARA');
        setOrDefault(preview.telp, fields.nomor_telepon?.value, '08xxxxxxxxxx');
        setOrDefault(preview.email, fields.email_toko?.value, 'email@toko.com');
        setOrDefault(preview.alamat, fields.alamat_toko?.value, 'Alamat toko');
        setOrDefault(preview.header, fields.custom_header_text?.value, 'Terima kasih sudah berbelanja');
        setOrDefault(preview.footer, fields.footer_nota?.value, 'Terima kasih atas pembelian Anda');

        const selectedFont = fields.font_nota?.value || 'Arial';
        if (preview.root) {
            preview.root.style.fontFamily = `${selectedFont}, monospace`;
            const width = parseInt(fields.lebar_kertas?.value || '80', 10);
            preview.root.style.width = `${width}mm`;
            if (preview.paperLabel) preview.paperLabel.textContent = `${width} mm`;
        }

        if (preview.jam) {
            preview.jam.style.display = fields.tampilkan_jam?.checked ? 'inline' : 'none';
        }

        if (preview.footerCounter && fields.footer_nota) {
            preview.footerCounter.textContent = String(fields.footer_nota.value.length);
        }
    }

    Object.values(fields).forEach((el) => {
        if (!el) return;
        el.addEventListener('input', syncPreview);
        el.addEventListener('change', syncPreview);
    });

    syncPreview();
})();
</script>

<?php
$content = ob_get_clean();
$title = 'Konfigurasi Format Nota - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
