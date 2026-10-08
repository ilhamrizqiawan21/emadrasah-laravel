<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    use HasFactory;

    protected $table = 'surat_masuk';
    protected $fillable = ['nomor_agenda', 'asal_surat', 'nomor_surat', 'perihal', 'tanggal_terima', 'tanggal_surat', 'disposisi', 'file_scan', 'status'];

    protected $casts = [
        'tanggal_terima' => 'date',
        'tanggal_surat' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}