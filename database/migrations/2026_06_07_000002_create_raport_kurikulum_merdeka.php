<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel Raport Kurikulum Merdeka — sesuai skema database madrasah_db (7).sql
     */
    public function up(): void
    {
        // 1. Raport Nilai Akademik
        Schema::create('raport_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester')->comment('1=Ganjil Kl7, 2=Genap Kl7, …, 6=Genap Kl9');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedTinyInteger('nilai_akhir')->nullable()->comment('0-100');
            $table->unsignedTinyInteger('kktp')->nullable()->comment('Kriteria Ketercapaian Tujuan Pembelajaran');
            $table->text('deskripsi')->nullable()->comment('Capaian pembelajaran deskriptif');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unsignedTinyInteger('nilai_ujian_madrasah')->nullable()->comment('Khusus semester 6');
            $table->text('deskripsi_ujian')->nullable()->comment('Khusus semester 6');
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['siswa_id', 'tahun_pelajaran_id', 'semester', 'mapel_id'], 'uq_raport_nilai');
        });

        // 2. Raport Ekstrakurikuler
        Schema::create('raport_ekskul', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester')->comment('1-6');
            $table->string('nama_ekskul', 150);
            $table->string('keterangan')->nullable();
            $table->string('nilai', 30)->nullable()->comment('misal: A, B, C atau Sangat Baik');
            $table->unsignedTinyInteger('urut')->default(99);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['siswa_id', 'tahun_pelajaran_id', 'semester'], 'idx_ekskul_siswa');
        });

        // 3. Raport Kehadiran
        Schema::create('raport_kehadiran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester')->comment('1-6');
            $table->unsignedSmallInteger('sakit')->default(0)->comment('Jumlah hari sakit');
            $table->unsignedSmallInteger('ijin')->default(0)->comment('Jumlah hari ijin');
            $table->unsignedSmallInteger('tanpa_keterangan')->default(0)->comment('Jumlah hari alpha/tanpa keterangan');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['siswa_id', 'tahun_pelajaran_id', 'semester'], 'uq_kehadiran');
            $table->index('siswa_id', 'idx_kehadiran_siswa');
        });

        // 4. Raport Kelulusan
        Schema::create('raport_kelulusan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->enum('status_kelulusan', ['LULUS', 'TIDAK LULUS'])->nullable();
            $table->date('tanggal_keputusan')->nullable();
            $table->string('no_ijazah', 50)->nullable();
            $table->string('no_skhus', 50)->nullable();
            $table->date('tgl_ijazah')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['siswa_id', 'tahun_pelajaran_id'], 'uq_kelulusan');
        });

        // 5. Raport P5/PPRA (Header)
        Schema::create('raport_p5ppra', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester')->comment('1-6');
            $table->string('tema_projek_1')->nullable();
            $table->string('tema_projek_2')->nullable();
            $table->string('tema_projek_3')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['siswa_id', 'tahun_pelajaran_id', 'semester'], 'uq_p5ppra');
        });

        // 6. Raport P5/PPRA Detail
        Schema::create('raport_p5ppra_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('p5ppra_id')->comment('FK ke raport_p5ppra.id');
            $table->unsignedTinyInteger('urut')->default(1);
            $table->string('dimensi', 150)->nullable();
            $table->string('elemen')->nullable();
            $table->string('sub_elemen')->nullable();
            $table->text('target_pencapaian')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('p5ppra_id', 'idx_p5_header');
            $table->foreign('p5ppra_id', 'fk_p5detail_header')
                ->references('id')->on('raport_p5ppra')->onDelete('cascade');
        });

        // 7. Raport Prestasi
        Schema::create('raport_prestasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester')->comment('1-6');
            $table->string('jenis_prestasi', 200)->nullable();
            $table->string('keterangan')->nullable();
            $table->string('nilai', 30)->nullable();
            $table->unsignedTinyInteger('urut')->default(99);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['siswa_id', 'tahun_pelajaran_id', 'semester'], 'idx_prestasi_siswa');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_prestasi');
        Schema::dropIfExists('raport_p5ppra_detail');
        Schema::dropIfExists('raport_p5ppra');
        Schema::dropIfExists('raport_kelulusan');
        Schema::dropIfExists('raport_kehadiran');
        Schema::dropIfExists('raport_ekskul');
        Schema::dropIfExists('raport_nilai');
    }
};
