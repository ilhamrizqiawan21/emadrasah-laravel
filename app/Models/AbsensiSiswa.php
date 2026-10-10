<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiSiswa extends Model
{
    protected $table = 'absensi_siswa';

    protected $fillable = ['tanggal', 'siswa_id', 'kelas_id', 'status', 'keterangan', 'dicatat_oleh'];

    protected $casts = ['tanggal' => 'date'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
