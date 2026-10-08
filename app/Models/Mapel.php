<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapels';

    protected $fillable = [
        'nama_mapel',
        'jp_per_sesi',
        'parent_id',
        'urut',
    ];

    protected $casts = [
        'jp_per_sesi' => 'integer',
        'urut' => 'integer',
    ];

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function parent()
    {
        return $this->belongsTo(Mapel::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Mapel::class, 'parent_id');
    }

    public function bebanMengajar()
    {
        return $this->hasMany(BebanMengajar::class);
    }

    public function nilai()
    {
        return $this->hasMany(RaportNilai::class);
    }
}
