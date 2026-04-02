<?php

require_once BASE_PATH . '/app/config/database.php';

class PermissionGate {
    private const CONFIGURED_MARKER = '__configured__';
    private const MATRIX = [
        'admin' => ['*'],
        'kasir' => [
            'dashboard.view',
            'barang.view',
            'barang.edit',
            'barang.delete',
            'barang.export',
            'pembelian.view',
            'pembelian.create',
            'pembelian.edit',
            'pembelian.delete',
            'pembelian.export',
            'penjualan.view',
            'penjualan.create',
            'penjualan.edit',
            'penjualan.delete',
            'penjualan.export',
            'hutang.view',
            'hutang.update',
            'laporan.penjualan.view',
            'laporan.penjualan.export',
            'laporan.pembelian.view',
            'laporan.pembelian.export',
            'laporan.keuntungan.view',
            'laporan.keuntungan.export',
            'laporan.stok.view',
            'laporan.stok.export',
            'setting.nota.view',
            'setting.nota.edit'
        ],
        'inspeksi' => [
            'dashboard.view',
            'barang.view',
            'barang.edit',
            'barang.delete',
            'barang.export'
        ]
    ];

    private const CONFIGURABLE_ROLES = ['kasir', 'inspeksi'];
    private static ?array $runtimeMatrixCache = null;

    private const ACTION_MAP = [
        'LaporanController@index' => 'dashboard.view',

        'BarangController@index' => 'barang.view',
        'BarangController@create' => 'barang.create',
        'BarangController@store' => 'barang.create',
        'BarangController@edit' => 'barang.edit',
        'BarangController@update' => 'barang.edit',
        'BarangController@delete' => 'barang.delete',
        'BarangController@exportExcel' => 'barang.export',

        'PembelianController@index' => 'pembelian.view',
        'PembelianController@detail' => 'pembelian.view',
        'PembelianController@create' => 'pembelian.create',
        'PembelianController@store' => 'pembelian.create',
        'PembelianController@edit' => 'pembelian.edit',
        'PembelianController@update' => 'pembelian.edit',
        'PembelianController@delete' => 'pembelian.delete',
        'PembelianController@export' => 'pembelian.export',

        'PenjualanController@index' => 'penjualan.view',
        'PenjualanController@detail' => 'penjualan.view',
        'PenjualanController@create' => 'penjualan.create',
        'PenjualanController@store' => 'penjualan.create',
        'PenjualanController@edit' => 'penjualan.edit',
        'PenjualanController@update' => 'penjualan.edit',
        'PenjualanController@delete' => 'penjualan.delete',
        'PenjualanController@export' => 'penjualan.export',

        'HutangController@index' => 'hutang.view',
        'HutangController@updateStatus' => 'hutang.update',
        'HutangController@delete' => 'hutang.delete',

        'LaporanController@pembelian' => 'laporan.pembelian.view',
        'LaporanController@penjualan' => 'laporan.penjualan.view',
        'LaporanController@stok' => 'laporan.stok.view',
        'LaporanController@keuntungan' => 'laporan.keuntungan.view',
        'LaporanController@exportPembelian' => 'laporan.pembelian.export',
        'LaporanController@exportPenjualan' => 'laporan.penjualan.export',
        'LaporanController@exportStok' => 'laporan.stok.export',
        'LaporanController@exportKeuntungan' => 'laporan.keuntungan.export',

        'SettingController@nota' => 'setting.nota.view',
        'SettingController@saveNota' => 'setting.nota.edit',
        'SettingController@rolePermissions' => 'setting.roles.manage',
        'SettingController@kategoriSatuan' => 'setting.master.view',
        'SettingController@addKategori' => 'setting.master.edit',
        'SettingController@updateKategori' => 'setting.master.edit',
        'SettingController@deleteKategori' => 'setting.master.edit',
        'SettingController@addSatuan' => 'setting.master.edit',
        'SettingController@updateSatuan' => 'setting.master.edit',
        'SettingController@deleteSatuan' => 'setting.master.edit',

        'UserController@index' => 'users.view',
        'UserController@create' => 'users.edit',
        'UserController@store' => 'users.edit',
        'UserController@edit' => 'users.edit',
        'UserController@update' => 'users.edit',
        'UserController@delete' => 'users.edit',
        'UserController@resetPasswordForm' => 'users.edit',
        'UserController@updatePassword' => 'users.edit'
        ,
        'BackupController@index' => 'backup.manage',
        'BackupController@create' => 'backup.manage',
        'BackupController@download' => 'backup.manage',
        'BackupController@restore' => 'backup.manage'
    ];

    public static function normalizeRole(?string $role): string {
        $r = strtolower(trim((string)$role));
        if ($r === 'user') {
            return 'kasir';
        }
        return $r !== '' ? $r : 'kasir';
    }

    public static function resolvePermission(string $controller, string $method): ?string {
        $key = $controller . '@' . $method;
        return self::ACTION_MAP[$key] ?? null;
    }

    public static function allows(?string $role, ?string $permission): bool {
        if ($permission === null || $permission === '') {
            return true;
        }

        $normalizedRole = self::normalizeRole($role);
        if ($normalizedRole === 'kasir' && strpos((string)$permission, 'laporan.keuntungan.') === 0) {
            return false;
        }
        $matrix = self::getRuntimeMatrix();
        $grants = $matrix[$normalizedRole] ?? [];

        if (in_array('*', $grants, true)) {
            return true;
        }

        return in_array($permission, $grants, true);
    }

    public static function getConfigurableRoles(): array {
        return self::CONFIGURABLE_ROLES;
    }

    public static function getPermissionCatalog(): array {
        $allPermissions = array_values(array_unique(array_filter(array_values(self::ACTION_MAP))));
        sort($allPermissions);

        $catalog = [];
        foreach ($allPermissions as $perm) {
            if ($perm === 'setting.roles.manage') {
                continue;
            }
            $parts = explode('.', $perm);
            $group = $parts[0] ?? 'lainnya';
            $catalog[$group][] = [
                'key' => $perm,
                'label' => self::humanizePermission($perm)
            ];
        }

        ksort($catalog);
        return $catalog;
    }

    public static function getRolePermissions(string $role): array {
        $normalizedRole = self::normalizeRole($role);
        $matrix = self::getRuntimeMatrix();
        $grants = $matrix[$normalizedRole] ?? [];
        if ($normalizedRole === 'kasir') {
            $grants = array_values(array_filter($grants, fn($p) => strpos((string)$p, 'laporan.keuntungan.') !== 0));
        }
        $grants = array_values(array_filter($grants, fn($p) => $p !== '*'));
        sort($grants);
        return $grants;
    }

    public static function saveRolePermissions(string $role, array $permissions): bool {
        $normalizedRole = self::normalizeRole($role);
        if (!in_array($normalizedRole, self::CONFIGURABLE_ROLES, true)) {
            return false;
        }

        $allAllowed = array_values(array_unique(array_filter(array_values(self::ACTION_MAP))));
        $validSet = array_flip($allAllowed);
        $cleanPermissions = [];
        foreach ($permissions as $perm) {
            $key = trim((string)$perm);
            if ($normalizedRole === 'kasir' && strpos($key, 'laporan.keuntungan.') === 0) {
                continue;
            }
            if ($key !== '' && isset($validSet[$key]) && $key !== 'setting.roles.manage') {
                $cleanPermissions[] = $key;
            }
        }
        $cleanPermissions = array_values(array_unique($cleanPermissions));

        try {
            $database = new Database();
            $conn = $database->getConnection();
            if (!$conn) {
                return false;
            }

            self::ensurePermissionTable($conn);

            $conn->beginTransaction();
            $deleteStmt = $conn->prepare("DELETE FROM role_permissions WHERE role = :role");
            $deleteStmt->bindValue(':role', $normalizedRole);
            $deleteStmt->execute();

            $markerStmt = $conn->prepare("INSERT INTO role_permissions (role, permission_key, allowed, updated_at)
                                          VALUES (:role, :permission_key, 1, CURRENT_TIMESTAMP)");
            $markerStmt->bindValue(':role', $normalizedRole);
            $markerStmt->bindValue(':permission_key', self::CONFIGURED_MARKER);
            $markerStmt->execute();

            if (!empty($cleanPermissions)) {
                $insertStmt = $conn->prepare("INSERT INTO role_permissions (role, permission_key, allowed, updated_at)
                                              VALUES (:role, :permission_key, 1, CURRENT_TIMESTAMP)");
                foreach ($cleanPermissions as $perm) {
                    $insertStmt->bindValue(':role', $normalizedRole);
                    $insertStmt->bindValue(':permission_key', $perm);
                    $insertStmt->execute();
                }
            }

            $conn->commit();
            self::clearRuntimeCache();
            return true;
        } catch (Throwable $e) {
            try {
                if (isset($conn) && $conn instanceof PDO && $conn->inTransaction()) {
                    $conn->rollBack();
                }
            } catch (Throwable $inner) {
                // ignore rollback errors
            }
            error_log('saveRolePermissions error: ' . $e->getMessage());
            return false;
        }
    }

    public static function clearRuntimeCache(): void {
        self::$runtimeMatrixCache = null;
    }

    private static function getRuntimeMatrix(): array {
        if (self::$runtimeMatrixCache !== null) {
            return self::$runtimeMatrixCache;
        }

        $matrix = self::MATRIX;

        try {
            $dynamic = self::loadDynamicPermissions();
            foreach ($dynamic as $role => $permissions) {
                if (in_array($role, self::CONFIGURABLE_ROLES, true)) {
                    $matrix[$role] = $permissions;
                }
            }
        } catch (Throwable $e) {
            error_log('getRuntimeMatrix error: ' . $e->getMessage());
        }

        if (!isset($matrix['admin'])) {
            $matrix['admin'] = ['*'];
        } elseif (!in_array('*', $matrix['admin'], true)) {
            $matrix['admin'][] = '*';
        }

        self::$runtimeMatrixCache = $matrix;
        return $matrix;
    }

    private static function loadDynamicPermissions(): array {
        $database = new Database();
        $conn = $database->getConnection();
        if (!$conn) {
            return [];
        }

        self::ensurePermissionTable($conn);

        $stmt = $conn->prepare("SELECT role, permission_key
                                FROM role_permissions
                                WHERE allowed = 1");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $role = self::normalizeRole($row['role'] ?? '');
            $perm = trim((string)($row['permission_key'] ?? ''));
            if ($role === '' || $perm === '') {
                continue;
            }
            if (!isset($result[$role])) {
                $result[$role] = [];
            }
            if ($perm === self::CONFIGURED_MARKER) {
                continue;
            }
            $result[$role][] = $perm;
        }

        foreach ($result as $role => $permissions) {
            $result[$role] = array_values(array_unique($permissions));
            sort($result[$role]);
        }

        return $result;
    }

    private static function ensurePermissionTable(PDO $conn): void {
        $conn->exec("CREATE TABLE IF NOT EXISTS role_permissions (
            role VARCHAR(32) NOT NULL,
            permission_key VARCHAR(120) NOT NULL,
            allowed SMALLINT NOT NULL DEFAULT 1,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (role, permission_key)
        )");
    }

    private static function humanizePermission(string $permission): string {
        $parts = explode('.', $permission);
        if (count($parts) < 2) {
            return ucwords(str_replace(['_', '.'], ' ', $permission));
        }

        $moduleMap = [
            'dashboard' => 'Dashboard',
            'barang' => 'Stok Barang',
            'pembelian' => 'Barang Masuk',
            'penjualan' => 'Penjualan',
            'hutang' => 'Hutang',
            'laporan' => 'Laporan',
            'setting' => 'Pengaturan',
            'users' => 'Pengguna',
            'backup' => 'Backup'
        ];
        $actionMap = [
            'view' => 'Lihat',
            'create' => 'Tambah',
            'edit' => 'Edit',
            'delete' => 'Hapus',
            'export' => 'Export',
            'update' => 'Update',
            'manage' => 'Kelola',
            'master' => 'Master'
        ];

        $module = $moduleMap[$parts[0]] ?? ucwords(str_replace('_', ' ', $parts[0]));
        $action = $actionMap[end($parts)] ?? ucwords(str_replace('_', ' ', end($parts)));
        return $module . ' - ' . $action;
    }
}
