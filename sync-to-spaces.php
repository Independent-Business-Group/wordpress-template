#!/usr/bin/env php
<?php
/**
 * Sync wp-content changes back to DigitalOcean Spaces
 * Called by WordPress cron or can be run manually
 */

// Prevent web access
if (php_sapi_name() !== 'cli' && !defined('DOING_CRON')) {
    die('CLI only');
}

$bucket = getenv('SPACES_BUCKET');
$folder = getenv('SPACES_FOLDER');
$key = getenv('SPACES_KEY');
$secret = getenv('SPACES_SECRET');

if (!$bucket || !$folder || !$key || !$secret) {
    echo "⚠ Spaces environment variables not configured\n";
    exit(1);
}

// Create s3cmd config
$s3cfg = "/tmp/.s3cfg";
file_put_contents($s3cfg, <<<EOF
[default]
access_key = {$key}
secret_key = {$secret}
host_base = syd1.digitaloceanspaces.com
host_bucket = %(bucket)s.syd1.digitaloceanspaces.com
use_https = True
signature_v2 = False
EOF
);

echo "→ Syncing wp-content to Spaces...\n";

// Only sync uploads, plugins, and themes (skip cache and temp files)
$sync_paths = ['uploads', 'plugins', 'themes'];

foreach ($sync_paths as $path) {
    $local_path = "/workspace/wp-content/{$path}/";
    $remote_path = "s3://{$bucket}/{$folder}/wp-content/{$path}/";
    
    if (!is_dir($local_path)) {
        continue;
    }
    
    echo "  → Syncing {$path}...\n";
    
    $cmd = sprintf(
        's3cmd -c %s sync %s %s --delete-removed --skip-existing --exclude="*.log" --exclude="cache/*" 2>&1',
        escapeshellarg($s3cfg),
        escapeshellarg($local_path),
        escapeshellarg($remote_path)
    );
    
    exec($cmd, $output, $return);
    
    if ($return === 0) {
        echo "  ✓ {$path} synced\n";
    } else {
        echo "  ⚠ {$path} sync failed: " . implode("\n", $output) . "\n";
    }
}

// Clean up
unlink($s3cfg);

echo "✓ Sync complete\n";
