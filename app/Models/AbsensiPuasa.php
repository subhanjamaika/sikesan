<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AbsensiPuasa extends Model
{
    protected $table = 'absensi_puasa';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'santri_id',
        'tanggal',
        'pencatat_id',
        'is_archived',
        'status_puasa',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'     => 'date',
            'is_archived' => 'boolean',
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

    /**
     * Cek apakah suatu tanggal jatuh pada hari Senin atau Kamis.
     */
    public static function isHariPuasaValid($tanggal): bool
    {
        $hari = Carbon::parse($tanggal)->dayOfWeek;
        return in_array($hari, [Carbon::MONDAY, Carbon::THURSDAY]);
    }

    // ─── Relasi ───

    public function santri(): BelongsTo
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pencatat_id');
    }
}
