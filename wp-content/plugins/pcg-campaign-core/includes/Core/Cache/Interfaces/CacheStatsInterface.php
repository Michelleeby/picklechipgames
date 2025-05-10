<?php
namespace PCG\CampaignCore\Core\Cache\Interfaces;

interface CacheStatsInterface {
    public function recordCleanup(int $cleanedItems, int $totalItems): void;
    public function getStats(): array;
    public function getLastCleanupTime(): ?int;
    public function getTotalItems(): int;
    public function getTotalSize(): int;
} 