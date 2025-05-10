<?php
namespace PCG\CampaignCore\Core\Cache\Repositories;

use PCG\CampaignCore\Core\Cache\Interfaces\CacheRepositoryInterface;

class WordPressCacheRepository implements CacheRepositoryInterface {
    private $wpdb;
    private $cache;

    public function __construct(\wpdb $wpdb, $cache) {
        $this->wpdb = $wpdb;
        $this->cache = $cache;
    }

    public function getAllItems(): array {
        $items = [];
        
        // Get transient cache items
        $transients = $this->wpdb->get_results(
            "SELECT option_name, option_value 
            FROM {$this->wpdb->options} 
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
            $items = array_merge($items, $this->getPersistentItems());
        }
        
        return $items;
    }

    public function delete(string $key, string $group): bool {
        return $this->cache->delete($key, $group);
    }

    public function get(string $key, string $group) {
        return $this->cache->get($key, $group);
    }

    public function set(string $key, $value, string $group, int $expiration = 0): bool {
        return $this->cache->set($key, $value, $group, $expiration);
    }

    private function getPersistentItems(): array {
        // Implementation would depend on the specific object cache being used
        return [];
    }
} 