<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunPelajaran extends Model
{
    use Diaudit, HasFactory;

    protected $table = 'tahun_pelajaran';

    const UPDATED_AT = null;

    protected $fillable = ['kode', 'nama', 'is_aktif'];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    /** Kode tahun pelajaran yang sedang aktif, atau null bila belum ada. */
    public static function kodeAktif(): ?string
    {
        return static::where('is_aktif', true)->value('kode');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function bebanMengajar()
    {
        return $this->hasMany(BebanMengajar::class);
    }

    public function raportNilai()
    {
        return $this->hasMany(RaportNilai::class);
    }

    public function auditLabel(): string
    {
        return (string) $this->kode;
    }
}
