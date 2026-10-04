<?php

namespace App\Policies;

use App\Models\AbsensiPuasa;
use App\Models\User;

class AbsensiPuasaPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'dewan_pemangku'])) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['pengurus_sektor', 'pengurus_daerah', 'pengurus_wilayah', 'wali_santri']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AbsensiPuasa $absensi): bool
    {
        if ($user->hasRole(['pengurus_sektor', 'pengurus_daerah', 'pengurus_wilayah'])) {
            return $absensi->santri && in_array($absensi->santri->unit_organisasi_id, $user->getAllowedUnitIds());
        }

        if ($user->hasRole('wali_santri')) {
            return $user->santri_id !== null && $user->santri_id === $absensi->santri_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['pengurus_sektor', 'pengurus_daerah', 'pengurus_wilayah']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AbsensiPuasa $absensi): bool
    {
        if ($user->hasRole(['pengurus_sektor', 'pengurus_daerah', 'pengurus_wilayah'])) {
            return $absensi->santri && in_array($absensi->santri->unit_organisasi_id, $user->getAllowedUnitIds());
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AbsensiPuasa $absensi): bool
    {
        if ($user->hasRole(['pengurus_sektor', 'pengurus_daerah', 'pengurus_wilayah'])) {
            return $absensi->santri && in_array($absensi->santri->unit_organisasi_id, $user->getAllowedUnitIds());
        }

        return false;
    }
}
