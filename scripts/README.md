Extract documents helper

This folder contains a script to extract text from PDFs and run OCR on images.

Prerequisites (Windows):
- Python 3.8+
- Install Tesseract OCR: https://github.com/tesseract-ocr/tesseract
  - Add the Tesseract installation folder to your PATH, or set environment variable `TESSERACT_CMD`.
- (Optional) For some PDF -> image operations you may need `poppler` for `pdf2image` (not required for PyMuPDF fallback).

Install Python requirements:

```powershell
python -m pip install -r scripts/requirements.txt
```

Run extraction (scans project root and the Downloads folders used by the project):

```powershell
python scripts\extract_all_documents.py
```

Or provide custom paths (semicolon-separated):

```powershell
python scripts\extract_all_documents.py --paths "C:\path\to\pdfs;C:\another\folder"
```

Outputs will be written to `scripts/extracted_texts/` as .txt files.

## Laravel deployment permissions

Setelah deploy, pastikan `storage` dan `bootstrap/cache` bisa ditulis oleh user aplikasi dan web server:

```bash
APP_USER=dzakir WEB_GROUP=nogroup sudo -E bash scripts/fix-laravel-permissions.sh
```

Server ini memakai proses web/cache `nobody:nogroup`, sehingga `WEB_GROUP=nogroup`.
Di server lain, sesuaikan `WEB_GROUP` dengan group web server yang aktif, misalnya `www-data`, `nginx`, atau `apache`.
Default `APP_USER` adalah user shell saat script dijalankan, dan default `WEB_GROUP` adalah `www-data`.

## Backup database dan file upload

Backup manual:

```bash
php artisan emadrasah:backup
```

Command ini membuat arsip ZIP di `storage/app/private/backups` berisi `database/database.sql`, folder `uploads/`, dan `manifest.json`.
Secara default backup terjadwal harian pukul 23:30 melalui Laravel Scheduler, jadi cron server perlu menjalankan:

```bash
* * * * * cd /path/to/e-madrasah && php artisan schedule:run >> /dev/null 2>&1
```

Konfigurasi dapat diubah lewat `.env`: `EMADRASAH_BACKUP_ENABLED`, `EMADRASAH_BACKUP_PATH`, `EMADRASAH_BACKUP_RETENTION_DAYS`, `EMADRASAH_BACKUP_DB_CONNECTION`, `EMADRASAH_BACKUP_UPLOAD_DISK`, `EMADRASAH_MYSQLDUMP_BINARY`, dan `EMADRASAH_PG_DUMP_BINARY`.
