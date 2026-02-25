<?php
// Deactivate all WordPress plugins via direct DB update
define('DB_NAME', getenv('DB_NAME') ?: 'wp_preciseitservices');
define('DB_USER', getenv('DB_USER') ?: 'doadmin');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
$table_prefix = getenv('TABLE_PREFIX') ?: 'mjD6nT_';

// Connect to database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, (int)getenv('DB_PORT') ?: 3306);

if ($mysqli->connect_error) {
    die('Connection failed: ' . $mysqli->connect_error);
}

// Deactivate all plugins
$result = $mysqli->query("UPDATE {$table_prefix}options SET option_value = 'a:0:{}' WHERE option_name = 'active_plugins'");

if ($result) {
    echo "✓ All plugins deactivated successfully\n";
    
    // Verify
    $check = $mysqli->query("SELECT option_value FROM {$table_prefix}options WHERE option_name = 'active_plugins'");
    $row = $check->fetch_assoc();
    echo "Active plugins value: " . $row['option_value'] . "\n";
} else {
    echo "✗ Failed to deactivate plugins: " . $mysqli->error . "\n";
}

$mysqli->close();

// Self-destruct for security
unlink(__FILE__);
echo "✓ Cleanup script removed\n";
?>
