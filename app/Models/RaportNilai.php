<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RaportNilai extends Model
{
    use HasFactory;

    protected $table = 'raport_nilai';
    public $timestamps = false; // Karena di SQL tadi tidak ada created_at/updated_at di raport_nilai

    protected $fillable = [
        'siswa_id', 'tahun_pelajaran_id', 'semester', 'mapel_id', 'nilai_akhir', 'capaian_kompetensi'
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
}
