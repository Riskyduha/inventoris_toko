<?php

class PermissionGate {
    private const MATRIX = [
        'admin' => ['*'],
        'kasir' => [
            'dashboard.view',
            'barang.view',
            'barang.edit',
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
            'barang.export'
        ]
    ];

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
        $grants = self::MATRIX[$normalizedRole] ?? [];

        if (in_array('*', $grants, true)) {
            return true;
        }

        return in_array($permission, $grants, true);
    }
}
