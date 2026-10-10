<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tautan akun wali murid/siswa ke data siswa; satu wali boleh memiliki beberapa anak. */
    public function up(): void
    {
        Schema::create('wali_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wali_siswa');
    }
};
