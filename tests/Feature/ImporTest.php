<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class ImporTest extends TestCase
{
    use RefreshDatabase;

    private const HEADER_SISWA = ['NIS', 'NISN', 'NIK', 'Nama Lengkap', 'Kelas', 'L/P', 'Tempat Lahir', 'Tanggal Lahir', 'Agama', 'Alamat', 'Nama Orang Tua', 'No. HP', 'Status'];

    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->kelas = Kelas::create(['nama_kelas' => 'Impor 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    /** Buat berkas unggahan xlsx/csv dari baris (baris pertama = header). */
    protected function berkas(array $rows, string $ext = 'xlsx', string $delimiter = ','): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'impor').'.'.$ext;

        if ($ext === 'csv') {
            $fh = fopen($path, 'w');
            foreach ($rows as $row) {
                fputcsv($fh, $row, $delimiter);
            }
            fclose($fh);
        } else {
            $writer = new XlsxWriter;
            $writer->openToFile($path);
            foreach ($rows as $row) {
                $writer->addRow(new Row(array_map(fn ($v) => $v instanceof Cell ? $v : Cell::fromValue($v), $row)));
            }
            $writer->close();
        }

        return new UploadedFile($path, 'data.'.$ext, null, null, true);
    }

    protected function siswaRow(array $o = []): array
    {
        $d = array_merge([
            'NIS' => '1110001', 'NISN' => '0091234567', 'NIK' => '3201014603100001', 'Nama Lengkap' => 'Siti Impor', 'Kelas' => 'Impor 7A',
            'L/P' => 'P', 'Tempat Lahir' => 'Bogor', 'Tanggal Lahir' => '2012-03-06', 'Agama' => 'Islam', 'Alamat' => 'Jl. Melati 1',
            'Nama Orang Tua' => 'Budi', 'No. HP' => '08123456789', 'Status' => 'Aktif',
        ], $o);

        return array_map(fn ($h) => $d[$h], self::HEADER_SISWA);
    }

    protected function impor(string $jenis, UploadedFile $file, array $extra = [])
    {
        return $this->post("/impor/{$jenis}", ['berkas' => $file] + $extra);
    }

    public function test_imports_valid_students_from_xlsx(): void
    {
        $file = $this->berkas([self::HEADER_SISWA, $this->siswaRow(), $this->siswaRow(['NIS' => '1110002', 'NISN' => '', 'NIK' => '', 'Nama Lengkap' => 'Andi Impor', 'L/P' => 'Laki-laki', 'Kelas' => ' impor 7a ', 'Tanggal Lahir' => '06/03/2011', 'Status' => ''])]);

        $this->impor('siswa', $file)->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 2 && $h['dilewati'] === []);

        $siti = Siswa::where('nis', '1110001')->firstOrFail();
        $this->assertSame($this->kelas->id, $siti->kelas_id);
        $this->assertSame('P', $siti->jenis_kelamin);
        $this->assertSame('Aktif', $siti->status);
        $this->assertSame('2012-03-06', $siti->tanggal_lahir->toDateString());

        $andi = Siswa::where('nis', '1110002')->firstOrFail();
        $this->assertSame('L', $andi->jenis_kelamin);
        $this->assertSame('Aktif', $andi->status);
        $this->assertSame('2011-03-06', $andi->tanggal_lahir->toDateString());
        $this->assertNull($andi->nisn);
    }

    public function test_imports_csv_with_semicolon_delimiter(): void
    {
        $file = $this->berkas([self::HEADER_SISWA, $this->siswaRow()], 'csv', ';');

        $this->impor('siswa', $file)->assertRedirect()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 1);
        $this->assertSame('Siti Impor', Siswa::where('nis', '1110001')->value('nama_lengkap'));
    }

    public function test_numeric_cells_keep_leading_zero_of_nisn(): void
    {
        $file = $this->berkas([self::HEADER_SISWA, $this->siswaRow(['NISN' => 91234567, 'NIK' => 3201014603100001, 'NIS' => 1110001])]);

        $this->impor('siswa', $file)->assertRedirect()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 1);
        $siswa = Siswa::where('nis', '1110001')->firstOrFail();
        $this->assertSame('0091234567', $siswa->nisn);
        $this->assertSame('3201014603100001', $siswa->nik);
    }

    public function test_invalid_rows_are_skipped_with_row_numbers_and_valid_rows_still_import(): void
    {
        Siswa::create(['nis' => '1110003', 'nama_lengkap' => 'Sudah Ada', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $file = $this->berkas([
            self::HEADER_SISWA,
            $this->siswaRow(),                                                                  // baris 2: valid
            $this->siswaRow(['NIS' => '1110004', 'NISN' => '', 'NIK' => '', 'Kelas' => 'Kelas Hantu']), // 3: kelas tidak ada
            $this->siswaRow(['NIS' => '1110003', 'NISN' => '', 'NIK' => '']),                  // 4: NIS sudah ada
            $this->siswaRow(['NIS' => '1110001', 'NISN' => '', 'NIK' => '']),                  // 5: NIS kembar di berkas
            $this->siswaRow(['NIS' => '1110005', 'NISN' => '', 'NIK' => '123']),               // 6: NIK salah
            $this->siswaRow(['NIS' => '1110006', 'NISN' => '', 'NIK' => '', 'L/P' => 'X']),    // 7: L/P salah
            $this->siswaRow(['NIS' => '1110007', 'NISN' => '', 'NIK' => '', 'Nama Lengkap' => '']), // 8: nama kosong
            array_fill(0, 13, ''),                                                              // 9: kosong, diabaikan
        ]);

        $this->impor('siswa', $file)->assertRedirect()->assertSessionHas('hasil_impor', function ($h) {
            $baris = collect($h['dilewati'])->pluck('pesan', 'baris');

            return $h['diimpor'] === 1
                && $baris->keys()->all() === [3, 4, 5, 6, 7, 8]
                && str_contains($baris[3], 'Kelas Hantu')
                && str_contains($baris[4], 'sudah terdaftar')
                && str_contains($baris[5], 'dua kali');
        });
        $this->assertSame(1, Siswa::where('nis', '1110001')->count());
        $this->assertSame(0, Siswa::where('nis', '1110004')->count());
    }

    public function test_bad_dates_skip_the_row_and_real_excel_date_cells_work(): void
    {
        $file = $this->berkas([
            self::HEADER_SISWA,
            $this->siswaRow(['NIS' => '1110001', 'NISN' => '', 'NIK' => '', 'Tanggal Lahir' => 'kemarin sore']),
            $this->siswaRow(['NIS' => '1110002', 'NISN' => '', 'NIK' => '', 'Tanggal Lahir' => '31/02/2012']),
            $this->siswaRow(['NIS' => '1110003', 'NISN' => '', 'NIK' => '', 'Tanggal Lahir' => new \DateTimeImmutable('2012-03-06')]),       // nomor seri (kolom "Umum")
            $this->siswaRow(['NIS' => '1110004', 'NISN' => '', 'NIK' => '', 'Tanggal Lahir' => Cell::fromValue(new \DateTimeImmutable('2011-04-07'), (new Style)->withFormat('yyyy-mm-dd'))]), // sel berformat tanggal
        ]);

        $this->impor('siswa', $file)->assertRedirect()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 2
            && array_column($h['dilewati'], 'baris') === [2, 3]
            && str_contains($h['dilewati'][0]['pesan'], 'Tanggal Lahir tidak valid'));
        $this->assertSame('2012-03-06', Siswa::where('nis', '1110003')->firstOrFail()->tanggal_lahir->toDateString());
        $this->assertSame('2011-04-07', Siswa::where('nis', '1110004')->firstOrFail()->tanggal_lahir->toDateString());
    }

    public function test_deleted_students_still_reserve_their_nis(): void
    {
        $lama = Siswa::create(['nis' => '1110001', 'nama_lengkap' => 'Lama', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $lama->delete();

        $this->impor('siswa', $this->berkas([self::HEADER_SISWA, $this->siswaRow()]))
            ->assertRedirect()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 0 && count($h['dilewati']) === 1);
    }

    public function test_check_only_mode_saves_nothing(): void
    {
        $this->impor('siswa', $this->berkas([self::HEADER_SISWA, $this->siswaRow()]), ['periksa' => 1])
            ->assertRedirect()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 1 && $h['periksa'] === true);

        $this->assertSame(0, Siswa::where('nis', '1110001')->count());
    }

    public function test_missing_required_columns_aborts_everything(): void
    {
        $this->impor('siswa', $this->berkas([['NISN', 'Nama Lengkap'], ['0091234567', 'Tanpa Kelas']]))
            ->assertSessionHasErrors('berkas');

        $this->assertSame(0, Siswa::where('nama_lengkap', 'Tanpa Kelas')->count());
    }

    public function test_rejects_wrong_file_type_missing_file_and_oversized_sheets(): void
    {
        $this->impor('siswa', UploadedFile::fake()->createWithContent('x.txt', 'bukan spreadsheet'))->assertSessionHasErrors('berkas');
        $this->post('/impor/siswa', [])->assertSessionHasErrors('berkas');

        $banyak = array_fill(0, 1001, $this->siswaRow(['NIS' => '1', 'NISN' => '', 'NIK' => '']));
        $this->impor('siswa', $this->berkas(array_merge([self::HEADER_SISWA], $banyak)))->assertSessionHasErrors('berkas');
        $this->assertSame(0, Siswa::where('nama_lengkap', 'Siti Impor')->count());
    }

    public function test_a_corrupted_spreadsheet_gives_a_friendly_error(): void
    {
        $this->impor('siswa', UploadedFile::fake()->createWithContent('rusak.xlsx', 'ini bukan zip'))->assertSessionHasErrors('berkas');
    }

    public function test_imports_teachers(): void
    {
        Guru::create(['kode' => 'ZZ1', 'nama' => 'Sudah Ada']);
        $file = $this->berkas([
            ['Kode', 'Nama', 'NIP', 'Bidang Studi', 'Email', 'No. HP', 'Beban JP'],
            ['IM1', 'Guru Impor Satu', '1980', 'Fikih', 'satu@contoh.id', '0812', 24],
            ['ZZ1', 'Kode Kembar', '', '', '', '', ''],
            ['IM2', 'Email Rusak', '', '', 'bukan-email', '', ''],
            ['IM3', 'Guru Tiga', '', '', '', '', ''],
        ]);

        $this->impor('guru', $file)->assertRedirect()->assertSessionHas('hasil_impor', fn ($h) => $h['diimpor'] === 2 && array_column($h['dilewati'], 'baris') === [3, 4]);
        $this->assertSame('Fikih', Guru::where('kode', 'IM1')->value('bidang_studi'));
        $this->assertSame(24, Guru::where('kode', 'IM1')->value('beban_jp'));
        $this->assertSame(24, Guru::where('kode', 'IM3')->value('beban_jp'));
    }

    public function test_templates_download_with_the_expected_headers(): void
    {
        foreach (['siswa' => self::HEADER_SISWA, 'guru' => ['Kode', 'Nama', 'NIP', 'Bidang Studi', 'Email', 'No. HP', 'Beban JP']] as $jenis => $header) {
            $response = $this->get("/impor/{$jenis}/template")->assertOk();
            $this->assertStringContainsString("template-{$jenis}.xlsx", $response->headers->get('Content-Disposition'));

            $reader = new XlsxReader;
            $reader->open($response->baseResponse->getFile()->getPathname());
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $this->assertSame($header, array_slice($row->toArray(), 0, count($header)));
                    break 2;
                }
            }
            $reader->close();
        }
    }

    public function test_form_page_loads_and_template_round_trips_through_the_importer(): void
    {
        $this->get('/impor/siswa')->assertOk()->assertSee('Impor Data Siswa');
        $this->get('/impor/lainnya')->assertNotFound();

        $path = $this->get('/impor/siswa/template')->baseResponse->getFile()->getPathname();
        $copy = tempnam(sys_get_temp_dir(), 'tpl').'.xlsx';
        copy($path, $copy);
        // Template kosong: header diterima (bukan galat kolom), hanya ditolak karena belum berisi data.
        $this->impor('siswa', new UploadedFile($copy, 'template.xlsx', null, null, true))
            ->assertSessionHasErrors(['berkas' => 'Berkas tidak berisi baris data.']);
    }

    public function test_guru_role_cannot_import(): void
    {
        $this->actingAs(User::where('role', 'guru')->firstOrFail());
        $this->get('/impor/siswa')->assertForbidden();
        $this->impor('siswa', $this->berkas([self::HEADER_SISWA, $this->siswaRow()]))->assertForbidden();
        $this->assertSame(0, Siswa::where('nis', '1110001')->count());
    }
}
