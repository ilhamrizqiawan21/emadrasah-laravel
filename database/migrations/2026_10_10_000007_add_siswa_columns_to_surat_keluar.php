<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Surat yang diterbitkan untuk siswa (mis. keterangan aktif) tercatat di register surat keluar beserta salinan datanya. */
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->foreignId('siswa_id')->nullable()->after('id')->constrained('siswa')->nullOnDelete();
            $table->string('jenis', 30)->nullable()->after('siswa_id');
            $table->string('keperluan')->nullable()->after('perihal');
            $table->json('data')->nullable()->after('keperluan');
        });
    }

    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('siswa_id');
            $table->dropColumn(['jenis', 'keperluan', 'data']);
        });
    }
};
