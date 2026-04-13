<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Sistem Inventori Toko' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/tailwind.css?v=<?= time() ?>">
    <style>
        /* Mobile menu animation */
        .mobile-menu {
            transition: transform 0.3s ease-in-out;
        }
        .mobile-menu.closed {
            transform: translateX(-100%);
        }
        /* Better touch targets for mobile */
        @media (max-width: 768px) {
            .nav-item, button {
                min-height: 44px;
                min-width: 44px;
            }
        }

        /* Global interactive treatment for action controls */
        .app-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            color: #ffffff;
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 55%, #14b8a6 100%);
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.26);
            transition: transform 150ms ease, box-shadow 220ms ease, filter 180ms ease;
        }

        .app-btn-primary:hover {
            filter: brightness(1.05);
            box-shadow: 0 12px 26px rgba(13, 148, 136, 0.34);
        }

        .app-btn-primary:active {
            transform: translateY(1px) scale(0.99);
        }

        .app-btn-primary:focus-visible {
            outline: 3px solid rgba(20, 184, 166, 0.35);
            outline-offset: 2px;
        }

        .app-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            color: #334155;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            transition: transform 150ms ease, background-color 180ms ease, border-color 180ms ease, color 180ms ease;
        }

        .app-btn-secondary:hover {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #0f172a;
        }

        .app-btn-secondary:active {
            transform: translateY(1px) scale(0.99);
        }

        .app-btn-secondary:focus-visible {
            outline: 3px solid rgba(148, 163, 184, 0.35);
            outline-offset: 2px;
        }

        .app-action-btn {
            position: relative;
            overflow: hidden;
            transition: transform 150ms ease, box-shadow 220ms ease, filter 180ms ease, background-color 180ms ease;
        }

        .app-action-btn::after {
            content: "";
            position: absolute;
            top: 0;
            left: -120%;
            width: 70%;
            height: 100%;
            background: linear-gradient(105deg, transparent, rgba(255, 255, 255, 0.22), transparent);
            transition: left 360ms ease;
            pointer-events: none;
        }

        .app-action-btn:hover::after {
            left: 130%;
        }

        .app-action-btn:hover {
            filter: saturate(1.04);
        }

        .app-action-btn:active {
            transform: translateY(1px) scale(0.99);
        }

        .app-action-btn:focus-visible {
            outline: 3px solid rgba(20, 184, 166, 0.35);
            outline-offset: 2px;
        }

        .app-action-btn.is-loading {
            cursor: not-allowed !important;
            pointer-events: none;
            opacity: 0.88;
        }

        .app-action-btn.is-loading::after {
            display: none;
        }
    </style>
</head>
<body class="h-full flex flex-col">
    <?php
    $rawRole = strtolower(trim((string)($_SESSION['role'] ?? 'user')));
    $currentRole = $rawRole;
    if ($currentRole === 'kasir') {
        $currentRole = 'user';
    }
    if ($rawRole === 'admin') {
        $displayRole = 'Admin';
    } elseif ($rawRole === 'manager') {
        $displayRole = 'Manager';
    } elseif ($rawRole === 'kasir') {
        $displayRole = 'Kasir';
    } elseif ($rawRole === 'inspeksi') {
        $displayRole = 'Inspeksi';
    } else {
        $displayRole = 'User';
    }
    $normalizedRole = class_exists('PermissionGate')
        ? PermissionGate::normalizeRole($rawRole)
        : ($rawRole === 'user' ? 'kasir' : $rawRole);
    $isKasir = $normalizedRole === 'kasir';
    $dashboardHref = $isKasir ? '/penjualan/create' : '/';
    $dashboardLabel = $isKasir ? 'Penjualan' : 'Dashboard';
    $dashboardIconClass = $isKasir ? 'fas fa-cash-register' : 'fas fa-home';
    $operationalDashboardHref = $isKasir ? '/penjualan/create' : '/laporan';
    $operationalDashboardLabel = $isKasir ? 'Buka Penjualan' : 'Buka Dashboard';

    $canViewPembelian = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'pembelian.view') : ($currentRole !== 'inspeksi');
    $canViewPenjualan = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'penjualan.view') : ($currentRole !== 'inspeksi');
    $canAccessTransaksi = $canViewPembelian || $canViewPenjualan;

    $canViewLaporanPembelian = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'laporan.pembelian.view') : ($currentRole !== 'inspeksi');
    $canViewLaporanPenjualan = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'laporan.penjualan.view') : true;
    $canViewLaporanStok = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'laporan.stok.view') : ($currentRole !== 'inspeksi');
    $canViewLaporanKeuntungan = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'laporan.keuntungan.view') : ($currentRole !== 'inspeksi');
    $canViewHutang = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'hutang.view') : ($currentRole !== 'inspeksi');
    $canViewAnyLaporan = $canViewLaporanPembelian || $canViewLaporanPenjualan || $canViewLaporanStok || $canViewLaporanKeuntungan || $canViewHutang;
    $canViewUsersMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'users.view') : ($rawRole === 'admin');
    $canViewMasterMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'setting.master.view') : ($rawRole === 'admin');
    $canViewNotaMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'setting.nota.view') : ($rawRole === 'admin');
    $canViewBackupMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'backup.manage') : ($rawRole === 'admin');
    $canManageRolePermissionsMenu = class_exists('PermissionGate') ? PermissionGate::allows($normalizedRole, 'setting.roles.manage') : ($rawRole === 'admin');
    $canViewSettingsMenu = $canViewUsersMenu || $canViewMasterMenu || $canViewNotaMenu || $canViewBackupMenu || $canManageRolePermissionsMenu;
    ?>
    <nav class="bg-gradient-to-r from-teal-700 via-teal-600 to-amber-500 text-white shadow-2xl z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <!-- Logo & Brand -->
                <a href="/" class="flex items-center space-x-2 sm:space-x-3 group">
                    <div class="bg-white bg-opacity-20 p-1.5 sm:p-2 rounded-lg group-hover:bg-opacity-30 transition">
                        <i class="fas fa-store text-lg sm:text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-base sm:text-xl font-bold">UD. Bersaudara</h1>
                        <p class="text-xs text-teal-100 hidden sm:block">Sistem Inventori</p>
                    </div>
                </a>
                
                <!-- Mobile Menu Button -->
                <button type="button" onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="<?= $dashboardHref ?>" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="<?= $dashboardIconClass ?>"></i>
                        <span><?= $dashboardLabel ?></span>
                    </a>
                    <a href="/barang" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="fas fa-box"></i>
                        <span>Stok</span>
                    </a>
                    <?php if ($canViewPembelian): ?>
                    <a href="/pembelian" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Barang Masuk</span>
                    </a>
                    <?php endif; ?>
                    <?php if ($canViewPenjualan && !$isKasir): ?>
                    <a href="/penjualan" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="fas fa-cash-register"></i>
                        <span>Penjualan</span>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($canViewAnyLaporan): ?>
                        <!-- Dropdown Laporan -->
                        <div class="relative" onclick="toggleDropdown(event, this)">
                            <button type="button" class="nav-item px-4 py-2 rounded-lg hover:bg-teal-800 transition flex items-center gap-2">
                                <i class="fas fa-chart-line"></i>
                                <span>Laporan</span>
                                <i class="fas fa-caret-down text-xs"></i>
                            </button>
                            <div class="absolute hidden bg-white text-gray-800 shadow-xl rounded-lg mt-2 w-56 dropdown-menu-content border border-gray-200 overflow-hidden" style="left: 0;">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Laporan</p>
                                </div>
                                <?php if ($canViewLaporanPembelian): ?>
                                <a href="/laporan/pembelian" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-shopping-cart text-blue-600 w-4"></i>
                                    <span>Barang Masuk</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canViewLaporanPenjualan): ?>
                                <a href="/laporan/penjualan" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-cash-register text-green-600 w-4"></i>
                                    <span>Penjualan</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canViewLaporanStok): ?>
                                <a href="/laporan/stok" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-boxes text-orange-600 w-4"></i>
                                    <span>Stok</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canViewLaporanKeuntungan): ?>
                                <a href="/laporan/keuntungan" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-chart-pie text-purple-600 w-4"></i>
                                    <span>Laba</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canViewHutang): ?>
                                <div class="border-t border-gray-200"></div>
                                <a href="/hutang" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-file-invoice-dollar text-red-600 w-4"></i>
                                    <span>Hutang</span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($canViewSettingsMenu): ?>
                        <!-- Dropdown Pengaturan -->
                        <div class="relative" onclick="toggleDropdown(event, this)">
                            <button type="button" class="nav-item px-4 py-2 rounded-lg hover:bg-teal-800 transition flex items-center gap-2">
                                <i class="fas fa-cog"></i>
                                <span>Pengaturan</span>
                                <i class="fas fa-caret-down text-xs"></i>
                            </button>
                            <div class="absolute hidden bg-white text-gray-800 shadow-xl rounded-lg mt-2 w-56 dropdown-menu-content border border-gray-200 overflow-hidden" style="left: 0;">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Pengaturan</p>
                                </div>
                                <?php if ($canViewUsersMenu): ?>
                                <a href="/user" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-users text-blue-600 w-4"></i>
                                    <span>Manajemen Pengguna</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canViewMasterMenu): ?>
                                <a href="/setting/kategori-satuan" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-tags text-green-600 w-4"></i>
                                    <span>Kategori & Satuan</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canManageRolePermissionsMenu): ?>
                                <a href="/setting/role-permissions" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-user-shield text-indigo-600 w-4"></i>
                                    <span>Role Permission</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canViewNotaMenu): ?>
                                <a href="/setting/nota" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-receipt text-purple-600 w-4"></i>
                                    <span>Format Nota</span>
                                </a>
                                <?php endif; ?>
                                <?php if ($canViewBackupMenu): ?>
                                <a href="/backup" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-database text-emerald-600 w-4"></i>
                                    <span>Backup & Restore</span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Operational Notification -->
                <div class="relative" onclick="toggleDropdown(event, this)">
                    <button type="button" class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-white hover:bg-opacity-10 transition" aria-label="Notifikasi Operasional">
                        <i class="fas fa-bell text-sm"></i>
                        <span id="operationalNotifBadge" class="hidden absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold">0</span>
                    </button>
                    <div class="absolute hidden bg-white text-gray-800 shadow-xl rounded-lg mt-2 w-80 dropdown-menu-content border border-gray-200 overflow-hidden" style="right: 0;">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                            <p class="font-semibold text-gray-800 text-sm">Notifikasi Operasional</p>
                            <span id="operationalNotifUpdatedAt" class="text-[11px] text-gray-500">Memuat...</span>
                        </div>
                        <div id="operationalNotifList" class="max-h-80 overflow-y-auto divide-y divide-gray-100">
                            <div class="px-4 py-3 text-sm text-gray-500">Memuat notifikasi...</div>
                        </div>
                        <a href="<?= $operationalDashboardHref ?>" class="block px-4 py-2.5 text-xs font-semibold text-teal-700 hover:bg-teal-50 border-t border-gray-100">
                            <?= $operationalDashboardLabel ?>
                        </a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="relative" onclick="toggleDropdown(event, this)">
                    <button type="button" class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <div class="text-left hidden md:block">
                                <p class="text-sm font-semibold"><?php echo $_SESSION['nama'] ?? 'User'; ?></p>
                                <p class="text-xs text-teal-100"><?php echo $displayRole; ?></p>
                            </div>
                        </div>
                        <i class="fas fa-caret-down text-xs"></i>
                    </button>
                    <div class="absolute hidden bg-white text-gray-800 shadow-xl rounded-lg mt-2 w-48 dropdown-menu-content border border-gray-200 overflow-hidden" style="right: 0;">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <p class="font-semibold text-gray-800"><?php echo $_SESSION['nama'] ?? 'User'; ?></p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <i class="fas fa-circle text-green-500 text-[6px] mr-1"></i>
                                <?php echo $displayRole; ?>
                            </p>
                        </div>
                        <a href="/logout" class="flex items-center gap-3 px-4 py-3 hover:bg-red-50 text-red-600 transition">
                            <i class="fas fa-sign-out-alt w-4"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden lg:hidden z-40" onclick="toggleMobileMenu()"></div>
        
        <!-- Mobile Menu Sidebar -->
        <div id="mobileMenu" class="mobile-menu closed fixed top-0 left-0 h-full w-80 max-w-[85vw] bg-white text-gray-800 shadow-2xl z-50 lg:hidden overflow-y-auto">
            <div class="bg-gradient-to-r from-teal-700 via-teal-600 to-amber-500 text-white p-4 flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-lg">Menu</h2>
                    <p class="text-xs text-blue-100"><?php echo $_SESSION['nama'] ?? 'User'; ?></p>
                </div>
                <button type="button" onclick="toggleMobileMenu()" class="p-2 hover:bg-white hover:bg-opacity-10 rounded-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-2">
                <a href="<?= $dashboardHref ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="<?= $dashboardIconClass ?> text-blue-600 w-5"></i>
                    <span><?= $dashboardLabel ?></span>
                </a>
                <a href="/barang" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="fas fa-box text-green-600 w-5"></i>
                    <span>Stok</span>
                </a>
                <?php if ($canViewPembelian): ?>
                <a href="/pembelian" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="fas fa-shopping-cart text-orange-600 w-5"></i>
                    <span>Barang Masuk</span>
                </a>
                <?php endif; ?>
                <?php if ($canViewPenjualan && !$isKasir): ?>
                <a href="/penjualan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="fas fa-cash-register text-purple-600 w-5"></i>
                    <span>Penjualan</span>
                </a>
                <?php endif; ?>
                
                <?php if ($canViewAnyLaporan): ?>
                    <!-- Laporan Section -->
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 mb-2">Laporan</p>
                        <?php if ($canViewLaporanPembelian): ?>
                            <a href="/laporan/pembelian" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-shopping-cart text-blue-600 w-5"></i>
                                <span>Barang Masuk</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($canViewLaporanPenjualan): ?>
                            <a href="/laporan/penjualan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-cash-register text-green-600 w-5"></i>
                                <span>Penjualan</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($canViewLaporanStok): ?>
                            <a href="/laporan/stok" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-boxes text-orange-600 w-5"></i>
                                <span>Stok</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($canViewLaporanKeuntungan): ?>
                            <a href="/laporan/keuntungan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-chart-pie text-purple-600 w-5"></i>
                                <span>Laba</span>
                            </a>
                        <?php endif; ?>
                        <?php if ($canViewHutang): ?>
                            <a href="/hutang" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                                <i class="fas fa-file-invoice-dollar text-red-600 w-5"></i>
                                <span>Hutang</span>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Settings Section -->
                <?php if ($canViewSettingsMenu): ?>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 mb-2">Pengaturan</p>
                        <?php if ($canViewUsersMenu): ?>
                        <a href="/user" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-users text-blue-600 w-5"></i>
                            <span>Manajemen Pengguna</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($canViewMasterMenu): ?>
                        <a href="/setting/kategori-satuan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-tags text-green-600 w-5"></i>
                            <span>Kategori & Satuan</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($canManageRolePermissionsMenu): ?>
                        <a href="/setting/role-permissions" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-user-shield text-indigo-600 w-5"></i>
                            <span>Role Permission</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($canViewNotaMenu): ?>
                        <a href="/setting/nota" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-receipt text-purple-600 w-5"></i>
                            <span>Format Nota</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($canViewBackupMenu): ?>
                        <a href="/backup" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-database text-emerald-600 w-5"></i>
                            <span>Backup & Restore</span>
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Logout -->
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <a href="/logout" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-50 text-red-600 transition">
                        <i class="fas fa-sign-out-alt w-5"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-3 sm:px-4 py-4 sm:py-8 flex-grow">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="app-alert app-alert-success relative mb-4 app-reveal" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="app-alert app-alert-error relative mb-4 app-reveal" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <footer class="bg-gradient-to-r from-teal-700 via-teal-600 to-amber-500 text-white mt-8 sm:mt-12 shadow-2xl">
        <div class="container mx-auto px-4 py-6 sm:py-8">
            <div class="border-t border-white border-opacity-20 pt-4 sm:pt-6">
                <p class="text-center text-xs sm:text-sm font-medium">
                    <i class="fas fa-copyright mr-1"></i>Copyright © 2026 Muhamad Rizqi Duha Pramudya. All rights reserved.
                </p>
                <p class="text-center text-xs text-white text-opacity-80 mt-2">
                    Sistem Inventori Toko
                </p>
            </div>
        </div>
    </footer>

    <div id="globalLoadingOverlay" class="hidden fixed inset-0 z-[90] bg-slate-900/30 backdrop-blur-[1px]">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="rounded-xl bg-white border border-slate-200 px-4 py-3 shadow-lg text-sm font-semibold text-slate-700 inline-flex items-center gap-2">
                <i class="fas fa-spinner fa-spin text-teal-600"></i>
                Memuat halaman...
            </div>
        </div>
    </div>
    <div id="operationalRealtimeToast" class="hidden fixed top-16 right-4 z-[95] max-w-sm rounded-xl px-4 py-3 text-sm font-semibold shadow-lg border border-amber-200 bg-amber-50 text-amber-700"></div>

    <script>
        function applyInteractiveEnhancements() {
            const selectors = [
                'button',
                'input[type="submit"]',
                'input[type="button"]',
                'a.nav-item',
                'a.app-btn-primary',
                'a.app-btn-secondary',
                'button.app-btn-primary',
                'button.app-btn-secondary',
                '.dropdown-menu-content a',
                '#mobileMenu a',
                'a.inline-flex[class*="rounded"]',
                'a[class*="bg-"][class*="rounded"]'
            ];

            document.querySelectorAll(selectors.join(',')).forEach((el) => {
                if (!el.classList.contains('app-action-btn')) {
                    el.classList.add('app-action-btn');
                }
            });
        }

        function enhancePostForms() {
            document.querySelectorAll('form:not([data-skip-auto-submit-enhance="true"])').forEach((form) => {
                if (form.dataset.appSubmitEnhanced === 'true') {
                    return;
                }
                if ((form.method || '').toUpperCase() !== 'POST') {
                    return;
                }

                form.dataset.appSubmitEnhanced = 'true';
                form.addEventListener('submit', (event) => {
                    const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
                    if (!submitter || submitter.dataset.noLoading === 'true' || submitter.disabled) {
                        return;
                    }

                    submitter.classList.add('is-loading', 'cursor-not-allowed', 'opacity-90');
                    submitter.disabled = true;
                    submitter.setAttribute('aria-busy', 'true');

                    const loadingText = submitter.getAttribute('data-loading-text') || 'Memproses...';

                    if (submitter.tagName === 'BUTTON') {
                        if (!submitter.dataset.originalHtml) {
                            submitter.dataset.originalHtml = submitter.innerHTML;
                        }
                        submitter.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>' + loadingText + '</span>';
                        submitter.classList.add('inline-flex', 'items-center', 'justify-center', 'gap-2');
                    } else if (submitter.tagName === 'INPUT') {
                        if (!submitter.dataset.originalValue) {
                            submitter.dataset.originalValue = submitter.value;
                        }
                        submitter.value = loadingText;
                    }
                }, { once: true });
            });
        }

        function enhanceNavigationLoading() {
            document.querySelectorAll('a[href^="/"]:not([download])').forEach((link) => {
                if (link.dataset.navLoadingBound === 'true') {
                    return;
                }
                const href = link.getAttribute('href') || '';
                if (href.includes('/logout') || href.includes('/backup/download') || href.includes('/export')) {
                    return;
                }
                link.dataset.navLoadingBound = 'true';
                link.addEventListener('click', () => {
                    const overlay = document.getElementById('globalLoadingOverlay');
                    if (overlay) {
                        overlay.classList.remove('hidden');
                    }
                });
            });
        }

        let operationalNotifSignature = null;
        let operationalNotifInitialized = false;

        function showOperationalToast(message) {
            const toast = document.getElementById('operationalRealtimeToast');
            if (!toast) return;
            toast.textContent = message;
            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3200);
        }

        function renderOperationalNotifications(payload) {
            const list = document.getElementById('operationalNotifList');
            const badge = document.getElementById('operationalNotifBadge');
            const updatedAt = document.getElementById('operationalNotifUpdatedAt');
            if (!list || !badge || !updatedAt) return;

            const summary = payload.summary || {};
            const totalAlert = Number(summary.total_alert || 0);
            const items = Array.isArray(payload.items) ? payload.items : [];
            const currentSignature = payload.signature || '';

            if (!operationalNotifInitialized) {
                operationalNotifInitialized = true;
                operationalNotifSignature = currentSignature;
            } else if (currentSignature && operationalNotifSignature !== currentSignature) {
                operationalNotifSignature = currentSignature;
                if (Number(summary.total_critical || 0) > 0) {
                    showOperationalToast('Update notifikasi operasional baru terdeteksi.');
                }
            }

            if (totalAlert > 0) {
                badge.textContent = String(totalAlert > 99 ? '99+' : totalAlert);
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }

            updatedAt.textContent = payload.generated_at ? 'Update ' + payload.generated_at : 'Terbaru';

            if (items.length === 0) {
                list.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Tidak ada notifikasi operasional.</div>';
                return;
            }

            list.innerHTML = items.map((item) => {
                const level = item.level || 'medium';
                const levelClass = level === 'high'
                    ? 'bg-red-50 text-red-700 border-red-100'
                    : 'bg-amber-50 text-amber-700 border-amber-100';
                const title = String(item.title || 'Notifikasi');
                const message = String(item.message || '');
                const link = String(item.link || '/laporan');
                return `
                    <a href="${link}" class="block px-4 py-3 hover:bg-slate-50 transition">
                        <div class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border ${levelClass}">
                            ${level === 'high' ? 'TINGGI' : 'SEDANG'}
                        </div>
                        <p class="text-sm font-semibold text-slate-800 mt-1">${title}</p>
                        <p class="text-xs text-slate-600 mt-0.5">${message}</p>
                    </a>
                `;
            }).join('');
        }

        async function refreshOperationalNotifications() {
            try {
                const res = await fetch('/api/operational-alerts', {
                    method: 'GET',
                    cache: 'no-store',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data || data.success !== true) return;
                renderOperationalNotifications(data);
            } catch (e) {
                // silent fail
            }
        }

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');
            
            if (menu.classList.contains('closed')) {
                menu.classList.remove('closed');
                overlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                menu.classList.add('closed');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        }
        
        // Close mobile menu when clicking a link
        document.querySelectorAll('#mobileMenu a').forEach(link => {
            link.addEventListener('click', () => {
                toggleMobileMenu();
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Dropdown show/hide (click-based to avoid accidental close)
        function toggleDropdown(event, element) {
            event.stopPropagation();
            closeAllDropdowns(element);
            const dropdown = element.querySelector('.dropdown-menu-content');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        function closeAllDropdowns(except) {
            document.querySelectorAll('.dropdown-menu-content').forEach(menu => {
                if (!except || !except.contains(menu)) {
                    menu.classList.add('hidden');
                }
            });
        }

        document.addEventListener('click', () => closeAllDropdowns());

        // Stagger reveal for major cards/sections
        document.querySelectorAll('.app-reveal').forEach((el, idx) => {
            el.style.animationDelay = `${Math.min(idx * 60, 420)}ms`;
        });

        applyInteractiveEnhancements();
        enhancePostForms();
        enhanceNavigationLoading();
        refreshOperationalNotifications();
        setInterval(refreshOperationalNotifications, 60000);

        const interactiveObserver = new MutationObserver(() => {
            applyInteractiveEnhancements();
            enhancePostForms();
            enhanceNavigationLoading();
        });
        interactiveObserver.observe(document.body, { childList: true, subtree: true });
    </script>
</body>
</html>
