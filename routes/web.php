<?php

// Simple routing system
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove /public from the path if it exists
if (strpos($requestUri, '/public') === 0) {
    $requestUri = substr($requestUri, 7);
}

// Remove trailing slash
$requestUri = rtrim($requestUri, '/');
if (empty($requestUri)) {
    $requestUri = '/';
}

// Define routes
$routes = [
    // Auth routes
    '/login' => ['controller' => 'AuthController', 'method' => 'login'],
    '/logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    
    // User Management routes (Admin only)
    '/user' => ['controller' => 'UserController', 'method' => 'index'],
    '/user/create' => ['controller' => 'UserController', 'method' => 'create'],
    '/user/store' => ['controller' => 'UserController', 'method' => 'store'],
    
    // API routes
    '/api/search-barang' => ['controller' => 'ApiController', 'method' => 'searchBarang'],
    '/api/barang/store' => ['controller' => 'ApiController', 'method' => 'createBarang'],
    
    // Dashboard
    '/' => ['controller' => 'LaporanController', 'method' => 'index'],
    
    // Barang routes
    '/barang' => ['controller' => 'BarangController', 'method' => 'index'],
    '/barang/create' => ['controller' => 'BarangController', 'method' => 'create'],
    '/barang/store' => ['controller' => 'BarangController', 'method' => 'store'],
    '/barang/export' => ['controller' => 'BarangController', 'method' => 'exportExcel'],
    
    // Pembelian routes
    '/pembelian' => ['controller' => 'PembelianController', 'method' => 'index'],
    '/pembelian/create' => ['controller' => 'PembelianController', 'method' => 'create'],
    '/pembelian/store' => ['controller' => 'PembelianController', 'method' => 'store'],
    
    // Penjualan routes
    '/penjualan' => ['controller' => 'PenjualanController', 'method' => 'index'],
    '/penjualan/create' => ['controller' => 'PenjualanController', 'method' => 'create'],
    '/penjualan/store' => ['controller' => 'PenjualanController', 'method' => 'store'],
    
    // Hutang routes
    '/hutang' => ['controller' => 'HutangController', 'method' => 'index'],
    
    // Laporan routes
    '/laporan' => ['controller' => 'LaporanController', 'method' => 'index'],
    '/laporan/pembelian' => ['controller' => 'LaporanController', 'method' => 'pembelian'],
    '/laporan/penjualan' => ['controller' => 'LaporanController', 'method' => 'penjualan'],
    '/laporan/penjualan/export' => ['controller' => 'LaporanController', 'method' => 'exportPenjualan'],
    '/laporan/stok' => ['controller' => 'LaporanController', 'method' => 'stok'],
    '/laporan/stok/export' => ['controller' => 'LaporanController', 'method' => 'exportStok'],
    '/laporan/pembelian/export' => ['controller' => 'LaporanController', 'method' => 'exportPembelian'],
    '/laporan/keuntungan' => ['controller' => 'LaporanController', 'method' => 'keuntungan'],
    '/laporan/keuntungan/export' => ['controller' => 'LaporanController', 'method' => 'exportKeuntungan'],
    
    // Setting routes
    '/setting/kategori-satuan' => ['controller' => 'SettingController', 'method' => 'kategoriSatuan'],
    '/setting/kategori/add' => ['controller' => 'SettingController', 'method' => 'addKategori'],
    '/setting/kategori/update' => ['controller' => 'SettingController', 'method' => 'updateKategori'],
    '/setting/kategori/delete' => ['controller' => 'SettingController', 'method' => 'deleteKategori'],
    '/setting/satuan/add' => ['controller' => 'SettingController', 'method' => 'addSatuan'],
    '/setting/satuan/update' => ['controller' => 'SettingController', 'method' => 'updateSatuan'],
    '/setting/satuan/delete' => ['controller' => 'SettingController', 'method' => 'deleteSatuan'],
    '/setting/nota' => ['controller' => 'SettingController', 'method' => 'nota'],
];

// Check for dynamic routes (edit, update, delete, detail)
if (preg_match('#^/user/edit/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'UserController', 'method' => 'edit', 'params' => [$matches[1]]];
} elseif (preg_match('#^/user/update/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'UserController', 'method' => 'update', 'params' => [$matches[1]]];
} elseif (preg_match('#^/user/delete/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'UserController', 'method' => 'delete', 'params' => [$matches[1]]];
} elseif (preg_match('#^/user/reset-password/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'UserController', 'method' => 'resetPasswordForm', 'params' => [$matches[1]]];
} elseif (preg_match('#^/user/update-password/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'UserController', 'method' => 'updatePassword', 'params' => [$matches[1]]];
} elseif (preg_match('#^/barang/edit/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'BarangController', 'method' => 'edit', 'params' => [$matches[1]]];
} elseif (preg_match('#^/barang/update/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'BarangController', 'method' => 'update', 'params' => [$matches[1]]];
} elseif (preg_match('#^/barang/delete/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'BarangController', 'method' => 'delete', 'params' => [$matches[1]]];
} elseif (preg_match('#^/pembelian/detail/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PembelianController', 'method' => 'detail', 'params' => [$matches[1]]];
} elseif (preg_match('#^/pembelian/edit/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PembelianController', 'method' => 'edit', 'params' => [$matches[1]]];
} elseif (preg_match('#^/pembelian/update/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PembelianController', 'method' => 'update', 'params' => [$matches[1]]];
} elseif (preg_match('#^/pembelian/delete/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PembelianController', 'method' => 'delete', 'params' => [$matches[1]]];
} elseif (preg_match('#^/penjualan/edit/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PenjualanController', 'method' => 'edit', 'params' => [$matches[1]]];
} elseif (preg_match('#^/penjualan/update/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PenjualanController', 'method' => 'update', 'params' => [$matches[1]]];
} elseif (preg_match('#^/penjualan/delete/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PenjualanController', 'method' => 'delete', 'params' => [$matches[1]]];
} elseif (preg_match('#^/penjualan/detail/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'PenjualanController', 'method' => 'detail', 'params' => [$matches[1]]];
} elseif (preg_match('#^/hutang/update-status/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'HutangController', 'method' => 'updateStatus', 'params' => [$matches[1]]];
} elseif (preg_match('#^/hutang/delete/(\d+)$#', $requestUri, $matches)) {
    $route = ['controller' => 'HutangController', 'method' => 'delete', 'params' => [$matches[1]]];
} elseif (isset($routes[$requestUri])) {
    $route = $routes[$requestUri];
} else {
    // 404 Not Found
    http_response_code(404);
    echo "<h1>404 - Page Not Found</h1>";
    exit;
}

// Load controller and call method
$controllerName = $route['controller'];
$methodName = $route['method'];
$params = $route['params'] ?? [];

require_once BASE_PATH . '/app/controllers/' . $controllerName . '.php';

$controller = new $controllerName();
call_user_func_array([$controller, $methodName], $params);
