# eMadrasah - Sistem Informasi Manajemen Madrasah

[![Laravel Version](https://img.shields.io/badge/Laravel-13.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**eMadrasah** adalah sistem manajemen sekolah terintegrasi yang dirancang khusus untuk Madrasah Tsanawiyah (MTs), dengan fokus pada digitalisasi administrasi akademik sesuai standar Kurikulum Merdeka.

---

## 🚀 Fitur Utama

### 1. Manajemen Data Master
- **Tahun Pelajaran & Semester**: Pengaturan periode akademik aktif.
- **Data Guru & Staff**: Manajemen profil tenaga pendidik secara komprehensif.
- **Data Siswa**: Pengelolaan biodata siswa secara detail.
- **Kelas & Mata Pelajaran**: Struktur organisasi kelas dan distribusi kurikulum.

### 2. Manajemen Akademik & Kurikulum
- **Jadwal Pelajaran**: Penjadwalan mingguan dengan antarmuka grid yang intuitif.
- **Buku Induk Digital**: Implementasi Buku Induk Register Peserta Didik sesuai standar nasional.
- **Raport Kurikulum Merdeka**: Sistem penilaian dan pencetakan raport otomatis.
- **Agenda & Absensi Guru**: Pencatatan kehadiran guru dan manajemen guru pengganti (infaler).
- **Arsip Akademik**: Digitalisasi dokumen-dokumen penting madrasah.

### 3. Administrasi & Persuratan
- **Surat Masuk & Keluar**: Pencatatan dan pelacakan surat menyurat secara sistematis.
- **Template Surat**: Pembuatan surat otomatis berdasarkan template yang telah ditentukan.
- **Task Management**: Sistem manajemen tugas internal untuk koordinasi staff dan guru.

### 4. Sarana & Prasarana (SARPRAS)
- **Inventarisasi**: Pendataan aset dan kategori sarana madrasah.
- **Peminjaman Aset**: Alur kerja peminjaman dan pengembalian sarana secara digital.
- **Pemeliharaan**: Log pemeliharaan untuk menjaga kualitas infrastruktur.

---

## 🛠️ Tech Stack

- **Framework**: [Laravel 13](https://laravel.com)
- **Bahasa Pemrograman**: PHP 8.3
- **Frontend**: Blade Templates, TailwindCSS, Alpine.js
- **Database**: MySQL
- **Eksportir**: 
  - PDF: [Barryvdh/Laravel-DomPDF](https://github.com/barryvdh/laravel-dompdf)
  - Excel: [Maatwebsite/Laravel-Excel](https://laravel-excel.com/)
- **Scripts**: Python (untuk ekstraksi teks dari dokumen statis)

---

## 💻 Instalasi

### Prasyarat
- PHP >= 8.3
- Composer
- Node.js & NPM
- MySQL

### Langkah-langkah
1. **Clone repositori:**
   ```bash
   git clone <repository-url>
   cd emadrasah2
   ```

2. **Instal dependensi PHP:**
   ```bash
   composer install
   ```

3. **Instal dependensi Frontend:**
   ```bash
   npm install
   ```

4. **Konfigurasi Lingkungan:**
   Salin file `.env.example` menjadi `.env` dan sesuaikan pengaturan database Anda.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Migrasi Database:**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Aplikasi:**
   ```bash
   php artisan serve
   ```

---

## 📂 Struktur Folder Utama

- `app/Http/Controllers`: Logika bisnis dan penanganan permintaan web.
- `app/Models`: Definisi skema data dan relasi Eloquent.
- `resources/views`: Template tampilan menggunakan Blade.
- `database/migrations`: Struktur database.
- `scripts/`: Skrip pembantu (Python) untuk pemrosesan dokumen.

---

## 📄 Lisensi

Proyek ini bersifat open-source di bawah lisensi [MIT](LICENSE).
