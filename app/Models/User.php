<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Diaudit, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'alamat',
        'is_active',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function createdTasks()
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class);
    }

    /** Siswa yang tertaut ke akun ini (anak untuk wali murid, diri sendiri untuk akun siswa). */
    public function anak()
    {
        return $this->belongsToMany(Siswa::class, 'wali_siswa')->withTimestamps();
    }

    public function auditLabel(): string
    {
        return "{$this->name} ({$this->email})";
    }
}
