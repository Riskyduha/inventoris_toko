<?php ob_start(); ?>

<?php 
$totalUser = count($users);
$totalAdmin = array_reduce($users, fn($c, $u) => $c + ($u['role'] === 'admin' ? 1 : 0), 0);
$totalRegularUser = $totalUser - $totalAdmin;
?>

<div class="container mx-auto px-4 py-6 space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen User</h1>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
                <p class="text-xs uppercase text-gray-500">Total User</p>
                <p class="text-xl font-bold text-gray-900"><?= $totalUser ?></p>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-blue-800">
                <p class="text-xs uppercase">Admin</p>
                <p class="text-lg font-bold"><?= $totalAdmin ?></p>
            </div>
            <div class="bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-green-800">
                <p class="text-xs uppercase">User</p>
                <p class="text-lg font-bold"><?= $totalRegularUser ?></p>
            </div>
            <a href="/users/create" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
                + Tambah User
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-6 py-4 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Daftar Akun</h2>
                <p class="text-sm text-gray-500">Kelola akun pengguna sistem inventori UD. Bersaudara.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" id="user-search" placeholder="Cari nama atau username..." onkeyup="filterUsers()"
                       class="px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none text-sm">
                <select id="role-filter" onchange="filterUsers()" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Role</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="kasir">Kasir</option>
                    <option value="inspeksi">Inspeksi</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-sm text-gray-600">
                        <th class="px-6 py-3 font-semibold">Username</th>
                        <th class="px-6 py-3 font-semibold">Nama</th>
                        <th class="px-6 py-3 font-semibold">Role</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="user-table" class="divide-y divide-gray-100 text-sm">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada user</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50" data-role="<?= htmlspecialchars($u['role']) ?>">
                                <td class="px-6 py-3 font-medium text-gray-900"><?= htmlspecialchars($u['username']) ?></td>
                                <td class="px-6 py-3 text-gray-700"><?= htmlspecialchars($u['nama']) ?></td>
                                <td class="px-6 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $u['role'] === 'admin' ? 'bg-blue-100 text-blue-800' : ($u['role'] === 'manager' ? 'bg-indigo-100 text-indigo-800' : ($u['role'] === 'inspeksi' ? 'bg-amber-100 text-amber-800' : 'bg-green-100 text-green-800')) ?>">
                                        <?= ucfirst($u['role']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right space-x-3">
                                    <a href="/users/edit/<?= $u['id_user'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold">Edit</a>
                                    <a href="/users/delete/<?= $u['id_user'] ?>" onclick="return confirm('Yakin hapus user ini?')" class="text-red-600 hover:text-red-800 font-semibold">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function filterUsers() {
    const term = (document.getElementById('user-search')?.value || '').toLowerCase();
    const role = (document.getElementById('role-filter')?.value || '').toLowerCase();
    document.querySelectorAll('#user-table tr[data-role]').forEach(row => {
        const matchText = row.innerText.toLowerCase();
        const rowRole = row.getAttribute('data-role').toLowerCase();
        const match = matchText.includes(term) && (!role || rowRole === role);
        row.classList.toggle('hidden', !match);
    });
}
</script>

<?php 
$content = ob_get_clean();
$title = 'Manajemen User - Sistem Inventori';
include __DIR__ . '/../layout/header.php';
?>
