[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/79kKOuxV)
[![Open in Visual Studio Code](https://classroom.github.com/assets/open-in-vscode-2e0aaae1b6195c2367325f4f02e2d04e9abb55f0b24a779b69b11b9e10269abc.svg)](https://classroom.github.com/online_ide?assignment_repo_id=23752745&assignment_repo_type=AssignmentRepo)
# Tugas #1: Membangun Website Personal dengan HTML & CSS

## Deskripsi
Buat sebuah website personal statis menggunakan **HTML** dan **CSS** yang mencerminkan identitas dan portofolio Anda. Website ini harus memiliki desain yang **responsif**, **menarik**, dan memenuhi standar kualitas web modern.

## Tujuan
Setelah menyelesaikan tugas ini, mahasiswa diharapkan mampu:
- Membangun halaman web statis menggunakan **HTML** semantik dan **CSS** eksternal.
- Menerapkan teknik layout modern dengan **flexbox** atau **grid**.
- Membuat tampilan yang **responsif** untuk berbagai ukuran layar.
- Menggunakan **Git** secara terstruktur: membuat branch, menulis pesan commit yang deskriptif, dan menggabungkan perubahan via Pull Request.
- Mempublikasikan website ke internet menggunakan layanan **hosting** gratis.
- Memastikan kualitas kode melalui **validasi** dan standar **aksesibilitas** dasar.

## Spesifikasi

### 1. Struktur Halaman (Minimal 3 Halaman)
- **Halaman Utama (index.html)**: Berisi perkenalan singkat, foto profil, dan deskripsi diri.
- **Halaman Portofolio (portfolio.html)**: Menampilkan proyek atau pengalaman yang pernah dilakukan.
- **Halaman Kontak (contact.html)**: Berisi formulir kontak (tidak perlu fungsional, cukup desain).

### 2. Elemen Wajib dalam HTML
- Menggunakan **header, nav, main, footer** secara semantik.
- Navigasi antar halaman harus berfungsi.
- Setiap halaman wajib memiliki meta tags berikut di dalam `<head>`:
  ```html
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Deskripsi singkat halaman ini">
  ```
- Sertakan **favicon** (ikon kecil yang muncul di tab browser).

### 3. Gaya dan Tampilan (CSS)
- Menggunakan **CSS eksternal** (file `.css` terpisah).
- Memanfaatkan **flexbox atau grid** untuk tata letak (layout).
- Menerapkan warna, font, dan spacing yang konsisten.
- Menggunakan **Google Fonts** atau ikon dari **FontAwesome**.
- **Responsif** (menggunakan media queries minimal untuk tampilan mobile dan desktop).
- Efek hover atau **animasi** sederhana menggunakan CSS.
- Tidak menggunakan **inline style** (`style="..."`) — semua gaya harus di file CSS eksternal.

### 4. Kualitas Kode
- Nama file dan folder menggunakan **huruf kecil** dan **tanpa spasi** (gunakan `-` sebagai pemisah, contoh: `about-me.html`, `style/main.css`).
- HTML dan CSS harus **lolos validasi** menggunakan:
  - [W3C HTML Validator](https://validator.w3.org/)
  - [W3C CSS Validator](https://jigsaw.w3.org/css-validator/)
- Sertakan tangkapan layar hasil validasi di `LAPORAN.md`.

### 5. Aksesibilitas (a11y)
- Semua elemen `<img>` wajib memiliki atribut `alt` yang deskriptif.
- Kontras warna teks dan latar belakang harus memadai (rasio minimal 4.5:1 untuk teks normal).
- Seluruh navigasi harus dapat diakses menggunakan keyboard (tombol Tab).

### 6. Deployment (Wajib)
- **Host website ke internet** menggunakan **GitHub Pages**, Netlify, atau Vercel.
- Sertakan link website yang sudah di-host di `LAPORAN.md`.

### 7. Bonus (Opsional)
- Dark mode toggle.
- Skor **Lighthouse ≥ 80** pada kategori **Performance** dan **Accessibility** (sertakan tangkapan layar di `LAPORAN.md`).

## Pengumpulan melalui GitHub Classroom

> **Tugas individu.** Setiap mahasiswa mengerjakan repository masing-masing yang diberikan oleh GitHub Classroom.

### 0. Prasyarat: Setup SSH di GitHub
Sebelum mulai mengerjakan, pastikan Anda sudah mengonfigurasi SSH key di GitHub agar dapat melakukan `git push` tanpa memasukkan password setiap saat.

1. Generate SSH key (jika belum ada):
   ```bash
   ssh-keygen -t ed25519 -C "email@anda.com"
   ```
2. Salin isi public key (`~/.ssh/id_ed25519.pub`) ke **GitHub → Settings → SSH and GPG keys → New SSH key**.
3. Uji koneksi:
   ```bash
   ssh -T git@github.com
   ```
   Jika berhasil, akan muncul pesan: `Hi <username>! You've successfully authenticated...`
4. Clone repository menggunakan URL **SSH** (bukan HTTPS):
   ```bash
   git clone git@github.com:....git
   ```

### 1. Repository GitHub
- Pastikan seluruh file proyek disimpan dan **dikirim** melalui repository yang diberikan melalui **GitHub Classroom**.
- Struktur direktori harus rapi dan mudah dipahami.
- Disarankan untuk **membuat branch** (misalnya `dev` atau `feature/nama-fitur`) dan menggabungkannya ke `main` melalui **Pull Request** di GitHub.
- Buat *commit* secara **berkala** dan deskriptif — **minimal 5 commit** yang bermakna (bukan sekadar satu commit besar di akhir).

#### Format Pesan Commit
Gunakan format **Conventional Commits** berikut:

| Prefix | Digunakan untuk |
|--------|-----------------|
| `feat:` | menambah fitur atau halaman baru |
| `fix:` | memperbaiki bug atau kesalahan tampilan |
| `style:` | perubahan CSS tanpa mengubah fungsionalitas |
| `docs:` | perubahan pada `LAPORAN.md` atau dokumentasi |
| `chore:` | pekerjaan umum (menambah gambar, favicon, dll.) |

Contoh pesan commit yang baik:
```
feat: tambah halaman portofolio dengan grid layout
fix: perbaiki navigasi yang tidak responsif di mobile
style: sesuaikan warna dan tipografi header
docs: tambah screenshot hasil validasi W3C ke laporan
```

### 2. Laporan dalam Format Markdown (`LAPORAN.md`)
Buat file `LAPORAN.md` di dalam repository yang berisi:
- **Deskripsi proyek** (tujuan, fitur utama, teknologi yang digunakan).
- **Struktur folder dan file** dalam proyek.
- **Link website yang sudah di-host** (wajib).
- **Tangkapan layar bukti SSH berhasil dikonfigurasi** (output perintah `ssh -T git@github.com`).
- **Tangkapan layar hasil validasi** W3C HTML dan CSS.
- **Tangkapan layar Lighthouse** (jika mengerjakan bonus).
- Format laporan harus jelas dan mudah dibaca.

## Batas Waktu Pengumpulan
- Jumat, **8 Mei 2026** jam 23.59 WIB.

## Penilaian

| Kriteria | Bobot | Sub-kriteria |
|----------|-------|--------------|
| **Struktur & Kelengkapan HTML** | 25% | Penggunaan tag semantik, meta tags, favicon, struktur antar halaman |
| **Desain & Kerapihan CSS** | 25% | Konsistensi warna/font, penggunaan flexbox/grid yang tepat, tidak ada inline style |
| **Responsivitas** | 15% | Layout berfungsi di mobile dan desktop, media queries yang tepat |
| **Navigasi & User Experience** | 15% | Navigasi antar halaman lancar, aksesibilitas keyboard, atribut `alt` pada gambar |
| **Deployment & Kualitas Kode** | 20% | Website berhasil di-host, lolos validasi W3C, penamaan file konsisten |
| **Bonus: LAPORAN.md** | +10 poin | Dokumentasi sangat lengkap dan terstruktur |
| **Bonus: Lighthouse ≥ 80** | +10 poin | Skor Performance dan Accessibility ≥ 80 |

## Referensi

### HTML & CSS
| Sumber | Topik |
|--------|-------|
| [MDN Web Docs — HTML](https://developer.mozilla.org/en-US/docs/Web/HTML) | Referensi lengkap elemen dan atribut HTML |
| [MDN Web Docs — CSS](https://developer.mozilla.org/en-US/docs/Web/CSS) | Referensi lengkap properti CSS |
| [CSS-Tricks: A Guide to Flexbox](https://css-tricks.com/snippets/css/a-guide-to-flexbox/) | Panduan visual flexbox |
| [CSS-Tricks: A Complete Guide to Grid](https://css-tricks.com/snippets/css/complete-guide-grid/) | Panduan visual CSS grid |

### Git & GitHub
| Sumber | Topik |
|--------|-------|
| [Pro Git Book](https://git-scm.com/book/en/v2) | Buku Git gratis dan lengkap |
| [GitHub Docs](https://docs.github.com) | Dokumentasi resmi GitHub |
| [Learn Git Branching](https://learngitbranching.js.org/) | Latihan Git interaktif berbasis visual |
| [Conventional Commits](https://www.conventionalcommits.org/) | Spesifikasi format pesan commit |

#### Video: Setup SSH di GitHub
| Video | Channel | Keterangan |
|-------|---------|------------|
| [Generating a new SSH key and adding it to the ssh-agent](https://www.youtube.com/watch?v=X40b9x9BFGo) | GitHub | Tutorial resmi SSH key untuk GitHub |
| [SSH GitHub Setup (Windows, Mac, Linux)](https://www.youtube.com/watch?v=snCP3c7wXw0) | Fireship | Penjelasan singkat dan padat, semua OS |

#### Video: Bekerja dengan Git secara Efektif
| Video | Channel | Keterangan |
|-------|---------|------------|
| [Git in 100 Seconds](https://www.youtube.com/watch?v=hwP7WQkmECE) | Fireship | Konsep inti Git dalam 100 detik |
| [13 Advanced (but useful) Git Techniques and Shortcuts](https://www.youtube.com/watch?v=ecK3EnyGD8o) | Fireship | Tips praktis sehari-hari |
| [Git and GitHub for Beginners — Crash Course](https://www.youtube.com/watch?v=RGOj5yH7evk) | freeCodeCamp | Tutorial lengkap untuk pemula |
| [Git Branches Tutorial](https://www.youtube.com/watch?v=e2IbNHi4uCI) | freeCodeCamp | Cara kerja branch secara mendalam |

### Deployment
| Sumber | Topik |
|--------|-------|
| [GitHub Pages Docs](https://pages.github.com/) | Cara hosting via GitHub Pages |
| [Netlify Docs](https://docs.netlify.com/) | Cara hosting via Netlify |
| [Vercel Docs](https://vercel.com/docs) | Cara hosting via Vercel |

### Aksesibilitas & Kualitas
| Sumber | Topik |
|--------|-------|
| [web.dev — Accessibility](https://web.dev/accessibility) | Panduan aksesibilitas dari Google |
| [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/) | Cek rasio kontras warna |
| [W3C HTML Validator](https://validator.w3.org/) | Validasi HTML |
| [W3C CSS Validator](https://jigsaw.w3.org/css-validator/) | Validasi CSS |

### Video Tutorial
| Channel | Topik |
|---------|-------|
| [Kevin Powell](https://www.youtube.com/@KevinPowell) | CSS mendalam: flexbox, grid, responsivitas |
| [Traversy Media](https://www.youtube.com/@TraversyMedia) | Tutorial HTML, CSS, dan web development umum |
| [Web Dev Simplified](https://www.youtube.com/@WebDevSimplified) | Konsep web dengan penjelasan singkat dan jelas |

## Contoh Website Personal
Berikut adalah beberapa contoh website dan screenshot personal yang dapat dijadikan inspirasi:
- [Brittany Chiang](https://brittanychiang.com/)
- [Sindre Sorhus](https://sindresorhus.com/)
- [Benedikt Deicke](https://benediktdeicke.com/)


![](./images/personal-web-sample2.jpeg)
![](./images/personal-web-sample1.png)

## Selamat Mengerjakan! 🚀
Gunakan kesempatan ini untuk mengekspresikan kreativitas kalian dalam membangun website pribadi yang menarik dan profesional. Jangan ragu untuk bereksperimen dengan desain dan fitur tambahan! Jika ada kendala, diskusikan dengan teman atau tanyakan di kelas.

**Semangat dan selamat berkarya!** 💪😊
