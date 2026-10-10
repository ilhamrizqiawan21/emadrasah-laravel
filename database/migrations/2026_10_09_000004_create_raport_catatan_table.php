<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raport_catatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester');
            $table->text('catatan_wali')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_pelajaran_id', 'semester'], 'uq_raport_catatan');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_catatan');
    }
};
