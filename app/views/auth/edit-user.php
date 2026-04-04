<?php require_once BASE_PATH . '/app/views/layout/header.php'; ?>

<div class="container mx-auto px-4 py-6">
    <h1 class="text-3xl font-bold mb-6">Edit User</h1>

    <?php if ($error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-6 max-w-md">
        <form method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    Username *
                </label>
                <input
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    type="text"
                    id="username"
                    name="username"
                    required
                    value="<?php echo $user['username']; ?>"
                >
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nama">
                    Nama *
                </label>
                <input
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    type="text"
                    id="nama"
                    name="nama"
                    required
                    value="<?php echo $user['nama']; ?>"
                >
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="role">
                    Role *
                </label>
                <select
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500"
                    id="role"
                    name="role"
                    required
                >
                    <option value="manager" <?php echo $user['role'] == 'manager' ? 'selected' : ''; ?>>Manager</option>
                    <option value="kasir" <?php echo ($user['role'] == 'user' || $user['role'] == 'kasir') ? 'selected' : ''; ?>>Kasir</option>
                    <option value="inspeksi" <?php echo $user['role'] == 'inspeksi' ? 'selected' : ''; ?>>Inspeksi</option>
                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <p class="text-sm text-gray-500 mb-6">Password tidak dapat diubah di sini. Hubungi admin untuk reset password.</p>

            <div class="flex gap-2">
                <button
                    class="flex-1 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    type="submit"
                >
                    Simpan
                </button>
                <a href="/users" class="flex-1 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layout/footer.php'; ?>
