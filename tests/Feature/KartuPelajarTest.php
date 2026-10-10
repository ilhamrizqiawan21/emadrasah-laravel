<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Support\Code39;
use App\Support\KartuPelajar;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KartuPelajarTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Siswa $aktif;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->kelas = Kelas::create(['nama_kelas' => 'Kartu 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->aktif = Siswa::create(['nis' => '7770001', 'nisn' => '0099887766', 'nama_lengkap' => 'Dina Kartu', 'tempat_lahir' => 'Bogor', 'tanggal_lahir' => '2012-02-03', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'P', 'status' => 'Aktif']);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    public function test_every_code39_symbol_has_exactly_three_wide_elements_of_nine(): void
    {
        foreach (Code39::POLA as $karakter => $pola) {
            $this->assertSame(9, strlen($pola), "panjang pola {$karakter}");
            $this->assertSame(3, substr_count($pola, '1'), "elemen lebar {$karakter}");
        }
        $this->assertCount(44, Code39::POLA);   // 43 karakter + start/stop "*"
    }

    public function test_code39_svg_has_five_bars_per_symbol_including_start_and_stop(): void
    {
        $svg = base64_decode(substr(Code39::dataUri('123'), strlen('data:image/svg+xml;base64,')));

        $this->assertSame(5 * 5, substr_count($svg, '<rect'));   // *,1,2,3,* masing-masing 5 batang
        $this->assertStringContainsString('<svg', $svg);
    }

    public function test_code39_accepts_only_supported_characters(): void
    {
        $this->assertTrue(Code39::bisa('AB-12.3'));
        $this->assertTrue(Code39::bisa('7770001'));
        $this->assertFalse(Code39::bisa(''));
        $this->assertFalse(Code39::bisa('NIS#1'));
        $this->assertFalse(Code39::bisa('*A*'));   // bintang dicadangkan untuk start/stop
        $this->assertNull(Code39::dataUri('NIS#1'));
    }

    public function test_single_card_returns_a_pdf(): void
    {
        $r = $this->get("/kartu-pelajar/siswa/{$this->aktif->id}")->assertOk();

        $this->assertSame('application/pdf', $r->headers->get('Content-Type'));
    }

    public function test_card_view_has_identity_and_barcode_and_embeds_the_photo_when_present(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('siswa-foto/dina.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='));
        $this->aktif->update(['foto' => 'siswa-foto/dina.png']);

        $html = view('kartu-pelajar.cetak', ['kartu' => [KartuPelajar::data($this->aktif->fresh('kelas'))], 'satu' => true])->render();

        $this->assertStringContainsString('Dina Kartu', $html);
        $this->assertStringContainsString('7770001', $html);
        $this->assertStringContainsString('Kartu 7A', $html);
        $this->assertStringContainsString('data:image/png;base64', $html);
        $this->assertStringContainsString('data:image/svg+xml;base64', $html);
    }

    public function test_card_without_photo_or_with_unsupported_nis_still_renders(): void
    {
        $this->aktif->update(['nis' => 'NIS#1']);

        $html = view('kartu-pelajar.cetak', ['kartu' => [KartuPelajar::data($this->aktif->fresh('kelas'))], 'satu' => true])->render();

        $this->assertStringContainsString('Dina Kartu', $html);
        $this->assertStringNotContainsString('data:image/svg+xml;base64', $html);
    }

    public function test_photo_outside_the_student_photo_folder_is_never_embedded(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('surat-keluar/rahasia.png', 'RAHASIA');
        $this->aktif->update(['foto' => 'surat-keluar/rahasia.png']);

        $data = KartuPelajar::data($this->aktif->fresh('kelas'));

        $this->assertNull($data['foto']);
    }

    public function test_only_active_students_get_a_card(): void
    {
        $this->aktif->update(['status' => 'Lulus']);

        $this->get("/kartu-pelajar/siswa/{$this->aktif->id}")->assertRedirect()->assertSessionHas('error');
    }

    public function test_class_sheet_includes_active_students_only_and_rejects_empty_classes(): void
    {
        Siswa::create(['nis' => '7770002', 'nama_lengkap' => 'Eko Lulus', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Lulus']);

        $r = $this->get("/kartu-pelajar/kelas/{$this->kelas->id}")->assertOk();
        $this->assertSame('application/pdf', $r->headers->get('Content-Type'));

        $kosong = Kelas::create(['nama_kelas' => 'Kosong', 'tingkat' => '9', 'kapasitas' => 30]);
        $this->get("/kartu-pelajar/kelas/{$kosong->id}")->assertRedirect()->assertSessionHas('error');
    }
}
