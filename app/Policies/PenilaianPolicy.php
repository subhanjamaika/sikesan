<?php

namespace App\Policies;

use App\Models\PenilaianKeseharian;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PenilaianPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PenilaianKeseharian $penilaian = null): bool
    {
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
    public function update(User $user, PenilaianKeseharian $penilaian = null): bool
    {
        return $user->hasRole(['admin', 'daerah']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PenilaianKeseharian $penilaian = null): bool
    {
        return $user->hasRole(['admin', 'daerah']);
    }
}
