<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CatatanBk;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\PelanggaranJenis;
use App\Models\Siswa;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KedisiplinanTest extends TestCase
{
    use RefreshDatabase;

    private Siswa $a;

    private Siswa $b;

    private PelanggaranJenis $telat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $kelas = Kelas::create(['nama_kelas' => 'Disiplin 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->a = Siswa::create(['nis' => '4440001', 'nama_lengkap' => 'Ani Disiplin', 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'P', 'status' => 'Aktif']);
        $this->b = Siswa::create(['nis' => '4440002', 'nama_lengkap' => 'Budi Tertib', 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $this->telat = PelanggaranJenis::create(['nama' => 'Terlambat', 'kategori' => 'ringan', 'poin' => 5]);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    public function test_violation_copies_points_from_the_type_and_records_the_officer(): void
    {
        $this->post("/kedisiplinan/siswa/{$this->a->id}/pelanggaran", ['jenis_id' => $this->telat->id, 'tanggal' => '2026-10-10', 'keterangan' => 'Pukul 07.20'])
            ->assertRedirect()->assertSessionHas('success');

        $p = Pelanggaran::firstOrFail();
        $this->assertSame(5, $p->poin);
        $this->assertSame(User::where('role', 'operator')->firstOrFail()->id, $p->dicatat_oleh);

        $this->telat->update(['poin' => 50]);   // mengubah jenis tidak mengubah catatan lama
        $this->assertSame(5, $p->fresh()->poin);
    }

    public function test_violation_validates_type_and_date(): void
    {
        $this->post("/kedisiplinan/siswa/{$this->a->id}/pelanggaran", ['jenis_id' => 9999, 'tanggal' => '2099-01-01'])
            ->assertSessionHasErrors(['jenis_id', 'tanggal']);
        $this->assertSame(0, Pelanggaran::count());
    }

    public function test_index_ranks_students_by_total_points_and_flags_the_threshold(): void
    {
        config(['madrasah.ambang_poin' => 10]);
        $berat = PelanggaranJenis::create(['nama' => 'Berkelahi', 'kategori' => 'berat', 'poin' => 30]);
        Pelanggaran::create(['siswa_id' => $this->a->id, 'jenis_id' => $berat->id, 'poin' => 30, 'tanggal' => '2026-10-01']);
        Pelanggaran::create(['siswa_id' => $this->b->id, 'jenis_id' => $this->telat->id, 'poin' => 5, 'tanggal' => '2026-10-02']);

        $this->get('/kedisiplinan')->assertOk()
            ->assertSeeInOrder(['Ani Disiplin', 'Budi Tertib'])
            ->assertViewHas('daftar', fn ($d) => (int) $d[0]->total_poin === 30 && (int) $d[1]->total_poin === 5)
            ->assertSee('Melewati ambang');   // hanya Ani (30 >= 10)
        $this->assertSame(1, substr_count($this->get('/kedisiplinan')->getContent(), 'Melewati ambang'));
    }

    public function test_student_page_shows_history_total_and_deleting_reduces_it(): void
    {
        $p = Pelanggaran::create(['siswa_id' => $this->a->id, 'jenis_id' => $this->telat->id, 'poin' => 5, 'tanggal' => '2026-10-01', 'keterangan' => 'Catatan satu']);

        $this->get("/kedisiplinan/siswa/{$this->a->id}")->assertOk()->assertSee('Terlambat')->assertSee('Catatan satu')->assertViewHas('totalPoin', 5);

        $this->delete("/kedisiplinan/pelanggaran/{$p->id}")->assertRedirect();
        $this->get("/kedisiplinan/siswa/{$this->a->id}")->assertViewHas('totalPoin', 0);
    }

    public function test_counselling_note_is_stored_and_its_content_stays_out_of_the_audit_log(): void
    {
        $this->post("/kedisiplinan/siswa/{$this->a->id}/bk", ['tanggal' => '2026-10-10', 'topik' => 'Sering melamun', 'uraian' => 'RAHASIA-ISI-KONSELING', 'tindak_lanjut' => 'Panggil wali'])
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame('Sering melamun', CatatanBk::firstOrFail()->topik);
        $this->get("/kedisiplinan/siswa/{$this->a->id}")->assertSee('RAHASIA-ISI-KONSELING');
        $this->assertStringNotContainsString('RAHASIA-ISI-KONSELING', AuditLog::all()->toJson());
    }

    public function test_type_in_use_cannot_be_deleted_but_unused_can(): void
    {
        Pelanggaran::create(['siswa_id' => $this->a->id, 'jenis_id' => $this->telat->id, 'poin' => 5, 'tanggal' => '2026-10-01']);
        $kosong = PelanggaranJenis::create(['nama' => 'Bolos', 'kategori' => 'sedang', 'poin' => 15]);

        $this->delete("/kedisiplinan/jenis/{$this->telat->id}")->assertSessionHas('error');
        $this->assertModelExists($this->telat);
        $this->delete("/kedisiplinan/jenis/{$kosong->id}")->assertSessionHas('success');
        $this->assertModelMissing($kosong);
    }

    public function test_type_can_be_created_and_duplicate_names_are_rejected(): void
    {
        $this->post('/kedisiplinan/jenis', ['nama' => 'Bolos', 'kategori' => 'sedang', 'poin' => 15])->assertRedirect()->assertSessionHas('success');
        $this->post('/kedisiplinan/jenis', ['nama' => 'Bolos', 'kategori' => 'sedang', 'poin' => 15])->assertSessionHasErrors('nama');
        $this->assertSame(1, PelanggaranJenis::where('nama', 'Bolos')->count());
    }

    public function test_wali_cannot_open_discipline_pages(): void
    {
        $wali = User::create(['name' => 'Wali', 'email' => 'wali-disiplin@test.id', 'password' => 'rahasia-panjang-1', 'role' => 'wali_murid', 'is_active' => true]);
        $this->actingAs($wali)->get("/kedisiplinan/siswa/{$this->a->id}")->assertForbidden();
    }
}
