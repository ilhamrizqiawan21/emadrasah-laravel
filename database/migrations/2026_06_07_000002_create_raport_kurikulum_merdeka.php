<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Migrasi Khusus Laporan Capaian Hasil Belajar (Raport) - Kurikulum Merdeka
     */
    public function up(): void
    {
        // 1. Tabel Nilai Akademik
        Schema::create('raport_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester')->comment('1=Ganjil, 2=Genap');
            $table->unsignedBigInteger('mapel_id');
            $table->integer('nilai_akhir')->nullable();
            $table->text('capaian_kompetensi')->nullable()->comment('Deskripsi kemajuan/capaian siswa');
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id')->on('mapels')->onDelete('cascade');
        });

        // 2. Tabel Ekstrakurikuler (Nilai Raport)
        Schema::create('raport_ekskul', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester');
            $table->string('nama_ekskul', 100);
            $table->string('predikat', 20)->comment('Sangat Baik, Baik, Cukup');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        // 3. Tabel Ketidakhadiran (Presensi Semester)
        Schema::create('raport_absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester');
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('alpa')->default(0);
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        // 4. Tabel Proyek P5-PPRA (Profil Pelajar Pancasila & Rahmatan Lil Alamin)
        Schema::create('raport_p5_ppra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->string('tema_proyek', 255);
            $table->string('nama_proyek', 255);
            $table->text('deskripsi_proyek')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        // 5. Tabel Detail Capaian P5-PPRA (Sub-Elemen)
        Schema::create('raport_p5_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('raport_p5_id');
            $table->string('dimensi', 100);
            $table->string('sub_elemen', 255);
            $table->enum('nilai', ['MB', 'B', 'BSH', 'SAB'])->comment('Mulai Berkembang, Berkembang, Berkembang Sesuai Harapan, Sangat Berkembang');
            $table->timestamps();

            $table->foreign('raport_p5_id')->references('id')->on('raport_p5_ppra')->onDelete('cascade');
        });

        // 6. Catatan Wali Kelas
        Schema::create('raport_catatan_wali', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester');
            $table->text('catatan')->nullable();
            $table->string('kenaikan_kelas', 50)->nullable()->comment('Naik ke kelas X / Tinggal di kelas X');
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_catatan_wali');
        Schema::dropIfExists('raport_p5_detail');
        Schema::dropIfExists('raport_p5_ppra');
        Schema::dropIfExists('raport_absensi');
        Schema::dropIfExists('raport_ekskul');
        Schema::dropIfExists('raport_nilai');
    }
};
