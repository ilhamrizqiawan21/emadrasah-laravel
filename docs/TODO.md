# Rencana kerja: menyiapkan e-Madrasah untuk dijual dan online

Disusun dari kondisi project saat ini. Tiap fase bisa dikerjakan terpisah, urutannya dari yang paling berpengaruh. Keputusan di Fase 0 menentukan fase sesudahnya.

## Fase 0: Keputusan dan audit (sebelum menulis kode)
- [ ] Putuskan model penjualan: satu instalasi per client, atau satu sistem multi-tenant
- [ ] Putuskan arah desain: Bootstrap yang di-tema ulang, atau pindah ke Tailwind
- [ ] Kumpulkan referensi tampilan yang disukai
- [ ] Jalankan aplikasi, ambil screenshot semua halaman utama (desktop dan HP) dengan Playwright
- [ ] Buat daftar masalah tampilan dari screenshot, urutkan berdasarkan prioritas
- [ ] Siapkan data seeder demo yang realistis, supaya calon client melihat tampilan yang terisi

## Fase 1: Fondasi (kecil, risiko rendah)
- [ ] Pasang Vite, lalu bundel CSS dan JS sendiri
- [ ] Hapus ketergantungan CDN (Bootstrap, Font Awesome, Google Fonts), pakai versi lokal yang di-bundle
- [ ] Pindahkan 9 blok `<style>` inline ke CSS terpusat
- [ ] Rapikan `welcome.blade.php` (sisa Tailwind/Vite) atau hapus
- [ ] Tambah Alpine.js untuk interaksi ringan (sidebar, modal, dropdown)
- [ ] Pastikan `npm run build` dan `npm run dev` benar-benar berfungsi

## Fase 2: Desain ulang tampilan (pekerjaan terbesar)
- [ ] Tetapkan design system: palet warna, tipografi, jarak, bayangan, radius
- [ ] Buat komponen Blade yang bisa dipakai ulang: tombol, kartu, tabel, form, badge, modal, alert, pagination
- [ ] Desain ulang halaman login
- [ ] Desain ulang layout utama: sidebar dan topbar
- [ ] Desain ulang dashboard
- [ ] Terapkan standar desain ke satu modul contoh (misalnya Siswa) sebagai acuan
- [ ] Terapkan ke modul akademik: kelas, mapel, jadwal, jam pelajaran, tahun pelajaran
- [ ] Terapkan ke modul kesiswaan: siswa, buku induk, perkembangan siswa
- [ ] Terapkan ke modul raport
- [ ] Terapkan ke modul administrasi: surat masuk/keluar, template surat, arsip
- [ ] Terapkan ke modul sarana prasarana, agenda guru, absensi, tasks, users
- [ ] Rapikan tabel lebar di HP (scroll horizontal, atau tampilan kartu)
- [ ] Tambah empty state, loading state, dan pesan error yang ramah
- [ ] Uji di berbagai ukuran layar (HP, tablet, desktop)
- [ ] Rapikan tampilan hasil cetak dan PDF (surat, raport)

## Fase 3: Kesiapan produksi dan keamanan
- [ ] Tinjau dan update 31 advisory keamanan Composer
- [ ] Siapkan konfigurasi produksi: `APP_DEBUG=false`, `APP_ENV=production`, HTTPS, cookie aman
- [ ] Pindahkan database ke MySQL atau PostgreSQL
- [ ] Konfigurasi mail sungguhan (SMTP atau layanan email), ganti driver `log`
- [ ] Siapkan queue worker dan scheduler
- [ ] Siapkan penyimpanan file upload, lengkap dengan batasan ukuran dan tipe
- [ ] Siapkan backup database otomatis dan prosedur restore
- [ ] Siapkan logging dan monitoring error
- [ ] Tinjau ulang rate limiting login, kebijakan password, dan session
- [ ] Jalankan audit keamanan menyeluruh sebelum rilis

## Fase 4: Kualitas dan test
- [ ] Tambah test fitur untuk alur utama: login, CRUD siswa, raport, surat
- [ ] Perluas `RouteAuditTest` agar mencakup semua route dan role
- [ ] Tambah test end-to-end (Playwright) untuk alur penting
- [ ] Jalankan Pint dan pastikan gaya kode seragam
- [ ] Ukur kecepatan halaman (Lighthouse), perbaiki query N+1 dan aset yang berat

## Fase 5: Paket penjualan
- [ ] Siapkan akun dan data demo untuk presentasi ke calon client
- [ ] Tulis dokumentasi instalasi dan deployment
- [ ] Tulis panduan pengguna per role (admin, guru, dst.)
- [ ] Siapkan prosedur onboarding client baru (setup, impor data awal)
- [ ] Perbarui README (masih menyebut PHP 8.3)
- [ ] Putuskan lisensi dan skema update/pemeliharaan untuk client
- [ ] Putuskan nasib `maatwebsite/excel`: apakah ekspor/impor Excel dibutuhkan client (dicabut karena belum kompatibel PHP 8.5)

## Urutan yang disarankan
1. Fase 0, lalu Fase 1. Keduanya cepat dan membuka jalan.
2. Fase 2 dikerjakan per modul, mulai dari login, dashboard, dan satu modul contoh. Nilai arah desain dulu sebelum lanjut ke modul lain.
3. Fase 3 dan 4 berjalan paralel dengan akhir Fase 2.
4. Fase 5 menjelang demo ke calon client.
