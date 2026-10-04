<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CoreApiService
{
    /**
     * Dapatkan base URL dari konfigurasi.
     */
    protected static function getBaseUrl()
    {
        return env('CORE_SERVICE_BASE_URL', 'https://sso.zabitsa.site');
    }

    /**
     * Membangun HTTP client dengan header standar REST API (Service Key).
     */
    protected static function client()
    {
        return Http::withHeaders([
            'X-Service-Key' => env('CORE_SERVICE_KEY', config('services.core.api_key')),
            'Accept'        => 'application/json',
        ])->baseUrl(self::getBaseUrl());
    }

    /**
     * Fetch Unit Organisasi (Tree)
     * GET /api/unit-organisasi
     */
    public static function fetchUnitOrganisasi()
    {
        try {
            $response = self::client()->get('/api/unit-organisasi');
            if ($response->successful()) {
                return $response->json();
            }
            Log::error('[CoreApiService] Failed to fetch Unit Organisasi', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('[CoreApiService] Exception when fetching Unit Organisasi: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Meratakan (flatten) tree Unit Organisasi menjadi array 1 dimensi (Collection of objects)
     */
    public static function flattenUnitOrganisasi($tree)
    {
        $flat = collect();
        foreach ($tree as $node) {
            $children = $node['children'] ?? [];
            unset($node['children']);
            $flat->push((object) $node);
            
            if (count($children) > 0) {
                $flat = $flat->merge(self::flattenUnitOrganisasi($children));
            }
        }
        return $flat;
    }

    /**
     * Fetch Kamar
     * GET /api/kamar
     */
    public static function fetchKamar($daerahId = null)
    {
        try {
            $params = ['per_page' => 500]; // Tarik banyak agar cukup untuk dropdown
            if ($daerahId) {
                $params['daerah_pendidikan_id'] = $daerahId;
            }
            $response = self::client()->get('/api/kamar', $params);
            
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
            Log::error('[CoreApiService] Failed to fetch Kamar', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('[CoreApiService] Exception when fetching Kamar: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch Program
     * GET /api/program
     */
    public static function fetchProgram()
    {
        try {
            $response = self::client()->get('/api/program');
            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
            Log::error('[CoreApiService] Failed to fetch Program', ['status' => $response->status(), 'body' => $response->body()]);
            return [];
        } catch (\Exception $e) {
            Log::error('[CoreApiService] Exception when fetching Program: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch Santri
     * GET /api/santri
     */
    public static function fetchSantri($filters = [])
    {
        try {
            $response = self::client()->get('/api/santri', $filters);
            
            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('[CoreApiService] Failed to fetch Santri', ['status' => $response->status(), 'body' => $response->body()]);
            return [
                'data' => [],
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1
            ];
        } catch (\Exception $e) {
            Log::error('[CoreApiService] Exception when fetching Santri: ' . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'per_page' => 10,
                'current_page' => 1
            ];
        }
    }
}
