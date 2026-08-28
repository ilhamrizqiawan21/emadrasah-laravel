<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArsipAkademik extends Model
{
    use Auditable, HasFactory, SoftDeletes;

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
