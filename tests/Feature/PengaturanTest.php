<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\Madrasah;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaturanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('local');
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    /** @param array<string,mixed> $override */
    private function payload(array $override = []): array
    {
        return array_merge([
            'nama' => 'MA Nurul Huda Contoh',
            'nama_pendek' => 'MA Nurul Huda',
            'nama_lengkap' => 'Madrasah Aliyah Nurul Huda',
            'warna_utama' => '#047857',
        ], $override);
    }

    private function save(array $override = [])
    {
        return $this->actingAs($this->admin())->put('/pengaturan', $this->payload($override));
    }

    private function stored(string $key): ?string
    {
        return Setting::where('key', $key)->value('value');
    }

    // ── Akses ─────────────────────────────────────────────────────────────

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pengaturan')->assertRedirect('/login');
        $this->put('/pengaturan', $this->payload())->assertRedirect('/login');
    }

    public function test_only_admin_can_open_or_change_settings(): void
    {
        foreach (['operator', 'guru'] as $role) {
            $user = User::where('role', $role)->firstOrFail();

            $this->actingAs($user)->get('/pengaturan')->assertForbidden();
            $this->actingAs($user)->put('/pengaturan', $this->payload())->assertForbidden();
        }

        $this->assertDatabaseMissing('settings', ['key' => 'nama']);
    }

    public function test_admin_can_open_settings_page(): void
    {
        $this->actingAs($this->admin())->get('/pengaturan')
            ->assertOk()
            ->assertSee('Identitas Madrasah')
            ->assertSee('Warna utama');
    }

    public function test_sidebar_shows_settings_link_for_admin_only(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertSee(route('pengaturan.edit'), false);

        $operator = User::where('role', 'operator')->firstOrFail();
        $this->actingAs($operator)->get('/dashboard')->assertDontSee(route('pengaturan.edit'), false);
    }

    // ── Nilai ─────────────────────────────────────────────────────────────

    public function test_defaults_come_from_config_until_settings_are_saved(): void
    {
        config(['madrasah.nama' => 'Nama Dari Config']);

        $this->get('/login')->assertSee('Nama Dari Config');
    }

    public function test_admin_can_update_identity_and_it_appears_everywhere(): void
    {
        $this->save([
            'npsn' => '12345678',
            'alamat' => 'Jl. Contoh No. 1',
            'telepon' => '+62 22 1234567',
            'email' => 'info@contoh.sch.id',
            'website' => 'https://contoh.sch.id',
            'kepala_nama' => 'Drs. Contoh, M.Pd.',
            'kepala_nip' => '197001011999031001',
        ])->assertRedirect(route('pengaturan.edit'))->assertSessionHas('success');

        $this->assertDatabaseHas('settings', ['key' => 'nama', 'value' => 'MA Nurul Huda Contoh']);
        $this->assertDatabaseHas('settings', ['key' => 'npsn', 'value' => '12345678']);

        // Halaman login (tanpa login) dan dashboard memakai nama baru.
        $this->get('/login')->assertSee('MA Nurul Huda Contoh');
        $this->actingAs($this->admin())->get('/dashboard')
            ->assertSee('MA Nurul Huda Contoh')
            ->assertSee('MA Nurul Huda');
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs($this->admin())->put('/pengaturan', [])
            ->assertSessionHasErrors(['nama', 'nama_pendek', 'nama_lengkap', 'warna_utama']);
    }

    public function test_invalid_optional_fields_are_rejected(): void
    {
        $this->save([
            'npsn' => '123',
            'email' => 'bukan-email',
            'website' => 'ftp://contoh.id',
            'telepon' => 'abc',
        ])->assertSessionHasErrors(['npsn', 'email', 'website', 'telepon']);

        $this->assertDatabaseMissing('settings', ['key' => 'nama']);
    }

    public function test_blank_optional_fields_are_stored_as_null(): void
    {
        $this->save(['alamat' => '   ', 'telepon' => ''])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'alamat', 'value' => null]);
    }

    public function test_text_is_escaped_in_views(): void
    {
        $this->save(['nama' => '<script>alert(1)</script>'])->assertSessionHasNoErrors();

        $this->get('/login')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    // ── Warna ─────────────────────────────────────────────────────────────

    public function test_colour_must_be_a_dark_enough_hex(): void
    {
        $this->save(['warna_utama' => '#facc15'])->assertSessionHasErrors('warna_utama');
        $this->save(['warna_utama' => 'red'])->assertSessionHasErrors('warna_utama');
        $this->save(['warna_utama' => '#047857; } body { display:none'])->assertSessionHasErrors('warna_utama');

        $this->assertDatabaseMissing('settings', ['key' => 'warna_utama']);
    }

    public function test_custom_colour_overrides_theme_and_default_does_not(): void
    {
        $this->save(['warna_utama' => '#1d4ed8'])->assertSessionHasNoErrors();

        $this->get('/login')
            ->assertSee('id="em-theme"', false)
            ->assertSee('--em-green-700:#1d4ed8', false);

        $this->save(['warna_utama' => '#047857'])->assertSessionHasNoErrors();

        $this->get('/login')->assertDontSee('id="em-theme"', false);
    }

    // ── Logo & favicon ────────────────────────────────────────────────────

    public function test_uploaded_logo_is_stored_and_served_publicly(): void
    {
        $this->save(['logo' => UploadedFile::fake()->image('logo.png', 200, 200)])->assertSessionHasNoErrors();

        $path = $this->stored('logo');
        $this->assertStringStartsWith('branding/', $path);
        Storage::disk('local')->assertExists($path);

        // Tanpa login: halaman login butuh logo ini.
        $response = $this->get(route('branding.show', 'logo'))
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        // Header khas controller ini (SecurityHeaders tidak memasangnya): gambar tidak boleh
        // dieksekusi sebagai halaman, dan URL ber-versi boleh di-cache lama.
        $this->assertStringContainsString('sandbox', $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('immutable', $response->headers->get('Cache-Control'));
    }

    public function test_replacing_logo_removes_the_old_file(): void
    {
        $this->save(['logo' => UploadedFile::fake()->image('a.png', 200, 200)]);
        $old = $this->stored('logo');

        $this->save(['logo' => UploadedFile::fake()->image('b.png', 200, 200)]);
        $new = $this->stored('logo');

        $this->assertNotSame($old, $new);
        Storage::disk('local')->assertMissing($old);
        Storage::disk('local')->assertExists($new);
    }

    public function test_logo_can_be_reset_to_default(): void
    {
        $this->save(['logo' => UploadedFile::fake()->image('a.png', 200, 200)]);
        $path = $this->stored('logo');

        $this->save(['hapus_logo' => '1'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'logo', 'value' => null]);
        Storage::disk('local')->assertMissing($path);
        $this->get(route('branding.show', 'logo'))->assertOk();
    }

    public function test_invalid_logo_files_are_rejected(): void
    {
        $bad = [
            'teks' => UploadedFile::fake()->createWithContent('logo.png', 'bukan gambar'),
            'svg' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"></svg>'),
            'php' => UploadedFile::fake()->createWithContent('logo.php', '<?php echo 1;'),
            'kecil' => UploadedFile::fake()->image('kecil.png', 10, 10),
            'besar' => UploadedFile::fake()->image('besar.png', 200, 200)->size(2048),
        ];

        foreach ($bad as $name => $file) {
            $this->save(['logo' => $file])->assertSessionHasErrors('logo', "logo {$name} harus ditolak");
        }

        $this->assertDatabaseMissing('settings', ['key' => 'logo']);
        $this->assertSame([], Storage::disk('local')->allFiles('branding'));
    }

    public function test_invalid_favicon_files_are_rejected(): void
    {
        $this->save(['favicon' => UploadedFile::fake()->createWithContent('f.php', '<?php echo 1;')])
            ->assertSessionHasErrors('favicon');
        $this->save(['favicon' => UploadedFile::fake()->createWithContent('f.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>')])
            ->assertSessionHasErrors('favicon');
    }

    public function test_valid_favicon_is_stored_and_takes_priority_over_logo(): void
    {
        $this->save([
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
            'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
        ])->assertSessionHasNoErrors();

        $favicon = $this->stored('favicon');
        $this->assertStringStartsWith('branding/', $favicon);

        $served = $this->get(route('branding.show', 'favicon'))->assertOk()->streamedContent();
        $this->assertSame(Storage::disk('local')->get($favicon), $served);
        $this->assertNotSame(Storage::disk('local')->get($this->stored('logo')), $served);
    }

    public function test_favicon_falls_back_to_logo_then_to_default(): void
    {
        // Belum ada apa pun: favicon bawaan.
        $this->get(route('branding.show', 'favicon'))->assertOk();

        $this->save(['logo' => UploadedFile::fake()->image('logo.png', 200, 200)]);
        $logoPath = $this->stored('logo');

        $served = $this->get(route('branding.show', 'favicon'))->assertOk()->streamedContent();
        $this->assertSame(Storage::disk('local')->get($logoPath), $served);
    }

    public function test_default_logo_url_changes_when_the_default_file_changes(): void
    {
        // URL bawaan di-cache "immutable" oleh browser; ia harus berganti saat berkas bawaan diganti.
        $file = public_path('images/logo.png');
        $original = filemtime($file);

        try {
            $before = app(Madrasah::class)->logoUrl();

            touch($file, $original + 120);
            clearstatcache();
            $after = app(Madrasah::class)->logoUrl();
        } finally {
            touch($file, $original);
            clearstatcache();
        }

        $this->assertNotSame($before, $after);
    }

    public function test_branding_route_only_serves_logo_and_favicon(): void
    {
        $this->get('/branding/passwd')->assertNotFound();
        $this->get('/branding/..%2F..%2F.env')->assertNotFound();
    }

    public function test_logo_data_uri_is_available_for_pdf(): void
    {
        $this->assertStringStartsWith('data:image/png;base64,', app(Madrasah::class)->logoDataUri());

        $this->save(['logo' => UploadedFile::fake()->image('logo.jpg', 200, 200)]);
        $this->assertStringStartsWith('data:image/jpeg;base64,', app(Madrasah::class)->logoDataUri());
    }
}
