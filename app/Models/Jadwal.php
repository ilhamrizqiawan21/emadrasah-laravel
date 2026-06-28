<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwals';

    protected $fillable = [
        'kelas_id',
        'mapel_id',
        'guru_id',
        'jam_pelajaran_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruang',
        'semester',
        'tahun_pelajaran_kode',
        'status',
    ];

    protected $casts = [
        'semester' => 'integer',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_id');
    }
}
