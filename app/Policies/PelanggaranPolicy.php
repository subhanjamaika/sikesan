<?php

namespace App\Policies;

use App\Models\Pelanggaran;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PelanggaranPolicy
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
    public function view(User $user, Pelanggaran $pelanggaran = null): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Sektor diberikan hak akses penuh di modul Disiplin/Pelanggaran
        return $user->hasRole(['admin', 'daerah', 'sektor']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pelanggaran $pelanggaran = null): bool
    {
        return $user->hasRole(['admin', 'daerah', 'sektor']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pelanggaran $pelanggaran = null): bool
    {
        return $user->hasRole(['admin', 'daerah', 'sektor']);
    }

    /**
     * Custom method for adding Pembinaan
     */
    public function addPembinaan(User $user, Pelanggaran $pelanggaran = null): bool
    {
        return $user->hasRole(['admin', 'daerah', 'sektor']);
    }
}
