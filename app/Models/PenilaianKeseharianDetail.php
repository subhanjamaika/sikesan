<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PenilaianKeseharianDetail extends Model
{
    protected $table = 'penilaian_keseharian_detail';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'penilaian_id',
        'aspek_id',
        'skor',
    ];

    protected function casts(): array
    {
        return [
            'skor' => 'integer',
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

    public function penilaian(): BelongsTo
    {
        return $this->belongsTo(PenilaianKeseharian::class, 'penilaian_id');
    }

    public function aspek(): BelongsTo
    {
        return $this->belongsTo(AspekPenilaian::class, 'aspek_id');
    }

    // ─── Helpers ───

    /**
     * Label skor dalam bahasa Indonesia.
     */
    public function getSkorLabelAttribute(): string
    {
        return match ($this->skor) {
            4 => 'Sangat Baik',
            3 => 'Baik',
            2 => 'Cukup',
            1 => 'Perlu Pembinaan',
            default => '-',
        };
    }
}
