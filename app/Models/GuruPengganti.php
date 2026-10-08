<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruPengganti extends Model
{
    use HasFactory;

    protected $table = 'guru_pengganti';

    protected $fillable = ['agenda_guru_id', 'jam_pelajaran_id', 'guru_pengganti_id', 'keterangan'];

    public function agendaGuru()
    {
        return $this->belongsTo(AgendaGuru::class);
    }

    public function jamPelajaran()
    {
        return $this->belongsTo(JamPelajaran::class);
    }

    public function guruPengganti()
    {
        return $this->belongsTo(Guru::class, 'guru_pengganti_id');
    }
}
