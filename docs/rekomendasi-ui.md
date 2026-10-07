# Rekomendasi UI/Layout eMadrasah

Hasil review 7 Oktober 2026. 18 halaman diperiksa di desktop (1366px) dan ponsel (390px)
dengan browser sungguhan (Chromium via Playwright), lengkap dengan tangkapan layar dan
pengukuran otomatis (overflow, ukuran target sentuh, ukuran font, label form, error konsol).

## Sudah baik
- Sidebar, kartu statistik, tipografi, dan halaman login rapi di desktop.
- Grid input jadwal dan form siswa nyaman dipakai.
- Tabel dibungkus `table-responsive`.
- Tidak ada error JavaScript maupun request gagal.

## Daftar masalah dan status

| # | Masalah | Usulan | Paket | Status |
|---|---|---|---|---|
| A | Tabel di ponsel terpotong: kolom Status dan Aksi di luar layar tanpa petunjuk geser. Halaman Jadwal (448 dari 390px) dan Absensi (401 dari 390px) membuat seluruh halaman bisa digeser ke samping. | Di ponsel sembunyikan kolom kurang penting (No, L/P) atau ubah baris jadi kartu. Perbaiki lebar Jadwal dan Absensi. | 2 | Selesai |
| B | Halaman raport menulis "Data disimpan otomatis" padahal tidak ada simpan otomatis. Ganti Tahun/Semester membuang isian yang belum disimpan. Kolom capaian terpotong satu baris. | Ubah teks, beri peringatan sebelum pindah, tombol Simpan tetap terlihat (sticky), perbesar kolom capaian. | 1 | Selesai |
| C | Link "Lupa Password?" mati (`href="#"`). Kolom login berlabel "Username" padahal memakai email. | Hapus link, ganti label jadi "Email". | 1 | Selesai |
| D | Menu ponsel: ruang kosong 80px di atas konten, tombol menu melayang menutupi konten dan sidebar. | Bar atas yang tetap (sticky) berisi tombol menu, kurangi padding. | 1 | Selesai |
| E | Warna tidak konsisten: brand hijau, tapi judul kartu form, link NIS, dan beberapa ikon memakai biru bawaan Bootstrap. | Atur `--bs-primary` ke hijau brand di satu tempat. | 1 | Selesai |
| F | Tanggal tampil berbahasa Inggris ("07 October 2026") di absensi, tugas, buku induk. | Ganti `format()` dengan `translatedFormat()`. | 1 | Selesai |
| G | Dashboard: kartu ke-5 sendirian di baris kedua, teks kartu terpotong di ponsel. | Susun 4+3 dan izinkan teks turun baris. | 1 | Selesai |
| H | Keterbacaan: teks 10,4-11,5px, abu `#94a3b8` di latar terang (kontras sekitar 2,3:1, standar 4,5:1). Tombol aksi kecil. | Naikkan ke 12-13px, gelapkan abu, perbesar target sentuh. | 2 | Selesai |
| I | Halaman Tugas: filter di bawah papan, lencana prioritas teks putih di latar kuning (kontras rendah), drag and drop belum diuji di layar sentuh. | Pindahkan filter ke atas, perbaiki lencana, uji sentuh. | 3 | Selesai |
| J | Label form tidak terhubung ke input (13 field Siswa, 26 field Buku Induk). Field wajib di Buku Induk tanpa tanda bintang. | Tambah `for` dan `id`, tandai field wajib. | 3 | Sebagian |
| K | `confirmDelete` ditulis ulang di sekitar 20 view, 77 `style=` inline, 9 view punya blok `<style>` sendiri. | Gabungkan ke `app.js` dan `custom.css`. | 3 | Belum |

## Hasil Paket 1 (selesai 7 Oktober 2026)
Item B, C, D, E, F, G dikerjakan dan diverifikasi dengan browser (desktop dan ponsel).

- **B Raport:** teks diganti "Nilai tersimpan setelah klik Simpan Perubahan Nilai". Bar Simpan menempel di bawah layar
  (`.em-sticky-save`) dan menampilkan "Ada perubahan yang belum disimpan" saat ada isian. Mengganti tahun/semester atau
  menutup halaman saat ada isian belum disimpan memunculkan konfirmasi (nilai dropdown dikembalikan bila dibatalkan,
  tidak ada peringatan saat menyimpan). Kolom capaian dibuat 3 baris.
- **C Login:** label menjadi "Email", link "Lupa Password?" dihapus, atribut `autocomplete` ditambahkan.
- **D Menu ponsel:** tombol melayang diganti bar atas tetap (`.em-topbar`, 56px) berisi tombol menu dan nama aplikasi.
  Padding atas konten 80px menjadi 72px. Bar berada di bawah overlay dan sidebar sehingga tidak menutupi menu.
- **E Warna:** `--bs-primary` dan variabel terkait (link, outline-primary, fokus form, checkbox, pagination) diarahkan ke
  hijau brand `#047857` di `public/css/custom.css`.
- **F Tanggal:** `format()` berpola F/M/D/l diganti `translatedFormat()` di dashboard, tugas, pengganti absensi, absensi, buku induk.
- **G Dashboard:** susunan 4+4 (Siswa, Kelas, Tugas, Surat Masuk / Guru Hadir, Sarana Rusak, Buku Induk, Kehadiran).
  Teks sub-kartu boleh turun baris sehingga tidak terpotong di ponsel, dan kartu sebaris sama tinggi.
- **Temuan tambahan:** kolom Tanggal di Absensi berupa `type="hidden"` (hanya label, tanggal tidak bisa dipilih).
  Diganti `type="date"` dengan `max` hari ini.

Catatan: opsi status absensi memakai emoji. Di browser tanpa font emoji (mis. Chromium headless) tampil sebagai kotak kosong;
di peramban biasa normal. Bukan bug, tapi pertimbangkan ikon Font Awesome bila ingin konsisten di semua perangkat.

Belum disentuh (sengaja, masuk Paket 2): overflow halaman Jadwal (448 dari 390px) dan Absensi (401 dari 390px) di ponsel,
kolom Status/Aksi tabel yang terpotong di ponsel, serta ukuran font dan kontras.

## Hasil Paket 2 + polesan menyeluruh (selesai 7 Oktober 2026)
Diukur ulang dengan skrip yang sama (18 halaman x desktop/ponsel), awal -> sekarang:

| Ukuran | Awal | Sekarang |
|---|---|---|
| Elemen teks di bawah 12px | 277 | 0 |
| Target ketuk di bawah 32px (ponsel) | 136 | 9 |
| Input tanpa label | 54 | 10 |
| Halaman yang melebar di ponsel | Jadwal (448px), Absensi (401px) | tidak ada |
| Error konsol / request gagal | 0 | 0 |

**A. Tabel di ponsel.** Atribut `data-hide-sm="1 5 6"` pada `<table>` menyembunyikan kolom tertentu di bawah 576px (nomor
kolom mulai dari 1; baris "data kosong" tetap tampil). 15 tabel sudah diberi atribut. Kolom terakhir (Aksi) menempel di
kanan sehingga tombol edit/hapus selalu terlihat. Tabel berisi input (raport, absensi) ditumpuk per baris dengan label
(`class="table-stack-sm"` + `data-label` pada `<td>`). Bayangan tipis menandai tabel yang masih bisa digeser.
**H. Keterbacaan.** Font minimal 12px (`.badge` bawaan Bootstrap 10,8px dinaikkan), teks abu diganti `--em-text-soft`
(`#5b6b80`, kontras sekitar 5:1), tombol kecil minimal 34px (38px di layar sentuh).
**I. Tugas.** Filter dipindah ke atas papan. Lencana prioritas kini berbentuk pil (lihat temuan 1 di bawah).
**J. Label form.** `initLabels()` di `public/js/app.js` menghubungkan `<label>` tanpa `for` ke input terdekat. Sisa 10 input
masih tanpa label (perlu dicek satu per satu di Paket 3).

**Polesan gaya** (di akhir `public/css/custom.css`, blok "POLISH v5"): token bayangan dan easing; konten halaman muncul
bertahap (fade-up 450ms); kartu statistik berurutan; tabel dengan header kecil huruf kapital dan hover hijau lembut; tombol
naik 1px saat hover dan menekan saat diklik; fokus keyboard yang jelas; badge, pagination, modal, dropdown, alert yang
lebih halus; sidebar dengan geser 2px saat hover, penanda menu aktif, dan animasi dropdown. Tombol aksi di tabel memakai
gaya "soft". Semua gerak dimatikan otomatis untuk pengguna dengan `prefers-reduced-motion`.
Catatan teknis: pakai `animation-fill-mode: backwards` (bukan `both`) agar efek hover tetap bekerja setelah animasi masuk.
CSS dan JS kini dimuat dengan `?v=<waktu modifikasi>` supaya browser tidak memakai salinan lama.

**Temuan bug saat Paket 2 (sudah diperbaiki)**
1. `jadwal/index` dan `tasks/index` memakai `@section('styles')`, padahal layout hanya membaca `@stack('styles')`, jadi
   CSS kedua halaman itu tidak pernah dimuat (penyebab tabel jadwal polos dan lencana prioritas menempel di pojok).
   Diganti `@push('styles')`. Aturan: di view, selalu pakai `@push('styles')` / `@push('scripts')`.
2. Drag and drop Tugas memanggil `/tasks/{id}/status` (path absolut) sehingga gagal saat aplikasi dilayani dari subfolder
   `/emadrasah`. Diganti `route('tasks.status', ...)`. Aturan: jangan menulis path absolut di JavaScript.
3. Sel pojok "Sesi & Waktu" di tabel jadwal tak terbaca (teks putih di latar hijau muda).

## Perbaikan sidebar (selesai 7 Oktober 2026)
Bug mode ciut yang diperbaiki (sumber utama: selector `.em-sidebar.is-collapsed + .em-main` tidak pernah cocok karena ada
`<header class="em-topbar">` di antara sidebar dan main; aturan footer dan `.status-bar` juga salah selektor):
- Konten, footer, dan bar status grid jadwal kini ikut menyempit (margin 260px -> 75px) dengan selector `~` / `:has()`.
- Tidak ada lagi scroll horizontal di dalam sidebar (teks menyusut ke `max-width: 0`, label grup menjadi garis pemisah).
- Header ciut: logo di tengah, tombol toggle bulat melayang di tepi sidebar (tidak lagi bertumpuk dengan logo).
- Submenu Kesiswaan sebelumnya tidak bisa dijangkau saat ciut; kini muncul sebagai flyout (hover atau fokus keyboard)
  dengan judul grup, posisi dihitung di JS (`--em-flyout-top`), dan jembatan hover agar tidak menutup saat mouse menyeberang.
- Footer ciut: avatar + tombol keluar bertumpuk (sebelumnya tombol keluar hilang).
- Keadaan ciut diterapkan sebelum render pertama (skrip kecil di layout) + kelas `em-preload` menahan transisi sampai
  halaman siap, jadi sidebar tidak berkedip atau beranimasi tiap pindah halaman.
- Ciut hanya berlaku di desktop: di ponsel dilepas otomatis dan dipulihkan saat kembali ke desktop (`syncCollapsed`).
- Gaya menyeluruh: induk dropdown tersorot bila anaknya aktif, garis pemandu submenu, submenu aktif langsung terbuka dari
  server, tombol tutup (X) di ponsel, lebar sidebar ponsel `min(300px, 86vw)`, posisi gulir sidebar dipertahankan antar
  halaman dan menu aktif otomatis digulirkan ke area terlihat.
- Aksesibilitas: toggle dropdown kini `role="button"`, `tabindex=0`, `aria-expanded`, bisa dioperasikan dengan Enter/Spasi.
- Favicon dideklarasikan (sebelumnya browser meminta `/favicon.ico` di domain utama dan mendapat 404).
Kode: blok "SIDEBAR v2" di akhir `public/css/custom.css`, `initSidebar()` di `public/js/app.js`, `components/sidebar.blade.php`.

## Rencana paket kerja
- **Paket 1 (cepat, berdampak besar untuk demo):** B, C, D, E, F, G. **Selesai.**
- **Paket 2 (paling terasa bagi pengguna):** A (tabel ponsel) dan H (keterbacaan). **Selesai**, bersama polesan gaya menyeluruh.
- **Paket 3 (perapian):** sisa J (10 input tanpa label), K (gabungkan `confirmDelete`, kurangi `style=` inline), 9 target ketuk yang masih kecil, dan uji drag and drop di layar sentuh.

## Temuan keamanan terkait UI (sudah diperbaiki)
Nama berisi tanda kutip bisa menjalankan JavaScript lewat atribut `onclick` tombol hapus
(XSS tersimpan, terbukti dengan data uji). Pola ini ada di 12 tempat dan semuanya sudah
memakai `Js::from`. Aturan untuk pekerjaan berikutnya: jangan menyisipkan nilai dari database
ke string JS atau atribut `on*=` dengan `'{{ ... }}'`; pakai `{{ Js::from($nilai) }}`.

## Cara mengulang pemeriksaan
Skrip Playwright yang dipakai (login, 18 halaman x 2 ukuran layar, ukur masalah umum) ada di
folder sementara sesi review dan belum disimpan di repo. Bisa ditambahkan ke `scripts/` bila
ingin dipakai ulang sebagai uji regresi tampilan setelah Paket 2.

## Catatan kerja untuk paket berikutnya
- Setelah mengubah banyak view sekaligus (terutama dengan regex), jalankan `php artisan view:cache` (mengompilasi semua
  view dan menangkap error sintaks) lalu `php artisan view:clear`, dan buka semua halaman, termasuk halaman edit dan
  tampilan daftar. Pada Paket 1 sebuah regex sempat merusak `jam-pelajaran/index` dan baru ketahuan di sapuan akhir.
- Pengujian yang menekan tombol Simpan akan mengubah data demo. Catat nilai awal dan kembalikan setelahnya.
- Jangan jalankan `php artisan route:cache` (route `/` memakai closure).
