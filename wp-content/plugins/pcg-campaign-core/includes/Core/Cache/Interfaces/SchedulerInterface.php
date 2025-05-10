<?php
namespace PCG\CampaignCore\Core\Cache\Interfaces;

interface SchedulerInterface {
    public function scheduleNextCleanup(): void;
    public function getNextScheduledTime(): ?int;
    public function cancelScheduledCleanup(): void;
} 