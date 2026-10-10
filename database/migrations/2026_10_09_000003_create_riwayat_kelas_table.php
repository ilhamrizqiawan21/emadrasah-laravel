<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->enum('hasil', ['naik', 'tinggal', 'lulus']);
            $table->unsignedBigInteger('kelas_asal_id')->nullable();
            $table->unsignedBigInteger('kelas_tujuan_id')->nullable();
            // Nama disalin agar riwayat tetap terbaca bila kelasnya kelak dihapus.
            $table->string('kelas_asal_nama');
            $table->string('kelas_tujuan_nama')->nullable();
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->timestamps();

            // Satu hasil per siswa per tahun pelajaran: mencegah naik dua kali dalam satu tahun.
            $table->unique(['siswa_id', 'tahun_pelajaran_id']);
            $table->index(['kelas_asal_id', 'tahun_pelajaran_id']);
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran');
            $table->foreign('kelas_asal_id')->references('id')->on('kelas')->nullOnDelete();
            $table->foreign('kelas_tujuan_id')->references('id')->on('kelas')->nullOnDelete();
            $table->foreign('dicatat_oleh')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_kelas');
    }
};
