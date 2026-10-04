<?php

namespace App\Enums;

enum ProgramEnum: string
{
    // I'dadiyah
    case IDADIYAH = 'idadiyah';
    case SIFIR = 'sifir';

    // Tahsin
    case WUSTHO_TAHSIN = 'wustho_tahsin';
    
    // Amtsilati
    case AWWALIYAH_I = 'awwaliyah_i';
    case WUSTHO_KITAB = 'wustho_kitab';
    
    // Excellent Language
    case AWWALIYAH_II = 'awwaliyah_ii';
    case WUSTHO_BAHASA = 'wustho_bahasa';
    
    // Tahfidzul Qur'an
    case WUSTHO_TAHFIDZ = 'wustho_tahfidz';
    
    // Al-Mahfudzi
    case AL_MAHFUDZI = 'al_mahfudzi';

    public function label(): string
    {
        return match($this) {
            self::IDADIYAH => "I'dadiyah",
            self::WUSTHO_TAHSIN => "Wustho Tahsin",
            self::AWWALIYAH_I => "Awwaliyah I",
            self::WUSTHO_KITAB => "Wustho Kitab",
            self::AWWALIYAH_II => "Awwaliyah II",
            self::WUSTHO_BAHASA => "Wustho Bahasa",
            self::WUSTHO_TAHFIDZ => "Tahfidzul Qur'an",
            self::AL_MAHFUDZI => "Al-Mahfudzi",
            self::SIFIR => "Sifir",
        };
    }

    /**
     * Mengembalikan daftar Enum Program berdasarkan nama Daerah (Unit Organisasi)
     */
    public static function forDaerah(string $daerahName): array
    {
        return match($daerahName) {
            "Tahsinul Qiro'ah" => [self::IDADIYAH, self::WUSTHO_TAHSIN],
            "Amtsilati" => [self::AWWALIYAH_I, self::WUSTHO_KITAB],
            "Excellent Language" => [self::AWWALIYAH_II, self::WUSTHO_BAHASA],
            "Tahfidzul Qur'an" => [self::WUSTHO_TAHFIDZ],
            "Al-Mahfudzi" => [self::AL_MAHFUDZI],
            "I'dadiyah" => [self::SIFIR],
            default => [],
        };
    }

    /**
     * Mengembalikan daftar string program ID (misalnya: wustho_tahsin) untuk query WhereIn.
     */
    public static function getProgramIdsForDaerah(string $daerahName): array
    {
        return array_map(fn($enum) => $enum->value, self::forDaerah($daerahName));
    }
}
