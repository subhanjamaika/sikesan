<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SsoAuthController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  1. DUMMY LOGIN — Simulasi SSO via Query String (GET)
    //     Contoh: /dev/sso-login?user_id=xxx&role=wali_asuh&unit_id=yyy&nama=John&username=john123
    // ─────────────────────────────────────────────────────────────

    /**
     * Endpoint simulasi/testing lokal.
     * Menerima query string dan mensimulasikan payload seolah datang dari Main Core Zabitsa.
     */
    public function dummyLogin(Request $request)
    {
        // Hanya bisa diakses di environment lokal / staging
        if (app()->environment('production')) {
            abort(403, 'SSO Dummy Login tidak tersedia di production.');
        }

        // Normalisasi role untuk generate username default
        $roleName = $request->query('role', 'admin');
        $roleSlug = strtolower(str_replace(' ', '_', trim($roleName)));

        // Bangun payload dari query string
        $payload = [
            'id'                 => $request->query('user_id', \Illuminate\Support\Str::uuid()->toString()),
            'nama'               => $request->query('nama', 'Dummy User'),
            'username'           => $request->query('username', 'dummy_' . $roleSlug),
            'password'           => $request->query('password', 'password'),
            'role'               => $roleName,
            'unit_organisasi_id' => $request->query('unit_id'),
            'santri_id'          => $request->query('santri_id'),
        ];

        // Proses sinkronisasi & auto-login
        $user = $this->syncAndLogin($payload);

        return redirect()
            ->route('dashboard')
            ->with('success', "✅ [SSO Dummy] Berhasil login sebagai {$user->nama} (Role: {$user->role->nama_role})");
    }

    // ─────────────────────────────────────────────────────────────
    //  2. CALLBACK — Cangkang Penerima SSO Resmi (POST JSON)
    //     Endpoint: POST /auth/sso-callback
    //     Payload JSON: { id, nama, username, password, role, unit_organisasi_id }
    // ─────────────────────────────────────────────────────────────

    /**
     * Endpoint cangkang SSO resmi yang nanti dihubungkan ke Main Core Zabitsa.
     */
    public function callback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            return redirect('/')->with('error', 'SSO Login gagal: authorization code tidak ditemukan.');
        }

        // ── 1. Tukar Code Menjadi Token (Token Exchange) ──
        $clientId = config('services.core.client_id');
        $clientSecret = config('services.core.client_secret');

        $response = \Illuminate\Support\Facades\Http::asJson()->post(
            config('services.core.base_url', 'http://127.0.0.1:8000') . '/auth/token-exchange',
            [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]
        );

        if (!$response->successful()) {
            return redirect('/')->with('error', 'SSO Login gagal saat menukar token: ' . $response->json('message', 'Unknown error'));
        }

        $accessToken = $response->json('access_token');
        $refreshToken = $response->json('refresh_token');

        // ── 2. Decode JWT untuk mendapatkan identitas ──
        $payloadBase64 = explode('.', $accessToken)[1] ?? '';
        $decoded = json_decode(base64_decode($payloadBase64), true);

        if (!$decoded || !isset($decoded['sub'])) {
            return redirect('/')->with('error', 'SSO Login gagal: Payload JWT tidak valid.');
        }

        $syncPayload = [
            'id'                 => $decoded['sub'],
            'nama'               => $decoded['nama'] ?? 'Unknown',
            'username'           => $decoded['username'] ?? ($decoded['niup'] ?? 'sso_user'),
            'password'           => \Illuminate\Support\Str::random(16),
            'role'               => $decoded['role'] ?? 'admin',
            'unit_organisasi_id' => $decoded['unit_organisasi_id'] ?? null,
            'santri_id'          => $decoded['akses_wali_santri_id'] ?? $decoded['santri_id'] ?? null,
        ];

        // Proses sinkronisasi & auto-login
        $user = $this->syncAndLogin($syncPayload);

        // Simpan token ke session
        session([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);

        Log::info('[SSO Callback] User disinkronkan dan login berhasil.', [
            'user_id'  => $user->id,
            'username' => $user->username,
            'role'     => $user->role->nama_role,
        ]);

        return redirect()->route('dashboard')->with('success', "Berhasil login via SSO sebagai {$user->nama}.");
    }

    /**
     * Endpoint untuk memproses perpanjangan token (Rotasi Refresh Token)
     */
    public function refreshToken(Request $request)
    {
        $oldRefreshToken = session('refresh_token');
        if (!$oldRefreshToken) {
            return redirect('/login')->with('error', 'Sesi telah berakhir, silakan login ulang.');
        }

        $response = \Illuminate\Support\Facades\Http::asJson()->post(
            config('services.core.base_url', 'http://127.0.0.1:8000') . '/auth/refresh',
            [
                'refresh_token' => $oldRefreshToken,
                'client_id' => config('services.core.client_id'),
                'client_secret' => config('services.core.client_secret'),
            ]
        );

        if ($response->successful()) {
            $newAccessToken = $response->json('access_token');
            $newRefreshToken = $response->json('refresh_token');

            // Simpan refresh_token baru hasil rotasi
            session([
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken
            ]);

            return back()->with('success', 'Sesi berhasil diperbarui.');
        }

        // Jika refresh gagal (invalid/kadaluarsa/logout)
        Auth::logout();
        session()->flush();
        return redirect('/login')->with('error', 'Sesi tidak valid atau telah berakhir, silakan login ulang.');
    }

    // ─────────────────────────────────────────────────────────────
    //  PRIVATE: Logika Sinkronisasi & Auto-Login
    // ─────────────────────────────────────────────────────────────

    /**
     * Sinkronkan data user dari payload Main Core (atau dummy) ke database lokal,
     * lalu jalankan Auth::login() untuk membuat session aktif tanpa password.
     *
     * @param  array  $payload  ['id', 'nama', 'username', 'password', 'role', 'unit_organisasi_id']
     * @return \App\Models\User
     */
    private function syncAndLogin(array $payload): User
    {
        // ── 1. Resolve Role ──
        // Normalisasi nama role: "Wali Asuh" → "pengurus_sektor"
        $roleName = strtolower(str_replace(' ', '_', trim($payload['role'])));

        $role = Role::where('nama_role', $roleName)->first();

        if (!$role) {
            // Fallback: buat role baru jika belum ada (berguna untuk development)
            $role = Role::firstOrCreate(
                ['nama_role' => $roleName],
                ['nama_role' => $roleName]
            );

            Log::warning('[SSO Sync] Role baru dibuat otomatis.', [
                'nama_role' => $roleName,
            ]);
        }

        // ── 2. Sinkronisasi User (updateOrCreate berdasarkan UUID) ──
        $user = User::updateOrCreate(
            // Match berdasarkan id (UUID char(36))
            ['id' => $payload['id']],
            // Data yang selalu disinkronkan dari Main Core
            [
                'nama'               => $payload['nama'],
                'username'           => $payload['username'],
                'password'           => bcrypt($payload['password']),
                'role_id'            => $role->id,
                'unit_organisasi_id' => $payload['unit_organisasi_id'] ?? null,
                'santri_id'          => $payload['santri_id'] ?? null,
            ]
        );

        // ── 3. Auto-login (buat session aktif tanpa password) ──
        Auth::login($user);

        Log::info('[SSO Sync] Sinkronisasi berhasil.', [
            'user_id'   => $user->id,
            'username'  => $user->username,
            'nama'      => $user->nama,
            'role'      => $role->nama_role,
            'is_new'    => $user->wasRecentlyCreated,
        ]);

        return $user;
    }
}
