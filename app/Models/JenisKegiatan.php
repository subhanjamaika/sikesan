<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class JenisKegiatan extends Model
{
    protected $table = 'jenis_kegiatan';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'unit_organisasi_id',
        'program_id',
        'nama_kegiatan',
        'jam_pelaksanaan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif'  => 'boolean',
            'program_id' => \App\Enums\ProgramEnum::class,
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

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    // ─── Relasi ───

    public function absensiKegiatan(): HasMany
    {
        return $this->hasMany(AbsensiKegiatan::class, 'jenis_kegiatan_id');
    }

    public function unitOrganisasi(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class, 'unit_organisasi_id');
    }
}
