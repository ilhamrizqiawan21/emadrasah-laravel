<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Model;

class CatatanBk extends Model
{
    use Diaudit;

    protected $table = 'catatan_bk';

    protected $fillable = ['siswa_id', 'tanggal', 'topik', 'uraian', 'tindak_lanjut', 'dicatat_oleh'];

    protected $casts = ['tanggal' => 'date'];

    /** Isi konseling bersifat rahasia: tidak ikut tersimpan di log audit, hanya penanda bahwa ada perubahan. */
    protected function auditRahasia(): array
    {
        return ['uraian', 'tindak_lanjut'];
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class)->withTrashed();
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function auditLabel(): string
    {
        return "Catatan BK siswa #{$this->siswa_id}";
    }
}
