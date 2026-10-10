<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SuratKeluar;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SuratSiswaTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Siswa $siswa;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-10 09:00:00');
        $this->seed(DatabaseSeeder::class);
        SuratKeluar::query()->delete();   // register bersih agar penomoran tidak bergantung data seeder
        $this->kelas = Kelas::create(['nama_kelas' => 'Surat 8A', 'tingkat' => '8', 'kapasitas' => 30]);
        $this->siswa = Siswa::create(['nis' => '5550001', 'nisn' => '0012345678', 'nama_lengkap' => 'Citra Surat', 'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '2011-05-06', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'P', 'status' => 'Aktif']);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    private function terbitkan(?Siswa $siswa = null, array $data = [])
    {
        return $this->post('/surat-siswa/'.($siswa ?? $this->siswa)->id, $data + ['keperluan' => 'Persyaratan beasiswa']);
    }

    public function test_issuing_registers_a_numbered_letter_with_a_snapshot_of_the_student(): void
    {
        $this->terbitkan()->assertRedirect()->assertSessionHas('success');

        $s = SuratKeluar::firstOrFail();
        $this->assertSame('001/SK/X/2026', $s->nomor_surat);
        $this->assertSame($this->siswa->id, $s->siswa_id);
        $this->assertSame('keterangan_aktif', $s->jenis);
        $this->assertSame('2026-10-10', $s->tanggal_kirim->toDateString());
        $this->assertSame('Persyaratan beasiswa', $s->keperluan);
        $this->assertSame('Citra Surat', $s->data['nama']);
        $this->assertSame('Surat 8A', $s->data['kelas']);
    }

    public function test_numbers_increase_and_continue_after_manually_registered_letters(): void
    {
        SuratKeluar::create(['nomor_surat' => '007/MA/IX/2026', 'tujuan' => 'X', 'perihal' => 'Manual', 'tanggal_kirim' => '2026-09-01']);
        SuratKeluar::create(['nomor_surat' => '099/MA/XII/2025', 'tujuan' => 'X', 'perihal' => 'Tahun lalu', 'tanggal_kirim' => '2025-12-01']);

        $this->terbitkan();
        $this->terbitkan();

        $this->assertSame(['008/SK/X/2026', '009/SK/X/2026'], SuratKeluar::whereNotNull('siswa_id')->orderBy('id')->pluck('nomor_surat')->all());
    }

    public function test_numbering_restarts_each_year(): void
    {
        SuratKeluar::create(['nomor_surat' => '050/MA/XII/2025', 'tujuan' => 'X', 'perihal' => 'Lama', 'tanggal_kirim' => '2025-12-01']);

        $this->terbitkan();

        $this->assertSame('001/SK/X/2026', SuratKeluar::whereNotNull('siswa_id')->value('nomor_surat'));
    }

    public function test_only_active_students_get_a_certificate(): void
    {
        $this->siswa->update(['status' => 'Lulus']);

        $this->terbitkan()->assertSessionHas('error');
        $this->assertSame(0, SuratKeluar::count());
    }

    public function test_purpose_is_required_and_limited(): void
    {
        $this->terbitkan(null, ['keperluan' => ''])->assertSessionHasErrors('keperluan');
        $this->terbitkan(null, ['keperluan' => str_repeat('a', 256)])->assertSessionHasErrors('keperluan');
        $this->assertSame(0, SuratKeluar::count());
    }

    public function test_page_lists_issued_letters(): void
    {
        $this->terbitkan();

        $this->get('/surat-siswa/'.$this->siswa->id)->assertOk()->assertSee('001/SK/X/2026')->assertSee('Persyaratan beasiswa');
    }

    public function test_print_returns_a_pdf_and_reprint_keeps_the_same_snapshot(): void
    {
        $this->terbitkan();
        $surat = SuratKeluar::firstOrFail();
        $this->siswa->update(['nama_lengkap' => 'Nama Diubah', 'kelas_id' => Kelas::create(['nama_kelas' => 'Pindah 9A', 'tingkat' => '9', 'kapasitas' => 30])->id]);

        $r = $this->get("/surat-siswa/cetak/{$surat->id}")->assertOk();
        $this->assertSame('application/pdf', $r->headers->get('Content-Type'));

        $html = view('surat-siswa.keterangan-aktif', ['surat' => $surat->fresh()])->render();
        $this->assertStringContainsString('001/SK/X/2026', $html);
        $this->assertStringContainsString('Citra Surat', $html);
        $this->assertStringContainsString('Surat 8A', $html);
        $this->assertStringNotContainsString('Nama Diubah', $html);
    }

    public function test_print_rejects_letters_that_are_not_student_certificates(): void
    {
        $biasa = SuratKeluar::create(['nomor_surat' => '001/MA/X/2026', 'tujuan' => 'X', 'perihal' => 'Biasa', 'tanggal_kirim' => '2026-10-01']);

        $this->get("/surat-siswa/cetak/{$biasa->id}")->assertNotFound();
    }
}
