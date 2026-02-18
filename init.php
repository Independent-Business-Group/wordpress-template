#!/usr/bin/env php
<?php
/**
 * WordPress Template - First Run Initialization
 * 
 * This script runs on first deployment to:
 * 1. Create a database in the MariaDB cluster
 * 2. Create a DO Spaces bucket
 * 3. Install WordPress
 * 
 * Environment variables required:
 * - MYSQL_ROOT_PASSWORD: MariaDB cluster root password
 * - MYSQL_HOST: MariaDB cluster host
 * - MYSQL_PORT: MariaDB cluster port
 * - DO_SPACES_KEY: DigitalOcean Spaces access key
 * - DO_SPACES_SECRET: DigitalOcean Spaces secret key
 * - DO_SPACES_REGION: DigitalOcean Spaces region (e.g., nyc3)
 * - SITE_NAME: Name of this WordPress site (used for DB and bucket naming)
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "===========================================\n";
echo "WordPress Template - First Run Setup\n";
echo "===========================================\n\n";

// Check if already initialized
$init_flag = __DIR__ . '/.initialized';
if (file_exists($init_flag)) {
    echo "✓ Already initialized (flag file exists)\n";
    echo "  Delete .initialized file to re-run setup\n";
    exit(0);
}

// Get environment variables
$mysql_root_pw = getenv('MYSQL_ROOT_PASSWORD');
$mysql_host = getenv('MYSQL_HOST');
$mysql_port = getenv('MYSQL_PORT') ?: '3306';
$spaces_key = getenv('DO_SPACES_KEY');
$spaces_secret = getenv('DO_SPACES_SECRET');
$spaces_region = getenv('DO_SPACES_REGION') ?: 'nyc3';
$site_name = getenv('SITE_NAME') ?: 'wordpress-' . bin2hex(random_bytes(4));

// Validate required variables
$required = [
    'MYSQL_ROOT_PASSWORD' => $mysql_root_pw,
    'MYSQL_HOST' => $mysql_host,
    'DO_SPACES_KEY' => $spaces_key,
    'DO_SPACES_SECRET' => $spaces_secret,
];

$missing = [];
foreach ($required as $var => $value) {
    if (empty($value)) {
        $missing[] = $var;
    }
}

if (!empty($missing)) {
    echo "✗ Error: Missing required environment variables:\n";
    foreach ($missing as $var) {
        echo "  - $var\n";
    }
    exit(1);
}

echo "Configuration:\n";
echo "  Site Name: $site_name\n";
echo "  MySQL Host: $mysql_host:$mysql_port\n";
echo "  Spaces Region: $spaces_region\n\n";

// ============================================================================
// Step 1: Create Database
// ============================================================================

echo "Step 1: Creating database...\n";

// Generate database credentials
$db_name = preg_replace('/[^a-z0-9_]/', '_', strtolower($site_name));
$db_user = $db_name . '_user';
$db_password = bin2hex(random_bytes(16));
$table_prefix = substr($db_name, 0, 10) . '_';

echo "  DB Name: $db_name\n";
echo "  DB User: $db_user\n";
echo "  Table Prefix: $table_prefix\n";

try {
    // Connect as root
    $mysqli = new mysqli($mysql_host, 'doadmin', $mysql_root_pw, '', (int)$mysql_port);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "  ✓ Connected to MariaDB cluster\n";
    
    // Create database
    $db_name_safe = $mysqli->real_escape_string($db_name);
    if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name_safe` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        throw new Exception("Failed to create database: " . $mysqli->error);
    }
    echo "  ✓ Database created: $db_name\n";
    
    // Create user
    $db_user_safe = $mysqli->real_escape_string($db_user);
    $db_password_safe = $mysqli->real_escape_string($db_password);
    
    $queries = [
        "DROP USER IF EXISTS '$db_user_safe'@'%'",
        "CREATE USER '$db_user_safe'@'%' IDENTIFIED BY '$db_password_safe'",
        "GRANT ALL PRIVILEGES ON `$db_name_safe`.* TO '$db_user_safe'@'%'",
        "FLUSH PRIVILEGES"
    ];
    
    foreach ($queries as $query) {
        if (!$mysqli->query($query)) {
            throw new Exception("Query failed: " . $mysqli->error . " (Query: $query)");
        }
    }
    
    echo "  ✓ Database user created and granted permissions\n";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// ============================================================================
// Step 2: Create DO Spaces Bucket
// ============================================================================

echo "\nStep 2: Creating DO Spaces bucket...\n";

$bucket_name = $site_name;
$endpoint = "https://{$spaces_region}.digitaloceanspaces.com";

echo "  Bucket Name: $bucket_name\n";
echo "  Endpoint: $endpoint\n";

// Use AWS SDK compatible approach
require_once __DIR__ . '/vendor/autoload.php';

try {
    // For now, we'll just log the bucket creation details
    // In production, you'd use the AWS SDK for PHP to create the bucket
    echo "  ⚠ Bucket creation via API requires AWS SDK\n";
    echo "  ℹ To create bucket manually:\n";
    echo "    doctl spaces create $bucket_name --region $spaces_region\n";
    
    // TODO: Implement bucket creation when AWS SDK is available
    
} catch (Exception $e) {
    echo "  ⚠ Bucket creation skipped: " . $e->getMessage() . "\n";
}

// ============================================================================
// Step 3: Save Configuration
// ============================================================================

echo "\nStep 3: Saving configuration...\n";

$env_file = __DIR__ . '/.env.generated';
$env_content = <<<ENV
# Auto-generated WordPress configuration
# Generated: {date('Y-m-d H:i:s')}

DB_NAME=$db_name
DB_USER=$db_user
DB_PASSWORD=$db_password
DB_HOST=$mysql_host
DB_PORT=$mysql_port
TABLE_PREFIX=$table_prefix

DO_SPACES_BUCKET=$bucket_name
DO_SPACES_REGION=$spaces_region
DO_SPACES_ENDPOINT=$endpoint

SITE_NAME=$site_name
ENV;

if (file_put_contents($env_file, $env_content)) {
    echo "  ✓ Configuration saved to .env.generated\n";
} else {
    echo "  ✗ Failed to save configuration\n";
    exit(1);
}

// ============================================================================
// Step 4: Create initialization flag
// ============================================================================

file_put_contents($init_flag, date('Y-m-d H:i:s'));
echo "\n✓ Initialization complete!\n";
echo "===========================================\n";
echo "\nNext steps:\n";
echo "1. Set these environment variables in App Platform:\n";
echo "   DB_NAME=$db_name\n";
echo "   DB_USER=$db_user\n";
echo "   DB_PASSWORD=$db_password\n";
echo "   TABLE_PREFIX=$table_prefix\n";
echo "2. Access your WordPress site to complete installation\n";
echo "===========================================\n";
