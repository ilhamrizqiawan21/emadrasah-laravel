<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Model;

class PelanggaranJenis extends Model
{
    use Diaudit;

    public const KATEGORI = ['ringan' => 'Ringan', 'sedang' => 'Sedang', 'berat' => 'Berat'];

    protected $table = 'pelanggaran_jenis';

    protected $fillable = ['nama', 'kategori', 'poin'];

    protected $casts = ['poin' => 'integer'];

    public function pelanggaran()
    {
        return $this->hasMany(Pelanggaran::class, 'jenis_id');
    }

    public function auditLabel(): string
    {
        return "Jenis pelanggaran {$this->nama}";
    }
}
