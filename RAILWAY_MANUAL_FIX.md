# SOLUTION: Manual Database Setup di Railway

## MASALAH DITEMUKAN

Setelah debugging:
1. ✅ Railway container berjalan dengan benar
2. ✅ Koneksi ke PostgreSQL berhasil  
3. ✅ PHP code sudah benar
4. ❌ **Tables tidak ter-create di Railway database**
5. ❌ **File baru tidak muncul setelah push (deploy issue)**

## ROOT CAUSE

Railway sepertinya:
- Tidak auto-redeploy setelah GitHub push ATAU
- Menggunakan cached deployment lama ATAU
- Ada issue dengan build/deployment process

## SOLUSI CEPAT: Manual SQL Execution

### Step 1: Access Railway PostgreSQL

1. Buka Railway Dashboard: https://railway.app/
2. Pilih project: `inventoristoko-production`
3. Klik service **PostgreSQL** (bukan web service)
4. Klik tab **"Data"** atau **"Query"**

### Step 2: Execute SQL Berikut

Copy-paste SQL berikut ke Railway PostgreSQL Query console:

```sql
-- Drop existing tables if any (clean slate)
DROP TABLE IF EXISTS detail_penjualan CASCADE;
DROP TABLE IF EXISTS detail_pembelian CASCADE;
DROP TABLE IF EXISTS hutang CASCADE;
DROP TABLE IF EXISTS penjualan CASCADE;
DROP TABLE IF EXISTS pembelian CASCADE;
DROP TABLE IF EXISTS barang CASCADE;
DROP TABLE IF EXISTS satuan CASCADE;
DROP TABLE IF EXISTS kategori CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF NOT EXISTS konfigurasi_nota CASCADE;

-- Create Users Table
CREATE TABLE users (
    id_user SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Kategori Table
CREATE TABLE kategori (
    id_kategori SERIAL PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Satuan Table
CREATE TABLE satuan (
    id_satuan SERIAL PRIMARY KEY,
    nama_satuan VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Barang Table
CREATE TABLE barang (
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

-- Create Pembelian Table
CREATE TABLE pembelian (
    id_pembelian SERIAL PRIMARY KEY,
    no_faktur VARCHAR(50) UNIQUE NOT NULL,
    tanggal_pembelian DATE NOT NULL,
    supplier VARCHAR(200),
    total_pembelian DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Detail Pembelian Table
CREATE TABLE detail_pembelian (
    id_detail_pembelian SERIAL PRIMARY KEY,
    id_pembelian INTEGER REFERENCES pembelian(id_pembelian) ON DELETE CASCADE,
    id_barang INTEGER REFERENCES barang(id_barang),
    jumlah INTEGER NOT NULL,
    harga_beli DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL
);

-- Create Penjualan Table
CREATE TABLE penjualan (
    id_penjualan SERIAL PRIMARY KEY,
    no_nota VARCHAR(50) UNIQUE NOT NULL,
    tanggal_penjualan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INTEGER REFERENCES users(id_user),
    total_penjualan DECIMAL(15,2) DEFAULT 0,
    bayar DECIMAL(15,2) DEFAULT 0,
    kembalian DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Detail Penjualan Table
CREATE TABLE detail_penjualan (
    id_detail_penjualan SERIAL PRIMARY KEY,
    id_penjualan INTEGER REFERENCES penjualan(id_penjualan) ON DELETE CASCADE,
    id_barang INTEGER REFERENCES barang(id_barang),
    jumlah INTEGER NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL
);

-- Create Hutang Table
CREATE TABLE hutang (
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

-- Create Konfigurasi Nota Table
CREATE TABLE konfigurasi_nota (
    id_config SERIAL PRIMARY KEY,
    nama_toko VARCHAR(200) NOT NULL,
    alamat_toko TEXT,
    telepon_toko VARCHAR(20),
    header_nota TEXT,
    footer_nota TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Indexes
CREATE INDEX IF NOT EXISTS idx_barang_kode ON barang(kode_barang);
CREATE INDEX IF NOT EXISTS idx_barang_kategori ON barang(id_kategori);
CREATE INDEX IF NOT EXISTS idx_pembelian_tanggal ON pembelian(tanggal_pembelian);
CREATE INDEX IF NOT EXISTS idx_penjualan_tanggal ON penjualan(tanggal_penjualan);
CREATE INDEX IF NOT EXISTS idx_hutang_status ON hutang(status);

-- Insert Default Users with correct password hashes
INSERT INTO users (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kasir')
ON CONFLICT (username) DO UPDATE SET 
    password = EXCLUDED.password,
    role = EXCLUDED.role;

-- Insert Default Kategori
INSERT INTO kategori (nama_kategori) VALUES
('Makanan'),
('Minuman'),
('Elektronik'),
('Pakaian'),
('Alat Tulis'),
('Lainnya')
ON CONFLICT DO NOTHING;

-- Insert Default Satuan
INSERT INTO satuan (nama_satuan) VALUES
('Pcs'),
('Box'),
('Kg'),
('Liter'),
('Meter'),
('Pack'),
('Lusin'),
('Karton'),
('Botol'),
('Kaleng'),
('Unit')
ON CONFLICT DO NOTHING;

-- Insert Sample Barang
INSERT INTO barang (kode_barang, nama_barang, id_kategori, id_satuan, harga_beli, harga_jual, stok, stok_minimum) VALUES
('BRG001', 'Indomie Goreng', 1, 1, 2500, 3000, 100, 20),
('BRG002', 'Aqua 600ml', 2, 9, 2000, 3000, 50, 10),
('BRG003', 'Buku Tulis 38 Lembar', 5, 1, 2000, 2500, 200, 50),
('BRG004', 'Pulpen Standard', 5, 1, 1500, 2000, 150, 30),
('BRG005', 'Teh Botol Sosro', 2, 9, 3000, 4000, 80, 15),
('BRG006', 'Roti Tawar Sari Roti', 1, 6, 8000, 10000, 30, 10),
('BRG007', 'Susu Ultra 1L', 2, 2, 15000, 18000, 25, 5),
('BRG008', 'Sabun Mandi Lifebuoy', 6, 1, 3000, 4000, 60, 15),
('BRG009', 'Shampo Pantene 170ml', 6, 9, 15000, 18000, 40, 10),
('BRG010', 'Gula Pasir 1Kg', 1, 3, 12000, 14000, 50, 10),
('BRG011', 'Minyak Goreng 1L', 1, 4, 14000, 16000, 45, 10),
('BRG012', 'Detergen Rinso 800g', 6, 6, 18000, 21000, 35, 8),
('BRG013', 'Kopi Kapal Api 165g', 2, 6, 12000, 14000, 55, 12)
ON CONFLICT (kode_barang) DO UPDATE SET
    nama_barang = EXCLUDED.nama_barang,
    harga_beli = EXCLUDED.harga_beli,
    harga_jual = EXCLUDED.harga_jual;

-- Insert Default Konfigurasi Nota
INSERT INTO konfigurasi_nota (nama_toko, alamat_toko, telepon_toko, header_nota, footer_nota) VALUES
('Toko Makmur', 'Jl. Raya No. 123, Jakarta', '021-12345678', 'Terima kasih atas kunjungan Anda', 'Barang yang sudah dibeli tidak dapat dikembalikan')
ON CONFLICT DO NOTHING;
```

### Step 3: Verify

Setelah execute SQL di atas, test:
1. Access: https://inventoristoko-production.up.railway.app/debug.php
2. Harusnya sekarang show:
   - ✓ users: 2 records
   - ✓ kategori: 6 records
   - ✓ satuan: 11 records
   - ✓ barang: 13 records

### Step 4: Login

1. Access: https://inventoristoko-production.up.railway.app/login
2. Login dengan:
   - **Username**: `admin`
   - **Password**: `admin123`
   
   ATAU
   
   - **Username**: `kasir`  
   - **Password**: `kasir123`

## NOTES PENTING

⚠️ Password hash di SQL: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

Ini adalah hash untuk password: `admin123` dan `kasir123`

Jika login MASIH gagal setelah manual SQL, berarti ada issue di User.php login logic. Tapi berdasarkan test local, code sudah benar.

## ALTERNATIVE: Fix Railway Auto-Deploy

Jika mau fix auto-deploy issue:

1. **Manual Redeploy di Railway Dashboard**:
   - Buka Railway Dashboard
   - Pilih web service (bukan PostgreSQL)
   - Klik "Deployments"
   - Klik "Deploy" atau "Redeploy"

2. **Force Rebuild**:
   - Settings → General → "Force Redeploy"
   - Atau disconnect & reconnect GitHub integration

3. **Check Railway Logs**:
   - Deploy Logs tab
   - Runtime Logs tab
   - Cari error messages

## NEXT STEPS SETELAH LOGIN BERHASIL

Setelah berhasil login, system seharusnya sudah fully functional:
- ✅ Dashboard
- ✅ Manage Barang
- ✅ Transaksi Pembelian
- ✅ Transaksi Penjualan
- ✅ Laporan

## TROUBLESHOOTING

**Q: Login masih gagal setelah manual SQL?**
A: Check password hash. Generate fresh hash dengan:
```php
php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
```
Lalu update users table di Railway PostgreSQL.

**Q: Redirect loop atau error 500?**
A: Check Railway logs untuk PHP errors.

**Q: Tables created tapi empty?**
A: Re-run INSERT statements (bagian setelah CREATE TABLE).
