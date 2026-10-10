<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeuanganTest extends TestCase
{
    use RefreshDatabase;

    private Kelas $kelas;

    private Siswa $a;

    private Siswa $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->kelas = Kelas::create(['nama_kelas' => 'Keu 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->a = Siswa::create(['nis' => '3330001', 'nama_lengkap' => 'Ani Keuangan', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'P', 'status' => 'Aktif']);
        $this->b = Siswa::create(['nis' => '3330002', 'nama_lengkap' => 'Budi Lulus', 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Lulus']);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
    }

    private function tagihan(int $jumlah = 150000, string $tempo = '2099-01-10', ?Siswa $siswa = null): Tagihan
    {
        return Tagihan::create(['siswa_id' => ($siswa ?? $this->a)->id, 'jenis' => 'SPP', 'periode' => '2099-01', 'jumlah' => $jumlah, 'jatuh_tempo' => $tempo]);
    }

    public function test_generate_creates_bills_for_active_students_only_and_is_idempotent(): void
    {
        $data = ['kelas_id' => $this->kelas->id, 'jenis' => 'SPP', 'periode' => '2026-11', 'jumlah' => 150000, 'jatuh_tempo' => '2026-11-10'];

        $this->post('/keuangan/generate', $data)->assertRedirect()->assertSessionHas('success');
        $this->assertSame([$this->a->id], Tagihan::pluck('siswa_id')->all());

        $this->post('/keuangan/generate', $data)->assertRedirect();
        $this->assertSame(1, Tagihan::count());   // tidak dobel
    }

    public function test_generate_validates_input(): void
    {
        $this->post('/keuangan/generate', ['kelas_id' => $this->kelas->id, 'jenis' => 'SPP', 'periode' => '2026-13', 'jumlah' => 0, 'jatuh_tempo' => '2026-11-10'])
            ->assertSessionHasErrors(['periode', 'jumlah']);
        $this->assertSame(0, Tagihan::count());
    }

    public function test_partial_then_full_payment_updates_status(): void
    {
        $t = $this->tagihan(150000);

        $this->post("/keuangan/tagihan/{$t->id}/pembayaran", ['jumlah' => 50000, 'tanggal' => '2026-10-10', 'metode' => 'tunai'])->assertRedirect();
        $t = Tagihan::denganTerbayar()->find($t->id);
        $this->assertSame('sebagian', $t->status());
        $this->assertSame(100000, $t->sisa());

        $this->post("/keuangan/tagihan/{$t->id}/pembayaran", ['jumlah' => 100000, 'tanggal' => '2026-10-09', 'metode' => 'transfer'])->assertRedirect();
        $this->assertSame('lunas', Tagihan::denganTerbayar()->find($t->id)->status());
        $this->assertSame(User::where('role', 'operator')->firstOrFail()->id, Pembayaran::latest('id')->first()->dicatat_oleh);
    }

    public function test_payment_cannot_exceed_remaining_balance(): void
    {
        $t = $this->tagihan(100000);

        $this->post("/keuangan/tagihan/{$t->id}/pembayaran", ['jumlah' => 100001, 'tanggal' => '2026-10-10', 'metode' => 'tunai'])
            ->assertSessionHasErrors('jumlah');
        $this->assertSame(0, Pembayaran::count());
    }

    public function test_deleting_a_payment_restores_the_balance(): void
    {
        $t = $this->tagihan(100000);
        $p = Pembayaran::create(['tagihan_id' => $t->id, 'jumlah' => 100000, 'tanggal' => '2026-10-10', 'metode' => 'tunai']);

        $this->delete("/keuangan/pembayaran/{$p->id}")->assertRedirect();
        $this->assertSame('belum', Tagihan::denganTerbayar()->find($t->id)->status());
    }

    public function test_bill_with_payments_cannot_be_deleted_but_unpaid_can(): void
    {
        $dibayar = $this->tagihan(100000);
        Pembayaran::create(['tagihan_id' => $dibayar->id, 'jumlah' => 1000, 'tanggal' => '2026-10-10', 'metode' => 'tunai']);
        $kosong = Tagihan::create(['siswa_id' => $this->b->id, 'jenis' => 'Buku', 'jumlah' => 5000, 'jatuh_tempo' => '2099-01-10']);

        $this->delete("/keuangan/tagihan/{$dibayar->id}")->assertSessionHas('error');
        $this->assertModelExists($dibayar);

        $this->delete("/keuangan/tagihan/{$kosong->id}")->assertSessionHas('success');
        $this->assertModelMissing($kosong);
    }

    public function test_index_filters_by_status_and_summarises_arrears(): void
    {
        $lunas = $this->tagihan(100000, '2020-01-10');
        Pembayaran::create(['tagihan_id' => $lunas->id, 'jumlah' => 100000, 'tanggal' => '2020-01-05', 'metode' => 'tunai']);
        Tagihan::create(['siswa_id' => $this->b->id, 'jenis' => 'Buku', 'jumlah' => 40000, 'jatuh_tempo' => '2020-02-01']);   // lewat tempo, belum bayar

        $r = $this->get('/keuangan')->assertOk();
        $r->assertViewHas('ringkasan', fn ($s) => $s['tagihan'] === 140000 && $s['terbayar'] === 100000 && $s['tunggakan'] === 40000);

        $this->get('/keuangan?status=lunas')->assertOk()->assertSee('Ani Keuangan')->assertDontSee('Budi Lulus');
        $this->get('/keuangan?status=terlambat')->assertOk()->assertSee('Budi Lulus')->assertDontSee('Ani Keuangan');
    }

    public function test_show_page_lists_payments(): void
    {
        $t = $this->tagihan(100000);
        Pembayaran::create(['tagihan_id' => $t->id, 'jumlah' => 25000, 'tanggal' => '2026-10-10', 'metode' => 'transfer', 'catatan' => 'via BSI']);

        $this->get("/keuangan/tagihan/{$t->id}")->assertOk()->assertSee('Ani Keuangan')->assertSee('via BSI');
    }
}
