<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AspekPenilaianSeeder extends Seeder
{
    /**
     * Seed 15 aspek penilaian keseharian santri.
     * Data dari rubrik standar penilaian pesantren.
     * Setiap aspek memiliki skala 1-4:
     *   4 = Sangat Baik, 3 = Baik, 2 = Cukup, 1 = Perlu Pembinaan
     * Total skor maksimal = 15 × 4 = 60
     */
    public function run(): void
    {
        $aspek = [
            [
                'nomor_urut' => 1,
                'nama_aspek' => 'Kedisiplinan Waktu',
                'indikator'  => 'Ketepatan dalam mengikuti seluruh jadwal kegiatan pondok pesantren, termasuk bangun pagi, shalat berjamaah, kegiatan belajar, dan istirahat sesuai waktu yang telah ditentukan.',
            ],
            [
                'nomor_urut' => 2,
                'nama_aspek' => 'Kehadiran Shalat Berjamaah',
                'indikator'  => 'Keaktifan dan konsistensi dalam mengikuti shalat 5 waktu berjamaah di masjid/mushola pondok. Termasuk shalat Subuh, Dzuhur, Ashar, Maghrib, dan Isya.',
            ],
            [
                'nomor_urut' => 3,
                'nama_aspek' => 'Tilawah & Tahfidz Al-Quran',
                'indikator'  => 'Konsistensi dalam membaca Al-Quran harian (tilawah) dan progres menghafal Al-Quran (tahfidz) sesuai target yang ditetapkan oleh musyrif/musyrifah.',
            ],
            [
                'nomor_urut' => 4,
                'nama_aspek' => 'Adab & Akhlak',
                'indikator'  => 'Sopan santun dalam berinteraksi dengan ustadz/ustadzah, pengurus pondok, sesama santri, dan tamu. Meliputi tutur kata, sikap hormat, dan perilaku keseharian.',
            ],
            [
                'nomor_urut' => 5,
                'nama_aspek' => 'Kebersihan Diri',
                'indikator'  => 'Menjaga kebersihan badan (mandi teratur, memotong kuku, dll), kerapian berpakaian, dan kebersihan perlengkapan pribadi seperti tempat tidur dan lemari.',
            ],
            [
                'nomor_urut' => 6,
                'nama_aspek' => 'Kebersihan Lingkungan',
                'indikator'  => 'Partisipasi aktif dalam menjaga kebersihan kamar, asrama, kamar mandi, dan area umum pondok pesantren. Termasuk piket kebersihan sesuai jadwal.',
            ],
            [
                'nomor_urut' => 7,
                'nama_aspek' => 'Kemandirian',
                'indikator'  => 'Kemampuan mengurus kebutuhan pribadi tanpa bergantung pada orang lain, seperti mencuci pakaian, merapikan tempat tidur, dan mengelola uang saku.',
            ],
            [
                'nomor_urut' => 8,
                'nama_aspek' => 'Tanggung Jawab',
                'indikator'  => 'Kesungguhan dalam menjalankan tugas dan amanah yang diberikan, baik tugas belajar, piket, maupun peran organisasi. Menyelesaikan tugas tepat waktu.',
            ],
            [
                'nomor_urut' => 9,
                'nama_aspek' => 'Kerja Sama',
                'indikator'  => 'Kemampuan bekerja sama dalam kegiatan kelompok, gotong royong, dan kegiatan organisasi. Bersedia membantu teman dan tidak individualis.',
            ],
            [
                'nomor_urut' => 10,
                'nama_aspek' => 'Kepedulian Sosial',
                'indikator'  => 'Kepekaan terhadap kebutuhan dan kondisi sesama santri. Mau menjenguk teman yang sakit, membantu santri baru beradaptasi, dan berempati terhadap sesama.',
            ],
            [
                'nomor_urut' => 11,
                'nama_aspek' => 'Semangat Belajar',
                'indikator'  => 'Antusiasme dan motivasi dalam mengikuti kegiatan pembelajaran formal (madrasah/sekolah) dan non-formal (kajian, halaqah, kursus). Aktif bertanya dan berdiskusi.',
            ],
            [
                'nomor_urut' => 12,
                'nama_aspek' => 'Komunikasi',
                'indikator'  => 'Kemampuan berkomunikasi dengan baik, sopan, dan efektif. Mampu menyampaikan pendapat dengan santun, mendengarkan orang lain, dan menghindari ghibah/namimah.',
            ],
            [
                'nomor_urut' => 13,
                'nama_aspek' => 'Pengelolaan Emosi',
                'indikator'  => 'Kemampuan mengendalikan emosi, sabar dalam menghadapi masalah, dan menyelesaikan konflik dengan cara yang baik tanpa kekerasan fisik maupun verbal.',
            ],
            [
                'nomor_urut' => 14,
                'nama_aspek' => 'Kreativitas & Inisiatif',
                'indikator'  => 'Kemampuan memberikan ide dan inisiatif positif dalam kegiatan pondok, menunjukkan kreativitas dalam menyelesaikan tugas, dan proaktif dalam kegiatan ekstrakurikuler.',
            ],
            [
                'nomor_urut' => 15,
                'nama_aspek' => 'Ketaatan pada Peraturan',
                'indikator'  => 'Kepatuhan terhadap seluruh tata tertib pondok pesantren, termasuk aturan berpakaian, jam malam, larangan membawa barang terlarang, dan ketentuan izin keluar.',
            ],
        ];

        foreach ($aspek as $item) {
            DB::table('aspek_penilaian')->insert([
                'id'         => Str::uuid(),
                'nomor_urut' => $item['nomor_urut'],
                'nama_aspek' => $item['nama_aspek'],
                'indikator'  => $item['indikator'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
