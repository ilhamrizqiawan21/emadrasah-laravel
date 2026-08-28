<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RaportP5ppra extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'raport_p5ppra';

    protected $fillable = [
        'siswa_id',
        'tahun_pelajaran_id',
        'semester',
        'tema_projek_1',
        'tema_projek_2',
        'tema_projek_3',
    ];

    protected $casts = [
        'semester' => 'integer',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(RaportP5ppraDetail::class, 'p5ppra_id');
    }
}
