<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\RaportCatatan;
use App\Models\RaportEkskul;
use App\Models\RaportKehadiran;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Support\DataRaport;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaportPelengkapTest extends TestCase
{
    use RefreshDatabase;

    private Siswa $siswa;

    private TahunPelajaran $tp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->tp = TahunPelajaran::updateOrCreate(['kode' => '2025/2026'], ['nama' => 'Tahun Ajaran 2025/2026', 'is_aktif' => true]);
        $this->siswa = Siswa::firstOrFail();
        // data raport contoh dari seeder tidak relevan di sini
        RaportKehadiran::query()->delete();
        RaportEkskul::query()->delete();
        RaportCatatan::query()->delete();
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'tahun_pelajaran_id' => $this->tp->id, 'semester' => 1,
            'ekskul' => [
                ['nama' => 'Pramuka', 'nilai' => 'A', 'keterangan' => 'Aktif'],
                ['nama' => '', 'nilai' => '', 'keterangan' => ''],
                ['nama' => 'Hadroh', 'nilai' => 'B', 'keterangan' => ''],
            ],
            'sakit' => 2, 'ijin' => 1, 'tanpa_keterangan' => 0,
            'catatan_wali' => 'Pertahankan prestasimu.',
        ], $override);
    }

    private function absen(string $tanggal, string $status): void
    {
        AbsensiSiswa::create(['tanggal' => $tanggal, 'siswa_id' => $this->siswa->id, 'kelas_id' => $this->siswa->kelas_id, 'status' => $status]);
    }

    public function test_saves_extracurriculars_attendance_and_homeroom_note(): void
    {
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload())->assertSessionHasNoErrors()->assertSessionHas('success');

        $ekskul = RaportEkskul::where('siswa_id', $this->siswa->id)->orderBy('urut')->get();
        $this->assertSame(['Pramuka', 'Hadroh'], $ekskul->pluck('nama_ekskul')->all());
        $this->assertSame('A', $ekskul->first()->nilai);

        $hadir = RaportKehadiran::where('siswa_id', $this->siswa->id)->firstOrFail();
        $this->assertSame([2, 1, 0], [$hadir->sakit, $hadir->ijin, $hadir->tanpa_keterangan]);
        $this->assertSame('Pertahankan prestasimu.', RaportCatatan::where('siswa_id', $this->siswa->id)->value('catatan_wali'));
    }

    public function test_saving_again_replaces_instead_of_duplicating(): void
    {
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload());
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload([
            'ekskul' => [['nama' => 'Futsal', 'nilai' => 'A', 'keterangan' => '']], 'sakit' => 5, 'catatan_wali' => 'Baru',
        ]));

        $this->assertSame(['Futsal'], RaportEkskul::where('siswa_id', $this->siswa->id)->pluck('nama_ekskul')->all());
        $this->assertSame(1, RaportKehadiran::where('siswa_id', $this->siswa->id)->count());
        $this->assertSame(5, RaportKehadiran::where('siswa_id', $this->siswa->id)->value('sakit'));
        $this->assertSame(1, RaportCatatan::where('siswa_id', $this->siswa->id)->count());
        $this->assertSame('Baru', RaportCatatan::where('siswa_id', $this->siswa->id)->value('catatan_wali'));
    }

    public function test_semesters_are_stored_independently(): void
    {
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload(['semester' => 1, 'catatan_wali' => 'Ganjil']));
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload(['semester' => 2, 'catatan_wali' => 'Genap']));

        $this->assertSame(2, RaportCatatan::where('siswa_id', $this->siswa->id)->count());
        $this->assertSame('Ganjil', RaportCatatan::where(['siswa_id' => $this->siswa->id, 'semester' => 1])->value('catatan_wali'));
    }

    public function test_validation(): void
    {
        $url = "/raport/{$this->siswa->id}/pelengkap";
        $this->post($url, $this->payload(['semester' => 3]))->assertSessionHasErrors('semester');
        $this->post($url, $this->payload(['sakit' => -1]))->assertSessionHasErrors('sakit');
        $this->post($url, $this->payload(['ijin' => 'banyak']))->assertSessionHasErrors('ijin');
        $this->post($url, $this->payload(['ekskul' => array_fill(0, 9, ['nama' => 'X', 'nilai' => '', 'keterangan' => ''])]))->assertSessionHasErrors('ekskul');
        $this->post($url, $this->payload(['catatan_wali' => str_repeat('a', 2001)]))->assertSessionHasErrors('catatan_wali');
        $this->post($url, $this->payload(['tahun_pelajaran_id' => 999999]))->assertSessionHasErrors('tahun_pelajaran_id');
        $this->assertSame(0, RaportCatatan::count());
    }

    public function test_attendance_defaults_to_the_semester_totals_from_daily_attendance(): void
    {
        $this->absen('2025-08-04', 'sakit');
        $this->absen('2025-08-05', 'sakit');
        $this->absen('2025-09-01', 'izin');
        $this->absen('2025-09-02', 'alpha');
        $this->absen('2025-09-03', 'hadir');
        $this->absen('2026-02-02', 'sakit'); // semester genap, tidak ikut semester 1

        $d1 = DataRaport::untuk($this->siswa, $this->tp->id, 1);
        $this->assertSame(['sakit' => 2, 'ijin' => 1, 'tanpa_keterangan' => 1], $d1['kehadiran']);
        $this->assertTrue($d1['kehadiranOtomatis']);

        $d2 = DataRaport::untuk($this->siswa, $this->tp->id, 2);
        $this->assertSame(['sakit' => 1, 'ijin' => 0, 'tanpa_keterangan' => 0], $d2['kehadiran']);
    }

    public function test_saved_attendance_wins_unless_recalculation_is_requested(): void
    {
        $this->absen('2025-08-04', 'sakit');
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload(['sakit' => 9, 'ijin' => 0, 'tanpa_keterangan' => 0]));

        $simpan = DataRaport::untuk($this->siswa, $this->tp->id, 1);
        $this->assertSame(9, $simpan['kehadiran']['sakit']);
        $this->assertFalse($simpan['kehadiranOtomatis']);

        $hitung = DataRaport::untuk($this->siswa, $this->tp->id, 1, hitungUlang: true);
        $this->assertSame(1, $hitung['kehadiran']['sakit']);

        $this->get("/raport/{$this->siswa->id}/manage?tahun_pelajaran_id={$this->tp->id}&semester=1&hitung=1")
            ->assertOk()->assertViewHas('kehadiran', fn ($k) => $k['sakit'] === 1);
    }

    public function test_pdf_view_contains_the_new_sections(): void
    {
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload());

        $html = view('raport.pdf', DataRaport::untuk($this->siswa, $this->tp->id, 1))->render();

        $this->assertStringContainsString('Pramuka', $html);
        $this->assertStringContainsString('Hadroh', $html);
        $this->assertStringContainsString('Pertahankan prestasimu.', $html);
        $this->assertStringContainsString('Sakit', $html);
        $this->get("/raport/{$this->siswa->id}/export-pdf?tahun_pelajaran_id={$this->tp->id}&semester=1")->assertOk();
    }

    public function test_guru_cannot_edit_report_extras(): void
    {
        $this->actingAs(User::where('role', 'guru')->firstOrFail());
        $this->post("/raport/{$this->siswa->id}/pelengkap", $this->payload())->assertForbidden();
        $this->assertSame(0, RaportCatatan::count());
    }
}
