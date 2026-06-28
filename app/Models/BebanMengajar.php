<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BebanMengajar extends Model
{
    protected $table = 'beban_mengajar';

    protected $fillable = [
        'guru_id',
        'tahun_pelajaran_id',
        'mapel_id',
        'kelas_id',
        'jumlah_jam',
    ];

    protected $casts = [
        'jumlah_jam' => 'integer',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
