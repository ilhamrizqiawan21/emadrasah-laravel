<?php

namespace Tests\Feature;

use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\RaportRilis;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalWaliTest extends TestCase
{
    use RefreshDatabase;

    private User $wali;

    private Siswa $anak;

    private Siswa $orangLain;

    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->kelas = Kelas::create(['nama_kelas' => 'Uji 7A', 'tingkat' => '7', 'kapasitas' => 30]);
        $this->anak = $this->siswa('8880001', 'Anak Saya');
        $this->orangLain = $this->siswa('8880002', 'Anak Orang Lain');
        $this->wali = $this->akun('wali_murid', 'wali@uji.test');
        $this->wali->anak()->attach($this->anak->id);
    }

    private function siswa(string $nis, string $nama): Siswa
    {
        return Siswa::create(['nis' => $nis, 'nama_lengkap' => $nama, 'kelas_id' => $this->kelas->id, 'jenis_kelamin' => 'L', 'status' => 'Aktif']);
    }

    private function akun(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'rahasia-panjang-1', 'role' => $role, 'is_active' => true]);
    }

    private function nilai(Siswa $siswa, bool $rilis = true): void
    {
        $tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();
        $mapel = Mapel::create(['nama_mapel' => 'Fikih Uji', 'jp_per_sesi' => 2]);
        RaportNilai::create(['siswa_id' => $siswa->id, 'tahun_pelajaran_id' => $tp->id, 'semester' => 1, 'mapel_id' => $mapel->id, 'nilai_akhir' => 88, 'kktp' => 75]);
        if ($rilis) {
            RaportRilis::create(['tahun_pelajaran_id' => $tp->id, 'semester' => 1]);
        }
    }

    public function test_unreleased_raport_is_hidden_from_wali_in_page_and_pdf(): void
    {
        $this->nilai($this->anak, rilis: false);

        $this->actingAs($this->wali)->get(route('wali.show', $this->anak))
            ->assertOk()->assertDontSee('Fikih Uji')->assertSee('belum dirilis');
        $this->actingAs($this->wali)->get(route('wali.raport', ['siswa' => $this->anak, 'semester' => 1]))->assertNotFound();
    }

    public function test_admin_can_release_and_withdraw_a_semester(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();
        $data = ['tahun_pelajaran_id' => $tp->id, 'semester' => 1];

        $this->actingAs($admin)->post(route('pengaturan.raport-rilis'), $data + ['dirilis' => 1])->assertRedirect();
        $this->assertTrue(RaportRilis::dirilis($tp->id, 1));
        $this->assertFalse(RaportRilis::dirilis($tp->id, 2));

        $this->actingAs($admin)->post(route('pengaturan.raport-rilis'), $data + ['dirilis' => 0])->assertRedirect();
        $this->assertFalse(RaportRilis::dirilis($tp->id, 1));
    }

    public function test_operator_cannot_release_raport(): void
    {
        $tp = TahunPelajaran::where('is_aktif', true)->firstOrFail();

        $this->actingAs(User::where('role', 'operator')->firstOrFail())
            ->post(route('pengaturan.raport-rilis'), ['tahun_pelajaran_id' => $tp->id, 'semester' => 1, 'dirilis' => 1])->assertForbidden();
    }

    public function test_dashboard_redirects_wali_to_their_portal_instead_of_admin_dashboard(): void
    {
        $this->actingAs($this->wali)->get('/dashboard')->assertRedirect(route('wali.index'));
    }

    public function test_wali_without_linked_children_sees_an_explanation(): void
    {
        $kosong = $this->akun('wali_murid', 'kosong@uji.test');

        $this->actingAs($kosong)->get(route('wali.index'))
            ->assertOk()->assertSee('belum ditautkan');
    }

    public function test_index_lists_only_own_children(): void
    {
        $this->actingAs($this->wali)->get(route('wali.index'))
            ->assertOk()->assertSee('Anak Saya')->assertDontSee('Anak Orang Lain');
    }

    public function test_detail_shows_attendance_summary_and_grades_of_own_child(): void
    {
        AbsensiSiswa::create(['tanggal' => today(), 'siswa_id' => $this->anak->id, 'kelas_id' => $this->kelas->id, 'status' => 'alpha']);
        $this->nilai($this->anak);

        $this->actingAs($this->wali)->get(route('wali.show', $this->anak))
            ->assertOk()->assertSee('Anak Saya')->assertSee('Fikih Uji')->assertSee('88')->assertSee('Alpha');
    }

    public function test_other_childrens_data_is_not_found_not_forbidden(): void
    {
        $this->actingAs($this->wali)->get(route('wali.show', $this->orangLain))->assertNotFound();
        $this->actingAs($this->wali)->get(route('wali.raport', $this->orangLain))->assertNotFound();
    }

    public function test_siswa_role_uses_the_same_portal_for_their_own_record(): void
    {
        $akun = $this->akun('siswa', 'siswa@uji.test');
        $akun->anak()->attach($this->orangLain->id);

        $this->actingAs($akun)->get(route('wali.show', $this->orangLain))->assertOk()->assertSee('Anak Orang Lain');
        $this->actingAs($akun)->get(route('wali.show', $this->anak))->assertNotFound();
    }

    public function test_raport_pdf_is_served_for_own_child_only(): void
    {
        $this->nilai($this->anak);

        $this->actingAs($this->wali)->get(route('wali.raport', ['siswa' => $this->anak, 'semester' => 1]))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_wali_cannot_open_staff_pages(): void
    {
        foreach (['siswa.index', 'users.index', 'absensi-siswa.index', 'nilai.index', 'raport.index'] as $nama) {
            $this->actingAs($this->wali)->get(route($nama))->assertForbidden();
        }
    }

    public function test_staff_cannot_open_the_wali_portal(): void
    {
        foreach (['admin', 'operator', 'guru'] as $role) {
            $this->actingAs(User::where('role', $role)->firstOrFail())->get(route('wali.index'))->assertForbidden();
        }
    }

    public function test_admin_links_and_unlinks_a_child_by_nis(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $kosong = $this->akun('wali_murid', 'tautan@uji.test');

        $this->actingAs($admin)->post(route('users.siswa.store', $kosong), ['nis' => '8880002'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertTrue($kosong->anak()->whereKey($this->orangLain->id)->exists());

        $this->actingAs($admin)->delete(route('users.siswa.destroy', [$kosong, $this->orangLain]))->assertRedirect();
        $this->assertFalse($kosong->anak()->whereKey($this->orangLain->id)->exists());
    }

    public function test_linking_rejects_unknown_nis_and_non_wali_accounts(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $guru = User::where('role', 'guru')->firstOrFail();

        $this->actingAs($admin)->from('/x')->post(route('users.siswa.store', $this->wali), ['nis' => 'TIDAKADA'])
            ->assertSessionHasErrors('nis');
        $this->actingAs($admin)->post(route('users.siswa.store', $guru), ['nis' => '8880001'])->assertNotFound();
    }

    public function test_only_admin_can_manage_links(): void
    {
        $this->actingAs(User::where('role', 'operator')->firstOrFail())
            ->post(route('users.siswa.store', $this->wali), ['nis' => '8880002'])->assertForbidden();
    }
}
