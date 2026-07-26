# Sistem Pengajuan Surat Perizinan Kampus

Aplikasi web pengajuan surat perizinan online untuk mahasiswa, staff administrasi, dan dekan.

## Live Demo

🔗 **https://surat-kampus-production-328c.up.railway.app**

## Tech Stack

- **Backend:** PHP 8.2 (Native)
- **Database:** MySQL 8.0 (Railway)
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** Railway (Docker + PHP CLI)
- **Cloud Platform:** Railway

## Fitur Utama

- ✅ Role-based Access Control (Mahasiswa, Staff, Dekan)
- ✅ Form pengajuan surat dengan file upload
- ✅ Approval workflow (Staff → Dekan)
- ✅ Notifikasi in-app
- ✅ Auto-reject via cron job
- ✅ Dashboard statistik
- ✅ Activity logging

## Requirements

- PHP >= 8.2
- MySQL >= 8.0
- Apache (XAMPP/WAMP)

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/Ronaldnksp/surat-kampus.git
cd surat-kampus
```

### 2. Setup Database

Buka phpMyAdmin, lalu import file `database/surat_kampus.sql`

### 3. Konfigurasi

Edit `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'surat_kampus');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Jalankan

Buka browser: `http://localhost/surat-kampus/`

## Default Users

| Role | Email | Password |
|------|-------|----------|
| Staff | staff@kampus.ac.id | password |
| Dekan | dekan@kampus.ac.id | password |
| Mahasiswa | mahasiswa@kampus.ac.id | password |

## Cron Jobs

```bash
# Auto-reject overdue submissions (setiap menit)
* * * * * php /path/to/cron.php

# Daily report (jam 1 pagi)
0 1 * * * php /path/to/cron_report.php
```

## Project Structure

```
surat-kampus/
├── includes/
│   ├── config.php      # Database configuration
│   ├── auth.php        # Authentication functions
│   └── functions.php   # Helper functions
├── css/
│   └── style.css       # Stylesheet
├── uploads/            # User uploads
├── index.php           # Login page
├── dashboard.php       # Dashboard
├── submit.php          # Submit letter
├── my-submissions.php  # My submissions
├── review.php          # Review letters (Staff/Dekan)
├── detail.php          # Letter detail
├── notifications.php   # Notifications
├── cron.php            # Auto-reject cron
├── cron_report.php     # Daily report
└── database/
    └── surat_kampus.sql
```

## UML Documentation

Dokumentasi UML tersedia di folder `docs/uml/`:
1. Use Case Diagram
2. Class Diagram
3. Sequence Diagram (2)
4. Activity Diagram
5. Component Diagram
6. Data Flow Diagram

## License

MIT License
