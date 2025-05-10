<?php
namespace PCG\CampaignCore\Core;

use PCG\CampaignCore\Core\Cache\CacheManager;
use PCG\CampaignCore\Core\Interfaces\DatabaseManagerInterface;
use PCG\CampaignCore\Core\Interfaces\OptionsManagerInterface;
use PCG\CampaignCore\Core\Interfaces\PluginInterface;
use PCG\CampaignCore\Core\PostTypes\CampaignCorePostTypes;
use PCG\CampaignCore\Core\Taxonomies\CampaignCoreTaxonomies;
use PCG\CampaignCore\Core\Blocks\CampaignCoreBlocks;

class Plugin implements PluginInterface {
    private $cache;
    private $database;
    private $options;
    private $postTypeManager;
    private $taxonomyManager;
    private $blockManager;

    public function __construct(
        CacheManager $cache,
        DatabaseManagerInterface $database,
        OptionsManagerInterface $options
    ) {
        $this->cache = $cache;
        $this->database = $database;
        $this->options = $options;

        $this->initManagers();
        $this->initHooks();
    }

    private function initManagers(): void {
        $this->postTypeManager = new CampaignCorePostTypes('campaign_core');
        $this->taxonomyManager = new CampaignCoreTaxonomies('campaign_core');
        $this->blockManager = new CampaignCoreBlocks('campaign_core', PCG_CAMPAIGN_CORE_PLUGIN_URL);
    }

    private function initHooks(): void {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Register activation/deactivation hooks
        register_activation_hook(PCG_CAMPAIGN_CORE_PLUGIN_DIR . 'campaign-core.php', [$this, 'activate']);
        register_deactivation_hook(PCG_CAMPAIGN_CORE_PLUGIN_DIR . 'campaign-core.php', [$this, 'deactivate']);
    }

    public function init(): void {
        $this->postTypeManager->register_post_types();
        $this->taxonomyManager->register_taxonomies();
        $this->blockManager->register_blocks();
    }

    public function addMenuPage(): void {
        add_menu_page(
            __('Campaign Core', 'campaign-core'),
            __('Campaign Core', 'campaign-core'),
            'manage_options',
            'campaign-core',
            [$this, 'renderAdminPage'],
            'dashicons-admin-generic',
            30
        );

        add_submenu_page(
            'campaign-core',
            __('Settings', 'campaign-core'),
            __('Settings', 'campaign-core'),
            'manage_options',
            'campaign-core-settings',
            [$this, 'renderSettingsPage']
        );
    }

    public function enqueueAdminAssets(): void {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['toplevel_page_campaign-core', 'campaign-core_page_campaign-core-settings'])) {
            return;
        }

        wp_enqueue_style(
            'campaign-core-admin',
            PCG_CAMPAIGN_CORE_PLUGIN_URL . 'assets/css/admin.css',
            [],
            PCG_CAMPAIGN_CORE_VERSION
        );

        wp_enqueue_script(
            'campaign-core-admin',
            PCG_CAMPAIGN_CORE_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            PCG_CAMPAIGN_CORE_VERSION,
            true
        );
    }

    public function renderAdminPage(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Make cache instance available to the template
        $cache = $this->cache;
        require_once PCG_CAMPAIGN_CORE_PLUGIN_DIR . 'templates/admin-page.php';
    }

    public function renderSettingsPage(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Campaign Core Settings', 'campaign-core'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('campaign_core_options');
                do_settings_sections('campaign-core-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function activate(): void {
        $this->database->create_tables();
        $this->options->set_defaults();
        $this->cache->scheduleCleanup();
    }

    public function deactivate(): void {
        // Clean up if necessary
    }

    public function get_plugin_name(): string {
        return 'Campaign Core';
    }

    public function get_plugin_version(): string {
        return PCG_CAMPAIGN_CORE_VERSION;
    }

    public function get_plugin_path(): string {
        return PCG_CAMPAIGN_CORE_PLUGIN_DIR;
    }

    public function get_plugin_url(): string {
        return PCG_CAMPAIGN_CORE_PLUGIN_URL;
    }

    /**
     * Cache Management Methods
     */
    public function get_cached_data($key, $group = '', $callback = null, $expiration = 3600) {
        $cache_key = $this->build_cache_key($key, $group);
        $data = $this->cache->get($cache_key);

        if (false === $data && is_callable($callback)) {
            $data = $callback();
            if (false !== $data) {
                $this->cache->set($cache_key, $data, $expiration);
            }
        }

        return $data;
    }

    public function clear_post_cache($post_id, $post = null) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $post_type = get_post_type($post_id);
        if (!$post_type) {
            return;
        }

        // Clear post-specific cache
        $this->cache->delete('post_' . $post_id);
        
        // Clear post type cache
        $this->cache->delete('post_type_' . $post_type);
        
        // Clear related caches
        $this->clear_related_caches($post_id, $post_type);
    }

    public function clear_term_cache($term_id, $tt_id, $taxonomy) {
        // Clear term-specific cache
        $this->cache->delete('term_' . $term_id);
        
        // Clear taxonomy cache
        $this->cache->delete('taxonomy_' . $taxonomy);
    }

    private function clear_related_caches($post_id, $post_type) {
        // Clear author cache
        $author_id = get_post_field('post_author', $post_id);
        if ($author_id) {
            $this->cache->delete('author_' . $author_id);
        }

        // Clear term caches
        $taxonomies = get_object_taxonomies($post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($post_id, $taxonomy);
            if ($terms && !is_wp_error($terms)) {
                foreach ($terms as $term) {
                    $this->cache->delete('term_' . $term->term_id);
                }
            }
        }
    }

    private function build_cache_key($key, $group = '') {
        return $group ? $group . '_' . $key : $key;
    }

    /**
     * Cache Warming Methods
     */
    public function warm_cache() {
        // Warm post type caches
        $post_types = get_post_types(['public' => true]);
        foreach ($post_types as $post_type) {
            $this->warm_post_type_cache($post_type);
        }

        // Warm taxonomy caches
        $taxonomies = get_taxonomies(['public' => true]);
        foreach ($taxonomies as $taxonomy) {
            $this->warm_taxonomy_cache($taxonomy);
        }
    }

    private function warm_post_type_cache($post_type) {
        $posts = get_posts([
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        foreach ($posts as $post_id) {
            $this->get_cached_data('post_' . $post_id, '', function() use ($post_id) {
                return get_post($post_id);
            });
        }
    }

    private function warm_taxonomy_cache($taxonomy) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'fields' => 'ids'
        ]);

        foreach ($terms as $term_id) {
            $this->get_cached_data('term_' . $term_id, '', function() use ($term_id) {
                return get_term($term_id);
            });
        }
    }
} 