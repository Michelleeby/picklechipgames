<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignCore\Core\Cache as CoreCache;

class CacheManager {
    private $cache;
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->cache = CoreCache::get_instance();
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('save_post_route99_location', [$this, 'clear_location_cache'], 10, 2);
        add_action('save_post_route99_mystery', [$this, 'clear_mystery_cache'], 10, 2);
        add_action('edited_route99_location_type', [$this, 'clear_location_type_cache'], 10, 3);
        add_action('edited_route99_mystery_type', [$this, 'clear_mystery_type_cache'], 10, 3);
        add_action('campaign_core_cache_cleanup', [$this, 'cleanup_route99_cache']);
    }

    public function clear_location_cache($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->cache->delete('location_' . $post_id, 'route99');
        $this->cache->delete('locations_list', 'route99');
        $this->cache->set('active_campaign_location_' . $post_id, true, 'route99', 86400);
    }

    public function clear_mystery_cache($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $this->cache->delete('mystery_' . $post_id, 'route99');
        $this->cache->delete('mysteries_list', 'route99');
        $this->cache->set('active_campaign_mystery_' . $post_id, true, 'route99', 86400);
    }

    public function clear_location_type_cache($term_id, $tt_id, $taxonomy) {
        $this->cache->delete('location_type_' . $term_id, 'route99');
        $this->cache->delete('location_types_list', 'route99');
        $this->cache->set('recent_location_type_' . $term_id, true, 'route99', 604800);
    }

    public function clear_mystery_type_cache($term_id, $tt_id, $taxonomy) {
        $this->cache->delete('mystery_type_' . $term_id, 'route99');
        $this->cache->delete('mystery_types_list', 'route99');
        $this->cache->set('recent_mystery_type_' . $term_id, true, 'route99', 604800);
    }

    public function cleanup_route99_cache() {
        $cached_items = $this->get_route99_cached_items();
        
        foreach ($cached_items as $item) {
            $retention_period = $this->determine_route99_retention_period($item);
            $last_accessed = $this->get_last_accessed_time($item);
            
            if (time() - $last_accessed > $retention_period) {
                $this->cache->delete($item['key'], 'route99');
            }
        }
    }

    private function get_route99_cached_items() {
        global $wpdb;
        
        $items = [];
        $transients = $wpdb->get_results(
            "SELECT option_name, option_value 
            FROM {$wpdb->options} 
            WHERE option_name LIKE '%_transient_route99_%'"
        );
        
        foreach ($transients as $transient) {
            $key = str_replace('_transient_route99_', '', $transient->option_name);
            $items[] = [
                'key' => $key,
                'group' => 'route99',
                'value' => $transient->option_value,
                'last_accessed' => get_option('_transient_timeout_route99_' . $key, 0)
            ];
        }
        
        return $items;
    }

    private function determine_route99_retention_period($item) {
        if (strpos($item['key'], 'active_campaign_') !== false) {
            return 86400; // 24 hours
        }
        
        if (strpos($item['key'], 'recent_') !== false) {
            return 604800; // 7 days
        }
        
        if (strpos($item['key'], 'historical_') !== false) {
            return 2592000; // 30 days
        }
        
        return 31536000; // 1 year
    }

    private function get_last_accessed_time($item) {
        return isset($item['last_accessed']) ? $item['last_accessed'] : 0;
    }
} 