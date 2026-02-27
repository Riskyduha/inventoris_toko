# Deploy ke VPS (format sama seperti project Node Anda)

## 1) Persiapan server (sekali saja)
Jalankan di VPS:

```bash
sudo apt-get update
sudo apt-get install -y docker.io docker-compose-plugin
sudo usermod -aG docker $USER
newgrp docker
```

Buat folder project di VPS (contoh):

```bash
mkdir -p /root/inventoris_toko
```

Buat `.env` production di folder project VPS:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com
TIMEZONE=Asia/Jakarta

DB_HOST=<host-postgres-anda>
DB_PORT=5432
DB_NAME=toko_inventori
DB_USER=<user-db>
DB_PASS=<password-db>
```

## 2) GitHub Secrets yang wajib
Isi di `Settings > Secrets and variables > Actions`:

- `VPS_HOST`
- `VPS_USER`
- `VPS_SSH_KEY`
- `VPS_SSH_PORT`
- `VPS_APP_DIR` (contoh: `/root/inventoris_toko`)

## 3) Cara deploy
Setiap `push` ke branch `main`, workflow akan:

1. `rsync` source code ke VPS (tanpa menimpa `.env`)
2. Pastikan network Docker `web` tersedia
3. Jalankan `docker compose up -d --build --remove-orphans`

Workflow: `.github/workflows/deploy-server.yml`

## 4) Catatan jaringan
`docker-compose.yml` proyek ini sudah memakai:

```yaml
networks:
  web:
    external: true
```

Ini cocok dengan setup Caddy reverse-proxy di server Anda.
