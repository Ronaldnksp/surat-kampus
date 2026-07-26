# Panduan Deployment ke Cloud

## Arsitektur Cloud

```
                    ┌─────────────────┐
                    │     GitHub      │
                    │    Actions      │
                    └────────┬────────┘
                             │ CI/CD
                    ┌────────▼────────┐
                    │    Railway /    │
                    │     Render      │ ← PHP App
                    └────────┬────────┘
                             │
            ┌────────────────┼────────────────┐
            │                │                │
     ┌──────▼──────┐  ┌─────▼─────┐  ┌───────▼───────┐
     │  Managed    │  │  Object   │  │     SMTP      │
     │  Database   │  │  Storage  │  │    Service    │
     │ (MySQL)     │  │ (R2 / S3) │  │  (Mailtrap)   │
     └─────────────┘  └───────────┘  └───────────────┘
```

## Option 1: Railway (Recommended)

### Langkah Deployment:

1. **Buat akun GitHub** (jika belum ada)

2. **Push kode ke GitHub:**
   ```bash
   git init
   git add .
   git commit -m "Initial commit"
   git remote add origin https://github.com/USERNAME/surat-kampus.git
   git push -u origin main
   ```

3. **Buka Railway:**
   - Kunjungi: https://railway.app/
   - Login dengan GitHub

4. **Create New Project:**
   - Klik **New Project**
   - Pilih **Deploy from GitHub repo**
   - Pilih repository `surat-kampus`

5. **Tambah MySQL Database:**
   - Klik **New** → **Database** → **MySQL**
   - Railway akan memberikan koneksi database

6. **Setup Environment Variables:**
   ```
   DB_HOST=mysql.railway.internal
   DB_NAME=railway
   DB_USER=root
   DB_PASSWORD=<dari Railway>
   APP_URL=https://surat-kampus.up.railway.app
   ```

7. **Import Database:**
   - Buka MySQL client (phpMyAdmin Online)
   - Import file `database/surat_kampus.sql`

8. **Deploy:**
   - Railway akan otomatis deploy
   - Dapat URL: `https://surat-kampus.up.railway.app`

### Estimasi Biaya:
| Service | Plan | Cost/Bulan |
|---------|------|------------|
| Web Service | Starter | $5 |
| MySQL | Starter | $5 |
| **Total** | | **$10** |

---

## Option 2: Render

### Langkah Deployment:

1. **Buka Render:**
   - Kunjungi: https://render.com/
   - Login dengan GitHub

2. **Create Web Service:**
   - Klik **New** → **Web Service**
   - Connect GitHub repository
   - Pilih repository `surat-kampus`

3. **Konfigurasi:**
   - Name: `surat-kampus`
   - Runtime: `PHP`
   - Build Command: `apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev && docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd pdo pdo_mysql`
   - Start Command: `apache2-foreground`

4. **Environment Variables:**
   ```
   DB_HOST=<Render MySQL host>
   DB_NAME=<database name>
   DB_USER=<username>
   DB_PASS=<password>
   APP_URL=https://surat-kampus.onrender.com
   ```

5. **Tambah MySQL:**
   - Klik **New** → **MySQL**
   - Copy connection details

6. **Deploy:**
   - Render akan build dan deploy
   - URL: `https://surat-kampus.onrender.com`

### Estimasi Biaya:
| Service | Plan | Cost/Bulan |
|---------|------|------------|
| Web Service | Starter | $7 |
| MySQL | Starter | $7 |
| **Total** | | **$14** |

---

## Option 3: VPS (DigitalOcean/Hetzner)

### Langkah Deployment:

1. **Buat VPS:**
   - DigitalOcean: https://www.digitalocean.com/
   - Hetzner: https://www.hetzner.com/

2. **Install Docker:**
   ```bash
   curl -fsSL https://get.docker.com -o get-docker.sh
   sh get-docker.sh
   ```

3. **Clone repository:**
   ```bash
   git clone https://github.com/USERNAME/surat-kampus.git
   cd surat-kampus
   ```

4. **Jalankan:**
   ```bash
   docker-compose up -d
   ```

5. **Setup Nginx Reverse Proxy:**
   ```nginx
   server {
       listen 80;
       server_name surat-kampus.com;
       
       location / {
           proxy_pass http://localhost:8080;
       }
   }
   ```

### Estimasi Biaya:
| Service | Plan | Cost/Bulan |
|---------|------|------------|
| VPS (2GB) | Basic | $10-15 |
| Domain | - | $1 |
| **Total** | | **$11-16** |

---

## Konsep Cloud Computing yang Diterapkan

### 1. Managed Database
- **Railway/Render MySQL:** Database terkelola, otomatis backup, scaling
- **Bukan:** MySQL install manual di server

### 2. Environment/Secrets Management
- **Environment Variables:** Kredensial database tidak hardcode
- **Railway/Render Dashboard:** Kelola secrets tanpa edit kode

### 3. Object Storage (Optional)
- **Cloudflare R2 / AWS S3:** Untuk file upload
- **Bukan:** Simpan di disk lokal server

### 4. Auto-scaling (Optional)
- **Railway:** Otomatis scale berdasarkan traffic
- **Render:** Auto-scaling di plan premium

### 5. CI/CD (Optional)
- **GitHub Actions:** Auto-deploy saat push ke main
- **Railway/Render:** Otomatis deploy dari GitHub

---

## Checklist Deployment

- [ ] Repository di GitHub
- [ ] Database ter-import
- [ ] Environment variables ter-set
- [ ] Aplikasi bisa diakses publik
- [ ] Login bisa dilakukan
- [ ] Fitur CRUD berjalan
- [ ] File upload berfungsi
- [ ] Notifikasi muncul
- [ ] Cron job berjalan

---

## Monitoring & Maintenance

### Logging
- **Railway:** Logs di dashboard
- **Render:** Logs di dashboard

### Backups
- **Managed Database:** Otomatis backup harian
- **File Upload:** Manual backup ke cloud storage

### Scaling
- **Railway/Render:** Upgrade plan sesuai kebutuhan
- **VPS:** Upgrade RAM/CPU
