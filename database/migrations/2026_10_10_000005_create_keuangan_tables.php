<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tagihan siswa (SPP dan sejenisnya) beserta pembayarannya. Jumlah dalam rupiah utuh. */
    public function up(): void
    {
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->string('jenis', 30)->default('SPP');
            $table->string('periode', 7)->nullable();
            $table->unsignedInteger('jumlah');
            $table->date('jatuh_tempo');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'jenis', 'periode']);
            $table->index('jatuh_tempo');
        });

        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->unsignedInteger('jumlah');
            $table->date('tanggal');
            $table->string('metode', 20)->default('tunai');
            $table->string('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
        Schema::dropIfExists('tagihan');
    }
};
