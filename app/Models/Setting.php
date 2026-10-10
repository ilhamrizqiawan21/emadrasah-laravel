<?php

namespace App\Models;

use App\Models\Concerns\Diaudit;
use Illuminate\Database\Eloquent\Model;

/**
 * Generic key/value store for installation-wide settings.
 * Read and written through App\Support\Madrasah, never directly from views.
 */
class Setting extends Model
{
    use Diaudit;

    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    public function auditLabel(): string
    {
        return (string) $this->key;
    }
}
