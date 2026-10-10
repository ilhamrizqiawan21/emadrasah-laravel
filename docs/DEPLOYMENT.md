# Panduan deploy e-Madrasah

Satu instalasi melayani satu madrasah. Panduan ini untuk server Linux dengan Nginx/Apache.

## 1. Kebutuhan server
- **PHP 8.3 atau lebih baru** dengan ekstensi: `ctype, dom, fileinfo, filter, gd, hash, iconv, json, libxml, mbstring, openssl, pcre, pdo_mysql (atau pdo_pgsql), session, tokenizer, xml, zip`.
  `gd` dibutuhkan dompdf untuk logo PNG di PDF; `zip` dibutuhkan perintah backup.
- Composer 2. Node.js hanya untuk membangun aset (bisa dilakukan di komputer lain, lalu unggah `public/build`).
- MySQL 8.x/MariaDB (teruji di MySQL 8.4) atau PostgreSQL (belum teruji). Untuk backup otomatis, `mysqldump` (atau `pg_dump`) harus terpasang di server.
- Dependensi dikunci agar cocok dengan PHP 8.3 (`config.platform.php` di `composer.json`), jadi aman dipasang di hosting PHP 8.3 maupun yang lebih baru.

## 2. Pemasangan pertama
```bash
git clone <repo> /var/www/emadrasah && cd /var/www/emadrasah
composer install --no-dev --optimize-autoloader
npm ci && npm run build                 # atau unggah public/build dari mesin lain

cp .env.production.example .env         # lalu isi APP_URL, DB_*, TRUSTED_PROXIES
php artisan key:generate --force
php artisan migrate --force
php artisan madrasah:install            # membuat admin pertama (kata sandi dibuat/ditanya, min. 10 karakter)

php artisan config:cache && php artisan route:cache && php artisan view:cache
```
Jangan menjalankan `db:seed` di produksi: seeder produksi tidak membuat akun demo, dan data contoh hanya untuk pengembangan.

Izin berkas: web server hanya perlu menulis ke `storage/` dan `bootstrap/cache/`.
```bash
chown -R www-data:www-data storage bootstrap/cache && chmod -R u+rwX,g+rwX storage bootstrap/cache
```

## 3. Web server
Arahkan *document root* ke folder **`public/`** (bukan root proyek), paksa HTTPS, dan tolak berkas tersembunyi.
```nginx
root /var/www/emadrasah/public;
index index.php;
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; fastcgi_pass unix:/run/php/php8.3-fpm.sock; }
location ~ /\.(?!well-known) { deny all; }
client_max_body_size 60M;   # unggahan dokumen siswa: maks. 10 berkas x 5 MB per permintaan
```
Setelah `.env` benar dan https aktif, tutup port 80 atau alihkan ke 443. Cek kesehatan: `GET /up`.

Batas unggahan aplikasi: 5 MB per berkas (dokumen siswa, arsip, tugas), 2 MB (foto sarana, scan surat masuk), 1 MB (logo); dokumen siswa maksimal 10 berkas per permintaan. Atur PHP agar tidak lebih kecil dari itu: `upload_max_filesize = 6M`, `post_max_size = 60M`, `max_file_uploads = 20`.

## 4. Penjadwal (wajib untuk backup otomatis)
Tambahkan satu entri cron untuk pengguna web server:
```
* * * * * cd /var/www/emadrasah && php artisan schedule:run >> /dev/null 2>&1
```
Jadwal yang berjalan: `madrasah:backup` setiap hari 01:30 dan `madrasah:pengingat` setiap hari 07:00 (tugas jatuh tempo/terlambat dan sarana yang dipinjam lebih dari 7 hari, masuk ke kotak notifikasi petugas). Periksa dengan `php artisan schedule:list`.

Notifikasi di dalam aplikasi (lonceng di topbar) bersifat sinkron dan tidak memakai antrean. Kabar siswa alpha ke wali juga dikirim lewat email **hanya bila** `MAIL_MAILER` bukan `log`/`array` dan akun wali punya email; kegagalan kirim tidak menggagalkan penyimpanan absensi dan hanya dicatat di log.

Antrean (`queue:work`) belum dibutuhkan karena aplikasi belum memakai *job*. Bila nanti ditambahkan, jalankan `php artisan queue:work --tries=3` lewat Supervisor/systemd.

## 5. Backup dan pemulihan
`php artisan madrasah:backup` membuat `storage/app/backups/backup-<tanggal>.zip` (izin 0600) berisi database dan seluruh berkas unggahan (`storage/app/private`). Cadangan lebih dari 14 hari dihapus otomatis (`--keep=N` untuk mengubah).

**Salin cadangan keluar server.** Cadangan di server yang sama tidak melindungi dari kerusakan disk atau server hilang. Cara paling mudah: isi `BACKUP_DISK` di `.env` dengan nama disk dari `config/filesystems.php` (mis. disk `s3`, `sftp`, atau folder hasil mount NAS yang didaftarkan sebagai disk `local` baru). `madrasah:backup` lalu mengunggah zip hari itu ke disk tersebut dan menghapus salinan di sana yang lebih tua dari `--keep` hari. Jika unggahan gagal, perintah berakhir dengan kode galat (cadangan lokal tetap ada). Alternatif: rsync/rclone terjadwal dari server lain. Status cadangan terakhir tampil di dashboard admin (peringatan bila lebih dari 48 jam).

Pemulihan:
```bash
php artisan down
unzip backup-YYYYMMDD-HHMMSS.zip -d /tmp/pulih
mysql -u emadrasah -p emadrasah < /tmp/pulih/database.sql      # SQLite: salin database.sqlite ke database/database.sqlite
cp -a /tmp/pulih/uploads/. storage/app/private/
php artisan up
```
**Uji pemulihan** ke server percobaan sekurang-kurangnya sekali sebelum dipakai sungguhan.

## 6. Pembaruan aplikasi
```bash
php artisan down
php artisan madrasah:backup
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## 7. Daftar periksa keamanan sebelum rilis
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` terisi (jangan sama dengan instalasi lain)
- [ ] `APP_URL` memakai `https://`, sertifikat TLS valid, `SESSION_SECURE_COOKIE=true`
- [ ] `TRUSTED_PROXIES` diisi bila di belakang proxy
- [ ] Tidak ada akun demo: `php artisan tinker --execute="echo App\Models\User::where('email','admin@madrasah.id')->exists() ? 'ADA' : 'aman';"`
- [ ] Database memakai user khusus (bukan root), kata sandi kuat
- [ ] `.env`, `storage/`, `vendor/` tidak terjangkau dari web (document root = `public/`)
- [ ] Cron penjadwal aktif dan salinan cadangan keluar server berjalan
- [ ] `composer audit` bersih sebelum tiap rilis
- [ ] Identitas madrasah, logo, dan warna sudah diisi di menu Pengaturan
