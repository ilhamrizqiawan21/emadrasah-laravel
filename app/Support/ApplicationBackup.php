<?php

namespace App\Support;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class ApplicationBackup
{
    public function __construct(
        private readonly Config $config,
        private readonly Filesystem $files,
    ) {}

    /**
     * @return array{path:string, filename:string, size:int, database:string, uploads:int}
     */
    public function create(?string $name = null): array
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP zip belum aktif.');
        }

        $backupPath = $this->backupPath();
        $this->files->ensureDirectoryExists($backupPath);

        $timestamp = now()->format('Ymd-His');
        $filename = ($name ?: "emadrasah-backup-{$timestamp}").'.zip';
        $path = $backupPath.DIRECTORY_SEPARATOR.$filename;

        $zip = new ZipArchive;

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Gagal membuat file backup: {$path}");
        }

        $tempDatabaseDump = null;
        $uploads = 0;
        $failed = false;

        try {
            $tempDatabaseDump = $this->dumpDatabase();
            $zip->addFile($tempDatabaseDump, 'database/database.sql');

            if ((bool) $this->config->get('emadrasah.backup.include_uploads', true)) {
                $uploads = $this->addUploads($zip);
            }

            $manifest = [
                'application' => $this->config->get('app.name'),
                'created_at' => now()->toIso8601String(),
                'database_connection' => $this->databaseConnectionName(),
                'upload_disk' => $this->config->get('emadrasah.backup.upload_disk', 'public'),
                'upload_files' => $uploads,
            ];

            $zip->addFromString(
                'manifest.json',
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL,
            );
        } catch (Throwable $exception) {
            $failed = true;

            throw $exception;
        } finally {
            $zip->close();

            if ($failed) {
                $this->files->delete($path);
            }

            if ($tempDatabaseDump) {
                $this->files->delete($tempDatabaseDump);
            }
        }

        $this->pruneOldBackups($backupPath);

        return [
            'path' => $path,
            'filename' => $filename,
            'size' => $this->files->size($path),
            'database' => $this->databaseConnectionName(),
            'uploads' => $uploads,
        ];
    }

    private function backupPath(): string
    {
        $path = (string) $this->config->get('emadrasah.backup.path');

        return $path !== '' ? $path : storage_path('app/private/backups');
    }

    private function databaseConnectionName(): string
    {
        return (string) ($this->config->get('emadrasah.backup.database_connection') ?: $this->config->get('database.default'));
    }

    private function dumpDatabase(): string
    {
        $connectionName = $this->databaseConnectionName();
        $connection = DB::connection($connectionName);
        $driver = $connection->getDriverName();
        $path = tempnam(sys_get_temp_dir(), 'emadrasah-db-');

        if ($path === false) {
            throw new RuntimeException('Gagal membuat file sementara untuk dump database.');
        }

        return match ($driver) {
            'sqlite' => $this->dumpSqlite($connectionName, $path),
            'mysql', 'mariadb' => $this->dumpMysql($connectionName, $path),
            'pgsql' => $this->dumpPgsql($connectionName, $path),
            default => throw new RuntimeException("Backup database belum mendukung driver {$driver}."),
        };
    }

    private function dumpSqlite(string $connectionName, string $path): string
    {
        $database = (string) $this->config->get("database.connections.{$connectionName}.database");

        if ($database !== ':memory:' && $database !== '' && $this->files->exists($database)) {
            $this->files->copy($database, $path);

            return $path;
        }

        $pdo = DB::connection($connectionName)->getPdo();
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Gagal menulis dump SQLite.');
        }

        fwrite($handle, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n");

        $tables = $pdo->query(
            "select name, sql from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name",
        )->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($tables as $table) {
            fwrite($handle, $table['sql'].";\n");

            $rows = $pdo->query('select * from "'.str_replace('"', '""', $table['name']).'"')->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $columns = array_map(
                    fn (string $column) => '"'.str_replace('"', '""', $column).'"',
                    array_keys($row),
                );
                $values = array_map(fn ($value) => $this->sqlValue($value), array_values($row));

                fwrite(
                    $handle,
                    'INSERT INTO "'.str_replace('"', '""', $table['name']).'" ('.implode(', ', $columns).') VALUES ('.implode(', ', $values).");\n",
                );
            }
        }

        fwrite($handle, "COMMIT;\nPRAGMA foreign_keys=ON;\n");
        fclose($handle);

        return $path;
    }

    private function dumpMysql(string $connectionName, string $path): string
    {
        $connection = $this->config->get("database.connections.{$connectionName}");
        $command = [
            (string) $this->config->get('emadrasah.backup.mysqldump_binary', 'mysqldump'),
            '--single-transaction',
            '--quick',
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
            '--user='.$connection['username'],
            '--databases',
            $connection['database'],
        ];

        if (! empty($connection['unix_socket'])) {
            $command[] = '--socket='.$connection['unix_socket'];
        } else {
            $command[] = '--host='.$connection['host'];
            $command[] = '--port='.(string) $connection['port'];
        }

        $this->runDumpProcess($command, $path, ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);

        return $path;
    }

    private function dumpPgsql(string $connectionName, string $path): string
    {
        $connection = $this->config->get("database.connections.{$connectionName}");
        $command = [
            (string) $this->config->get('emadrasah.backup.pg_dump_binary', 'pg_dump'),
            '--format=plain',
            '--no-owner',
            '--no-privileges',
            '--host='.$connection['host'],
            '--port='.(string) $connection['port'],
            '--username='.$connection['username'],
            $connection['database'],
        ];

        $this->runDumpProcess($command, $path, ['PGPASSWORD' => (string) ($connection['password'] ?? '')]);

        return $path;
    }

    /**
     * @param  array<int, string>  $command
     * @param  array<string, string>  $environment
     */
    private function runDumpProcess(array $command, string $path, array $environment): void
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException('Gagal menulis dump database.');
        }

        $process = new Process($command, base_path(), $environment, null, 300);
        $process->run(function (string $type, string $buffer) use ($handle): void {
            if ($type === Process::OUT) {
                fwrite($handle, $buffer);
            }
        });

        fclose($handle);

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'Proses dump database gagal.');
        }
    }

    private function addUploads(ZipArchive $zip): int
    {
        $disk = Storage::disk((string) $this->config->get('emadrasah.backup.upload_disk', 'public'));
        $paths = $this->config->get('emadrasah.backup.upload_paths', []);
        $count = 0;

        foreach ($paths as $directory) {
            if (! $disk->exists($directory)) {
                continue;
            }

            foreach ($disk->allFiles($directory) as $file) {
                $stream = $disk->readStream($file);

                if ($stream === null || $stream === false) {
                    continue;
                }

                $zip->addFromString('uploads/'.$file, stream_get_contents($stream) ?: '');

                if (is_resource($stream)) {
                    fclose($stream);
                }

                $count++;
            }
        }

        return $count;
    }

    private function pruneOldBackups(string $backupPath): void
    {
        $retentionDays = (int) $this->config->get('emadrasah.backup.retention_days', 14);

        if ($retentionDays < 1) {
            return;
        }

        $threshold = now()->subDays($retentionDays)->getTimestamp();

        foreach ($this->files->glob($backupPath.DIRECTORY_SEPARATOR.'emadrasah-backup-*.zip') ?: [] as $file) {
            if ($this->files->lastModified($file) < $threshold) {
                $this->files->delete($file);
            }
        }
    }

    private function sqlValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "'".str_replace("'", "''", (string) $value)."'";
    }
}
