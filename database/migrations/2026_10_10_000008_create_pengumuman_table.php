<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Pengumuman madrasah untuk semua pengguna, guru saja, atau wali murid dan siswa. */
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('isi');
            $table->string('target', 10)->default('semua');
            $table->date('terbit_pada');
            $table->date('berakhir_pada')->nullable();
            $table->boolean('disematkan')->default(false);
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('terbit_pada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
