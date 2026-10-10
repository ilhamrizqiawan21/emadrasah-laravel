<?php

namespace App\Support;

/** Angka rupiah dalam kata-kata bahasa Indonesia, untuk kuitansi (mis. 150000 → "seratus lima puluh ribu rupiah"). */
class Terbilang
{
    private const SATUAN = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

    public static function rupiah(int $angka): string
    {
        return ($angka === 0 ? 'nol' : self::kata($angka)).' rupiah';
    }

    private static function kata(int $n): string
    {
        return trim(match (true) {
            $n < 12 => self::SATUAN[$n],
            $n < 20 => self::SATUAN[$n - 10].' belas',
            $n < 100 => self::SATUAN[intdiv($n, 10)].' puluh '.self::kata($n % 10),
            $n < 200 => 'seratus '.self::kata($n - 100),
            $n < 1000 => self::SATUAN[intdiv($n, 100)].' ratus '.self::kata($n % 100),
            $n < 2000 => 'seribu '.self::kata($n - 1000),
            $n < 1_000_000 => self::kata(intdiv($n, 1000)).' ribu '.self::kata($n % 1000),
            $n < 1_000_000_000 => self::kata(intdiv($n, 1_000_000)).' juta '.self::kata($n % 1_000_000),
            default => self::kata(intdiv($n, 1_000_000_000)).' miliar '.self::kata($n % 1_000_000_000),
        });
    }
}
