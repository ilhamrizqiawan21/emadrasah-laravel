<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaportCatatan extends Model
{
    protected $table = 'raport_catatan';

    protected $fillable = ['siswa_id', 'tahun_pelajaran_id', 'semester', 'catatan_wali'];

    protected $casts = ['semester' => 'integer'];
}
