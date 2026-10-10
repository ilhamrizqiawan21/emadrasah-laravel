<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Model;

class RiwayatKelas extends Model
{
    use Diaudit;

    protected $table = 'riwayat_kelas';

    protected $fillable = [
        'siswa_id', 'tahun_pelajaran_id', 'hasil', 'kelas_asal_id', 'kelas_tujuan_id',
        'kelas_asal_nama', 'kelas_tujuan_nama', 'dicatat_oleh',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function auditLabel(): string
    {
        $siswa = Siswa::withTrashed()->find($this->siswa_id)?->nama_lengkap ?? '#'.$this->siswa_id;

        return "{$siswa}: {$this->hasil} dari {$this->kelas_asal_nama}";
    }
}
