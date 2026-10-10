<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\PerkembanganSiswa;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KenaikanKelasTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $asal;

    private Kelas $tujuan;

    private TahunPelajaran $tp;

    private Siswa $a;

    private Siswa $b;

    private Siswa $c;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();
        $this->asal = Kelas::create(['nama_kelas' => 'Naik 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->tujuan = Kelas::create(['nama_kelas' => 'Naik 8A', 'tingkat' => '8', 'kapasitas' => 30]);
        $this->a = $this->siswa('5550001', 'Anak A');
        $this->b = $this->siswa('5550002', 'Anak B');
        $this->c = $this->siswa('5550003', 'Anak C');
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    private function siswa(string $nis, string $nama, string $status = 'Aktif'): Siswa
    {
        return Siswa::create(['nis' => $nis, 'nama_lengkap' => $nama, 'kelas_id' => $this->asal->id, 'jenis_kelamin' => 'L', 'status' => $status]);
    }

    private function proses(array $aksi, array $override = [])
    {
        return $this->post('/kenaikan-kelas', array_merge([
            'kelas_asal_id' => $this->asal->id, 'tahun_pelajaran_id' => $this->tp->id,
            'kelas_tujuan_id' => $this->tujuan->id, 'aksi' => $aksi,
        ], $override));
    }

    public function test_promote_moves_students_and_records_history(): void
    {
        $this->proses([$this->a->id => 'naik', $this->b->id => 'naik'])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertSame($this->tujuan->id, $this->a->fresh()->kelas_id);
        $this->assertSame($this->tujuan->id, $this->b->fresh()->kelas_id);
        $this->assertSame($this->asal->id, $this->c->fresh()->kelas_id);
        $this->assertDatabaseHas('riwayat_kelas', [
            'siswa_id' => $this->a->id, 'tahun_pelajaran_id' => $this->tp->id, 'hasil' => 'naik',
            'kelas_asal_id' => $this->asal->id, 'kelas_tujuan_id' => $this->tujuan->id, 'kelas_asal_nama' => 'Naik 7A',
        ]);
    }

    public function test_repeat_year_keeps_class_and_graduating_closes_the_record(): void
    {
        $this->proses([$this->a->id => 'tinggal', $this->b->id => 'lulus'], ['kelas_tujuan_id' => null])->assertSessionHasNoErrors();

        $this->assertSame($this->asal->id, $this->a->fresh()->kelas_id);
        $this->assertSame('Aktif', $this->a->fresh()->status);
        $this->assertDatabaseHas('riwayat_kelas', ['siswa_id' => $this->a->id, 'hasil' => 'tinggal', 'kelas_tujuan_id' => null]);

        $this->assertSame('Lulus', $this->b->fresh()->status);
        $this->assertSame($this->asal->id, $this->b->fresh()->kelas_id);
        $perkembangan = PerkembanganSiswa::where('siswa_id', $this->b->id)->firstOrFail();
        $this->assertSame('Lulus', $perkembangan->jenis_keluar);
        $this->assertSame((int) substr($this->tp->kode, -4), (int) $perkembangan->thn_lulus);
    }

    public function test_deferred_students_are_left_untouched(): void
    {
        $this->proses([$this->a->id => 'naik', $this->b->id => 'tunda'])->assertSessionHasNoErrors();

        $this->assertSame($this->asal->id, $this->b->fresh()->kelas_id);
        $this->assertSame(0, RiwayatKelas::where('siswa_id', $this->b->id)->count());
    }

    public function test_processing_twice_in_the_same_year_does_not_repeat(): void
    {
        $this->proses([$this->a->id => 'naik']);
        $tujuan2 = Kelas::create(['nama_kelas' => 'Naik 9A', 'tingkat' => '9', 'kapasitas' => 30]);
        // a sekarang di kelas tujuan; memproses lagi dari kelas asal harus mengabaikannya
        $this->proses([$this->a->id => 'naik'], ['kelas_tujuan_id' => $tujuan2->id])->assertSessionHasNoErrors();
        $this->assertSame($this->tujuan->id, $this->a->fresh()->kelas_id);
        $this->assertSame(1, RiwayatKelas::where('siswa_id', $this->a->id)->count());

        // siswa yang tinggal kelas lalu diproses lagi di tahun yang sama juga tidak diulang
        $this->proses([$this->b->id => 'tinggal'], ['kelas_tujuan_id' => null]);
        $this->proses([$this->b->id => 'naik']);
        $this->assertSame($this->asal->id, $this->b->fresh()->kelas_id);
        $this->assertSame('tinggal', RiwayatKelas::where('siswa_id', $this->b->id)->value('hasil'));
    }

    public function test_validation(): void
    {
        $this->proses([$this->a->id => 'naik'], ['kelas_tujuan_id' => null])->assertSessionHasErrors('kelas_tujuan_id');
        $this->proses([$this->a->id => 'naik'], ['kelas_tujuan_id' => $this->asal->id])->assertSessionHasErrors('kelas_tujuan_id');
        $this->proses([$this->a->id => 'loncat'])->assertSessionHasErrors('aksi.'.$this->a->id);
        $this->proses([$this->a->id => 'naik'], ['tahun_pelajaran_id' => 999999])->assertSessionHasErrors('tahun_pelajaran_id');

        $this->assertSame(0, RiwayatKelas::count());
        $this->assertSame($this->asal->id, $this->a->fresh()->kelas_id);
    }

    public function test_students_from_other_classes_or_inactive_are_ignored(): void
    {
        $lain = Siswa::create(['nis' => '5550009', 'nama_lengkap' => 'Anak Lain', 'kelas_id' => $this->tujuan->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $pindah = $this->siswa('5550010', 'Anak Pindah', 'Pindah');

        $this->proses([$lain->id => 'lulus', $pindah->id => 'naik'])->assertSessionHasNoErrors();

        $this->assertSame('Aktif', $lain->fresh()->status);
        $this->assertSame($this->asal->id, $pindah->fresh()->kelas_id);
        $this->assertSame(0, RiwayatKelas::count());
    }

    public function test_index_lists_active_students_and_marks_processed_ones(): void
    {
        $this->siswa('5550011', 'Anak Pindah', 'Pindah');
        $this->proses([$this->a->id => 'naik']);

        $r = $this->get('/kenaikan-kelas?kelas_id='.$this->tujuan->id.'&tahun_pelajaran_id='.$this->tp->id)->assertOk();
        $r->assertDontSee('Anak Pindah');

        $r = $this->get('/kenaikan-kelas?kelas_id='.$this->asal->id.'&tahun_pelajaran_id='.$this->tp->id)->assertOk();
        $r->assertSee('Anak B')->assertDontSee('Anak Pindah');
    }

    public function test_undo_restores_class_and_status_and_removes_history(): void
    {
        $this->proses([$this->a->id => 'naik', $this->b->id => 'lulus']);

        foreach ([$this->a, $this->b] as $s) {
            $riwayat = RiwayatKelas::where('siswa_id', $s->id)->firstOrFail();
            $this->delete("/kenaikan-kelas/{$riwayat->id}")->assertSessionHas('success');
        }

        $this->assertSame($this->asal->id, $this->a->fresh()->kelas_id);
        $this->assertSame('Aktif', $this->b->fresh()->status);
        $this->assertSame(0, RiwayatKelas::count());
        $this->assertNull(PerkembanganSiswa::where('siswa_id', $this->b->id)->value('jenis_keluar'));
    }

    public function test_undo_refuses_when_student_moved_on_manually(): void
    {
        $this->proses([$this->a->id => 'naik']);
        Siswa::whereKey($this->a->id)->update(['kelas_id' => $this->asal->id]); // diubah manual lewat menu Siswa
        $riwayat = RiwayatKelas::firstOrFail();

        $this->delete("/kenaikan-kelas/{$riwayat->id}")->assertSessionHas('error');
        $this->assertSame(1, RiwayatKelas::count());
    }

    public function test_history_is_shown_in_the_student_ledger(): void
    {
        $this->proses([$this->a->id => 'naik']);

        $this->get("/buku-induk/{$this->a->id}")->assertOk()->assertSee('Riwayat Kelas')->assertSee('Naik 7A')->assertSee('Naik 8A');
    }

    public function test_guru_cannot_promote(): void
    {
        $this->actingAs(User::where('role', 'guru')->firstOrFail());
        $this->get('/kenaikan-kelas')->assertForbidden();
        $this->proses([$this->a->id => 'naik'])->assertForbidden();
        $this->assertSame($this->asal->id, $this->a->fresh()->kelas_id);
    }
}
