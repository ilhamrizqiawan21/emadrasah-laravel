<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class KalenderAkademik extends Model
{
    use Diaudit;

    /** jenis => [nama, kelas warna Bootstrap] */
    public const JENIS = [
        'libur' => ['Libur', 'danger'],
        'ujian' => ['Ujian', 'warning'],
        'kegiatan' => ['Kegiatan', 'success'],
        'rapat' => ['Rapat', 'info'],
    ];

    protected $table = 'kalender_akademik';

    protected $fillable = ['judul', 'jenis', 'tanggal_mulai', 'tanggal_selesai', 'keterangan'];

    protected $casts = ['tanggal_mulai' => 'date', 'tanggal_selesai' => 'date'];

    /** Agenda yang bersinggungan dengan rentang tanggal (termasuk yang mulai sebelum atau selesai sesudahnya). */
    public function scopeAntara(Builder $query, Carbon $dari, Carbon $sampai): Builder
    {
        return $query->whereDate('tanggal_mulai', '<=', $sampai)->whereDate('tanggal_selesai', '>=', $dari);
    }

    public static function liburPada(Carbon $tanggal): bool
    {
        return static::where('jenis', 'libur')->antara($tanggal, $tanggal)->exists();
    }

    public function auditLabel(): string
    {
        return "Agenda \"{$this->judul}\"";
    }
}
