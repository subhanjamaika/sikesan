<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JenisKegiatanSeeder extends Seeder
{
    /**
     * Seed master data jenis_kegiatan (7 baris).
     */
    public function run(): void
    {
        $kegiatan = [
            [
                'nama_kegiatan'   => 'Sholat Tahajjud',
                'jam_pelaksanaan' => '03:00:00',
                'urutan'          => 1,
            ],
            [
                'nama_kegiatan'   => 'Kegiatan Pagi',
                'jam_pelaksanaan' => '05:30:00',
                'urutan'          => 2,
            ],
            [
                'nama_kegiatan'   => 'Sholat Dhuha',
                'jam_pelaksanaan' => '07:00:00',
                'urutan'          => 3,
            ],
            [
                'nama_kegiatan'   => 'Kegiatan Sore',
                'jam_pelaksanaan' => '16:00:00',
                'urutan'          => 4,
            ],
            [
                'nama_kegiatan'   => 'Kegiatan Ba\'da Maghrib',
                'jam_pelaksanaan' => '18:30:00',
                'urutan'          => 5,
            ],
            [
                'nama_kegiatan'   => 'Kegiatan 20:00',
                'jam_pelaksanaan' => '20:00:00',
                'urutan'          => 6,
            ],
            [
                'nama_kegiatan'   => 'Kegiatan 21:00',
                'jam_pelaksanaan' => '21:00:00',
                'urutan'          => 7,
            ],
        ];

        foreach ($kegiatan as $item) {
            DB::table('jenis_kegiatan')->updateOrInsert(
                ['nama_kegiatan' => $item['nama_kegiatan']],
                [
                    'id'              => Str::uuid()->toString(),
                    'jam_pelaksanaan' => $item['jam_pelaksanaan'],
                    'urutan'          => $item['urutan'],
                    'aktif'           => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }
    }
}
