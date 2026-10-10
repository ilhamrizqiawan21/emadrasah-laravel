<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use Diaudit;

    public const METODE = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'lainnya' => 'Lainnya'];

    protected $table = 'pembayaran';

    protected $fillable = ['tagihan_id', 'jumlah', 'tanggal', 'metode', 'catatan', 'dicatat_oleh'];

    protected $casts = ['jumlah' => 'integer', 'tanggal' => 'date'];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function nomorKuitansi(): string
    {
        return sprintf('KW-%06d', $this->id);
    }

    public function auditLabel(): string
    {
        return "Pembayaran Rp {$this->jumlah} untuk tagihan #{$this->tagihan_id}";
    }
}
