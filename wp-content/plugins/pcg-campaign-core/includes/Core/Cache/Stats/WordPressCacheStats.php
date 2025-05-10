<?php
namespace PCG\CampaignCore\Core\Cache\Stats;

use PCG\CampaignCore\Core\Cache\Interfaces\CacheStatsInterface;

class WordPressCacheStats implements CacheStatsInterface {
    private const OPTION_LAST_CLEANUP = 'campaign_core_last_cache_cleanup';
    private const OPTION_CLEANUP_STATS = 'campaign_core_last_cleanup_stats';
    private const OPTION_TOTAL_ITEMS = 'campaign_core_cache_total_items';
    private const OPTION_TOTAL_SIZE = 'campaign_core_cache_total_size';

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

    public function __construct() {
        $this->loadStats();
    }

    public function incrementHits(): void {
        $this->stats['hits']++;
        $this->saveStats();
    }

    public function incrementMisses(): void {
        $this->stats['misses']++;
        $this->saveStats();
    }

    public function incrementSets(): void {
        $this->stats['sets']++;
        $this->saveStats();
    }

    public function incrementDeletes(): void {
        $this->stats['deletes']++;
        $this->saveStats();
    }

    public function recordPerformance(string $operation, float $time): void {
        if (!isset($this->stats['performance'][$operation])) {
            $this->stats['performance'][$operation] = [];
        }
        
        // Keep only the last 1000 measurements
        if (count($this->stats['performance'][$operation]) >= 1000) {
            array_shift($this->stats['performance'][$operation]);
        }
        
        $this->stats['performance'][$operation][] = $time;
        $this->saveStats();
    }

    public function getStats(): array {
        $stats = $this->stats;
        $total_requests = $stats['hits'] + $stats['misses'];
        
        $stats['hit_rate'] = $total_requests > 0 ? 
            round(($stats['hits'] / $total_requests) * 100, 2) : 0;
        
        $stats['uptime'] = time() - ($stats['last_reset'] ?? time());
        
        return $stats;
    }

    public function getPerformanceMetrics(): array {
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

    public function reset(): void {
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
        $this->saveStats();
    }

    private function loadStats(): void {
        $saved_stats = get_option('campaign_core_cache_stats', []);
        if (!empty($saved_stats)) {
            $this->stats = array_merge($this->stats, $saved_stats);
        }
    }

    private function saveStats(): void {
        update_option('campaign_core_cache_stats', $this->stats);
    }

    public function recordCleanup(int $cleanedItems, int $totalItems): void {
        update_option(self::OPTION_LAST_CLEANUP, time());
        update_option(self::OPTION_CLEANUP_STATS, [
            'items_cleaned' => $cleanedItems,
            'total_items' => $totalItems,
            'timestamp' => time()
        ]);
    }

    public function getLastCleanupTime(): ?int {
        return get_option(self::OPTION_LAST_CLEANUP);
    }

    public function getTotalItems(): int {
        return (int) get_option(self::OPTION_TOTAL_ITEMS, 0);
    }

    public function getTotalSize(): int {
        return (int) get_option(self::OPTION_TOTAL_SIZE, 0);
    }

    public function updateTotals(int $items, int $size): void {
        update_option(self::OPTION_TOTAL_ITEMS, $items);
        update_option(self::OPTION_TOTAL_SIZE, $size);
    }
} 