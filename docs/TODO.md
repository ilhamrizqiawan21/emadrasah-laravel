# Rencana kerja: menyiapkan e-Madrasah untuk dijual dan online

Disusun dari kondisi project saat ini. Tiap fase bisa dikerjakan terpisah, urutannya dari yang paling berpengaruh. Keputusan di Fase 0 menentukan fase sesudahnya.

## Fase 0: Keputusan dan audit (sebelum menulis kode)
- [x] Putuskan model penjualan: **satu instalasi per client** (keputusan user 2026-10-08; bukan multi-tenant). Identitas madrasah (nama, logo, alamat, warna) cukup lewat konfigurasi/`.env` per instalasi, tanpa `tenant_id` di database
- [x] Putuskan arah desain: **Bootstrap 5 + Vite (Sass), di-tema ulang** (keputusan user 2026-10-08; Tailwind tidak dipakai)
- [ ] Kumpulkan referensi tampilan yang disukai
- [x] Jalankan aplikasi, ambil screenshot semua halaman utama (desktop dan HP) dengan Playwright
- [x] Buat daftar masalah tampilan dari screenshot, urutkan berdasarkan prioritas (lihat `docs/FASE-0-AUDIT.md`)
- [x] Seeder demo bisa dijalankan berulang tanpa error (diverifikasi dua kali seed, hitungan stabil)
- [ ] Perbesar data demo (saat ini 6 siswa, 8 guru, 6 kelas) supaya tabel dan grafik terlihat terisi saat demo ke calon client

## Fase 1: Fondasi (kecil, risiko rendah)
- [x] Aktifkan locale `id` dan timezone `Asia/Jakarta` (`config/app.php`, `.env.example`); nama bulan/hari kini berbahasa Indonesia
- [x] Kompres `public/images/logo.png`: 385 KB menjadi 18 KB (192x192, palet 256 warna)
- [x] Pasang Vite, lalu bundel CSS (Sass) dan JS sendiri
- [x] Hapus ketergantungan CDN (Bootstrap, Font Awesome, Google Fonts, Chart.js), pakai versi lokal yang di-bundle
- [x] Pindahkan blok `<style>` inline ke `resources/sass/pages/` (5 view biasa; 3 view PDF dompdf sengaja tetap inline). Diverifikasi: login identik per piksel, dashboard/tasks/jadwal tampil sama
- [x] Hapus `welcome.blade.php` (tidak dipakai route mana pun)
- [x] ~~Tambah Alpine.js~~ Tidak diperlukan: interaksi memakai Bootstrap JS dan `resources/js/app.js` (Chart.js dimuat malas)
- [x] Pastikan `npm run build` berfungsi (terverifikasi); `npm run dev` belum dicoba

## Fase 2: Desain ulang tampilan (pekerjaan terbesar)
- [x] Design system terdokumentasi: token, komponen, pola halaman, dan jebakan di `docs/DESIGN-SYSTEM.md`
- [x] Komponen Blade: `x-page-header` dan `x-empty-row` dipakai di 9 halaman daftar dan 20 form (lihat `docs/DESIGN-SYSTEM.md`). Kartu, form-group, dan badge status sengaja belum dibuat komponen: markup-nya terlalu beragam dan tidak ada duplikasi yang sepadan
- [x] Identitas madrasah configurable dari menu **Pengaturan** (admin): nama (lengkap/tampilan/singkat), NPSN, alamat, telepon, email, website, kepala madrasah (nama dan NIP), logo, favicon, warna utama. Disimpan di tabel `settings` (cache), nilai awal dari `config/madrasah.php`/`.env`. Dipakai di sidebar, login, footer, dashboard, favicon, dan PDF buku induk. 33 tes lulus; tes diverifikasi dengan mutasi
- [x] Warna utama per instalasi: satu warna menghasilkan skala `--em-green-*` dan variabel Bootstrap; warna di CSS tidak lagi ditulis langsung. Validasi kontras terhadap teks putih (WCAG AA)
- [x] Kop dan tanda tangan kepala madrasah di PDF raport, rekap absensi, dan buku induk (partial `pdf-kop` dan `pdf-ttd`); logo dan favicon bawaan kini netral (lambang buku, 1,9 KB)
- [ ] Sisa identitas: kop pada surat/template surat (isi surat disimpan di database), tagline login dan teks "e-Madrasah v2.0" yang masih tertulis tetap
- [x] Desain ulang halaman login: dua panel di desktop (panel merek dengan logo dan nama madrasah besar | form), satu kolom di HP; tombol lihat password; mengikuti warna tema
- [x] Layout utama (sidebar/topbar): ditinjau, sudah konsisten, mengikuti tema dan logo dari Pengaturan, responsif, dan sudah diuji di HP/tablet/desktop. Tidak didesain ulang tanpa referensi tampilan
- [x] Poles dashboard: sapaan mengikuti waktu (pagi/siang/sore/malam), badge "Sistem Online" statis dihapus, kartu utama mengikuti warna tema, animasi masuk dipersingkat. Redesain besar belum (menunggu referensi tampilan)
- [x] Standar desain diterapkan: judul halaman seragam di 9 halaman daftar + 20 form tambah/ubah (siswa, guru, kelas, mapel, jadwal, sarana, kategori sarana, surat masuk/keluar, template surat, users, tahun pelajaran). Belum seragam: `jadwal/index`, `sarana/index`, view detail/riwayat (`*/show`, `sarana/pemeliharaan`, `sarana/peminjaman`), tasks, jam-pelajaran, absensi, raport, buku-induk (judul `h3`/lainnya)
- [~] Modul akademik: kelas, mapel, tahun pelajaran sudah. Jadwal (daftar) dan jam pelajaran belum seragam
- [~] Modul kesiswaan: siswa (daftar, form) sudah. Buku induk dan perkembangan siswa belum seragam
- [~] Modul raport: daftar sudah memakai komponen; halaman kelola nilai belum
- [x] Modul administrasi: surat masuk/keluar, template surat, arsip. Daftar dan form seragam; halaman detail surat belum
- [~] Sarana, agenda guru/absensi, tasks, users: users dan sarana (form) sudah; absensi kompak di HP; tasks dan sarana/index belum seragam
- [x] Tabel di HP: 15 tabel daftar (`data-hide-sm`) kini tampil sebagai kartu berisi semua kolom (label otomatis dari judul kolom oleh `app.js`), bukan menyembunyikan kolom. Diverifikasi di 15 halaman: tanpa overflow, tanpa error JS, desktop tidak berubah
- [x] Empty state: 10 tabel daftar kini memakai `x-empty-row` dengan ikon. Loading state: semua form POST menonaktifkan tombol + spinner (terverifikasi; batal konfirmasi dan validasi gagal tidak membuat tombol macet). Pesan error: galat form tampil lewat `x-alert`; belum ada halaman 404/500 khusus
- [x] Diuji di 375px, 768px, dan 1440px: 20 halaman di tablet tanpa overflow; HP dan desktop sudah diperiksa pada sapuan sebelumnya. Target sentuh di halaman Tugas belum diaudit
- [x] PDF raport, rekap absensi, dan buku induk: kop, logo, dan tanda tangan kepala madrasah dari Pengaturan; raport dirender dan diperiksa visual. Surat/template surat belum

## Redesain gaya "Lembut & Ramah" (opsi B), dikerjakan setelah Fase 4
- [x] Font: Inter (isi/tabel) + Plus Jakarta Sans (judul); angka tabular
- [x] Sidebar presisi (menempel penuh, sudut lurus, garis tipis) dengan tombol buka-tutup tepat di tepi, sejajar logo, ARIA, Ctrl+B; diuji E2E (5 tes) dan dimutasi (geser 1px terdeteksi)
- [x] Tabel: header bertinta, baris lega, badge pil, angka tabular, aksi berupa tombol ikon lingkaran lembut, tabel lebar dipadatkan otomatis (13 tabel, tanpa gulir samping), avatar inisial di Siswa/Guru/Users
- [x] Ikon: seluruh ikon Font Awesome; 4 emoji di absensi diganti ikon status berwarna
- [x] Dashboard: kartu statistik pastel; kartu kehadiran mengikuti warna tema
- [x] Bug lama yang ditemukan: token palet siklik dan kanal `-rgb` tidak terdefinisi (kini dijaga `ThemeTokensTest`)
- [ ] Belum: login dipoles ulang ke gaya B (saat ini masih dua panel dari Fase 2), halaman form dan detail belum ditinjau satu per satu, mode gelap, pencarian global (Ctrl+K), skeleton loading

## Fase 3: Kesiapan produksi dan keamanan
- [x] Advisory keamanan Composer: 31 advisory (dompdf, guzzle, psr7, commonmark, flysystem, laravel/framework) menjadi 0 lewat update terarah ke Laravel 13.35 / dompdf 3.1.6. `composer.json` mengunci `config.platform.php = 8.3.0` supaya lock tidak menarik paket yang butuh PHP 8.4+ (percobaan pertama sempat menarik Symfony 8 dan ketahuan). Perlu `composer audit` bersih di tiap rilis
- [x] Konfigurasi produksi: `.env.production.example` (debug off, https, cookie secure + terenkripsi, log harian), `URL::forceScheme('https')` di produksi, trusted proxy lewat `config/trustedproxy.php`. Diuji dengan menjalankan aplikasi sungguhan di `APP_ENV=production` (HSTS, cookie secure/httponly/samesite, login ke https, 404 tanpa kebocoran, `route:cache`/`view:cache`/`config:cache` sukses). Bug yang ditemukan lewat uji: `TRUSTED_PROXIES` di `bootstrap/app.php` tidak pernah terbaca (env() dievaluasi sebelum .env dimuat)
- [x] MySQL: **teruji** di MySQL 8.4 (database `emadrasah` milik user sudah menjalankan 6 migrasi; semua tabel InnoDB `utf8mb4_unicode_ci`). Suite penuh 49/49 lulus di MySQL (database sementara), dan backup `mysqldump` + pemulihan terbukti: 40 tabel, 0 selisih jumlah baris. PostgreSQL belum diuji (`pg_dump` terimplementasi tetapi tidak dicoba). Catatan: user MySQL pengembangan punya hak admin global; produksi wajib user khusus satu database
- [~] Mail: bagian `MAIL_*` disiapkan di template produksi, tetapi aplikasi belum mengirim email apa pun (tidak ada fitur lupa kata sandi atau notifikasi), jadi belum ada yang bisa diuji. Kerjakan bila fitur itu ditambahkan
- [x] Penjadwal: `madrasah:backup` harian 01:30 (`schedule:list` terverifikasi), butuh satu entri cron (di `docs/DEPLOYMENT.md`). Queue worker belum dibutuhkan karena aplikasi belum memakai job; cara menjalankannya sudah didokumentasikan
- [x] Unggahan: tipe dan ukuran sudah divalidasi di tiap controller (2-5 MB per berkas, logo 1 MB, tanpa SVG); ditambahkan batas 10 berkas per permintaan pada dokumen siswa. Nilai Nginx/PHP yang selaras ada di `docs/DEPLOYMENT.md` (sempat salah tulis 12M, dikoreksi ke 60M)
- [x] Backup: `php artisan madrasah:backup` (database + unggahan, zip izin 0600, simpan 14 hari) + jadwal harian + prosedur pemulihan. Pemulihan SQLite terbukti (data hasil ekstraksi cocok) dan tes otomatis. Belum: penyalinan keluar server (tugas operasional klien) dan uji di MySQL
- [~] Logging: template produksi memakai log harian level warning, simpan 14 hari. Monitoring error eksternal (Sentry/Flare dan sejenisnya) belum dipasang; pilih sesuai kebutuhan klien
- [x] Login: pembatas 5 percobaan/menit per email+IP dan 30/menit per IP (sebelumnya 5/menit per IP, yang bisa mengunci seluruh madrasah di satu jaringan); kata sandi minimal 10 karakter (admin pertama dan form Pengguna); session aman di template produksi. Belum ada alur lupa kata sandi (admin mengatur ulang lewat menu Pengguna)
- [~] Audit keamanan: pindai pola berisiko (XSS mentah, SQL mentah, mass assignment, CSRF, redirect terbuka, eval/exec, route tanpa auth) bersih; akses per role sudah dijaga `RouteAuditTest`. Ini pemindaian otomatis, **bukan** audit manual atau penetration test menyeluruh; sebelum dijual sebaiknya ada tinjauan independen

- [x] Akun demo: seeder tidak lagi membuat `admin@madrasah.id`/`admin123` atau data contoh di produksi; admin pertama dibuat dengan `php artisan madrasah:install` (kata sandi min. 10 karakter, ditolak bila umum, atau dibuat acak)

## Fase 4: Kualitas dan test
- [x] Tes alur utama (`tests/Feature/MainFlowsTest.php`): masuk/keluar (salah sandi, akun nonaktif, redirect tujuan), siswa (tambah, cari, ubah, hapus, validasi, NIS unik), surat masuk (unggah, penyajian berkas per role, hapus berkas, berkas berbahaya/terlalu besar, path traversal), raport (simpan tanpa duplikat, nilai di luar rentang, PDF), absensi (simpan dua kali). Menemukan bug nyata: menyimpan absensi hari yang sama dua kali gagal di SQLite (kunci `updateOrCreate` berupa string tanggal)
- [x] `tests/Feature/RouteMatrixTest.php` menelusuri SELURUH tabel route (141 route) dengan ekspektasi per role yang ditulis mandiri: tamu ke login di semua route, admin tanpa 5xx/403, operator dan guru 403 persis di route terlarang (semua metode, id ada maupun tidak). Menemukan: 14 route mengarah ke metode controller yang tidak ada (500, kini dibatasi `except/only`) dan kebocoran 404-vs-403 akibat urutan middleware (kini `role` sebelum `SubstituteBindings`). Diverifikasi dengan mutasi
- [x] E2E Playwright (`npm run e2e`, 13 tes, ~30 dtk, Chrome terpasang): login/logout, akses per role, CRUD siswa lewat antarmuka, Pengaturan (nama, warna, logo; tampilan tamu), tampilan HP, dan unggahan ke server sungguhan (berkas PHP berkedok .pdf ditolak; hal yang tidak bisa dibuktikan `UploadedFile::fake()`). Server, SQLite, dan storage terisolasi di `/tmp`; pengaman menolak lanjut bila konfigurasi aktif bukan SQLite sementara
- [x] Pint dijalankan pada `app`, `routes`, `tests`, `database/seeders`, `config`, `bootstrap/app.php`: 34 berkas dirapikan, perilaku sama (70 tes lulus). Migration lama sengaja tidak disentuh
- [~] Performa: N+1 diukur di 20 halaman daftar dengan data diperbanyak 4x; ditemukan dan diperbaiki di `/kelas` (8 ke 3 query) dan `/tasks` (7 ke 3); penjaga permanen `QueryBudgetTest`. Ukuran aset terukur dari build (CSS ~39 KB gzip, JS ~27 KB gzip, Chart.js 69 KB gzip hanya di dashboard). **Lighthouse belum dijalankan**; skor performa/aksesibilitas belum terukur

## Fase 5: Paket penjualan
- [ ] Siapkan akun dan data demo untuk presentasi ke calon client
- [ ] Tulis dokumentasi instalasi dan deployment
- [ ] Tulis panduan pengguna per role (admin, guru, dst.)
- [ ] Siapkan prosedur onboarding client baru: satu instalasi per client (skrip setup, isi `.env` identitas, buat admin pertama tanpa kredensial demo, impor data awal)
- [ ] Perbarui README (masih menyebut PHP 8.3)
- [ ] Putuskan lisensi dan skema update/pemeliharaan untuk client
- [ ] Putuskan nasib `maatwebsite/excel`: apakah ekspor/impor Excel dibutuhkan client (dicabut karena belum kompatibel PHP 8.5)

## Fase 6: Kelengkapan fitur untuk dipakai nyata (hasil evaluasi 2026-10-09)
### Sprint 1: keamanan data
- [x] Hapus kelas ditolak bila masih ada siswa (sebelumnya `onDelete('cascade')` menghapus seluruh siswa kelas itu diam-diam)
- [x] Siswa memakai soft delete (migrasi `2026_10_09_000001`); riwayat raport dan dokumen dipertahankan. NIS/NISN/NIK siswa terhapus tetap terkunci supaya tidak dipakai ulang tanpa sadar. Belum ada layar pemulihan
- [x] Semester raport diputuskan **1-2 per tahun pelajaran** (sesuai validasi). Komentar kolom di migrasi lama yang menyebut 1-6 sengaja tidak diubah
- [x] Jadwal tidak lagi menebak `'2025/2026'`; menolak dengan pesan jelas bila tidak ada tahun pelajaran aktif. Default kolom `jadwals.tahun_pelajaran_kode` di migrasi lama masih `2025/2026`
- [x] Tes: `tests/Feature/DataSafetyTest.php` (89 tes lulus)
### Sprint 2: alur guru
- [x] Absensi siswa harian (`/absensi-siswa`, `/absensi-siswa/rekap`): input per kelas dan tanggal, tombol "Semua hadir", rekap bulanan per siswa. Guru hanya bisa mengisi kelas yang dia walikan atau ajar (jadwal); admin/operator semua kelas. Tabel `absensi_siswa` menyimpan `kelas_id` saat dicatat. Tes PHP `AbsensiSiswaTest` (8) dan E2E (1). Belum: hubungan otomatis ke `raport_kehadiran`, ekspor PDF/Excel rekap, hari libur
- [x] Portal guru (`/portal`): jadwal hari ini, jadwal mingguan, kelas saya (wali/pengampu, jumlah siswa aktif), daftar siswa per kelas (`/portal/kelas/{kelas}`), pintasan ke absensi dan nilai. Admin/operator diarahkan ke dashboard. Belum: guru masih mendarat di dashboard umum setelah login (bukan portal)
- [x] Input nilai per kelas x mapel (`/nilai`): satu mapel untuk seluruh siswa kelas, per tahun pelajaran dan semester 1-2. Guru hanya kelas+mapel di jadwalnya (wali kelas saja tidak cukup). Kolom kosong dilewati dan tidak menghapus nilai lama (sama seperti raport per siswa), jadi nilai yang salah belum bisa dikosongkan. Pengecekan akses terpusat di `App\Support\AksesKelas`. Tes: `NilaiKelasTest` (7), `PortalGuruTest` (4), E2E 4
- [ ] Catatan: tes E2E sidebar "laci menu" di HP tidak stabil (gagal ~1 dari 3 percobaan, juga dengan sidebar HEAD), perlu diselidiki terpisah
### Sprint 3: siklus tahun ajaran
- [x] Kenaikan kelas dan kelulusan (`/kenaikan-kelas`, menu Kesiswaan): per kelas asal dan tahun pelajaran, hasil naik/tinggal/lulus/tunda per siswa, satu kelas tujuan per proses. Riwayat di tabel `riwayat_kelas` (unik per siswa per tahun, jadi tidak bisa naik dua kali; urutan kelas bebas), tampil di buku induk. Lulus mengubah status dan mengisi `jenis_keluar`/`thn_lulus` di buku induk. Pembatalan per siswa hanya bila datanya belum diubah manual. Belum: pindah/keluar/meninggal massal, cek kapasitas kelas
- [x] Raport lengkap: ekskul (maks. 8 baris), ketidakhadiran, dan catatan wali kelas per siswa per semester (`raport.pelengkap`; tabel baru `raport_catatan`). Kehadiran dihitung otomatis dari absensi siswa harian (Ganjil Jul-Des, Genap Jan-Jun, dari kode tahun pelajaran; nilai tersimpan diutamakan, ada tombol isi ulang). Disatukan di `App\Support\DataRaport`; PDF raport memuat ketiganya
- [x] Cetak raport satu kelas (`/raport/export-kelas`): satu PDF, satu siswa per halaman, urut nama, hanya siswa aktif. Diperiksa visual: satu raport muat satu halaman A4 (sempat dua halaman, sudah dipadatkan)
- [ ] Belum dikerjakan dari rencana Sprint 3: **P5/PPRA** (tabel `raport_p5ppra*` rumit, perlu rancangan tampilan), **prestasi** (`raport_prestasi`), `raport_kelulusan`/ijazah, dan catatan wali oleh guru wali kelas sendiri (kini hanya admin/operator)
- [ ] Sprint 4: impor/ekspor Excel, lupa kata sandi, audit log

## Urutan yang disarankan
1. Fase 0, lalu Fase 1. Keduanya cepat dan membuka jalan.
2. Fase 2 dikerjakan per modul, mulai dari login, dashboard, dan satu modul contoh. Nilai arah desain dulu sebelum lanjut ke modul lain.
3. Fase 3 dan 4 berjalan paralel dengan akhir Fase 2.
4. Fase 5 menjelang demo ke calon client.
