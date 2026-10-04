<?php
/**
 * Script untuk mengganti semua referensi nama role lama
 * menjadi nama role baru secara otomatis di seluruh folder app/
 */

$directory = __DIR__ . '/app';

$replacements = [
    // Role lama → Role baru
    "'kabag_daerah'"     => "'pengurus_daerah'",
    "'wali_asuh'"        => "'pengurus_sektor'",
    "'kamtib_sektor'"    => "'pengurus_sektor'",
    "'pengurus_bkwa'"    => "'dewan_pemangku'",
    
    // Dalam string quotes ganda
    '"kabag_daerah"'     => '"pengurus_daerah"',
    '"wali_asuh"'        => '"pengurus_sektor"',
    '"kamtib_sektor"'    => '"pengurus_sektor"',
    '"pengurus_bkwa"'    => '"dewan_pemangku"',
];

function scanDirForRoles($dir, $replacements, &$count) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            scanDirForRoles($path, $replacements, $count);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            $newContent = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $content
            );
            if ($content !== $newContent) {
                file_put_contents($path, $newContent);
                echo "Updated: " . str_replace(__DIR__ . '/', '', $path) . "\n";
                $count++;
            }
        }
    }
}

$count = 0;
scanDirForRoles($directory, $replacements, $count);
echo "\nTotal files updated: $count\n";
