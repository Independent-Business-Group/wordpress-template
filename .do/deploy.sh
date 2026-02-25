#!/bin/bash
set -e

echo "====================================="
echo "WordPress Template - Build Script"
echo "====================================="

# Download WordPress core
echo "→ Downloading WordPress latest..."
wget -q https://wordpress.org/wordpress-latest.tar.gz
tar -xzf wordpress-latest.tar.gz --strip-components=1
rm wordpress-latest.tar.gz
echo "✓ WordPress core downloaded"

# Download wp-content from DO Spaces
if [ -n "$SPACES_BUCKET" ] && [ -n "$SPACES_FOLDER" ]; then
    echo "→ Downloading wp-content from Spaces..."
    
    # Configure s3cmd
    cat > /tmp/.s3cfg << EOF
[default]
access_key = ${SPACES_KEY}
secret_key = ${SPACES_SECRET}
host_base = syd1.digitaloceanspaces.com
host_bucket = %(bucket)s.syd1.digitaloceanspaces.com
use_https = True
signature_v2 = False
EOF
    
    # Install s3cmd if not present
    if ! command -v s3cmd &> /dev/null; then
        pip install s3cmd
    fi
    
    # Download wp-content from Spaces
    # Remove default wp-content that came with WordPress core
    rm -rf wp-content/*
    
    # Sync from Spaces
    s3cmd -c /tmp/.s3cfg sync \
        s3://${SPACES_BUCKET}/${SPACES_FOLDER}/wp-content/ \
        wp-content/ \
        --skip-existing \
        --no-preserve
    
    echo "✓ wp-content downloaded from Spaces ($(du -sh wp-content | cut -f1))"
    
    # Create mu-plugins directory if it doesn't exist
    mkdir -p wp-content/mu-plugins
    
    # Copy sync script to root
    cp sync-to-spaces.php . || echo "⚠ sync-to-spaces.php not found in repo"
    chmod +x sync-to-spaces.php || true
    
    echo "✓ Spaces sync configured"
else
    echo "⚠ SPACES_* environment variables not set, using default wp-content"
fi

# Generate wp-config.php template
# This uses getenv() so it reads environment variables at runtime
echo "→ Generating wp-config.php template..."
cat > wp-config.php << 'WPCONFIG'
<?php
/**
 * WordPress Configuration - Template
 * Auto-configured from environment variables
 */

// Database Configuration - from environment
define('DB_NAME', getenv('DB_NAME') ?: 'wordpress');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
define('DB_HOST', getenv('DB_HOST') . ':' . (getenv('DB_PORT') ?: '3306'));
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Table Prefix
$table_prefix = getenv('TABLE_PREFIX') ?: 'wp_';

// Security Keys - fetch from WordPress API or use environment-based fallback
if ($salt = @file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/')) {
    eval($salt);
} else {
    $base = getenv('DB_NAME') . getenv('DB_HOST');
    define('AUTH_KEY',         md5($base . 'auth'));
    define('SECURE_AUTH_KEY',  md5($base . 'secure'));
    define('LOGGED_IN_KEY',    md5($base . 'logged'));
    define('NONCE_KEY',        md5($base . 'nonce'));
    define('AUTH_SALT',        md5($base . 'authsalt'));
    define('SECURE_AUTH_SALT', md5($base . 'securesalt'));
    define('LOGGED_IN_SALT',   md5($base . 'loggedsalt'));
    define('NONCE_SALT',       md5($base . 'noncesalt'));
}

// Debugging
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Security
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);

// Memory Limits
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// HTTPS behind proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
}

// Kubernetes health probe compatibility
if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'kube-probe') !== false) {
    $_SERVER['HTTPS'] = 'off';
}

// Absolute path
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
WPCONFIG

echo "✓ wp-config.php template created"

echo "✓ Build complete"
echo "====================================="
