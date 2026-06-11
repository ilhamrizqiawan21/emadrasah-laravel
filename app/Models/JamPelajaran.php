<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jam_pelajaran';
    protected $fillable = ['hari', 'sesi_ke', 'jam_mulai', 'jam_selesai'];

    public function guruPengganti()
    {
        return $this->hasMany(GuruPengganti::class);
    }
}