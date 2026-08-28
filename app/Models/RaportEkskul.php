<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RaportEkskul extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'raport_ekskul';

    protected $fillable = [
        'siswa_id',
        'tahun_pelajaran_id',
        'semester',
        'nama_ekskul',
        'keterangan',
        'nilai',
        'urut',
    ];

    protected $casts = [
        'semester' => 'integer',
        'urut' => 'integer',
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
