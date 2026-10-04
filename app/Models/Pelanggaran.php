<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pelanggaran extends Model
{
    protected $table = 'pelanggaran';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'santri_id',
        'kategori',
        'deskripsi',
        'tanggal',
        'pencatat_id',
        'tingkat',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

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

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pencatat_id');
    }

    public function pembinaan(): HasMany
    {
        return $this->hasMany(Pembinaan::class, 'pelanggaran_id');
    }
}
