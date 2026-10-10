<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Tagihan extends Model
{
    use Diaudit;

    public const JENIS = ['SPP', 'Daftar Ulang', 'Seragam', 'Buku', 'Kegiatan', 'Lainnya'];

    protected $table = 'tagihan';

    protected $fillable = ['siswa_id', 'jenis', 'periode', 'jumlah', 'jatuh_tempo', 'keterangan'];

    protected $casts = ['jumlah' => 'integer', 'jatuh_tempo' => 'date'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class)->orderBy('tanggal')->orderBy('id');
    }

    /** Muat total terbayar sebagai `terbayar` agar daftar tidak memicu query per baris. */
    public function scopeDenganTerbayar(Builder $query): Builder
    {
        return $query->withSum('pembayaran as terbayar', 'jumlah');
    }

    /** Saring menurut status: belum | sebagian | lunas | terlambat (belum lunas dan lewat jatuh tempo). */
    public function scopeStatus(Builder $query, string $status): Builder
    {
        $bayar = '(select coalesce(sum(p.jumlah), 0) from pembayaran p where p.tagihan_id = tagihan.id)';

        return match ($status) {
            'lunas' => $query->whereRaw("$bayar >= tagihan.jumlah"),
            'belum' => $query->whereRaw("$bayar = 0"),
            'sebagian' => $query->whereRaw("$bayar > 0 and $bayar < tagihan.jumlah"),
            'terlambat' => $query->whereRaw("$bayar < tagihan.jumlah")->whereDate('tagihan.jatuh_tempo', '<', today()),
            default => $query,
        };
    }

    public function totalTerbayar(): int
    {
        return (int) ($this->terbayar ?? $this->pembayaran()->sum('jumlah'));
    }

    public function sisa(): int
    {
        return max(0, $this->jumlah - $this->totalTerbayar());
    }

    /** lunas | sebagian | belum */
    public function status(): string
    {
        $bayar = $this->totalTerbayar();

        return $bayar >= $this->jumlah ? 'lunas' : ($bayar > 0 ? 'sebagian' : 'belum');
    }

    public function terlambat(): bool
    {
        return $this->status() !== 'lunas' && $this->jatuh_tempo->lt(today());
    }

    public function namaPeriode(): string
    {
        return $this->periode ? Carbon::createFromFormat('!Y-m', $this->periode)->translatedFormat('F Y') : '-';
    }

    public function auditLabel(): string
    {
        return "Tagihan {$this->jenis} {$this->periode} siswa #{$this->siswa_id}";
    }
}
