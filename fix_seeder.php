<?php

$file = __DIR__.'/database/seeders/DemoSeeder.php';
$content = file_get_contents($file);

// Replace email with username
$content = preg_replace_callback("/'email'\s*=>\s*'([^@]+)@[^']+',/", function($matches) {
    return "'username'           => '" . $matches[1] . "',";
}, $content);

// Remove email_verified_at
$content = preg_replace("/'email_verified_at'\s*=>\s*now\(\),\s*/", "", $content);

file_put_contents($file, $content);
echo "Seeder berhasil diupdate!";
