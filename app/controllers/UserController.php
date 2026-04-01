<?php

class UserController {
    private $userModel;

    public function __construct() {
        require_once BASE_PATH . '/app/models/User.php';
        $this->userModel = new User();
    }

    // List semua user
    public function index() {
        // Hanya admin yang bisa akses
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
            header('Location: /');
            exit;
        }

        $users = $this->userModel->getAllUsers();
        
        // Urutkan admin di atas
        usort($users, function($a, $b) {
            if ($a['role'] === 'admin' && $b['role'] !== 'admin') {
                return -1;
            } elseif ($a['role'] !== 'admin' && $b['role'] === 'admin') {
                return 1;
            }
            return 0;
        });
        
        ob_start();
        ?>
        <?php
        $totalUsers = count($users);
        $totalAdmin = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
        $totalKasir = count(array_filter($users, fn($u) => $u['role'] === 'kasir'));
        $totalInspeksi = count(array_filter($users, fn($u) => $u['role'] === 'inspeksi'));
        ?>
        <div class="max-w-7xl mx-auto px-4 space-y-6 app-reveal">
            <div class="app-card p-5 sm:p-6">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Akses Pengguna</p>
                        <h1 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1 flex items-center gap-2">
                            <i class="fas fa-users text-blue-600"></i>
                            Manajemen Pengguna
                        </h1>
                        <p class="text-slate-600 mt-2">Kelola akun, role, dan keamanan akses sistem.</p>
                    </div>
                    <a href="/user/create" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm transition">
                        <i class="fas fa-plus-circle"></i>
                        Tambah Pengguna
                    </a>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="app-card p-4 border border-blue-200 bg-blue-50">
                    <p class="text-xs uppercase tracking-wide font-semibold text-blue-700">Total Pengguna</p>
                    <p class="text-2xl font-bold text-blue-800 mt-1"><?= $totalUsers ?></p>
                </div>
                <div class="app-card p-4 border border-red-200 bg-red-50">
                    <p class="text-xs uppercase tracking-wide font-semibold text-red-700">Admin</p>
                    <p class="text-2xl font-bold text-red-800 mt-1"><?= $totalAdmin ?></p>
                </div>
                <div class="app-card p-4 border border-emerald-200 bg-emerald-50">
                    <p class="text-xs uppercase tracking-wide font-semibold text-emerald-700">Kasir</p>
                    <p class="text-2xl font-bold text-emerald-800 mt-1"><?= $totalKasir ?></p>
                </div>
                <div class="app-card p-4 border border-amber-200 bg-amber-50">
                    <p class="text-xs uppercase tracking-wide font-semibold text-amber-700">Inspeksi</p>
                    <p class="text-2xl font-bold text-amber-800 mt-1"><?= $totalInspeksi ?></p>
                </div>
            </div>

            <div class="app-card border border-slate-200 overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="relative w-full md:max-w-xs">
                            <i class="fas fa-search text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-sm"></i>
                            <input id="searchUserInput" type="text" placeholder="Cari username / nama..." class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>
                        <select id="roleFilterUser" class="w-full md:w-52 px-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            <option value="all">Semua Role</option>
                            <option value="admin">Admin</option>
                            <option value="kasir">Kasir</option>
                            <option value="inspeksi">Inspeksi</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px]">
                        <thead>
                            <tr class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">No</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Akun</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Nama Lengkap</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Role</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="divide-y divide-slate-200 bg-white">
                            <?php if (empty($users)): ?>
                                <tr id="emptyStaticRow">
                                    <td colspan="5" class="px-6 py-12 text-center">
                                        <i class="fas fa-user-slash text-4xl text-slate-300 mb-2"></i>
                                        <p class="font-semibold text-slate-600">Belum ada pengguna</p>
                                        <p class="text-sm text-slate-500 mt-1">Klik tombol <strong>Tambah Pengguna</strong> untuk mulai menambahkan akun.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $index => $user):
                                    $isAdmin = $user['role'] === 'admin';
                                    $isSelf = $user['id_user'] === $_SESSION['user_id'];
                                ?>
                                    <tr class="hover:bg-slate-50 transition user-row"
                                        data-role="<?= htmlspecialchars($user['role']) ?>"
                                        data-search="<?= htmlspecialchars(strtolower(trim(($user['username'] ?? '') . ' ' . ($user['nama'] ?? '')))) ?>">
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs"><?= $index + 1 ?></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full <?= $isAdmin ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?>">
                                                    <i class="fas <?= $isAdmin ? 'fa-shield-alt' : 'fa-user' ?>"></i>
                                                </span>
                                                <div>
                                                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($user['username']) ?></p>
                                                    <?php if ($isSelf): ?>
                                                        <span class="inline-flex items-center gap-1 mt-1 text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">
                                                            <i class="fas fa-user-check"></i>Akun Anda
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-slate-700"><?= htmlspecialchars($user['nama']) ?></td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold <?= $isAdmin ? 'bg-red-100 text-red-700' : ($user['role'] === 'kasir' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') ?>">
                                                <i class="fas <?= $isAdmin ? 'fa-crown' : ($user['role'] === 'kasir' ? 'fa-cash-register' : 'fa-clipboard-check') ?>"></i>
                                                <?= ucfirst($user['role']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-2">
                                                <a href="/user/edit/<?= $user['id_user'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-blue-200 text-blue-700 bg-blue-50 hover:bg-blue-100 transition" title="Edit Pengguna">
                                                    <i class="fas fa-edit"></i>Edit
                                                </a>
                                                <a href="/user/reset-password/<?= $user['id_user'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 transition" title="Reset Password">
                                                    <i class="fas fa-key"></i>Reset
                                                </a>
                                                <?php if (!$isSelf): ?>
                                                    <a href="/user/delete/<?= $user['id_user'] ?>" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-red-200 text-red-700 bg-red-50 hover:bg-red-100 transition" title="Hapus Pengguna" onclick="return confirm('Yakin ingin menghapus pengguna <?= htmlspecialchars($user['nama']) ?>?')">
                                                        <i class="fas fa-trash-alt"></i>Hapus
                                                    </a>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-slate-200 text-slate-400 bg-slate-100 cursor-not-allowed" title="Akun sendiri tidak bisa dihapus">
                                                        <i class="fas fa-lock"></i>Terkunci
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr id="emptyFilterRow" class="hidden">
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <i class="fas fa-search text-3xl text-slate-300 mb-2"></i>
                                    <p class="font-semibold text-slate-600">Pengguna tidak ditemukan</p>
                                    <p class="text-sm text-slate-500 mt-1">Ubah kata kunci pencarian atau filter role.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-sm text-slate-500">Total pengguna saat ini: <span class="font-semibold text-slate-700"><?= $totalUsers ?></span></p>
        </div>

        <script>
        (function () {
            const searchInput = document.getElementById('searchUserInput');
            const roleFilter = document.getElementById('roleFilterUser');
            const rows = Array.from(document.querySelectorAll('.user-row'));
            const emptyFilterRow = document.getElementById('emptyFilterRow');
            if (!searchInput || !roleFilter || rows.length === 0 || !emptyFilterRow) return;

            function applyFilter() {
                const term = (searchInput.value || '').toLowerCase().trim();
                const role = roleFilter.value;
                let visible = 0;
                rows.forEach((row) => {
                    const matchesRole = role === 'all' || row.dataset.role === role;
                    const matchesTerm = !term || (row.dataset.search || '').includes(term);
                    const isShow = matchesRole && matchesTerm;
                    row.classList.toggle('hidden', !isShow);
                    if (isShow) visible += 1;
                });
                emptyFilterRow.classList.toggle('hidden', visible > 0);
            }

            searchInput.addEventListener('input', applyFilter);
            roleFilter.addEventListener('change', applyFilter);
        })();
        </script>
        <?php
        $content = ob_get_clean();
        $title = 'Manajemen Pengguna - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Form create user
    public function create() {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman ini';
            header('Location: /');
            exit;
        }

        ob_start();
        ?>
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-user-plus text-blue-600 mr-2"></i>Tambah Pengguna Baru
                </h2>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/user/store" method="POST">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-semibold mb-2">
                            Username <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="username" name="username" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                        <small class="text-gray-500">Gunakan untuk login</small>
                    </div>

                    <div class="mb-4">
                        <label for="nama" class="block text-gray-700 font-semibold mb-2">
                            Nama Lengkap <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 font-semibold mb-2">
                            Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="password" name="password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                        <small class="text-gray-500">Minimal 6 karakter</small>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">
                            Konfirmasi Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="role" class="block text-gray-700 font-semibold mb-2">
                            Role <span class="text-red-600">*</span>
                        </label>
                        <select id="role" name="role" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                required>
                            <option value="">-- Pilih Role --</option>
                            <option value="kasir">Kasir</option>
                            <option value="inspeksi">Inspeksi</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>Simpan Pengguna
                        </button>
                        <a href="/user" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Tambah Pengguna - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Store user baru
    public function store() {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/create');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? '';

        // Validasi
        if (empty($username) || empty($nama) || empty($password) || empty($role)) {
            $_SESSION['error'] = 'Semua field harus diisi';
            header('Location: /user/create');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter';
            header('Location: /user/create');
            exit;
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = 'Password tidak cocok';
            header('Location: /user/create');
            exit;
        }

        if ($this->userModel->usernameExists($username)) {
            $_SESSION['error'] = 'Username sudah digunakan';
            header('Location: /user/create');
            exit;
        }

        if (!in_array($role, ['admin', 'kasir', 'inspeksi'], true)) {
            $_SESSION['error'] = 'Role tidak valid';
            header('Location: /user/create');
            exit;
        }

        if ($this->userModel->createUser($username, $password, $nama, $role)) {
            $_SESSION['success'] = 'Pengguna berhasil ditambahkan';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal menambahkan pengguna';
            header('Location: /user/create');
            exit;
        }
    }

    // Form edit user
    public function edit($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /');
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        ob_start();
        ?>
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-user-edit text-blue-600 mr-2"></i>Edit Pengguna
                </h2>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/user/update/<?= $user['id_user'] ?>" method="POST">
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 font-semibold mb-2">
                            Username <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="username" name="username" 
                               value="<?= htmlspecialchars($user['username']) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-4">
                        <label for="nama" class="block text-gray-700 font-semibold mb-2">
                            Nama Lengkap <span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nama" name="nama" 
                               value="<?= htmlspecialchars($user['nama']) ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="mb-6">
                        <label for="role" class="block text-gray-700 font-semibold mb-2">
                            Role <span class="text-red-600">*</span>
                        </label>
                        <select id="role" name="role" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                required>
                            <option value="kasir" <?= $user['role'] === 'kasir' ? 'selected' : '' ?>>Kasir</option>
                            <option value="inspeksi" <?= $user['role'] === 'inspeksi' ? 'selected' : '' ?>>Inspeksi</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>Update Pengguna
                        </button>
                        <a href="/user" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Edit Pengguna - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Update user
    public function update($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/edit/' . $id);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $role = $_POST['role'] ?? '';

        if (empty($username) || empty($nama) || empty($role)) {
            $_SESSION['error'] = 'Semua field harus diisi';
            header('Location: /user/edit/' . $id);
            exit;
        }

        if ($this->userModel->usernameExists($username, $id)) {
            $_SESSION['error'] = 'Username sudah digunakan oleh pengguna lain';
            header('Location: /user/edit/' . $id);
            exit;
        }

        if (!in_array($role, ['admin', 'kasir', 'inspeksi'], true)) {
            $_SESSION['error'] = 'Role tidak valid';
            header('Location: /user/edit/' . $id);
            exit;
        }

        if ($this->userModel->updateUser($id, $username, $nama, $role)) {
            $_SESSION['success'] = 'Pengguna berhasil diupdate';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal update pengguna';
            header('Location: /user/edit/' . $id);
            exit;
        }
    }

    // Form reset password
    public function resetPasswordForm($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /');
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        ob_start();
        ?>
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">
                    <i class="fas fa-key text-blue-600 mr-2"></i>Reset Password
                </h2>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-gray-700"><strong>Pengguna:</strong> <?= htmlspecialchars($user['nama']) ?> (<?= htmlspecialchars($user['username']) ?>)</p>
                </div>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="/user/update-password/<?= $user['id_user'] ?>" method="POST">
                    <div class="mb-4">
                        <label for="new_password" class="block text-gray-700 font-semibold mb-2">
                            Password Baru <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="new_password" name="new_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                        <small class="text-gray-500">Minimal 6 karakter</small>
                    </div>

                    <div class="mb-6">
                        <label for="confirm_password" class="block text-gray-700 font-semibold mb-2">
                            Konfirmasi Password <span class="text-red-600">*</span>
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-key"></i>Reset Password
                        </button>
                        <a href="/user" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition font-semibold flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left"></i>Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        $title = 'Reset Password - Sistem Inventori';
        include __DIR__ . '/../views/layout/header.php';
    }

    // Update password
    public function updatePassword($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Pengguna tidak ditemukan';
            header('Location: /user');
            exit;
        }

        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($new_password)) {
            $_SESSION['error'] = 'Password tidak boleh kosong';
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        if (strlen($new_password) < 6) {
            $_SESSION['error'] = 'Password minimal 6 karakter';
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'Password tidak cocok';
            header('Location: /user/reset-password/' . $id);
            exit;
        }

        if ($this->userModel->resetPassword($id, $new_password)) {
            $_SESSION['success'] = 'Password pengguna berhasil direset';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal reset password';
            header('Location: /user/reset-password/' . $id);
            exit;
        }
    }

    // Delete user
    public function delete($id) {
        if ($_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Anda tidak memiliki akses';
            header('Location: /user');
            exit;
        }

        if ($id === $_SESSION['user_id']) {
            $_SESSION['error'] = 'Anda tidak bisa menghapus akun sendiri';
            header('Location: /user');
            exit;
        }

        if ($this->userModel->deleteUser($id)) {
            $_SESSION['success'] = 'Pengguna berhasil dihapus';
            header('Location: /user');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal menghapus pengguna';
            header('Location: /user');
            exit;
        }
    }
}
