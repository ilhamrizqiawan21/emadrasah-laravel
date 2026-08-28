<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('gurus', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_gurus_user')
                ->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('mapels', function (Blueprint $table) {
            $table->foreign('parent_id', 'fk_mapels_parent')
                ->references('id')->on('mapels')->nullOnDelete();
        });

        DB::statement('ALTER TABLE siswa MODIFY tahun_pelajaran_id SMALLINT UNSIGNED NULL');

        Schema::table('siswa', function (Blueprint $table) {
            $table->foreign('tahun_pelajaran_id', 'fk_siswa_tahun_pelajaran')
                ->references('id')->on('tahun_pelajaran')->nullOnDelete();
            $table->index('nama_lengkap', 'idx_siswa_nama_lengkap');
        });

        Schema::table('jadwals', function (Blueprint $table) {
            $table->unique(
                ['kelas_id', 'hari', 'jam_pelajaran_id', 'semester', 'tahun_pelajaran_kode'],
                'uq_jadwal_slot_kelas'
            );
            $table->index(['guru_id', 'hari', 'jam_mulai', 'jam_selesai'], 'idx_jadwal_guru_waktu');
        });

        Schema::table('raport_nilai', function (Blueprint $table) {
            $table->foreign('siswa_id', 'fk_raport_nilai_siswa')->references('id')->on('siswa')->cascadeOnDelete();
            $table->foreign('tahun_pelajaran_id', 'fk_raport_nilai_tahun')->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            $table->foreign('mapel_id', 'fk_raport_nilai_mapel')->references('id')->on('mapels')->cascadeOnDelete();
            $table->foreign('updated_by', 'fk_raport_nilai_updated_by')->references('id')->on('users')->nullOnDelete();
        });

        foreach (['raport_ekskul', 'raport_kehadiran', 'raport_kelulusan', 'raport_p5ppra', 'raport_prestasi'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->foreign('siswa_id', "fk_{$tableName}_siswa")->references('id')->on('siswa')->cascadeOnDelete();
                $table->foreign('tahun_pelajaran_id', "fk_{$tableName}_tahun")->references('id')->on('tahun_pelajaran')->cascadeOnDelete();
            });
        }

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->index(['nomor_agenda', 'status'], 'idx_surat_masuk_agenda_status');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->index('nomor_surat', 'idx_surat_keluar_nomor');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['status', 'prioritas', 'deadline'], 'idx_tasks_status_prioritas_deadline');
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('idx_tasks_status_prioritas_deadline');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropIndex('idx_surat_keluar_nomor');
        });

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropIndex('idx_surat_masuk_agenda_status');
        });

        foreach (['raport_prestasi', 'raport_p5ppra', 'raport_kelulusan', 'raport_kehadiran', 'raport_ekskul'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign("fk_{$tableName}_tahun");
                $table->dropForeign("fk_{$tableName}_siswa");
            });
        }

        Schema::table('raport_nilai', function (Blueprint $table) {
            $table->dropForeign('fk_raport_nilai_updated_by');
            $table->dropForeign('fk_raport_nilai_mapel');
            $table->dropForeign('fk_raport_nilai_tahun');
            $table->dropForeign('fk_raport_nilai_siswa');
        });

        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_guru_waktu');
            $table->dropUnique('uq_jadwal_slot_kelas');
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropIndex('idx_siswa_nama_lengkap');
            $table->dropForeign('fk_siswa_tahun_pelajaran');
        });

        DB::statement('ALTER TABLE siswa MODIFY tahun_pelajaran_id BIGINT UNSIGNED NULL');

        Schema::table('mapels', function (Blueprint $table) {
            $table->dropForeign('fk_mapels_parent');
        });

        Schema::table('gurus', function (Blueprint $table) {
            $table->dropForeign('fk_gurus_user');
        });
    }
};
