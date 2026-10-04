<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    /**
     * Seed 7 roles sesuai matriks hak akses Sistem 2.
     */
    public function run(): void
    {
        $roles = [
            ['id' => Str::uuid(), 'nama_role' => 'admin',            'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'nama_role' => 'dewan_pemangku',   'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'nama_role' => 'pengurus_wilayah', 'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'nama_role' => 'pengurus_sektor',  'created_at' => now(), 'updated_at' => now()],
            ['id' => Str::uuid(), 'nama_role' => 'pengurus_daerah',  'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('roles')->insert($roles);
    }
}
