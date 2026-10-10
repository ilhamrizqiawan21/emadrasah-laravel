<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Penanda raport satu semester sudah boleh dilihat wali murid/siswa. */
class RaportRilis extends Model
{
    protected $table = 'raport_rilis';

    protected $fillable = ['tahun_pelajaran_id', 'semester'];

    public static function dirilis(?int $tahunPelajaranId, int $semester): bool
    {
        return $tahunPelajaranId !== null
            && static::where('tahun_pelajaran_id', $tahunPelajaranId)->where('semester', $semester)->exists();
    }
}
