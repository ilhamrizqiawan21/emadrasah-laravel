<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Brankas Dokumen Siswa (E-Document)
        Schema::create('siswa_dokumen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('jenis_dokumen')->comment('Akta, KK, Ijazah, KIP, dll');
            $table->string('file_path');
            $table->string('nama_file')->nullable();
            $table->timestamps();

            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        // 2. Tabel Arsip Administrasi Akademik (Legger/RDM)
        Schema::create('arsip_akademik', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tahun_pelajaran_id');
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
    }
};
