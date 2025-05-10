<?php
namespace PCG\CampaignBase\Core;

class Plugin {
    private static $instance = null;
    private $post_types = [];
    private $taxonomies = [];
    private $blocks = [];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('init', [$this, 'init']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('admin_notices', [$this, 'check_cache_status']);
    }

    public function init() {
        $this->register_post_types();
        $this->register_taxonomies();
        $this->register_blocks();
    }

    public function check_cache_status() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $cache = \PCG\CampaignCore\Core\Cache::get_instance();
        $status = $cache->get_status();

        if (!$status['has_persistent_cache']) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php _e('Campaign plugin: Persistent object cache is not available. This may impact performance.', 'campaign'); ?>
                    <?php if ($status['is_wpcom']) : ?>
                        <br>
                        <?php _e('You are on WordPress.com. Please contact support if you believe this is incorrect.', 'campaign'); ?>
                    <?php endif; ?>
                </p>
            </div>
            <?php
        }
    }

    public function register_post_types() {
        // Register base post types that all campaigns will need
        $this->register_character_post_type();
        $this->register_session_post_type();
    }

    private function register_character_post_type() {
        $args = [
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-groups',
            'labels' => [
                'name' => __('Characters', 'campaign'),
                'singular_name' => __('Character', 'campaign'),
            ],
        ];

        register_post_type('campaign_character', $args);
    }

    private function register_session_post_type() {
        $args = [
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-book-alt',
            'labels' => [
                'name' => __('Sessions', 'campaign'),
                'singular_name' => __('Session', 'campaign'),
            ],
        ];

        register_post_type('campaign_session', $args);
    }

    public function register_taxonomies() {
        // Register base taxonomies
    }

    public function register_blocks() {
        // Register base blocks
    }

    public function enqueue_editor_assets() {
        // Enqueue editor assets
    }

    public function enqueue_frontend_assets() {
        // Enqueue frontend assets
    }

    // Getters for child plugins
    public function get_post_types() {
        return $this->post_types;
    }

    public function get_taxonomies() {
        return $this->taxonomies;
    }

    public function get_blocks() {
        return $this->blocks;
    }
} 