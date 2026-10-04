<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DummyAuthController extends Controller
{
    /**
     * Endpoint untuk simulasi login SSO secara lokal.
     * Dapat menerima input role (misal: ?role=Wali Asuh).
     */
    public function loginSimulator(Request $request)
    {
        $roleName = $request->query('role', 'Admin');
        
        // Cari role berdasarkan nama, jika tidak ada fallback ke Admin
        $role = Role::where('nama_role', strtolower(str_replace(' ', '_', $roleName)))->first();
        if (!$role) {
            $role = Role::firstOrCreate(['nama_role' => 'admin'], ['nama_role' => 'admin']);
        }

        // Cari user dengan role tersebut, atau user dummy pertama
        $user = User::where('role_id', $role->id)->first();
        
        if (!$user) {
            // Jika belum ada, buat user dummy on-the-fly
            $user = User::create([
                'nama' => 'Dummy ' . ucwords($roleName),
                'email' => strtolower(str_replace(' ', '', $roleName)) . '@example.com',
                'password' => bcrypt('password'),
                'role_id' => $role->id,
                // Secara default kosongkan unit_organisasi_id, atau isi jika ada request
                'unit_organisasi_id' => $request->query('unit_id', null),
            ]);
        } else {
            // Update unit_id jika dipass via query string
            if ($request->has('unit_id')) {
                $user->update(['unit_organisasi_id' => $request->query('unit_id')]);
            }
        }

        // Jika user adalah wali_santri atau santri dan belum terhubung ke santri manapun, otomatis hubungkan ke santri pertama
        if (in_array($role->nama_role, ['wali_santri', 'santri']) && !$user->santri_id && !$user->akses_wali_santri_id) {
            $firstSantri = \App\Models\Santri::first();
            if ($firstSantri) {
                $user->update([
                    'santri_id'            => $firstSantri->id,
                    'akses_wali_santri_id' => $firstSantri->id,
                ]);
            }
        }

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Berhasil login sebagai ' . $user->nama . ' via SSO Simulator.');
    }
}
