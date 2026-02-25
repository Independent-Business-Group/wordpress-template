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

echo "=== WordPress URL Fix ===\n\n";

// Show current values
echo "BEFORE:\n";
$result = $mysqli->query("SELECT option_name, option_value FROM {$table_prefix}options WHERE option_name IN ('siteurl', 'home')");
while ($row = $result->fetch_assoc()) {
    echo "  {$row['option_name']}: {$row['option_value']}\n";
}

// Fix to correct domain
$correct_url = 'https://preciseitservices.com.au';
echo "\nUpdating to: $correct_url\n\n";

$mysqli->query("UPDATE {$table_prefix}options SET option_value = '$correct_url' WHERE option_name = 'siteurl'");
$mysqli->query("UPDATE {$table_prefix}options SET option_value = '$correct_url' WHERE option_name = 'home'");

// Show updated values
echo "AFTER:\n";
$result = $mysqli->query("SELECT option_name, option_value FROM {$table_prefix}options WHERE option_name IN ('siteurl', 'home')");
while ($row = $result->fetch_assoc()) {
    echo "  {$row['option_name']}: {$row['option_value']}\n";
}

$mysqli->close();

echo "\n✓ URLs updated!\n";
echo "\nClear browser cookies for preciseitservices.com.au and try logging in again.\n";

// Self-destruct
unlink(__FILE__);
echo "\n✓ Fix script removed\n";
?>
