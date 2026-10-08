<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'guru_pembimbing_id',
        'kapasitas',
        'ruangan',
        'fase',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
    ];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function guruPembimbing()
    {
        return $this->belongsTo(Guru::class, 'guru_pembimbing_id');
    }

    public function guruKelas()
    {
        return $this->hasMany(GuruKelas::class);
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }
}
