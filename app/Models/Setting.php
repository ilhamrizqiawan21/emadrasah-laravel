<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic key/value store for installation-wide settings.
 * Read and written through App\Support\Madrasah, never directly from views.
 */
class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];
}
