# Design system e-Madrasah

Acuan agar halaman baru konsisten. Basis: Bootstrap 5 (Sass) + tema `resources/sass/_custom.scss`, dibangun dengan Vite.

## Token (CSS variable di `:root`, `_custom.scss`)
| Kelompok | Variabel | Catatan |
|---|---|---|
| Warna utama | `--em-green-950 … --em-green-50`, `--em-green-{700,600,500}-rgb` | Skala dari **satu warna** di menu Pengaturan (`ThemePalette`). Nama "green" historis; nilainya mengikuti warna pilihan klien. |
| Bootstrap | `--bs-primary`, `--bs-primary-rgb` | Ikut berubah bersama skala di atas. |
| Netral | `--em-gray-*`, `--em-text-muted`, `--em-border` | |
| Aksen | `--em-gold`, `--em-gold-light` | Tidak mengikuti tema. |
| Bentuk | `--em-r-*` (radius), `--em-shadow-*`, `--em-ease*`, `--em-dur-*` | |
| Font | DM Sans (isi), Plus Jakarta Sans (judul) | Di-bundle lewat Fontsource, tanpa CDN. |

**Aturan:** jangan menulis warna hijau/hex merek langsung di CSS. Pakai `var(--em-green-*)` atau `rgba(var(--em-green-700-rgb), .12)`; kalau tidak, warna tidak ikut berubah saat klien mengganti tema. Warna yang bermakna status (hijau hadir, merah rusak, kuning tertunda) boleh tetap tertulis.

## Komponen Blade (`resources/views/components`, `partials`)
| Komponen | Fungsi |
|---|---|
| `<x-page-header title="…">` | Judul halaman. Slot bawaan = subjudul, `<x-slot:actions>` = tombol di kanan. Prop `cols` mengatur lebar kolom kiri. |
| `<x-empty-row :colspan="N" icon="fa-…">` | Baris "data kosong" di tabel (`@empty`). |
| `<x-alert>` / `components/alert` | Flash `success`/`error` dan galat validasi. |
| `partials/pdf-kop`, `partials/pdf-ttd` | Kop dan tanda tangan kepala madrasah untuk PDF (gaya inline untuk dompdf). |
| `partials/theme` | Menyuntikkan warna tema dari Pengaturan. |

`$madrasah` tersedia di semua view (`$madrasah->nama`, `->logoUrl()`, `->kepala_nama`, …). Jangan menulis nama madrasah di view.

## Pola halaman
- **Daftar (index):** `x-page-header` + kartu berisi tabel. Tabel diberi `data-hide-sm` agar di HP (<576px) tiap baris menjadi kartu berlabel (label otomatis dari judul kolom oleh `app.js`). Kolom "No" disembunyikan di kartu.
- **Form (tambah/ubah):** `x-page-header` dengan tombol "Kembali" (`btn btn-light border`) di slot aksi, lalu kartu berisi form. Judul tidak diulang di kepala kartu. Contoh acuan: `tahun-pelajaran/create`, `guru/create`.
- **Data kosong:** selalu `x-empty-row` dengan ikon.
- **Form POST:** tombol submit otomatis nonaktif + spinner saat dikirim (`app.js`). Lewati dengan `data-no-loading` pada `<form>`. Form yang dibatalkan handler lain (validasi, `confirm`) tidak terpengaruh.
- **Absensi di HP:** kartu kompak khusus (`resources/sass/pages/_absensi.scss`).

## CSS per halaman
`resources/sass/pages/_<halaman>.scss`, diimpor dari `app.scss`. Jangan menaruh `<style>` di view (kecuali view PDF dompdf).

## Responsif
Titik uji: 375px (HP), 768px (tablet), 1440px (desktop). Syarat: tanpa overflow horizontal, target sentuh ≥ 38px di layar sentuh.

## Jebakan yang pernah terjadi
- Jangan mencampur `@php(...)` satu baris dengan blok `@php … @endphp` di satu file Blade; Blade menelan HTML di antaranya.
- Selector CSS yang menimpa aturan stack tabel harus lebih spesifik (lihat komentar di `_absensi.scss`).
- Aset bawaan (logo/favicon) di-cache `immutable`; versi di URL harus berubah saat berkas bawaan diganti (`Madrasah::brandingUrl()`).
