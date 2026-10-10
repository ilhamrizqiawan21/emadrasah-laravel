# Konteks Proyek & Panduan Pengembangan

## 1. Deskripsi & Tujuan Proyek
- **Nama Proyek:** Emadrasah
- **Tujuan Utama:** Aplikasi yang bertujuan untuk pengelolaan madrasah , dan staff Tata Usaha
- **Target Audiens/Pengguna:** Madrasah dan Sekolah Sekolah dalam naungan Kementerian Agama

## 2. Arsitektur & Tujuan Proyek
- **Pola Arsitektur:** MVC
- **Peta Folder Penting:** 
    - '/src/components': Komponen UI reusable (hanya presentasional)
    - '/src/hooks' : Logika bisnis dan state global.
    - '/src/api' : Endpoint integrasi backend

## 3. Tech Stack & Dependensi Utama
- **Frontend / Backend:** Laravel , CSS Boostrap, Vite , Tailwind
- **State Management:** Admin Panel
- **Database / ORM:** MySQL dengan Eloquent
- **Testing:** NPM Test 

## 4. Konvensi Koding (Coding Style)
- **Format Kontrak:** PHP Version adalah PHP 8.3+, Selalu jalankan pengecekan ./vendor/bin/pint --test, dan format otomatis ./vendor/bin/pint
- **Identitas dan Berkas:** PHP, Blade, JS: 4 Spasi, YAML/JSON: 2 Spasi, Line ending: LF (\n), UTF-8, dengan trailing newline di akhir file.
- Gunakan 'camelCase' unruk method/action, Eloquent dan helper
- Gunakan 'snake_case' untuk tabel database dan kolom database
- Gunakan 'kebab-case' untuk URL-Route, Blade Views
- Gunakan 'dot.notation' untuk nama route
- Gunakan bahasa inggris atu teknis ringkas untuk komentar dan commit

## 5. Alur Kerja & Perintah Eksekusi (Build/Test)
Sebelum menganggap pekerjaan selesai atau membuat Pull Request, pastikan anda sudah menjalankan :
1. Build Proyek : npm run build
2. Menjalankan Linter : npm run lint
3. Menjalankan test : php artisan test dan npm run test

## 6. Batasan penting (Negative Boundaries)
- **Dilarang** mengubah menjalankan push tanpa izin atau instruksi dari saya
- **Jangan Pernah** hardocode API Key atau kredensial rahasia apapun
- **Setting Env** seperti nama database, user, dan password itu bagian saya
- **Jangan terlalu lama dan berputar-putar** dalam mengecek dependency, versi, library, lakukan secukupnya, agar tidak menguras waktu yang lama, namun jangan juga anda ceroboh dalam memahami versi versi yang saya sebutkan diatas
- **Selalu Tanyakan** Poin-Poin atau langkah eksekusi yang membutuhkan Decission dari saya
- **Jangan Selalu Tanya** saat akan mengeksekusi command terminal selain rm, rf, rmdir.