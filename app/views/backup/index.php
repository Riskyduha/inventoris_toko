<?php ob_start(); ?>

<div class="app-card p-6 app-reveal">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800"><i class="fas fa-database text-teal-600 mr-2"></i>Backup & Restore</h2>
            <p class="text-sm text-slate-500 mt-1">Auto-backup harian aktif. Lakukan backup manual sebelum perubahan besar.</p>
        </div>
        <a href="/backup/create" class="app-btn-primary px-4 py-2 font-semibold">
            <i class="fas fa-plus mr-2"></i>Buat Backup Sekarang
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full bg-white">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase">File</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase">Ukuran</th>
                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-600 uppercase">Waktu</th>
                    <th class="px-4 py-3 text-center text-xs font-bold text-slate-600 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($backups)): ?>
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400 italic">Belum ada file backup</td></tr>
                <?php else: ?>
                    <?php foreach ($backups as $b): ?>
                        <tr>
                            <td class="px-4 py-3 font-mono text-sm text-slate-700"><?= htmlspecialchars($b['name']) ?></td>
                            <td class="px-4 py-3 text-sm text-slate-600"><?= number_format(($b['size'] ?? 0) / 1024, 1, ',', '.') ?> KB</td>
                            <td class="px-4 py-3 text-sm text-slate-600"><?= htmlspecialchars($b['modified']) ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="/backup/download?file=<?= rawurlencode($b['name']) ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100">Download</a>
                                    <form method="POST" action="/backup/restore" onsubmit="return confirm('Restore database dari backup ini? Pastikan sudah backup data terbaru.');">
                                        <input type="hidden" name="file" value="<?= htmlspecialchars($b['name']) ?>">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100">Restore</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Backup & Restore - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
