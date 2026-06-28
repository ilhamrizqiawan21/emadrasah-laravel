<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'judul',
        'deskripsi',
        'assigned_to',
        'prioritas',
        'deadline',
        'status',
        'created_by',
        'kategori',
        'attachment',
        'progress_persen',
    ];

    protected $casts = [
        'deadline' => 'date',
        'progress_persen' => 'integer',
    ];

    public function logs()
    {
        return $this->hasMany(TaskLog::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
