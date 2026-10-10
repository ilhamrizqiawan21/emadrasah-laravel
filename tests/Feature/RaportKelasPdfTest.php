<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class RaportKelasPdfTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private TahunPelajaran $tp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();
        $this->kelas = Kelas::create(['nama_kelas' => 'Cetak 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        foreach ([['4440001', 'Zahra Cetak', 'Aktif'], ['4440002', 'Andi Cetak', 'Aktif'], ['4440003', 'Budi Pindahan', 'Pindah']] as [$nis, $nama, $status]) {
            Siswa::create(['nis' => $nis, 'nama_lengkap' => $nama, 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => $status]);
        }
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    private function urlCetak(array $override = []): string
    {
        return '/raport/export-kelas?'.http_build_query(array_merge(['kelas_id' => $this->kelas->id, 'tahun_pelajaran_id' => $this->tp->id, 'semester' => 1], $override));
    }

    public function test_class_pdf_contains_every_active_student_sorted_by_name(): void
    {
        $dikirim = null;
        $pdf = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        Pdf::shouldReceive('loadView')->once()->andReturnUsing(function ($view, $data) use (&$dikirim, $pdf) {
            $dikirim = [$view, $data];

            return $pdf;
        });
        $pdf->shouldReceive('setPaper')->andReturnSelf();
        $pdf->shouldReceive('stream')->once()->andReturn(response('PDF', 200, ['Content-Type' => 'application/pdf']));

        $this->get($this->urlCetak())->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->assertSame('raport.pdf-kelas', $dikirim[0]);
        $this->assertSame(['Andi Cetak', 'Zahra Cetak'], collect($dikirim[1]['semuaRaport'])->pluck('siswa.nama_lengkap')->all());

        $html = view($dikirim[0], $dikirim[1])->render();
        $this->assertStringContainsString('Zahra Cetak', $html);
        $this->assertStringContainsString('Andi Cetak', $html);
        $this->assertStringNotContainsString('Budi Pindahan', $html);
        $this->assertSame(1, substr_count($html, 'page-break-after'));
    }

    public function test_real_pdf_is_generated(): void
    {
        $response = $this->get($this->urlCetak())->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_empty_class_is_refused_politely(): void
    {
        $kosong = Kelas::create(['nama_kelas' => 'Kosong', 'tingkat' => '9', 'kapasitas' => 30]);

        $this->get($this->urlCetak(['kelas_id' => $kosong->id]))->assertRedirect()->assertSessionHas('error');
    }

    public function test_validation(): void
    {
        $this->get($this->urlCetak(['semester' => 3]))->assertSessionHasErrors('semester');
        $this->get($this->urlCetak(['kelas_id' => 999999]))->assertSessionHasErrors('kelas_id');
    }

    public function test_guru_is_forbidden(): void
    {
        $this->actingAs(User::where('role', 'guru')->firstOrFail());
        $this->get($this->urlCetak())->assertForbidden();
    }
}
