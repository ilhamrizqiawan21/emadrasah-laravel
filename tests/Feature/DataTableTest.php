<?php

namespace Tests\Feature;

use App\Models\ArsipAkademik;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_siswa_table_can_filter_by_status_kelas_tahun_and_sort(): void
    {
        $admin = $this->admin();
        [$kelasA, $kelasB, $tahunA, $tahunB] = $this->academicFixtures();

        Siswa::create([
            'nis' => '9002',
            'nama_lengkap' => 'Siswa Dicari',
            'jenis_kelamin' => 'L',
            'kelas_id' => $kelasA->id,
            'tahun_pelajaran_id' => $tahunA->id,
            'status' => 'Aktif',
        ]);
        Siswa::create([
            'nis' => '9001',
            'nama_lengkap' => 'Siswa Lain',
            'jenis_kelamin' => 'P',
            'kelas_id' => $kelasB->id,
            'tahun_pelajaran_id' => $tahunB->id,
            'status' => 'Lulus',
        ]);

        $response = $this->actingAs($admin)->get('/siswa?status=Aktif&kelas_id='.$kelasA->id.'&tahun_pelajaran_id='.$tahunA->id.'&sort=nis&direction=desc');

        $response->assertOk();
        $response->assertSee('Siswa Dicari');
        $response->assertDontSee('Siswa Lain');
    }

    public function test_surat_masuk_table_can_filter_by_status_and_sort(): void
    {
        $admin = $this->admin();

        SuratMasuk::create([
            'nomor_agenda' => 'SM-2026-001',
            'asal_surat' => 'Kemenag',
            'nomor_surat' => '001/KM',
            'perihal' => 'Undangan',
            'tanggal_terima' => '2026-08-10',
            'status' => 'diproses',
        ]);
        SuratMasuk::create([
            'nomor_agenda' => 'SM-2026-002',
            'asal_surat' => 'Komite',
            'nomor_surat' => '002/KT',
            'perihal' => 'Informasi',
            'tanggal_terima' => '2026-08-11',
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($admin)->get('/surat-masuk?status=diproses&sort=agenda&direction=asc');

        $response->assertOk();
        $response->assertSee('SM-2026-001');
        $response->assertDontSee('SM-2026-002');
    }

    public function test_bulk_delete_surat_masuk_and_surat_keluar(): void
    {
        $admin = $this->admin();
        $suratMasuk = SuratMasuk::create([
            'nomor_agenda' => 'SM-2026-003',
            'asal_surat' => 'Kemenag',
            'perihal' => 'Rapat',
            'tanggal_terima' => '2026-08-12',
            'status' => 'diterima',
        ]);
        $suratKeluar = SuratKeluar::create([
            'nomor_surat' => 'SK-2026-001',
            'tujuan' => 'Wali Murid',
            'perihal' => 'Pemberitahuan',
            'tanggal_kirim' => '2026-08-13',
        ]);

        $this->actingAs($admin)->delete(route('surat-masuk.bulk-destroy'), [
            'ids' => [$suratMasuk->id],
        ])->assertRedirect(route('surat-masuk.index'));

        $this->actingAs($admin)->delete(route('surat-keluar.bulk-destroy'), [
            'ids' => [$suratKeluar->id],
        ])->assertRedirect(route('surat-keluar.index'));

        $this->assertSoftDeleted('surat_masuk', ['id' => $suratMasuk->id]);
        $this->assertSoftDeleted('surat_keluar', ['id' => $suratKeluar->id]);
    }

    public function test_arsip_table_filters_and_bulk_delete(): void
    {
        $admin = $this->admin();
        [$kelasA, $kelasB, $tahunA, $tahunB] = $this->academicFixtures();

        $arsip = ArsipAkademik::create([
            'tahun_pelajaran_id' => $tahunA->id,
            'kelas_id' => $kelasA->id,
            'semester' => 1,
            'nama_arsip' => 'Leger Dicari',
            'file_path' => 'arsip-akademik/leger.pdf',
            'tipe' => 'Leger',
        ]);
        ArsipAkademik::create([
            'tahun_pelajaran_id' => $tahunB->id,
            'kelas_id' => $kelasB->id,
            'semester' => 2,
            'nama_arsip' => 'RDM Lain',
            'file_path' => 'arsip-akademik/rdm.pdf',
            'tipe' => 'RDM',
        ]);

        $response = $this->actingAs($admin)->get('/arsip-akademik?tipe=Leger&kelas_id='.$kelasA->id.'&tahun_pelajaran_id='.$tahunA->id.'&semester=1');

        $response->assertOk();
        $response->assertSee('Leger Dicari');
        $response->assertDontSee('RDM Lain');

        $this->actingAs($admin)->delete(route('arsip-akademik.bulk-destroy'), [
            'ids' => [$arsip->id],
        ])->assertRedirect(route('arsip-akademik.index'));

        $this->assertSoftDeleted('arsip_akademik', ['id' => $arsip->id]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    /**
     * @return array{0:Kelas,1:Kelas,2:TahunPelajaran,3:TahunPelajaran}
     */
    private function academicFixtures(): array
    {
        $kelasA = Kelas::create([
            'nama_kelas' => '7A',
            'tingkat' => '7',
            'kapasitas' => 32,
        ]);
        $kelasB = Kelas::create([
            'nama_kelas' => '8A',
            'tingkat' => '8',
            'kapasitas' => 32,
        ]);
        $tahunA = TahunPelajaran::create([
            'kode' => '2026/2027',
            'nama' => 'Tahun Pelajaran 2026/2027',
            'is_aktif' => true,
        ]);
        $tahunB = TahunPelajaran::create([
            'kode' => '2027/2028',
            'nama' => 'Tahun Pelajaran 2027/2028',
        ]);

        return [$kelasA, $kelasB, $tahunA, $tahunB];
    }
}
