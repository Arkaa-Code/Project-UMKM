[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/VUTo3A1n)
# Tugas Kelompok: Aplikasi Web UMKM atau PCM

**Mata Kuliah:** Pemrograman Web  
**Deadline:** Senin, 8 Juni 2026 pukul 23.59 WIB  
**Anggota Kelompok:** 5–6 orang

---

## Deskripsi Tugas

Buatlah aplikasi website untuk salah satu dari dua jenis organisasi berikut:

**Pilihan A — UMKM (Usaha Mikro, Kecil, dan Menengah)**  
Pilih satu UMKM nyata di sekitar kalian — bisa dari kampung halaman, kota asal, atau lingkungan sekitar kampus. Bangun website yang membantu UMKM tersebut memperluas jangkauan dan mengelola usahanya secara digital.

**Pilihan B — PCM (Pimpinan Cabang Muhammadiyah)**  
Pilih satu PCM nyata di wilayah mana pun. Bangun website yang membantu PCM mengelola informasi, program, dan amal usaha di bawah naungannya.

Lokasi UMKM/PCM bebas — termasuk di kampung halaman anggota kelompok.

---

## Stack Teknologi Wajib

### Frontend (FE)

| Teknologi | Peran |
|-----------|-------|
| **HTML** | Struktur halaman web |
| **CSS** | Tampilan dan responsivitas |
| **JavaScript** | Interaktivitas sisi klien |

Framework CSS (Bootstrap, Tailwind) dan library JS (jQuery, Alpine.js) diperbolehkan sebagai tambahan.

### Backend (BE)

Pilih **salah satu** dari dua opsi berikut:

**Opsi 1 — Python (Flask)**

| Teknologi | Peran |
|-----------|-------|
| **Python (Flask)** | Logika server dan routing |
| **MySQL** | Basis data |

**Opsi 2 — Native PHP**

| Teknologi | Peran |
|-----------|-------|
| **PHP (tanpa framework)** | Logika server dan routing |
| **MySQL** | Basis data |

> Native PHP berarti tidak menggunakan framework seperti Laravel, CodeIgniter, atau Symfony. Penggunaan PDO atau MySQLi untuk koneksi database diperbolehkan.

---

## Fitur Wajib (Umum — Berlaku untuk Pilihan A maupun B)

Semua aplikasi harus memiliki fitur-fitur berikut:

### 1. Halaman Beranda (Home)
- Profil singkat UMKM/PCM (nama, sejarah, visi-misi)
- Foto atau banner utama
- Alamat lengkap dan peta lokasi yang dapat diklik — wajib menggunakan salah satu dari:
  - [OpenStreetMap](https://www.openstreetmap.org/) via embed iframe atau Leaflet.js
  - Google Maps embed

### 2. Basis Data MySQL (Terhubung ke Website)
- Seluruh data yang ditampilkan di website **harus diambil dari database**, bukan di-hardcode di HTML
- Minimal 5 tabel yang saling berelasi
- Menggunakan foreign key dan query JOIN
- Tidak menggunakan ORM — tulis query SQL secara langsung
  - Flask: boleh menggunakan `mysql-connector-python` atau `PyMySQL`
  - PHP: boleh menggunakan PDO atau MySQLi (procedural maupun OOP)

### 3. Halaman Login Admin
- Halaman login tersendiri (misalnya `/login` atau `/admin/login`) yang hanya diperuntukkan bagi pengelola
- Password di-hash sebelum disimpan ke database (`bcrypt` untuk Flask, `password_hash()` untuk PHP)
- Manajemen sesi aktif (Flask session atau PHP session)
- Semua halaman admin **wajib** dilindungi — redirect ke halaman login jika belum terautentikasi

### 4. Panel Admin
- Dashboard dengan ringkasan data (jumlah produk/program, anggota, dll.)
- CRUD (Create, Read, Update, Delete) untuk konten utama
- Upload gambar (format JPG/PNG, disimpan di server)
- Hanya dapat diakses setelah login sebagai admin

### 5. Kontak via WhatsApp
- Tersedia tombol atau menu yang mengarahkan pengunjung langsung ke WhatsApp pemilik/pengelola
- Gunakan format tautan `https://wa.me/<nomor>` dengan nomor yang valid
- Dapat ditempatkan di navbar, halaman beranda, halaman kontak, atau footer — selama mudah ditemukan pengunjung

---

## Fitur Spesifik per Pilihan

### Pilihan A — UMKM

Wajib implementasikan **minimal 3** dari fitur berikut:

| No | Fitur | Deskripsi |
|----|-------|-----------|
| 1 | **Katalog Produk** | Daftar produk lengkap dengan foto, deskripsi, harga, dan stok |
| 2 | **Keranjang Belanja & Pemesanan** | Pelanggan dapat memesan produk; pemilik mengelola pesanan dari panel admin |
| 3 | **Galeri & Testimoni** | Foto kegiatan/produk dan ulasan pelanggan |
| 4 | **Blog / Artikel** | Konten edukasi atau promosi yang dapat dikelola dari admin |
| 5 | **Pencarian & Filter Produk** | Filter berdasarkan kategori, harga, atau kata kunci |
| 6 | **Laporan Penjualan Sederhana** | Rekap pesanan per periode, dapat diekspor ke CSV |
| 7 | **WhatsApp / Media Sosial Integration** | Tombol berbagi produk atau pesan via WhatsApp |

### Pilihan B — PCM (Pimpinan Cabang Muhammadiyah)

Wajib implementasikan **minimal 3** dari fitur berikut:

| No | Fitur | Deskripsi |
|----|-------|-----------|
| 1 | **Direktori AUM** | Daftar Amal Usaha Muhammadiyah (sekolah, rumah sakit, masjid, dll.) di bawah PCM tersebut, lengkap dengan informasi, alamat, dan kontak |
| 2 | **Layanan & Donasi** | Formulir donasi online, riwayat donasi, dan transparansi laporan penerimaan donasi |
| 3 | **Agenda & Kegiatan** | Kalender kegiatan PCM, pengumuman, dan notifikasi |
| 4 | **Berita & Artikel** | Portal berita/pengumuman yang dikelola oleh admin PCM |
| 5 | **Direktori Anggota / Ranting** | Daftar ranting (cabang bawah) dan anggota aktif |
| 6 | **Galeri Dokumentasi** | Foto dan video kegiatan PCM |
| 7 | **Formulir Kontak / Aspirasi** | Sarana warga menyampaikan pertanyaan atau aspirasi kepada PCM |

---

## Struktur Repositori yang Disarankan

**Opsi 1 — Flask**

```
nama-kelompok-umkm-pcm/
├── app/
│   ├── __init__.py
│   ├── routes/
│   │   ├── auth.py
│   │   ├── admin.py
│   │   └── public.py
│   ├── templates/
│   │   ├── base.html
│   │   ├── index.html
│   │   └── admin/
│   ├── static/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── models/         # fungsi-fungsi query SQL
├── database/
│   ├── schema.sql      # DDL: CREATE TABLE, dll.
│   └── seed.sql        # Data awal/contoh
├── .env.example        # Template variabel lingkungan (tanpa nilai sensitif)
├── requirements.txt
├── run.py
└── README.md
```

**Opsi 2 — Native PHP**

```
nama-kelompok-umkm-pcm/
├── public/             # Document root server (index.php, aset publik)
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── images/
├── src/
│   ├── config/
│   │   └── database.php   # Koneksi PDO/MySQLi
│   ├── pages/
│   │   ├── auth/          # login.php, register.php, logout.php
│   │   ├── admin/         # dashboard.php, CRUD pages
│   │   └── public/        # halaman publik
│   └── includes/          # header.php, footer.php, fungsi bantu
├── database/
│   ├── schema.sql          # DDL: CREATE TABLE, dll.
│   └── seed.sql            # Data awal/contoh
├── .env.example            # Template variabel lingkungan (tanpa nilai sensitif)
└── README.md
```

---

## Cara Menjalankan Aplikasi (wajib dicantumkan di README kalian)

README kelompok kalian **wajib** menyertakan langkah-langkah menjalankan aplikasi sesuai stack yang dipilih, minimal:

**Opsi 1 — Flask**

```bash
# 1. Clone repositori
git clone <url-repo>
cd <nama-folder>

# 2. Buat virtual environment
python -m venv venv
source venv/bin/activate   # Windows: venv\Scripts\activate

# 3. Install dependensi
pip install -r requirements.txt

# 4. Salin dan isi file konfigurasi
cp .env.example .env
# Edit .env: isi DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, SECRET_KEY

# 5. Buat database dan jalankan schema
mysql -u root -p < database/schema.sql
mysql -u root -p nama_database < database/seed.sql

# 6. Jalankan aplikasi
python run.py
```

**Opsi 2 — Native PHP**

```bash
# 1. Clone repositori
git clone <url-repo>
cd <nama-folder>

# 2. Salin dan isi file konfigurasi
cp .env.example .env
# Edit .env: isi DB_HOST, DB_USER, DB_PASSWORD, DB_NAME

# 3. Buat database dan jalankan schema
mysql -u root -p < database/schema.sql
mysql -u root -p nama_database < database/seed.sql

# 4. Jalankan dengan PHP built-in server (development)
php -S localhost:8000 -t public/

# Atau arahkan document root Apache/Nginx ke folder public/
```

---

## Ketentuan Pengerjaan

1. **Kelompok:** 5–6 orang. Setiap anggota wajib memiliki kontribusi commit di repositori.
2. **Repositori:** Gunakan repositori GitHub Classroom yang sudah disediakan. Jangan membuat repositori baru di luar classroom.
3. **Branch:** Kerjakan di branch `main`. Penggunaan branch fitur sangat dianjurkan.
4. **File `.env`:** Jangan pernah meng-commit file `.env` yang berisi password atau secret key. Sertakan `.env.example` sebagai gantinya, dan tambahkan `.env` ke `.gitignore`.
5. **Commit history:** Pastikan commit history mencerminkan kontribusi seluruh anggota kelompok, bukan hanya satu orang.
6. **Data dummy:** Sertakan data awal yang cukup di `seed.sql` agar fitur aplikasi dapat langsung didemonstrasikan.

---

## Pengumpulan

Pengumpulan dilakukan melalui GitHub Classroom. Pastikan sebelum deadline:

- [ ] Seluruh kode sudah di-push ke branch `main`
- [ ] *(Flask)* `requirements.txt` sudah diperbarui (`pip freeze > requirements.txt`)
- [ ] `database/schema.sql` sudah ada dan bisa dijalankan dari awal
- [ ] `database/seed.sql` sudah ada dan berisi data contoh yang memadai
- [ ] README kelompok berisi deskripsi aplikasi, daftar anggota, stack yang digunakan, dan cara menjalankan
- [ ] File `.env` **tidak** ikut ter-commit (ada di `.gitignore`)

---

## Penilaian

| Aspek | Bobot |
|-------|-------|
| Kelengkapan dan kebenaran fitur | 35% |
| Kualitas database (relasi, query, normalisasi) | 20% |
| Tampilan antarmuka (UI/UX, responsif) | 15% |
| Kualitas kode (struktur, keterbacaan, keamanan) | 15% |
| Kolaborasi (distribusi commit antar anggota) | 10% |
| README dan dokumentasi | 5% |

### Catatan Penilaian
- Aplikasi yang tidak bisa dijalankan dari instruksi README akan mendapat pengurangan nilai signifikan.
- Commit yang semuanya dari satu orang akan diinvestigasi dan dapat berpengaruh pada nilai individu.
- Penggunaan AI generatif diperbolehkan sebagai alat bantu, namun setiap anggota harus mampu menjelaskan kode yang di-submit saat presentasi.

---

## Tips

- Mulai dari database: rancang skema tabel terlebih dahulu sebelum menulis kode apapun.
- Bagi tugas per fitur, bukan per layer (jangan satu orang mengerjakan semua HTML, satu orang mengerjakan semua backend).
- Commit secara rutin dengan pesan commit yang deskriptif.
- Test fitur secara menyeluruh sebelum deadline — terutama alur login, CRUD, dan query database.
- Flask: gunakan `mysql-connector-python` atau `PyMySQL` untuk koneksi ke MySQL.
- PHP: gunakan PDO (direkomendasikan karena mendukung prepared statements) atau MySQLi untuk koneksi ke MySQL; gunakan `password_hash()` dan `password_verify()` untuk hashing password.

---

*Apabila ada pertanyaan, hubungi dosen melalui forum diskusi yang tersedia di Discord.*
