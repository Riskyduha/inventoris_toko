<?php
ob_start();
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/setting/kategori-satuan', PHP_URL_PATH) ?? '/setting/kategori-satuan';
$settingBasePath = '/setting';
if (preg_match('#^(.*?/setting)(?:/.*)?$#', $requestPath, $matches)) {
    $settingBasePath = rtrim($matches[1], '/');
}
?>

<div class="max-w-7xl mx-auto px-4 space-y-6 app-reveal">
    <div class="app-card p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Master Data</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1 flex items-center gap-2">
                    <i class="fas fa-tags text-blue-600"></i>
                    Manajemen Kategori & Satuan
                </h1>
                <p class="text-slate-600 mt-2">Kelola referensi kategori dan satuan agar input stok lebih rapi dan konsisten.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 min-w-[260px]">
                <div class="app-card p-3 border border-blue-200 bg-blue-50 text-blue-700">
                    <p class="text-xs uppercase tracking-wide">Kategori</p>
                    <p class="text-2xl font-bold leading-none mt-1"><?= count($kategori) ?></p>
                </div>
                <div class="app-card p-3 border border-emerald-200 bg-emerald-50 text-emerald-700">
                    <p class="text-xs uppercase tracking-wide">Satuan</p>
                    <p class="text-2xl font-bold leading-none mt-1"><?= count($satuan) ?></p>
                </div>
            </div>
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

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="app-card border border-slate-200 p-5 sm:p-6 space-y-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-slate-800">Kategori Produk</h2>
                <span class="text-xs px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 font-semibold"><?= count($kategori) ?> data</span>
            </div>

            <form method="POST" action="<?= htmlspecialchars($settingBasePath) ?>/kategori/add" class="space-y-2">
                <label for="nama_kategori" class="text-sm font-semibold text-slate-700">Tambah Kategori</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="nama_kategori" name="nama_kategori" placeholder="Contoh: Minuman, Snack, Elektronik" class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition">
                        <i class="fas fa-plus"></i>Tambah
                    </button>
                </div>
            </form>

            <div class="space-y-3 border-t border-slate-200 pt-4">
                <div class="relative">
                    <i class="fas fa-search text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-sm"></i>
                    <input type="text" id="searchKategori" placeholder="Cari kategori..." class="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div id="kategori-list" class="space-y-2 max-h-[400px] overflow-auto pr-1">
                    <?php if (empty($kategori)): ?>
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-slate-500 text-sm">
                            <i class="fas fa-folder-open text-2xl mb-2"></i>
                            <p>Belum ada kategori</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($kategori as $item): ?>
                            <div class="item-row flex items-center justify-between gap-3 rounded-xl border border-blue-100 bg-gradient-to-r from-blue-50 to-white px-3 py-2.5" data-search="<?= htmlspecialchars(strtolower($item['nama_kategori'] ?? '')) ?>">
                                <div>
                                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($item['nama_kategori']) ?></p>
                                    <p class="text-[11px] text-slate-500">Kategori</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-xs px-3 py-1.5 rounded-lg border border-blue-200 text-blue-700 bg-white hover:bg-blue-50" onclick="editItem('kategori', <?= $item['id_kategori'] ?>, '<?= htmlspecialchars($item['nama_kategori'], ENT_QUOTES) ?>')">Edit</button>
                                    <button type="button" class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100" onclick="deleteItem('kategori', <?= $item['id_kategori'] ?>, '<?= htmlspecialchars($item['nama_kategori'], ENT_QUOTES) ?>')">Hapus</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <p id="kategori-empty-filter" class="hidden text-sm text-slate-500 text-center py-4">Kategori tidak ditemukan.</p>
            </div>
        </section>

        <section class="app-card border border-slate-200 p-5 sm:p-6 space-y-5">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-xl font-bold text-slate-800">Satuan Produk</h2>
                <span class="text-xs px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold"><?= count($satuan) ?> data</span>
            </div>

            <form method="POST" action="<?= htmlspecialchars($settingBasePath) ?>/satuan/add" class="space-y-2">
                <label for="nama_satuan" class="text-sm font-semibold text-slate-700">Tambah Satuan</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="nama_satuan" name="nama_satuan" placeholder="Contoh: pcs, box, kg, liter" class="flex-1 px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition">
                        <i class="fas fa-plus"></i>Tambah
                    </button>
                </div>
            </form>

            <div class="space-y-3 border-t border-slate-200 pt-4">
                <div class="relative">
                    <i class="fas fa-search text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-sm"></i>
                    <input type="text" id="searchSatuan" placeholder="Cari satuan..." class="w-full pl-9 pr-3 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div id="satuan-list" class="space-y-2 max-h-[400px] overflow-auto pr-1">
                    <?php if (empty($satuan)): ?>
                        <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-8 text-center text-slate-500 text-sm">
                            <i class="fas fa-ruler text-2xl mb-2"></i>
                            <p>Belum ada satuan</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($satuan as $item): ?>
                            <div class="item-row flex items-center justify-between gap-3 rounded-xl border border-emerald-100 bg-gradient-to-r from-emerald-50 to-white px-3 py-2.5" data-search="<?= htmlspecialchars(strtolower($item['nama_satuan'] ?? '')) ?>">
                                <div>
                                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($item['nama_satuan']) ?></p>
                                    <p class="text-[11px] text-slate-500">Satuan</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="text-xs px-3 py-1.5 rounded-lg border border-emerald-200 text-emerald-700 bg-white hover:bg-emerald-50" onclick="editItem('satuan', <?= $item['id_satuan'] ?>, '<?= htmlspecialchars($item['nama_satuan'], ENT_QUOTES) ?>')">Edit</button>
                                    <button type="button" class="text-xs px-3 py-1.5 rounded-lg border border-red-200 text-red-700 bg-red-50 hover:bg-red-100" onclick="deleteItem('satuan', <?= $item['id_satuan'] ?>, '<?= htmlspecialchars($item['nama_satuan'], ENT_QUOTES) ?>')">Hapus</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <p id="satuan-empty-filter" class="hidden text-sm text-slate-500 text-center py-4">Satuan tidak ditemukan.</p>
            </div>
        </section>
    </div>

    <div class="app-card border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
        <p class="font-semibold text-blue-900 mb-1">Tips:</p>
        <p>Gunakan nama singkat dan jelas, hindari duplikasi, serta jaga konsistensi penulisan agar laporan lebih rapi.</p>
    </div>
</div>

<div id="manageModal" class="hidden fixed inset-0 z-[100] bg-slate-900/40 backdrop-blur-[1px]">
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-2xl">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 id="manageModalTitle" class="text-lg font-bold text-slate-800">Ubah Data</h3>
                <p id="manageModalDesc" class="text-sm text-slate-500 mt-1"></p>
            </div>
            <div class="px-5 py-4">
                <input id="manageModalInput" type="text" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2">
                <button id="manageModalCancel" type="button" class="px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100">Batal</button>
                <button id="manageModalAction" type="button" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
const settingBasePath = <?= json_encode($settingBasePath) ?>;
let modalActionHandler = null;

function settingUrl(endpoint) {
    const base = (settingBasePath || '/setting').replace(/\/+$/, '');
    const path = String(endpoint || '').replace(/^\/+/, '');
    return base + '/' + path;
}

function submitHiddenForm(action, payload) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;
    Object.keys(payload).forEach((key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = payload[key];
        form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
}

function openManageModal(options) {
    const modal = document.getElementById('manageModal');
    const titleEl = document.getElementById('manageModalTitle');
    const descEl = document.getElementById('manageModalDesc');
    const inputEl = document.getElementById('manageModalInput');
    const actionBtn = document.getElementById('manageModalAction');
    if (!modal || !titleEl || !descEl || !actionBtn || !inputEl) return;

    titleEl.textContent = options.title || 'Konfirmasi';
    descEl.textContent = options.description || '';
    inputEl.value = options.inputValue || '';
    inputEl.classList.toggle('hidden', !options.showInput);
    actionBtn.textContent = options.actionLabel || 'Simpan';
    actionBtn.className = 'px-4 py-2 rounded-lg text-white ' + (options.actionClass || 'bg-blue-600 hover:bg-blue-700');
    modalActionHandler = options.onConfirm || null;
    modal.classList.remove('hidden');
    if (options.showInput) {
        setTimeout(() => inputEl.focus(), 50);
    }
}

function closeManageModal() {
    const modal = document.getElementById('manageModal');
    if (modal) modal.classList.add('hidden');
    modalActionHandler = null;
}

function editItem(type, id, currentName) {
    const label = type === 'kategori' ? 'kategori' : 'satuan';
    openManageModal({
        title: 'Edit ' + (type === 'kategori' ? 'Kategori' : 'Satuan'),
        description: 'Perbarui nama ' + label + ' lalu simpan perubahan.',
        showInput: true,
        inputValue: currentName || '',
        actionLabel: 'Simpan',
        actionClass: 'bg-blue-600 hover:bg-blue-700',
        onConfirm: () => {
            const inputEl = document.getElementById('manageModalInput');
            const trimmed = (inputEl?.value || '').trim();
            if (!trimmed) {
                alert('Nama tidak boleh kosong.');
                return;
            }
            if (type === 'kategori') {
                submitHiddenForm(settingUrl('kategori/update'), { id_kategori: id, nama_kategori: trimmed });
            } else {
                submitHiddenForm(settingUrl('satuan/update'), { id_satuan: id, nama_satuan: trimmed });
            }
        }
    });
}

function deleteItem(type, id, name) {
    const label = type === 'kategori' ? 'kategori' : 'satuan';
    openManageModal({
        title: 'Hapus ' + (type === 'kategori' ? 'Kategori' : 'Satuan'),
        description: 'Yakin ingin menghapus ' + label + ' "' + (name || '') + '"?',
        showInput: false,
        actionLabel: 'Hapus',
        actionClass: 'bg-red-600 hover:bg-red-700',
        onConfirm: () => {
            if (type === 'kategori') {
                submitHiddenForm(settingUrl('kategori/delete'), { id_kategori: id });
            } else {
                submitHiddenForm(settingUrl('satuan/delete'), { id_satuan: id });
            }
        }
    });
}

function bindFilter(inputId, listId, emptyId) {
    const input = document.getElementById(inputId);
    const list = document.getElementById(listId);
    const empty = document.getElementById(emptyId);
    if (!input || !list || !empty) return;

    const items = Array.from(list.querySelectorAll('.item-row'));
    if (items.length === 0) return;

    input.addEventListener('input', function () {
        const term = (input.value || '').toLowerCase().trim();
        let visible = 0;
        items.forEach((item) => {
            const matched = !term || (item.dataset.search || '').includes(term);
            item.classList.toggle('hidden', !matched);
            if (matched) visible += 1;
        });
        empty.classList.toggle('hidden', visible > 0);
    });
}

bindFilter('searchKategori', 'kategori-list', 'kategori-empty-filter');
bindFilter('searchSatuan', 'satuan-list', 'satuan-empty-filter');

document.getElementById('manageModalCancel')?.addEventListener('click', closeManageModal);
document.getElementById('manageModalAction')?.addEventListener('click', function () {
    if (typeof modalActionHandler === 'function') {
        modalActionHandler();
    }
});
document.getElementById('manageModal')?.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'manageModal') {
        closeManageModal();
    }
});
</script>

<?php
$content = ob_get_clean();
$title = 'Kategori & Satuan - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
