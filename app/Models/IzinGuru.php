<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class IzinGuru extends Model
{
    public const JENIS = ['izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti', 'dinas' => 'Dinas luar'];

    /** Batas panjang satu pengajuan (hari). */
    public const MAKS_HARI = 60;

    protected $table = 'izin_guru';

    protected $fillable = [
        'guru_id', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'alasan', 'status',
        'diputuskan_oleh', 'diputuskan_pada', 'catatan_keputusan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diputuskan_pada' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function pemutus()
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('status'), 'menunggu');
    }

    /** Status absensi guru yang dicatat untuk jenis ini: sakit tetap sakit, lainnya dicatat izin. */
    public function statusAbsensi(): string
    {
        return $this->jenis === 'sakit' ? 'sakit' : 'izin';
    }

    public function rentang(): string
    {
        return $this->tanggal_mulai->isSameDay($this->tanggal_selesai)
            ? $this->tanggal_mulai->translatedFormat('d F Y')
            : $this->tanggal_mulai->translatedFormat('d F').' – '.$this->tanggal_selesai->translatedFormat('d F Y');
    }
}
