# SIAKAD Mini

Sistem Informasi Manajemen Dosen & Mata Kuliah untuk Program Studi Teknik Informatika.

## Fitur per Level

### Level 1 (Wajib) - Nilai s/d 70
- Autentikasi login/logout dengan password_hash()
- Guard session (belum login redirect ke login.php)
- CRUD Dosen (Create, Read, Update, Soft Delete)
- Paginasi 5 data per halaman
- Pencarian by NIDN/Nama
- Upload foto dengan MIME check (finfo)
- CSRF Token di semua form POST

### Level 2 (Lanjutan) - Nilai s/d 90
- Arsitektur OOP (DosenRepository, Validator)
- RBAC (Admin/Operator)
- Many-to-Many Dosen ↔ Mata Kuliah
- Filter by Status + Program Studi
- Sorting ASC/DESC
- Trash + Restore (admin)

### Level 3 (Bonus) - Nilai s/d 100
- Activity Log (Audit Trail)
- Dashboard Statistik
- Export CSV sesuai filter
- IDOR Protection + Rate Limiting

## Cara Setup

1. Buat database:
```sql
CREATE DATABASE siakad_mini;
2. Import seed.sql (phpMyAdmin - SQL tab atau Import)
Buka browser:
Copy code
http://localhost/siakad-mini/public/

=== Akun Demo ===
Role        Username    Password
Admin       admin       password123
Operator    operator    password123

=== Struktur Folder ===
siakad-mini/
├── config/
│   └── database.php
├── src/
│   ├── Auth.php
│   ├── DosenRepository.php
│   └── Validator.php
├── public/
│   ├── login.php
│   ├── logout.php
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── delete.php
│   ├── trash.php
│   ├── dashboard.php
│   ├── activity.php
│   ├── export.php
│   └── style.css
├── uploads/
├── seed.sql
└── README.md

=== Tech Stack ===
PHP 8.2+ (Native)
MySQL / MariaDB (XAMPP)
PDO + Prepared Statement
Session + CSRF Token

=== Fitur Keamanan ===
password_hash() (Bcrypt)
Prepared Statement (SQL Injection)
htmlspecialchars() (XSS)
CSRF Token
MIME check via finfo
session_regenerate_id()
RBAC (server-side)
Soft Delete

=== Schema Database ===
Tabel               Fungsi
users               Akun login
dosen               Data dosen
mata_kuliah         Data mata kuliah
dosen_matakuliah    Relasi DOSEN-MK
activity_log        Audit trail

=== Git Commit ===

git init
git add .
git commit -m "Initial: struktur folder + Auth"
git add config/ src/
git commit -m "Add: Database + DosenRepository"
git add public/index.php
git commit -m "Add: Index + paginasi + filter"
git add public/create.php public/edit.php
git commit -m "Add: CRUD DOSEN"
git add public/trash.php
git commit -m "Add: Soft delete + trash + restore"
git add public/dashboard.php public/export.php
git commit -m "Add: Dashboard + Export CSV"
git add src/Validator.php
git commit -m "Add: Validator class"
git add public/activity.php
git commit -m "Add: Activity log"
git add seed.sql README.md
git commit -m "Add: Seed + Readme"