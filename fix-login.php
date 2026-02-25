<?php
// WordPress Login Fix - Updates siteurl/home and verifies user capabilities
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

echo "=== WordPress Login Fix ===\n\n";

// Get current values
$result = $mysqli->query("SELECT option_name, option_value FROM {$table_prefix}options WHERE option_name IN ('siteurl', 'home')");
echo "Current configuration:\n";
while ($row = $result->fetch_assoc()) {
    echo "  {$row['option_name']}: {$row['option_value']}\n";
}

// Fix URLs to HTTPS
$correct_url = 'https://preciseitservices.com.au';
echo "\nUpdating to: $correct_url\n";

$mysqli->query("UPDATE {$table_prefix}options SET option_value = '$correct_url' WHERE option_name = 'siteurl'");
$mysqli->query("UPDATE {$table_prefix}options SET option_value = '$correct_url' WHERE option_name = 'home'");

echo "✓ URLs updated\n\n";

// Check user capabilities
echo "Checking admin user...\n";
$user_result = $mysqli->query("SELECT ID, user_login FROM {$table_prefix}users WHERE user_login = 'dablackfox' LIMIT 1");
if ($user_row = $user_result->fetch_assoc()) {
    $user_id = $user_row['ID'];
    echo "  User ID: $user_id\n";
    
    // Check capabilities
    $cap_result = $mysqli->query("SELECT meta_value FROM {$table_prefix}usermeta WHERE user_id = $user_id AND meta_key = '{$table_prefix}capabilities'");
    if ($cap_row = $cap_result->fetch_assoc()) {
        echo "  Current capabilities: {$cap_row['meta_value']}\n";
        
        // Verify admin role
        if (strpos($cap_row['meta_value'], 'administrator') === false) {
            echo "  WARNING: User is not an administrator!\n";
            echo "  Setting administrator role...\n";
            $admin_caps = 'a:1:{s:13:"administrator";b:1;}';
            $mysqli->query("UPDATE {$table_prefix}usermeta SET meta_value = '$admin_caps' WHERE user_id = $user_id AND meta_key = '{$table_prefix}capabilities'");
            echo "  ✓ Administrator role set\n";
        } else {
            echo "  ✓ User has administrator role\n";
        }
    } else {
        echo "  WARNING: User has no capabilities!\n";
        echo "  Setting administrator role...\n";
        $admin_caps = 'a:1:{s:13:"administrator";b:1;}';
        $mysqli->query("INSERT INTO {$table_prefix}usermeta (user_id, meta_key, meta_value) VALUES ($user_id, '{$table_prefix}capabilities', '$admin_caps')");
        echo "  ✓ Administrator role set\n";
    }
} else {
    echo "  ERROR: User 'dablackfox' not found!\n";
}

$mysqli->close();

echo "\n✓ Login fix complete\n";
echo "\nTry logging in now at: https://preciseitservices.com.au/wp-login.php\n";

// Self-destruct for security
unlink(__FILE__);
echo "\n✓ Fix script removed\n";
?>
