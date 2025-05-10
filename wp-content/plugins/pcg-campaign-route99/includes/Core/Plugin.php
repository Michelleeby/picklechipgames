<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignCore\Core\Cache\CacheManager;
use PCG\CampaignCore\Core\Interfaces\DatabaseManagerInterface;
use PCG\CampaignCore\Core\Interfaces\OptionsManagerInterface;
use PCG\CampaignCore\Core\Interfaces\PluginInterface;

class Plugin implements PluginInterface {
    private $cacheManager;
    private $database;
    private $options;
    private $postTypeManager;
    private $taxonomyManager;
    private $blockManager;

    public function __construct(
        CacheManager $cacheManager,
        DatabaseManagerInterface $database,
        OptionsManagerInterface $options
    ) {
        $this->cacheManager = $cacheManager;
        $this->database = $database;
        $this->options = $options;

        $this->initManagers();
        $this->initHooks();
    }

    private function initManagers(): void {
        $this->postTypeManager = new PostTypes\PostTypeManager('route99');
        $this->taxonomyManager = new Taxonomies\TaxonomyManager('route99');
        $this->blockManager = new Blocks\BlockManager('route99', PCG_CAMPAIGN_ROUTE99_PLUGIN_URL);
    }

    private function initHooks(): void {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        
        // Register activation/deactivation hooks
        register_activation_hook(PCG_CAMPAIGN_ROUTE99_PLUGIN_DIR . 'pcg-campaign-route99.php', [$this, 'activate']);
        register_deactivation_hook(PCG_CAMPAIGN_ROUTE99_PLUGIN_DIR . 'pcg-campaign-route99.php', [$this, 'deactivate']);
    }

    public function init(): void {
        $this->postTypeManager->register_post_types();
        $this->taxonomyManager->register_taxonomies();
        $this->blockManager->register_blocks();
    }

    public function addMenuPage(): void {
        add_menu_page(
            __('Route 99', 'route99'),
            __('Route 99', 'route99'),
            'manage_options',
            'route99',
            [$this, 'renderAdminPage'],
            'dashicons-location',
            30
        );

        add_submenu_page(
            'route99',
            __('Settings', 'route99'),
            __('Settings', 'route99'),
            'manage_options',
            'route99-settings',
            [$this, 'renderSettingsPage']
        );
    }

    public function enqueueAdminAssets(): void {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['toplevel_page_route99', 'route99_page_route99-settings'])) {
            return;
        }

        wp_enqueue_style(
            'route99-admin',
            PCG_CAMPAIGN_ROUTE99_PLUGIN_URL . 'assets/css/admin.css',
            [],
            PCG_CAMPAIGN_ROUTE99_VERSION
        );

        wp_enqueue_script(
            'route99-admin',
            PCG_CAMPAIGN_ROUTE99_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            PCG_CAMPAIGN_ROUTE99_VERSION,
            true
        );
    }

    public function renderAdminPage(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Route 99', 'route99'); ?></h1>
            <div class="route99-admin-content">
                <div class="route99-stats">
                    <h2><?php _e('Campaign Statistics', 'route99'); ?></h2>
                    <?php $this->renderStats(); ?>
                </div>
            </div>
        </div>
        <?php
    }

    public function renderSettingsPage(): void {
        ?>
        <div class="wrap">
            <h1><?php _e('Route 99 Settings', 'route99'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('route99_options');
                do_settings_sections('route99-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    private function renderStats(): void {
        $location_count = wp_count_posts('route99_location');
        $mystery_count = wp_count_posts('route99_mystery');

        $location_published = isset($location_count->publish) ? $location_count->publish : 0;
        $mystery_published = isset($mystery_count->publish) ? $mystery_count->publish : 0;
        ?>
        <div class="route99-stats-grid">
            <div class="route99-stat-box">
                <h3><?php _e('Locations', 'route99'); ?></h3>
                <p class="route99-stat-number"><?php echo esc_html($location_published); ?></p>
            </div>
            <div class="route99-stat-box">
                <h3><?php _e('Mysteries', 'route99'); ?></h3>
                <p class="route99-stat-number"><?php echo esc_html($mystery_published); ?></p>
            </div>
        </div>
        <?php
    }

    public function activate(): void {
        $this->database->create_tables();
        $this->options->set_defaults();
        $this->cacheManager->scheduleCleanup();
    }

    public function deactivate(): void {
        // Clean up if necessary
    }

    public function get_plugin_name(): string {
        return 'Route 99';
    }

    public function get_plugin_version(): string {
        return PCG_CAMPAIGN_ROUTE99_VERSION;
    }

    public function get_plugin_path(): string {
        return PCG_CAMPAIGN_ROUTE99_PLUGIN_DIR;
    }

    public function get_plugin_url(): string {
        return PCG_CAMPAIGN_ROUTE99_PLUGIN_URL;
    }
} 