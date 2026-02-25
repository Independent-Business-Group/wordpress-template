<?php
// WordPress DB diagnostics - doesn't self-destruct for repeated checks
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

echo "=== WordPress Configuration ===\n\n";

// Critical options
$critical_options = ['siteurl', 'home', 'active_plugins'];
foreach ($critical_options as $option) {
    $result = $mysqli->query("SELECT option_value FROM {$table_prefix}options WHERE option_name = '$option'");
    if ($row = $result->fetch_assoc()) {
        echo "$option: " . $row['option_value'] . "\n";
    }
}

echo "\n=== Admin User (dablackfox) ===\n\n";
$user_result = $mysqli->query("SELECT ID, user_login, user_email FROM {$table_prefix}users WHERE user_login = 'dablackfox' LIMIT 1");
if ($user_row = $user_result->fetch_assoc()) {
    echo "User ID: {$user_row['ID']}\n";
    echo "Login: {$user_row['user_login']}\n";
    echo "Email: {$user_row['user_email']}\n\n";
    
    // Capabilities
    $meta_result = $mysqli->query("SELECT meta_value FROM {$table_prefix}usermeta WHERE user_id = {$user_row['ID']} AND meta_key = '{$table_prefix}capabilities'");
    if ($meta_row = $meta_result->fetch_assoc()) {
        echo "Capabilities: " . $meta_row['meta_value'] . "\n";
    } else {
        echo "WARNING: No capabilities found!\n";
    }
} else {
    echo "WARNING: User not found!\n";
}

$mysqli->close();
?>
