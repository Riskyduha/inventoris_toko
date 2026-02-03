<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $title ?? 'Sistem Inventori Toko' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    </style>
</head>
<body class="bg-gray-100 h-full flex flex-col">
    <nav class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-3">
                <!-- Logo & Brand -->
                <a href="/" class="flex items-center space-x-2 sm:space-x-3 group">
                    <div class="bg-white bg-opacity-20 p-1.5 sm:p-2 rounded-lg group-hover:bg-opacity-30 transition">
                        <i class="fas fa-store text-lg sm:text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-base sm:text-xl font-bold">UD. Bersaudara</h1>
                        <p class="text-xs text-blue-100 hidden sm:block">Sistem Inventori</p>
                    </div>
                </a>
                
                <!-- Mobile Menu Button -->
                <button type="button" onclick="toggleMobileMenu()" class="lg:hidden p-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-1">
                    <a href="/" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="/barang" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="fas fa-box"></i>
                        <span>Barang</span>
                    </a>
                    <a href="/pembelian" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Pembelian</span>
                    </a>
                    <a href="/penjualan" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                        <i class="fas fa-cash-register"></i>
                        <span>Penjualan</span>
                    </a>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'kasir'): ?>
                        <a href="/laporan/penjualan" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                            <i class="fas fa-chart-line"></i>
                            <span>Laporan</span>
                        </a>
                    <?php else: ?>
                        <!-- Dropdown Laporan -->
                        <div class="relative" onclick="toggleDropdown(event, this)">
                            <button type="button" class="nav-item px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                                <i class="fas fa-chart-line"></i>
                                <span>Laporan</span>
                                <i class="fas fa-caret-down text-xs"></i>
                            </button>
                            <div class="absolute hidden bg-white text-gray-800 shadow-xl rounded-lg mt-2 w-56 dropdown-menu-content border border-gray-200 overflow-hidden" style="left: 0;">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Laporan</p>
                                </div>
                                <a href="/laporan/pembelian" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-shopping-cart text-blue-600 w-4"></i>
                                    <span>Pembelian</span>
                                </a>
                                <a href="/laporan/penjualan" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-cash-register text-green-600 w-4"></i>
                                    <span>Penjualan</span>
                                </a>
                                <a href="/laporan/stok" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-boxes text-orange-600 w-4"></i>
                                    <span>Stok Barang</span>
                                </a>
                                <a href="/laporan/keuntungan" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-chart-pie text-purple-600 w-4"></i>
                                    <span>Keuntungan</span>
                                </a>
                                <div class="border-t border-gray-200"></div>
                                <a href="/hutang" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-file-invoice-dollar text-red-600 w-4"></i>
                                    <span>Hutang</span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <!-- Dropdown Pengaturan -->
                        <div class="relative" onclick="toggleDropdown(event, this)">
                            <button type="button" class="nav-item px-4 py-2 rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                                <i class="fas fa-cog"></i>
                                <span>Pengaturan</span>
                                <i class="fas fa-caret-down text-xs"></i>
                            </button>
                            <div class="absolute hidden bg-white text-gray-800 shadow-xl rounded-lg mt-2 w-56 dropdown-menu-content border border-gray-200 overflow-hidden" style="left: 0;">
                                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                                    <p class="text-xs font-semibold text-gray-500 uppercase">Pengaturan</p>
                                </div>
                                <a href="/user" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-users text-blue-600 w-4"></i>
                                    <span>Manajemen Pengguna</span>
                                </a>
                                <a href="/setting/kategori-satuan" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-tags text-green-600 w-4"></i>
                                    <span>Kategori & Satuan</span>
                                </a>
                                <a href="/setting/nota" class="flex items-center gap-3 px-4 py-3 hover:bg-blue-50 transition">
                                    <i class="fas fa-receipt text-purple-600 w-4"></i>
                                    <span>Format Nota</span>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/setting/kategori-satuan" class="nav-item px-4 py-2 rounded-lg hover:bg-white hover:bg-opacity-10 transition flex items-center gap-2">
                            <i class="fas fa-tags"></i>
                            <span>Kategori</span>
                        </a>
                    <?php endif; ?>
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
                                <p class="text-xs text-blue-100"><?php echo ucfirst($_SESSION['role'] ?? 'user'); ?></p>
                            </div>
                        </div>
                        <i class="fas fa-caret-down text-xs"></i>
                    </button>
                    <div class="absolute hidden bg-white text-gray-800 shadow-xl rounded-lg mt-2 w-48 dropdown-menu-content border border-gray-200 overflow-hidden" style="right: 0;">
                        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                            <p class="font-semibold text-gray-800"><?php echo $_SESSION['nama'] ?? 'User'; ?></p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                <i class="fas fa-circle text-green-500 text-[6px] mr-1"></i>
                                <?php echo ucfirst($_SESSION['role'] ?? 'user'); ?>
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
            <div class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white p-4 flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-lg">Menu</h2>
                    <p class="text-xs text-blue-100"><?php echo $_SESSION['nama'] ?? 'User'; ?></p>
                </div>
                <button type="button" onclick="toggleMobileMenu()" class="p-2 hover:bg-white hover:bg-opacity-10 rounded-lg">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="p-4 space-y-2">
                <a href="/" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="fas fa-home text-blue-600 w-5"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/barang" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="fas fa-box text-green-600 w-5"></i>
                    <span>Barang</span>
                </a>
                <a href="/pembelian" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="fas fa-shopping-cart text-orange-600 w-5"></i>
                    <span>Pembelian</span>
                </a>
                <a href="/penjualan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                    <i class="fas fa-cash-register text-purple-600 w-5"></i>
                    <span>Penjualan</span>
                </a>
                
                <!-- Laporan Section -->
                <div class="border-t border-gray-200 pt-2 mt-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase px-4 mb-2">Laporan</p>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'kasir'): ?>
                        <a href="/laporan/penjualan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-chart-line text-green-600 w-5"></i>
                            <span>Laporan Penjualan</span>
                        </a>
                    <?php else: ?>
                        <a href="/laporan/pembelian" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-shopping-cart text-blue-600 w-5"></i>
                            <span>Pembelian</span>
                        </a>
                        <a href="/laporan/penjualan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-cash-register text-green-600 w-5"></i>
                            <span>Penjualan</span>
                        </a>
                        <a href="/laporan/stok" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-boxes text-orange-600 w-5"></i>
                            <span>Stok Barang</span>
                        </a>
                        <a href="/laporan/keuntungan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-chart-pie text-purple-600 w-5"></i>
                            <span>Keuntungan</span>
                        </a>
                        <a href="/hutang" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-file-invoice-dollar text-red-600 w-5"></i>
                            <span>Hutang</span>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Settings Section -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <div class="border-t border-gray-200 pt-2 mt-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase px-4 mb-2">Pengaturan</p>
                        <a href="/user" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-users text-blue-600 w-5"></i>
                            <span>Manajemen Pengguna</span>
                        </a>
                        <a href="/setting/kategori-satuan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-tags text-green-600 w-5"></i>
                            <span>Kategori & Satuan</span>
                        </a>
                        <a href="/setting/nota" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                            <i class="fas fa-receipt text-purple-600 w-5"></i>
                            <span>Format Nota</span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="/setting/kategori-satuan" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-blue-50 transition">
                        <i class="fas fa-tags text-green-600 w-5"></i>
                        <span>Kategori & Satuan</span>
                    </a>
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
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['success'] ?></span>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?= $_SESSION['error'] ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>

    <footer class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-white mt-8 sm:mt-12 shadow-2xl">
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

    <script>
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
    </script>
</body>
</html>
