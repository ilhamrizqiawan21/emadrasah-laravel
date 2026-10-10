<?php

namespace Tests\Feature;

use App\Models\KalenderAkademik;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Support\PerluTindakan;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KalenderTest extends TestCase
{
    use RefreshDatabase;

    private User $staf;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-12 08:00:00');   // Senin
        $this->seed(DatabaseSeeder::class);
        KalenderAkademik::query()->delete();
        $this->staf = User::where('role', 'operator')->firstOrFail();
    }

    private function akun(string $role): User
    {
        return User::create(['name' => $role, 'email' => "{$role}-kal@uji.test", 'password' => 'rahasia-panjang-1', 'role' => $role, 'is_active' => true]);
    }

    private function agenda(array $data = []): KalenderAkademik
    {
        return KalenderAkademik::create($data + ['judul' => 'Agenda Uji', 'jenis' => 'kegiatan', 'tanggal_mulai' => '2026-10-20', 'tanggal_selesai' => '2026-10-20']);
    }

    public function test_staff_can_create_update_and_delete_events(): void
    {
        $this->actingAs($this->staf);

        $this->post('/kalender', ['judul' => 'PTS Ganjil', 'jenis' => 'ujian', 'tanggal_mulai' => '2026-10-20', 'tanggal_selesai' => '2026-10-24'])
            ->assertRedirect('/kalender?bulan=2026-10')->assertSessionHas('success');
        $a = KalenderAkademik::firstOrFail();

        $this->put("/kalender/{$a->id}", ['judul' => 'PTS Diundur', 'jenis' => 'ujian', 'tanggal_mulai' => '2026-10-27', 'tanggal_selesai' => '2026-10-31'])->assertRedirect();
        $this->assertSame('PTS Diundur', $a->fresh()->judul);

        $this->delete("/kalender/{$a->id}")->assertRedirect();
        $this->assertModelMissing($a);
    }

    public function test_end_date_cannot_precede_start_and_type_must_be_known(): void
    {
        $this->actingAs($this->staf)->post('/kalender', ['judul' => 'x', 'jenis' => 'aneh', 'tanggal_mulai' => '2026-10-20', 'tanggal_selesai' => '2026-10-10'])
            ->assertSessionHasErrors(['jenis', 'tanggal_selesai']);
        $this->assertSame(0, KalenderAkademik::count());
    }

    public function test_every_role_can_view_but_only_staff_can_manage(): void
    {
        $a = $this->agenda(['judul' => 'Hari Santri']);

        foreach (['guru', 'wali_murid', 'siswa'] as $role) {
            $this->actingAs($this->akun($role));
            $this->get('/kalender?bulan=2026-10')->assertOk()->assertSee('Hari Santri');
            $this->get('/kalender/buat')->assertForbidden();
            $this->post('/kalender', ['judul' => 'x', 'jenis' => 'libur', 'tanggal_mulai' => '2026-10-20', 'tanggal_selesai' => '2026-10-20'])->assertForbidden();
            $this->delete("/kalender/{$a->id}")->assertForbidden();
        }
        $this->assertModelExists($a);
    }

    public function test_month_view_includes_events_that_overlap_the_month_only(): void
    {
        $this->agenda(['judul' => 'Lintas Bulan', 'tanggal_mulai' => '2026-09-28', 'tanggal_selesai' => '2026-10-02']);
        $this->agenda(['judul' => 'Bulan Depan', 'tanggal_mulai' => '2026-11-05', 'tanggal_selesai' => '2026-11-05']);

        $this->actingAs($this->staf)->get('/kalender?bulan=2026-10')->assertOk()->assertSee('Lintas Bulan')->assertDontSee('Bulan Depan');
        $this->get('/kalender?bulan=2026-11')->assertOk()->assertSee('Bulan Depan')->assertDontSee('Lintas Bulan');
    }

    public function test_month_grid_is_made_of_complete_monday_to_sunday_weeks(): void
    {
        foreach (['2026-10', '2026-02', '2026-08', '2027-02'] as $bulan) {
            $minggu = $this->actingAs($this->staf)->get("/kalender?bulan={$bulan}")->assertOk()->viewData('minggu');

            foreach ($minggu as $pekan) {
                $this->assertCount(7, $pekan, "pekan tidak lengkap pada {$bulan}");
                $this->assertTrue($pekan[0]['tanggal']->isMonday());
                $this->assertTrue($pekan[6]['tanggal']->isSunday());
            }
            $this->assertSame(1, $minggu[0][array_search(true, array_column($minggu[0], 'bulanIni'))]['tanggal']->day, "tanggal 1 harus ada di pekan pertama {$bulan}");
        }
    }

    public function test_invalid_month_is_rejected(): void
    {
        $this->actingAs($this->staf)->get('/kalender?bulan=bukan-bulan')->assertSessionHasErrors('bulan');
    }

    public function test_holiday_lookup_covers_ranges_and_ignores_other_types(): void
    {
        $this->agenda(['jenis' => 'libur', 'tanggal_mulai' => '2026-10-10', 'tanggal_selesai' => '2026-10-14']);
        $this->agenda(['jenis' => 'ujian', 'tanggal_mulai' => '2026-10-20', 'tanggal_selesai' => '2026-10-20']);

        $this->assertTrue(KalenderAkademik::liburPada(Carbon::parse('2026-10-12')));
        $this->assertTrue(KalenderAkademik::liburPada(Carbon::parse('2026-10-14')));
        $this->assertFalse(KalenderAkademik::liburPada(Carbon::parse('2026-10-15')));
        $this->assertFalse(KalenderAkademik::liburPada(Carbon::parse('2026-10-20')));
    }

    public function test_attendance_reminders_are_skipped_on_a_holiday(): void
    {
        $kelas = Kelas::create(['nama_kelas' => 'Libur 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        Siswa::create(['nis' => 'L1', 'nama_lengkap' => 'Siswa Libur', 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $item = fn () => collect(PerluTindakan::untuk(User::where('role', 'admin')->firstOrFail())['item'])->firstWhere('kunci', 'kelas-belum-absen');

        $this->assertGreaterThan(0, $item()['jumlah']);

        $this->agenda(['jenis' => 'libur', 'tanggal_mulai' => '2026-10-12', 'tanggal_selesai' => '2026-10-12']);
        $this->assertSame(0, $item()['jumlah']);
    }
}
