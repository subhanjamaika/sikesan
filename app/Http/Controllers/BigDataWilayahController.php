<?php

namespace App\Http\Controllers;

use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BigDataWilayahController extends Controller
{
    /**
     * Halaman Big Data Wilayah Zaid bin Tsabit — Tampilan Tabel Pokok Santri + Filter Bar.
     */
    public function index(Request $request)
    {
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $showDaerahFilter = $request->user()->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']);

        // Data Daerah dari API Core
        $allUnitsTree = \App\Services\CoreApiService::fetchUnitOrganisasi();
        $flatUnits = \App\Services\CoreApiService::flattenUnitOrganisasi($allUnitsTree);
        
        $daerahList = $flatUnits->filter(function($u) {
            return in_array($u->tipe ?? '', ['daerah', 'daerah_pendidikan']);
        })->when($scopeMode !== 'all', function($collection) use ($allowedUnitIds) {
            return $collection->whereIn('id', $allowedUnitIds);
        })->sortBy('nama')->unique('nama')->values();

        // Parameter filter dari URL (?search=...&daerah_id=...&kamar_id=...)
        $filters = [
            'search'     => $request->query('search'),
            'daerah_id'  => $request->query('daerah_id'),
            'kamar_id'   => $request->query('kamar_id'),
            'page'       => $request->query('page', 1),
        ];

        // Data Kamar dari API Core
        $allKamars = collect(\App\Services\CoreApiService::fetchKamar($filters['daerah_id']))->map(fn($k) => (object)$k);
        $kamarsForSelectedDaerah = $allKamars->when($scopeMode !== 'all', function($collection) use ($allowedUnitIds) {
            return $collection->whereIn('id', $allowedUnitIds);
        })->sortBy('nama')->unique('nama')->values();

        // Data Santri dari API Core
        $apiFilters = [
            'search'   => $filters['search'],
            'status'   => 'aktif',
            'page'     => $filters['page'],
            'per_page' => 10,
        ];
        
        // Scope Data (Role Based Access Control)
        if ($scopeMode !== 'all') {
            $userRole = $request->user();
            if ($userRole->hasRole(['pengurus_daerah', 'daerah'])) {
                $apiFilters['unit_organisasi_id'] = $userRole->unit_organisasi_id;
            } elseif ($userRole->hasRole(['pengurus_sektor', 'sektor'])) {
                $apiFilters['kamar_id'] = $userRole->unit_organisasi_id;
            }
        }
        
        if (!empty($filters['daerah_id']) && empty($filters['kamar_id'])) {
            $apiFilters['unit_organisasi_id'] = $filters['daerah_id'];
        }
        if (!empty($filters['kamar_id'])) {
            $apiFilters['kamar_id'] = $filters['kamar_id'];
        }

        $santriResponse = \App\Services\CoreApiService::fetchSantri($apiFilters);
        $rawSantriData = $santriResponse['data'] ?? [];

        $santriList = new \Illuminate\Pagination\LengthAwarePaginator(
            $rawSantriData,
            $santriResponse['total'] ?? 0,
            $santriResponse['per_page'] ?? 10,
            $santriResponse['current_page'] ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('big-data.index', compact('daerahList', 'santriList', 'filters', 'kamarsForSelectedDaerah', 'showDaerahFilter'));
    }

    /**
     * Integrasi dengan tabel santri lokal
     */
    public function fetchSantriFromApi(array $filters, array $allowedUnitIds = [], string $scopeMode = 'all'): LengthAwarePaginator
    {
        $query = \App\Models\Santri::with(['unitOrganisasi.parent.parent']);

        // Filter Scope User
        if ($scopeMode !== 'all' && !empty($allowedUnitIds)) {
            $query->whereIn('unit_organisasi_id', $allowedUnitIds);
            // also need to handle if allowedUnitIds contains daerah, but santri belongs to kamar.
            // for now assume allowedUnitIds includes all kamar ids.
        }

        // Filter Daerah (Santri belongs to kamar, kamar belongs to daerah)
        if (!empty($filters['daerah_id'])) {
            $query->whereHas('unitOrganisasi', function($q) use ($filters) {
                $q->where('parent_id', $filters['daerah_id']);
            });
        }


        // Filter Kamar
        if (!empty($filters['kamar_id'])) {
            $query->where('unit_organisasi_id', $filters['kamar_id']);
        }

        // Filter Search
        if (!empty($filters['search'])) {
            $keyword = strtolower($filters['search']);
            $query->where(function($q) use ($keyword) {
                $q->where('nama', 'like', "%{$keyword}%")
                  ->orWhere('nis_induk', 'like', "%{$keyword}%");
            });
        }

        $page = (int) ($filters['page'] ?? 1);
        $perPage = 10;
        
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $mappedItems = $paginator->getCollection()->map(function ($santri) {
            $kamar = $santri->unitOrganisasi;
            $daerah = $kamar ? $kamar->parent : null;
            $wilayah = $daerah ? $daerah->parent : null;
            
            return [
                'id'         => $santri->id,
                'nis'        => $santri->nis_induk ?? '-',
                'nama'       => $santri->nama,
                'daerah_id'  => $daerah ? $daerah->id : null,
                'daerah'     => $daerah ? $daerah->nama : '-',
                'kamar_id'   => $kamar ? $kamar->id : null,
                'kamar'      => $kamar ? $kamar->nama : '-',
                'wilayah'    => $wilayah ? $wilayah->nama : 'Wilayah Zaid bin Tsabit',
                'nik'        => '-', // Not used much
            ];
        });

        $paginator->setCollection($mappedItems);
        $paginator->appends($filters);

        return $paginator;
    }


}
