<?php
header('Content-Type: text/plain');

define('DB_NAME', getenv('DB_NAME') ?: 'wp_preciseitservices');
define('DB_USER', getenv('DB_USER') ?: 'doadmin');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
$table_prefix = getenv('TABLE_PREFIX') ?: 'mjD6nT_';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, (int)getenv('DB_PORT') ?: 3306);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "=== Current WordPress URLs ===\n\n";

$result = $mysqli->query("SELECT option_name, option_value FROM {$table_prefix}options WHERE option_name IN ('siteurl', 'home')");
while ($row = $result->fetch_assoc()) {
    echo "{$row['option_name']}: {$row['option_value']}\n";
}

echo "\n=== Expected URL ===\n";
echo "Should be: https://preciseitservices.com.au\n";

echo "\n=== Current Request ===\n";
echo "Accessing via: https://{$_SERVER['HTTP_HOST']}\n";

$mysqli->close();
?>
