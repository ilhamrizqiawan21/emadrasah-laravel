# TODO Penyempurnaan eMadrasah

Roadmap ini dibagi menjadi 3 phase besar agar pengembangan setelah migrasi awal Inertia React tetap terarah.

## Phase 1: Backend & System Security

Tujuan: menstabilkan fondasi sistem, auth, otorisasi, integritas data, dan kesiapan operasional.

### Auth & Authorization

- [x] Finalisasi daftar role resmi: `admin`, `operator`, `guru`, `siswa`, `wali_murid`.
- [x] Terapkan middleware role pada seluruh modul sensitif, bukan hanya `users`.
- [x] Buat policy/permission per modul untuk aksi `view`, `create`, `update`, `delete`, `export`.
- [x] Pastikan akun `is_active = false` tidak bisa login.
- [x] Tambahkan proteksi agar user tidak bisa menonaktifkan/menghapus akun sendiri secara tidak sengaja.
- [x] Tambahkan rate limit pada login.

### Database & Data Integrity

- [x] Tambahkan foreign key untuk relasi yang masih implisit: `gurus.user_id`, `mapels.parent_id`, tabel raport, dan `raport_nilai.updated_by`.
- [ ] Konsistenkan referensi tahun pelajaran pada jadwal, idealnya memakai `tahun_pelajaran_id`.
- [x] Tambahkan unique constraint untuk mencegah jadwal ganda pada slot kelas/hari/jam/tahun/semester.
- [x] Evaluasi soft delete untuk data penting: siswa, guru, surat, sarana, arsip, dan raport.
  Catatan: data penting memakai `deleted_at` dan trait `SoftDeletes`; unique identifier tetap unik walau record dihapus sementara agar nomor induk/surat/kode aset tidak dipakai ulang tanpa restore/validasi.
- [x] Tambahkan indeks pencarian untuk data besar seperti siswa, surat, tasks, dan arsip.

### Business Rules

- [x] Perbaiki validasi konflik jadwal dengan formula overlap waktu yang benar.
- [x] Tambahkan validasi konflik kelas pada jadwal.
- [x] Buat update stok sarana secara transaksional saat peminjaman dan pengembalian.
- [x] Tambahkan validasi tipe dan ukuran file untuk seluruh upload dokumen.
- [ ] Standarkan nomor agenda/surat dan pola generate otomatis bila dibutuhkan.

### Observability & Operations

- [x] Perbaiki ownership/permission `storage` dan `bootstrap/cache` untuk deployment.
- [x] Tambahkan audit log untuk perubahan data sensitif.
  Catatan: tabel `audit_logs` mencatat create/update/delete/restore data sensitif beserta actor, nilai lama/baru, IP, user agent, dan URL.
- [x] Tambahkan backup database dan file upload.
  Catatan: tersedia command `php artisan emadrasah:backup` yang membuat ZIP berisi dump database, folder upload public, dan manifest; scheduler menjalankannya harian pukul 23:30 dengan retensi default 14 hari.
- [ ] Tambahkan konfigurasi production cache: config, route, view, dan event.
- [x] Jalankan `composer audit` dan prioritaskan advisory dependency production.

### Testing

- [x] Tambahkan feature test auth, role middleware, dan akses modul.
- [x] Tambahkan test CRUD untuk user dan master data utama.
- [x] Tambahkan test business rule jadwal.
- [x] Tambahkan test stok sarana.
- [x] Tambahkan test export PDF tetap berjalan setelah migrasi frontend.

## Phase 2: UI/UX

Tujuan: membuat aplikasi terasa lebih rapi, cepat dipahami, dan nyaman dipakai staf madrasah sehari-hari.

### Information Architecture

- [x] Rapikan struktur sidebar berdasarkan kelompok: Dashboard, Master Data, Akademik, Administrasi, Sarpras, Sistem.
- [x] Tambahkan breadcrumb pada halaman detail/form.
- [x] Standarkan judul halaman, subtitle, action utama, dan action sekunder.
- [x] Buat empty state yang konsisten untuk tabel kosong.
- [x] Buat pola filter/search yang seragam pada halaman index.

### Data Tables

- [x] Standarkan tabel: kolom utama, status badge, action icon, pagination, dan loading state.
- [x] Tambahkan filter per modul: status siswa, kelas, tahun pelajaran, role user, status surat, status task.
- [x] Tambahkan bulk action untuk modul yang aman, seperti arsip atau surat.
- [x] Tambahkan sort kolom pada data besar.

### Forms

- [x] Standarkan layout form create/edit.
- [x] Tambahkan field error inline dan ringkasan error di atas form.
- [x] Gunakan input mask untuk NISN, NIK, nomor HP, tahun pelajaran, dan jam.
- [x] Pisahkan form panjang buku induk menjadi tab/stepper.
- [x] Tambahkan confirm dialog untuk aksi berisiko.

### Dashboard

- [x] Buat dashboard per role.
- [x] Tambahkan ringkasan siswa, guru, jadwal hari ini, task pending, surat terbaru, dan sarana bermasalah.
- [x] Tambahkan quick action sesuai role.
- [x] Tambahkan indikator data yang perlu dilengkapi.

### Accessibility & Responsiveness

- [x] Pastikan navigasi keyboard pada form dan dialog.
- [x] Tambahkan label dan aria-label untuk tombol ikon.
- [x] Pastikan layout tabel responsif di mobile.
- [x] Periksa kontras warna untuk teks, badge, dan tombol.
- [x] Hindari text overflow pada tombol, badge, dan kartu statistik.

## Phase 3: Frontend

Tujuan: menyelesaikan migrasi bertahap dari Blade ke Inertia React TypeScript tanpa memutus fitur lama.

### Foundation

- [x] Tambahkan Inertia Laravel.
- [x] Tambahkan React, TypeScript, Vite, Tailwind CSS, dan komponen bergaya shadcn/ui.
- [x] Buat root Inertia view.
- [x] Buat layout dashboard React.
- [x] Migrasikan login, dashboard, dan users sebagai irisan awal.

### Component System

- [x] Lengkapi komponen UI dasar: textarea, checkbox, radio, switch, dialog, dropdown, tabs, toast, skeleton.
- [x] Buat reusable `DataTable`.
- [x] Buat reusable `ResourceForm`.
- [x] Buat reusable `ConfirmDeleteDialog`.
- [x] Buat reusable `FileUploadField`.
- [x] Buat helper format tanggal, angka, status, dan role.

### Module Migration Order

- [x] Migrasikan master data: guru.
- [x] Migrasikan master data: kelas.
- [x] Migrasikan master data: mapel.
- [x] Migrasikan master data: jam pelajaran.
- [x] Migrasikan master data: tahun pelajaran.
- [x] Migrasikan siswa index/create/edit.
- [x] Migrasikan buku induk index/detail/create/edit.
- [x] Migrasikan raport index/manage.
- [x] Migrasikan jadwal list dan grid.
- [x] Migrasikan absensi dan guru pengganti.
- [x] Migrasikan surat masuk, surat keluar, dan template surat.
- [x] Migrasikan tasks.
- [x] Migrasikan sarana, kategori sarana, peminjaman, dan pemeliharaan.
- [x] Migrasikan arsip akademik.

### Frontend Quality

- [ ] Tambahkan type untuk seluruh Inertia props.
- [ ] Tambahkan state loading, submitting, success, error, dan empty pada setiap halaman.
- [ ] Tambahkan debounce search dan preserve query string pada index.
- [ ] Tambahkan optimistic UI hanya untuk aksi yang aman.
- [ ] Pastikan PDF export tetap memakai endpoint Laravel lama.
- [ ] Jalankan `npm run build` pada setiap batch migrasi.

### Cleanup

- [ ] Hapus Blade yang sudah tidak dipakai setelah modul selesai dimigrasi.
- [ ] Pertahankan Blade PDF.
- [ ] Hapus dependency Bootstrap/Font Awesome/CDN dari layout lama setelah tidak dipakai.
- [ ] Rapikan `public/css/custom.css` dan `public/js/app.js` setelah semua halaman pindah.
- [ ] Dokumentasikan pola membuat halaman Inertia baru.
