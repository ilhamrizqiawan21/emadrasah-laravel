<?php

namespace Tests\Feature;

use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_buku_induk_pdf_export_returns_pdf_response(): void
    {
        $admin = $this->admin();
        $siswa = $this->siswaFixture();

        $response = $this->actingAs($admin)->get("/buku-induk/{$siswa->id}/export-pdf");

        $response->assertOk();
        $this->assertPdfResponse($response->baseResponse);
    }

    public function test_raport_pdf_export_returns_pdf_response(): void
    {
        $admin = $this->admin();
        $siswa = $this->siswaFixture();
        $mapel = Mapel::create([
            'nama_mapel' => 'Matematika',
            'jp_per_sesi' => 2,
            'urut' => 1,
        ]);

        RaportNilai::create([
            'siswa_id' => $siswa->id,
            'tahun_pelajaran_id' => $siswa->tahun_pelajaran_id,
            'semester' => 1,
            'mapel_id' => $mapel->id,
            'nilai_akhir' => 88,
            'deskripsi' => 'Tuntas',
        ]);

        $response = $this->actingAs($admin)->get("/raport/{$siswa->id}/export-pdf", [
            'tahun_pelajaran_id' => $siswa->tahun_pelajaran_id,
            'semester' => 1,
        ]);

        $response->assertOk();
        $this->assertPdfResponse($response->baseResponse);
    }

    public function test_absensi_rekap_pdf_export_returns_pdf_response(): void
    {
        $admin = $this->admin();
        $guru = Guru::create([
            'kode' => 'GR-PDF',
            'nama' => 'Guru PDF',
            'beban_jp' => 24,
        ]);

        AgendaGuru::create([
            'tanggal' => '2026-08-10',
            'guru_id' => $guru->id,
            'status' => 'hadir',
        ]);

        $response = $this->actingAs($admin)->get('/absensi/rekap/export', [
            'bulan' => 8,
            'tahun' => 2026,
        ]);

        $response->assertOk();
        $this->assertPdfResponse($response->baseResponse);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    private function siswaFixture(): Siswa
    {
        $tahun = TahunPelajaran::create([
            'kode' => '2026/2027',
            'nama' => 'Tahun Pelajaran 2026/2027',
            'is_aktif' => true,
        ]);
        $kelas = Kelas::create([
            'nama_kelas' => '7 PDF',
            'tingkat' => '7',
            'kapasitas' => 32,
            'fase' => 'D',
        ]);

        return Siswa::create([
            'nis' => 'SIS-PDF-001',
            'nisn' => '1234567890',
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Siswa PDF',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Bandung',
            'tanggal_lahir' => '2013-01-01',
            'kelas_id' => $kelas->id,
            'tahun_pelajaran_id' => $tahun->id,
            'status' => 'Aktif',
        ]);
    }

    private function assertPdfResponse($response): void
    {
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
