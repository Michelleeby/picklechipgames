<?php
namespace PCG\CampaignCore\Core;

class CacheCleanup {
    private static $instance = null;
    private $cache;
    
    // Retention periods in seconds
    const RETENTION_PERIODS = [
        'active' => 86400,        // 24 hours for active campaign data
        'recent' => 604800,       // 7 days for recently accessed data
        'historical' => 2592000,  // 30 days for historical data
        'archived' => 31536000,   // 1 year for archived data
    ];

    // Action Scheduler hook name
    const CLEANUP_HOOK = 'campaign_core_cache_cleanup';

    private function __construct() {
        $this->cache = Cache::get_instance();
        $this->init_hooks();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_hooks() {
        // Initialize Action Scheduler
        add_action('init', [$this, 'init_action_scheduler']);
        
        // Add admin menu for manual cleanup
        add_action('admin_menu', [$this, 'add_cleanup_menu']);
        
        // Add Action Scheduler hooks
        add_action(self::CLEANUP_HOOK, [$this, 'run_cleanup']);
        add_action('campaign_core_after_cleanup', [$this, 'schedule_next_cleanup']);
    }

    public function init_action_scheduler() {
        if (!class_exists('ActionScheduler')) {
            return;
        }

        // Schedule initial cleanup if not already scheduled
        if (!as_next_scheduled_action(self::CLEANUP_HOOK)) {
            $this->schedule_next_cleanup();
        }
    }

    private function schedule_next_cleanup() {
        if (!class_exists('ActionScheduler')) {
            return;
        }

        // Schedule next cleanup in 24 hours
        as_schedule_single_action(
            time() + 86400,
            self::CLEANUP_HOOK,
            [],
            'campaign-core-cache'
        );
    }

    public function add_cleanup_menu() {
        add_submenu_page(
            'campaign-core',
            __('Cache Management', 'campaign-core'),
            __('Cache Management', 'campaign-core'),
            'manage_options',
            'campaign-cache-management',
            [$this, 'render_cleanup_page']
        );
    }

    public function render_cleanup_page() {
        if (isset($_POST['campaign_cache_cleanup']) && check_admin_referer('campaign_cache_cleanup')) {
            $this->run_cleanup();
            echo '<div class="notice notice-success"><p>' . __('Cache cleanup completed.', 'campaign-core') . '</p></div>';
        }

        $stats = $this->get_cache_stats();
        $next_scheduled = $this->get_next_scheduled_cleanup();
        ?>
        <div class="wrap">
            <h1><?php _e('Campaign Cache Management', 'campaign-core'); ?></h1>
            
            <div class="card">
                <h2><?php _e('Cache Statistics', 'campaign-core'); ?></h2>
                <p><?php printf(__('Total cached items: %d', 'campaign-core'), $stats['total_items']); ?></p>
                <p><?php printf(__('Total cache size: %s', 'campaign-core'), size_format($stats['total_size'])); ?></p>
                <p><?php printf(__('Last cleanup: %s', 'campaign-core'), $stats['last_cleanup']); ?></p>
                <p><?php printf(__('Next scheduled cleanup: %s', 'campaign-core'), $next_scheduled); ?></p>
            </div>

            <div class="card">
                <h2><?php _e('Retention Policy', 'campaign-core'); ?></h2>
                <ul>
                    <li><?php _e('Active campaign data: 24 hours', 'campaign-core'); ?></li>
                    <li><?php _e('Recently accessed data: 7 days', 'campaign-core'); ?></li>
                    <li><?php _e('Historical data: 30 days', 'campaign-core'); ?></li>
                    <li><?php _e('Archived data: 1 year', 'campaign-core'); ?></li>
                </ul>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('campaign_cache_cleanup'); ?>
                <p class="submit">
                    <input type="submit" name="campaign_cache_cleanup" class="button button-primary" 
                           value="<?php _e('Run Cache Cleanup Now', 'campaign-core'); ?>">
                </p>
            </form>
        </div>
        <?php
    }

    public function run_cleanup() {
        global $wpdb;

        // Get all cached items
        $cached_items = $this->get_all_cached_items();
        $cleaned_items = 0;
        
        foreach ($cached_items as $item) {
            $retention_period = $this->determine_retention_period($item);
            $last_accessed = $this->get_last_accessed_time($item);
            
            if (time() - $last_accessed > $retention_period) {
                $this->cache->delete($item['key'], $item['group']);
                $cleaned_items++;
            }
        }

        // Update last cleanup time and stats
        update_option('campaign_core_last_cache_cleanup', time());
        update_option('campaign_core_last_cleanup_stats', [
            'items_cleaned' => $cleaned_items,
            'total_items' => count($cached_items),
            'timestamp' => time()
        ]);

        // Schedule next cleanup
        do_action('campaign_core_after_cleanup');
    }

    private function get_all_cached_items() {
        global $wpdb;
        
        $items = [];
        
        // Get transient cache items
        $transients = $wpdb->get_results(
            "SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE '%_transient_campaign_%'"
        );
        
        foreach ($transients as $transient) {
            $key = str_replace('_transient_campaign_', '', $transient->option_name);
            $items[] = [
                'key' => $key,
                'group' => 'transient',
                'value' => $transient->option_value,
                'last_accessed' => get_option('_transient_timeout_campaign_' . $key, 0)
            ];
        }
        
        // Get persistent cache items if available
        if (wp_using_ext_object_cache()) {
            $persistent_items = $this->get_persistent_cache_items();
            $items = array_merge($items, $persistent_items);
        }
        
        return $items;
    }

    private function get_persistent_cache_items() {
        // This would need to be implemented based on the specific object cache being used
        // For example, with Redis or Memcached, you'd use their specific APIs
        return [];
    }

    private function determine_retention_period($item) {
        // Determine retention period based on item type and usage
        if (strpos($item['key'], 'active_campaign') !== false) {
            return self::RETENTION_PERIODS['active'];
        }
        
        if (strpos($item['key'], 'recent_') !== false) {
            return self::RETENTION_PERIODS['recent'];
        }
        
        if (strpos($item['key'], 'historical_') !== false) {
            return self::RETENTION_PERIODS['historical'];
        }
        
        return self::RETENTION_PERIODS['archived'];
    }

    private function get_last_accessed_time($item) {
        return isset($item['last_accessed']) ? $item['last_accessed'] : 0;
    }

    private function get_cache_stats() {
        $items = $this->get_all_cached_items();
        $total_size = 0;
        
        foreach ($items as $item) {
            $total_size += strlen(serialize($item['value']));
        }
        
        return [
            'total_items' => count($items),
            'total_size' => $total_size,
            'last_cleanup' => get_option('campaign_core_last_cache_cleanup') 
                ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), 
                           get_option('campaign_core_last_cache_cleanup'))
                : __('Never', 'campaign-core')
        ];
    }

    private function get_next_scheduled_cleanup() {
        if (!class_exists('ActionScheduler')) {
            return __('Action Scheduler not available', 'campaign-core');
        }

        $next_scheduled = as_next_scheduled_action(self::CLEANUP_HOOK);
        return $next_scheduled 
            ? date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_scheduled)
            : __('Not scheduled', 'campaign-core');
    }

    public function __destruct() {
        if (class_exists('ActionScheduler')) {
            as_unschedule_all_actions(self::CLEANUP_HOOK);
        }
    }
} 