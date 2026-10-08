# GEMINI.md: panduan kerja untuk e-Madrasah

Kamu adalah pelaksana (executor) pekerjaan di project ini. Hasil tiap fase akan di-review oleh Claude sebelum dianggap selesai.

## Tentang project
Sistem informasi madrasah (e-Madrasah) yang akan dijual ke client dan dijalankan online. Cakupan: akademik, kesiswaan, raport Kurikulum Merdeka, surat-menyurat, arsip, sarana prasarana, agenda guru, absensi, tasks, dan manajemen user dengan role.

## Rencana kerja
Sumber kebenaran ada di `docs/TODO.md` (Fase 0 sampai 5).

Aturan:
- Kerjakan **satu fase per sesi**, sesuai urutan di `docs/TODO.md`. Jangan loncat fase.
- Centang `- [x]` hanya untuk item yang benar-benar selesai dan sudah diverifikasi.
- Item yang butuh keputusan user (misalnya Fase 0: model penjualan, Bootstrap vs Tailwind) **jangan diputuskan sendiri**. Berhenti dan tanyakan.
- Setelah fase selesai, berhenti dan tulis ringkasan untuk review (lihat "Serah terima" di bawah). Jangan lanjut ke fase berikutnya sebelum user menyatakan lolos review.

## Stack (jangan diubah tanpa diminta)
- PHP 8.5, Laravel 13, Eloquent, Blade (server-rendered)
- Auth: Laravel Breeze + middleware role `EnsureUserHasRole` (`role:admin,operator`, dst.)
- PDF: `barryvdh/laravel-dompdf`
- Frontend saat ini: Bootstrap 5.3 + Font Awesome (CDN), `public/css/custom.css`, `public/js/app.js`, tanpa build step
- Database dev: SQLite. Test: PHPUnit 12
- `maatwebsite/excel` sengaja dicabut (tidak kompatibel PHP 8.5). Jangan dipasang lagi tanpa persetujuan.

## Perintah
```
composer install
cp .env.example .env && php artisan key:generate   # hanya jika .env belum ada
php artisan migrate
php artisan serve
php artisan test
vendor/bin/pint            # format kode PHP
```

## Aturan kerja
1. Baca kode yang ada sebelum mengubah. Ikuti gaya, penamaan, dan pola yang sudah dipakai.
2. Perubahan sekecil yang diperlukan untuk item yang sedang dikerjakan. Jangan refactor atau menambah fitur di luar item.
3. Jalankan `php artisan test` dan `vendor/bin/pint` sebelum menyatakan selesai. Laporkan jujur jika ada yang gagal, jangan disembunyikan.
4. Untuk perubahan UI, buka halamannya di browser dan lihat hasilnya di desktop dan lebar HP (sekitar 375px). Lolos test saja tidak cukup.
5. Satu commit per kelompok perubahan yang logis. **Jangan commit atau push kecuali user meminta.**
6. Jangan melakukan aksi destruktif atau sulit dibatalkan (hapus file/tabel, ubah migrasi yang sudah ada, force push, `migrate:fresh` pada data penting) tanpa konfirmasi.
7. Jangan mengubah file migrasi lama. Perubahan skema lewat migrasi baru.
8. Jangan menurunkan kontrol akses. Setiap route manajemen harus tetap dilindungi middleware role, dan `tests/Feature/RouteAuditTest.php` harus tetap lulus.

## Keamanan
- Jangan menulis secret/API key ke kode atau commit. Pakai environment variable.
- Jangan membaca atau menampilkan isi `.env`.
- Validasi semua input dan batasi tipe serta ukuran file upload.
- Jangan memakai `{!! !!}` untuk data dari pengguna. Gunakan `{{ }}`.

## Konvensi bahasa
- Teks antarmuka dan komunikasi dalam Bahasa Indonesia.
- Kode, nama variabel/kelas, dan pesan commit dalam Bahasa Inggris.

## Serah terima untuk review
Di akhir fase, tulis ringkasan dengan format:
1. Fase dan item checklist yang dikerjakan (dan yang sengaja dilewati beserta alasannya)
2. Daftar file yang diubah atau ditambah
3. Hasil `php artisan test` dan `vendor/bin/pint`
4. Cara memverifikasi manual (URL atau halaman yang perlu dibuka)
5. Hal yang belum pasti atau butuh keputusan user
