<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Menelusuri SELURUH tabel route (bukan daftar tulisan tangan), sehingga route baru otomatis
 * ikut teruji. Ekspektasi akses per role ditulis di bawah secara mandiri dan sengaja tidak
 * diturunkan dari middleware di routes/web.php.
 */
class RouteMatrixTest extends TestCase
{
    use RefreshDatabase;

    /** Route yang memang terbuka untuk tamu. */
    private const PUBLIC_NAMES = ['login', 'branding.show'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /** Akses yang seharusnya dimiliki tiap role, tertulis mandiri. */
    private function allowed(string $role, string $name): bool
    {
        $adminOnly = str_starts_with($name, 'users.') || str_starts_with($name, 'pengaturan.');

        return match ($role) {
            'admin' => true,
            'operator' => ! $adminOnly,
            'guru' => str_starts_with($name, 'absensi.') || in_array($name, ['dashboard', 'logout'], true),
            default => false,
        };
    }

    /** @return list<array{route: LaravelRoute, method: string, uri: string, missing: string, name: string}> */
    private function protectedRoutes(): array
    {
        $found = [];

        foreach (Route::getRoutes()->getRoutes() as $route) {
            $uri = $route->uri();
            $name = (string) $route->getName();

            if (in_array($uri, ['up', 'login'], true) || str_starts_with($uri, 'storage/') || in_array($name, self::PUBLIC_NAMES, true)) {
                continue;
            }

            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                $found[] = [
                    'route' => $route,
                    'method' => $method,
                    // Parameter diganti 1: id contoh dari seeder, atau 404 bila tidak ada.
                    'uri' => '/'.ltrim(preg_replace('/\{[^}]+\}/', '1', $uri), '/'),
                    // Id yang pasti tidak ada, agar tes akses tidak bergantung pada isi/auto-increment database.
                    'missing' => '/'.ltrim(preg_replace('/\{[^}]+\}/', '999999', $uri), '/'),
                    'name' => $name,
                ];
            }
        }

        return $found;
    }

    private function user(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }

    public function test_the_matrix_actually_covers_the_application(): void
    {
        $routes = $this->protectedRoutes();

        $this->assertGreaterThan(120, count($routes), 'Tabel route seharusnya memuat ratusan kombinasi metode');
        $this->assertNotEmpty(array_filter($routes, fn ($r) => $r['method'] === 'DELETE'));
        $this->assertNotEmpty(array_filter($routes, fn ($r) => $r['method'] === 'POST'));
    }

    public function test_guests_are_redirected_to_login_on_every_protected_route(): void
    {
        $failures = [];

        foreach ($this->protectedRoutes() as $r) {
            $response = $this->call($r['method'], $r['uri']);

            if ($response->status() !== 302 || ! str_ends_with((string) $response->headers->get('Location'), '/login')) {
                $failures[] = "{$r['method']} {$r['uri']} -> {$response->status()}";
            }
        }

        $this->assertSame([], $failures, "Route yang tidak melindungi tamu:\n".implode("\n", $failures));
    }

    public function test_admin_pages_never_fail_with_server_errors_or_forbidden(): void
    {
        $this->actingAs($this->user('admin'));
        $failures = [];

        foreach ($this->protectedRoutes() as $r) {
            if ($r['method'] !== 'GET' || $r['name'] === 'logout') {
                continue;
            }

            $status = $this->call('GET', $r['uri'])->status();

            if ($status >= 500 || $status === 403) {
                $failures[] = "GET {$r['uri']} -> {$status}";
            }
        }

        $this->assertSame([], $failures, "Halaman admin bermasalah:\n".implode("\n", $failures));
    }

    public function test_operator_is_blocked_exactly_from_admin_only_routes(): void
    {
        $this->assertRoleBoundary('operator');
    }

    public function test_guru_is_limited_to_attendance_and_dashboard(): void
    {
        $this->assertRoleBoundary('guru');
    }

    /**
     * Route terlarang harus 403 untuk SEMUA metode (middleware berhenti sebelum controller,
     * jadi aman dikirim tanpa data). Route yang diizinkan hanya dicoba lewat GET agar tidak
     * mengubah data.
     */
    private function assertRoleBoundary(string $role): void
    {
        $this->actingAs($this->user($role));
        $failures = [];

        foreach ($this->protectedRoutes() as $r) {
            if ($r['name'] === 'logout') {
                continue;
            }

            // "/" hanya mengalihkan ke dashboard dan sah untuk semua role yang sudah login.
            if ($r['uri'] === '/') {
                $this->get('/')->assertRedirect(route('dashboard'));

                continue;
            }

            if ($this->allowed($role, $r['name'])) {
                if ($r['method'] !== 'GET') {
                    continue;
                }
                $status = $this->call('GET', $r['uri'])->status();
                if ($status === 403 || $status >= 500) {
                    $failures[] = "[{$role}] seharusnya boleh: GET {$r['uri']} -> {$status}";
                }

                continue;
            }

            // Id yang tidak ada harus tetap 403 (bukan 404): role diperiksa sebelum record dicari,
            // sehingga pengguna tanpa hak akses tidak bisa menebak id yang tersimpan.
            foreach (['uri', 'missing'] as $kind) {
                $status = $this->call($r['method'], $r[$kind])->status();
                if ($status !== 403) {
                    $failures[] = "[{$role}] seharusnya 403: {$r['method']} {$r[$kind]} ({$r['name']}) -> {$status}";
                }
            }
        }

        $this->assertSame([], $failures, implode("\n", $failures));
    }
}
