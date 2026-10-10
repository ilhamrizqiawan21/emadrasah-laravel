<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

/**
 * Cadangan database + berkas unggahan dalam satu file zip di storage/app/backups.
 * Dijadwalkan harian (routes/console.php). Langkah pemulihan ada di docs/DEPLOYMENT.md.
 */
class BackupCommand extends Command
{
    protected $signature = 'madrasah:backup {--keep=14 : Hapus cadangan yang lebih lama dari N hari}';

    protected $description = 'Cadangkan database dan berkas unggahan ke storage/app/backups';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        if (! is_dir($dir) && ! mkdir($dir, 0700, true) && ! is_dir($dir)) {
            $this->error("Tidak bisa membuat folder {$dir}");

            return self::FAILURE;
        }

        $stamp = now()->format('Ymd-His');
        $zipPath = "{$dir}/backup-{$stamp}.zip";
        $tmpDb = null;

        try {
            [$tmpDb, $dbName] = $this->dumpDatabase();

            $zip = new ZipArchive;
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new RuntimeException('Tidak bisa membuat berkas zip.');
            }

            $zip->addFile($tmpDb, $dbName);
            $files = $this->addUploads($zip);

            if ($zip->close() === false) {
                throw new RuntimeException('Gagal menulis berkas zip.');
            }
        } catch (\Throwable $e) {
            @unlink($zipPath);
            $this->error('Backup gagal: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            if ($tmpDb && is_file($tmpDb)) {
                @unlink($tmpDb);
            }
        }

        chmod($zipPath, 0600);
        $this->info(sprintf('Backup selesai: %s (%s KB, database + %d berkas unggahan)', $zipPath, number_format(filesize($zipPath) / 1024, 1), $files));

        $removed = $this->prune($dir, max(1, (int) $this->option('keep')));
        if ($removed > 0) {
            $this->line("Cadangan lama dihapus: {$removed}");
        }

        return self::SUCCESS;
    }

    /** @return array{0:string,1:string} [path berkas sementara, nama di dalam zip] */
    private function dumpDatabase(): array
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        $tmp = tempnam(sys_get_temp_dir(), 'em-db-');

        switch ($config['driver']) {
            case 'sqlite':
                $source = $config['database'];
                if ($source === ':memory:' || ! is_file($source)) {
                    throw new RuntimeException('Database SQLite bukan berkas, tidak bisa dicadangkan.');
                }
                @unlink($tmp);
                // VACUUM INTO menghasilkan salinan yang konsisten walau database sedang dipakai.
                DB::connection($connection)->statement('VACUUM INTO '.DB::connection($connection)->getPdo()->quote($tmp));

                return [$tmp, 'database.sqlite'];

            case 'mysql':
            case 'mariadb':
                $this->runDump([
                    'mysqldump', '--single-transaction', '--no-tablespaces', '--routines',
                    '-h', (string) $config['host'], '-P', (string) $config['port'], '-u', (string) $config['username'],
                    (string) $config['database'],
                ], ['MYSQL_PWD' => (string) $config['password']], $tmp);

                return [$tmp, 'database.sql'];

            case 'pgsql':
                $this->runDump([
                    'pg_dump', '--no-owner', '-h', (string) $config['host'], '-p', (string) $config['port'],
                    '-U', (string) $config['username'], (string) $config['database'],
                ], ['PGPASSWORD' => (string) $config['password']], $tmp);

                return [$tmp, 'database.sql'];
        }

        throw new RuntimeException("Driver database '{$config['driver']}' belum didukung untuk backup.");
    }

    /** Kata sandi dikirim lewat environment (bukan argumen) agar tidak terlihat di daftar proses. */
    private function runDump(array $command, array $env, string $target): void
    {
        $handle = fopen($target, 'wb');
        $process = new Process($command, null, $env, null, 600);

        try {
            $process->run(function (string $type, string $buffer) use ($handle) {
                if ($type === Process::OUT) {
                    fwrite($handle, $buffer);
                }
            });
        } finally {
            fclose($handle);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException("{$command[0]} gagal: ".trim($process->getErrorOutput() ?: 'periksa apakah perintahnya terpasang'));
        }
    }

    /** Menambahkan seluruh isi storage/app/private (surat, dokumen siswa, logo, dll.) ke zip. */
    private function addUploads(ZipArchive $zip): int
    {
        $root = storage_path('app/private');
        if (! is_dir($root)) {
            return 0;
        }

        $count = 0;
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile() && ! $file->isLink()) {
                $relative = ltrim(substr($file->getPathname(), strlen($root)), DIRECTORY_SEPARATOR);
                $zip->addFile($file->getPathname(), 'uploads/'.str_replace(DIRECTORY_SEPARATOR, '/', $relative));
                $count++;
            }
        }

        return $count;
    }

    private function prune(string $dir, int $days): int
    {
        $limit = now()->subDays($days)->getTimestamp();
        $removed = 0;

        foreach (glob("{$dir}/backup-*.zip") ?: [] as $file) {
            if (filemtime($file) < $limit && @unlink($file)) {
                $removed++;
            }
        }

        return $removed;
    }
}
