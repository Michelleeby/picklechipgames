<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignCore\Core\Cache\CacheManager;

class Cache {
    private $cache_manager;

    public function __construct(CacheManager $cache_manager) {
        $this->cache_manager = $cache_manager;
    }

    /**
     * Get cached data with Route 99 specific group
     */
    public function get($key, $group = '') {
        return $this->cache_manager->get($key, $group);
    }

    /**
     * Set cached data with Route 99 specific group
     */
    public function set($key, $data, $expiration = null, $group = '') {
        return $this->cache_manager->set($key, $data, $group, $expiration);
    }

    /**
     * Delete cached data with Route 99 specific group
     */
    public function delete($key, $group = '') {
        return $this->cache_manager->delete($key, $group);
    }

    /**
     * Get cache status information including Route 99 specific data
     */
    public function get_status() {
        return $this->cache_manager->getStatus();
    }

    /**
     * Clear all Route 99 related caches
     */
    public function flush() {
        $this->cache_manager->flush();
    }
} 