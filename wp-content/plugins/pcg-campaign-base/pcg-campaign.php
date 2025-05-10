<?php
/**
 * Plugin Name: PCG Campaign Base   
 * Description: Base plugin for tabletop RPG campaign management
 * Version: 1.0.0
 * Author: Pickle Chip Games
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pcg-campaign-base
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PCG_CAMPAIGN_VERSION', '1.0.0');
define('PCG_CAMPAIGN_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PCG_CAMPAIGN_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for campaign classes
spl_autoload_register(function ($class) {
    $prefix = 'PCG\\CampaignBase\\';
    $base_dir = PCG_CAMPAIGN_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function pcg_campaign_init() {
    // Load text domain
    load_plugin_textdomain('pcg-campaign-base', false, dirname(plugin_basename(__FILE__)) . '/languages');

    // Initialize core components
    add_action('init', function() {
        PCG\CampaignBase\Core\Plugin::get_instance();
    });
}
add_action('plugins_loaded', 'pcg_campaign_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create necessary database tables
    PCG\CampaignBase\Core\Database::create_tables();
    
    // Set up default options
    PCG\CampaignBase\Core\Options::set_defaults();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if necessary
}); 