<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArsipAkademik extends Model
{
    use HasFactory;

    protected $table = 'arsip_akademik';
    protected $fillable = ['tahun_pelajaran_id', 'kelas_id', 'semester', 'nama_arsip', 'file_path', 'tipe'];

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
