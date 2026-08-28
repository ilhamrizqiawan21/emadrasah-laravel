<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RaportP5ppraDetail extends Model
{
    use Auditable, SoftDeletes;

    protected $table = 'raport_p5ppra_detail';

    public $timestamps = false;

    protected $fillable = [
        'p5ppra_id',
        'urut',
        'dimensi',
        'elemen',
        'sub_elemen',
        'target_pencapaian',
    ];

    protected $casts = [
        'urut' => 'integer',
    ];

    public function p5ppra(): BelongsTo
    {
        return $this->belongsTo(RaportP5ppra::class, 'p5ppra_id');
    }
}
