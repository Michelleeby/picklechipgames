<?php
/**
 * Plugin Name: Campaign Core
 * Plugin URI: https://picklechipgames.com
 * Description: Core functionality for campaign management, including caching, monitoring, and database management.
 * Version: 1.0.0
 * Author: Pickle Chip Games
 * Author URI: https://picklechipgames.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: campaign-core
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PCG_CAMPAIGN_CORE_VERSION', '1.0.0');
define('PCG_CAMPAIGN_CORE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PCG_CAMPAIGN_CORE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Composer autoloader
if (file_exists(PCG_CAMPAIGN_CORE_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once PCG_CAMPAIGN_CORE_PLUGIN_DIR . 'vendor/autoload.php';
}

// Initialize the plugin
add_action('plugins_loaded', function() {
    $container = PCG\CampaignCore\Core\Container::getInstance();
    $plugin = $container['plugin'];
});

// Enqueue block editor JS for the example block (built file)
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'pcg-campaign-core-example-block',
        plugins_url('blocks/build/example-block/index.js', __FILE__),
        [ 'wp-blocks', 'wp-element', 'wp-editor' ]
    );
});

// Require the PHP block registration file
require_once __DIR__ . '/blocks/register.php'; 