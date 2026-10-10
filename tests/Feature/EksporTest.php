<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\OrangTuaWali;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class EksporTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Siswa $a;

    private Siswa $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->kelas = Kelas::create(['nama_kelas' => 'Ekspor 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->a = Siswa::create(['nis' => '2220001', 'nisn' => '0091234567', 'nama_lengkap' => '=1+1', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'P', 'status' => 'Aktif']);
        $this->b = Siswa::create(['nis' => '2220002', 'nama_lengkap' => 'Budi Ekspor', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Lulus']);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    /** Isi lembar pertama berkas unduhan sebagai array baris. */
    private function baca($response): array
    {
        $response->assertOk();
        $reader = new Reader;
        $reader->open($response->baseResponse->getFile()->getPathname());
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = $row->toArray();
            }
            break;
        }
        $reader->close();

        return $rows;
    }

    public function test_student_export_has_headers_filters_and_keeps_text_as_text(): void
    {
        $r = $this->get('/ekspor/siswa?kelas_id='.$this->kelas->id);
        $this->assertStringContainsString('siswa.xlsx', $r->headers->get('Content-Disposition'));
        $rows = $this->baca($r);

        $this->assertSame(['NIS', 'NISN', 'NIK', 'Nama Lengkap', 'Kelas', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Alamat', 'Nama Orang Tua', 'No. HP', 'Status'], $rows[0]);
        $this->assertCount(3, $rows);
        $this->assertSame('2220001', $rows[1][0]);
        $this->assertSame('0091234567', $rows[1][1]);   // nol di depan tidak hilang
        $this->assertSame('=1+1', $rows[1][3]);          // teks biasa, bukan rumus
        $this->assertSame('Ekspor 7A', $rows[1][4]);
        $this->assertSame('Lulus', $rows[2][12]);

        $aktif = $this->baca($this->get('/ekspor/siswa?kelas_id='.$this->kelas->id.'&status=Aktif'));
        $this->assertSame(['2220001'], array_column(array_slice($aktif, 1), 0));
    }

    public function test_deleted_students_are_not_exported(): void
    {
        $this->b->delete();
        $rows = $this->baca($this->get('/ekspor/siswa?kelas_id='.$this->kelas->id));

        $this->assertSame(['2220001'], array_column(array_slice($rows, 1), 0));
    }

    public function test_student_export_round_trips_into_the_importer_as_duplicates(): void
    {
        $path = $this->get('/ekspor/siswa?kelas_id='.$this->kelas->id)->baseResponse->getFile()->getPathname();
        $copy = tempnam(sys_get_temp_dir(), 'rt').'.xlsx';
        copy($path, $copy);

        $this->post('/impor/siswa', ['berkas' => new UploadedFile($copy, 'rt.xlsx', null, null, true)])
            ->assertRedirect()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 0 && count($h['dilewati']) === 2);
    }

    public function test_emis_export_has_full_columns_with_parents_and_keeps_identifiers_as_text(): void
    {
        $this->a->update(['nik' => '3201010101010001', 'no_kk' => '3201010101010002', 'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '2012-03-04', 'rt' => '01', 'rw' => '02', 'desa_kelurahan' => 'Sukamaju', 'kode_pos' => '16110']);
        $this->b->update(['status' => 'Aktif']);
        OrangTuaWali::create(['siswa_id' => $this->a->id, 'nama_ayah' => 'Ayah A', 'pekerjaan_ayah' => 'Petani', 'nama_ibu' => 'Ibu A', 'nama_wali' => 'Wali A']);

        $rows = $this->baca($this->get('/ekspor/emis?kelas_id='.$this->kelas->id));
        $kolom = array_flip($rows[0]);

        foreach (['NISN', 'NIK', 'No. KK', 'Nama Lengkap', 'Tanggal Lahir', 'RT', 'RW', 'Desa/Kelurahan', 'Kode Pos', 'Nama Ayah', 'Pekerjaan Ayah', 'Nama Ibu', 'Nama Wali'] as $nama) {
            $this->assertArrayHasKey($nama, $kolom);
        }
        $this->assertCount(3, $rows);
        $a = $rows[1];
        $this->assertSame('0091234567', $a[$kolom['NISN']]);
        $this->assertSame('3201010101010001', $a[$kolom['NIK']]);
        $this->assertSame('01', $a[$kolom['RT']]);
        $this->assertSame('=1+1', $a[$kolom['Nama Lengkap']]);
        $this->assertSame('2012-03-04', $a[$kolom['Tanggal Lahir']]);
        $this->assertSame('Ayah A', $a[$kolom['Nama Ayah']]);
        $this->assertSame('Petani', $a[$kolom['Pekerjaan Ayah']]);
        $this->assertEmpty($rows[2][$kolom['Nama Ayah']] ?? null);   // siswa tanpa data orang tua tetap diekspor
    }

    public function test_emis_export_defaults_to_active_students_and_skips_deleted(): void
    {
        $semua = $this->baca($this->get('/ekspor/emis?kelas_id='.$this->kelas->id));
        $this->assertCount(2, $semua);   // header + satu siswa Aktif (b berstatus Lulus)

        $lulus = $this->baca($this->get('/ekspor/emis?status=Lulus'));
        $this->assertCount(2, $lulus);

        $this->a->delete();
        $this->assertCount(1, $this->baca($this->get('/ekspor/emis?kelas_id='.$this->kelas->id)));
    }

    public function test_teacher_export(): void
    {
        $rows = $this->baca($this->get('/ekspor/guru'));

        $this->assertSame(['Kode', 'Nama', 'NIP', 'Bidang Studi', 'Email', 'No. HP', 'Beban JP'], $rows[0]);
        $this->assertCount(Guru::count() + 1, $rows);
    }

    public function test_attendance_recap_export_matches_the_screen_recap(): void
    {
        foreach ([['2026-10-05', 'hadir', 'sakit'], ['2026-10-06', 'alpha', 'sakit'], ['2026-10-07', 'hadir', 'izin']] as [$tgl, $sa, $sb]) {
            AbsensiSiswa::create(['tanggal' => $tgl, 'siswa_id' => $this->a->id, 'kelas_id' => $this->kelas->id, 'status' => $sa]);
            AbsensiSiswa::create(['tanggal' => $tgl, 'siswa_id' => $this->b->id, 'kelas_id' => $this->kelas->id, 'status' => $sb]);
        }
        $this->b->update(['status' => 'Aktif']);

        $rows = $this->baca($this->get('/ekspor/absensi-siswa?kelas_id='.$this->kelas->id.'&bulan=2026-10'));

        $this->assertSame(['No', 'NIS', 'Nama Siswa', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Kehadiran (%)'], $rows[0]);
        $this->assertSame([1, '2220001', '=1+1', 2, 0, 0, 1, 67], $rows[1]);
        $this->assertSame([2, '2220002', 'Budi Ekspor', 0, 1, 2, 0, 0], $rows[2]);

        $layar = $this->get('/absensi-siswa/rekap?kelas_id='.$this->kelas->id.'&bulan=2026-10')->viewData('rekap');
        $this->assertSame($layar[$this->a->id]['hadir'], $rows[1][3]);
        $this->assertSame($layar[$this->b->id]['sakit'], $rows[2][5]);
    }

    public function test_scores_export_is_a_student_by_subject_matrix_with_average(): void
    {
        $tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();
        $m1 = Mapel::create(['nama_mapel' => 'Fikih Ekspor']);
        $m2 = Mapel::create(['nama_mapel' => 'Akidah Ekspor']);
        foreach ([[$this->a, $m1, 80], [$this->a, $m2, 90], [$this->b, $m1, 70]] as [$s, $m, $n]) {
            RaportNilai::create(['siswa_id' => $s->id, 'tahun_pelajaran_id' => $tp->id, 'semester' => 1, 'mapel_id' => $m->id, 'nilai_akhir' => $n]);
        }
        $this->b->update(['status' => 'Aktif']);

        $rows = $this->baca($this->get('/ekspor/nilai?kelas_id='.$this->kelas->id.'&tahun_pelajaran_id='.$tp->id.'&semester=1'));

        $header = $rows[0];
        $this->assertSame(['No', 'NIS', 'Nama Siswa'], array_slice($header, 0, 3));
        $this->assertSame('Rata-rata', end($header));
        $iFikih = array_search('Fikih Ekspor', $header, true);
        $iAkidah = array_search('Akidah Ekspor', $header, true);
        $this->assertNotFalse($iFikih);
        $this->assertSame([80, 90, 85.0], [$rows[1][$iFikih], $rows[1][$iAkidah], (float) end($rows[1])]);
        $this->assertSame(70, $rows[2][$iFikih]);
        $this->assertNull($rows[2][$iAkidah] ?: null);
        $this->assertSame(70.0, (float) end($rows[2]));
    }

    public function test_validation_and_access(): void
    {
        $this->get('/ekspor/absensi-siswa?kelas_id=999999')->assertSessionHasErrors('kelas_id');
        $this->get('/ekspor/nilai?kelas_id='.$this->kelas->id.'&semester=3&tahun_pelajaran_id=1')->assertSessionHasErrors('semester');
        $this->get('/ekspor/lainnya')->assertNotFound();

        $this->actingAs(User::where('role', 'guru')->firstOrFail());
        $this->get('/ekspor/siswa')->assertForbidden();
    }
}
