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
        'nama_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'nik_ayah', 'kewarganegaraan_ayah',
        'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah', 'alamat_ayah', 'no_telp_ayah', 'status_ayah',
        'nama_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'nik_ibu', 'kewarganegaraan_ibu',
        'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu', 'alamat_ibu', 'no_telp_ibu', 'status_ibu',
        'nama_wali', 'tempat_lahir_wali', 'tanggal_lahir_wali', 'nik_wali', 'hubungan_wali',
        'pendidikan_wali', 'pekerjaan_wali', 'penghasilan_wali', 'alamat_wali', 'no_telp_wali'
    ];

    protected $casts = [
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu' => 'date',
        'tanggal_lahir_wali' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
    public $timestamps = false;
}
