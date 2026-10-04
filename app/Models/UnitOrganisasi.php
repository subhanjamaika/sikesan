<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOrganisasi extends Model
{
    use HasUuids;

    protected $table = 'unit_organisasi';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'nama',
        'tipe',
        'parent_id',
    ];

    // ─── Relasi ───

    public function parent(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(UnitOrganisasi::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_organisasi_id');
    }

    public function santri(): HasMany
    {
        return $this->hasMany(Santri::class, 'unit_organisasi_id');
    }

    // ─── Helpers ───

    /**
     * Mendapatkan semua ID unit yang berada di bawah unit ini (termasuk diri sendiri) secara rekursif.
     */
    public function getAllChildIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildIds());
        }
        return $ids;
    }
}
