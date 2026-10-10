<?php

namespace App\Support;

use App\Models\SuratKeluar;
use Illuminate\Support\Carbon;

/** Nomor surat keluar otomatis berformat "012/KODE/X/2026"; urutan mulai dari 001 tiap tahun dan melanjutkan nomor yang diketik manual. */
class NomorSurat
{
    private const ROMAWI = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    public static function berikut(Carbon $tanggal, ?string $kode = null): string
    {
        $kode ??= config('madrasah.kode_surat');

        $terakhir = SuratKeluar::where('nomor_surat', 'like', "%/{$tanggal->year}")->pluck('nomor_surat')
            ->map(fn (string $nomor) => preg_match('/^(\d+)\//', $nomor, $m) ? (int) $m[1] : 0)
            ->max() ?? 0;

        return sprintf('%03d/%s/%s/%d', $terakhir + 1, $kode, self::ROMAWI[$tanggal->month - 1], $tanggal->year);
    }
}
