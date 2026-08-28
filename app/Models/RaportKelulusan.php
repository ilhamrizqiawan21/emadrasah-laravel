<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RaportKelulusan extends Model
{
    use Auditable, SoftDeletes;

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
