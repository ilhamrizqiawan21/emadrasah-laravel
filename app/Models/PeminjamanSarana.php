<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeminjamanSarana extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_sarana';
    protected $fillable = ['sarana_id', 'peminjam', 'tipe_peminjam', 'tanggal_pinjam', 'tanggal_kembali', 'denda', 'status'];

    public function sarana()
    {
        return $this->belongsTo(SaranaPrasarana::class);
    }
}