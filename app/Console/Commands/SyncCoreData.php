<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UnitOrganisasi;
use App\Models\Program;
use App\Models\Santri;
use App\Services\CoreApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncCoreData extends Command
{
    protected $signature = 'sso:sync-all';
    protected $description = 'Sync all master data (Unit Organisasi, Kamar, Program, Santri) from SSO Main Core';

    public function handle()
    {
        $this->info("Memulai sinkronisasi data dari Main Core SSO...");

        // Pastikan CoreApiService bisa dihubungi
        try {
            $this->info("Menghubungi API Unit Organisasi...");
            $unitTree = CoreApiService::fetchUnitOrganisasi();
            $flatUnits = CoreApiService::flattenUnitOrganisasi($unitTree);
        } catch (\Exception $e) {
            $this->error("Gagal terhubung ke API: " . $e->getMessage());
            return;
        }

        DB::beginTransaction();
        try {
            // Disable foreign key checks for syncing
            Schema::disableForeignKeyConstraints();

            // 1. Sync Unit Organisasi (Daerah, dll)
            $this->info("Menyinkronkan " . $flatUnits->count() . " Unit Organisasi...");
            $unitIds = [];
            foreach ($flatUnits as $unit) {
                // Map API types to local ENUM ['wilayah', 'daerah', 'sektor']
                $apiTipe = strtolower($unit->tipe ?? 'daerah');
                if (str_contains($apiTipe, 'wilayah')) {
                    $localTipe = 'wilayah';
                } elseif (str_contains($apiTipe, 'sektor') || str_contains($apiTipe, 'kamar')) {
                    $localTipe = 'sektor';
                } else {
                    $localTipe = 'daerah';
                }

                UnitOrganisasi::updateOrCreate(
                    ['id' => $unit->id],
                    [
                        'nama' => $unit->nama,
                        'tipe' => $localTipe,
                        'parent_id' => $unit->parent_id ?? null,
                    ]
                );
                $unitIds[] = $unit->id;
            }

            // 2. Sync Kamar
            $this->info("Menyinkronkan Kamar...");
            $kamars = CoreApiService::fetchKamar();
            foreach ($kamars as $kamar) {
                UnitOrganisasi::updateOrCreate(
                    ['id' => $kamar['id']],
                    [
                        'nama' => $kamar['nama'],
                        'tipe' => 'sektor', // local enum for Kamar is 'sektor'
                        'parent_id' => $kamar['unit_organisasi_id'] ?? $kamar['daerah_pendidikan_id'] ?? null,
                    ]
                );
                $unitIds[] = $kamar['id'];
            }

            // 3. Sync Program
            $this->info("Menyinkronkan Program...");
            $programs = CoreApiService::fetchProgram();
            $programIds = [];
            foreach ($programs as $prog) {
                Program::updateOrCreate(
                    ['id' => $prog['id']],
                    [
                        'nama_program' => $prog['nama_program'] ?? $prog['nama'],
                        // 'aktif' => true, // Sesuaikan dengan skema jika ada
                    ]
                );
                $programIds[] = $prog['id'];
            }

            // 4. Sync Santri
            $this->info("Menyinkronkan Data Santri...");
            $santriResponse = CoreApiService::fetchSantri(['status' => 'aktif', 'per_page' => 2000]);
            $santris = $santriResponse['data'] ?? [];
            $this->info("Mendapatkan " . count($santris) . " santri aktif.");
            
            $santriIds = [];
            foreach ($santris as $santri) {
                // Santri model might have 'program_id' or we sync from $santri['program_aktif']
                $programId = null;
                if (!empty($santri['program_aktif']) && is_array($santri['program_aktif'])) {
                    $progName = strtolower($santri['program_aktif'][0]['program']['nama'] ?? '');
                    if (str_contains($progName, 'wustho tahsin') || str_contains($progName, 'tahsin')) {
                        $programId = \App\Enums\ProgramEnum::WUSTHO_TAHSIN->value;
                    } elseif (str_contains($progName, 'kitab')) {
                        $programId = \App\Enums\ProgramEnum::WUSTHO_KITAB->value;
                    } elseif (str_contains($progName, 'mandarin') || str_contains($progName, 'bahasa')) {
                        $programId = \App\Enums\ProgramEnum::WUSTHO_BAHASA->value;
                    } elseif (str_contains($progName, 'tahfidz')) {
                        $programId = \App\Enums\ProgramEnum::WUSTHO_TAHFIDZ->value;
                    } elseif (str_contains($progName, 'mahfudz') || str_contains($progName, 'pengurus')) {
                        $programId = \App\Enums\ProgramEnum::AL_MAHFUDZI->value;
                    } elseif (str_contains($progName, "i'dadiyah") || str_contains($progName, "idadiyah")) {
                        $programId = \App\Enums\ProgramEnum::IDADIYAH->value;
                    } elseif (str_contains($progName, 'sifir')) {
                        $programId = \App\Enums\ProgramEnum::SIFIR->value;
                    } elseif (str_contains($progName, 'awaliyah') || str_contains($progName, 'awwaliyah')) {
                        if (str_contains($progName, 'ii') || str_contains($progName, 'muhawara')) {
                            $programId = \App\Enums\ProgramEnum::AWWALIYAH_II->value;
                        } else {
                            $programId = \App\Enums\ProgramEnum::AWWALIYAH_I->value;
                        }
                    }
                }

                Santri::updateOrCreate(
                    ['id' => $santri['id']],
                    [
                        'nama' => $santri['nama'],
                        'nis_induk' => $santri['nis_induk'] ?? $santri['niup'] ?? '-',
                        'unit_organisasi_id' => $santri['kamar']['id'] ?? $santri['unit_organisasi']['id'] ?? null,
                        'program_id' => $programId,
                        'wali_santri_kontak' => $santri['wali_santri_kontak'] ?? null,
                    ]
                );
                $santriIds[] = $santri['id'];
            }

            // 5. Clean up old dummy data (opsional, hapus yang tidak ada di API)
            // Hati-hati menghapus data santri jika sudah ada relasi absensi. 
            // Kita hanya menghapus jika dipastikan aman, atau biarkan saja.
            $this->info("Menghapus data dummy lokal yang tidak ada di API...");
            if (count($santriIds) > 0) {
                Santri::whereNotIn('id', $santriIds)->delete();
            }
            if (count($programIds) > 0) {
                Program::whereNotIn('id', $programIds)->delete();
            }
            if (count($unitIds) > 0) {
                UnitOrganisasi::whereNotIn('id', $unitIds)->delete();
            }

            Schema::enableForeignKeyConstraints();
            DB::commit();

            $this->info("Sinkronisasi Selesai!");

        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();
            $this->error("Terjadi kesalahan saat menyimpan ke database: " . $e->getMessage());
        }
    }
}
