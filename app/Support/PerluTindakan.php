<?php

namespace App\Support;

use App\Models\AbsensiSiswa;
use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\IzinGuru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SuratMasuk;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Daftar "perlu tindakan hari ini" untuk dashboard admin/operator. Hanya menghitung hal yang
 * benar-benar bisa ditindaklanjuti; item bernilai 0 tetap dikembalikan agar tampilan bisa menandainya selesai.
 */
class PerluTindakan
{
    /** Surat masuk yang belum selesai lebih lama dari ini dianggap menunggu tindak lanjut. */
    public const HARI_SURAT_MENUNGGU = 3;

    /** Cadangan terakhir yang lebih tua dari ini dianggap bermasalah (backup berjalan harian). */
    public const BATAS_BACKUP_JAM = 48;

    /** @return array{item: list<array<string, mixed>>, backup: ?array{terakhir: ?Carbon, bermasalah: bool}} */
    public static function untuk(User $user): array
    {
        return [
            'item' => [self::kelasBelumAbsen(), self::guruBelumAbsen(), self::izinMenunggu(), self::tugasTerlambat(), self::suratMenunggu()],
            'backup' => $user->role === 'admin' ? self::statusBackup() : null,
        ];
    }

    /** Kelas berisi siswa aktif yang absensinya belum diisi hari ini (tidak dihitung pada hari Minggu). */
    private static function kelasBelumAbsen(): array
    {
        $nama = collect();

        if (! today()->isSunday()) {
            $sudah = AbsensiSiswa::whereDate('tanggal', today())->distinct()->pluck('kelas_id');
            $kelasIds = Siswa::where('status', 'Aktif')->whereNotNull('kelas_id')->whereNotIn('kelas_id', $sudah)->distinct()->pluck('kelas_id');
            $nama = Kelas::whereIn('id', $kelasIds)->orderBy('nama_kelas')->pluck('nama_kelas');
        }

        return self::item('kelas-belum-absen', 'Kelas belum diabsen hari ini', $nama, route('absensi-siswa.index'), 'fa-user-check');
    }

    /** Guru aktif yang punya jadwal hari ini tetapi belum tercatat di absensi guru. */
    private static function guruBelumAbsen(): array
    {
        $sudah = AgendaGuru::whereDate('tanggal', today())->pluck('guru_id');
        $mengajar = Jadwal::where('hari', now()->translatedFormat('l'))->where('status', 'aktif')->distinct()->pluck('guru_id');
        $nama = Guru::whereIn('id', $mengajar)->whereNotIn('id', $sudah)->where('status', 'aktif')->orderBy('nama')->pluck('nama');

        return self::item('guru-belum-absen', 'Guru belum diabsen hari ini', $nama, route('absensi.index'), 'fa-fingerprint');
    }

    /** Pengajuan izin/cuti guru yang belum diputuskan (satu query dengan join). */
    private static function izinMenunggu(): array
    {
        $nama = IzinGuru::menunggu()->join('gurus', 'gurus.id', '=', 'izin_guru.guru_id')
            ->orderBy('izin_guru.tanggal_mulai')->pluck('gurus.nama');

        return self::item('izin-menunggu', 'Izin guru menunggu persetujuan', $nama, route('izin-guru.index', ['status' => 'menunggu']), 'fa-calendar-check');
    }

    private static function tugasTerlambat(): array
    {
        $judul = Task::where('status', '!=', 'selesai')->whereNotNull('deadline')->whereDate('deadline', '<', today())
            ->orderBy('deadline')->pluck('judul');

        return self::item('tugas-terlambat', 'Tugas melewati tenggat', $judul, route('tasks.index'), 'fa-list-check');
    }

    private static function suratMenunggu(): array
    {
        $perihal = SuratMasuk::where('status', '!=', 'selesai')
            ->whereDate('tanggal_terima', '<=', today()->subDays(self::HARI_SURAT_MENUNGGU))
            ->orderBy('tanggal_terima')->pluck('perihal');

        return self::item('surat-menunggu', 'Surat masuk belum selesai > '.self::HARI_SURAT_MENUNGGU.' hari', $perihal, route('surat-masuk.index'), 'fa-envelope-open-text');
    }

    private static function item(string $kunci, string $label, $contoh, string $url, string $ikon): array
    {
        return [
            'kunci' => $kunci,
            'label' => $label,
            'jumlah' => $contoh->count(),
            'contoh' => $contoh->take(3)->values()->all(),
            'url' => $url,
            'ikon' => $ikon,
        ];
    }

    /** Waktu cadangan terakhir di folder backup, dan apakah sudah terlalu lama atau belum pernah ada. */
    public static function statusBackup(?string $dir = null): array
    {
        $files = glob(($dir ?? storage_path('app/backups')).'/backup-*.zip') ?: [];
        $terakhir = $files === [] ? null : Carbon::createFromTimestamp(max(array_map('filemtime', $files)));

        return [
            'terakhir' => $terakhir,
            'bermasalah' => $terakhir === null || $terakhir->lt(now()->subHours(self::BATAS_BACKUP_JAM)),
        ];
    }
}
