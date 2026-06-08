# Admin Panel — RZKY Equipment Services

Dokumentasi lengkap sistem admin untuk manajemen reservasi, invoice PDF otomatis, dan notifikasi WhatsApp/Email.

---

## Daftar Isi

1. [Instalasi & Setup](#instalasi--setup)
2. [Struktur File](#struktur-file)
3. [Fitur Lengkap](#fitur-lengkap)
4. [Panduan Penggunaan](#panduan-penggunaan)
5. [Konfigurasi Notifikasi](#konfigurasi-notifikasi)
6. [Keamanan](#keamanan)
7. [Troubleshooting](#troubleshooting)

---

## Instalasi & Setup

### Prasyarat

- XAMPP (PHP 8.0+, MySQL 5.7+)
- Browser modern (Chrome, Firefox, Edge)

### Langkah 1 — Import Database

Jalankan file SQL berikut secara berurutan di **phpMyAdmin → SQL tab**:

```sql
-- 1. Database utama
SOURCE database/equipment_rental.sql;

-- 2. Migrasi invoice settings
SOURCE database/migration_invoice_settings.sql;

-- 3. Migrasi notifikasi
SOURCE database/migration_notification_settings.sql;
```

### Langkah 2 — Install Library Pihak Ketiga

**FPDF** (untuk generate PDF invoice):
- Sudah tersedia di `src/includes/fpdf/` — tidak perlu install ulang.

**PHPMailer** (untuk kirim email via SMTP):
- Download dari [github.com/PHPMailer/PHPMailer/releases](https://github.com/PHPMailer/PHPMailer/releases)
- Salin 3 file berikut ke `src/includes/phpmailer/`:
  - `src/Exception.php`
  - `src/PHPMailer.php`
  - `src/SMTP.php`

### Langkah 3 — Konfigurasi Database

Edit `src/config/database.php` sesuai environment:

```php
define('DB_HOST',    'localhost');
define('DB_NAME',    'equipment_rental');
define('DB_USER',    'root');
define('DB_PASS',    '');
```

### Langkah 4 — Buat Folder Invoice

Buat folder untuk menyimpan file PDF invoice:

```
assets/invoices/     ← buat folder ini jika belum ada
```

Pastikan folder ini bisa ditulis (writable).

### Langkah 5 — Akses Admin Panel

```
http://localhost/membangun-web-umkm-atau-pcm-commitajadulu/admin/login.php
```

| Kredensial | Nilai |
|------------|-------|
| Username   | `admin` |
| Password   | `admin123` |

> **Segera ganti password** setelah login pertama melalui **Pengaturan → Ubah Password**.

---

## Struktur File

```
admin/
├── login.php                    # Halaman login
├── logout.php                   # Handler logout
├── dashboard.php                # Dashboard utama
├── reservations.php             # Manajemen reservasi
├── reports.php                  # Laporan & statistik
├── settings.php                 # Pengaturan admin
├── invoice-stream.php           # Preview/generate invoice PDF
├── send-notification.php        # AJAX handler kirim WA/Email
└── save-notification-settings.php  # Handler simpan pengaturan notifikasi

src/
├── config/
│   └── database.php             # Koneksi database (PDO)
├── includes/
│   ├── admin-header.php         # Header + sidebar admin
│   ├── admin-footer.php         # Footer admin
│   ├── invoice-generator.php    # Helper generate PDF (FPDF)
│   ├── notification-helper.php  # Helper kirim WA (Fonnte) & Email (PHPMailer)
│   ├── fpdf/                    # Library FPDF
│   └── phpmailer/               # Library PHPMailer (install manual)
└── inventory/
    ├── category-list.php        # Daftar kategori
    ├── category-create.php      # Tambah kategori
    ├── category-edit.php        # Edit kategori
    ├── category-delete.php      # Hapus kategori
    ├── equipment-list.php       # Daftar equipment
    ├── equipment-create.php     # Tambah equipment
    ├── equipment-edit.php       # Edit equipment
    └── equipment-delete.php     # Hapus equipment

database/
├── equipment_rental.sql                  # Schema + data utama
├── migration_invoice_settings.sql        # Tabel invoice_settings
└── migration_notification_settings.sql   # Tabel notification_settings + log

assets/
├── css/
│   ├── admin.css                # Stylesheet admin panel
│   └── style.css                # Stylesheet halaman publik
├── images/
│   └── equipment/               # Foto-foto equipment
└── invoices/                    # File PDF invoice (auto-generated)
```

---

## Fitur Lengkap

### Dashboard

- Ringkasan statistik: total equipment, kategori, reservasi, pending
- Status kondisi equipment (Baik / Maintenance / Rusak)
- Tabel reservasi terbaru
- Quick action buttons

### Manajemen Inventori

**Kategori** — CRUD lengkap:
- Tambah, edit, hapus kategori
- Deskripsi dan ikon kategori

**Equipment** — CRUD lengkap:
- Tambah equipment dengan upload foto
- Edit detail dan harga sewa per hari
- Hapus equipment
- Filter berdasarkan kategori dan status kondisi

### Manajemen Reservasi

- Daftar semua reservasi dengan filter status, pencarian, dan rentang tanggal
- Filter cepat via pill: Semua / Pending / Approved / Rejected / Completed / Cancelled
- Detail reservasi: data pelanggan, item yang disewa, periode, total biaya
- Update status langsung dari tabel maupun dari modal detail
- **Generate PDF invoice otomatis** saat status diubah ke `approved`

**Alur status reservasi:**
```
Pending → Approved → Completed
        ↘ Rejected
        ↘ Cancelled
```

### Invoice PDF Otomatis

Invoice di-generate otomatis menggunakan FPDF saat reservasi di-approve. File disimpan di `assets/invoices/`.

Konten invoice:
- Header perusahaan (nama, alamat, telepon, email, logo)
- Nomor invoice unik (`INV-XXXXX`)
- Data pelanggan dan periode sewa
- Tabel item equipment (qty, harga/hari, subtotal)
- Total pembayaran
- Instruksi pembayaran dan catatan

**Kustomisasi** template invoice melalui **Pengaturan → Template Invoice**.

### Notifikasi Otomatis

Setelah reservasi di-approve, admin dapat mengirim notifikasi ke pelanggan:

| Tombol | Metode | Keterangan |
|--------|--------|------------|
| Kirim WhatsApp Otomatis | Fonnte API | Server-side, langsung masuk WA pelanggan |
| Manual (WA) | WhatsApp Web | Buka WA Web dengan pesan siap kirim |
| Kirim Email Otomatis | SMTP Gmail | Server-side via PHPMailer |
| Manual (Email) | mailto: | Buka email client lokal |

Log pengiriman tersimpan otomatis di tabel `notification_log`.

### Laporan

- Statistik reservasi per periode
- Pendapatan per bulan
- Equipment paling banyak disewa

### Pengaturan

Tersedia 5 tab pengaturan:

| Tab | Fungsi |
|-----|--------|
| Profil Admin | Edit username dan nama lengkap |
| Ubah Password | Ganti password dengan validasi kekuatan |
| Sistem | Info database, daftar tabel, migrasi schema |
| Template Invoice | Konfigurasi data perusahaan pada PDF invoice |
| Notifikasi | Konfigurasi Fonnte token (WA) dan SMTP Gmail (email) |

---

## Panduan Penggunaan

### Approve Reservasi & Kirim Notifikasi

1. Buka **Manajemen Reservasi**
2. Klik ikon 👁 untuk melihat detail reservasi berstatus *Pending*
3. Klik tombol **Setujui** — invoice PDF otomatis digenerate
4. Setelah approved, panel **Aksi Notifikasi & Invoice** muncul di bawah:
   - **Download Invoice PDF** — unduh file PDF
   - **Preview Invoice** — buka PDF di tab baru
   - **Kirim WhatsApp Otomatis** — kirim via Fonnte (perlu konfigurasi token)
   - **Kirim Email Otomatis** — kirim via SMTP Gmail (perlu konfigurasi)
5. Toast notifikasi muncul di pojok kanan bawah menandakan sukses/gagal

### Setup Invoice Template

1. Buka **Pengaturan → Template Invoice**
2. Isi data perusahaan: nama, alamat, telepon, email
3. Isi instruksi pembayaran dan catatan footer
4. Upload logo perusahaan (PNG/JPG, maks 2MB) — opsional
5. Klik **Simpan**

Invoice yang sudah ada tidak akan berubah retroaktif — hanya invoice baru yang menggunakan template terbaru.

---

## Konfigurasi Notifikasi

### WhatsApp via Fonnte

1. Daftar di [fonnte.com](https://fonnte.com)
2. Login → menu **Device** → **Add Device**
3. Scan QR code dengan WhatsApp HP pengirim
4. Setelah terhubung, salin **Token** dari halaman device
5. Tempel token di **Pengaturan → Notifikasi → Fonnte Token** → Simpan

> Fonnte menyediakan free trial terbatas. Untuk produksi, diperlukan paket berbayar.

### Email via Gmail SMTP

1. Aktifkan **2-Step Verification** di akun Gmail pengirim:
   [myaccount.google.com/security](https://myaccount.google.com/security)
2. Buat **App Password** di:
   [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
   - App name: ketik bebas (misal `RZKY`)
   - Salin 16 karakter yang muncul (tanpa spasi)
3. Isi di **Pengaturan → Notifikasi**:
   - SMTP Host: `smtp.gmail.com`
   - Port: `587`
   - Akun Gmail Pengirim: `namakamu@gmail.com`
   - App Password: 16 karakter tadi
4. Klik **Simpan**

> Gunakan App Password, **bukan** password Gmail biasa. Pastikan tidak ada spasi saat menyalin.

---

## Keamanan

| Fitur | Implementasi |
|-------|--------------|
| Autentikasi | Session-based, redirect otomatis ke login jika belum masuk |
| Password | Di-hash dengan `password_hash()` + verifikasi `password_verify()` |
| SQL Injection | Semua query pakai PDO prepared statements |
| XSS | Semua output di-escape dengan `htmlspecialchars()` |
| CSRF | Form POST dengan hidden field validasi tab |
| Output Buffering | AJAX handler pakai `ob_start()` agar error PHP tidak merusak JSON |

---

## Troubleshooting

### Halaman blank / error 500
Aktifkan error reporting sementara di `src/config/database.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
Matikan kembali setelah masalah teratasi.

### Invoice tidak terbuat saat approve
- Pastikan folder `assets/invoices/` sudah ada dan writable
- Cek apakah file `src/includes/fpdf/fpdf.php` ada
- Lihat pesan error di alert merah yang muncul setelah approve

### Email gagal dikirim: "Konfigurasi SMTP belum lengkap"
- Pastikan `smtp_user` dan `smtp_pass` sudah diisi dan tersimpan di DB
- Cek di phpMyAdmin: `SELECT smtp_user, LENGTH(smtp_pass) FROM notification_settings WHERE id=1;`
- `LENGTH(smtp_pass)` harus **16**. Kalau lebih, ada spasi — jalankan:
  ```sql
  UPDATE notification_settings SET smtp_pass = REPLACE(smtp_pass, ' ', '') WHERE id = 1;
  ```

### Email gagal: "Could not authenticate"
- App Password salah atau ada spasi yang tersimpan (lihat solusi di atas)
- Pastikan 2FA sudah aktif di akun Gmail tersebut
- App Password harus dibuat dari akun yang sama dengan `smtp_user`

### WhatsApp tidak terkirim
- Token Fonnte kosong atau salah — cek di Pengaturan → Notifikasi
- Pastikan device di Fonnte masih terhubung (tidak logout/expired)
- Cek log di tabel `notification_log` untuk pesan error detail

### File `debug-notif.php` tidak boleh ada di produksi
File ini dibuat untuk keperluan debug saja. Hapus setelah tidak diperlukan:
```
admin/debug-notif.php  ← hapus file ini
```

### Database connection error
Pastikan MySQL sudah berjalan dan kredensial di `src/config/database.php` sesuai. Default XAMPP:
- Host: `localhost`
- User: `root`
- Password: *(kosong)*

---

## Design System

| Elemen | Nilai |
|--------|-------|
| Primary | `#2563EB` (Blue) |
| Success | `#22C55E` (Green) |
| Warning | `#F59E0B` (Amber) |
| Danger | `#EF4444` (Red) |
| Sidebar | `#0a1628` (Dark Navy) |
| Font | System UI / sans-serif |
| Icons | Bootstrap Icons |
| Breakpoint mobile | `768px` |

---

*Dokumentasi ini mencerminkan kondisi sistem per Juni 2026.*
