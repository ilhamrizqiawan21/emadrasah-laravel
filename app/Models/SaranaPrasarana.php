<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaranaPrasarana extends Model
{
    use HasFactory;

    protected $table = 'sarana_prasarana';
    protected $fillable = ['kode_sarana', 'nama_sarana', 'kategori_id', 'spesifikasi', 'jumlah', 'kondisi', 'lokasi_ruang', 'tahun_pengadaan', 'foto'];

    public function kategori()
    {
        return $this->belongsTo(KategoriSarana::class, 'kategori_id');
    }

    public function peminjaman()
    {
        return $this->hasMany(PeminjamanSarana::class);
    }

    public function pemeliharaan()
    {
        return $this->hasMany(PemeliharaanSarana::class);
    }
}