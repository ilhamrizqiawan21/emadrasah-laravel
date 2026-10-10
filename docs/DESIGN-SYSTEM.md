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
| Font | Inter (isi, tabel, angka tabular), Plus Jakarta Sans (judul) | Di-bundle lewat Fontsource, tanpa CDN. Angka memakai `tabular-nums` agar sejajar. |

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

## Gaya UI: "Lembut & Ramah" (opsi B), `resources/sass/_soft.scss`
Semua gaya B ada di satu partial yang menimpa `_custom.scss`; ubah di sana, jangan menambal di tiap halaman.

- **Sidebar presisi:** menempel penuh di tepi kiri (`--em-gap: 0`), sudut lurus, dipisah garis 1px, tanpa bayangan. Kartu, tombol, dan form di konten tetap membulat dan lembut.
- **Tombol buka-tutup sidebar** (`#sidebarToggleDesktop`): bulat 30px, titik tengahnya tepat di tepi sidebar (`right: -16px` = 15px setengah tombol + 1px garis batas) dan sejajar sumbu logo (header 89px). Status (`aria-expanded`, label, tooltip) diatur satu fungsi di `app.js`; pintasan Ctrl+B (diabaikan saat mengetik); pilihan bertahan di `localStorage`. Diuji di E2E (posisi ±0,5px, ciut/lebar, ARIA, bertahan, keyboard, laci HP).
- **Ikon:** semua ikon Font Awesome (`fas`); jangan memakai emoji atau set ikon lain. Ikon yang bermakna status ditulis lewat `--fa` (mis. `--fa: '\f058'`), bukan `content:` (kalah urutan dengan CSS Font Awesome). Cocokkan kode glyph dengan `node_modules/@fortawesome/fontawesome-free/scss/_variables.scss`, jangan dari ingatan.
- **Tombol hanya-ikon (aksi di tabel):** `app.js` menambah kelas `btn-icon` ke tombol tanpa teks; CSS menjadikannya lingkaran lembut sesuai warna maknanya (primary/warning/danger/info/success). CSS murni tidak bisa membedakan "ikon saja" dari "ikon + teks" (`:only-child` hanya menghitung elemen).
- **Tabel:** header bertinta merek huruf kecil kapital, baris lega dengan garis tipis, hover lembut, badge pil, angka tabular. Tabel 7+ kolom otomatis dipadatkan (`:has(th:nth-child(7))`) agar muat tanpa menggulir; badge dan tombol tidak boleh terpecah baris. Kolom nama memakai `<div class="em-identity"><x-avatar :name="..."/>...</div>`.
- **`<x-avatar :name>`:** inisial dua huruf (gelar Dr./Hj./Ust. dilewati) dengan 6 warna pastel stabil per nama.
- **Kartu statistik dashboard:** latar pastel dari `color-mix(--em-stat-color 11%, #fff)`; kartu utama mengikuti warna tema.

### Jebakan tambahan (pernah terjadi)
- Di layout ada `<script>` di antara `<aside>` dan `<main>`: selector sibling-langsung `+` tidak pernah cocok, pakai `~`.
- Jangan menimpa `padding` pada `.form-select` tanpa menyisakan ruang panah di kanan.
- Token warna tema: tiga shade palet pernah merujuk dirinya sendiri dan kanal `-rgb` tidak terdefinisi (diam-diam menghilangkan tint di tema bawaan). Dijaga `tests/Unit/ThemeTokensTest.php`.
