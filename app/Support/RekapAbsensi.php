<?php

namespace App\Support;

use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/** Rekap absensi bulanan satu kelas; dipakai layar rekap dan ekspor Excel agar angkanya pasti sama. */
class RekapAbsensi
{
    public const STATUS = ['hadir', 'izin', 'sakit', 'alpha'];

    /**
     * @return array{siswa: Collection, rekap: array<int, array{hadir: int, izin: int, sakit: int, alpha: int, total: int, persen: ?int}>}
     */
    public static function untuk(Kelas $kelas, Carbon $bulan): array
    {
        $siswa = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
        $jumlah = AbsensiSiswa::where('kelas_id', $kelas->id)
            ->whereBetween('tanggal', [$bulan->copy()->startOfMonth(), $bulan->copy()->endOfMonth()])
            ->selectRaw('siswa_id, status, count(*) as total')
            ->groupBy('siswa_id', 'status')->get()->groupBy('siswa_id');

        $rekap = [];
        foreach ($siswa as $s) {
            $baris = array_fill_keys(self::STATUS, 0);
            foreach ($jumlah[$s->id] ?? [] as $row) {
                $baris[$row->status] = (int) $row->total;
            }
            $baris['total'] = array_sum($baris);
            $baris['persen'] = $baris['total'] > 0 ? (int) round($baris['hadir'] / $baris['total'] * 100) : null;
            $rekap[$s->id] = $baris;
        }

        return ['siswa' => $siswa, 'rekap' => $rekap];
    }
}
