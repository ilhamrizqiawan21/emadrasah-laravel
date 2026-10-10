<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use Diaudit;

    public const TARGET = ['semua' => 'Semua pengguna', 'guru' => 'Guru', 'wali' => 'Wali murid dan siswa'];

    protected $table = 'pengumuman';

    protected $fillable = ['judul', 'isi', 'target', 'terbit_pada', 'berakhir_pada', 'disematkan', 'dibuat_oleh'];

    protected $casts = ['terbit_pada' => 'date', 'berakhir_pada' => 'date', 'disematkan' => 'boolean'];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    /** Sudah terbit dan belum berakhir hari ini. */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereDate('terbit_pada', '<=', today())
            ->where(fn ($q) => $q->whereNull('berakhir_pada')->orWhereDate('berakhir_pada', '>=', today()));
    }

    /** Pengumuman yang ditujukan ke role ini. Admin dan operator melihat semuanya. */
    public function scopeUntukRole(Builder $query, string $role): Builder
    {
        return match ($role) {
            'admin', 'operator' => $query,
            'guru' => $query->whereIn('target', ['semua', 'guru']),
            default => $query->whereIn('target', ['semua', 'wali']),
        };
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderByDesc('disematkan')->orderByDesc('terbit_pada')->orderByDesc('id');
    }

    /** @return list<string> Role akun yang dituju (untuk notifikasi). */
    public function roleTujuan(): array
    {
        return match ($this->target) {
            'guru' => ['guru'],
            'wali' => ['wali_murid', 'siswa'],
            default => ['admin', 'operator', 'guru', 'wali_murid', 'siswa'],
        };
    }

    /** aktif | terjadwal | berakhir */
    public function status(): string
    {
        return match (true) {
            $this->terbit_pada->gt(today()) => 'terjadwal',
            $this->berakhir_pada !== null && $this->berakhir_pada->lt(today()) => 'berakhir',
            default => 'aktif',
        };
    }

    public function auditLabel(): string
    {
        return "Pengumuman \"{$this->judul}\"";
    }
}
