# 📋 Action Plan - Deploy ke Railway dengan Migration Fix

## Ringkasan Perbaikan

Sudah diperbaiki masalah migration yang menyebabkan table `users` tidak terbuat di Railway:

✅ **Path schema file diperbaiki** - Sudah sesuai dengan struktur container
✅ **Table existence check diperbaiki** - Menggunakan simple query (lebih compatible)
✅ **Logging ditambah** - Sekarang error/progress akan terlihat di Railway logs
✅ **Troubleshooting guide dibuat** - Untuk reference jika ada issue

## Step-by-Step untuk Deploy

### 1️⃣ Verify di Local Dulu

```bash
cd /Users/riskyduha/Documents/Toko/toko-inventori

# Test migration script
php test_migration.php

# Expected output:
# ✓ ALL TESTS PASSED - Migration working correctly!
```

### 2️⃣ Push ke GitHub (Sudah Done ✅)

```bash
git status  # Pastikan working tree clean
git log --oneline -5  # Lihat commit terakhir

# Output seharusnya:
# f66ca0d Add comprehensive troubleshooting guide
# 9fbb977 fix: Use simple query to check table existence
# 9254096 improve: Add detailed logging to migration and seed
# 49caeae fix: Correct schema file path for Railway container
```

### 3️⃣ Redeploy di Railway

**Option A: Auto-redeploy (recommended)**
- Push ke GitHub (already done)
- Railway otomatis detect → Build → Deploy
- Wait ~2-3 minutes

**Option B: Manual trigger**
1. Buka Railway Dashboard
2. Pilih project
3. Klik "Redeploy" button
4. Wait untuk build selesai

### 4️⃣ Monitor Logs di Railway

```
Railway Dashboard → Project → Logs
```

**Look untuk message:**

```
Starting Nginx + PHP-FPM
Migration check: users table DOES NOT EXIST (first time)
Looking for schema at: /app/database/skema_postgresql.sql
Schema file found, starting migration...
Total statements to execute: 45
Migration completed: 9 table statements executed
Seed check started (FORCE=false)
Seed check completed
```

### 5️⃣ Test Application

Setelah deploy selesai:

1. **Buka aplikasi:**
   ```
   https://your-railway-app-url/
   ```

2. **Test login:**
   - Username: `admin`
   - Password: `admin123`

3. **Harusnya:**
   - Login berhasil
   - Redirect ke dashboard
   - Bisa buat/edit data

### 6️⃣ Jika Masih Error

**Cek logs lebih detail:**
```bash
# Railway CLI (jika installed)
railway logs --all

# Atau lihat di Railway Dashboard → Logs
```

**Common issues & solutions:**
- "Schema file not found" → Check TROUBLESHOOTING.md
- "users table does not exist" → Check TROUBLESHOOTING.md
- Login gagal → Check TROUBLESHOOTING.md

## File yang Berubah

```
app/config/migrate.php      ← NEW (create tables)
app/config/database.php     ← MODIFIED (call migration)
app/config/seed.php         ← MODIFIED (better logging)
database/skema_postgresql.sql ← MODIFIED (IF NOT EXISTS)
MIGRATION.md                ← NEW (documentation)
DEPLOY_RAILWAY.md           ← NEW (deployment guide)
TROUBLESHOOTING.md          ← NEW (debugging guide)
test_migration.php          ← NEW (test script)
```

## Timeline

| Event | Time |
|-------|------|
| Migration fix committed | ✅ Done |
| Push to GitHub | ✅ Done |
| Railway Redeploy | ⏳ Next |
| Build & Deploy | ~2-3 min |
| Test Login | ~5 min |
| **Total** | **~10 min** |

## Success Criteria

✅ Deploy selesai tanpa error
✅ Migration logs muncul di Railway logs
✅ 9 tables berhasil dibuat
✅ Data seed tersimpan
✅ Login dengan admin/admin123 berhasil
✅ Dashboard menampilkan data

## Rollback (Jika Dibutuhkan)

```bash
# Revert ke commit sebelum ini
git revert f66ca0d
git push origin main

# Railway otomatis redeploy dengan kode sebelumnya
```

---

**Status:** Ready for deployment ✅
**Next Step:** Redeploy di Railway dan monitor logs
