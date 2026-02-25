<?php
header('Content-Type: text/plain');

echo "=== Database Configuration ===\n\n";

echo "Environment Variables:\n";
echo "DB_NAME: " . (getenv('DB_NAME') ?: 'NOT SET') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'NOT SET') . "\n";
echo "DB_PASSWORD: " . (getenv('DB_PASSWORD') ? 'SET (' . strlen(getenv('DB_PASSWORD')) . ' chars)' : 'NOT SET') . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'NOT SET') . "\n";
echo "DB_PORT: " . (getenv('DB_PORT') ?: 'NOT SET') . "\n";
echo "TABLE_PREFIX: " . (getenv('TABLE_PREFIX') ?: 'NOT SET') . "\n";

echo "\nConstructed DB_HOST for WordPress:\n";
$db_host = getenv('DB_HOST') . ':' . (getenv('DB_PORT') ?: '3306');
echo "'" . $db_host . "'\n";

echo "\nTesting MySQL connection...\n";
try {
    $mysqli = new mysqli(
        getenv('DB_HOST'),
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        getenv('DB_NAME'),
        getenv('DB_PORT') ?: 3306
    );
    
    if ($mysqli->connect_error) {
        echo "✗ Connection failed: " . $mysqli->connect_error . "\n";
        echo "  Error code: " . $mysqli->connect_errno . "\n";
    } else {
        echo "✓ Connection successful!\n";
        echo "  Server: " . $mysqli->server_info . "\n";
        echo "  Character set: " . $mysqli->character_set_name() . "\n";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

// Test with SSL
echo "\nTesting MySQL connection with SSL...\n";
try {
    $mysqli = mysqli_init();
    $mysqli->ssl_set(null, null, null, null, null);
    $mysqli->real_connect(
        getenv('DB_HOST'),
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        getenv('DB_NAME'),
        getenv('DB_PORT') ?: 3306,
        null,
        MYSQLI_CLIENT_SSL
    );
    
    if ($mysqli->connect_error) {
        echo "✗ SSL connection failed: " . $mysqli->connect_error . "\n";
    } else {
        echo "✓ SSL connection successful!\n";
        $mysqli->close();
    }
} catch (Exception $e) {
    echo "✗ SSL Exception: " . $e->getMessage() . "\n";
}

// Self-destruct
unlink(__FILE__);
echo "\n✓ Diagnostic script removed\n";
