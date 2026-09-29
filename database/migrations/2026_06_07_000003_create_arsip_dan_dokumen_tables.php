<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sarana, Surat, Tasks, & Dokumen — sesuai skema database madrasah_db (7).sql
     */
    public function up(): void
    {
        // 1. Kategori Sarana
        Schema::create('kategori_sarana', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->timestamps();
        });

        // 2. Sarana Prasarana
        Schema::create('sarana_prasarana', function (Blueprint $table) {
            $table->id();
            $table->string('kode_sarana')->unique();
            $table->string('nama_sarana');
            $table->unsignedBigInteger('kategori_id');
            $table->text('spesifikasi')->nullable();
            $table->integer('jumlah')->default(1);
            $table->integer('stok_tersedia')->default(0);
            $table->enum('kondisi', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->default('baik');
            $table->string('lokasi_ruang')->nullable();
            $table->year('tahun_pengadaan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->foreign('kategori_id')->references('id')->on('kategori_sarana')->onDelete('restrict');
        });

        // 3. Peminjaman Sarana
        Schema::create('peminjaman_sarana', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sarana_id');
            $table->string('peminjam');
            $table->enum('tipe_peminjam', ['guru', 'siswa'])->default('guru');
            $table->date('tanggal_pinjam');
            $table->date('tanggal_kembali')->nullable();
            $table->decimal('denda', 10, 2)->default(0.00);
            $table->enum('status', ['dipinjam', 'dikembalikan'])->default('dipinjam');
            $table->timestamps();
            $table->foreign('sarana_id')->references('id')->on('sarana_prasarana')->onDelete('cascade');
        });

        // 4. Pemeliharaan Sarana
        Schema::create('pemeliharaan_sarana', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sarana_id');
            $table->date('tanggal_pemeliharaan');
            $table->decimal('biaya', 10, 2)->default(0.00);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['proses', 'selesai'])->default('proses');
            $table->date('tanggal_selesai')->nullable();
            $table->string('teknisi')->nullable();
            $table->timestamps();
            $table->foreign('sarana_id')->references('id')->on('sarana_prasarana')->onDelete('cascade');
        });

        // 5. Surat Masuk
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_agenda')->unique();
            $table->string('asal_surat');
            $table->string('nomor_surat')->nullable();
            $table->string('perihal');
            $table->date('tanggal_terima');
            $table->date('tanggal_surat')->nullable();
            $table->text('disposisi')->nullable();
            $table->string('file_scan')->nullable();
            $table->enum('status', ['diterima', 'diproses', 'selesai'])->default('diterima');
            $table->timestamps();
        });

        // 6. Surat Keluar
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->string('tujuan');
            $table->string('perihal');
            $table->date('tanggal_kirim');
            $table->string('lampiran')->nullable();
            $table->string('file_draft')->nullable();
            $table->timestamps();
        });

        // 7. Template Surat
        Schema::create('template_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->text('konten');
            $table->timestamps();
        });

        // 8. Tasks
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('prioritas')->default('sedang');
            $table->date('deadline')->nullable();
            $table->enum('status', ['antrean', 'proses', 'selesai'])->default('antrean');
            $table->string('kategori', 100)->nullable();
            $table->string('attachment')->nullable();
            $table->integer('progress_persen')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
        });

        // 9. Task Logs
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
        });

        // 10. Siswa Dokumen
        Schema::create('siswa_dokumen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('jenis_dokumen')->comment('Akta, KK, Ijazah, KIP, dll');
            $table->string('file_path');
            $table->string('nama_file')->nullable();
            $table->timestamps();
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        // 11. Arsip Akademik
        Schema::create('arsip_akademik', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedTinyInteger('semester')->comment('1=Ganjil, 2=Genap');
            $table->string('nama_arsip')->comment('Contoh: Leger Kelas 7A, Raport Kolektif');
            $table->string('file_path');
            $table->enum('tipe', ['Leger', 'RDM', 'Lainnya'])->default('Leger');
            $table->timestamps();
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_akademik');
        Schema::dropIfExists('siswa_dokumen');
        Schema::dropIfExists('task_logs');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('template_surat');
        Schema::dropIfExists('surat_keluar');
        Schema::dropIfExists('surat_masuk');
        Schema::dropIfExists('pemeliharaan_sarana');
        Schema::dropIfExists('peminjaman_sarana');
        Schema::dropIfExists('sarana_prasarana');
        Schema::dropIfExists('kategori_sarana');
    }
};
