<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsensiPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Semua role bisa melihat (Read-Only)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, $model = null): bool
    {
        // Semua role bisa melihat detail
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'daerah']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, $model = null): bool
    {
        return $user->hasRole(['admin', 'daerah']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, $model = null): bool
    {
        return $user->hasRole(['admin', 'daerah']);
    }
}
