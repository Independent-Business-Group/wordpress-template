<?php
header('Content-Type: text/plain');

echo "=== Environment Variables ===\n\n";
echo "WP_HOME: " . (getenv('WP_HOME') ?: 'NOT SET') . "\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "\n";
echo "TABLE_PREFIX: " . (getenv('TABLE_PREFIX') ?: 'NOT SET') . "\n";

echo "\n=== WordPress Constants (if wp-config loaded) ===\n\n";
if (defined('WP_HOME')) {
    echo "WP_HOME constant: " . WP_HOME . "\n";
    echo "WP_SITEURL constant: " . WP_SITEURL . "\n";
} else {
    echo "WordPress not loaded yet\n";
}

echo "\n=== Current Request ===\n\n";
echo "Host: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
?>
