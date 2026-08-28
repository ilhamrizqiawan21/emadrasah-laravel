<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_siswa_forms_include_masks_and_error_summary_remains_available(): void
    {
        $admin = $this->admin();
        Kelas::create(['nama_kelas' => '7A', 'tingkat' => '7']);
        TahunPelajaran::create(['kode' => '2026/2027', 'nama' => 'TP 2026/2027']);

        $response = $this->actingAs($admin)->get('/siswa/create');

        $response->assertOk();
        $response->assertSee('data-mask="nisn"', false);
        $response->assertSee('data-mask="nik"', false);
        $response->assertSee('data-mask="phone"', false);

        $this->actingAs($admin)->from('/siswa/create')->post('/siswa', [])
            ->assertRedirect('/siswa/create')
            ->assertSessionHasErrors(['nis', 'nama_lengkap', 'kelas_id', 'jenis_kelamin', 'status']);
    }

    public function test_year_and_time_forms_include_input_masks(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/tahun-pelajaran/create')
            ->assertOk()
            ->assertSee('data-mask="year-code"', false);

        $this->actingAs($admin)->get('/jam-pelajaran/create')
            ->assertOk()
            ->assertSee('data-mask="time"', false);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
