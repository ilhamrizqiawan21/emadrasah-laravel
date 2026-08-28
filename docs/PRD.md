# PRD eMadrasah

## 1. Latar Belakang

eMadrasah adalah sistem informasi manajemen madrasah untuk membantu digitalisasi administrasi akademik, kesiswaan, persuratan, tugas internal, dan sarana prasarana. Produk ditujukan untuk MTs/madrasah yang membutuhkan aplikasi internal sederhana, terpusat, dan mudah dioperasikan oleh admin/operator, guru, dan staf.

## 2. Tujuan Produk

- Menyatukan data master madrasah dalam satu aplikasi.
- Mempercepat pengelolaan data siswa, buku induk, jadwal, absensi guru, dan raport.
- Menyediakan arsip dokumen akademik dan persuratan yang mudah dicari.
- Membantu pencatatan inventaris, peminjaman, dan pemeliharaan sarana.
- Memberikan dasar aplikasi yang dapat dikembangkan bertahap menuju operasional production.

## 3. Pengguna

- Admin: mengelola seluruh data, user, konfigurasi, dan monitoring.
- Operator/TU: mengelola data siswa, buku induk, surat, arsip, sarana, dan task.
- Guru: melihat jadwal, mengisi agenda/absensi, mengelola nilai sesuai hak akses.
- Wali kelas: memantau siswa kelas, raport, kehadiran, dan arsip kelas.
- Siswa/wali murid: rencana jangka lanjut untuk akses informasi terbatas.

## 4. Ruang Lingkup MVP

### 4.1 Autentikasi dan User Management

- Login dan logout.
- CRUD user.
- Role user: admin, operator, guru, siswa, wali murid.
- Status aktif/nonaktif akun.
- Pembatasan akses berbasis role.

### 4.2 Master Data Akademik

- CRUD tahun pelajaran dan penanda tahun aktif.
- CRUD guru.
- CRUD kelas dan wali/guru pembimbing.
- CRUD mapel, termasuk urutan dan parent mapel.
- CRUD jam pelajaran.

### 4.3 Jadwal Pelajaran

- CRUD jadwal reguler.
- Tampilan grid jadwal.
- Validasi konflik guru pada slot waktu yang sama.
- Validasi konflik kelas pada slot waktu yang sama.
- Penyimpanan jadwal per tahun pelajaran dan semester.

### 4.4 Absensi Guru dan Guru Pengganti

- Input status kehadiran guru per tanggal.
- Rekap absensi guru.
- Penunjukan guru pengganti pada jam pelajaran.
- Export rekap ke PDF.

### 4.5 Siswa dan Buku Induk

- CRUD data siswa ringkas.
- Buku induk lengkap: biodata, orang tua/wali, perkembangan siswa, dokumen.
- Export buku induk siswa ke PDF.
- Upload dokumen pendukung siswa dengan validasi tipe dan ukuran file.

### 4.6 Raport dan Arsip Nilai

- Daftar siswa untuk pengelolaan raport.
- Input nilai akademik per mapel, semester, dan tahun pelajaran.
- Deskripsi capaian pembelajaran.
- Export raport ke PDF.
- Data pendukung: kehadiran, ekstrakurikuler, prestasi, P5/PPRA, kelulusan.

### 4.7 Persuratan

- CRUD surat masuk.
- CRUD surat keluar.
- CRUD template surat.
- Upload file scan/draft.
- Pencarian berdasarkan nomor, asal/tujuan, dan perihal.

### 4.8 Task Management

- CRUD tugas internal.
- Assignment ke user.
- Prioritas, deadline, status, kategori, attachment, progress.
- Log aktivitas tugas.

### 4.9 Sarana Prasarana

- CRUD kategori sarana.
- CRUD inventaris sarana.
- Pencatatan peminjaman dan pengembalian.
- Pencatatan pemeliharaan.
- Stok tersedia yang konsisten dengan transaksi peminjaman.

### 4.10 Arsip Akademik

- Upload arsip per tahun pelajaran, kelas, dan semester.
- Kategori arsip: Leger, RDM, Lainnya.
- Pencarian dan filter arsip.

## 5. Di Luar Ruang Lingkup MVP

- Portal publik madrasah.
- Pembayaran/SPP.
- Integrasi EMIS/Dapodik.
- Notifikasi WhatsApp/SMS/email.
- Mobile app native.
- Multi-tenant untuk banyak madrasah.

## 6. Kebutuhan Fungsional

| Kode | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-001 | User dapat login dan logout dengan aman. | Must |
| FR-002 | Admin dapat mengelola user dan role. | Must |
| FR-003 | Admin/operator dapat mengelola master data akademik. | Must |
| FR-004 | Operator dapat mengelola siswa dan buku induk lengkap. | Must |
| FR-005 | Sistem dapat membuat dan memvalidasi jadwal pelajaran. | Must |
| FR-006 | Guru/operator dapat mencatat absensi guru dan pengganti. | Should |
| FR-007 | Guru/operator dapat mengelola nilai raport dan export PDF. | Must |
| FR-008 | Operator dapat mengelola surat masuk/keluar dan template. | Should |
| FR-009 | Staff dapat mengelola tugas internal dan progres. | Should |
| FR-010 | Operator dapat mengelola inventaris, peminjaman, dan pemeliharaan. | Should |
| FR-011 | Operator dapat mengarsipkan dokumen akademik. | Should |
| FR-012 | Sistem menyediakan pencarian dan filter pada data besar. | Should |

## 7. Kebutuhan Non-Fungsional

- Keamanan: seluruh route internal wajib login dan route sensitif wajib role/permission.
- Integritas data: relasi utama wajib memakai foreign key dan validasi domain.
- Auditabilitas: perubahan data sensitif perlu dicatat.
- Performa: halaman index memakai pagination dan indeks pencarian.
- Keandalan: file upload disimpan terstruktur dan dapat dibackup.
- Maintainability: controller, validation, dan view mengikuti pola konsisten.
- Testability: minimal feature test untuk auth dan CRUD inti.

## 8. Alur Utama

### 8.1 Setup Tahun Pelajaran

1. Admin membuat tahun pelajaran.
2. Admin menandai satu tahun pelajaran sebagai aktif.
3. Modul siswa, jadwal, raport, dan arsip memakai tahun aktif sebagai default.

### 8.2 Pengelolaan Buku Induk

1. Operator membuat data siswa.
2. Operator melengkapi biodata, orang tua/wali, dan perkembangan.
3. Operator mengunggah dokumen pendukung.
4. Sistem menyediakan tampilan detail dan export PDF.

### 8.3 Pengelolaan Jadwal

1. Admin/operator membuat jam pelajaran.
2. Admin/operator mengisi kelas, guru, mapel, semester, dan tahun pelajaran.
3. Sistem menolak konflik guru atau kelas pada slot yang sama.
4. Jadwal dapat dilihat dalam daftar dan grid.

### 8.4 Pengelolaan Raport

1. Guru/operator memilih siswa, tahun pelajaran, dan semester.
2. Guru/operator mengisi nilai per mapel.
3. Sistem menyimpan nilai unik per siswa-tahun-semester-mapel.
4. Sistem menghasilkan PDF raport.

### 8.5 Pengelolaan Sarana

1. Operator membuat kategori dan data sarana.
2. Operator mencatat peminjaman.
3. Sistem mengurangi stok tersedia.
4. Operator mencatat pengembalian.
5. Sistem menambah stok tersedia dan menyimpan riwayat.

## 9. Hak Akses Awal

| Modul | Admin | Operator/TU | Guru | Siswa/Wali |
| --- | --- | --- | --- | --- |
| Dashboard | Ya | Ya | Ya | Terbatas |
| User | Ya | Tidak | Tidak | Tidak |
| Master Akademik | Ya | Ya | Lihat | Tidak |
| Jadwal | Ya | Ya | Lihat | Lihat terbatas |
| Absensi Guru | Ya | Ya | Input sendiri | Tidak |
| Siswa/Buku Induk | Ya | Ya | Lihat terbatas | Lihat sendiri |
| Raport | Ya | Ya | Input sesuai mapel/kelas | Lihat sendiri |
| Persuratan | Ya | Ya | Tidak | Tidak |
| Task | Ya | Ya | Ya | Tidak |
| Sarana | Ya | Ya | Lihat/pinjam | Tidak |
| Arsip Akademik | Ya | Ya | Lihat terbatas | Tidak |

## 10. Metrik Keberhasilan

- Admin/operator dapat menyelesaikan CRUD data master tanpa error.
- Data siswa lengkap dapat diekspor ke PDF buku induk.
- Jadwal tidak mengizinkan konflik guru/kelas.
- Nilai raport dapat disimpan dan diekspor per semester.
- Stok sarana sesuai dengan jumlah peminjaman aktif.
- Semua route sensitif terlindungi role.
- Test inti berjalan hijau di CI/local.

## 11. Risiko

- Data sensitif siswa dan dokumen belum cukup terlindungi jika role belum diterapkan.
- Inkonsistensi role dapat menghambat user management.
- Relasi tanpa FK dapat menghasilkan data yatim.
- Hard delete dapat menghilangkan riwayat akademik.
- Test minim membuat regresi sulit terdeteksi.

## 12. Prioritas Iterasi Berikutnya

1. Perbaiki auth, role, dan status aktif user.
2. Tambahkan test auth dan route protection.
3. Perkuat FK dan constraint database.
4. Perbaiki validasi jadwal dan stok sarana.
5. Lengkapi workflow raport dan arsip dokumen.
