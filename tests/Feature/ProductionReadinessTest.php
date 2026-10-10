<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    // ── Pembatas login ────────────────────────────────────────────────────

    public function test_login_is_throttled_per_email_not_for_the_whole_school(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Lima kesalahan untuk satu email dari satu IP...
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'guru@madrasah.id', 'password' => 'salah'])->assertStatus(302);
        }
        $this->post('/login', ['email' => 'guru@madrasah.id', 'password' => 'salah'])->assertStatus(429);

        // ...tidak boleh mengunci pengguna lain di jaringan yang sama.
        $this->post('/login', ['email' => 'admin@madrasah.id', 'password' => 'admin123'])
            ->assertRedirect('/dashboard');
    }

    public function test_login_throttle_ignores_email_letter_case(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'Guru@Madrasah.ID', 'password' => 'salah']);
        }

        $this->post('/login', ['email' => 'guru@madrasah.id', 'password' => 'salah'])->assertStatus(429);
    }

    // ── Proxy tepercaya ───────────────────────────────────────────────────

    public function test_forwarded_headers_are_ignored_unless_proxies_are_trusted(): void
    {
        $this->withHeaders(['X-Forwarded-Proto' => 'https'])->get('/login')
            ->assertSee('action="http://', false)
            ->assertDontSee('action="https://', false);
    }

    public function test_forwarded_headers_are_honoured_when_proxies_are_trusted(): void
    {
        config(['trustedproxy.proxies' => '*']);

        $this->withHeaders(['X-Forwarded-Proto' => 'https'])->get('/login')
            ->assertSee('action="https://', false);
    }

    // ── Kebijakan kata sandi ──────────────────────────────────────────────

    public function test_user_passwords_must_be_at_least_ten_characters(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $user = ['name' => 'Guru Baru', 'email' => 'baru@contoh.sch.id', 'role' => 'guru'];

        $this->actingAs($admin)->post('/users', $user + ['password' => 'abc123456'])->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'baru@contoh.sch.id']);

        $this->actingAs($admin)->post('/users', $user + ['password' => 'abc1234567'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'baru@contoh.sch.id']);

        $created = User::where('email', 'baru@contoh.sch.id')->firstOrFail();
        $this->actingAs($admin)->put("/users/{$created->id}", ['name' => 'Guru Baru', 'email' => 'baru@contoh.sch.id', 'role' => 'guru', 'password' => 'pendek'])
            ->assertSessionHasErrors('password');
    }

    // ── Seeder & akun demo ────────────────────────────────────────────────

    public function test_seeder_does_not_create_demo_accounts_or_data_in_production(): void
    {
        $this->app['env'] = 'production';

        // db:seed meminta konfirmasi di produksi, jadi --force.
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertSame(0, User::count(), 'Tidak boleh ada akun demo di produksi');
        $this->assertSame(0, DB::table('siswa')->count());
        $this->assertSame(0, DB::table('gurus')->count());
        // Data dasar tetap dibuat agar aplikasi bisa dipakai.
        $this->assertGreaterThan(0, DB::table('kategori_sarana')->count());
        $this->assertGreaterThan(0, DB::table('tahun_pelajaran')->count());
    }

    public function test_seeder_still_creates_demo_accounts_outside_production(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@madrasah.id', 'role' => 'admin']);
    }

    // ── madrasah:install ──────────────────────────────────────────────────

    public function test_install_creates_first_admin_with_given_credentials(): void
    {
        $this->artisan('madrasah:install', ['--name' => 'Kepala TU', '--email' => 'tu@contoh.sch.id', '--password' => 'Rahasia-Panjang-9'])
            ->expectsOutputToContain('Admin dibuat')
            ->assertSuccessful();

        $admin = User::where('email', 'tu@contoh.sch.id')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue((bool) $admin->is_active);
        $this->assertTrue(Hash::check('Rahasia-Panjang-9', $admin->password));
    }

    public function test_install_generates_a_strong_password_when_none_is_given(): void
    {
        $this->artisan('madrasah:install', ['--name' => 'Admin', '--email' => 'a@contoh.sch.id', '--no-interaction' => true])
            ->expectsOutputToContain('Kata sandi (hanya ditampilkan sekali')
            ->assertSuccessful();

        $admin = User::where('email', 'a@contoh.sch.id')->firstOrFail();
        $this->assertFalse(Hash::check('admin123', $admin->password));
    }

    public function test_install_rejects_weak_passwords_and_bad_emails(): void
    {
        $this->artisan('madrasah:install', ['--name' => 'A', '--email' => 'a@contoh.sch.id', '--password' => 'admin123'])->assertFailed();
        $this->artisan('madrasah:install', ['--name' => 'A', '--email' => 'a@contoh.sch.id', '--password' => 'pendek'])->assertFailed();
        $this->artisan('madrasah:install', ['--name' => 'A', '--email' => 'bukan-email', '--password' => 'Rahasia-Panjang-9'])->assertFailed();

        $this->assertSame(0, User::count());
    }

    public function test_install_refuses_when_an_admin_already_exists(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->artisan('madrasah:install', ['--name' => 'B', '--email' => 'b@contoh.sch.id', '--password' => 'Rahasia-Panjang-9'])
            ->expectsOutputToContain('Sudah ada akun admin')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'b@contoh.sch.id']);
    }
}
