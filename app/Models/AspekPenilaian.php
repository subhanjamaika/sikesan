<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AspekPenilaian extends Model
{
    protected $table = 'aspek_penilaian';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'unit_organisasi_id',
        'nomor_urut',
        'nama_aspek',
        'indikator',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    // ─── Relasi ───

    public function penilaianDetail(): HasMany
    {
        return $this->hasMany(PenilaianKeseharianDetail::class, 'aspek_id');
    }

    public function unitOrganisasi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class, 'unit_organisasi_id');
    }
}
