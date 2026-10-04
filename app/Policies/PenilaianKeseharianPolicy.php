<?php

namespace App\Policies;

use App\Models\PenilaianKeseharian;
use App\Models\User;

class PenilaianKeseharianPolicy
{
    /**
     * Matriks Hak Akses - Penilaian Keseharian & Perlengkapan:
     * Admin            → full akses
     * Pengurus Wilayah → lihat semua (read-only)
     * Kabag Daerah     → lihat (lingkup daerah)
     * Pengurus BKWA    → tidak ada akses
     * Wali Asuh        → input + lihat (santri asuhannya saja)
     * Kamtib Sektor    → tidak ada akses
     * Wali Santri      → lihat saja (anak sendiri, read-only)
     */

    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasAnyRole(['admin', 'dewan_pemangku'])) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole([
            'admin', 'pengurus_wilayah', 'pengurus_daerah',
            'pengurus_sektor', 'wali_santri',
        ]);
    }

    public function view(User $user, PenilaianKeseharian $penilaian): bool
    {
        if ($user->hasRole(['admin', 'pengurus_wilayah'])) {
            return true;
        }

        if ($user->hasRole('pengurus_daerah')) {
            $unitScope = $user->getUnitScope();
            return in_array($penilaian->santri->unit_organisasi_id, $unitScope);
        }

        if ($user->hasRole('pengurus_sektor')) {
            return $penilaian->santri->wali_asuh_id === $user->id;
        }

        if ($user->hasRole('wali_santri')) {
            $santriId = $user->santri_id ?? $user->akses_wali_santri_id;
            return $santriId !== null && $santriId === $penilaian->santri_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'pengurus_sektor', 'pengurus_daerah']);
    }

    public function update(User $user, PenilaianKeseharian $penilaian): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('pengurus_daerah')) {
            $unitScope = $user->getUnitScope();
            return in_array($penilaian->santri->unit_organisasi_id, $unitScope);
        }

        if ($user->hasRole('pengurus_sektor')) {
            return $penilaian->wali_asuh_id === $user->id;
        }

        return false;
    }

    public function delete(User $user, PenilaianKeseharian $penilaian): bool
    {
        return $user->hasRole('admin');
    }
}
