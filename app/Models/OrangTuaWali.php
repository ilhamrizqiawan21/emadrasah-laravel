<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrangTuaWali extends Model
{
    use HasFactory;

    protected $table = 'orang_tua_wali';

    protected $fillable = [
        'siswa_id',
        'nama_ayah',
        'pendidikan_ayah',
        'pekerjaan_ayah',
        'penghasilan_ayah',
        'no_hp_ayah',
        'status_ayah',
        'nama_ibu',
        'pendidikan_ibu',
        'pekerjaan_ibu',
        'penghasilan_ibu',
        'no_hp_ibu',
        'status_ibu',
        'nama_wali',
        'hubungan_wali',
        'pendidikan_wali',
        'pekerjaan_wali',
        'no_hp_wali',
        'alamat_ortu',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
