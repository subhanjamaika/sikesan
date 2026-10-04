<?php
/**
 * Script untuk mengganti getKamarAsuhanIds() → getAllowedUnitIds()
 * dan isRole() → hasRole() di seluruh folder app/
 */

$directory = __DIR__ . '/app';

$replacements = [
    'getKamarAsuhanIds()' => 'getAllowedUnitIds()',
    '->isRole('           => '->hasRole(',
];

function fixMethodCalls($dir, $replacements, &$count) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            fixMethodCalls($path, $replacements, $count);
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
fixMethodCalls($directory, $replacements, $count);
echo "\nTotal files updated: $count\n";
