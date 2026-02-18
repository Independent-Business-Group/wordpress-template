<?php
/**
 * WordPress Template - Index
 * This file is replaced by WordPress core during deployment
 */

// If WordPress is not installed yet, show a simple message
if (!file_exists(__DIR__ . '/wp-config.php')) {
    die('<h1>WordPress Template</h1><p>Deployment in progress...</p>');
}

// Load WordPress
define('WP_USE_THEMES', true);
require __DIR__ . '/wp-blog-header.php';
