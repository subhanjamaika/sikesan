<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Santri extends Model
{
    protected $table = 'santri';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama',
        'nis_induk',
        'unit_organisasi_id',
        'wali_asuh_id',
        'wali_santri_kontak',
        'program_id',
    ];

    protected function casts(): array
    {
        return [
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

    // ─── Relasi ───

    public function unitOrganisasi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class, 'unit_organisasi_id');
    }

    public function waliAsuh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_asuh_id');
    }

    public function pelanggaran(): HasMany
    {
        return $this->hasMany(Pelanggaran::class, 'santri_id');
    }

    public function penilaianKeseharian(): HasMany
    {
        return $this->hasMany(PenilaianKeseharian::class, 'santri_id');
    }

    public function absensiKegiatan(): HasMany
    {
        return $this->hasMany(AbsensiKegiatan::class, 'santri_id');
    }

    public function absensiPuasa(): HasMany
    {
        return $this->hasMany(AbsensiPuasa::class, 'santri_id');
    }

    public function scopeInUnitOrProgram($query, $unitIds)
    {
        if (empty($unitIds)) {
            return $query->whereRaw('1 = 0');
        }

        // Cari nama Daerah berdasarkan unitIds
        $daerahNames = \App\Models\UnitOrganisasi::whereIn('id', $unitIds)->pluck('nama');
        
        $programIds = [];
        foreach ($daerahNames as $name) {
            $programIds = array_merge($programIds, \App\Enums\ProgramEnum::getProgramIdsForDaerah($name));
        }

        return $query->where(function ($q) use ($unitIds, $programIds) {
            $q->whereIn('unit_organisasi_id', $unitIds);
            if (!empty($programIds)) {
                $q->orWhereIn('program_id', $programIds);
            }
        });
    }
}
