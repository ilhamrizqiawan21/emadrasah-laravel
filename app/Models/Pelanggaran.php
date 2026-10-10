<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Model;

class Pelanggaran extends Model
{
    use Diaudit;

    protected $table = 'pelanggaran';

    protected $fillable = ['siswa_id', 'jenis_id', 'poin', 'tanggal', 'keterangan', 'dicatat_oleh'];

    protected $casts = ['poin' => 'integer', 'tanggal' => 'date'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }

    public function jenis()
    {
        return $this->belongsTo(PelanggaranJenis::class, 'jenis_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function auditLabel(): string
    {
        return "Pelanggaran siswa #{$this->siswa_id} ({$this->poin} poin)";
    }
}
