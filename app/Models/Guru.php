<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';
    protected $fillable = ['kode', 'nama', 'bidang_studi', 'jam_tidak_tersedia', 'user_id', 'email', 'phone', 'nip', 'status'];
    
    protected $casts = [
        'jam_tidak_tersedia' => 'array',
    ];

    // Relasi: seorang guru memiliki banyak jadwal
    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    // Relasi: seorang guru memiliki banyak agenda absensi
    public function agendas()
    {
        return $this->hasMany(AgendaGuru::class);
    }

    // Relasi: guru sebagai pengganti
    public function menjadiPengganti()
    {
        return $this->hasMany(GuruPengganti::class, 'guru_pengganti_id');
    }
}