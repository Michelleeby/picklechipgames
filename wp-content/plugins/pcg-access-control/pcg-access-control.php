<?php
/**
 * Plugin Name: PCG Access Control
 * Plugin URI: https://picklechipgames.com
 * Description: User management and access control for campaign-based content
 * Version: 1.0.0
 * Author: Pickle Chip Games
 * Author URI: https://picklechipgames.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: pcg-access-control
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PCG_ACCESS_CONTROL_VERSION', '1.0.0');
define('PCG_ACCESS_CONTROL_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PCG_ACCESS_CONTROL_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoloader
if (file_exists(PCG_ACCESS_CONTROL_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once PCG_ACCESS_CONTROL_PLUGIN_DIR . 'vendor/autoload.php';
}

// Initialize the plugin
add_action('plugins_loaded', function() {
    $container = PCG\AccessControl\Core\Container::getInstance();
    $plugin = $container['plugin'];
});

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create necessary database tables
    PCG\AccessControl\Core\Database::create_tables();
    
    // Set up default roles and capabilities
    PCG\AccessControl\Core\Roles::setup_roles();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if necessary
    PCG\AccessControl\Core\Roles::cleanup_roles();
}); 