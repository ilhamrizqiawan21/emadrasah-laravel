<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaportKelulusan extends Model
{
    protected $table = 'raport_kelulusan';

    protected $fillable = [
        'siswa_id',
        'tahun_pelajaran_id',
        'status_kelulusan',
        'tanggal_keputusan',
        'no_ijazah',
        'no_skhus',
        'tgl_ijazah',
    ];

    protected $casts = [
        'tanggal_keputusan' => 'date',
        'tgl_ijazah' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }
}
