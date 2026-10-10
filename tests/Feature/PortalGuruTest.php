<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalGuruTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelasAjar;

    private Kelas $kelasWali;

    private Kelas $kelasLain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->travelTo('2026-10-12 08:00:00'); // Senin
        Jadwal::query()->delete(); // jadwal contoh dari seeder tidak relevan di sini

        $guru = Guru::where('user_id', User::where('role', 'guru')->value('id'))->firstOrFail();
        $rekan = Guru::where('id', '!=', $guru->id)->firstOrFail();
        $kode = TahunPelajaran::where('is_aktif', true)->value('kode');
        $this->kelasAjar = Kelas::create(['nama_kelas' => 'Portal Ajar', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->kelasWali = Kelas::create(['nama_kelas' => 'Portal Wali', 'tingkat' => '8', 'kapasitas' => 30, 'guru_pembimbing_id' => $guru->id]);
        $this->kelasLain = Kelas::create(['nama_kelas' => 'Portal Lain', 'tingkat' => '9', 'kapasitas' => 30]);

        $jadwal = fn (Guru $g, Kelas $k, string $mapel, string $hari) => Jadwal::create([
            'kelas_id' => $k->id, 'mapel_id' => Mapel::create(['nama_mapel' => $mapel])->id, 'guru_id' => $g->id,
            'jam_pelajaran_id' => JamPelajaran::firstOrFail()->id, 'hari' => $hari,
            'jam_mulai' => '07:00:00', 'jam_selesai' => '08:00:00', 'tahun_pelajaran_kode' => $kode,
        ]);
        $jadwal($guru, $this->kelasAjar, 'Fikih Senin', 'Senin');
        $jadwal($guru, $this->kelasAjar, 'Akidah Selasa', 'Selasa');
        $jadwal($rekan, $this->kelasLain, 'Mapel Rekan', 'Senin');

        foreach ([['6650001', 'Anak Ajar', $this->kelasAjar], ['6650002', 'Anak Wali', $this->kelasWali], ['6650003', 'Anak Lain', $this->kelasLain]] as [$nis, $nama, $k]) {
            Siswa::create(['nis' => $nis, 'nama_lengkap' => $nama, 'kelas_id' => $k->id, 'jenis_kelamin' => 'P', 'status' => 'Aktif']);
        }
    }

    private function as(string $role): static
    {
        return $this->actingAs(User::where('role', $role)->firstOrFail());
    }

    public function test_guru_sees_own_schedule_for_today_and_the_week_but_not_colleagues(): void
    {
        $r = $this->as('guru')->get('/portal')->assertOk();

        $r->assertSee('Fikih Senin')->assertSee('Akidah Selasa')->assertDontSee('Mapel Rekan');
        $this->assertSame(['Fikih Senin'], $r->viewData('jadwalHariIni')->pluck('mapel.nama_mapel')->all());
    }

    public function test_guru_sees_homeroom_and_teaching_classes_only(): void
    {
        $this->as('guru')->get('/portal')->assertSee('Portal Ajar')->assertSee('Portal Wali')->assertDontSee('Portal Lain');
    }

    public function test_guru_can_open_student_list_of_own_classes_only(): void
    {
        $this->as('guru');

        $this->get("/portal/kelas/{$this->kelasAjar->id}")->assertOk()->assertSee('Anak Ajar')->assertDontSee('Anak Lain');
        $this->get("/portal/kelas/{$this->kelasWali->id}")->assertOk()->assertSee('Anak Wali');
        $this->get("/portal/kelas/{$this->kelasLain->id}")->assertForbidden();
    }

    public function test_admin_is_sent_to_dashboard_but_can_open_any_class(): void
    {
        $this->as('admin')->get('/portal')->assertRedirect(route('dashboard'));
        $this->get("/portal/kelas/{$this->kelasLain->id}")->assertOk()->assertSee('Anak Lain');
    }
}
