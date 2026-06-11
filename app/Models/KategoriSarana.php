<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriSarana extends Model
{
    use HasFactory;

    protected $table = 'kategori_sarana';
    protected $fillable = ['nama_kategori'];

    public function sarana()
    {
        return $this->hasMany(SaranaPrasarana::class);
    }
}