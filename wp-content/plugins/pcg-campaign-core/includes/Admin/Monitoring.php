<?php
namespace CampaignCore\Admin;

class Monitoring {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_campaign_core_reset_stats', [$this, 'ajax_reset_stats']);
    }

    public function add_menu_page() {
        add_menu_page(
            __('Campaign Core', 'campaign-core'),
            __('Campaign Core', 'campaign-core'),
            'manage_options',
            'campaign-core',
            [$this, 'render_page'],
            'dashicons-chart-area',
            30
        );
    }

    public function enqueue_scripts($hook) {
        if ('toplevel_page_campaign-core' !== $hook) {
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

        wp_localize_script('campaign-core-admin', 'campaignCore', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('campaign_core_nonce')
        ]);
    }

    public function render_page() {
        $cache = \PCG\CampaignCore\Core\Cache::get_instance();
        $stats = $cache->get_stats();
        $status = $cache->get_status();
        ?>
        <div class="wrap">
            <h1><?php _e('Campaign Core Monitoring', 'campaign-core'); ?></h1>
            
            <div class="campaign-core-status">
                <h2><?php _e('Cache Status', 'campaign-core'); ?></h2>
                <table class="widefat">
                    <tr>
                        <th><?php _e('WordPress.com', 'campaign-core'); ?></th>
                        <td><?php echo $status['is_wpcom'] ? '✓' : '✗'; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Persistent Cache', 'campaign-core'); ?></th>
                        <td><?php echo $status['has_persistent_cache'] ? '✓' : '✗'; ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Cache Group', 'campaign-core'); ?></th>
                        <td><?php echo esc_html($status['cache_group']); ?></td>
                    </tr>
                </table>
            </div>

            <div class="campaign-core-stats">
                <h2><?php _e('Cache Statistics', 'campaign-core'); ?></h2>
                <table class="widefat">
                    <tr>
                        <th><?php _e('Hit Rate', 'campaign-core'); ?></th>
                        <td><?php echo esc_html($stats['hit_rate']); ?>%</td>
                    </tr>
                    <tr>
                        <th><?php _e('Total Hits', 'campaign-core'); ?></th>
                        <td><?php echo esc_html($stats['hits']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Total Misses', 'campaign-core'); ?></th>
                        <td><?php echo esc_html($stats['misses']); ?></td>
                    </tr>
                    <tr>
                        <th><?php _e('Memory Usage', 'campaign-core'); ?></th>
                        <td><?php echo esc_html($this->format_bytes($stats['memory_usage'])); ?></td>
                    </tr>
                    <?php if ($status['has_persistent_cache']): ?>
                    <tr>
                        <th><?php _e('Cache Size', 'campaign-core'); ?></th>
                        <td><?php echo esc_html($this->format_bytes($stats['cache_size'])); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <div class="campaign-core-performance">
                <h2><?php _e('Performance Metrics', 'campaign-core'); ?></h2>
                <?php foreach ($stats['performance'] as $operation => $metrics): ?>
                <div class="performance-metric">
                    <h3><?php echo esc_html(ucfirst(str_replace('_', ' ', $operation))); ?></h3>
                    <table class="widefat">
                        <tr>
                            <th><?php _e('Average Time', 'campaign-core'); ?></th>
                            <td><?php echo esc_html(number_format($metrics['avg'] * 1000, 2)); ?> ms</td>
                        </tr>
                        <tr>
                            <th><?php _e('Min Time', 'campaign-core'); ?></th>
                            <td><?php echo esc_html(number_format($metrics['min'] * 1000, 2)); ?> ms</td>
                        </tr>
                        <tr>
                            <th><?php _e('Max Time', 'campaign-core'); ?></th>
                            <td><?php echo esc_html(number_format($metrics['max'] * 1000, 2)); ?> ms</td>
                        </tr>
                        <tr>
                            <th><?php _e('Total Operations', 'campaign-core'); ?></th>
                            <td><?php echo esc_html($metrics['count']); ?></td>
                        </tr>
                    </table>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="campaign-core-actions">
                <button type="button" class="button button-primary" id="reset-stats">
                    <?php _e('Reset Statistics', 'campaign-core'); ?>
                </button>
            </div>
        </div>
        <?php
    }

    public function ajax_reset_stats() {
        check_ajax_referer('campaign_core_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $cache = \PCG\CampaignCore\Core\Cache::get_instance();
        $cache->reset_stats();

        wp_send_json_success();
    }

    private function format_bytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
} 