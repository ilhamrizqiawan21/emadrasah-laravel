<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgendaGuru extends Model
{
    use HasFactory;

    protected $table = 'agenda_guru';

    protected $fillable = ['tanggal', 'guru_id', 'status', 'keterangan'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function guruPengganti()
    {
        return $this->hasMany(GuruPengganti::class);
    }
}
