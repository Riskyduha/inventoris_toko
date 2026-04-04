<?php ob_start(); ?>
<?php
$roleLabels = [
    'manager' => 'Manager',
    'kasir' => 'Kasir',
    'inspeksi' => 'Inspeksi',
];
$roleColors = [
    'manager' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'bar' => 'bg-indigo-500'],
    'kasir' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'bar' => 'bg-emerald-500'],
    'inspeksi' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'bar' => 'bg-amber-500'],
];

$roleTotals = [];
foreach ($roles as $role) {
    $roleTotals[$role] = 0;
}
foreach ($catalog as $group => $permissions) {
    foreach ($permissions as $perm) {
        foreach ($roles as $role) {
            $roleTotals[$role]++;
        }
    }
}

$flashSuccess = $_SESSION['success'] ?? null;
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>

<div class="max-w-7xl mx-auto px-4 space-y-6 app-reveal">
    <div id="permissionToastHost" class="fixed top-24 right-4 z-[120] space-y-2 pointer-events-none"></div>

    <div class="app-card p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengaturan Akses</p>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1 flex items-center gap-2">
                    <i class="fas fa-user-shield text-blue-600"></i>
                    Manajemen Role Permission
                </h1>
                <p class="text-slate-600 mt-2">Atur hak akses role secara dinamis untuk aplikasi.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                <i class="fas fa-lock"></i> Admin tetap akses penuh
            </span>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="app-card border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-check-circle"></i><?= htmlspecialchars((string)$flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="app-card border border-red-200 bg-red-50 text-red-700 px-4 py-3 rounded-xl flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i><?= htmlspecialchars((string)$flashError) ?>
        </div>
    <?php endif; ?>

    <form id="rolePermissionsForm" method="POST" action="/setting/role-permissions" class="space-y-5">
        <div class="app-card border border-slate-200 p-4 sm:p-5 md:sticky md:top-20 md:z-20 backdrop-blur bg-white/95 shadow-sm">
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-4">
                <div class="xl:col-span-4">
                    <label for="permissionSearch" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Permission</label>
                    <div class="relative mt-2">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input id="permissionSearch" type="text" placeholder="Cari modul, label, atau key permission..." class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-500 focus:outline-none">
                    </div>
                </div>
                <div class="xl:col-span-8 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    <?php foreach ($roles as $role): ?>
                        <?php
                            $style = $roleColors[$role] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'bar' => 'bg-slate-500'];
                        ?>
                        <div class="rounded-xl border border-slate-200 p-3 <?= $style['bg'] ?>">
                            <p class="text-[11px] uppercase tracking-wide font-semibold <?= $style['text'] ?>">
                                Aksi Cepat <?= htmlspecialchars($roleLabels[$role] ?? ucfirst($role)) ?>
                            </p>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <button type="button" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-300 bg-white text-slate-700 hover:bg-slate-100" onclick="toggleRolePermissions('<?= htmlspecialchars($role) ?>', true)">
                                    Pilih Semua
                                </button>
                                <button type="button" class="px-2.5 py-2 rounded-lg text-xs font-semibold border border-slate-300 bg-white text-slate-700 hover:bg-slate-100" onclick="toggleRolePermissions('<?= htmlspecialchars($role) ?>', false)">
                                    Reset Semua
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
                        <p class="text-[11px] uppercase tracking-wide font-semibold text-slate-700">Aksi Modul</p>
                        <div class="mt-2 grid grid-cols-1 gap-2">
                            <button type="button" class="px-3 py-2 rounded-lg text-xs font-semibold bg-slate-900 text-white hover:bg-slate-700" onclick="expandCollapseAllModules(true)">
                                Buka Semua Modul
                            </button>
                            <button type="button" class="px-3 py-2 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-100" onclick="expandCollapseAllModules(false)">
                                Tutup Semua Modul
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($roles as $role): ?>
                <?php
                    $selectedCount = count($currentPermissions[$role] ?? []);
                    $totalCount = (int)($roleTotals[$role] ?? 0);
                    $percent = $totalCount > 0 ? (int)round(($selectedCount / $totalCount) * 100) : 0;
                    $style = $roleColors[$role] ?? ['bg' => 'bg-slate-50', 'text' => 'text-slate-700', 'bar' => 'bg-slate-500'];
                ?>
                <div class="app-card border border-slate-200 p-4 <?= $style['bg'] ?>">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <p class="text-sm font-bold <?= $style['text'] ?>"><?= htmlspecialchars($roleLabels[$role] ?? ucfirst($role)) ?></p>
                        <span id="roleCount_<?= htmlspecialchars($role) ?>" class="text-xs font-semibold <?= $style['text'] ?>"><?= $selectedCount ?> / <?= $totalCount ?></span>
                    </div>
                    <div class="h-2 rounded-full bg-white/70 overflow-hidden border border-white/40">
                        <div id="roleBar_<?= htmlspecialchars($role) ?>" class="h-full <?= $style['bar'] ?> transition-all duration-300" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($catalog as $group => $permissions): ?>
            <?php
                $moduleKey = strtolower((string)$group);
                $moduleKey = preg_replace('/[^a-z0-9_\-]/', '_', $moduleKey);
            ?>
            <section class="permission-module app-card border border-slate-200 overflow-hidden" data-module="<?= htmlspecialchars($moduleKey) ?>">
                <header class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button type="button" class="w-7 h-7 rounded-lg border border-slate-300 bg-white text-slate-600 hover:bg-slate-100" onclick="toggleModuleBody('<?= htmlspecialchars($moduleKey) ?>')" aria-label="Toggle modul">
                                <i id="moduleIcon_<?= htmlspecialchars($moduleKey) ?>" class="fas fa-chevron-down text-xs"></i>
                            </button>
                            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700">Modul <?= htmlspecialchars($group) ?></h2>
                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-white border border-slate-200 text-slate-500"><?= count($permissions) ?> permission</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2">
                            <?php foreach ($roles as $role): ?>
                                <button type="button" class="px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-100" onclick="toggleModuleRole('<?= htmlspecialchars($moduleKey) ?>', '<?= htmlspecialchars($role) ?>', true)">Pilih <?= htmlspecialchars($roleLabels[$role] ?? ucfirst($role)) ?></button>
                                <button type="button" class="px-2.5 py-1.5 rounded-lg text-[11px] font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-100" onclick="toggleModuleRole('<?= htmlspecialchars($moduleKey) ?>', '<?= htmlspecialchars($role) ?>', false)">Reset <?= htmlspecialchars($roleLabels[$role] ?? ucfirst($role)) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </header>

                <div id="moduleBody_<?= htmlspecialchars($moduleKey) ?>" class="module-body overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-white border-b border-slate-200 sticky top-0 z-10">
                            <tr>
                                <th class="px-4 py-3 text-left text-slate-600 font-semibold">Permission</th>
                                <?php foreach ($roles as $role): ?>
                                    <th class="px-4 py-3 text-center text-slate-600 font-semibold"><?= htmlspecialchars($roleLabels[$role] ?? ucfirst($role)) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <?php foreach ($permissions as $perm): ?>
                                <?php $permKey = (string)($perm['key'] ?? ''); ?>
                                <?php $searchBlob = strtolower(trim(($perm['label'] ?? $permKey) . ' ' . $permKey . ' ' . $group)); ?>
                                <tr class="permission-row hover:bg-slate-50" data-module="<?= htmlspecialchars($moduleKey) ?>" data-search="<?= htmlspecialchars($searchBlob) ?>">
                                    <td class="px-4 py-3">
                                        <p class="font-semibold text-slate-800"><?= htmlspecialchars($perm['label'] ?? $permKey) ?></p>
                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($permKey) ?></p>
                                    </td>
                                    <?php foreach ($roles as $role): ?>
                                        <?php $isChecked = in_array($permKey, $currentPermissions[$role] ?? [], true); ?>
                                        <td class="px-4 py-3 text-center">
                                            <label class="inline-flex items-center justify-center cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    class="role-perm-checkbox h-4 w-4 rounded border-slate-300 text-teal-600"
                                                    data-role="<?= htmlspecialchars($role) ?>"
                                                    data-module="<?= htmlspecialchars($moduleKey) ?>"
                                                    name="permissions[<?= htmlspecialchars($role) ?>][]"
                                                    value="<?= htmlspecialchars($permKey) ?>"
                                                    <?= $isChecked ? 'checked' : '' ?>
                                                >
                                            </label>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endforeach; ?>

        <div class="app-card border border-slate-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 md:sticky md:bottom-4 md:z-20 bg-white/95 backdrop-blur">
            <span id="permissionDirtyIndicator" class="hidden inline-flex items-center gap-2 rounded-full bg-amber-50 border border-amber-200 px-3 py-1 text-xs font-semibold text-amber-700">
                <i class="fas fa-pen"></i> Perubahan belum disimpan
            </span>
            <span id="permissionSavedIndicator" class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200 px-3 py-1 text-xs font-semibold text-emerald-700">
                <i class="fas fa-check"></i> Semua perubahan tersimpan
            </span>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <a href="/" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 transition font-semibold">
                    <i class="fas fa-arrow-left"></i>
                    Kembali
                </a>
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white transition font-semibold shadow-sm">
                    <i class="fas fa-save"></i>
                    Simpan Permission
                </button>
            </div>
        </div>
    </form>
</div>

<script>
let permissionDirty = false;

function toggleRolePermissions(role, checked) {
    document.querySelectorAll('.role-perm-checkbox[data-role="' + role + '"]').forEach((checkbox) => {
        checkbox.checked = !!checked;
    });
    markPermissionDirty();
    updateRoleStats();
}

function toggleModuleRole(module, role, checked) {
    document.querySelectorAll('.role-perm-checkbox[data-module="' + module + '"][data-role="' + role + '"]').forEach((checkbox) => {
        checkbox.checked = !!checked;
    });
    markPermissionDirty();
    updateRoleStats();
}

function toggleModuleBody(module) {
    const body = document.getElementById('moduleBody_' + module);
    const icon = document.getElementById('moduleIcon_' + module);
    if (!body || !icon) return;
    const isHidden = body.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down', !isHidden);
    icon.classList.toggle('fa-chevron-right', isHidden);
}

function expandCollapseAllModules(expand) {
    document.querySelectorAll('.permission-module').forEach((section) => {
        const module = section.getAttribute('data-module');
        const body = document.getElementById('moduleBody_' + module);
        const icon = document.getElementById('moduleIcon_' + module);
        if (!body || !icon) return;
        body.classList.toggle('hidden', !expand);
        icon.classList.toggle('fa-chevron-down', expand);
        icon.classList.toggle('fa-chevron-right', !expand);
    });
}

function updateRoleStats() {
    const totals = {};
    const checked = {};
    document.querySelectorAll('.role-perm-checkbox').forEach((checkbox) => {
        const role = checkbox.getAttribute('data-role');
        if (!totals[role]) {
            totals[role] = 0;
            checked[role] = 0;
        }
        totals[role] += 1;
        if (checkbox.checked) checked[role] += 1;
    });

    Object.keys(totals).forEach((role) => {
        const countEl = document.getElementById('roleCount_' + role);
        const barEl = document.getElementById('roleBar_' + role);
        const total = totals[role] || 0;
        const value = checked[role] || 0;
        const percent = total > 0 ? Math.round((value / total) * 100) : 0;
        if (countEl) countEl.textContent = value + ' / ' + total;
        if (barEl) barEl.style.width = percent + '%';
    });
}

function filterPermissionRows(term) {
    const query = (term || '').trim().toLowerCase();
    const modules = {};

    document.querySelectorAll('.permission-row').forEach((row) => {
        const module = row.getAttribute('data-module') || '';
        const search = (row.getAttribute('data-search') || '').toLowerCase();
        const isVisible = !query || search.includes(query);
        row.classList.toggle('hidden', !isVisible);

        if (!modules[module]) modules[module] = 0;
        if (isVisible) modules[module] += 1;
    });

    document.querySelectorAll('.permission-module').forEach((section) => {
        const module = section.getAttribute('data-module') || '';
        const visibleRows = modules[module] || 0;
        section.classList.toggle('hidden', visibleRows === 0);
    });
}

function markPermissionDirty() {
    permissionDirty = true;
    const dirty = document.getElementById('permissionDirtyIndicator');
    const saved = document.getElementById('permissionSavedIndicator');
    if (dirty) dirty.classList.remove('hidden');
    if (saved) saved.classList.add('hidden');
}

function markPermissionSaved() {
    permissionDirty = false;
    const dirty = document.getElementById('permissionDirtyIndicator');
    const saved = document.getElementById('permissionSavedIndicator');
    if (dirty) dirty.classList.add('hidden');
    if (saved) saved.classList.remove('hidden');
}

document.querySelectorAll('.role-perm-checkbox').forEach((checkbox) => {
    checkbox.addEventListener('change', () => {
        markPermissionDirty();
        updateRoleStats();
    });
});

document.getElementById('permissionSearch')?.addEventListener('input', function () {
    filterPermissionRows(this.value || '');
});

document.getElementById('rolePermissionsForm')?.addEventListener('submit', function () {
    markPermissionSaved();
});

window.addEventListener('beforeunload', function (event) {
    if (!permissionDirty) return;
    event.preventDefault();
    event.returnValue = '';
});

updateRoleStats();

document.addEventListener('DOMContentLoaded', function () {
    const flashSuccess = <?= json_encode($flashSuccess, JSON_UNESCAPED_UNICODE) ?>;
    const flashError = <?= json_encode($flashError, JSON_UNESCAPED_UNICODE) ?>;
    const toastHost = document.getElementById('permissionToastHost');

    function showToast(message, type) {
        if (!toastHost || !message) return;

        const isSuccess = type === 'success';
        const toast = document.createElement('div');
        toast.className = [
            'pointer-events-auto',
            'w-[min(92vw,360px)]',
            'rounded-xl',
            'border',
            'shadow-lg',
            'px-4',
            'py-3',
            'backdrop-blur',
            'transition-all',
            'duration-300',
            'translate-y-2',
            'opacity-0',
            isSuccess ? 'bg-emerald-50/95 border-emerald-200 text-emerald-800' : 'bg-red-50/95 border-red-200 text-red-800'
        ].join(' ');

        const iconClass = isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle';
        toast.innerHTML = `
            <div class="flex items-start gap-3">
                <i class="fas ${iconClass} mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-semibold">${isSuccess ? 'Berhasil' : 'Gagal'}</p>
                    <p class="text-sm leading-relaxed">${String(message)}</p>
                </div>
                <button type="button" class="text-xs font-bold opacity-70 hover:opacity-100" aria-label="Tutup">&times;</button>
            </div>
        `;

        const closeBtn = toast.querySelector('button');
        const removeToast = () => {
            toast.classList.add('opacity-0', 'translate-y-2');
            setTimeout(() => toast.remove(), 250);
        };

        closeBtn?.addEventListener('click', removeToast);
        toastHost.appendChild(toast);

        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', 'translate-y-2');
        });

        setTimeout(removeToast, 4500);
    }

    if (flashSuccess) {
        showToast(flashSuccess, 'success');
    } else if (flashError) {
        showToast(flashError, 'error');
    }
});
</script>

<?php
$content = ob_get_clean();
$title = 'Manajemen Role Permission - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
