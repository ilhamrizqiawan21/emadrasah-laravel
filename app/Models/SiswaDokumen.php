<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaDokumen extends Model
{
    use HasFactory;

    protected $table = 'siswa_dokumen';

    protected $fillable = ['siswa_id', 'jenis_dokumen', 'file_path', 'nama_file'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
