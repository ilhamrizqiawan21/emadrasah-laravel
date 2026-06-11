<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Versi Lengkap sesuai Format Buku Induk Kurikulum Merdeka (MTs)
     */
    public function up(): void
    {
        // 1. Tabel Tahun Pelajaran
        Schema::create('tahun_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 9)->unique()->comment('Contoh: 2024/2025');
            $table->string('nama', 50);
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        // 2. Modifikasi/Re-create Tabel Siswa
        // Menggunakan dropIfExists agar fresh jika belum ada data penting
        Schema::dropIfExists('siswa');
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('no_urut')->nullable()->comment('No. Urut Buku Induk');
            $table->string('nis', 20)->nullable()->comment('Nomor Induk Siswa Lokal');
            $table->string('nisn', 10)->nullable()->unique()->comment('Nomor Induk Siswa Nasional');
            $table->string('nism', 30)->nullable()->comment('Nomor Induk Siswa Madrasah');
            $table->string('nik', 16)->nullable()->unique();
            $table->string('no_kk', 16)->nullable();
            
            // A. KETERANGAN TENTANG DIRI PESERTA DIDIK
            $table->string('nama_lengkap', 100);
            $table->string('nama_panggilan', 50)->nullable();
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 60)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 20)->nullable();
            $table->string('kewarganegaraan', 30)->default('Indonesia');
            $table->unsignedTinyInteger('anak_ke')->nullable();
            $table->unsignedTinyInteger('saudara_kandung')->default(0);
            $table->unsignedTinyInteger('saudara_tiri')->default(0);
            $table->unsignedTinyInteger('saudara_angkat')->default(0);
            $table->enum('status_anak', ['Kandung', 'Tiri', 'Angkat'])->default('Kandung');
            $table->enum('yatim_piatu', ['Tidak', 'Yatim', 'Piatu', 'Yatim Piatu'])->default('Tidak');
            $table->string('bahasa_sehari_hari', 50)->nullable();
            
            // B. KETERANGAN TEMPAT TINGGAL
            $table->string('alamat_jalan', 150)->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('desa_kelurahan', 60)->nullable();
            $table->string('kecamatan', 60)->nullable();
            $table->string('kabupaten_kota', 60)->nullable();
            $table->string('provinsi', 50)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('no_telp', 20)->nullable();
            $table->string('hp', 20)->nullable();
            $table->string('bertempat_tinggal_pada', 60)->nullable()->comment('Ortu/Saudara/Wali/Asrama/Kos');
            $table->string('jarak_ke_madrasah', 10)->nullable()->comment('km');
            $table->string('moda_transportasi', 50)->nullable();

            // C. KETERANGAN KESEHATAN
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O', 'Tidak Tahu'])->default('Tidak Tahu');
            $table->text('penyakit_pernah_diderita')->nullable()->comment('TBC, Cacar, Amandel, dll');
            $table->string('kelainan_jasmani', 100)->nullable();
            $table->decimal('tinggi_badan_awal', 5, 2)->nullable()->comment('cm');
            $table->decimal('berat_badan_awal', 5, 2)->nullable()->comment('kg');

            // G. KEGEMARAN PESERTA DIDIK
            $table->string('hobi_kesenian', 100)->nullable();
            $table->string('hobi_olahraga', 100)->nullable();
            $table->string('hobi_organisasi', 100)->nullable();
            $table->string('hobi_lain', 100)->nullable();

            $table->string('foto', 255)->nullable();
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->unsignedBigInteger('tahun_pelajaran_id')->nullable();
            $table->enum('status', ['Aktif', 'Lulus', 'Pindah', 'Keluar', 'Meninggal'])->default('Aktif');
            $table->timestamps();

            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran')->onDelete('set null');
        });

        // 3. Tabel Orang Tua / Wali
        Schema::create('orang_tua_wali', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            
            // AYAH
            $table->string('nama_ayah', 100)->nullable();
            $table->string('tempat_lahir_ayah', 60)->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('nik_ayah', 16)->nullable();
            $table->string('kewarganegaraan_ayah', 30)->default('Indonesia');
            $table->string('pendidikan_ayah', 50)->nullable();
            $table->string('pekerjaan_ayah', 60)->nullable();
            $table->string('penghasilan_ayah', 50)->nullable();
            $table->string('alamat_ayah', 255)->nullable();
            $table->string('no_telp_ayah', 20)->nullable();
            $table->enum('status_ayah', ['Hidup', 'Meninggal', 'Tidak Diketahui'])->default('Hidup');

            // IBU
            $table->string('nama_ibu', 100)->nullable();
            $table->string('tempat_lahir_ibu', 60)->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('nik_ibu', 16)->nullable();
            $table->string('kewarganegaraan_ibu', 30)->default('Indonesia');
            $table->string('pendidikan_ibu', 50)->nullable();
            $table->string('pekerjaan_ibu', 60)->nullable();
            $table->string('penghasilan_ibu', 50)->nullable();
            $table->string('alamat_ibu', 255)->nullable();
            $table->string('no_telp_ibu', 20)->nullable();
            $table->enum('status_ibu', ['Hidup', 'Meninggal', 'Tidak Diketahui'])->default('Hidup');

            // WALI (Opsional)
            $table->string('nama_wali', 100)->nullable();
            $table->string('tempat_lahir_wali', 60)->nullable();
            $table->date('tanggal_lahir_wali')->nullable();
            $table->string('nik_wali', 16)->nullable();
            $table->string('hubungan_wali', 50)->nullable();
            $table->string('pendidikan_wali', 50)->nullable();
            $table->string('pekerjaan_wali', 60)->nullable();
            $table->string('penghasilan_wali', 50)->nullable();
            $table->string('alamat_wali', 255)->nullable();
            $table->string('no_telp_wali', 20)->nullable();

            $table->timestamps();
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        // 4. Tabel Pendidikan Sebelumnya & Perkembangan
        Schema::create('perkembangan_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            
            // Pendidikan Sebelumnya (D)
            $table->string('asal_sekolah', 100)->nullable()->comment('SD/MI');
            $table->string('nama_sekolah_asal', 100)->nullable();
            $table->string('no_ijazah_asal', 50)->nullable();
            $table->date('tgl_ijazah_asal')->nullable();
            $table->string('lama_belajar_asal', 10)->nullable()->comment('Tahun');
            
            // Masuk ke Madrasah ini (E)
            $table->enum('jenis_masuk', ['Baru', 'Pindahan'])->default('Baru');
            $table->date('tgl_diterima')->nullable();
            $table->string('diterima_di_tingkat', 20)->nullable();
            $table->string('pindahan_dari_sekolah', 100)->nullable();
            $table->string('alasan_pindah', 255)->nullable();

            // Meninggalkan Madrasah ini (H)
            $table->enum('jenis_keluar', ['Lulus', 'Pindah', 'Putus Sekolah', 'Meninggal'])->nullable();
            $table->date('tgl_keluar')->nullable();
            $table->string('alasan_keluar', 255)->nullable();
            $table->string('no_ijazah_lulus', 50)->nullable();
            $table->string('no_skhun_lulus', 50)->nullable();
            $table->string('melanjutkan_ke', 150)->nullable();

            $table->timestamps();
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        // 5. Tabel Fisik, Prestasi, Beasiswa (Pendukung Buku Induk)
        Schema::create('bi_kesehatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedBigInteger('tahun_pelajaran_id');
            $table->unsignedTinyInteger('semester');
            $table->decimal('berat', 5, 2)->nullable();
            $table->decimal('tinggi', 5, 2)->nullable();
            $table->string('pendengaran', 50)->nullable();
            $table->string('penglihatan', 50)->nullable();
            $table->string('gigi', 50)->nullable();
            $table->string('kesehatan_lain', 255)->nullable();
            $table->timestamps();
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        Schema::create('bi_prestasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('jenis_prestasi', 100);
            $table->string('tingkat', 50)->nullable();
            $table->year('tahun')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });

        Schema::create('bi_beasiswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('jenis_beasiswa', 100);
            $table->string('sumber', 100)->nullable();
            $table->string('jangka_waktu', 50)->nullable();
            $table->timestamps();
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bi_beasiswa');
        Schema::dropIfExists('bi_prestasi');
        Schema::dropIfExists('bi_kesehatan');
        Schema::dropIfExists('perkembangan_siswa');
        Schema::dropIfExists('orang_tua_wali');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('tahun_pelajaran');
    }
};
