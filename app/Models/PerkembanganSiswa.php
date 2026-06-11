<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerkembanganSiswa extends Model
{
    use HasFactory;

    protected $table = 'perkembangan_siswa';
    // Database only has `updated_at` (no `created_at`) — configure Eloquent accordingly.
    public $timestamps = true;
    const CREATED_AT = null;
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'siswa_id',
        'asal_madrasah',
        'nama_madrasah_asal',
        'tgl_ijazah_asal',
        'no_ijazah_asal',
        'jenis_masuk',
        'tgl_diterima',
        'dari_tingkat',
        'no_surat_pindah',
        'jenis_keluar',
        'thn_lulus',
        'no_ijazah_lulus',
        'melanjutkan_ke',
        'pindah_ke_madrasah',
        'pindah_tingkat',
        'alasan_keluar',
        'tgl_keluar'
    ];

    protected $casts = [
        'tgl_ijazah_asal' => 'date',
        'tgl_diterima' => 'date',
        'tgl_keluar' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
