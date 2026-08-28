<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    public function hasPermission(string $permission): bool
    {
        return Permissions::roleHasPermission($this->role, $permission);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function permissions(): array
    {
        return Permissions::rolePermissions($this->role);
    }
}
