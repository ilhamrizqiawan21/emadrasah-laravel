# Fase 0: Hasil audit

Tanggal audit: 2026-10-08. Metode: aplikasi dijalankan lokal, login sebagai admin, 19 halaman utama diambil di desktop (1440px) dan HP (375px) dengan Playwright, setelah animasi masuk selesai.

## Ringkasan
Tampilan sekarang **lebih baik dari yang diduga**: konsisten (tema hijau emerald), tanpa error konsol, tanpa overflow horizontal di semua halaman, tombol dan form di HP sudah proporsional. Masalahnya bukan "rusak", melainkan **terasa seperti template Bootstrap generik**, ada beberapa kekurangan lokalisasi, dan fondasi produksi belum siap. Karena itu **tidak perlu menulis ulang dari nol**; yang dibutuhkan adalah poles dan penguatan identitas visual.

## Bug nyata yang ditemukan dan sudah diperbaiki
| Temuan | Dampak | Status |
|---|---|---|
| Tidak ada migration untuk tabel `sessions`, `cache`, `jobs` padahal `.env.example` memakai driver `database` | Instalasi baru: semua halaman termasuk login **error 500** | **Diperbaiki**: `database/migrations/2026_10_08_000001_create_framework_infrastructure_tables.php` |

## Temuan yang belum diperbaiki (masuk ke fase berikutnya)

### Lokalisasi dan waktu (prioritas tinggi, murah)
- `APP_LOCALE=en` dan `timezone=UTC`: tanggal tampil "Thursday, 08 October 2026", "Bulan October", "10 Oct 2026". Folder `lang/id` sudah ada tetapi tidak aktif.
- Timezone UTC berisiko membuat "hari ini" pada absensi dan agenda meleset sampai 7 jam dari waktu Indonesia (WIB).
- Perbaikan: `APP_LOCALE=id`, `timezone=Asia/Jakarta`, `Carbon::setLocale('id')`, lalu cek 10 tempat pemakaian format tanggal di view.

### Performa
- Satu halaman memuat sekitar **831 KB** dan 11 request, termasuk 3 CDN (jsDelivr, cdnjs, Google Fonts). Chart.js dimuat dari CDN hanya untuk satu grafik di dashboard.
- `public/images/logo.png` berukuran **385 KB** untuk logo kecil. Cukup dikonversi ke SVG atau WebP, target di bawah 30 KB.
- Semua konten memakai animasi fade-in sekitar 1 detik, sehingga halaman terasa lambat.

### Responsive di HP
- Tabel lebar di HP: `jam-pelajaran`, `buku-induk`, dan `surat-masuk` melebihi lebar container. Halaman lain menyembunyikan kolom (misalnya Siswa menghilangkan Kelas, L/P, Status), jadi informasi penting tidak terlihat di HP.
- Label kartu statistik terpotong di HP ("TUGAS PENDIN", "SARANA RUSAK").
- `tasks` memiliki 8 sampai 9 target sentuh yang lebih kecil dari 32px (tidak nyaman disentuh).
- Absensi di HP sangat panjang (sekitar 2800px) karena setiap guru memakai satu blok penuh.

### Tampilan dan identitas (soal "kurang puas")
- Dashboard, tabel, dan form sudah rapi tetapi memakai gaya Bootstrap standar tanpa ciri khas; tampilan akan terlihat sama seperti banyak aplikasi lain.
- Sidebar desktop memuat banyak menu sekaligus; submenu Kesiswaan membuat sidebar panjang dan harus di-scroll.
- Halaman Jadwal kosong sampai kelas dipilih (empty state ada, tetapi bisa lebih membantu).
- Teks login "Versi 2.0 Modern Edition" dan hak cipta MTs Al-Ihsan Batujajar ter-hard-code. Untuk dijual, identitas madrasah harus bisa dikonfigurasi per client.

### Data demo
- Data demo kecil (6 siswa, 8 guru, 6 kelas). Untuk demo ke calon client, tabel dan grafik terlihat sepi.
- `DummyDataSeeder` tidak idempotent: menjalankan ulang gagal dengan error UNIQUE pada `agenda_guru` (tanggal + guru_id). Perlu diperbaiki sebelum dipakai untuk demo berulang.

### Keamanan dan produksi (detail ada di Fase 3)
- Kredensial demo bawaan (`admin@madrasah.id / admin123`, dst.) tidak boleh ikut ke produksi.
- 31 advisory Composer belum ditangani.

## Keputusan user (2026-10-08)
1. **Model penjualan:** satu instalasi per client. Identitas madrasah dikonfigurasi per instalasi lewat `.env`/config, tanpa multi-tenant.
2. **Arah desain:** Bootstrap 5 + Vite (Sass), di-tema ulang. Tailwind tidak dipakai.
3. Referensi tampilan: belum ada (opsional).

## Status perbaikan setelah audit
- [x] Migration tabel session/cache/queue (login tidak lagi 500)
- [x] Locale `id` dan timezone `Asia/Jakarta`; dua tempat `date('F')` diganti `translatedFormat`
- [x] Logo 385 KB menjadi 18 KB
- [x] Seeder demo idempotent (kunci `tanggal` memakai Carbon, bukan string)
- [x] CDN dihapus; Bootstrap, Font Awesome 6, font, dan Chart.js di-bundle lewat Vite
- [ ] Tabel lebar di HP, label kartu terpotong, target sentuh kecil, absensi terlalu panjang di HP (Fase 2)
- [x] Identitas madrasah configurable dari menu Pengaturan (Fase 2, 2026-10-08)
- [ ] Data demo masih kecil (Fase 0/5)
- [ ] 31 advisory Composer dan kredensial demo (Fase 3)

Catatan: mengubah timezone dari UTC ke Asia/Jakarta menggeser tampilan `created_at` data lama sebanyak 7 jam. Aman untuk instalasi baru; untuk data lama perlu dipertimbangkan.

## Screenshot
Screenshot ada di folder scratchpad sesi audit (tidak disimpan di repo). Bisa diambil ulang kapan saja dengan skrip Playwright yang sama.
