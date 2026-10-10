<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NilaiKelasTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Kelas $kelasWali;

    private Mapel $mapel;

    private Mapel $mapelLain;

    private Siswa $s1;

    private Siswa $s2;

    private Siswa $asing;

    private TahunPelajaran $tp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $guru = Guru::where('user_id', User::where('role', 'guru')->value('id'))->firstOrFail();
        $this->tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();
        $this->kelas = Kelas::create(['nama_kelas' => 'Nilai A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->kelasWali = Kelas::create(['nama_kelas' => 'Nilai Wali', 'tingkat' => '8', 'kapasitas' => 30, 'guru_pembimbing_id' => $guru->id]);
        $this->mapel = Mapel::create(['nama_mapel' => 'Mapel Uji']);
        $this->mapelLain = Mapel::create(['nama_mapel' => 'Mapel Lain']);

        Jadwal::create([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $this->mapel->id, 'guru_id' => $guru->id,
            'jam_pelajaran_id' => JamPelajaran::firstOrFail()->id, 'hari' => 'Sabtu',
            'jam_mulai' => '23:00:00', 'jam_selesai' => '23:30:00', 'tahun_pelajaran_kode' => $this->tp->kode,
        ]);

        $this->s1 = $this->siswa('6660001', 'Murid Satu', $this->kelas);
        $this->s2 = $this->siswa('6660002', 'Murid Dua', $this->kelas);
        $this->asing = $this->siswa('6660003', 'Murid Asing', $this->kelasWali);
    }

    private function siswa(string $nis, string $nama, Kelas $kelas): Siswa
    {
        return Siswa::create(['nis' => $nis, 'nama_lengkap' => $nama, 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
    }

    private function as(string $role): static
    {
        return $this->actingAs(User::where('role', $role)->firstOrFail());
    }

    private function payload(array $nilai, array $override = []): array
    {
        return array_merge([
            'kelas_id' => $this->kelas->id, 'mapel_id' => $this->mapel->id,
            'tahun_pelajaran_id' => $this->tp->id, 'semester' => 1, 'nilai' => $nilai,
        ], $override);
    }

    public function test_operator_saves_scores_for_a_whole_class_and_resaving_updates(): void
    {
        $this->as('operator');

        $this->post('/nilai', $this->payload([
            $this->s1->id => ['angka' => 85, 'capaian' => 'Baik'],
            $this->s2->id => ['angka' => 70, 'capaian' => ''],
        ]))->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 90, 'capaian' => 'Sangat baik']]))->assertSessionHasNoErrors();

        $rows = RaportNilai::where('mapel_id', $this->mapel->id)->get();
        $this->assertCount(2, $rows);
        $baris = $rows->firstWhere('siswa_id', $this->s1->id);
        $this->assertSame(90, $baris->nilai_akhir);
        $this->assertSame('Sangat baik', $baris->deskripsi);
        $this->assertSame(auth()->id(), $baris->updated_by);
    }

    public function test_blank_scores_are_skipped_and_do_not_erase_existing_ones(): void
    {
        $this->as('operator');
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 80]]));
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => ''], $this->s2->id => ['angka' => '']]));

        $this->assertSame(80, RaportNilai::where('siswa_id', $this->s1->id)->value('nilai_akhir'));
        $this->assertSame(0, RaportNilai::where('siswa_id', $this->s2->id)->count());
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->as('operator');

        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 101]]))->assertSessionHasErrors('nilai.'.$this->s1->id.'.angka');
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 80]], ['semester' => 3]))->assertSessionHasErrors('semester');
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 80]], ['mapel_id' => 999999]))->assertSessionHasErrors('mapel_id');
        $this->assertSame(0, RaportNilai::where('mapel_id', $this->mapel->id)->count());
    }

    public function test_students_outside_the_class_are_ignored(): void
    {
        $this->as('operator');
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 80], $this->asing->id => ['angka' => 99]]))->assertSessionHasNoErrors();

        $this->assertSame([$this->s1->id], RaportNilai::where('mapel_id', $this->mapel->id)->pluck('siswa_id')->all());
    }

    public function test_index_shows_students_and_saved_scores(): void
    {
        RaportNilai::create(['siswa_id' => $this->s1->id, 'tahun_pelajaran_id' => $this->tp->id, 'semester' => 1, 'mapel_id' => $this->mapel->id, 'nilai_akhir' => 77]);
        $this->as('operator');

        $this->get('/nilai?kelas_id='.$this->kelas->id.'&mapel_id='.$this->mapel->id)
            ->assertOk()->assertSee('Murid Satu')->assertSee('Murid Dua')->assertDontSee('Murid Asing')
            ->assertSee('value="77"', false);
    }

    public function test_index_does_not_crash_without_an_active_academic_year(): void
    {
        TahunPelajaran::query()->update(['is_aktif' => false]);
        $this->as('operator')->get('/nilai')->assertOk()->assertSee('tahun pelajaran');
    }

    public function test_guru_can_grade_only_the_class_and_subject_they_teach(): void
    {
        $this->as('guru');

        $this->get('/nilai?kelas_id='.$this->kelas->id.'&mapel_id='.$this->mapel->id)->assertOk()->assertSee('Murid Satu');
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 88]]))->assertSessionHasNoErrors();
        $this->assertSame(1, RaportNilai::where('mapel_id', $this->mapel->id)->count());

        // mapel yang tidak dia ajarkan di kelas itu
        $this->post('/nilai', $this->payload([$this->s1->id => ['angka' => 88]], ['mapel_id' => $this->mapelLain->id]))->assertForbidden();
        $this->get('/nilai?kelas_id='.$this->kelas->id.'&mapel_id='.$this->mapelLain->id)->assertForbidden();
        // kelas yang hanya dia walikan, tanpa jadwal mengajar
        $this->post('/nilai', $this->payload([$this->asing->id => ['angka' => 88]], ['kelas_id' => $this->kelasWali->id]))->assertForbidden();
        $this->assertSame(0, RaportNilai::where('siswa_id', $this->asing->id)->count());
        $this->assertSame(0, RaportNilai::where('mapel_id', $this->mapelLain->id)->count());
    }
}
