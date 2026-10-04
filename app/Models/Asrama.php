<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asrama extends Model
{
    use HasUuids;

    protected $table = 'asramas';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama',
    ];

    /**
     * Relasi ke Kamar (One to Many).
     */
    public function kamars(): HasMany
    {
        return $this->hasMany(Kamar::class, 'asrama_id');
    }
}
