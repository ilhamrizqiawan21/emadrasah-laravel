<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add soft-delete columns to important records that should be recoverable.
     */
    public function up(): void
    {
        foreach ($this->tables() as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->tables()) as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'deleted_at')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }

    private function tables(): array
    {
        return [
            'siswa',
            'gurus',
            'surat_masuk',
            'surat_keluar',
            'sarana_prasarana',
            'arsip_akademik',
            'raport_nilai',
            'raport_ekskul',
            'raport_kehadiran',
            'raport_kelulusan',
            'raport_p5ppra',
            'raport_p5ppra_detail',
            'raport_prestasi',
        ];
    }
};
