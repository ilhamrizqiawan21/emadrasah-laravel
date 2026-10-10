<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Penjaga N+1: jumlah query tiap halaman daftar tidak boleh naik ketika datanya diperbanyak.
 * (Halaman dengan paginasi berhenti tumbuh di batas halaman, jadi data diperbanyak sampai
 * melewati batas itu.)
 */
class QueryBudgetTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        '/dashboard', '/siswa', '/guru', '/kelas', '/mapel', '/jadwal', '/jadwal?kelas_id=1',
        '/absensi', '/absensi/rekap', '/raport', '/buku-induk', '/surat-masuk', '/surat-keluar',
        '/sarana', '/tasks', '/tasks?view=list', '/users', '/jam-pelajaran', '/arsip-akademik', '/template-surat',
    ];

    private function queriesFor(string $uri): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $status = $this->get($uri)->status();
        $total = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame(200, $status, "{$uri} harus 200");

        return $total;
    }

    /** Menggandakan baris yang ada beberapa kali dengan nilai unik baru. */
    private function grow(string $model, int $times): void
    {
        $columns = Schema::getColumnListing((new $model)->getTable());
        $unique = array_intersect(['nama_kelas', 'nis', 'nisn', 'kode', 'nomor_agenda', 'nomor_surat', 'kode_sarana', 'nip', 'email'], $columns);

        foreach ($model::all() as $row) {
            for ($i = 0; $i < $times; $i++) {
                $copy = $row->replicate();
                foreach ($unique as $column) {
                    $copy->{$column} = $row->{$column} === null ? null : Str::random(10);
                }
                if (in_array('nik', $columns, true)) {
                    $copy->nik = null;
                }
                $copy->save();
            }
        }
    }

    public function test_list_pages_do_not_run_more_queries_as_data_grows(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'admin')->firstOrFail());

        $before = [];
        foreach (self::PAGES as $page) {
            $before[$page] = $this->queriesFor($page);
        }

        foreach ([Kelas::class, Task::class, JamPelajaran::class, Siswa::class, Guru::class, SuratMasuk::class, SuratKeluar::class, SaranaPrasarana::class] as $model) {
            $this->grow($model, 3);
        }

        $grown = [];
        foreach (self::PAGES as $page) {
            $after = $this->queriesFor($page);
            if ($after > $before[$page] + 1) {
                $grown[] = "{$page}: {$before[$page]} -> {$after} query";
            }
        }

        $this->assertSame([], $grown, "Dugaan N+1 (query bertambah seiring data):\n".implode("\n", $grown));
    }

    public function test_no_page_needs_an_excessive_number_of_queries(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('role', 'admin')->firstOrFail());

        foreach (self::PAGES as $page) {
            $this->assertLessThanOrEqual(30, $this->queriesFor($page), "{$page} memakai terlalu banyak query");
        }
    }
}
