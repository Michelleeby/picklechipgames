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

    // Create the Route 99 campaign
    PCG\CampaignRoute99\Core\Database::create_route99_campaign();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if necessary
});

// Dynamically add a submenu for each campaign under Campaign Entries
add_action('admin_menu', function() {
    // Query all published campaigns
    $campaigns = get_posts([
        'post_type'      => 'campaign',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
    ]);

    foreach ($campaigns as $campaign) {
        $submenu_slug = 'campaign-entries-' . $campaign->ID;
        add_submenu_page(
            'edit.php?post_type=cc_campaign_entry',
            $campaign->post_title . ' Entries',
            $campaign->post_title . ' Entries',
            'edit_posts',
            $submenu_slug,
            '__return_null'
        );
    }
});

// Redirect logic for each submenu
add_action('admin_init', function() {
    if (
        isset($_GET['post_type'], $_GET['page']) &&
        $_GET['post_type'] === 'cc_campaign_entry' &&
        strpos($_GET['page'], 'campaign-entries-') === 0
    ) {
        $campaign_id = str_replace('campaign-entries-', '', $_GET['page']);
        $url = admin_url('edit.php?post_type=cc_campaign_entry&campaign_id=' . intval($campaign_id));
        wp_redirect($url);
        exit;
    }
});

// Highlight the correct submenu for the current campaign
add_filter('parent_file', function($parent_file) {
    if (
        is_admin() &&
        isset($_GET['post_type'], $_GET['campaign_id']) &&
        $_GET['post_type'] === 'cc_campaign_entry'
    ) {
        return 'edit.php?post_type=cc_campaign_entry';
    }
    return $parent_file;
});

add_filter('submenu_file', function($submenu_file) {
    if (
        is_admin() &&
        isset($_GET['post_type'], $_GET['campaign_id']) &&
        $_GET['post_type'] === 'cc_campaign_entry'
    ) {
        return 'campaign-entries-' . intval($_GET['campaign_id']);
    }
    return $submenu_file;
});

// Filter entries list by campaign_id if present
add_action('pre_get_posts', function($query) {
    if (
        is_admin() &&
        $query->is_main_query() &&
        $query->get('post_type') === 'cc_campaign_entry' &&
        isset($_GET['campaign_id'])
    ) {
        $query->set('meta_key', 'associated_campaign'); // Adjust meta_key if needed
        $query->set('meta_value', $_GET['campaign_id']);
    }
});

// Enqueue JS to auto-fill campaign field on Add New if campaign_id is present
add_action('admin_enqueue_scripts', function($hook) {
    global $typenow;
    if ($typenow === 'cc_campaign_entry' && $hook === 'post-new.php' && isset($_GET['campaign_id'])) {
        wp_enqueue_script(
            'pcg-route99-autofill',
            plugin_dir_url(__FILE__) . 'route99-autofill.js',
            ['jquery'],
            '1.0',
            true
        );
        wp_localize_script('pcg-route99-autofill', 'PCGRoute99', [
            'campaignId' => sanitize_text_field($_GET['campaign_id'])
        ]);
    }
});

// Persist campaign_id in Add New link for cc_campaign_entry
add_filter('admin_url', function($url, $path, $blog_id) {
    if (
        is_admin() &&
        isset($_GET['post_type'], $_GET['campaign_id']) &&
        $_GET['post_type'] === 'cc_campaign_entry' &&
        strpos($url, 'post-new.php?post_type=cc_campaign_entry') !== false
    ) {
        $url = add_query_arg('campaign_id', sanitize_text_field($_GET['campaign_id']), $url);
    }
    return $url;
}, 10, 3);
 