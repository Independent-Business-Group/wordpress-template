<?php
// WordPress configuration diagnostics
define('DB_NAME', getenv('DB_NAME') ?: 'wp_preciseitservices');
define('DB_USER', getenv('DB_USER') ?: 'doadmin');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
$table_prefix = getenv('TABLE_PREFIX') ?: 'mjD6nT_';

// Connect to database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, (int)getenv('DB_PORT') ?: 3306);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

echo "<h2>WordPress Configuration Check</h2>\n";

// Check critical options
$critical_options = ['siteurl', 'home', 'active_plugins'];
foreach ($critical_options as $option) {
    $result = $mysqli->query("SELECT option_value FROM {$table_prefix}options WHERE option_name = '$option'");
    if ($row = $result->fetch_assoc()) {
        echo "<strong>$option:</strong> " . htmlspecialchars($row['option_value']) . "<br>\n";
    }
}

// Check admin user
echo "<br><h3>Admin User Check:</h3>\n";
$user_result = $mysqli->query("SELECT ID, user_login, user_email FROM {$table_prefix}users WHERE user_login = 'dablackfox' LIMIT 1");
if ($user_row = $user_result->fetch_assoc()) {
    echo "User ID: {$user_row['ID']}<br>\n";
    echo "Login: {$user_row['user_login']}<br>\n";
    echo "Email: {$user_row['user_email']}<br>\n";
    
    // Check user meta for capabilities
    $meta_result = $mysqli->query("SELECT meta_value FROM {$table_prefix}usermeta WHERE user_id = {$user_row['ID']} AND meta_key = '{$table_prefix}capabilities'");
    if ($meta_row = $meta_result->fetch_assoc()) {
        echo "Capabilities: " . htmlspecialchars($meta_row['meta_value']) . "<br>\n";
    } else {
        echo "<strong style='color:red;'>WARNING: No capabilities found!</strong><br>\n";
    }
}

$mysqli->close();

// Self-destruct
unlink(__FILE__);
echo "<br>✓ Diagnostic script removed<br>\n";
?>
