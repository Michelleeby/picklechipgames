<?php
namespace PCG\CampaignCore\Core\Cache;

use PCG\CampaignCore\Core\Cache\Interfaces\CacheRepositoryInterface;
use PCG\CampaignCore\Core\Cache\Interfaces\CacheStatsInterface;
use PCG\CampaignCore\Core\Cache\Interfaces\CacheCleanupInterface;
use PCG\CampaignCore\Core\Cache\Interfaces\SchedulerInterface;

class CacheManager {
    private $repository;
    private $stats;
    private $cleanup;
    private $scheduler;
    private $cache_group;
    private $default_expiration;
    private $is_wpcom;
    private $has_persistent_cache;

    public function __construct(
        CacheRepositoryInterface $repository,
        CacheStatsInterface $stats,
        CacheCleanupInterface $cleanup,
        SchedulerInterface $scheduler,
        string $cache_group = 'campaign_core',
        int $default_expiration = 3600
    ) {
        $this->repository = $repository;
        $this->stats = $stats;
        $this->cleanup = $cleanup;
        $this->scheduler = $scheduler;
        $this->cache_group = $cache_group;
        $this->default_expiration = $default_expiration;
        $this->is_wpcom = defined('IS_WPCOM') && IS_WPCOM;
        $this->has_persistent_cache = wp_using_ext_object_cache();
    }

    public function get(string $key, string $group = '') {
        $start_time = microtime(true);
        $cache_key = $this->build_group($group) . '_' . $key;
        
        if ($this->has_persistent_cache) {
            $data = wp_cache_get($cache_key, $this->cache_group);
            if (false !== $data) {
                $this->stats->incrementHits();
                $this->stats->recordPerformance('get_time', microtime(true) - $start_time);
                return $data;
            }
        }
        
        $data = get_transient($cache_key);
        if (false !== $data) {
            $this->stats->incrementHits();
            $this->stats->recordPerformance('get_time', microtime(true) - $start_time);
            return $data;
        }

        $this->stats->incrementMisses();
        $this->stats->recordPerformance('get_time', microtime(true) - $start_time);
        return false;
    }

    public function set(string $key, $value, string $group = '', ?int $expiration = null): bool {
        $start_time = microtime(true);
        $cache_key = $this->build_group($group) . '_' . $key;
        $expiration = $expiration ?? $this->default_expiration;
        
        if ($this->has_persistent_cache) {
            wp_cache_set($cache_key, $value, $this->cache_group, $expiration);
        }
        
        set_transient($cache_key, $value, $expiration);
        
        $this->stats->incrementSets();
        $this->stats->recordPerformance('set_time', microtime(true) - $start_time);
        return true;
    }

    public function delete(string $key, string $group = ''): bool {
        $start_time = microtime(true);
        $cache_key = $this->build_group($group) . '_' . $key;
        
        if ($this->has_persistent_cache) {
            wp_cache_delete($cache_key, $this->cache_group);
        }
        
        delete_transient($cache_key);
        
        $this->stats->incrementDeletes();
        $this->stats->recordPerformance('delete_time', microtime(true) - $start_time);
        return true;
    }

    public function flush(): void {
        $this->repository->flush($this->cache_group);
    }

    public function getStatus(): array {
        return [
            'is_wpcom' => $this->is_wpcom,
            'has_persistent_cache' => $this->has_persistent_cache,
            'cache_group' => $this->cache_group,
            'default_expiration' => $this->default_expiration,
            'stats' => $this->stats->getStats(),
            'next_cleanup' => $this->scheduler->getNextScheduledTime(),
            'performance_metrics' => $this->stats->getPerformanceMetrics()
        ];
    }

    public function runCleanup(): void {
        $this->cleanup->run();
    }

    public function scheduleCleanup(): void {
        $this->scheduler->scheduleNextCleanup();
    }

    private function build_group(string $group = ''): string {
        return $group ? $this->cache_group . '_' . $group : $this->cache_group;
    }
} 