<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class BersihkanAuditCommand extends Command
{
    protected $signature = 'madrasah:bersihkan-audit {--hari=730 : Hapus catatan audit yang lebih lama dari sekian hari (minimal 30)}';

    protected $description = 'Hapus catatan audit yang sudah lama agar database tidak terus membesar';

    public function handle(): int
    {
        $hari = (int) $this->option('hari');
        if ($hari < 30) {
            $this->error('Batas minimal 30 hari, supaya jejak audit tidak terhapus tanpa sengaja.');

            return self::FAILURE;
        }

        $jumlah = AuditLog::where('created_at', '<', now()->subDays($hari))->delete();
        $this->info("{$jumlah} catatan audit lebih dari {$hari} hari dihapus.");

        return self::SUCCESS;
    }
}
