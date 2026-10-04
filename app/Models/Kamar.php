<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kamar extends Model
{
    use HasUuids;

    protected $table = 'kamars';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama',
        'asrama_id',
    ];

    /**
     * Relasi ke Asrama (Many to One).
     */
    public function asrama(): BelongsTo
    {
        return $this->belongsTo(Asrama::class, 'asrama_id');
    }
}
