<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Jenis pelanggaran berpoin, catatan pelanggaran siswa, dan catatan konseling BK (rahasia, hanya staf). */
    public function up(): void
    {
        Schema::create('pelanggaran_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->string('kategori', 10)->default('ringan');
            $table->unsignedSmallInteger('poin');
            $table->timestamps();
        });

        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('jenis_id')->constrained('pelanggaran_jenis')->restrictOnDelete();
            $table->unsignedSmallInteger('poin');   // salinan poin saat dicatat; tidak berubah bila jenis diedit
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'tanggal']);
        });

        Schema::create('catatan_bk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('topik', 150);
            $table->text('uraian');
            $table->text('tindak_lanjut')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['siswa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catatan_bk');
        Schema::dropIfExists('pelanggaran');
        Schema::dropIfExists('pelanggaran_jenis');
    }
};
