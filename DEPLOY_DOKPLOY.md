# 🚀 Panduan Deployment Dokploy

Proyek ini telah dikonfigurasi dan dioptimalkan agar dapat berjalan dengan mulus di platform **Dokploy** menggunakan metode deployment **Application (Dockerfile)**.

## 📋 Prasyarat
1. Anda sudah memiliki server/VPS dengan **Dokploy** yang sudah terinstall.
2. Kode proyek ini sudah di-push ke repository GitHub Anda (bisa public atau private).
3. Anda sudah menyiapkan database (baik MySQL/MariaDB atau PostgreSQL). 

## 🛠 Langkah-langkah Deployment di Dokploy

### 1. Buat Aplikasi Baru di Dokploy
1. Buka dashboard Dokploy Anda.
2. Navigasi ke menu **Applications** dan klik **Create Application**.
3. Beri nama aplikasi Anda (misalnya: `inventoris-toko`).

### 2. Hubungkan ke Repository GitHub
1. Pada tab **General**, pilih sumber (Source) dari **GitHub**.
2. Pilih repository `inventoris_toko` Anda.
3. Pilih branch yang akan di-deploy (contoh: `main`).
4. Klik **Save**.

### 3. Konfigurasi Build Type (Penting!)
Dokploy harus tahu bagaimana membangun aplikasi ini. Karena kita sudah menyediakan file `Dockerfile` khusus:
1. Pindah ke tab **Build**.
2. Pada opsi **Build Type**, pilih **Dockerfile**.
3. Pastikan **Dockerfile Path** terisi dengan `/Dockerfile` atau dikosongkan (default).
4. Klik **Save**.

### 4. Atur Environment Variables (.env)
Aplikasi PHP ini membutuhkan konfigurasi database agar dapat berjalan:
1. Pindah ke tab **Environment**.
2. Masukkan konfigurasi `.env` production Anda. Contoh:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-inventori-anda.com
TIMEZONE=Asia/Jakarta

DB_HOST=<ip_atau_nama_container_database>
DB_PORT=5432
DB_NAME=toko_inventori
DB_USER=<user_database>
DB_PASS=<password_database>

# Dokploy default port routing (jangan diubah)
PORT=80
```
3. Klik **Save**.

### 5. Atur Domain (Opsional tapi Direkomendasikan)
1. Pindah ke tab **Domains**.
2. Tambahkan domain atau subdomain yang ingin Anda gunakan (contoh: `inventori.domain-anda.com`).
3. Port internal biarkan ke **80** (sesuai konfigurasi Nginx pada `Dockerfile`).
4. Aktifkan opsi **HTTPS** jika domain sudah di-pointing ke IP Dokploy Anda.
5. Klik **Save**.

### 6. Deploy Aplikasi
1. Kembali ke tab **General** atau tab **Deployments**.
2. Klik tombol **Deploy**.
3. Tunggu proses build (biasanya 1-3 menit). Anda bisa memantau prosesnya dengan mengklik tombol *View Logs*.

---

## 🎯 Setelah Berhasil Deploy

Jika aplikasi sudah berjalan (*Running*), Anda dapat mengakses domain yang sudah di-set tadi.

Gunakan kredensial default untuk login (pastikan untuk langsung mengubahnya setelah berhasil masuk!):
- **Username:** `admin`
- **Password:** `admin123`

### 💡 Troubleshooting
Jika terjadi *Error 502 Bad Gateway* atau aplikasi gagal terhubung ke database:
1. Cek tab **Logs** di Dokploy, periksa apakah ada error koneksi ke database.
2. Pastikan `DB_HOST`, `DB_PORT`, dan kredensial database di tab Environment sudah 100% benar.
3. Pastikan jaringan antara database dan aplikasi (jika database juga di-host di Dokploy) dapat saling berkomunikasi. Anda tidak perlu setup jaringan tambahan, Dokploy akan otomatis me-routing jika berada di VPS yang sama.

🎉 **Selesai! Aplikasi Inventori Toko Anda sekarang online via Dokploy.**
