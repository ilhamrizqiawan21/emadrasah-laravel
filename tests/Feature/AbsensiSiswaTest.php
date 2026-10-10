<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiSiswaTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelasA;

    private Kelas $kelasB;

    private Siswa $a1;

    private Siswa $a2;

    private Siswa $b1;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $guru = Guru::where('user_id', User::where('role', 'guru')->value('id'))->firstOrFail();
        $this->kelasA = Kelas::create(['nama_kelas' => 'Uji A', 'tingkat' => '7', 'kapasitas' => 30, 'guru_pembimbing_id' => $guru->id]);
        $this->kelasB = Kelas::create(['nama_kelas' => 'Uji B', 'tingkat' => '8', 'kapasitas' => 30]);

        $this->a1 = $this->siswa('7770001', 'Anak Satu', $this->kelasA);
        $this->a2 = $this->siswa('7770002', 'Anak Dua', $this->kelasA);
        $this->b1 = $this->siswa('7770003', 'Anak Lain', $this->kelasB);
    }

    private function siswa(string $nis, string $nama, Kelas $kelas, string $status = 'Aktif'): Siswa
    {
        return Siswa::create(['nis' => $nis, 'nama_lengkap' => $nama, 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => $status]);
    }

    private function as(string $role): static
    {
        return $this->actingAs(User::where('role', $role)->firstOrFail());
    }

    private function payload(Kelas $kelas, array $status, string $tanggal = '2026-10-07'): array
    {
        return ['kelas_id' => $kelas->id, 'tanggal' => $tanggal, 'status' => $status, 'keterangan' => []];
    }

    public function test_operator_records_attendance_and_saving_again_updates_instead_of_duplicating(): void
    {
        $this->as('operator');

        $this->post('/absensi-siswa', $this->payload($this->kelasA, [$this->a1->id => 'hadir', $this->a2->id => 'sakit']))
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->post('/absensi-siswa', $this->payload($this->kelasA, [$this->a1->id => 'izin', $this->a2->id => 'sakit']))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, AbsensiSiswa::count());
        $this->assertSame('izin', AbsensiSiswa::where('siswa_id', $this->a1->id)->value('status'));
        $this->assertSame($this->kelasA->id, AbsensiSiswa::where('siswa_id', $this->a1->id)->value('kelas_id'));
    }

    public function test_students_of_other_classes_and_inactive_students_are_ignored(): void
    {
        $lulus = $this->siswa('7770004', 'Sudah Lulus', $this->kelasA, 'Lulus');
        $this->as('operator');

        $this->post('/absensi-siswa', $this->payload($this->kelasA, [
            $this->a1->id => 'hadir', $this->b1->id => 'alpha', $lulus->id => 'alpha',
        ]))->assertSessionHasNoErrors();

        $this->assertSame([$this->a1->id], AbsensiSiswa::pluck('siswa_id')->all());
    }

    public function test_list_shows_only_active_students_of_the_chosen_class(): void
    {
        $this->siswa('7770005', 'Sudah Pindah', $this->kelasA, 'Pindah');
        $this->as('operator');

        $this->get('/absensi-siswa?kelas_id='.$this->kelasA->id)
            ->assertOk()->assertSee('Anak Satu')->assertSee('Anak Dua')
            ->assertDontSee('Anak Lain')->assertDontSee('Sudah Pindah');
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->as('operator');
        $ok = [$this->a1->id => 'hadir'];

        $this->post('/absensi-siswa', $this->payload($this->kelasA, $ok, now()->addDay()->toDateString()))->assertSessionHasErrors('tanggal');
        $this->post('/absensi-siswa', $this->payload($this->kelasA, [$this->a1->id => 'bolos']))->assertSessionHasErrors('status.'.$this->a1->id);
        $this->post('/absensi-siswa', ['tanggal' => '2026-10-07', 'status' => $ok])->assertSessionHasErrors('kelas_id');
        $this->assertSame(0, AbsensiSiswa::count());
    }

    public function test_guru_can_fill_own_homeroom_class_only(): void
    {
        $this->as('guru');

        $this->get('/absensi-siswa?kelas_id='.$this->kelasA->id)->assertOk()->assertSee('Anak Satu');
        $this->post('/absensi-siswa', $this->payload($this->kelasA, [$this->a1->id => 'hadir']))->assertSessionHasNoErrors();

        $this->get('/absensi-siswa?kelas_id='.$this->kelasB->id)->assertForbidden();
        $this->post('/absensi-siswa', $this->payload($this->kelasB, [$this->b1->id => 'hadir']))->assertForbidden();
        $this->assertSame(1, AbsensiSiswa::count());
    }

    public function test_guru_can_fill_a_class_they_teach(): void
    {
        $guru = Guru::where('user_id', User::where('role', 'guru')->value('id'))->firstOrFail();
        $jadwal = Jadwal::firstOrFail()->replicate();
        $jadwal->fill(['kelas_id' => $this->kelasB->id, 'guru_id' => $guru->id, 'hari' => 'Sabtu', 'jam_mulai' => '23:00:00', 'jam_selesai' => '23:30:00'])->save();
        $this->as('guru');

        $this->post('/absensi-siswa', $this->payload($this->kelasB, [$this->b1->id => 'hadir']))->assertSessionHasNoErrors();
        $this->assertSame(1, AbsensiSiswa::where('siswa_id', $this->b1->id)->count());
    }

    public function test_guru_without_linked_teacher_record_is_forbidden(): void
    {
        Guru::query()->update(['user_id' => null]);
        $this->as('guru')->get('/absensi-siswa')->assertForbidden();
    }

    public function test_monthly_recap_counts_each_status_per_student(): void
    {
        $this->as('operator');
        $this->post('/absensi-siswa', $this->payload($this->kelasA, [$this->a1->id => 'hadir', $this->a2->id => 'sakit'], '2026-10-05'));
        $this->post('/absensi-siswa', $this->payload($this->kelasA, [$this->a1->id => 'alpha', $this->a2->id => 'sakit'], '2026-10-06'));
        $this->post('/absensi-siswa', $this->payload($this->kelasA, [$this->a1->id => 'hadir'], '2026-09-30'));

        $response = $this->get('/absensi-siswa/rekap?kelas_id='.$this->kelasA->id.'&bulan=2026-10')->assertOk();

        $rekap = $response->viewData('rekap');
        $this->assertSame(['hadir' => 1, 'izin' => 0, 'sakit' => 0, 'alpha' => 1], collect($rekap[$this->a1->id])->only(['hadir', 'izin', 'sakit', 'alpha'])->all());
        $this->assertSame(2, $rekap[$this->a2->id]['sakit']);
    }
}
