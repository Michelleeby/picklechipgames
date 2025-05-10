<?php
namespace PCG\CampaignCore\Core;

class Cache {
    private static $instance = null;
    private $cache_group = 'campaign_core';
    private $default_expiration = 3600; // 1 hour
    private $is_wpcom;
    private $has_persistent_cache;
    private $stats = [
        'hits' => 0,
        'misses' => 0,
        'sets' => 0,
        'deletes' => 0,
        'last_reset' => null,
        'performance' => [
            'get_time' => [],
            'set_time' => [],
            'delete_time' => []
        ]
    ];

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->is_wpcom = defined('IS_WPCOM') && IS_WPCOM;
        $this->has_persistent_cache = wp_using_ext_object_cache();
        $this->stats['last_reset'] = time();
        $this->load_stats();
    }

    public function get($key, $group = '') {
        $start_time = microtime(true);
        $cache_key = $this->build_key($key, $group);
        
        if ($this->has_persistent_cache) {
            $data = wp_cache_get($cache_key, $this->cache_group);
            if (false !== $data) {
                $this->increment_stat('hits');
                $this->record_performance('get_time', microtime(true) - $start_time);
                return $data;
            }
        }
        
        $data = get_transient($cache_key);
        if (false !== $data) {
            $this->increment_stat('hits');
            $this->record_performance('get_time', microtime(true) - $start_time);
            return $data;
        }

        $this->increment_stat('misses');
        $this->record_performance('get_time', microtime(true) - $start_time);
        return false;
    }

    public function set($key, $data, $expiration = null, $group = '') {
        $start_time = microtime(true);
        $cache_key = $this->build_key($key, $group);
        $expiration = $expiration ?: $this->default_expiration;
        
        if ($this->has_persistent_cache) {
            wp_cache_set($cache_key, $data, $this->cache_group, $expiration);
        }
        
        set_transient($cache_key, $data, $expiration);
        
        $this->increment_stat('sets');
        $this->record_performance('set_time', microtime(true) - $start_time);
    }

    public function delete($key, $group = '') {
        $start_time = microtime(true);
        $cache_key = $this->build_key($key, $group);
        
        if ($this->has_persistent_cache) {
            wp_cache_delete($cache_key, $this->cache_group);
        }
        
        delete_transient($cache_key);
        
        $this->increment_stat('deletes');
        $this->record_performance('delete_time', microtime(true) - $start_time);
    }

    private function build_key($key, $group = '') {
        $prefix = 'campaign_core_';
        if ($group) {
            $prefix .= $group . '_';
        }
        return $prefix . $key;
    }

    public function get_status() {
        return [
            'is_wpcom' => $this->is_wpcom,
            'has_persistent_cache' => $this->has_persistent_cache,
            'cache_group' => $this->cache_group,
            'default_expiration' => $this->default_expiration,
            'performance_metrics' => $this->get_performance_metrics()
        ];
    }

    public function get_stats() {
        $stats = $this->stats;
        $total_requests = $stats['hits'] + $stats['misses'];
        
        $stats['hit_rate'] = $total_requests > 0 ? 
            round(($stats['hits'] / $total_requests) * 100, 2) : 0;
        
        $stats['memory_usage'] = $this->get_memory_usage();
        $stats['uptime'] = time() - $stats['last_reset'];
        
        if ($this->has_persistent_cache) {
            $stats['cache_size'] = $this->get_cache_size();
        }
        
        $stats['performance'] = $this->get_performance_metrics();
        
        return $stats;
    }

    private function get_performance_metrics() {
        $metrics = [];
        foreach ($this->stats['performance'] as $operation => $times) {
            if (!empty($times)) {
                $metrics[$operation] = [
                    'avg' => array_sum($times) / count($times),
                    'min' => min($times),
                    'max' => max($times),
                    'count' => count($times)
                ];
            }
        }
        return $metrics;
    }

    private function record_performance($operation, $time) {
        if (!isset($this->stats['performance'][$operation])) {
            $this->stats['performance'][$operation] = [];
        }
        
        // Keep only the last 1000 measurements
        if (count($this->stats['performance'][$operation]) >= 1000) {
            array_shift($this->stats['performance'][$operation]);
        }
        
        $this->stats['performance'][$operation][] = $time;
        $this->save_stats();
    }

    private function get_memory_usage() {
        if (!$this->has_persistent_cache) {
            return 0;
        }

        $size = 0;
        $keys = wp_cache_get('campaign_core_cache_keys', $this->cache_group);
        
        if (is_array($keys)) {
            foreach ($keys as $key) {
                $data = wp_cache_get($key, $this->cache_group);
                if ($data !== false) {
                    $size += strlen(serialize($data));
                }
            }
        }
        
        return $size;
    }

    private function get_cache_size() {
        if (!$this->has_persistent_cache) {
            return 0;
        }

        $stats = wp_cache_get_stats();
        return isset($stats['bytes']) ? $stats['bytes'] : 0;
    }

    private function increment_stat($stat) {
        if (isset($this->stats[$stat])) {
            $this->stats[$stat]++;
            $this->save_stats();
        }
    }

    private function load_stats() {
        $saved_stats = get_option('campaign_core_cache_stats', []);
        if (!empty($saved_stats)) {
            $this->stats = array_merge($this->stats, $saved_stats);
        }
    }

    private function save_stats() {
        update_option('campaign_core_cache_stats', $this->stats);
    }

    public function reset_stats() {
        $this->stats = [
            'hits' => 0,
            'misses' => 0,
            'sets' => 0,
            'deletes' => 0,
            'last_reset' => time(),
            'performance' => [
                'get_time' => [],
                'set_time' => [],
                'delete_time' => []
            ]
        ];
        $this->save_stats();
    }
} 