<?php
namespace PCG\CampaignCore\Core\Cache\Interfaces;

interface CacheCleanupInterface {
    public function run(): void;
} 