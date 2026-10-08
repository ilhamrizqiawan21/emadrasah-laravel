<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Master Data & Akademik — sesuai skema database madrasah_db (7).sql
     */
    public function up(): void
    {
        // 1. Tahun Pelajaran
        Schema::create('tahun_pelajaran', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('kode', 9)->unique()->comment('Contoh: 2024/2025');
            $table->string('nama', 100);
            $table->boolean('is_aktif')->default(false);
            $table->timestamp('created_at')->useCurrent();
            // DB tidak punya updated_at
        });

        // 2. Users (Laravel default + custom fields)
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('phone', 20)->nullable();
                $table->text('alamat')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('password');
                $table->enum('role', ['admin', 'guru', 'wali_murid', 'siswa', 'operator'])->default('operator');
                $table->rememberToken();
                $table->timestamps();
                $table->index('role');
                $table->index('is_active');
            });
        }

        // 3. Gurus
        Schema::create('gurus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('nip', 50)->nullable()->unique();
            $table->enum('status', ['aktif', 'cuti', 'pensiun'])->default('aktif');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('bidang_studi')->nullable();
            $table->json('jam_tidak_tersedia')->nullable();
            $table->timestamps();
            $table->integer('beban_jp')->default(24)->comment('Kuota Jam Pelajaran per minggu');
            $table->index('user_id');
            $table->index('status');
        });

        // 4. Kelas
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');
            $table->string('tingkat');
            $table->unsignedBigInteger('guru_pembimbing_id')->nullable();
            $table->integer('kapasitas')->default(40);
            $table->string('ruangan', 100)->nullable();
            $table->timestamps();
            $table->string('fase', 5)->default('D')->comment('Fase Kurikulum Merdeka');
            $table->foreign('guru_pembimbing_id')->references('id')->on('gurus')->onDelete('set null');
            $table->index('guru_pembimbing_id');
        });

        // 5. Mapels
        Schema::create('mapels', function (Blueprint $table) {
            $table->id();
            $table->string('nama_mapel');
            $table->timestamps();
            $table->unsignedTinyInteger('jp_per_sesi')->default(2);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('urut')->nullable();
        });

        // 6. Jam Pelajaran
        Schema::create('jam_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('hari');
            $table->integer('sesi_ke');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();
        });

        // 7. Jadwals
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('jam_pelajaran_id');
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('ruang')->nullable();
            $table->unsignedTinyInteger('semester')->default(1);
            $table->string('tahun_pelajaran_kode', 9)->default('2025/2026');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
            $table->foreign('mapel_id')->references('id')->on('mapels')->onDelete('cascade');
            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
            $table->foreign('jam_pelajaran_id')->references('id')->on('jam_pelajaran')->onDelete('cascade');
        });

        // 8. Agenda Guru
        Schema::create('agenda_guru', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->unsignedBigInteger('guru_id');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['tanggal', 'guru_id']);
            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
        });

        // 9. Guru Pengganti
        Schema::create('guru_pengganti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agenda_guru_id');
            $table->unsignedBigInteger('jam_pelajaran_id');
            $table->unsignedBigInteger('guru_pengganti_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->foreign('agenda_guru_id')->references('id')->on('agenda_guru')->onDelete('cascade');
            $table->foreign('jam_pelajaran_id')->references('id')->on('jam_pelajaran')->onDelete('cascade');
            $table->foreign('guru_pengganti_id')->references('id')->on('gurus')->onDelete('set null');
        });

        // 10. Guru Kelas
        Schema::create('guru_kelas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('kelas_id');
            $table->timestamps();
            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });

        // 11. Beban Mengajar
        Schema::create('beban_mengajar', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('guru_id');
            $table->unsignedSmallInteger('tahun_pelajaran_id');
            $table->unsignedBigInteger('mapel_id');
            $table->unsignedBigInteger('kelas_id');
            $table->integer('jumlah_jam')->default(2);
            $table->timestamps();
            $table->foreign('guru_id')->references('id')->on('gurus')->onDelete('cascade');
            $table->foreign('tahun_pelajaran_id')->references('id')->on('tahun_pelajaran');
            $table->foreign('mapel_id')->references('id')->on('mapels')->onDelete('cascade');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });

        // 12. Siswa — Buku Induk Lengkap
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('no_urut')->nullable()->comment('No. urut di buku induk');
            $table->string('nis', 50);
            $table->string('nisn', 10)->nullable()->unique()->comment('Nomor Induk Siswa Nasional');
            $table->string('nism', 30)->nullable()->comment('Nomor Induk Siswa Madrasah');
            $table->string('nik', 16)->nullable()->unique();
            $table->string('no_kk', 16)->nullable();
            $table->string('nama_lengkap');
            $table->string('nama_panggilan', 50)->nullable();
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('tahun_pelajaran_id')->nullable();
            $table->enum('status', ['Aktif', 'Lulus', 'Pindah', 'Keluar', 'Meninggal'])->default('Aktif');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('tempat_lahir', 100)->nullable();
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
            $table->text('alamat')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('desa_kelurahan', 60)->nullable();
            $table->string('kecamatan', 60)->nullable();
            $table->string('kabupaten_kota', 60)->nullable();
            $table->string('provinsi', 50)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('nama_orang_tua')->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->string('hp', 20)->nullable();
            $table->string('bertempat_tinggal_pada', 60)->nullable()->comment('Ortu/Saudara/Wali/Asrama/Kos');
            $table->string('jarak_ke_madrasah', 10)->nullable()->comment('km');
            $table->string('moda_transportasi', 50)->nullable();
            $table->enum('golongan_darah', ['A', 'B', 'AB', 'O', 'Tidak Tahu'])->default('Tidak Tahu');
            $table->text('penyakit_pernah_diderita')->nullable();
            $table->string('kelainan_jasmani', 100)->nullable();
            $table->decimal('tinggi_badan_awal', 5, 2)->nullable();
            $table->decimal('berat_badan_awal', 5, 2)->nullable();
            $table->string('hobi_kesenian', 100)->nullable();
            $table->string('hobi_olahraga', 100)->nullable();
            $table->string('hobi_organisasi', 100)->nullable();
            $table->string('hobi_lain', 100)->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
            $table->unique('nis');
            $table->foreign('kelas_id')->references('id')->on('kelas')->onDelete('cascade');
        });

        // 13. Orang Tua / Wali
        Schema::create('orang_tua_wali', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('nama_ayah', 100)->nullable();
            $table->string('pendidikan_ayah', 30)->nullable();
            $table->string('pekerjaan_ayah', 60)->nullable();
            $table->string('penghasilan_ayah', 30)->nullable();
            $table->string('no_hp_ayah', 20)->nullable();
            $table->enum('status_ayah', ['Hidup', 'Meninggal', 'Tidak Diketahui'])->default('Hidup');
            $table->string('nama_ibu', 100)->nullable();
            $table->string('pendidikan_ibu', 30)->nullable();
            $table->string('pekerjaan_ibu', 60)->nullable();
            $table->string('penghasilan_ibu', 30)->nullable();
            $table->string('no_hp_ibu', 20)->nullable();
            $table->enum('status_ibu', ['Hidup', 'Meninggal', 'Tidak Diketahui'])->default('Hidup');
            $table->string('nama_wali', 100)->nullable();
            $table->string('hubungan_wali', 40)->nullable();
            $table->string('pendidikan_wali', 30)->nullable();
            $table->string('pekerjaan_wali', 60)->nullable();
            $table->string('no_hp_wali', 20)->nullable();
            $table->string('alamat_ortu', 200)->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->timestamp('created_at')->nullable();
            $table->unique('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade')->onUpdate('cascade');
        });

        // 14. Perkembangan Siswa
        Schema::create('perkembangan_siswa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->string('asal_madrasah', 100)->nullable();
            $table->string('nama_madrasah_asal', 100)->nullable();
            $table->date('tgl_ijazah_asal')->nullable();
            $table->string('no_ijazah_asal', 50)->nullable();
            $table->enum('jenis_masuk', ['Baru', 'Pindahan'])->default('Baru');
            $table->date('tgl_diterima')->nullable();
            $table->string('dari_tingkat', 20)->nullable()->comment('Jika pindahan');
            $table->string('no_surat_pindah', 50)->nullable()->comment('Jika pindahan');
            $table->enum('jenis_keluar', ['Lulus', 'Pindah', 'Keluar', 'Meninggal'])->nullable();
            $table->year('thn_lulus')->nullable();
            $table->string('no_ijazah_lulus', 50)->nullable();
            $table->string('melanjutkan_ke', 100)->nullable();
            $table->string('pindah_ke_madrasah', 100)->nullable();
            $table->string('pindah_tingkat', 20)->nullable();
            $table->string('alasan_keluar', 200)->nullable();
            $table->date('tgl_keluar')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perkembangan_siswa');
        Schema::dropIfExists('orang_tua_wali');
        Schema::dropIfExists('siswa');
        Schema::dropIfExists('beban_mengajar');
        Schema::dropIfExists('guru_kelas');
        Schema::dropIfExists('guru_pengganti');
        Schema::dropIfExists('agenda_guru');
        Schema::dropIfExists('jadwals');
        Schema::dropIfExists('jam_pelajaran');
        Schema::dropIfExists('mapels');
        Schema::dropIfExists('kelas');
        Schema::dropIfExists('gurus');
        Schema::dropIfExists('tahun_pelajaran');
    }
};
