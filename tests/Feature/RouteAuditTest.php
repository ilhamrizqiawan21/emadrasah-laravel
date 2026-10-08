<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\SuratMasuk;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->first();
    }

    public function test_all_admin_get_routes(): void
    {
        $routes = [
            'dashboard' => '/dashboard',
            'guru.index' => '/guru',
            'guru.create' => '/guru/create',
            'kelas.index' => '/kelas',
            'kelas.create' => '/kelas/create',
            'mapel.index' => '/mapel',
            'mapel.create' => '/mapel/create',
            'jam-pelajaran.index' => '/jam-pelajaran',
            'jam-pelajaran.create' => '/jam-pelajaran/create',
            'tahun-pelajaran.index' => '/tahun-pelajaran',
            'tahun-pelajaran.create' => '/tahun-pelajaran/create',
            'siswa.index' => '/siswa',
            'siswa.create' => '/siswa/create',
            'buku-induk.index' => '/buku-induk',
            'buku-induk.create' => '/buku-induk/create',
            'raport.index' => '/raport',
            'jadwal.index' => '/jadwal',
            'jadwal.create' => '/jadwal/create',
            'jadwal.grid' => '/jadwal/grid',
            'absensi.index' => '/absensi',
            'absensi.rekap' => '/absensi/rekap',
            'absensi.rekap.export' => '/absensi/rekap/export',
            'arsip-akademik.index' => '/arsip-akademik',
            'surat-masuk.index' => '/surat-masuk',
            'surat-masuk.create' => '/surat-masuk/create',
            'surat-keluar.index' => '/surat-keluar',
            'surat-keluar.create' => '/surat-keluar/create',
            'template-surat.index' => '/template-surat',
            'template-surat.create' => '/template-surat/create',
            'tasks.index' => '/tasks',
            'tasks.index.list' => '/tasks?view=list',
            'tasks.create' => '/tasks/create',
            'sarana.index' => '/sarana',
            'sarana.create' => '/sarana/create',
            'kategori-sarana.index' => '/kategori-sarana',
            'kategori-sarana.create' => '/kategori-sarana/create',
            'users.index' => '/users',
            'users.create' => '/users/create',
            'pengaturan.edit' => '/pengaturan',
        ];

        // Model parameter routes
        $siswa = Siswa::first();
        if ($siswa) {
            $routes['siswa.show'] = '/siswa/'.$siswa->id;
            $routes['siswa.edit'] = '/siswa/'.$siswa->id.'/edit';
            $routes['buku-induk.show'] = '/buku-induk/'.$siswa->id;
            $routes['buku-induk.edit'] = '/buku-induk/'.$siswa->id.'/edit';
            $routes['buku-induk.export-pdf'] = '/buku-induk/'.$siswa->id.'/export-pdf';
            $routes['raport.manage'] = '/raport/'.$siswa->id.'/manage';
            $routes['raport.export-pdf'] = '/raport/'.$siswa->id.'/export-pdf';
        }

        $guru = Guru::first();
        if ($guru) {
            $routes['guru.edit'] = '/guru/'.$guru->id.'/edit';
        }

        $kelas = Kelas::first();
        if ($kelas) {
            $routes['kelas.edit'] = '/kelas/'.$kelas->id.'/edit';
        }

        $task = Task::first();
        if ($task) {
            $routes['tasks.show'] = '/tasks/'.$task->id;
            $routes['tasks.edit'] = '/tasks/'.$task->id.'/edit';
        }

        $suratMasuk = SuratMasuk::first();
        if ($suratMasuk) {
            $routes['surat-masuk.show'] = '/surat-masuk/'.$suratMasuk->id;
            $routes['surat-masuk.edit'] = '/surat-masuk/'.$suratMasuk->id.'/edit';
        }

        $sarana = SaranaPrasarana::first();
        if ($sarana) {
            $routes['sarana.edit'] = '/sarana/'.$sarana->id.'/edit';
            $routes['sarana.peminjaman'] = '/sarana/'.$sarana->id.'/peminjaman';
            $routes['sarana.pemeliharaan'] = '/sarana/'.$sarana->id.'/pemeliharaan';
        }

        $errors = [];

        foreach ($routes as $name => $url) {
            try {
                $response = $this->actingAs($this->admin)->get($url);
                $status = $response->status();
                if ($status !== 200) {
                    $errors[] = "Route [$name] ($url) returned status $status";
                }
            } catch (\Throwable $e) {
                $errors[] = "Route [$name] ($url) threw Exception: ".$e->getMessage();
            }
        }

        $this->assertEmpty($errors, "Failed routes:\n".implode("\n", $errors));
    }

    public function test_operator_access_and_restrictions(): void
    {
        $operator = User::where('role', 'operator')->first();
        $this->assertNotNull($operator);

        // Operator can access dashboard, guru, siswa
        $this->actingAs($operator)->get('/dashboard')->assertStatus(200);
        $this->actingAs($operator)->get('/guru')->assertStatus(200);
        $this->actingAs($operator)->get('/siswa')->assertStatus(200);

        // Operator is forbidden from user management
        $this->actingAs($operator)->get('/users')->assertStatus(403);
    }

    public function test_guru_access_and_restrictions(): void
    {
        $guru = User::where('role', 'guru')->first();
        $this->assertNotNull($guru);

        // Guru can access dashboard and absensi
        $this->actingAs($guru)->get('/dashboard')->assertStatus(200);
        $this->actingAs($guru)->get('/absensi')->assertStatus(200);

        // Guru is forbidden from master data & user management
        $this->actingAs($guru)->get('/guru')->assertStatus(403);
        $this->actingAs($guru)->get('/siswa')->assertStatus(403);
        $this->actingAs($guru)->get('/users')->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/guru')->assertRedirect('/login');
    }
}
