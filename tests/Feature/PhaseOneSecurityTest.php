<?php

namespace Tests\Feature;

use App\Models\ArsipAkademik;
use App\Models\AuditLog;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\KategoriSarana;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\PeminjamanSarana;
use App\Models\RaportNilai;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class PhaseOneSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_inactive_authenticated_user_is_logged_out(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_siswa_cannot_access_master_data_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/guru');

        $response->assertForbidden();
    }

    public function test_operator_permission_wildcard_allows_master_data_access(): void
    {
        $operator = User::factory()->create([
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->assertTrue($operator->hasPermission('guru.view'));
        $this->assertTrue($operator->hasPermission('guru.create'));
        $this->assertTrue($operator->can('guru.view'));

        $response = $this->actingAs($operator)->get('/guru');

        $response->assertOk();
    }

    public function test_guru_can_view_jadwal_but_cannot_create_jadwal(): void
    {
        $guru = User::factory()->create([
            'role' => 'guru',
            'is_active' => true,
        ]);

        $this->assertTrue($guru->hasPermission('jadwal.view'));
        $this->assertFalse($guru->hasPermission('jadwal.create'));
        $this->assertTrue($guru->can('jadwal.view'));
        $this->assertFalse($guru->can('jadwal.create'));

        $this->actingAs($guru)->get('/jadwal')->assertOk();
        $this->actingAs($guru)->post('/jadwal')->assertForbidden();
    }

    public function test_admin_cannot_deactivate_or_demote_own_account(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'phone' => '',
            'alamat' => '',
            'role' => 'operator',
            'is_active' => false,
        ]);

        $response->assertSessionHas('error');
        $admin->refresh();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue((bool) $admin->is_active);
    }

    public function test_jadwal_rejects_teacher_time_conflict(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $data = $this->jadwalFixture();

        Jadwal::create([
            'kelas_id' => $data['kelas']->id,
            'guru_id' => $data['guru']->id,
            'mapel_id' => $data['mapel']->id,
            'jam_pelajaran_id' => $data['jam']->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:00:00',
            'semester' => 1,
            'tahun_pelajaran_kode' => $data['tahun']->kode,
            'status' => 'aktif',
        ]);

        $kelasLain = Kelas::create([
            'nama_kelas' => '8A',
            'tingkat' => '8',
            'kapasitas' => 32,
            'fase' => 'D',
        ]);

        $response = $this->actingAs($admin)->post('/jadwal', [
            'kelas_id' => $kelasLain->id,
            'guru_id' => $data['guru']->id,
            'mapel_id' => $data['mapel']->id,
            'hari' => 'Senin',
            'sesi_id' => $data['jam']->id,
            'semester' => 1,
            'tahun_pelajaran_kode' => $data['tahun']->kode,
        ]);

        $response->assertSessionHasErrors('guru_id');
    }

    public function test_jadwal_rejects_class_time_conflict(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $data = $this->jadwalFixture();
        $guruLain = Guru::create([
            'kode' => 'GR002',
            'nama' => 'Guru Dua',
            'status' => 'aktif',
            'beban_jp' => 24,
        ]);

        Jadwal::create([
            'kelas_id' => $data['kelas']->id,
            'guru_id' => $data['guru']->id,
            'mapel_id' => $data['mapel']->id,
            'jam_pelajaran_id' => $data['jam']->id,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:00:00',
            'semester' => 1,
            'tahun_pelajaran_kode' => $data['tahun']->kode,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->post('/jadwal', [
            'kelas_id' => $data['kelas']->id,
            'guru_id' => $guruLain->id,
            'mapel_id' => $data['mapel']->id,
            'hari' => 'Senin',
            'sesi_id' => $data['jam']->id,
            'semester' => 1,
            'tahun_pelajaran_kode' => $data['tahun']->kode,
        ]);

        $response->assertSessionHasErrors('kelas_id');
    }

    public function test_sarana_peminjaman_and_pengembalian_update_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $kategori = KategoriSarana::create(['nama_kategori' => 'Elektronik']);
        $sarana = SaranaPrasarana::create([
            'kode_sarana' => 'LCD-001',
            'nama_sarana' => 'Proyektor',
            'kategori_id' => $kategori->id,
            'jumlah' => 2,
            'stok_tersedia' => 2,
            'kondisi' => 'baik',
        ]);

        $this->actingAs($admin)->post("/sarana/{$sarana->id}/peminjaman", [
            'peminjam' => 'Guru Satu',
            'tipe_peminjam' => 'guru',
            'tanggal_pinjam' => now()->toDateString(),
        ])->assertRedirect();

        $this->assertSame(1, $sarana->fresh()->stok_tersedia);

        $peminjaman = PeminjamanSarana::firstOrFail();

        $this->actingAs($admin)->put("/peminjaman/{$peminjaman->id}/kembali")
            ->assertRedirect();

        $this->assertSame(2, $sarana->fresh()->stok_tersedia);
        $this->assertSame('dikembalikan', $peminjaman->fresh()->status);
    }

    public function test_sarana_cannot_be_borrowed_without_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $kategori = KategoriSarana::create(['nama_kategori' => 'Elektronik']);
        $sarana = SaranaPrasarana::create([
            'kode_sarana' => 'LCD-002',
            'nama_sarana' => 'Proyektor Cadangan',
            'kategori_id' => $kategori->id,
            'jumlah' => 1,
            'stok_tersedia' => 0,
            'kondisi' => 'baik',
        ]);

        $response = $this->actingAs($admin)->post("/sarana/{$sarana->id}/peminjaman", [
            'peminjam' => 'Guru Satu',
            'tipe_peminjam' => 'guru',
            'tanggal_pinjam' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors('sarana_id');
        $this->assertSame(0, $sarana->fresh()->stok_tersedia);
    }

    public function test_backup_command_archives_database_and_uploaded_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('siswa-dokumen/sample.txt', 'dokumen siswa');

        config([
            'emadrasah.backup.path' => storage_path('framework/testing/backups'),
            'emadrasah.backup.upload_disk' => 'public',
            'emadrasah.backup.retention_days' => 0,
        ]);

        User::factory()->create([
            'name' => 'Admin Backup',
            'email' => 'backup@example.test',
        ]);

        $this->artisan('emadrasah:backup', ['--name' => 'test-backup'])
            ->expectsOutput('Backup berhasil dibuat.')
            ->assertSuccessful();

        $path = storage_path('framework/testing/backups/test-backup.zip');
        $this->assertFileExists($path);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));

        $this->assertNotFalse($zip->locateName('database/database.sql'));
        $this->assertNotFalse($zip->locateName('uploads/siswa-dokumen/sample.txt'));
        $this->assertNotFalse($zip->locateName('manifest.json'));
        $this->assertStringContainsString('backup@example.test', $zip->getFromName('database/database.sql'));
        $this->assertSame('dokumen siswa', $zip->getFromName('uploads/siswa-dokumen/sample.txt'));

        $zip->close();
    }

    public function test_important_data_uses_soft_deletes(): void
    {
        $data = $this->jadwalFixture();
        $kategori = KategoriSarana::create(['nama_kategori' => 'Arsip']);

        $siswa = Siswa::create([
            'nis' => 'SIS001',
            'nisn' => '1234567890',
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Siswa Soft Delete',
            'kelas_id' => $data['kelas']->id,
            'tahun_pelajaran_id' => $data['tahun']->id,
            'status' => 'Aktif',
        ]);

        $sarana = SaranaPrasarana::create([
            'kode_sarana' => 'ARS-001',
            'nama_sarana' => 'Lemari Arsip',
            'kategori_id' => $kategori->id,
            'jumlah' => 1,
            'stok_tersedia' => 1,
            'kondisi' => 'baik',
        ]);

        $suratMasuk = SuratMasuk::create([
            'nomor_agenda' => 'AG-001',
            'asal_surat' => 'Kemenag',
            'perihal' => 'Undangan',
            'tanggal_terima' => now()->toDateString(),
            'status' => 'diterima',
        ]);

        $suratKeluar = SuratKeluar::create([
            'nomor_surat' => 'SK-001',
            'tujuan' => 'Wali Murid',
            'perihal' => 'Pemberitahuan',
            'tanggal_kirim' => now()->toDateString(),
        ]);

        $arsip = ArsipAkademik::create([
            'tahun_pelajaran_id' => $data['tahun']->id,
            'kelas_id' => $data['kelas']->id,
            'semester' => 1,
            'nama_arsip' => 'Leger 7A',
            'file_path' => 'arsip/leger-7a.pdf',
            'tipe' => 'Leger',
        ]);

        $raportNilai = RaportNilai::create([
            'siswa_id' => $siswa->id,
            'tahun_pelajaran_id' => $data['tahun']->id,
            'semester' => 1,
            'mapel_id' => $data['mapel']->id,
            'nilai_akhir' => 90,
        ]);

        foreach ([$data['guru'], $siswa, $sarana, $suratMasuk, $suratKeluar, $arsip, $raportNilai] as $model) {
            $model->delete();
        }

        $this->assertSoftDeleted('gurus', ['id' => $data['guru']->id]);
        $this->assertSoftDeleted('siswa', ['id' => $siswa->id]);
        $this->assertSoftDeleted('sarana_prasarana', ['id' => $sarana->id]);
        $this->assertSoftDeleted('surat_masuk', ['id' => $suratMasuk->id]);
        $this->assertSoftDeleted('surat_keluar', ['id' => $suratKeluar->id]);
        $this->assertSoftDeleted('arsip_akademik', ['id' => $arsip->id]);
        $this->assertSoftDeleted('raport_nilai', ['id' => $raportNilai->id]);

        $this->assertNull(Guru::find($data['guru']->id));
        $this->assertNull(Siswa::find($siswa->id));
        $this->assertNull(SaranaPrasarana::find($sarana->id));
        $this->assertNull(SuratMasuk::find($suratMasuk->id));
        $this->assertNull(SuratKeluar::find($suratKeluar->id));
        $this->assertNull(ArsipAkademik::find($arsip->id));
        $this->assertNull(RaportNilai::find($raportNilai->id));
    }

    public function test_sensitive_data_changes_are_audited(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);

        $data = $this->jadwalFixture();

        $siswa = Siswa::create([
            'nis' => 'AUD001',
            'nama_lengkap' => 'Nama Awal',
            'kelas_id' => $data['kelas']->id,
            'tahun_pelajaran_id' => $data['tahun']->id,
            'status' => 'Aktif',
        ]);

        $createdLog = AuditLog::where('auditable_type', Siswa::class)
            ->where('auditable_id', $siswa->id)
            ->where('event', 'created')
            ->firstOrFail();

        $this->assertSame($admin->id, $createdLog->user_id);
        $this->assertSame('Nama Awal', $createdLog->new_values['nama_lengkap']);

        $siswa->update(['nama_lengkap' => 'Nama Baru']);

        $updatedLog = AuditLog::where('auditable_type', Siswa::class)
            ->where('auditable_id', $siswa->id)
            ->where('event', 'updated')
            ->latest()
            ->firstOrFail();

        $this->assertSame('Nama Awal', $updatedLog->old_values['nama_lengkap']);
        $this->assertSame('Nama Baru', $updatedLog->new_values['nama_lengkap']);

        $siswa->delete();

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Siswa::class,
            'auditable_id' => $siswa->id,
            'event' => 'deleted',
            'user_id' => $admin->id,
        ]);
    }

    public function test_user_management_changes_are_audited_without_password_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)->post('/users', [
            'name' => 'Operator Audit',
            'email' => 'operator-audit@example.test',
            'password' => 'secret-password',
            'phone' => '',
            'alamat' => '',
            'role' => 'operator',
            'is_active' => true,
        ])->assertRedirect();

        $user = User::where('email', 'operator-audit@example.test')->firstOrFail();
        $createdLog = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', 'created')
            ->firstOrFail();

        $this->assertSame($admin->id, $createdLog->user_id);
        $this->assertArrayNotHasKey('password', $createdLog->new_values);
        $this->assertSame('operator-audit@example.test', $createdLog->new_values['email']);

        $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Operator Audit Baru',
            'email' => 'operator-audit@example.test',
            'password' => 'new-secret-password',
            'phone' => '',
            'alamat' => '',
            'role' => 'operator',
            'is_active' => true,
        ])->assertRedirect();

        $updatedLog = AuditLog::where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', 'updated')
            ->latest()
            ->firstOrFail();

        $this->assertSame('Operator Audit', $updatedLog->old_values['name']);
        $this->assertSame('Operator Audit Baru', $updatedLog->new_values['name']);
        $this->assertTrue($updatedLog->new_values['password_changed']);
        $this->assertArrayNotHasKey('password', $updatedLog->new_values);
    }

    private function jadwalFixture(): array
    {
        $tahun = TahunPelajaran::create([
            'kode' => '2026/2027',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        $guru = Guru::create([
            'kode' => 'GR001',
            'nama' => 'Guru Satu',
            'status' => 'aktif',
            'beban_jp' => 24,
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'kapasitas' => 32,
            'fase' => 'D',
        ]);

        $mapel = Mapel::create([
            'nama_mapel' => 'Matematika',
            'jp_per_sesi' => 2,
        ]);

        $jam = JamPelajaran::create([
            'hari' => 'Senin',
            'sesi_ke' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '08:00:00',
        ]);

        return compact('tahun', 'guru', 'kelas', 'mapel', 'jam');
    }
}
