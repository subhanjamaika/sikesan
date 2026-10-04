<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pembinaan extends Model
{
    protected $table = 'pembinaan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'pelanggaran_id',
        'jenis_pembinaan',
        'tanggal',
        'hasil',
        'pembina_id',
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

    public function pelanggaran(): BelongsTo
    {
        return $this->belongsTo(Pelanggaran::class, 'pelanggaran_id');
    }

    public function pembina(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembina_id');
    }
}
