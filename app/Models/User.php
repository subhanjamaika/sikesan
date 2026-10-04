<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password',
        'role_id',
        'unit_organisasi_id',
        'santri_id',
        'akses_wali_santri_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relasi ───

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function unitOrganisasi(): BelongsTo
    {
        return $this->belongsTo(UnitOrganisasi::class, 'unit_organisasi_id');
    }

    /**
     * Santri sendiri (Khusus role Santri)
     */
    public function santriSendiri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    /**
     * Anak / Santri yang diwakili (Khusus Wali Santri)
     * atau relasi generik ke tabel santri.
     */
    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }
    public function penugasanDaerah(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(UnitOrganisasi::class, 'penugasan_pengurus', 'user_id', 'unit_organisasi_id')
                    ->withTimestamps();
    }

    // ─── Role Helpers ───

    /**
     * Cek apakah user memiliki role tertentu (atau salah satu dari beberapa role).
     */
    public function hasRole(string|array $roles): bool
    {
        if (!$this->role) {
            return false;
        }

        // Mapping role baru ke role lama (Legacy Compatibility)
        $aliases = [
            'daerah'           => ['daerah', 'pengurus_daerah', 'wali_asuh', 'kabag'],
            'pengurus_daerah'  => ['daerah', 'pengurus_daerah', 'wali_asuh', 'kabag'],
            'sektor'           => ['sektor', 'pengurus_sektor'],
            'pengurus_sektor'  => ['sektor', 'pengurus_sektor'],
            'wilayah'          => ['wilayah', 'pengurus_wilayah'],
            'pengurus_wilayah' => ['wilayah', 'pengurus_wilayah'],
            'admin'            => ['admin', 'superadmin'],
            'superadmin'       => ['admin', 'superadmin'],
        ];

        $rolesToCheck = is_array($roles) ? $roles : [$roles];
        
        $finalRoles = [];
        foreach ($rolesToCheck as $r) {
            $finalRoles[] = $r;
            if (isset($aliases[$r])) {
                $finalRoles = array_merge($finalRoles, $aliases[$r]);
            }
        }

        return in_array($this->role->nama_role, $finalRoles);
    }

    /**
     * Alias untuk hasRole jika menerima array.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }

    // ─── Scope Helpers ───

    /**
     * Ambil semua unit_organisasi_id yang menjadi wilayah wewenang user ini.
     * Termasuk unit utama (dari user.unit_organisasi_id) dan semua child-nya,
     * ditambah unit dari penugasan (tabel penugasan_pengurus).
     */
    public function getAllowedUnitIds(): array
    {
        $ids = [];

        // Dari unit organisasi utama + semua turunannya
        if ($this->unitOrganisasi) {
            $ids = $this->unitOrganisasi->getAllChildIds();
        }

        // Dari penugasan spesifik (jika ada)
        $penugasan = $this->penugasanDaerah()->get();
        foreach ($penugasan as $unit) {
            $ids = array_merge($ids, $unit->getAllChildIds());
        }

        return array_values(array_unique($ids));
    }

    /**
     * Alias untuk getAllowedUnitIds().
     * Digunakan di DashboardController dan beberapa Policy.
     */
    public function getUnitScope(): array
    {
        return $this->getAllowedUnitIds();
    }
}
