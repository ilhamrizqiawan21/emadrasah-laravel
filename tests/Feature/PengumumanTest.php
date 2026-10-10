<?php

namespace Tests\Feature;

use App\Models\Pengumuman;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengumumanTest extends TestCase
{
    use RefreshDatabase;

    private User $staf;

    private User $guru;

    private User $wali;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->staf = User::where('role', 'operator')->firstOrFail();
        $this->guru = $this->akun('guru', 'guru-info@uji.test');
        $this->wali = $this->akun('wali_murid', 'wali-info@uji.test');
    }

    private function akun(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'rahasia-panjang-1', 'role' => $role, 'is_active' => true]);
    }

    private function buat(array $data = []): Pengumuman
    {
        return Pengumuman::create($data + ['judul' => 'Libur Maulid', 'isi' => 'Madrasah libur.', 'target' => 'semua', 'terbit_pada' => today()]);
    }

    public function test_staff_publishes_and_only_targeted_roles_are_notified(): void
    {
        $this->actingAs($this->staf)->post('/pengumuman', ['judul' => 'Rapat Guru', 'isi' => 'Rapat Senin pagi.', 'target' => 'guru', 'terbit_pada' => today()->toDateString()])
            ->assertRedirect('/pengumuman')->assertSessionHas('success');

        $p = Pengumuman::firstOrFail();
        $this->assertSame($this->staf->id, $p->dibuat_oleh);
        $this->assertSame(1, $this->guru->notifications()->count());
        $this->assertSame(0, $this->wali->notifications()->count());
        $this->assertSame('Pengumuman: Rapat Guru', $this->guru->notifications()->first()->data['judul']);
    }

    public function test_scheduled_announcement_does_not_notify_yet(): void
    {
        $this->actingAs($this->staf)->post('/pengumuman', ['judul' => 'Nanti', 'isi' => 'Isi', 'target' => 'semua', 'terbit_pada' => today()->addDays(3)->toDateString()]);

        $this->assertSame(0, $this->guru->notifications()->count());
    }

    public function test_each_role_sees_only_active_announcements_meant_for_it(): void
    {
        $this->buat(['judul' => 'Untuk Semua']);
        $this->buat(['judul' => 'Khusus Guru', 'target' => 'guru']);
        $this->buat(['judul' => 'Khusus Wali', 'target' => 'wali']);
        $this->buat(['judul' => 'Sudah Berakhir', 'terbit_pada' => today()->subDays(10), 'berakhir_pada' => today()->subDay()]);
        $this->buat(['judul' => 'Belum Terbit', 'terbit_pada' => today()->addDay()]);

        $this->actingAs($this->wali)->get('/pengumuman')->assertOk()
            ->assertSee('Untuk Semua')->assertSee('Khusus Wali')
            ->assertDontSee('Khusus Guru')->assertDontSee('Sudah Berakhir')->assertDontSee('Belum Terbit');

        $this->actingAs($this->guru)->get('/pengumuman')->assertOk()
            ->assertSee('Untuk Semua')->assertSee('Khusus Guru')->assertDontSee('Khusus Wali');

        $this->actingAs($this->staf)->get('/pengumuman')->assertOk()
            ->assertSee('Khusus Guru')->assertSee('Khusus Wali')->assertSee('Sudah Berakhir')->assertSee('Belum Terbit');
    }

    public function test_only_staff_can_manage(): void
    {
        $p = $this->buat();

        foreach ([$this->guru, $this->wali] as $user) {
            $this->actingAs($user);
            $this->get('/pengumuman/buat')->assertForbidden();
            $this->post('/pengumuman', ['judul' => 'x', 'isi' => 'x', 'target' => 'semua', 'terbit_pada' => today()->toDateString()])->assertForbidden();
            $this->delete("/pengumuman/{$p->id}")->assertForbidden();
        }
        $this->assertModelExists($p);
    }

    public function test_validation_rejects_bad_dates_and_targets(): void
    {
        $this->actingAs($this->staf)->post('/pengumuman', ['judul' => 'x', 'isi' => 'x', 'target' => 'orang-asing', 'terbit_pada' => '2026-10-10', 'berakhir_pada' => '2026-10-01'])
            ->assertSessionHasErrors(['target', 'berakhir_pada']);
        $this->assertSame(0, Pengumuman::count());
    }

    public function test_update_and_delete(): void
    {
        $p = $this->buat();
        $this->actingAs($this->staf);

        $this->put("/pengumuman/{$p->id}", ['judul' => 'Judul Baru', 'isi' => 'Isi baru', 'target' => 'guru', 'terbit_pada' => today()->toDateString(), 'disematkan' => '1'])->assertRedirect('/pengumuman');
        $this->assertSame('Judul Baru', $p->fresh()->judul);
        $this->assertTrue($p->fresh()->disematkan);

        $this->delete("/pengumuman/{$p->id}")->assertRedirect('/pengumuman');
        $this->assertModelMissing($p);
    }

    public function test_dashboard_and_portal_show_active_announcements(): void
    {
        $this->buat(['judul' => 'Info Dashboard Guru', 'target' => 'guru']);
        $this->buat(['judul' => 'Info Portal Wali', 'target' => 'wali']);

        $this->actingAs($this->guru)->get('/dashboard')->assertOk()->assertSee('Info Dashboard Guru')->assertDontSee('Info Portal Wali');
        $this->actingAs($this->wali)->get('/wali')->assertOk()->assertSee('Info Portal Wali')->assertDontSee('Info Dashboard Guru');
    }
}
