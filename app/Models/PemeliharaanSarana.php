<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeliharaanSarana extends Model
{
    use HasFactory;

    protected $table = 'pemeliharaan_sarana';

    protected $fillable = [
        'sarana_id',
        'tanggal_pemeliharaan',
        'biaya',
        'keterangan',
        'status',
        'tanggal_selesai',
        'teknisi',
    ];

    protected $casts = [
        'tanggal_pemeliharaan' => 'date',
        'biaya' => 'decimal:2',
        'tanggal_selesai' => 'date',
    ];

    public function sarana()
    {
        return $this->belongsTo(SaranaPrasarana::class, 'sarana_id');
    }
}
