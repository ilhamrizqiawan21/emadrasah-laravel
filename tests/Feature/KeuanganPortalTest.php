<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\User;
use App\Support\PerluTindakan;
use App\Support\Terbilang;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeuanganPortalTest extends TestCase
{
    use RefreshDatabase;

    private Siswa $anak;

    private Siswa $orangLain;

    private User $wali;

    private Pembayaran $bayarAnak;

    private Pembayaran $bayarLain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $kelas = Kelas::create(['nama_kelas' => 'Portal 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->anak = Siswa::create(['nis' => '6660001', 'nama_lengkap' => 'Anak Saya', 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
        $this->orangLain = Siswa::create(['nis' => '6660002', 'nama_lengkap' => 'Anak Orang Lain', 'kelas_id' => $kelas->id, 'jenis_kelamin' => 'P', 'status' => 'Aktif']);
        $this->wali = User::create(['name' => 'Wali Uji', 'email' => 'wali-keu@uji.test', 'password' => 'rahasia-panjang-1', 'role' => 'wali_murid', 'is_active' => true]);
        $this->wali->anak()->attach($this->anak->id);

        $this->bayarAnak = $this->bayar($this->anak, 'SPP', 150000, 50000);
        $this->bayarLain = $this->bayar($this->orangLain, 'Buku', 80000, 80000);
    }

    private function bayar(Siswa $siswa, string $jenis, int $jumlah, int $dibayar): Pembayaran
    {
        $t = Tagihan::create(['siswa_id' => $siswa->id, 'jenis' => $jenis, 'periode' => $jenis === 'SPP' ? '2026-10' : null, 'jumlah' => $jumlah, 'jatuh_tempo' => '2099-01-01']);

        return Pembayaran::create(['tagihan_id' => $t->id, 'jumlah' => $dibayar, 'tanggal' => '2026-10-05', 'metode' => 'transfer']);
    }

    public function test_terbilang_spells_rupiah_in_indonesian(): void
    {
        $this->assertSame('nol rupiah', Terbilang::rupiah(0));
        $this->assertSame('seratus lima puluh ribu rupiah', Terbilang::rupiah(150000));
        $this->assertSame('seribu rupiah', Terbilang::rupiah(1000));
        $this->assertSame('sebelas ribu rupiah', Terbilang::rupiah(11000));
        $this->assertSame('dua juta tiga ratus dua puluh lima ribu rupiah', Terbilang::rupiah(2325000));
        $this->assertSame('satu miliar rupiah', Terbilang::rupiah(1000000000));
    }

    public function test_staff_can_print_a_receipt_as_pdf(): void
    {
        $this->actingAs(User::where('role', 'operator')->firstOrFail());

        $r = $this->get("/keuangan/pembayaran/{$this->bayarAnak->id}/kuitansi")->assertOk();
        $this->assertSame('application/pdf', $r->headers->get('Content-Type'));

        $html = view('keuangan.kuitansi', ['pembayaran' => $this->bayarAnak->load('tagihan.siswa.kelas', 'pencatat')])->render();
        $this->assertStringContainsString('Anak Saya', $html);
        $this->assertStringContainsString('lima puluh ribu rupiah', $html);
        $this->assertStringContainsString('Rp 50.000', $html);
    }

    public function test_wali_sees_only_their_childs_bills_with_balance(): void
    {
        $this->actingAs($this->wali)->get("/wali/{$this->anak->id}")->assertOk()
            ->assertSee('Tagihan')->assertSee('Rp 150.000')->assertSee('Rp 100.000')   // jumlah dan sisa
            ->assertDontSee('Rp 80.000');
    }

    public function test_wali_can_download_receipts_of_their_child_only(): void
    {
        $this->actingAs($this->wali);

        $this->get("/wali/{$this->anak->id}/kuitansi/{$this->bayarAnak->id}")->assertOk()->assertHeader('Content-Type', 'application/pdf');
        // pembayaran milik anak lain: lewat URL anak sendiri maupun anak lain, tidak boleh terbuka
        $this->get("/wali/{$this->anak->id}/kuitansi/{$this->bayarLain->id}")->assertNotFound();
        $this->get("/wali/{$this->orangLain->id}/kuitansi/{$this->bayarLain->id}")->assertNotFound();
    }

    public function test_overdue_bills_appear_in_the_action_panel_for_staff(): void
    {
        Tagihan::create(['siswa_id' => $this->anak->id, 'jenis' => 'Seragam', 'jumlah' => 100000, 'jatuh_tempo' => '2020-01-01']);
        $lunas = Tagihan::create(['siswa_id' => $this->orangLain->id, 'jenis' => 'Seragam', 'jumlah' => 100000, 'jatuh_tempo' => '2020-01-01']);
        Pembayaran::create(['tagihan_id' => $lunas->id, 'jumlah' => 100000, 'tanggal' => '2020-01-01', 'metode' => 'tunai']);

        $item = collect(PerluTindakan::untuk(User::where('role', 'admin')->firstOrFail())['item'])->firstWhere('kunci', 'tagihan-terlambat');

        $this->assertSame(1, $item['jumlah']);
        $this->assertSame(['Anak Saya'], $item['contoh']);
    }
}
