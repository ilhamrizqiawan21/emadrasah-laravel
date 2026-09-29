<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_pelajaran';

    const UPDATED_AT = null;

    protected $fillable = ['kode', 'nama', 'is_aktif'];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function bebanMengajar()
    {
        return $this->hasMany(BebanMengajar::class);
    }

    public function raportNilai()
    {
        return $this->hasMany(RaportNilai::class);
    }
}
