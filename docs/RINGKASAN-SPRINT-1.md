# Ringkasan Sprint 1 dan sisa pekerjaan

Disusun 2026-10-10. Status kode: belum di-commit saat dokumen ini ditulis. Verifikasi terakhir: 232 tes PHP lulus, Pint bersih, 27 tes e2e Playwright lulus.

## Sudah dikerjakan

### Tampilan login
- Satu kartu terpusat (tanpa panel hijau dua kolom), latar terang dengan semburat warna tema; tombol **Masuk** dibuat menonjol (bayangan berwarna, terangkat saat disorot).
- `layouts/auth.blade.php` dipakai juga halaman lupa dan reset sandi, jadi ikut berubah.
- File: `resources/views/auth/login.blade.php`, `resources/views/layouts/auth.blade.php`, `resources/sass/pages/_login.scss`.

### Portal wali murid dan siswa
- Tabel `wali_siswa` (satu wali boleh punya beberapa anak). Admin menautkan lewat NIS di halaman **Edit Pengguna** (`UserSiswaController`, route `users.siswa.*`, khusus admin).
- Role `wali_murid` dan `siswa` punya portal sendiri (`PortalWaliController`, route `wali.*`): kehadiran per bulan, nilai, jadwal kelas, unduh PDF raport.
- Semua data dibatasi ke siswa yang tertaut; siswa lain selalu 404. Staf (termasuk admin) mendapat 403 di route `wali.*`.
- Sebelumnya akun wali yang login melihat dashboard admin; kini `/dashboard` mengalihkan wali dan siswa ke portal. Sidebar wali hanya berisi Portal Wali.
- Akun demo (hanya di luar produksi): `wali@madrasah.id` / `wali123456`.

### Rilis raport
- Tabel `raport_rilis` (tahun pelajaran + semester). Admin merilis atau menarik di halaman Raport (`pengaturan.raport-rilis`, khusus admin).
- Wali/siswa hanya melihat nilai dan PDF semester yang sudah dirilis.

### Notifikasi
- Tabel `notifications` (saluran database). Lonceng di topbar dengan jumlah belum dibaca, halaman kotak masuk `notifikasi.*` untuk semua role (hanya milik sendiri).
- Wali dikabari saat anaknya **baru** tercatat alpha (`SiswaAlpha`): tidak dikirim ulang untuk status yang sama, dan tidak untuk tanggal lebih dari 7 hari lalu. Email hanya bila `MAIL_MAILER` bukan `log`/`array` dan wali punya email. Gagal kirim tidak menggagalkan penyimpanan absensi.
- `php artisan madrasah:pengingat` (jadwal harian 07:00): tugas jatuh tempo besok atau terlambat, dan sarana dipinjam lebih dari 7 hari (ke admin dan operator, sekali per minggu per peminjaman). Notifikasi sinkron, tidak memakai antrean.

### Dashboard admin dan operator
- Panel **Perlu Tindakan**: kelas belum diabsen hari ini (kecuali Minggu), guru belum diabsen, izin guru menunggu persetujuan, tugas lewat tenggat, surat masuk belum selesai lebih dari 3 hari. Admin juga melihat status cadangan terakhir (peringatan bila lebih dari 48 jam).
- Grafik kehadiran guru 7 hari kini satu query terkelompok (sebelumnya 14 query) agar `/dashboard` di bawah batas 30 query di `QueryBudgetTest`.
- Kode di `app/Support/PerluTindakan.php`.

### Backup
- Backup harian 01:30 sudah ada sebelumnya. Ditambah: salinan ke disk lain lewat `BACKUP_DISK` (S3, SFTP, atau folder mount) dengan pemangkasan salinan lama; bila gagal, perintah berakhir dengan kode galat dan cadangan lokal tetap ada.

### Izin dan cuti guru
- Tabel `izin_guru`. Guru mengajukan izin/sakit/cuti/dinas (maks. 60 hari, mulai paling lama 7 hari ke belakang, tidak boleh bertumpuk). Admin dan operator menyetujui atau menolak (`persetujuan-izin.putuskan`) dengan catatan opsional; keputusan hanya sekali.
- Persetujuan mengisi absensi guru otomatis untuk hari kerja (Minggu dilewati); catatan hadir, izin, dan sakit tidak ditimpa, hanya hari kosong atau alpha. Jenis sakit dicatat sakit, lainnya izin.
- Notifikasi ke staf (pengajuan baru) dan ke guru (keputusan).

### Migrasi baru (jalankan `php artisan migrate` saat deploy)
1. `2026_10_10_000001_create_wali_siswa_table`
2. `2026_10_10_000002_create_notifications_table`
3. `2026_10_10_000003_create_raport_rilis_table`
4. `2026_10_10_000004_create_izin_guru_table`

Catatan: `tahun_pelajaran.id` bertipe `smallint unsigned`, jadi kolom relasinya harus `unsignedSmallInteger` (kolom `bigint` ditolak MySQL, dan tes SQLite tidak menangkapnya).

### Tes baru
`PortalWaliTest`, `NotifikasiTest`, `PerluTindakanTest`, `IzinGuruTest`, tambahan di `BackupCommandTest` dan `RouteMatrixTest` (matriks akses kini mencakup role wali_murid dan siswa, `notifikasi.*`, `izin-guru.*`). Aturan penting diuji dengan mutasi (dedup notifikasi, try/catch pengiriman, pengecualian hari Minggu, pemeriksaan kepemilikan wali, tidak menimpa catatan hadir, keputusan sekali).

## Belum dikerjakan

### Keputusan yang menunggu pengguna
- **Notifikasi WhatsApp** untuk kabar alpha. Butuh penyedia pihak ketiga (mis. Fonnte atau WA Cloud API), biaya, dan kunci API; belum dipilih.

### Fitur dari audit yang belum disentuh
Urutan saran: ekspor EMIS, keuangan, kedisiplinan.
1. **Ekspor Dapodik/EMIS** (format cocok untuk laporan ke Kemenag): paling cepat memangkas kerja operator.
2. **Keuangan** (SPP, tabungan, infak): tagihan, pembayaran, tunggakan; modul besar, kerjakan bertahap.
3. **Kedisiplinan dan BK**: pelanggaran, poin, catatan konseling.
4. Cetak dokumen: surat keterangan aktif, kartu pelajar, daftar hadir, nomor surat otomatis, lembar disposisi.
5. Pengumuman dan kalender akademik.
6. PPDB (pendaftaran siswa baru).
7. Hak akses lebih rinci per modul, role wali kelas dan kepala madrasah (persetujuan raport, tanda tangan digital atau QR).
8. Keamanan: 2FA untuk admin, pengunci akun setelah gagal login berulang (saat ini hanya throttle).
9. Operasional: Redis untuk cache dan session bila pengguna bertambah, health check dan monitoring error eksternal (Sentry/Flare), Lighthouse belum dijalankan.
10. UX: pencarian global (Ctrl+K), mode gelap, tampilan HP absensi guru.

### Keterbatasan dan utang teknis yang diketahui
- Peringatan tidak dikirim untuk izin/sakit siswa, hanya alpha.
- Peminjaman sarana belum punya tanggal jatuh tempo; "belum kembali" didefinisikan lebih dari 7 hari (`PengingatCommand::HARI_PINJAM_MAKS`).
- Izin guru yang bentrok dengan catatan hadir yang sudah ada: catatan hadir dipertahankan tanpa peringatan.
- Judul `@section('title')` yang mengandung `&` tampil `&amp;` di breadcrumb (di-escape dua kali oleh topbar). Masih terjadi di `resources/views/portal/index.blade.php` ("Kelas & Jadwal Saya"); halaman baru sudah menghindarinya.
- Pint melaporkan urutan import di `routes/web.php`; sudah ada sebelum sprint ini dan tidak disentuh.
- Tes browser lewat Playwright MCP: klik mouse sungguhan tidak sampai ke halaman di sesi uji (klik lewat skrip berhasil, suite e2e lulus); batasan lingkungan uji, bukan bug aplikasi.
- Migrasi hanya diverifikasi di SQLite (tes) dan MySQL dev; PostgreSQL belum diuji.
- Belum ada tes e2e Playwright untuk portal wali, kotak notifikasi, dan izin guru (hanya tes fitur PHP dan pemeriksaan visual manual).

### Operasional sebelum dipakai nyata
- Pastikan satu entri cron `schedule:run` aktif (lihat `docs/DEPLOYMENT.md` bagian 4): backup 01:30, pengingat 07:00, pembersihan audit mingguan.
- Isi `BACKUP_DISK` dan uji pemulihan dari salinan di luar server sekurang-kurangnya sekali.
- Atur SMTP (`MAIL_*`) bila email ke wali diinginkan; tanpa itu notifikasi hanya muncul di aplikasi.
- Tautkan akun wali ke siswa lewat menu Pengguna; wali tanpa tautan melihat penjelasan "belum ditautkan".
- Rilis raport per semester dari halaman Raport sebelum wali diberi tahu.
