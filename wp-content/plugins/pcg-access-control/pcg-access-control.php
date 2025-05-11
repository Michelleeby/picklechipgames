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

// Enable user registration
add_filter('option_users_can_register', '__return_true');

// Initialize the plugin
add_action('plugins_loaded', function() {
    $container = PCG\AccessControl\Core\Container::getInstance();
    $plugin = $container['plugin'];
    
    // Initialize user registration
    new PCG\AccessControl\Core\UserRegistration();
    
    // Initialize invitation system
    new PCG\AccessControl\Core\InvitationSystem();
});

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create necessary database tables
    PCG\AccessControl\Core\Database::create_tables();
    
    // Set up default roles and capabilities
    PCG\AccessControl\Core\Roles::setup_roles();
    
    // Enable user registration
    update_option('users_can_register', 1);
    
    // Create Register page if it doesn't exist
    $register_page = get_page_by_path('register');
    if (!$register_page) {
        wp_insert_post([
            'post_title'   => 'Register',
            'post_name'    => 'register',
            'post_content' => '[pcg_custom_register]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Clean up if necessary
    PCG\AccessControl\Core\Roles::cleanup_roles();
});

// Hide email field and registration confirmation message on registration form
add_action('login_enqueue_scripts', function() {
    if (isset($_GET['action']) && $_GET['action'] === 'register') {
        echo '<style>
            #registerform label[for="user_email"],
            #registerform #user_email,
            .message:contains("Registration confirmation will be emailed to you.") {
                display: none !important;
            }
        </style>';
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // Hide the registration confirmation message if present
                var messages = document.querySelectorAll(".message");
                messages.forEach(function(msg) {
                    if (msg.textContent.includes("Registration confirmation will be emailed to you.")) {
                        msg.style.display = "none";
                    }
                });
            });
        </script>';
    }
});

// Redirect default registration page to custom registration page
add_action('login_init', function() {
    if (isset($_GET['action']) && $_GET['action'] === 'register') {
        wp_redirect(site_url('/register/'));
        exit;
    }
});

// Enqueue block editor JS for the registration block (built file)
add_action('enqueue_block_editor_assets', function() {
    wp_enqueue_script(
        'pcg-register-block',
        plugins_url('blocks/build/register/index.js', __FILE__),
        [ 'wp-blocks', 'wp-element', 'wp-editor' ]
    );
});

// Require the PHP block registration file
require_once __DIR__ . '/blocks/register.php'; 