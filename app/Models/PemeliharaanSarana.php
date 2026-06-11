<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PemeliharaanSarana extends Model
{
    use HasFactory;

    protected $table = 'pemeliharaan_sarana';
    protected $fillable = ['sarana_id', 'tanggal_pemeliharaan', 'keterangan', 'teknisi'];

    public function sarana()
    {
        return $this->belongsTo(SaranaPrasarana::class);
    }
}