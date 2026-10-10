<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Adanya baris berarti raport semester itu sudah dirilis ke wali murid/siswa. */
    public function up(): void
    {
        Schema::create('raport_rilis', function (Blueprint $table) {
            $table->id();
            // tahun_pelajaran.id bertipe smallint unsigned; tipe kolom FK harus sama persis di MySQL.
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('semester');
            $table->timestamps();

            $table->unique(['tahun_pelajaran_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_rilis');
    }
};
