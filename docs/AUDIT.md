# Audit Project eMadrasah

Tanggal audit: 2026-08-28

## Ringkasan

eMadrasah adalah aplikasi Laravel untuk manajemen administrasi madrasah dengan modul utama master data, akademik, buku induk, raport, persuratan, task management, dan sarana prasarana. Struktur aplikasi sudah cukup lengkap untuk MVP internal, namun perlu penyempurnaan pada kontrol akses, konsistensi skema, validasi bisnis, test coverage, dan kesiapan operasional.

## Kondisi Saat Ini

- Framework: Laravel 13.12.0, PHP 8.3.33.
- Frontend: Blade template, CSS/JS statis, tanpa proses build frontend aktif.
- Database: MySQL melalui migration Laravel.
- Fitur ekspor: PDF dengan `barryvdh/laravel-dompdf`.
- Modul aktif: dashboard, user, guru, kelas, mapel, jam pelajaran, jadwal, absensi guru, guru pengganti, siswa, buku induk, raport, arsip akademik, surat masuk, surat keluar, template surat, task, kategori sarana, sarana, peminjaman, pemeliharaan.

## Temuan Prioritas Tinggi

1. Kontrol akses belum granular.
   Semua route utama hanya memakai middleware `auth`. Belum ada policy, gate, atau middleware role untuk membatasi akses modul sensitif seperti user management, raport, data siswa, dan arsip dokumen.

2. Role user tidak konsisten.
   Migration `users.role` berisi `admin`, `guru`, `wali_murid`, `siswa`, `operator`, tetapi form user memakai opsi `staf`. Selain itu `UserController` belum memvalidasi dan menyimpan role, phone, alamat, atau status aktif dari form.

3. Akun nonaktif masih bisa login.
   Tabel `users` memiliki kolom `is_active`, tetapi proses login hanya memakai email dan password. Ini membuat akun yang dinonaktifkan tetap dapat masuk.

4. Relasi database raport sebagian belum memakai foreign key.
   Tabel raport menyimpan `siswa_id`, `tahun_pelajaran_id`, `mapel_id`, dan `updated_by`, tetapi migration belum mendeklarasikan FK untuk sebagian besar kolom tersebut. Risiko: data yatim ketika siswa, mapel, tahun pelajaran, atau user berubah/dihapus.

5. Test suite belum merepresentasikan behavior aplikasi.
   `php artisan test` gagal pada `Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response` karena test mengharapkan `/` status 200, sedangkan aplikasi mengalihkan user belum login. Test bawaan perlu diganti dengan test autentikasi dan smoke test route inti.

## Temuan Prioritas Menengah

1. Log file memiliki ownership berbeda.
   `storage/logs/laravel.log` dimiliki `nobody:nogroup`. Saat command tertentu memicu logging, Laravel bisa gagal menulis log. Perlu standar permission/deployment untuk `storage` dan `bootstrap/cache`.

2. Conflict check jadwal belum sempurna.
   Pemeriksaan overlap memakai `whereBetween` pada jam mulai dan selesai. Kasus jadwal yang membungkus rentang lain bisa luput. Sebaiknya gunakan formula overlap: `start < existing_end AND end > existing_start`, ditambah constraint unik slot kelas/hari/jam/tahun/semester.

3. `tahun_pelajaran_kode` di jadwal tidak terhubung FK.
   Jadwal memakai kode tahun pelajaran string, sementara tabel lain memakai `tahun_pelajaran_id`. Ini membuat model relasi akademik kurang konsisten.

4. Upload file perlu hardening.
   Beberapa upload sudah validasi image, tetapi dokumen buku induk belum terlihat punya validasi tipe/ukuran yang ketat. Nama file juga memakai nama asli sebagai metadata, sehingga perlu sanitasi tampilan dan batasan jenis dokumen.

5. Stok sarana belum otomatis berubah.
   Saat peminjaman dan pengembalian sarana dicatat, `stok_tersedia` belum diperbarui secara transaksional. Ini berisiko membuat stok tidak sesuai kondisi lapangan.

6. Delete cascade perlu ditinjau secara domain.
   Beberapa data penting seperti siswa dapat menghapus banyak riwayat karena cascade. Untuk arsip pendidikan, soft delete atau status arsip sering lebih aman daripada hard delete.

## Temuan Prioritas Rendah

1. Format kode dan indentasi belum konsisten di beberapa controller dan route.
2. README menyebut TailwindCSS dan Alpine.js, tetapi `package.json` tidak menunjukkan build frontend aktif.
3. Migration `create_buku_induk_complete.php` sudah superseded tetapi masih ada dengan timestamp yang sama. Ini aman karena kosong, tetapi bisa membingungkan maintainer baru.
4. Search dan filtering masih sederhana di beberapa modul.
5. Belum ada seed data user awal yang jelas selain `SaranaSeeder`.

## Rekomendasi Roadmap Teknis

### Keputusan Stack Frontend

- Dipilih: Laravel + Inertia + React + TypeScript + Tailwind CSS + komponen bergaya shadcn/ui.
- Strategi: migrasi hybrid bertahap, bukan rewrite total.
- Blade tetap dipertahankan untuk template PDF dan fallback modul yang belum dimigrasi.
- ADR: `docs/adr/0001-frontend-stack-inertia-react.md`.

### Fase 1: Stabilkan Fondasi

- Perbaiki role enum, form user, validasi user, dan login `is_active`.
- Tambahkan middleware role minimal: admin, operator, guru.
- Perbaiki test bawaan menjadi test login redirect dan akses dashboard.
- Standarkan permission `storage` dan `bootstrap/cache`.

### Fase 2: Kuatkan Data Akademik

- Tambahkan FK pada tabel raport dan konsistenkan referensi tahun pelajaran.
- Perbaiki conflict detection jadwal dan tambahkan constraint unik slot.
- Tambahkan validasi semester, tahun pelajaran aktif, dan status siswa.
- Evaluasi soft delete untuk siswa, guru, sarana, surat, dan arsip.

### Fase 3: Tingkatkan Workflow

- Buat dashboard metrik per role.
- Lengkapi alur stok sarana saat peminjaman/pengembalian.
- Tambahkan audit log untuk perubahan data sensitif.
- Tambahkan export/import Excel untuk data siswa, guru, mapel, dan nilai.

### Fase 4: Siap Operasional

- Tambahkan feature test untuk CRUD utama.
- Tambahkan backup database dan file upload.
- Tambahkan dokumentasi instalasi deployment production.
- Tambahkan CI sederhana untuk lint dan test.

## Hasil Pemeriksaan

- `php artisan about --only=environment`: berhasil.
- `php artisan route:list`: berhasil, terdeteksi 140 route.
- `php artisan test`: gagal 1 dari 2 test karena ekspektasi test bawaan tidak sesuai flow auth aplikasi.
- `php artisan route:list --compact`: opsi `--compact` tidak tersedia dan memicu error log permission ketika Laravel mencoba menulis ke `storage/logs/laravel.log`.
