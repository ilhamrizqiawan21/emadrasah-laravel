<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluar';

    protected $fillable = ['nomor_surat', 'tujuan', 'perihal', 'tanggal_kirim', 'lampiran', 'file_draft', 'siswa_id', 'jenis', 'keperluan', 'data'];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
