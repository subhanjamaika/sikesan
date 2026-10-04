<?php

namespace App\Policies;

use App\Models\Santri;
use App\Models\User;

class SantriPolicy
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
        return $user->hasRole(['pengurus_sektor', 'pengurus_daerah', 'pengurus_wilayah']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Santri $santri): bool
    {
        if ($user->hasRole('pengurus_sektor')) {
            return in_array($santri->unit_organisasi_id, $user->getAllowedUnitIds());
        }

        if ($user->hasRole(['pengurus_daerah', 'pengurus_wilayah'])) {
            return in_array($santri->unit_organisasi_id, $user->getUnitScope());
        }

        if ($user->hasRole('wali_santri')) {
            return $user->santri_id !== null && $user->santri_id === $santri->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('pengurus_sektor');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Santri $santri): bool
    {
        if ($user->hasRole('pengurus_sektor')) {
            return in_array($santri->unit_organisasi_id, $user->getAllowedUnitIds());
        }

        if ($user->hasRole(['pengurus_daerah', 'pengurus_wilayah'])) {
            return in_array($santri->unit_organisasi_id, $user->getUnitScope());
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Santri $santri): bool
    {
        if ($user->hasRole('pengurus_sektor')) {
            return in_array($santri->unit_organisasi_id, $user->getAllowedUnitIds());
        }

        if ($user->hasRole(['pengurus_daerah', 'pengurus_wilayah'])) {
            return in_array($santri->unit_organisasi_id, $user->getUnitScope());
        }

        return false;
    }
}
