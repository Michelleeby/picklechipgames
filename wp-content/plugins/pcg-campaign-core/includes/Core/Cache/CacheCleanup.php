<?php
namespace PCG\CampaignCore\Core\Cache;

use PCG\CampaignCore\Core\Cache\Interfaces\CacheCleanupInterface;
use PCG\CampaignCore\Core\Cache\Interfaces\CacheRepositoryInterface;
use PCG\CampaignCore\Core\Cache\Interfaces\CacheStatsInterface;
use PCG\CampaignCore\Core\Cache\Interfaces\SchedulerInterface;

class CacheCleanup implements CacheCleanupInterface {
    private CacheRepositoryInterface $repository;
    private CacheStatsInterface $stats;
    private SchedulerInterface $scheduler;
    private array $retentionPeriods;

    public function __construct(
        CacheRepositoryInterface $repository,
        CacheStatsInterface $stats,
        SchedulerInterface $scheduler,
        array $retentionPeriods = []
    ) {
        $this->repository = $repository;
        $this->stats = $stats;
        $this->scheduler = $scheduler;
        $this->retentionPeriods = $retentionPeriods ?: [
            'active' => 86400,        // 24 hours
            'recent' => 604800,       // 7 days
            'historical' => 2592000,  // 30 days
            'archived' => 31536000,   // 1 year
        ];
    }

    public function run(): void {
        $items = $this->repository->getAllItems();
        $cleanedItems = 0;

        foreach ($items as $item) {
            if ($this->shouldCleanup($item)) {
                $this->repository->delete($item['key'], $item['group']);
                $cleanedItems++;
            }
        }

        $this->stats->recordCleanup($cleanedItems, count($items));
        $this->scheduler->scheduleNextCleanup();
    }

    private function shouldCleanup(array $item): bool {
        $retentionPeriod = $this->getRetentionPeriod($item);
        $lastAccessed = $this->getLastAccessedTime($item);
        
        return time() - $lastAccessed > $retentionPeriod;
    }

    private function getRetentionPeriod(array $item): int {
        if (strpos($item['key'], 'active_campaign') !== false) {
            return $this->retentionPeriods['active'];
        }
        
        if (strpos($item['key'], 'recent_') !== false) {
            return $this->retentionPeriods['recent'];
        }
        
        if (strpos($item['key'], 'historical_') !== false) {
            return $this->retentionPeriods['historical'];
        }
        
        return $this->retentionPeriods['archived'];
    }

    private function getLastAccessedTime(array $item): int {
        return $item['last_accessed'] ?? 0;
    }
} 