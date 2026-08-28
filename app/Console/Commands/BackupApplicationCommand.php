<?php

namespace App\Console\Commands;

use App\Support\ApplicationBackup;
use Illuminate\Console\Command;

class BackupApplicationCommand extends Command
{
    protected $signature = 'emadrasah:backup {--name= : Nama file backup tanpa ekstensi .zip}';

    protected $description = 'Backup database dan file upload eMadrasah ke arsip ZIP.';

    public function handle(ApplicationBackup $backup): int
    {
        if (! config('emadrasah.backup.enabled', true)) {
            $this->warn('Backup dinonaktifkan melalui EMADRASAH_BACKUP_ENABLED.');

            return self::SUCCESS;
        }

        $result = $backup->create($this->option('name'));

        $this->info('Backup berhasil dibuat.');
        $this->line('File: '.$result['path']);
        $this->line('Ukuran: '.$this->formatBytes($result['size']));
        $this->line('Database: '.$result['database']);
        $this->line('File upload: '.$result['uploads']);

        return self::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return "{$bytes} B";
        }

        if ($bytes < 1048576) {
            return round($bytes / 1024, 2).' KB';
        }

        return round($bytes / 1048576, 2).' MB';
    }
}
