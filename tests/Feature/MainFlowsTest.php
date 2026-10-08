<?php

namespace Tests\Feature;

use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\SuratMasuk;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Alur bisnis utama dari ujung ke ujung: masuk/keluar, data siswa, surat masuk (dengan berkas),
 * raport (termasuk PDF), dan absensi guru.
 */
class MainFlowsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    private function as(string $role): static
    {
        return $this->actingAs(User::where('role', $role)->firstOrFail());
    }

    // ── Masuk / keluar ────────────────────────────────────────────────────

    public function test_user_can_log_in_and_out(): void
    {
        $this->post('/login', ['email' => 'operator@madrasah.id', 'password' => 'operator123'])->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected_without_revealing_which_part_was_wrong(): void
    {
        $this->from('/login')->post('/login', ['email' => 'operator@madrasah.id', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->post('/login', ['email' => 'tidak-ada@madrasah.id', 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
        $this->assertGuest();
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        User::where('email', 'guru@madrasah.id')->update(['is_active' => false]);

        $this->post('/login', ['email' => 'guru@madrasah.id', 'password' => 'guru123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logged_in_user_is_sent_to_the_page_they_wanted(): void
    {
        $this->get('/siswa')->assertRedirect('/login');

        $this->post('/login', ['email' => 'operator@madrasah.id', 'password' => 'operator123'])->assertRedirect('/siswa');
    }

    // ── Siswa ─────────────────────────────────────────────────────────────

    private function siswaPayload(array $override = []): array
    {
        return array_merge([
            'nis' => '9990001',
            'nama_lengkap' => 'Budi Uji Coba',
            'kelas_id' => Kelas::firstOrFail()->id,
            'jenis_kelamin' => 'L',
            'status' => 'Aktif',
        ], $override);
    }

    public function test_siswa_can_be_created_listed_searched_updated_and_deleted(): void
    {
        $this->as('operator')->post('/siswa', $this->siswaPayload())
            ->assertRedirect(route('siswa.index'))->assertSessionHas('success');
        $siswa = Siswa::where('nis', '9990001')->firstOrFail();

        $this->get('/siswa')->assertSee('Budi Uji Coba');
        $this->get('/siswa?search=9990001')->assertSee('Budi Uji Coba')->assertDontSee('Aisyah');
        $this->get('/siswa?search=tidak-ada-siapapun')->assertSee('Data siswa tidak ditemukan');

        $this->put("/siswa/{$siswa->id}", $this->siswaPayload(['nama_lengkap' => 'Budi Diubah']))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('siswa', ['id' => $siswa->id, 'nama_lengkap' => 'Budi Diubah', 'nis' => '9990001']);

        $this->delete("/siswa/{$siswa->id}")->assertRedirect(route('siswa.index'));
        $this->assertDatabaseMissing('siswa', ['id' => $siswa->id]);
    }

    public function test_siswa_validation_rules(): void
    {
        $this->as('operator');

        $this->post('/siswa', [])->assertSessionHasErrors(['nis', 'nama_lengkap', 'kelas_id', 'jenis_kelamin', 'status']);
        $this->post('/siswa', $this->siswaPayload(['jenis_kelamin' => 'X']))->assertSessionHasErrors('jenis_kelamin');
        $this->post('/siswa', $this->siswaPayload(['kelas_id' => 99999]))->assertSessionHasErrors('kelas_id');
        $this->post('/siswa', $this->siswaPayload(['nik' => '123']))->assertSessionHasErrors('nik');

        $this->post('/siswa', $this->siswaPayload())->assertSessionHasNoErrors();
        $this->post('/siswa', $this->siswaPayload(['nama_lengkap' => 'Orang Lain']))->assertSessionHasErrors('nis');
        $this->assertSame(1, Siswa::where('nis', '9990001')->count());
    }

    // ── Surat masuk + berkas ──────────────────────────────────────────────

    private function suratPayload(array $override = []): array
    {
        return array_merge([
            'asal_surat' => 'Dinas Pendidikan',
            'perihal' => 'Undangan rapat',
            'tanggal_terima' => '2026-10-01',
        ], $override);
    }

    public function test_surat_masuk_upload_is_stored_served_only_to_authorised_roles_and_removed_on_delete(): void
    {
        $this->as('operator')->post('/surat-masuk', $this->suratPayload([
            'file_scan' => UploadedFile::fake()->create('scan.pdf', 100, 'application/pdf'),
        ]))->assertSessionHasNoErrors()->assertRedirect();

        $surat = SuratMasuk::where('perihal', 'Undangan rapat')->firstOrFail();
        $this->assertStringStartsWith('surat-masuk/', $surat->file_scan);
        Storage::disk('local')->assertExists($surat->file_scan);

        // Operator boleh membuka; tamu dan guru tidak.
        $this->get(route('files.show', $surat->file_scan))->assertOk();
        auth()->logout();
        $this->get(route('files.show', $surat->file_scan))->assertRedirect('/login');
        $this->as('guru')->get(route('files.show', $surat->file_scan))->assertForbidden();

        // Menghapus surat menghapus berkasnya dari disk.
        $this->as('operator')->delete("/surat-masuk/{$surat->id}")->assertRedirect(route('surat-masuk.index'));
        $this->assertDatabaseMissing('surat_masuk', ['id' => $surat->id]);
        Storage::disk('local')->assertMissing($surat->file_scan);
    }

    public function test_surat_masuk_rejects_dangerous_or_oversized_files(): void
    {
        $this->as('operator');

        foreach ([
            'php' => UploadedFile::fake()->createWithContent('shell.php', '<?php system($_GET["c"]);'),
            'html' => UploadedFile::fake()->createWithContent('x.html', '<script>alert(1)</script>'),
            // Catatan: berkas bernama .pdf berisi PHP tidak diuji di sini karena UploadedFile::fake() menebak
            // tipe dari ekstensi, bukan isi. Pemeriksaan isi (finfo) hanya bisa dibuktikan lewat unggahan HTTP nyata.
            'besar' => UploadedFile::fake()->create('besar.pdf', 3000, 'application/pdf'),
        ] as $name => $file) {
            $this->post('/surat-masuk', $this->suratPayload(['file_scan' => $file]))
                ->assertSessionHasErrors('file_scan', "berkas {$name} harus ditolak");
        }

        $this->assertSame(0, SuratMasuk::where('perihal', 'Undangan rapat')->count());
        $this->assertSame([], Storage::disk('local')->allFiles('surat-masuk'));
    }

    public function test_file_route_rejects_path_traversal_and_unknown_folders(): void
    {
        $this->as('admin');

        $this->get('/files/surat-masuk/..%2F..%2F.env')->assertNotFound();
        $this->get('/files/..%2F.env')->assertNotFound();
        $this->get('/files/folder-tidak-dikenal/x.pdf')->assertNotFound();
        $this->get('/files/surat-masuk/tidak-ada.pdf')->assertNotFound();
    }

    // ── Raport ────────────────────────────────────────────────────────────

    public function test_raport_values_are_saved_updated_without_duplicates_and_exported_as_pdf(): void
    {
        $this->as('operator');
        $siswa = Siswa::firstOrFail();
        $tp = TahunPelajaran::firstOrFail();
        $mapel = Mapel::firstOrFail();
        $base = ['tahun_pelajaran_id' => $tp->id, 'semester' => 1];

        $this->post("/raport/{$siswa->id}/store", $base + ['nilai' => [$mapel->id => ['angka' => 88, 'capaian' => 'Sangat baik']]])
            ->assertSessionHasNoErrors();
        $this->post("/raport/{$siswa->id}/store", $base + ['nilai' => [$mapel->id => ['angka' => 92, 'capaian' => 'Istimewa']]])
            ->assertSessionHasNoErrors();

        $rows = RaportNilai::where(['siswa_id' => $siswa->id, 'mapel_id' => $mapel->id, 'tahun_pelajaran_id' => $tp->id, 'semester' => 1])->get();
        $this->assertCount(1, $rows, 'Menyimpan dua kali tidak boleh menggandakan baris');
        $this->assertEquals(92, $rows->first()->nilai_akhir);

        $pdf = $this->get("/raport/{$siswa->id}/export-pdf?tahun_pelajaran_id={$tp->id}&semester=1")->assertOk();
        $this->assertStringContainsString('application/pdf', $pdf->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_raport_rejects_out_of_range_scores(): void
    {
        $this->as('operator');
        $siswa = Siswa::firstOrFail();
        $mapel = Mapel::firstOrFail();

        $this->post("/raport/{$siswa->id}/store", [
            'tahun_pelajaran_id' => TahunPelajaran::firstOrFail()->id,
            'semester' => 1,
            'nilai' => [$mapel->id => ['angka' => 150]],
        ])->assertSessionHasErrors('nilai.'.$mapel->id.'.angka');
    }

    // ── Absensi guru ──────────────────────────────────────────────────────

    public function test_guru_can_record_attendance_and_saving_again_updates_instead_of_failing(): void
    {
        $this->as('guru');
        $guru = Guru::firstOrFail();
        $payload = fn (string $status) => ['tanggal' => '2026-10-07', 'status' => [$guru->id => $status], 'keterangan' => [$guru->id => 'uji']];

        $this->post('/absensi', $payload('hadir'))->assertSessionHasNoErrors()->assertRedirect()->assertSessionHas('success');
        $this->post('/absensi', $payload('sakit'))->assertSessionHasNoErrors()->assertRedirect()->assertSessionHas('success');

        $this->assertSame(1, AgendaGuru::where('guru_id', $guru->id)->whereDate('tanggal', '2026-10-07')->count());
        $this->assertSame('sakit', AgendaGuru::where('guru_id', $guru->id)->whereDate('tanggal', '2026-10-07')->value('status'));
    }

    public function test_attendance_rejects_unknown_status(): void
    {
        $this->as('guru');
        $guru = Guru::firstOrFail();

        $this->post('/absensi', ['tanggal' => '2026-10-07', 'status' => [$guru->id => 'bolos']])->assertSessionHasErrors('status.'.$guru->id);
    }

    public function test_password_hashing_is_used_for_seeded_accounts(): void
    {
        $this->assertTrue(Hash::check('operator123', User::where('role', 'operator')->value('password')));
        $this->assertNotSame('operator123', User::where('role', 'operator')->value('password'));
    }
}
