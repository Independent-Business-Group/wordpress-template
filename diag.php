<?php
header('Content-Type: text/plain');

echo "=== WordPress Deployment Diagnostic ===\n\n";

echo "Current Directory: " . getcwd() . "\n";
echo "Script Location: " . __DIR__ . "\n\n";

echo "Files in root directory:\n";
$files = scandir(__DIR__);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        $path = __DIR__ . '/' . $file;
        $type = is_dir($path) ? '[DIR]' : '[FILE]';
        $size = is_file($path) ? filesize($path) : 0;
        echo "  $type $file";
        if ($size > 0) echo " ($size bytes)";
        echo "\n";
    }
}

echo "\nwp-config.php exists: " . (file_exists(__DIR__ . '/wp-config.php') ? 'YES' : 'NO') . "\n";
echo "wp-blog-header.php exists: " . (file_exists(__DIR__ . '/wp-blog-header.php') ? 'YES' : 'NO') . "\n";

if (file_exists(__DIR__ . '/wp-config.php')) {
    echo "\nwp-config.php size: " . filesize(__DIR__ . '/wp-config.php') . " bytes\n";
}

echo "\nEnvironment Variables:\n";
$env_vars = ['DB_NAME', 'DB_USER', 'DB_HOST', 'DB_PORT', 'TABLE_PREFIX'];
foreach ($env_vars as $var) {
    $value = getenv($var);
    echo "  $var: " . ($value ? $value : 'NOT SET') . "\n";
}

// Self-destruct
unlink(__FILE__);
echo "\n✓ Diagnostic script removed\n";
?>
