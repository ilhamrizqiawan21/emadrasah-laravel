<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\KategoriSarana;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrudMasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_crud_users(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/users', [
            'name' => 'Operator Madrasah',
            'email' => 'operator@example.test',
            'password' => 'password',
            'phone' => '08123456789',
            'alamat' => 'Kantor TU',
            'role' => 'operator',
            'is_active' => true,
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'operator@example.test')->firstOrFail();

        $this->assertTrue(Hash::check('password', $user->password));

        $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Operator Akademik',
            'email' => 'operator.akademik@example.test',
            'password' => '',
            'phone' => '08987654321',
            'alamat' => 'Ruang Akademik',
            'role' => 'operator',
            'is_active' => true,
        ])->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Operator Akademik',
            'email' => 'operator.akademik@example.test',
            'phone' => '08987654321',
        ]);

        $this->actingAs($admin)->delete("/users/{$user->id}")
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_email_must_be_unique(): void
    {
        $admin = $this->admin();

        User::factory()->create(['email' => 'duplikat@example.test']);

        $this->actingAs($admin)->post('/users', [
            'name' => 'User Duplikat',
            'email' => 'duplikat@example.test',
            'password' => 'password',
            'role' => 'operator',
            'is_active' => true,
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_crud_guru(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/guru', [
            'kode' => 'GR-CRUD-001',
            'nama' => 'Guru CRUD',
            'bidang_studi' => 'Matematika',
            'nip' => '199001012020121001',
            'email' => 'guru.crud@example.test',
            'phone' => '081111111111',
            'beban_jp' => 24,
        ])->assertRedirect(route('guru.index'));

        $guru = Guru::where('kode', 'GR-CRUD-001')->firstOrFail();

        $this->actingAs($admin)->put("/guru/{$guru->id}", [
            'kode' => 'GR-CRUD-002',
            'nama' => 'Guru CRUD Update',
            'bidang_studi' => 'IPA',
            'nip' => '199001012020121002',
            'email' => 'guru.crud.update@example.test',
            'phone' => '082222222222',
            'beban_jp' => 20,
        ])->assertRedirect(route('guru.index'));

        $this->assertDatabaseHas('gurus', [
            'id' => $guru->id,
            'kode' => 'GR-CRUD-002',
            'nama' => 'Guru CRUD Update',
            'beban_jp' => 20,
        ]);

        $this->actingAs($admin)->delete("/guru/{$guru->id}")
            ->assertRedirect(route('guru.index'));

        $this->assertSoftDeleted('gurus', ['id' => $guru->id]);
    }

    public function test_guru_kode_must_be_unique(): void
    {
        $admin = $this->admin();

        Guru::create([
            'kode' => 'GR-DUP',
            'nama' => 'Guru Lama',
            'beban_jp' => 24,
        ]);

        $this->actingAs($admin)->post('/guru', [
            'kode' => 'GR-DUP',
            'nama' => 'Guru Baru',
            'beban_jp' => 24,
        ])->assertSessionHasErrors('kode');
    }

    public function test_admin_can_crud_kelas(): void
    {
        $admin = $this->admin();
        $guru = Guru::create([
            'kode' => 'GR-KELAS',
            'nama' => 'Wali Kelas',
            'beban_jp' => 24,
        ]);

        $this->actingAs($admin)->post('/kelas', [
            'nama_kelas' => '7 CRUD',
            'tingkat' => '7',
            'guru_pembimbing_id' => $guru->id,
            'kapasitas' => 32,
            'ruangan' => 'R-7',
            'fase' => 'D',
        ])->assertRedirect(route('kelas.index'));

        $kelas = Kelas::where('nama_kelas', '7 CRUD')->firstOrFail();

        $this->actingAs($admin)->put("/kelas/{$kelas->id}", [
            'nama_kelas' => '8 CRUD',
            'tingkat' => '8',
            'guru_pembimbing_id' => $guru->id,
            'kapasitas' => 30,
            'ruangan' => 'R-8',
            'fase' => 'D',
        ])->assertRedirect(route('kelas.index'));

        $this->assertDatabaseHas('kelas', [
            'id' => $kelas->id,
            'nama_kelas' => '8 CRUD',
            'kapasitas' => 30,
        ]);

        $this->actingAs($admin)->delete("/kelas/{$kelas->id}")
            ->assertRedirect(route('kelas.index'));

        $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
    }

    public function test_admin_can_crud_mapel(): void
    {
        $admin = $this->admin();
        $parent = Mapel::create([
            'nama_mapel' => 'Kelompok Umum',
            'jp_per_sesi' => 2,
            'urut' => 1,
        ]);

        $this->actingAs($admin)->post('/mapel', [
            'nama_mapel' => 'Bahasa CRUD',
            'jp_per_sesi' => 2,
            'parent_id' => $parent->id,
            'urut' => 2,
        ])->assertRedirect(route('mapel.index'));

        $mapel = Mapel::where('nama_mapel', 'Bahasa CRUD')->firstOrFail();

        $this->actingAs($admin)->put("/mapel/{$mapel->id}", [
            'nama_mapel' => 'Bahasa CRUD Update',
            'jp_per_sesi' => 3,
            'parent_id' => $parent->id,
            'urut' => 3,
        ])->assertRedirect(route('mapel.index'));

        $this->assertDatabaseHas('mapels', [
            'id' => $mapel->id,
            'nama_mapel' => 'Bahasa CRUD Update',
            'jp_per_sesi' => 3,
        ]);

        $this->actingAs($admin)->delete("/mapel/{$mapel->id}")
            ->assertRedirect(route('mapel.index'));

        $this->assertDatabaseMissing('mapels', ['id' => $mapel->id]);
    }

    public function test_admin_can_crud_jam_pelajaran(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/jam-pelajaran', [
            'hari' => 'Senin',
            'sesi_ke' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '07:40',
        ])->assertRedirect(route('jam-pelajaran.index'));

        $jam = JamPelajaran::where('hari', 'Senin')->where('sesi_ke', 1)->firstOrFail();

        $this->actingAs($admin)->put("/jam-pelajaran/{$jam->id}", [
            'hari' => 'Selasa',
            'sesi_ke' => 2,
            'jam_mulai' => '08:00',
            'jam_selesai' => '08:40',
        ])->assertRedirect(route('jam-pelajaran.index'));

        $this->assertDatabaseHas('jam_pelajaran', [
            'id' => $jam->id,
            'hari' => 'Selasa',
            'sesi_ke' => 2,
        ]);

        $this->actingAs($admin)->delete("/jam-pelajaran/{$jam->id}")
            ->assertRedirect(route('jam-pelajaran.index'));

        $this->assertDatabaseMissing('jam_pelajaran', ['id' => $jam->id]);
    }

    public function test_admin_can_crud_tahun_pelajaran(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/tahun-pelajaran', [
            'kode' => '2030/2031',
            'nama' => 'Tahun Pelajaran 2030/2031',
            'is_aktif' => '1',
        ])->assertRedirect(route('tahun-pelajaran.index'));

        $tahun = TahunPelajaran::where('kode', '2030/2031')->firstOrFail();

        $this->actingAs($admin)->put("/tahun-pelajaran/{$tahun->id}", [
            'kode' => '2031/2032',
            'nama' => 'Tahun Pelajaran 2031/2032',
        ])->assertRedirect(route('tahun-pelajaran.index'));

        $this->assertDatabaseHas('tahun_pelajaran', [
            'id' => $tahun->id,
            'kode' => '2031/2032',
            'nama' => 'Tahun Pelajaran 2031/2032',
            'is_aktif' => false,
        ]);

        $this->actingAs($admin)->delete("/tahun-pelajaran/{$tahun->id}")
            ->assertRedirect(route('tahun-pelajaran.index'));

        $this->assertDatabaseMissing('tahun_pelajaran', ['id' => $tahun->id]);
    }

    public function test_admin_can_crud_kategori_sarana(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/kategori-sarana', [
            'nama_kategori' => 'Elektronik CRUD',
        ])->assertRedirect(route('kategori-sarana.index'));

        $kategori = KategoriSarana::where('nama_kategori', 'Elektronik CRUD')->firstOrFail();

        $this->actingAs($admin)->put("/kategori-sarana/{$kategori->id}", [
            'nama_kategori' => 'Inventaris CRUD',
        ])->assertRedirect(route('kategori-sarana.index'));

        $this->assertDatabaseHas('kategori_sarana', [
            'id' => $kategori->id,
            'nama_kategori' => 'Inventaris CRUD',
        ]);

        $this->actingAs($admin)->delete("/kategori-sarana/{$kategori->id}")
            ->assertRedirect(route('kategori-sarana.index'));

        $this->assertDatabaseMissing('kategori_sarana', ['id' => $kategori->id]);
    }

    public function test_master_data_unique_validation_is_enforced(): void
    {
        $admin = $this->admin();

        Kelas::create(['nama_kelas' => '7 DUP', 'tingkat' => '7']);
        Mapel::create(['nama_mapel' => 'Mapel DUP', 'jp_per_sesi' => 2]);
        TahunPelajaran::create(['kode' => '2040/2041', 'nama' => 'TP DUP']);
        KategoriSarana::create(['nama_kategori' => 'Kategori DUP']);

        $this->actingAs($admin)->post('/kelas', [
            'nama_kelas' => '7 DUP',
            'tingkat' => '7',
        ])->assertSessionHasErrors('nama_kelas');

        $this->actingAs($admin)->post('/mapel', [
            'nama_mapel' => 'Mapel DUP',
            'jp_per_sesi' => 2,
        ])->assertSessionHasErrors('nama_mapel');

        $this->actingAs($admin)->post('/tahun-pelajaran', [
            'kode' => '2040/2041',
            'nama' => 'TP Baru',
        ])->assertSessionHasErrors('kode');

        $this->actingAs($admin)->post('/kategori-sarana', [
            'nama_kategori' => 'Kategori DUP',
        ])->assertSessionHasErrors('nama_kategori');
    }

    public function test_master_data_index_search_filters_results(): void
    {
        $admin = $this->admin();

        Guru::create([
            'kode' => 'GR-SEARCH-001',
            'nama' => 'Guru Dicari',
            'beban_jp' => 24,
        ]);
        Guru::create([
            'kode' => 'GR-OTHER-001',
            'nama' => 'Guru Lain',
            'beban_jp' => 24,
        ]);

        $response = $this->actingAs($admin)->get('/guru?search=Dicari');

        $response->assertOk();
        $response->assertSee('Guru Dicari');
        $response->assertDontSee('Guru Lain');
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
