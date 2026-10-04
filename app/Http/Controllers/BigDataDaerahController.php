<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CoreApiService;
use Illuminate\Pagination\LengthAwarePaginator;

class BigDataDaerahController extends Controller
{
    /**
     * Halaman Big Data Santri
     */
    public function index(Request $request)
    {
        $allowedUnitIds = $request->attributes->get('allowedUnitIds', []);
        $scopeMode = $request->attributes->get('scopeMode', 'unit');
        $showDaerahFilter = $request->user()->hasAnyRole(['admin', 'pengurus_wilayah', 'dewan_pemangku']);

        // Data Daerah dari API Core
        $allUnitsTree = CoreApiService::fetchUnitOrganisasi();
        $flatUnits = CoreApiService::flattenUnitOrganisasi($allUnitsTree);
        
        $daerahList = $flatUnits->filter(function($u) {
            return in_array($u->tipe ?? '', ['daerah', 'daerah_pendidikan']);
        })->when($scopeMode !== 'all', function($collection) use ($allowedUnitIds) {
            return $collection->whereIn('id', $allowedUnitIds);
        })->sortBy('nama')->unique('nama')->values();

        // Parameter filter dari URL
        $filters = [
            'search'     => $request->query('search'),
            'daerah_id'  => $request->query('daerah_id'),
            'kamar_id'   => $request->query('kamar_id'),
            'program_id' => $request->query('program_id'), // Tambahan Program
            'page'       => $request->query('page', 1),
        ];

        // Data Kamar dari API Core
        $allKamars = collect(CoreApiService::fetchKamar($filters['daerah_id']))->map(fn($k) => (object)$k);
        $kamarsForSelectedDaerah = $allKamars->when($scopeMode !== 'all', function($collection) use ($allowedUnitIds) {
            return $collection->whereIn('id', $allowedUnitIds);
        })->sortBy('nama')->unique('nama')->values();

        // Data Program dari API Core
        $programList = collect(CoreApiService::fetchProgram())->map(fn($p) => (object)$p)->sortBy('nama')->values();

        // Data Santri dari API Core
        $apiFilters = [
            'search'   => $filters['search'],
            'status'   => 'aktif',
            'page'     => $filters['page'],
            'per_page' => 10,
        ];
        
        if (!empty($filters['daerah_id']) && empty($filters['kamar_id'])) {
            $apiFilters['unit_organisasi_id'] = $filters['daerah_id'];
        }
        if (!empty($filters['kamar_id'])) {
            $apiFilters['kamar_id'] = $filters['kamar_id'];
        }
        if (!empty($filters['program_id'])) {
            $apiFilters['program_id'] = $filters['program_id']; // Asumsi API menerima param ini
        }

        $santriResponse = CoreApiService::fetchSantri($apiFilters);
        $rawSantriData = $santriResponse['data'] ?? [];

        $santriList = new LengthAwarePaginator(
            $rawSantriData,
            $santriResponse['total'] ?? 0,
            $santriResponse['per_page'] ?? 10,
            $santriResponse['current_page'] ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('big-data.index', compact(
            'daerahList', 
            'santriList', 
            'filters', 
            'kamarsForSelectedDaerah', 
            'showDaerahFilter',
            'programList'
        ));
    }
}
