<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_siswa', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('siswa_id');
            // Kelas saat absen dicatat, supaya rekap tetap benar setelah siswa naik kelas.
            $table->unsignedBigInteger('kelas_id');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->string('keterangan')->nullable();
            $table->unsignedBigInteger('dicatat_oleh')->nullable();
            $table->timestamps();

            $table->unique(['tanggal', 'siswa_id']);
            $table->index(['kelas_id', 'tanggal']);
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('dicatat_oleh')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa');
    }
};
