<?php
/**
 * Embedded schema for Railway deployment
 * This file contains the complete database schema as a PHP string
 * Used when skema_postgresql.sql file is not accessible
 */

function getEmbeddedSchema(): string {
    return <<<'SQL'
-- Database Schema untuk Sistem Inventori Toko - PostgreSQL
-- Catatan: CREATE DATABASE dan \c tidak dijalankan di Railway
-- Script ini otomatis dijalankan oleh migrate.php

-- Tabel Users
CREATE TABLE IF NOT EXISTS users (
    id_user SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori
CREATE TABLE IF NOT EXISTS kategori (
    id_kategori SERIAL PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Satuan
CREATE TABLE IF NOT EXISTS satuan (
    id_satuan SERIAL PRIMARY KEY,
    nama_satuan VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Barang
CREATE TABLE IF NOT EXISTS barang (
    id_barang SERIAL PRIMARY KEY,
    kode_barang VARCHAR(50) UNIQUE NOT NULL,
    nama_barang VARCHAR(200) NOT NULL,
    id_kategori INTEGER REFERENCES kategori(id_kategori),
    id_satuan INTEGER REFERENCES satuan(id_satuan),
    harga_beli DECIMAL(15,2) DEFAULT 0,
    harga_jual DECIMAL(15,2) DEFAULT 0,
    stok INTEGER DEFAULT 0,
    stok_minimum INTEGER DEFAULT 0,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pembelian
CREATE TABLE IF NOT EXISTS pembelian (
    id_pembelian SERIAL PRIMARY KEY,
    no_faktur VARCHAR(50) UNIQUE NOT NULL,
    tanggal_pembelian DATE NOT NULL,
    supplier VARCHAR(200),
    total_pembelian DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Detail Pembelian
CREATE TABLE IF NOT EXISTS detail_pembelian (
    id_detail_pembelian SERIAL PRIMARY KEY,
    id_pembelian INTEGER REFERENCES pembelian(id_pembelian) ON DELETE CASCADE,
    id_barang INTEGER REFERENCES barang(id_barang),
    jumlah INTEGER NOT NULL,
    harga_beli DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL
);

-- Tabel Penjualan
CREATE TABLE IF NOT EXISTS penjualan (
    id_penjualan SERIAL PRIMARY KEY,
    no_nota VARCHAR(50) UNIQUE NOT NULL,
    tanggal_penjualan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INTEGER REFERENCES users(id_user),
    total_penjualan DECIMAL(15,2) DEFAULT 0,
    bayar DECIMAL(15,2) DEFAULT 0,
    kembalian DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Detail Penjualan
CREATE TABLE IF NOT EXISTS detail_penjualan (
    id_detail_penjualan SERIAL PRIMARY KEY,
    id_penjualan INTEGER REFERENCES penjualan(id_penjualan) ON DELETE CASCADE,
    id_barang INTEGER REFERENCES barang(id_barang),
    jumlah INTEGER NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL
);

-- Tabel Hutang
CREATE TABLE IF NOT EXISTS hutang (
    id_hutang SERIAL PRIMARY KEY,
    id_penjualan INTEGER REFERENCES penjualan(id_penjualan) ON DELETE CASCADE,
    nama_pelanggan VARCHAR(200) NOT NULL,
    total_hutang DECIMAL(15,2) NOT NULL,
    dibayar DECIMAL(15,2) DEFAULT 0,
    sisa_hutang DECIMAL(15,2) NOT NULL,
    jatuh_tempo DATE,
    status VARCHAR(20) DEFAULT 'belum_lunas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Konfigurasi Nota
CREATE TABLE IF NOT EXISTS konfigurasi_nota (
    id_config SERIAL PRIMARY KEY,
    nama_toko VARCHAR(200) NOT NULL,
    alamat_toko TEXT,
    telepon_toko VARCHAR(20),
    header_nota TEXT,
    footer_nota TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes untuk performa
CREATE INDEX IF NOT EXISTS idx_barang_kode ON barang(kode_barang);
CREATE INDEX IF NOT EXISTS idx_barang_kategori ON barang(id_kategori);
CREATE INDEX IF NOT EXISTS idx_pembelian_tanggal ON pembelian(tanggal_pembelian);
CREATE INDEX IF NOT EXISTS idx_penjualan_tanggal ON penjualan(tanggal_penjualan);
CREATE INDEX IF NOT EXISTS idx_hutang_status ON hutang(status);
SQL;
}
?>
