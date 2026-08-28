<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratKeluar extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'surat_keluar';
    protected $fillable = ['nomor_surat', 'tujuan', 'perihal', 'tanggal_kirim', 'lampiran', 'file_draft'];

    protected $casts = [
        'tanggal_kirim' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
