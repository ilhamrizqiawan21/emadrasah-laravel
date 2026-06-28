<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RaportNilai extends Model
{
    use HasFactory;

    protected $table = 'raport_nilai';

    protected $fillable = [
        'siswa_id',
        'tahun_pelajaran_id',
        'semester',
        'mapel_id',
        'nilai_akhir',
        'kktp',
        'deskripsi',
        'updated_by',
        'nilai_ujian_madrasah',
        'deskripsi_ujian',
    ];

    protected $casts = [
        'semester' => 'integer',
        'nilai_akhir' => 'integer',
        'kktp' => 'integer',
        'nilai_ujian_madrasah' => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
