<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Seed data demo: unit organisasi (Wilayah > 7 Daerah Diniyah > Sektor/Kamar), users, dan santri.
     * Data ini untuk keperluan testing dan development.
     *
     * ══════════════════════════════════════════════════════════════════
     * UPDATED: Selaras 100% dengan skema SSO Zabitsa (refactored).
     *  - Kolom daerah_binaan_id sudah TIDAK ADA di tabel users.
     *  - Tabel penugasan_wali_asuh sudah DIGANTI menjadi penugasan_pengurus.
     *  - Wali Asuh diberi unit_organisasi_id agar getUnitScope() berfungsi.
     * ══════════════════════════════════════════════════════════════════
     */
    public function run(): void
    {
        // ─── Unit Organisasi (Wilayah) ───
        $wilayahId = Str::uuid()->toString();

        // ─── 7 Unit Organisasi (Daerah) ───
        $daerahSifirId       = Str::uuid()->toString();
        $daerahIdadiyahId    = Str::uuid()->toString();
        $daerahTahsinId      = Str::uuid()->toString();
        $daerahAmtsilatiId   = Str::uuid()->toString();
        $daerahExcellentId   = Str::uuid()->toString();
        $daerahTahfidzId     = Str::uuid()->toString();
        $daerahAlMahfudziId  = Str::uuid()->toString();

        // ─── Unit Organisasi (Sektor / Kamar) ───
        $kamarSifir1     = Str::uuid()->toString();
        $kamarSifir2     = Str::uuid()->toString();
        $kamarIdadiyah1  = Str::uuid()->toString();
        $kamarTahsin1    = Str::uuid()->toString();
        $kamarAmtsilati1 = Str::uuid()->toString();

        DB::table('unit_organisasi')->insert([
            // Wilayah
            [
                'id'         => $wilayahId,
                'nama'       => 'Wilayah Zaid bin Tsabit',
                'tipe'       => 'wilayah',
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // 7 Daerah Diniyah di bawah Wilayah Zaid bin Tsabit
            [
                'id'         => $daerahSifirId,
                'nama'       => 'Daerah Sifir',
                'tipe'       => 'daerah',
                'parent_id'  => $wilayahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $daerahIdadiyahId,
                'nama'       => "Daerah I'dadiyah",
                'tipe'       => 'daerah',
                'parent_id'  => $wilayahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $daerahTahsinId,
                'nama'       => 'Daerah Tahsin',
                'tipe'       => 'daerah',
                'parent_id'  => $wilayahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $daerahAmtsilatiId,
                'nama'       => 'Daerah Amtsilati',
                'tipe'       => 'daerah',
                'parent_id'  => $wilayahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $daerahExcellentId,
                'nama'       => 'Daerah Excellent Language',
                'tipe'       => 'daerah',
                'parent_id'  => $wilayahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $daerahTahfidzId,
                'nama'       => 'Daerah Tahfidzul Qur\'an',
                'tipe'       => 'daerah',
                'parent_id'  => $wilayahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $daerahAlMahfudziId,
                'nama'       => 'Daerah Al-Mahfudzi',
                'tipe'       => 'daerah',
                'parent_id'  => $wilayahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Kamar (Tipe Sektor) di bawah Daerah
            [
                'id'         => $kamarSifir1,
                'nama'       => 'Kamar Sifir 01',
                'tipe'       => 'sektor',
                'parent_id'  => $daerahSifirId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $kamarSifir2,
                'nama'       => 'Kamar Sifir 02',
                'tipe'       => 'sektor',
                'parent_id'  => $daerahSifirId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $kamarIdadiyah1,
                'nama'       => "Kamar I'dadiyah 01",
                'tipe'       => 'sektor',
                'parent_id'  => $daerahIdadiyahId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $kamarTahsin1,
                'nama'       => 'Kamar Tahsin 01',
                'tipe'       => 'sektor',
                'parent_id'  => $daerahTahsinId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => $kamarAmtsilati1,
                'nama'       => 'Kamar Amtsilati 01',
                'tipe'       => 'sektor',
                'parent_id'  => $daerahAmtsilatiId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ─── Ambil role IDs ───
        $roles = DB::table('roles')->pluck('id', 'nama_role');

        // ─── Users UUIDs ───
        $adminId          = Str::uuid()->toString();
        $pengurusWilId    = Str::uuid()->toString();
        $dewanPemangkuId  = Str::uuid()->toString();
        
        $pSifirId         = Str::uuid()->toString();
        $pIdadiyahId      = Str::uuid()->toString();
        $pTahsinId        = Str::uuid()->toString();
        $pAmtsilatiId     = Str::uuid()->toString();
        $pExcellentId     = Str::uuid()->toString();
        $pTahfidzId       = Str::uuid()->toString();
        $pAlMahfudziId    = Str::uuid()->toString();

        $pengurusSektorId = Str::uuid()->toString();
        $waliSantriId     = Str::uuid()->toString();

        $defaultPassword = Hash::make('password');

        DB::table('users')->insert([
            [
                'id'                 => $adminId,
                'nama'               => 'Admin Sistem',
                'username'           => 'admin',
                'password'           => $defaultPassword,
                'role_id'            => $roles['admin'],
                'unit_organisasi_id' => $wilayahId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pengurusWilId,
                'nama'               => 'Ustadz Subhan (Pengurus Wilayah)',
                'username'           => 'pengurus.wilayah',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_wilayah'],
                'unit_organisasi_id' => $wilayahId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $dewanPemangkuId,
                'nama'               => 'Dewan Pemangku',
                'username'           => 'dewan.pemangku',
                'password'           => $defaultPassword,
                'role_id'            => $roles['dewan_pemangku'] ?? $roles['admin'],
                'unit_organisasi_id' => $wilayahId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pSifirId,
                'nama'               => 'Pengurus Daerah Sifir',
                'username'           => 'sifir',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_daerah'],
                'unit_organisasi_id' => $daerahSifirId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pIdadiyahId,
                'nama'               => "Pengurus Daerah I'dadiyah",
                'username'           => 'idadiyah',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_daerah'],
                'unit_organisasi_id' => $daerahIdadiyahId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pTahsinId,
                'nama'               => 'Pengurus Daerah Tahsin',
                'username'           => 'tahsin',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_daerah'],
                'unit_organisasi_id' => $daerahTahsinId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pAmtsilatiId,
                'nama'               => 'Pengurus Daerah Amtsilati',
                'username'           => 'amtsilati',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_daerah'],
                'unit_organisasi_id' => $daerahAmtsilatiId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pExcellentId,
                'nama'               => 'Pengurus Daerah Excellent Language',
                'username'           => 'excellent',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_daerah'],
                'unit_organisasi_id' => $daerahExcellentId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pTahfidzId,
                'nama'               => 'Pengurus Daerah Tahfidzul Qur\'an',
                'username'           => 'tahfidz',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_daerah'],
                'unit_organisasi_id' => $daerahTahfidzId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pAlMahfudziId,
                'nama'               => 'Pengurus Daerah Al-Mahfudzi',
                'username'           => 'almahfudzi',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_daerah'],
                'unit_organisasi_id' => $daerahAlMahfudziId,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $pengurusSektorId,
                'nama'               => 'Ustadz Edi (Pengurus Sektor)',
                'username'           => 'sektor',
                'password'           => $defaultPassword,
                'role_id'            => $roles['pengurus_sektor'] ?? $roles['admin'],
                'unit_organisasi_id' => $kamarSifir1,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $waliSantriId,
                'nama'               => 'Bapak Fauzi (Wali Santri)',
                'username'           => 'wali.santri',
                'password'           => $defaultPassword,
                'role_id'            => $roles['wali_santri'] ?? $roles['admin'],
                'unit_organisasi_id' => null,
                'santri_id'          => null,
                'remember_token'     => Str::random(10),
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);

        // ─── Santri ───
        $santri1Id = Str::uuid()->toString();
        $santri2Id = Str::uuid()->toString();
        $santri3Id = Str::uuid()->toString();
        $santri4Id = Str::uuid()->toString();
        $santri5Id = Str::uuid()->toString();

        $programAwwaliyahId = DB::table('programs')->where('nama_program', 'Program Awwaliyah')->value('id');

        DB::table('santri')->insert([
            [
                'id'                 => $santri1Id,
                'nis_induk'          => '2024001',
                'nama'               => 'Muhammad Abdullah',
                'wali_asuh_id'       => $pTahsinId,
                'unit_organisasi_id' => $kamarTahsin1,
                'program_id'         => $programAwwaliyahId,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $santri2Id,
                'nis_induk'          => '2024002',
                'nama'               => 'Ahmad Zaki',
                'wali_asuh_id'       => $pSifirId,
                'unit_organisasi_id' => $kamarSifir1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $santri3Id,
                'nis_induk'          => '2024003',
                'nama'               => 'Faris Rahman',
                'wali_asuh_id'       => $pSifirId,
                'unit_organisasi_id' => $kamarSifir2,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $santri4Id,
                'nis_induk'          => '2024004',
                'nama'               => 'Bilal Hassan',
                'wali_asuh_id'       => $pIdadiyahId,
                'unit_organisasi_id' => $kamarIdadiyah1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => $santri5Id,
                'nis_induk'          => '2024005',
                'nama'               => 'Umar Al-Faruq',
                'wali_asuh_id'       => $pTahsinId,
                'unit_organisasi_id' => $kamarTahsin1,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);

        // Update User Wali Santri dengan santri_id
        DB::table('users')->where('id', $waliSantriId)->update(['santri_id' => $santri5Id]);

        // ─── Pelanggaran Demo ───
        $pelanggaran1Id = Str::uuid()->toString();
        $pelanggaran2Id = Str::uuid()->toString();

        DB::table('pelanggaran')->insert([
            [
                'id'          => $pelanggaran1Id,
                'santri_id'   => $santri1Id,
                'pencatat_id' => $pSifirId, // menggunakan pSifirId
                'kategori'    => 'devisi',
                'tingkat'     => 'sektor',
                'deskripsi'   => 'Terlambat mengikuti jamaah sholat subuh.',
                'tanggal'     => now()->subDays(2),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => $pelanggaran2Id,
                'santri_id'   => $santri3Id,
                'pencatat_id' => $pSifirId,
                'kategori'    => 'umum',
                'tingkat'     => 'daerah',
                'deskripsi'   => 'Tidak melaksanakan piket kamar sesuai jadwal.',
                'tanggal'     => now()->subDays(5),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // ─── Pembinaan Demo ───
        DB::table('pembinaan')->insert([
            [
                'id'              => Str::uuid()->toString(),
                'pelanggaran_id'  => $pelanggaran1Id,
                'pembina_id'      => $pSifirId,
                'jenis_pembinaan' => 'Membaca Al-Qur\'an 1 Juz di teras kamar.',
                'tanggal'         => now()->subDay(),
                'hasil'           => 'Santri kooperatif dan sudah menyelesaikan sanksi.',
                'created_at'      => now(),
                'updated_at'      => now(),
            ],
        ]);

        // ─── Penilaian Keseharian Demo ───
        $aspekIds = DB::table('aspek_penilaian')->pluck('id', 'nama_aspek');
        $penilaian1Id = Str::uuid()->toString();

        DB::table('penilaian_keseharian')->insert([
            [
                'id'                     => $penilaian1Id,
                'santri_id'              => $santri1Id,
                'wali_asuh_id'           => $pSifirId,
                'periode'                => now()->format('Y-m'),
                'tanggal_penilaian'      => now(),
                'jumlah_skor'            => 52,
                'nilai_akhir'            => 86.67,
                'kategori'               => 'sangat_baik',
                'catatan_kelebihan'      => 'Rajin jamaah dan kebersihan kamar teratur.',
                'catatan_peningkatan'    => 'Meningkatkan hafalan surah pendek.',
                'rencana_tindak_lanjut'  => 'Pendampingan khusus tajwid.',
                'created_at'             => now(),
                'updated_at'             => now(),
            ],
        ]);

        if ($aspekIds->isNotEmpty()) {
            $details = [];
            foreach ($aspekIds as $nama => $aspekId) {
                $details[] = [
                    'id'           => Str::uuid()->toString(),
                    'penilaian_id' => $penilaian1Id,
                    'aspek_id'     => $aspekId,
                    'skor'         => rand(3, 4),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            }
            DB::table('penilaian_keseharian_detail')->insert($details);
        }
    }
}
