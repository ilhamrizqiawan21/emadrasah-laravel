<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RaportNilai extends Model
{
    use Diaudit, HasFactory;

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

    public function auditLabel(): string
    {
        $siswa = Siswa::withTrashed()->find($this->siswa_id)?->nama_lengkap ?? '#'.$this->siswa_id;
        $mapel = Mapel::find($this->mapel_id)?->nama_mapel ?? '#'.$this->mapel_id;

        return "{$siswa} - {$mapel} (Smt {$this->semester})";
    }
}
