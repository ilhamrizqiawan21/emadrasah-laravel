<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'gurus';

    protected $fillable = [
        'kode',
        'nama',
        'bidang_studi',
        'jam_tidak_tersedia',
        'user_id',
        'email',
        'phone',
        'nip',
        'status',
        'beban_jp',
    ];

    protected $casts = [
        'jam_tidak_tersedia' => 'array',
        'beban_jp' => 'integer',
    ];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function agendas()
    {
        return $this->hasMany(AgendaGuru::class);
    }

    public function menjadiPengganti()
    {
        return $this->hasMany(GuruPengganti::class, 'guru_pengganti_id');
    }

    public function guruKelas()
    {
        return $this->hasMany(GuruKelas::class);
    }

    public function bebanMengajar()
    {
        return $this->hasMany(BebanMengajar::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
