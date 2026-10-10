<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

/**
 * Sengaja tanpa RefreshDatabase: tes ini mengganti koneksi bawaan ke berkas SQLite
 * sementara, yang bertabrakan dengan transaksi pembungkus RefreshDatabase.
 */
class BackupCommandTest extends TestCase
{
    public function test_backup_refuses_an_in_memory_database_without_leaving_files(): void
    {
        $storage = $this->isolatedStorage();

        // Paksa SQLite memori agar tes tidak bergantung pada DB_CONNECTION lingkungan (mis. MySQL).
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');

        $this->artisan('madrasah:backup')->expectsOutputToContain('Backup gagal')->assertFailed();

        $this->assertSame([], glob("{$storage}/app/backups/*.zip"));
        File::deleteDirectory($storage);
    }

    public function test_backup_contains_database_and_uploads_and_can_be_restored(): void
    {
        $storage = $this->isolatedStorage();
        $this->liveSqlite($storage);

        DB::connection('backup_test')->statement('create table catatan (isi text)');
        DB::connection('backup_test')->table('catatan')->insert(['isi' => 'data penting']);

        File::ensureDirectoryExists("{$storage}/app/private/surat-masuk");
        file_put_contents("{$storage}/app/private/surat-masuk/scan.txt", 'isi scan surat');

        $this->artisan('madrasah:backup')->expectsOutputToContain('Backup selesai')->assertSuccessful();

        $zips = glob("{$storage}/app/backups/backup-*.zip");
        $this->assertCount(1, $zips);
        $this->assertSame('0600', substr(sprintf('%o', fileperms($zips[0])), -4), 'Cadangan tidak boleh terbaca pengguna lain');

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zips[0]));
        $this->assertSame('isi scan surat', $zip->getFromName('uploads/surat-masuk/scan.txt'));

        // Pulihkan: database di dalam zip harus bisa dibuka dan berisi data yang sama.
        $restored = $storage.'/restored.sqlite';
        file_put_contents($restored, $zip->getFromName('database.sqlite'));
        $zip->close();

        $pdo = new \PDO("sqlite:{$restored}");
        $this->assertSame('data penting', $pdo->query('select isi from catatan')->fetchColumn());

        File::deleteDirectory($storage);
    }

    public function test_backup_prunes_only_old_archives(): void
    {
        $storage = $this->isolatedStorage();
        $this->liveSqlite($storage);
        DB::connection('backup_test')->statement('create table t (x int)');

        File::ensureDirectoryExists("{$storage}/app/backups");
        $old = "{$storage}/app/backups/backup-20200101-000000.zip";
        $recent = "{$storage}/app/backups/backup-20200102-000000.zip";
        touch($old, now()->subDays(30)->getTimestamp());
        touch($recent, now()->subDays(2)->getTimestamp());

        $this->artisan('madrasah:backup', ['--keep' => 14])->assertSuccessful();

        $this->assertFileDoesNotExist($old);
        $this->assertFileExists($recent);

        File::deleteDirectory($storage);
    }

    /** Mengarahkan storage_path() ke folder sementara agar tes tidak menyentuh data asli. */
    private function isolatedStorage(): string
    {
        $path = sys_get_temp_dir().'/em-storage-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($path);
        $this->app->useStoragePath($path);

        return $path;
    }

    /** Database SQLite berupa berkas (bukan :memory:) sebagai koneksi bawaan selama tes. */
    private function liveSqlite(string $storage): string
    {
        $db = $storage.'/live.sqlite';
        touch($db);

        config([
            'database.default' => 'backup_test',
            'database.connections.backup_test' => ['driver' => 'sqlite', 'database' => $db, 'prefix' => '', 'foreign_key_constraints' => false],
        ]);
        DB::purge('backup_test');

        return $db;
    }
}
