<?php
/**
 * Plugin Name: DO Spaces Auto-Sync
 * Description: Automatically syncs wp-content changes to DigitalOcean Spaces
 * Version: 1.0
 * Author: Automated
 */

// Register custom cron schedule (every 5 minutes)
add_filter('cron_schedules', function($schedules) {
    $schedules['five_minutes'] = array(
        'interval' => 300,
        'display' => __('Every 5 Minutes')
    );
    return $schedules;
});

// Schedule the sync
add_action('init', function() {
    if (!wp_next_scheduled('do_spaces_sync')) {
        wp_schedule_event(time(), 'five_minutes', 'do_spaces_sync');
    }
});

// Hook to run the sync script
add_action('do_spaces_sync', function() {
    $sync_script = ABSPATH . '../sync-to-spaces.php';
    
    if (file_exists($sync_script)) {
        // Run sync in background
        exec("php {$sync_script} > /dev/null 2>&1 &");
        error_log('DO Spaces sync triggered');
    }
});

// Also sync on plugin/theme changes
add_action('upgrader_process_complete', function() {
    $sync_script = ABSPATH . '../sync-to-spaces.php';
    if (file_exists($sync_script)) {
        exec("php {$sync_script} > /dev/null 2>&1 &");
        error_log('DO Spaces sync triggered after plugin/theme update');
    }
}, 10, 0);

// Add admin notice showing sync status
add_action('admin_notices', function() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $bucket = getenv('SPACES_BUCKET');
    $folder = getenv('SPACES_FOLDER');
    
    if ($bucket && $folder) {
        echo '<div class="notice notice-info"><p>';
        echo '<strong>DO Spaces:</strong> wp-content syncs to <code>s3://' . esc_html($bucket) . '/' . esc_html($folder) . '/wp-content/</code> every 5 minutes.';
        echo '</p></div>';
    }
});
