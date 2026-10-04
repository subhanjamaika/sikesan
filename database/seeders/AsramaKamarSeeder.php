<?php

namespace Database\Seeders;

use App\Models\Asrama;
use App\Models\Kamar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AsramaKamarSeeder extends Seeder
{
    /**
     * Seed 7 Asrama Diniyah + contoh Kamar untuk filter UI.
     */
    public function run(): void
    {
        $asramaList = [
            'Sifir'          => ['Kamar A1', 'Kamar A2', 'Kamar A3'],
            "I'dadiyah"      => ['Kamar B1', 'Kamar B2'],
            'Awwaliyah'      => ['Kamar C1', 'Kamar C2', 'Kamar C3'],
            'Wustho Kitab'   => ['Kamar D1', 'Kamar D2'],
            'Wustho Bahasa'  => ['Kamar E1', 'Kamar E2', 'Kamar E3'],
            'Wustho Tahsin'  => ['Kamar F1', 'Kamar F2'],
            'Wustho Tahfidz' => ['Kamar G1', 'Kamar G2', 'Kamar G3'],
        ];

        foreach ($asramaList as $namaAsrama => $kamars) {
            $asrama = Asrama::where('nama', $namaAsrama)->first();
            if (!$asrama) {
                $asrama = Asrama::create([
                    'id'   => Str::uuid()->toString(),
                    'nama' => $namaAsrama,
                ]);
            }

            foreach ($kamars as $namaKamar) {
                $kamar = Kamar::where('nama', $namaKamar)->where('asrama_id', $asrama->id)->first();
                if (!$kamar) {
                    Kamar::create([
                        'id'        => Str::uuid()->toString(),
                        'nama'      => $namaKamar,
                        'asrama_id' => $asrama->id,
                    ]);
                }
            }
        }
    }
}
