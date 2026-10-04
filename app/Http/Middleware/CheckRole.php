<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Middleware untuk memeriksa role user.
     * Penggunaan: middleware('role:admin,wali_asuh,...')
     *
     * Desain: mudah diganti ke SSO middleware nanti tanpa ubah controller/view.
     *
     * @param string $roles  Comma-separated list of allowed role names
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            abort(403, 'Akses ditolak: role tidak ditemukan.');
        }

        if (!in_array($user->role->nama_role, $roles)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
