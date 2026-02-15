<?php

require_once __DIR__ . '/../models/Laporan.php';
require_once __DIR__ . '/../helpers/format.php';

class LaporanController {
    private $model;

    public function __construct() {
        $this->model = new Laporan();
    }

    public function index() {
        // Default: laporan bulan ini
        $start = date('Y-m-01');
        $end = date('Y-m-t');

        if (isset($_GET['start']) && isset($_GET['end'])) {
            $start = $_GET['start'];
            $end = $_GET['end'];
        }

        $stats = $this->model->getDashboardStats();
        $trend = $this->model->getPenjualanTrend(7);
        $barangMenipis = $this->model->getBarangStokMenipis(10);
        require_once __DIR__ . '/../views/laporan/index.php';
    }

    public function pembelian() {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $all_pembelian = $this->model->getLaporanPembelian($start, $end);
        $total = $this->model->getTotalPembelian($start, $end);
        
        // Pagination
        $total_items = count($all_pembelian);
        $total_pages = ceil($total_items / $items_per_page);
        $pembelian = array_slice($all_pembelian, $offset, $items_per_page);
        
        $current_page = $page;
        
        require_once __DIR__ . '/../views/laporan/pembelian.php';
    }

    public function penjualan() {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $all_penjualan = $this->model->getLaporanPenjualan($start, $end);
        $total = $this->model->getTotalPenjualan($start, $end);
        
        // Pagination
        $total_items = count($all_penjualan);
        $total_pages = ceil($total_items / $items_per_page);
        $penjualan = array_slice($all_penjualan, $offset, $items_per_page);
        
        $current_page = $page;
        
        require_once __DIR__ . '/../views/laporan/penjualan.php';
    }

    public function exportPenjualan() {
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $format = $_GET['format'] ?? 'pdf';

        if ($start !== '' && $end !== '') {
            $penjualan = $this->model->getLaporanPenjualan($start, $end);
            $total = $this->model->getTotalPenjualan($start, $end);
        } else {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            $penjualan = $this->model->getLaporanPenjualan($startDate, $endDate);
            $total = $this->model->getTotalPenjualan($startDate, $endDate);
        }

        if ($format === 'excel') {
            $this->exportPenjualanExcel($penjualan, $total, $start, $end);
        } else {
            $this->exportPenjualanPDF($penjualan, $total, $start, $end);
        }
    }

    private function exportPenjualanPDF($penjualan, $total, $start, $end) {
        $timestamp = date('Ymd_His');
        $totalQty = 0;
        foreach ($penjualan as $row) {
            $totalQty += (float)($row['jumlah'] ?? 0);
        }
        
        $periodText = 'Semua Data';
        if ($start && $end) {
            $periodText = date('d M Y', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
        }
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #2563eb;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header .subtitle {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .summary-card {
            padding: 20px;
            background-color: #f3f4f6;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
            text-align: center;
        }
        
        .summary-card .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .summary-card .value {
            font-size: 20px;
            font-weight: 600;
            color: #2563eb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        table thead {
            background-color: #2563eb;
            color: white;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #1e40af;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        
        table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        
        .print-info {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
            .summary-card {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: avoid;
            }
        }
        
        @page {
            margin: 1cm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-info">
            Dicetak: ' . date('d M Y • H:i') . '
        </div>
        
        <div class="header">
            <h1>📊 LAPORAN PENJUALAN</h1>
            <div class="subtitle">Inventori Toko</div>
            <div class="subtitle">Periode: ' . $periodText . '</div>
        </div>
        
        <div class="summary">
            <div class="summary-card">
                <div class="label">Total Penjualan</div>
                <div class="value">Rp ' . number_format($total, 0, ',', '.') . '</div>
            </div>
            <div class="summary-card">
                <div class="label">Jumlah Barang Terjual</div>
                <div class="value">' . number_format($totalQty, 0, ',', '.') . ' Unit</div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Kode Barang</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 28%;">Nama Barang</th>
                    <th style="width: 10%; text-align: center;">Jumlah</th>
                    <th style="width: 15%; text-align: right;">Harga Jual</th>
                    <th style="width: 15%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($penjualan as $index => $item) {
            $html .= '<tr>
                <td class="text-center">' . str_pad($index + 1, 2, '0', STR_PAD_LEFT) . '</td>
                <td>' . htmlspecialchars($item['kode_barang'] ?? '-') . '</td>
                <td>' . date('d M Y', strtotime($item['tanggal'])) . '</td>
                <td><strong>' . htmlspecialchars($item['nama_barang']) . '</strong></td>
                <td class="text-center">' . $item['jumlah'] . '</td>
                <td class="text-right">Rp ' . number_format((float)$item['harga_satuan'], 0, ',', '.') . '</td>
                <td class="text-right"><strong>Rp ' . number_format((float)$item['total_harga'], 0, ',', '.') . '</strong></td>
            </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Laporan ini dicetak secara otomatis dari sistem Inventory Toko</p>
            <p style="margin-top: 10px; font-size: 10px;">Untuk informasi lebih lanjut, silakan hubungi admin</p>
        </div>
    </div>
</body>
</html>';
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="Laporan_Penjualan_' . $timestamp . '.html"');
        echo $html;
        exit;
    }

    private function exportPenjualanExcel($penjualan, $total, $start, $end) {
        $timestamp = date('Ymd_His');
        $filename = 'Laporan_Penjualan_' . $timestamp . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF";

        $period = 'Semua Data';
        if ($start && $end) {
            $period = $start . ' s/d ' . $end;
        }

        fputcsv(STDOUT, ['Laporan Penjualan', $period]);
        fputcsv(STDOUT, []);
        fputcsv(STDOUT, ['No', 'Tanggal', 'Jam', 'Pengguna', 'Kode Barang', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Jual', 'Total']);

        foreach ($penjualan as $index => $item) {
            $tanggal = isset($item['tanggal']) ? date('Y-m-d', strtotime($item['tanggal'])) : '';
            $jam = isset($item['tanggal']) ? date('H:i', strtotime($item['tanggal'])) : '';
            fputcsv(STDOUT, [
                $index + 1,
                $tanggal,
                $jam,
                $item['username'] ?? '-',
                $item['kode_barang'] ?? '-',
                $item['nama_barang'] ?? '',
                $item['jumlah'] ?? 0,
                $item['satuan'] ?? '',
                $item['harga_satuan'] ?? 0,
                $item['total_harga'] ?? 0,
            ]);
        }

        fputcsv(STDOUT, []);
        fputcsv(STDOUT, ['Total Penjualan', $total]);
        exit;
    }

    public function stok() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;
        
        $all_stok = $this->model->getLaporanStok();
        
        // Pagination
        $total_items = count($all_stok);
        $total_pages = ceil($total_items / $items_per_page);
        $stok = array_slice($all_stok, $offset, $items_per_page);
        
        $current_page = $page;
        
        // Get list of kategori
        require_once __DIR__ . '/../models/Barang.php';
        $barangModel = new Barang();
        $kategori = $barangModel->getAllKategori();
        
        require_once __DIR__ . '/../views/laporan/stok.php';
    }

    public function keuntungan() {
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $all_keuntungan = $this->model->getLaporanKeuntungan($start, $end);
        $totalKeuntungan = $this->model->getTotalKeuntungan($start, $end);
        
        // Pagination
        $total_items = count($all_keuntungan);
        $total_pages = ceil($total_items / $items_per_page);
        $keuntungan = array_slice($all_keuntungan, $offset, $items_per_page);
        
        $current_page = $page;
        
        require_once __DIR__ . '/../views/laporan/keuntungan.php';
    }

    public function exportStok() {
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $format = $_GET['format'] ?? 'pdf';

        if ($start !== '' && $end !== '') {
            $stok = $this->model->getLaporanStokRange($start, $end);
        } else {
            $stok = $this->model->getLaporanStok();
        }

        if ($format === 'pdf') {
            $this->exportStokPDF($stok);
        } else {
            $this->exportStokExcel($stok);
        }
    }

    public function exportPembelian() {
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $format = $_GET['format'] ?? 'pdf';

        if ($start !== '' && $end !== '') {
            $pembelian = $this->model->getLaporanPembelian($start, $end);
            $total = $this->model->getTotalPembelian($start, $end);
        } else {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            $pembelian = $this->model->getLaporanPembelian($startDate, $endDate);
            $total = $this->model->getTotalPembelian($startDate, $endDate);
        }

        if ($format === 'excel') {
            $this->exportPembelianExcel($pembelian, $total, $start, $end);
        } else {
            $this->exportPembelianPDF($pembelian, $total, $start, $end);
        }
    }

    private function exportPembelianPDF($pembelian, $total, $start, $end) {
        $timestamp = date('Ymd_His');
        $totalQty = 0;
        foreach ($pembelian as $row) {
            $totalQty += (float)($row['jumlah'] ?? 0);
        }
        
        $periodText = 'Semua Data';
        if ($start && $end) {
            $periodText = date('d M Y', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
        }
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #16a34a;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #16a34a;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header .subtitle {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .summary-card {
            padding: 20px;
            background-color: #f3f4f6;
            border-left: 4px solid #16a34a;
            border-radius: 4px;
            text-align: center;
        }
        
        .summary-card .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .summary-card .value {
            font-size: 20px;
            font-weight: 600;
            color: #16a34a;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        table thead {
            background-color: #16a34a;
            color: white;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #15803d;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        
        table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        
        .print-info {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
            .summary-card {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: avoid;
            }
        }
        
        @page {
            margin: 1cm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-info">
            Dicetak: ' . date('d M Y • H:i') . '
        </div>
        
        <div class="header">
            <h1>📋 LAPORAN PEMBELIAN</h1>
            <div class="subtitle">Inventori Toko</div>
            <div class="subtitle">Periode: ' . $periodText . '</div>
        </div>
        
        <div class="summary">
            <div class="summary-card">
                <div class="label">Total Pembelian</div>
                <div class="value">Rp ' . number_format($total, 0, ',', '.') . '</div>
            </div>
            <div class="summary-card">
                <div class="label">Jumlah Barang</div>
                <div class="value">' . number_format($totalQty, 0, ',', '.') . ' Unit</div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 30%;">Nama Barang</th>
                    <th style="width: 10%; text-align: center;">Jumlah</th>
                    <th style="width: 20%; text-align: right;">Harga Satuan</th>
                    <th style="width: 20%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($pembelian as $index => $item) {
            $html .= '<tr>
                <td class="text-center">' . str_pad($index + 1, 2, '0', STR_PAD_LEFT) . '</td>
                <td>' . date('d M Y', strtotime($item['tanggal'])) . '</td>
                <td><strong>' . htmlspecialchars($item['nama_barang']) . '</strong></td>
                <td class="text-center">' . $item['jumlah'] . '</td>
                <td class="text-right">Rp ' . number_format((float)$item['harga_satuan'], 0, ',', '.') . '</td>
                <td class="text-right"><strong>Rp ' . number_format((float)$item['subtotal'], 0, ',', '.') . '</strong></td>
            </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Laporan ini dicetak secara otomatis dari sistem Inventory Toko</p>
            <p style="margin-top: 10px; font-size: 10px;">Untuk informasi lebih lanjut, silakan hubungi admin</p>
        </div>
    </div>
</body>
</html>';
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="Laporan_Pembelian_' . $timestamp . '.html"');
        echo $html;
        exit;
    }

    private function exportPembelianExcel($pembelian, $total, $start, $end) {
        $timestamp = date('Ymd_His');
        $filename = 'Laporan_Pembelian_' . $timestamp . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF";

        $period = 'Semua Data';
        if ($start && $end) {
            $period = $start . ' s/d ' . $end;
        }

        fputcsv(STDOUT, ['Laporan Pembelian', $period]);
        fputcsv(STDOUT, []);
        fputcsv(STDOUT, ['No', 'Tanggal', 'Jam', 'Kode Barang', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Satuan', 'Subtotal']);

        foreach ($pembelian as $index => $item) {
            $tanggal = isset($item['tanggal']) ? date('Y-m-d', strtotime($item['tanggal'])) : '';
            $jam = isset($item['tanggal']) ? date('H:i', strtotime($item['tanggal'])) : '';
            fputcsv(STDOUT, [
                $index + 1,
                $tanggal,
                $jam,
                $item['kode_barang'] ?? '-',
                $item['nama_barang'] ?? '',
                $item['jumlah'] ?? 0,
                $item['satuan'] ?? '',
                $item['harga_satuan'] ?? 0,
                $item['subtotal'] ?? ($item['total_harga'] ?? 0),
            ]);
        }

        fputcsv(STDOUT, []);
        fputcsv(STDOUT, ['Total Pembelian', $total]);
        exit;
    }

    public function exportKeuntungan() {
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';
        $format = $_GET['format'] ?? 'pdf';

        if ($start !== '' && $end !== '') {
            $keuntungan = $this->model->getLaporanKeuntungan($start, $end);
            $totalKeuntungan = $this->model->getTotalKeuntungan($start, $end);
        } else {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            $keuntungan = $this->model->getLaporanKeuntungan($startDate, $endDate);
            $totalKeuntungan = $this->model->getTotalKeuntungan($startDate, $endDate);
        }

        if ($format === 'excel') {
            $this->exportKeuntunganExcel($keuntungan, $totalKeuntungan, $start, $end);
        } else {
            $this->exportKeuntunganPDF($keuntungan, $totalKeuntungan, $start, $end);
        }
    }

    private function exportKeuntunganPDF($keuntungan, $totalKeuntungan, $start, $end) {
        $timestamp = date('Ymd_His');
        
        $periodText = 'Semua Data';
        if ($start && $end) {
            $periodText = date('d M Y', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
        }
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuntungan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #eab308;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #eab308;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header .subtitle {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }
        
        .summary-card {
            padding: 20px;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
            border-radius: 4px;
            text-align: center;
            margin-bottom: 40px;
        }
        
        .summary-card .label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .summary-card .value {
            font-size: 28px;
            font-weight: 600;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        table thead {
            background-color: #eab308;
            color: #333;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #ca8a04;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        
        table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-green {
            color: #16a34a;
            font-weight: 600;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        
        .print-info {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
            table {
                page-break-inside: avoid;
            }
        }
        
        @page {
            margin: 1cm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-info">
            Dicetak: ' . date('d M Y • H:i') . '
        </div>
        
        <div class="header">
            <h1>💰 LAPORAN KEUNTUNGAN</h1>
            <div class="subtitle">Inventori Toko</div>
            <div class="subtitle">Periode: ' . $periodText . '</div>
        </div>
        
        <div class="summary-card">
            <div class="label">Total Keuntungan Periode Ini</div>
            <div class="value">Rp ' . number_format($totalKeuntungan, 0, ',', '.') . '</div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Tanggal</th>
                    <th style="width: 25%;">Nama Barang</th>
                    <th style="width: 8%; text-align: center;">Jumlah</th>
                    <th style="width: 12%; text-align: right;">Harga Beli</th>
                    <th style="width: 12%; text-align: right;">Harga Jual</th>
                    <th style="width: 13%; text-align: right;">Keuntungan/Unit</th>
                    <th style="width: 13%; text-align: right;">Total Keuntungan</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($keuntungan as $index => $item) {
            $html .= '<tr>
                <td class="text-center">' . str_pad($index + 1, 2, '0', STR_PAD_LEFT) . '</td>
                <td>' . date('d M Y', strtotime($item['tanggal'])) . '</td>
                <td><strong>' . htmlspecialchars($item['nama_barang']) . '</strong></td>
                <td class="text-center">' . $item['jumlah'] . '</td>
                <td class="text-right">Rp ' . number_format((float)$item['harga_beli'], 0, ',', '.') . '</td>
                <td class="text-right">Rp ' . number_format((float)$item['harga_jual'], 0, ',', '.') . '</td>
                <td class="text-right text-green">Rp ' . number_format((float)$item['keuntungan_per_unit'], 0, ',', '.') . '</td>
                <td class="text-right text-green"><strong>Rp ' . number_format((float)$item['keuntungan_total'], 0, ',', '.') . '</strong></td>
            </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Laporan ini dicetak secara otomatis dari sistem Inventory Toko</p>
            <p style="margin-top: 10px; font-size: 10px;">Untuk informasi lebih lanjut, silakan hubungi admin</p>
        </div>
    </div>
</body>
</html>';
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="Laporan_Keuntungan_' . $timestamp . '.html"');
        echo $html;
        exit;
    }

    private function exportKeuntunganExcel($keuntungan, $totalKeuntungan, $start, $end) {
        $timestamp = date('Ymd_His');
        $filename = 'Laporan_Keuntungan_' . $timestamp . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF";

        $period = 'Semua Data';
        if ($start && $end) {
            $period = $start . ' s/d ' . $end;
        }

        fputcsv(STDOUT, ['Laporan Keuntungan', $period]);
        fputcsv(STDOUT, []);
        fputcsv(STDOUT, ['No', 'Tanggal', 'Jam', 'Pengguna', 'Kode Barang', 'Nama Barang', 'Jumlah', 'Satuan', 'Harga Beli', 'Harga Jual', 'Keuntungan/Unit', 'Total Keuntungan']);

        foreach ($keuntungan as $index => $item) {
            $tanggal = isset($item['tanggal']) ? date('Y-m-d', strtotime($item['tanggal'])) : '';
            $jam = isset($item['tanggal']) ? date('H:i', strtotime($item['tanggal'])) : '';
            fputcsv(STDOUT, [
                $index + 1,
                $tanggal,
                $jam,
                $item['username'] ?? '-',
                $item['kode_barang'] ?? '-',
                $item['nama_barang'] ?? '',
                $item['jumlah'] ?? 0,
                $item['satuan'] ?? '',
                $item['harga_beli'] ?? 0,
                $item['harga_jual'] ?? 0,
                $item['keuntungan_per_unit'] ?? 0,
                $item['keuntungan_total'] ?? 0,
            ]);
        }

        fputcsv(STDOUT, []);
        fputcsv(STDOUT, ['Total Keuntungan', $totalKeuntungan]);
        exit;
    }

    private function exportStokPDF($stok) {
        $timestamp = date('Ymd_His');
        
        // Calculate totals
        $totalBeli = 0;
        $totalJual = 0;
        $totalStok = 0;
        foreach ($stok as $item) {
            $totalBeli += (float)$item['harga_beli'];
            $totalJual += (float)$item['harga_jual'];
            $totalStok += (int)$item['stok'];
        }
        
        // Generate HTML for PDF
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Stok Barang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #1e40af;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .header .subtitle {
            font-size: 13px;
            color: #666;
            margin: 5px 0;
        }
        
        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .summary-card {
            padding: 20px;
            background-color: #f3f4f6;
            border-left: 4px solid #1e40af;
            border-radius: 4px;
            text-align: center;
        }
        
        .summary-card .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .summary-card .value {
            font-size: 20px;
            font-weight: 600;
            color: #1e40af;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        table thead {
            background-color: #1e40af;
            color: white;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #1e3a8a;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        
        table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .no-column {
            width: 5%;
        }
        
        .kode-column {
            width: 12%;
        }
        
        .nama-column {
            width: 28%;
        }
        
        .satuan-column {
            width: 10%;
        }
        
        .harga-column {
            width: 15%;
        }
        
        .stok-column {
            width: 8%;
        }
        
        .status-column {
            width: 12%;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-habis {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-kritis {
            background-color: #fed7aa;
            color: #92400e;
        }
        
        .badge-rendah {
            background-color: #fef3c7;
            color: #78350f;
        }
        
        .badge-aman {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        
        .print-info {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 20px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 100%;
                padding: 0;
            }
            .summary-card {
                page-break-inside: avoid;
            }
            table {
                page-break-inside: avoid;
            }
        }
        
        @page {
            margin: 1cm;
            size: A4;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-info">
            Dicetak: ' . date('d M Y • H:i') . '
        </div>
        
        <div class="header">
            <h1>📦 LAPORAN STOK BARANG</h1>
            <div class="subtitle">Inventori Toko</div>
            <div class="subtitle">Data terupdate per ' . date('d F Y') . '</div>
        </div>
        
        <div class="summary">
            <div class="summary-card">
                <div class="label">Total Harga Beli</div>
                <div class="value">Rp ' . number_format($totalBeli, 0, ',', '.') . '</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Harga Jual</div>
                <div class="value">Rp ' . number_format($totalJual, 0, ',', '.') . '</div>
            </div>
            <div class="summary-card">
                <div class="label">Total Stok</div>
                <div class="value">' . number_format($totalStok, 0, ',', '.') . ' Unit</div>
            </div>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="no-column text-center">No</th>
                    <th class="kode-column text-left">Kode Barang</th>
                    <th class="nama-column">Nama Barang</th>
                    <th class="satuan-column text-center">Satuan</th>
                    <th class="harga-column text-right">Harga Beli</th>
                    <th class="harga-column text-right">Harga Jual</th>
                    <th class="stok-column text-center">Stok</th>
                    <th class="status-column text-center">Status</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($stok as $index => $item) {
            $status = 'AMAN';
            $statusBadge = 'badge-aman';
            
            if ($item['stok'] == 0) {
                $status = 'HABIS';
                $statusBadge = 'badge-habis';
            } elseif ($item['stok'] <= 5) {
                $status = 'KRITIS';
                $statusBadge = 'badge-kritis';
            } elseif ($item['stok'] <= 10) {
                $status = 'RENDAH';
                $statusBadge = 'badge-rendah';
            }
            
            $hargaBeli = number_format((float)$item['harga_beli'], 0, ',', '.');
            $hargaJual = number_format((float)$item['harga_jual'], 0, ',', '.');
            
            $html .= '<tr>
                <td class="no-column text-center">' . str_pad($index + 1, 2, '0', STR_PAD_LEFT) . '</td>
                <td class="kode-column text-left font-mono">' . htmlspecialchars($item['kode_barang'] ?? '-') . '</td>
                <td class="nama-column"><strong>' . htmlspecialchars($item['nama_barang']) . '</strong></td>
                <td class="satuan-column text-center">' . htmlspecialchars($item['satuan'] ?? '-') . '</td>
                <td class="harga-column text-right">Rp ' . $hargaBeli . '</td>
                <td class="harga-column text-right">Rp ' . $hargaJual . '</td>
                <td class="stok-column text-center"><strong>' . $item['stok'] . '</strong></td>
                <td class="status-column text-center"><span class="badge ' . $statusBadge . '">' . $status . '</span></td>
            </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        
        <div class="footer">
            <p>Laporan ini dicetak secara otomatis dari sistem Inventory Toko</p>
            <p style="margin-top: 10px; font-size: 10px;">Untuk informasi lebih lanjut, silakan hubungi admin</p>
        </div>
    </div>
</body>
</html>';
        
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="Laporan_Stok_' . $timestamp . '.html"');
        echo $html;
        exit;
    }

    private function exportStokExcel($stok) {
        $timestamp = date('Ymd_His');
        $filename = 'Laporan_Stok_' . $timestamp . '.csv';
        
        // Generate proper CSV format
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // UTF-8 BOM
        echo "\xEF\xBB\xBF";
        
        // Header row
        fputcsv(STDOUT, ['No', 'Kode', 'Nama Barang', 'Satuan', 'Harga Beli', 'Harga Jual', 'Stok', 'Status']);
        
        // Data rows
        foreach ($stok as $index => $item) {
            $status = 'Aman';
            if ($item['stok'] == 0) {
                $status = 'Habis';
            } elseif ($item['stok'] <= 5) {
                $status = 'Kritis';
            } elseif ($item['stok'] <= 10) {
                $status = 'Rendah';
            }
            
            fputcsv(STDOUT, [
                $index + 1,
                $item['kode_barang'] ?? '-',
                $item['nama_barang'],
                $item['satuan'],
                $item['harga_beli'],
                $item['harga_jual'],
                $item['stok'],
                $status
            ]);
        }
        
        exit;
    }
}
