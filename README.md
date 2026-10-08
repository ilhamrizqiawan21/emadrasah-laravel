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
- **Bahasa Pemrograman**: PHP 8.3+ (diuji di PHP 8.5)
- **Frontend**: Blade Templates, Bootstrap 5 (Sass), Font Awesome 6, Chart.js, dibangun dengan Vite
- **Database**: SQLite (pengembangan) atau MySQL/PostgreSQL (produksi)
- **Eksportir**: 
  - PDF: [Barryvdh/Laravel-DomPDF](https://github.com/barryvdh/laravel-dompdf)
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
   git clone https://github.com/ilhamrizqiawan21/emadrasah-laravel.git
   cd emadrasah-laravel
   ```

2. **Instal dependensi PHP:**
   ```bash
   composer install
   ```

3. **Instal dan bangun aset Frontend:**
   ```bash
   npm install
   npm run build
   ```
   Untuk pengembangan, jalankan `npm run dev` agar perubahan CSS/JS langsung termuat.

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

## ⚙️ Pengaturan Madrasah (tanpa mengubah kode)

Satu instalasi melayani satu madrasah. Identitas dan tampilan diatur dari menu **Pengaturan** (khusus admin), jadi klien tidak perlu menyentuh kode atau file `.env`:

- Nama madrasah (lengkap, tampilan, dan singkat), NPSN, alamat, telepon, email, website
- Nama dan NIP kepala madrasah (tercetak pada tanda tangan dokumen)
- Logo dan favicon (PNG/JPG/WebP, maksimal 1 MB; favicon mengikuti logo bila tidak diunggah)
- Warna utama aplikasi (pilih preset atau warna sendiri; warna yang terlalu terang ditolak agar teks tetap terbaca)

Nilai awal untuk instalasi baru diambil dari `config/madrasah.php` (dapat diisi lewat variabel `MADRASAH_*` di `.env`). Setelah admin menyimpan Pengaturan, nilai di database yang dipakai.

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
