<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AbsensiKegiatan extends Model
{
    protected $table = 'absensi_kegiatan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'santri_id',
        'jenis_kegiatan_id',
        'tanggal',
        'status',
        'pencatat_id',
        'keterangan',
        'is_archived',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'     => 'date',
            'is_archived' => 'boolean',
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

    public function jenisKegiatan(): BelongsTo
    {
        return $this->belongsTo(JenisKegiatan::class, 'jenis_kegiatan_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pencatat_id');
    }

    // ─── Accessors ───

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'Hadir',
            'izin'  => 'Izin',
            'sakit' => 'Sakit',
            'alpa'  => 'Alpa',
            default => ucfirst($this->status ?? ''),
        };
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'green',
            'izin'  => 'yellow',
            'sakit' => 'yellow',
            'alpa'  => 'red',
            default => 'gray',
        };
    }
}
