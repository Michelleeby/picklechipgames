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
        add_action('pre_get_posts', [$this, 'filter_campaign_entries_by_campaign']);
        
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

        add_submenu_page(
            'route99',
            __('Entries', 'route99'),
            __('Entries', 'route99'),
            'edit_posts',
            'route99-entries',
            function() {
                // Get the campaign post by slug
                $campaign = get_page_by_path('route-99', OBJECT, 'campaign');
                if ($campaign) {
                    $url = add_query_arg([
                        'post_type'   => 'cc_campaign_entry',
                        'campaign_id' => $campaign->ID,
                    ], admin_url('edit.php'));
                } else {
                    // Fallback: go to entries list without filter
                    $url = admin_url('edit.php?post_type=cc_campaign_entry');
                }
                echo '<script>window.location.replace(' . wp_json_encode($url) . ');</script>';
                exit;
            }
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
        // Handle form submission
        if (isset($_POST['route99_campaign_settings_nonce']) && wp_verify_nonce($_POST['route99_campaign_settings_nonce'], 'route99_campaign_settings')) {
            if (current_user_can('manage_options')) {
                update_option('campaign_title', sanitize_text_field($_POST['campaign_title']));
                update_option('campaign_description', sanitize_textarea_field($_POST['campaign_description']));
                update_option('campaign_image', esc_url_raw($_POST['campaign_image']));
                echo '<div class="updated"><p>' . __('Campaign settings updated.', 'route99') . '</p></div>';
            }
        }

        $title = get_option('campaign_title', 'Route 99');
        $description = get_option('campaign_description', 'A Kids on Bikes campaign set on Route 99.');
        $image = get_option('campaign_image', '');
        ?>
        <div class="wrap">
            <h1><?php _e('Route 99 Campaign Settings', 'route99'); ?></h1>
            <form method="post" action="">
                <?php wp_nonce_field('route99_campaign_settings', 'route99_campaign_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="campaign_title"><?php _e('Campaign Title', 'route99'); ?></label></th>
                        <td><input name="campaign_title" type="text" id="campaign_title" value="<?php echo esc_attr($title); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="campaign_description"><?php _e('Campaign Description', 'route99'); ?></label></th>
                        <td><textarea name="campaign_description" id="campaign_description" rows="5" class="large-text"><?php echo esc_textarea($description); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="campaign_image"><?php _e('Campaign Image URL', 'route99'); ?></label></th>
                        <td><input name="campaign_image" type="text" id="campaign_image" value="<?php echo esc_url($image); ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <?php submit_button(__('Save Campaign Settings', 'route99')); ?>
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

    public function filter_campaign_entries_by_campaign($query) {
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        global $pagenow;
        if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'cc_campaign_entry' && isset($_GET['associated_campaign'])) {
            $meta_query = $query->get('meta_query', []);
            $meta_query[] = [
                'key' => '_associated_campaign',
                'value' => sanitize_text_field($_GET['associated_campaign']),
                'compare' => '=',
            ];
            $query->set('meta_query', $meta_query);
        }
    }
} 