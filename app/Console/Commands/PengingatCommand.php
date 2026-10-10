<?php

namespace App\Console\Commands;

use App\Models\PeminjamanSarana;
use App\Models\Task;
use App\Models\User;
use App\Notifications\Pengingat;
use Illuminate\Console\Command;

class PengingatCommand extends Command
{
    /** Sarana yang dipinjam lebih lama dari ini dianggap belum kembali (peminjaman belum punya tenggat). */
    public const HARI_PINJAM_MAKS = 7;

    protected $signature = 'madrasah:pengingat';

    protected $description = 'Kirim pengingat harian: tugas jatuh tempo/terlambat dan sarana yang belum dikembalikan.';

    public function handle(): int
    {
        $jumlah = $this->pengingatTugas() + $this->pengingatSarana();
        $this->info("{$jumlah} pengingat dikirim.");

        return self::SUCCESS;
    }

    /** Tugas belum selesai yang tenggatnya besok atau sudah lewat → petugas yang ditugasi, sekali per hari. */
    private function pengingatTugas(): int
    {
        $terkirim = 0;
        $tugas = Task::with('assignedTo')->where('status', '!=', 'selesai')->whereNotNull('assigned_to')
            ->whereNotNull('deadline')->whereDate('deadline', '<=', today()->addDay())->get();

        foreach ($tugas as $t) {
            $user = $t->assignedTo;
            if (! $user?->is_active) {
                continue;
            }

            $terlambat = $t->deadline->lt(today());
            $terkirim += $this->kirim(
                $user,
                $terlambat ? 'Tugas terlambat' : 'Tugas jatuh tempo besok',
                "{$t->judul} (tenggat {$t->deadline->translatedFormat('d F Y')})",
                route('tasks.show', $t),
                'tugas:'.$t->id.':'.today()->toDateString(),
            );
        }

        return $terkirim;
    }

    /** Sarana dipinjam lebih dari seminggu → admin dan operator, sekali per minggu per peminjaman. */
    private function pengingatSarana(): int
    {
        $terkirim = 0;
        $staf = User::whereIn('role', ['admin', 'operator'])->where('is_active', true)->get();
        $pinjaman = PeminjamanSarana::with('sarana')->where('status', 'dipinjam')
            ->whereDate('tanggal_pinjam', '<=', today()->subDays(self::HARI_PINJAM_MAKS))->get();

        foreach ($pinjaman as $p) {
            foreach ($staf as $user) {
                $terkirim += $this->kirim(
                    $user,
                    'Sarana belum dikembalikan',
                    "{$p->sarana?->nama_sarana} dipinjam {$p->peminjam} sejak {$p->tanggal_pinjam->translatedFormat('d F Y')}",
                    route('sarana.peminjaman', $p->sarana_id),
                    'sarana:'.$p->id.':'.today()->format('o-W'),
                );
            }
        }

        return $terkirim;
    }

    /** Kirim bila pengingat dengan kunci sama belum pernah dikirim; 1 jika terkirim. */
    private function kirim(User $user, string $judul, string $pesan, string $url, string $kunci): int
    {
        if ($user->notifications()->where('data->kunci', $kunci)->exists()) {
            return 0;
        }

        $user->notify(new Pengingat($judul, $pesan, $url, $kunci));

        return 1;
    }
}
