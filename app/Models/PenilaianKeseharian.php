<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PenilaianKeseharian extends Model
{
    protected $table = 'penilaian_keseharian';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'santri_id',
        'wali_asuh_id',
        'periode',
        'tanggal_penilaian',
        'jumlah_skor',
        'nilai_akhir',
        'kategori',
        'catatan_kelebihan',
        'catatan_peningkatan',
        'rencana_tindak_lanjut',
        'catatan_pemangku',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penilaian' => 'date',
            'jumlah_skor'       => 'integer',
            'nilai_akhir'       => 'decimal:2',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    // ─── Relasi ───

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function waliAsuh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_asuh_id');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(PenilaianKeseharianDetail::class, 'penilaian_id');
    }

    // ─── Accessor & Kalkulasi ───

    /**
     * Hitung jumlah skor dari seluruh detail penilaian.
     * Digunakan saat store — SUM dari 15 detail skor.
     */
    public function hitungJumlahSkor(): int
    {
        return (int) $this->detail()->sum('skor');
    }

    /**
     * Hitung nilai akhir: (jumlah_skor / 60) * 100
     * 60 = skor maksimal (15 aspek × 4 poin)
     */
    public function hitungNilaiAkhir(?int $jumlahSkor = null): float
    {
        $skor = $jumlahSkor ?? $this->jumlah_skor;
        return round(($skor / 60) * 100, 2);
    }

    /**
     * Tentukan kategori berdasarkan nilai_akhir.
     * Rentang:
     *   >= 90  : istimewa
     *   >= 80  : sangat_baik
     *   >= 70  : baik
     *   >= 60  : cukup
     *   <  60  : perlu_pembinaan_intensif
     */
    public static function tentukanKategori(float $nilaiAkhir): string
    {
        return match (true) {
            $nilaiAkhir >= 90 => 'istimewa',
            $nilaiAkhir >= 80 => 'sangat_baik',
            $nilaiAkhir >= 70 => 'baik',
            $nilaiAkhir >= 60 => 'cukup',
            default           => 'perlu_pembinaan_intensif',
        };
    }

    /**
     * Hitung dan set otomatis jumlah_skor, nilai_akhir, dan kategori.
     * Dipanggil setelah detail disimpan.
     */
    public function hitungDanSimpan(): void
    {
        $this->jumlah_skor = $this->hitungJumlahSkor();
        $this->nilai_akhir = $this->hitungNilaiAkhir($this->jumlah_skor);
        $this->kategori    = self::tentukanKategori($this->nilai_akhir);
        $this->save();
    }

    // ─── Badge Helpers ───

    /**
     * Warna badge berdasarkan kategori.
     */
    public function getBadgeColorAttribute(): string
    {
        return match ($this->kategori) {
            'istimewa'                  => 'green',
            'sangat_baik'               => 'emerald',
            'baik'                      => 'yellow',
            'cukup'                     => 'amber',
            'perlu_pembinaan_intensif'  => 'red',
            default                     => 'gray',
        };
    }

    /**
     * Label kategori dalam bahasa Indonesia.
     */
    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'istimewa'                  => 'Istimewa',
            'sangat_baik'               => 'Sangat Baik',
            'baik'                      => 'Baik',
            'cukup'                     => 'Cukup',
            'perlu_pembinaan_intensif'  => 'Perlu Pembinaan Intensif',
            default                     => $this->kategori,
        };
    }
}
