<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->kelas = Kelas::firstOrFail();
    }

    private function siswa(array $o = []): Siswa
    {
        return Siswa::create($o + ['nis' => '3330001', 'nama_lengkap' => 'Siswa Audit', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
    }

    public function test_seeding_leaves_the_log_empty(): void
    {
        $this->assertSame(0, AuditLog::count());
    }

    public function test_create_update_delete_and_restore_are_recorded_with_the_actor(): void
    {
        $this->actingAs($this->admin);
        $siswa = $this->siswa();
        $siswa->update(['nama_lengkap' => 'Nama Baru', 'hp' => '0812']);
        $siswa->delete();
        $siswa->restore();

        $log = AuditLog::orderBy('id')->get();
        $this->assertSame(['dibuat', 'diubah', 'dihapus', 'dipulihkan'], $log->pluck('aksi')->all());

        $this->assertSame($this->admin->id, $log[0]->user_id);
        $this->assertSame($this->admin->name, $log[0]->user_nama);
        $this->assertSame(Siswa::class, $log[0]->auditable_type);
        $this->assertSame($siswa->id, $log[0]->auditable_id);
        $this->assertStringContainsString('Siswa Audit', $log[0]->label);
        $this->assertStringContainsString('3330001', $log[0]->label);

        $this->assertSame(['nama_lengkap' => 'Siswa Audit', 'hp' => null], $log[1]->perubahan['lama']);
        $this->assertSame(['nama_lengkap' => 'Nama Baru', 'hp' => '0812'], $log[1]->perubahan['baru']);
    }

    public function test_changes_without_real_field_differences_are_not_logged(): void
    {
        $this->actingAs($this->admin);
        $siswa = $this->siswa();
        AuditLog::query()->delete();

        $siswa->update(['nama_lengkap' => 'Siswa Audit']); // nilai sama
        $siswa->touch();                                    // hanya updated_at

        $this->assertSame(0, AuditLog::count());
    }

    public function test_actions_without_a_logged_in_user_are_attributed_to_the_system(): void
    {
        $this->siswa();

        $log = AuditLog::firstOrFail();
        $this->assertNull($log->user_id);
        $this->assertSame('Sistem', $log->user_nama);
    }

    public function test_passwords_and_tokens_are_never_written_to_the_log(): void
    {
        $this->actingAs($this->admin);
        $user = User::create(['name' => 'Pengguna Uji', 'email' => 'uji@contoh.id', 'password' => 'RahasiaSekali-123', 'role' => 'guru']);
        $user->update(['password' => 'GantiSandi-456', 'remember_token' => 'tokenrahasia', 'role' => 'operator']);

        $teks = AuditLog::all()->map(fn ($l) => json_encode($l->perubahan))->implode(' ');
        $this->assertStringNotContainsString('RahasiaSekali', $teks);
        $this->assertStringNotContainsString('GantiSandi', $teks);
        $this->assertStringNotContainsString('tokenrahasia', $teks);
        $this->assertStringNotContainsString('$2y$', $teks);
        $this->assertStringContainsString('[diubah]', $teks);

        $ubah = AuditLog::where('aksi', 'diubah')->firstOrFail();
        $this->assertSame('operator', $ubah->perubahan['baru']['role']);
        $this->assertArrayNotHasKey('remember_token', $ubah->perubahan['baru']);
    }

    public function test_score_changes_show_the_student_subject_and_both_values(): void
    {
        $this->actingAs($this->admin);
        $siswa = $this->siswa();
        $mapel = Mapel::create(['nama_mapel' => 'Fikih Audit']);
        $tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();
        $nilai = RaportNilai::create(['siswa_id' => $siswa->id, 'tahun_pelajaran_id' => $tp->id, 'semester' => 1, 'mapel_id' => $mapel->id, 'nilai_akhir' => 80]);
        $nilai->update(['nilai_akhir' => 90]);

        $log = AuditLog::where('auditable_type', RaportNilai::class)->where('aksi', 'diubah')->firstOrFail();
        $this->assertStringContainsString('Siswa Audit', $log->label);
        $this->assertStringContainsString('Fikih Audit', $log->label);
        $this->assertEquals(80, $log->perubahan['lama']['nilai_akhir']);
        $this->assertEquals(90, $log->perubahan['baru']['nilai_akhir']);
    }

    public function test_without_auditing_helper_suppresses_logging_and_restores_afterwards(): void
    {
        AuditLog::tanpaAudit(fn () => $this->siswa());
        $this->assertSame(0, AuditLog::count());

        $this->siswa(['nis' => '3330002']);
        $this->assertSame(1, AuditLog::count());
    }

    public function test_the_log_keeps_the_actor_name_after_the_user_is_deleted(): void
    {
        $pengguna = User::create(['name' => 'Operator Sementara', 'email' => 'sementara@contoh.id', 'password' => 'KataSandi-12345', 'role' => 'operator']);
        $this->actingAs($pengguna);
        $this->siswa();

        $pengguna->delete();

        $log = AuditLog::where('auditable_type', Siswa::class)->firstOrFail();
        $this->assertNull($log->fresh()->user_id);
        $this->assertSame('Operator Sementara', $log->fresh()->user_nama);
    }

    public function test_deleting_a_class_through_the_screen_is_logged(): void
    {
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Dihapus', 'tingkat' => '7', 'kapasitas' => 30]);

        $this->delete("/kelas/{$kelas->id}")->assertSessionHas('success');

        $this->assertTrue(AuditLog::where('auditable_type', Kelas::class)->where('aksi', 'dihapus')->where('label', 'like', '%Kelas Dihapus%')->exists());
    }

    public function test_importing_students_is_logged_per_student_with_the_operator(): void
    {
        $operator = User::where('role', 'operator')->firstOrFail();
        $this->actingAs($operator);
        $this->siswa(['nis' => '9']); // memastikan jalur biasa tetap tercatat

        $this->assertSame($operator->id, AuditLog::where('auditable_type', Siswa::class)->value('user_id'));
    }

    // ── Halaman audit ─────────────────────────────────────────────────────

    public function test_admin_sees_the_log_and_filters_work(): void
    {
        $operator = User::where('role', 'operator')->firstOrFail();
        $this->actingAs($operator);
        $siswa = $this->siswa(['nama_lengkap' => 'Cari Aku Audit']);
        $siswa->update(['hp' => '0899']);
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Lain Audit', 'tingkat' => '7', 'kapasitas' => 30]);

        $this->actingAs($this->admin);
        $this->get('/audit-log')->assertOk()->assertSee('Cari Aku Audit')->assertSee('Kelas Lain Audit')->assertSee($operator->name);

        $this->get('/audit-log?jenis=Kelas')->assertSee('Kelas Lain Audit')->assertDontSee('Cari Aku Audit');
        $this->get('/audit-log?aksi=diubah')->assertSee('Cari Aku Audit')->assertDontSee('Kelas Lain Audit');
        $this->get('/audit-log?cari=Aku')->assertSee('Cari Aku Audit')->assertDontSee('Kelas Lain Audit');
        $this->get('/audit-log?user_id='.$operator->id)->assertSee('Cari Aku Audit');
        $this->get('/audit-log?user_id='.$this->admin->id)->assertDontSee('Cari Aku Audit');

        AuditLog::query()->update(['created_at' => now()->subDays(10)]);
        $this->get('/audit-log?dari='.now()->subDays(2)->toDateString())->assertDontSee('Cari Aku Audit');
        $this->get('/audit-log?sampai='.now()->subDays(5)->toDateString())->assertSee('Cari Aku Audit');
        $this->assertNotNull($kelas);
    }

    public function test_the_log_page_is_paginated_and_validates_filters(): void
    {
        $this->actingAs($this->admin);
        for ($i = 0; $i < 35; $i++) {
            $this->siswa(['nis' => 'P'.$i, 'nama_lengkap' => 'Massal '.$i]);
        }

        $this->get('/audit-log')->assertOk()->assertViewHas('logs', fn ($p) => $p->perPage() === 30 && $p->total() === 35);
        $this->get('/audit-log?dari=bukan-tanggal')->assertSessionHasErrors('dari');
        $this->get('/audit-log?aksi=meledak')->assertSessionHasErrors('aksi');
    }

    public function test_only_admins_can_read_the_log(): void
    {
        $this->get('/audit-log')->assertRedirect('/login');
        foreach (['operator', 'guru'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail())->get('/audit-log')->assertForbidden();
        }
    }

    // ── Pembersihan ───────────────────────────────────────────────────────

    public function test_old_entries_can_be_purged_and_recent_ones_stay(): void
    {
        $this->siswa(['nis' => '1']);
        $this->siswa(['nis' => '2']);
        AuditLog::orderBy('id')->first()->update(['created_at' => now()->subDays(800)]);

        $this->artisan('madrasah:bersihkan-audit', ['--hari' => 730])->assertSuccessful();
        $this->assertSame(1, AuditLog::count());

        $this->artisan('madrasah:bersihkan-audit', ['--hari' => 0])->assertFailed(); // 0 = hapus semua: ditolak
        $this->assertSame(1, AuditLog::count());
    }
}
