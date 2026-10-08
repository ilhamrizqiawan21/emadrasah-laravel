<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\KategoriSarana;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\TahunPelajaran;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TemplateSurat;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Halaman daftar harus tetap ringan ketika datanya ribuan: setiap halaman membatasi jumlah baris,
 * menampilkan tautan halaman, dan halaman berikutnya berisi data yang berbeda.
 */
class PaginationTest extends TestCase
{
    use RefreshDatabase;

    /** uri => [model, ukuran halaman yang diharapkan] */
    private static function pages(): array
    {
        return [
            '/siswa' => [Siswa::class, 15],
            '/buku-induk' => [Siswa::class, 20],
            '/raport' => [Siswa::class, 15],
            '/guru' => [Guru::class, 10],
            '/kelas' => [Kelas::class, 10],
            '/mapel' => [Mapel::class, 10],
            '/jam-pelajaran' => [JamPelajaran::class, 20],
            '/tahun-pelajaran' => [TahunPelajaran::class, 20],
            '/surat-masuk' => [SuratMasuk::class, 15],
            '/surat-keluar' => [SuratKeluar::class, 15],
            '/template-surat' => [TemplateSurat::class, 10],
            '/sarana' => [SaranaPrasarana::class, 15],
            '/kategori-sarana' => [KategoriSarana::class, 10],
            '/tasks?view=list' => [Task::class, 15],
            '/users' => [User::class, 10],
        ];
    }

    private function grow(string $model, int $target): void
    {
        $columns = Schema::getColumnListing((new $model)->getTable());
        $unique = array_intersect(['nama_kelas', 'nis', 'nisn', 'kode', 'nomor_agenda', 'nomor_surat', 'kode_sarana', 'nama_kategori', 'nip', 'email', 'nama_mapel', 'nama', 'judul'], $columns);
        $source = $model::all();

        if ($source->isEmpty()) {
            $this->fail("{$model} tidak punya data contoh untuk diperbanyak");
        }

        for ($i = 0; $model::count() < $target; $i++) {
            $copy = $source[$i % $source->count()]->replicate();
            foreach ($unique as $column) {
                $copy->{$column} = ($copy->{$column} === null) ? null : Str::random(8); // pendek: MySQL menolak teks lebih panjang dari kolom
            }
            if (in_array('nik', $columns, true)) {
                $copy->nik = null;
            }
            $copy->save();
        }
    }

    /** Teks seluruh baris tabel dari halaman 1 sampai terakhir. */
    private function allRows(string $uri, int $maxPages): array
    {
        $glue = str_contains($uri, '?') ? '&' : '?';
        $all = [];

        for ($page = 1; $page <= $maxPages; $page++) {
            $html = $this->get($uri.$glue."page={$page}")->assertOk()->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
            foreach ((new \DOMXPath($dom))->query('//main//table/tbody/tr') as $tr) {
                $all[] = preg_replace('/\s+/', ' ', trim($tr->textContent));
            }
        }

        return $all;
    }

    /** @return array{rows:int, html:string} */
    private function rows(string $uri): array
    {
        $response = $this->get($uri)->assertOk();
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$response->getContent());
        $xpath = new \DOMXPath($dom);

        return ['rows' => $xpath->query('//main//table/tbody/tr')->length, 'html' => $response->getContent()];
    }

    public function test_every_list_page_limits_rows_links_to_the_next_page_and_serves_it(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'admin')->firstOrFail());

        foreach (self::pages() as [$model, $perPage]) {
            $this->grow($model, $perPage * 2 + 1);
        }

        $problems = [];
        foreach (self::pages() as $uri => [$model, $perPage]) {
            $first = $this->rows($uri);
            $glue = str_contains($uri, '?') ? '&' : '?';
            $second = $this->rows($uri.$glue.'page=2');

            if ($first['rows'] > $perPage) {
                $problems[] = "{$uri}: halaman 1 menampilkan {$first['rows']} baris (maks {$perPage})";
            }
            if (! str_contains($first['html'], 'page=2')) {
                $problems[] = "{$uri}: tidak ada tautan ke halaman 2";
            }
            if ($second['rows'] < 1) {
                $problems[] = "{$uri}: halaman 2 kosong padahal data melebihi satu halaman";
            }
            if ($first['html'] === $second['html']) {
                $problems[] = "{$uri}: halaman 2 sama dengan halaman 1";
            }

            // Urutan harus stabil: tidak ada baris ganda atau hilang ketika berpindah halaman
            // (MySQL boleh menyusun ulang baris dengan nilai urut sama di setiap kueri).
            $total = $model::count();
            $rows = $this->allRows($uri, (int) ceil($total / $perPage));
            if (count($rows) !== $total) {
                $problems[] = "{$uri}: total baris di semua halaman ".count($rows)." != jumlah data {$total}";
            }
            if (count(array_unique($rows)) !== count($rows)) {
                $problems[] = "{$uri}: ada baris yang tampil lebih dari sekali antar halaman";
            }
        }

        $this->assertSame([], $problems, "Masalah pagination:\n".implode("\n", $problems));
    }

    public function test_search_filter_is_kept_when_moving_to_the_next_page(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'admin')->firstOrFail());
        $this->grow(Siswa::class, 40);
        Siswa::query()->update(['nama_lengkap' => 'Cari Siswa Uji']);

        $html = $this->get('/siswa?search=Cari')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/page=2[^"]*search=Cari|search=Cari[^"]*page=2/', html_entity_decode($html), 'Tautan halaman harus membawa kata pencarian');
    }

    // ── Pencarian siswa (pengganti dropdown berisi semua siswa) ─────────────

    public function test_student_search_needs_two_characters_and_returns_at_most_ten_matches(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
        $this->grow(Siswa::class, 60);
        Siswa::query()->update(['nama_lengkap' => 'Siswa Pencarian']);

        $this->getJson('/cari/siswa?q=S')->assertOk()->assertExactJson([]);
        $this->getJson('/cari/siswa')->assertOk()->assertExactJson([]);

        $result = $this->getJson('/cari/siswa?q=Pencarian')->assertOk();
        $this->assertCount(10, $result->json(), 'Hasil harus dibatasi 10 walau yang cocok 60');
        $this->assertSame(['id', 'label'], array_keys($result->json()[0]));
        $this->assertStringContainsString('Siswa Pencarian (NIS: ', $result->json()[0]['label']);
    }

    public function test_student_search_matches_by_nis_and_treats_wildcards_literally(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
        $siswa = Siswa::firstOrFail();

        $this->getJson('/cari/siswa?q='.substr($siswa->nis, 0, 5))->assertOk()
            ->assertJsonFragment(['id' => $siswa->id]);

        // "%%" bukan "cocokkan semuanya": harus dianggap teks biasa dan tidak menemukan apa pun.
        $this->getJson('/cari/siswa?q=%25%25')->assertOk()->assertExactJson([]);
        $this->getJson('/cari/siswa?q=__')->assertOk()->assertExactJson([]);
    }

    public function test_student_search_finds_names_that_literally_contain_wildcard_characters(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'operator')->firstOrFail());
        Siswa::query()->orderBy('id')->first()->update(['nama_lengkap' => 'Diskon 100% Lulus']);

        $found = $this->getJson('/cari/siswa?q='.urlencode('100%'))->assertOk()->json();

        $this->assertCount(1, $found, 'Karakter % di kata pencarian harus dicari sebagai teks biasa');
        $this->assertStringContainsString('Diskon 100% Lulus', $found[0]['label']);
    }

    public function test_student_search_is_not_available_to_teachers_or_guests(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->getJson('/cari/siswa?q=Aisyah')->assertUnauthorized();
        $this->actingAs(User::where('role', 'guru')->firstOrFail())->getJson('/cari/siswa?q=Aisyah')->assertForbidden();
    }

    public function test_outgoing_letter_form_no_longer_embeds_every_student(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'admin')->firstOrFail());
        $this->grow(Siswa::class, 300);
        Siswa::query()->update(['nama_lengkap' => 'Nama Siswa Rahasia Daftar']);

        $html = $this->get('/surat-keluar/create')->assertOk()->getContent();

        $this->assertStringNotContainsString('Nama Siswa Rahasia Daftar', $html, 'Form tidak boleh memuat semua siswa');
        $this->assertStringContainsString('id="cariSiswa"', $html);
        $this->assertLessThan(120_000, strlen($html), 'Ukuran halaman tidak boleh tumbuh seiring jumlah siswa');
    }

    // ── Log aktivitas tugas ───────────────────────────────────────────────

    public function test_task_activity_log_is_paginated(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin);
        $task = Task::firstOrFail();

        for ($i = 1; $i <= 25; $i++) {
            TaskLog::forceCreate(['task_id' => $task->id, 'user_id' => $admin->id, 'action' => "Aktivitas ke-{$i}", 'keterangan' => 'uji']);
        }

        $seen = [];
        $htmlByPage = [];
        foreach ([1, 2, 3] as $page) {
            $html = $this->get("/tasks/{$task->id}?page={$page}")->assertOk()->getContent();
            $htmlByPage[$page] = $html;
            $this->assertLessThanOrEqual(10, preg_match_all('/<h6 class="mb-1">/', $html), "Halaman {$page} log tidak boleh lebih dari 10 entri");
            preg_match_all('/Aktivitas ke-(\d+)/', $html, $m);
            $seen = array_merge($seen, $m[1]);
        }

        $this->assertStringContainsString('page=2', $htmlByPage[1], 'Halaman 1 harus menautkan ke halaman 2');
        $this->assertCount(25, $seen, 'Seluruh 25 entri uji muncul di tiga halaman');
        $this->assertCount(25, array_unique($seen), 'Tidak ada entri yang tampil dua kali antar halaman');
        $this->assertNotSame($htmlByPage[1], $htmlByPage[2]);
    }

    public function test_page_beyond_the_last_is_handled_without_error(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'admin')->firstOrFail());

        $this->get('/siswa?page=9999')->assertOk();
        $this->get('/siswa?page=abc')->assertOk();
        $this->get('/siswa?page=-5')->assertOk();
    }
}
