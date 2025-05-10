<?php
/**
 * Plugin Name: PCG Campaign Route 99
 * Description: Kids on Bikes campaign set on Route 99
 * Version: 1.0.0
 * Author: Pickle Chip Games
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pcg-campaign-route99
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if Campaign plugin is active
if (!class_exists('PCG\CampaignBase\Core\Database')) {
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Route 99 requires the Campaign Base plugin to be installed and activated.', 'pcg-campaign-route99'); ?></p>
        </div>
        <?php
    });
    return;
}

// Define plugin constants
define('PCG_CAMPAIGN_ROUTE99_VERSION', '1.0.0');
define('PCG_CAMPAIGN_ROUTE99_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PCG_CAMPAIGN_ROUTE99_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoloader
if (file_exists(PCG_CAMPAIGN_ROUTE99_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once PCG_CAMPAIGN_ROUTE99_PLUGIN_DIR . 'vendor/autoload.php';
}

// Initialize the plugin
function pcg_campaign_route99_init() {
    // Load text domain
    load_plugin_textdomain('pcg-campaign-route99', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Initialize core components
    add_action('init', function() {
        $container = PCG\CampaignRoute99\Core\Container::getInstance();
        $plugin = $container['plugin'];
    });
}
add_action('plugins_loaded', 'pcg_campaign_route99_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create Route 99 specific database tables
    PCG\CampaignRoute99\Core\Database::create_tables();
    
    // Set up Route 99 specific options
    PCG\CampaignRoute99\Core\Options::set_defaults();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if necessary
}); 