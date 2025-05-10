<?php
namespace PCG\CampaignCore\Core\Cache\Schedulers;

use PCG\CampaignCore\Core\Cache\Interfaces\SchedulerInterface;

class ActionScheduler implements SchedulerInterface {
    private const HOOK = 'campaign_core_cache_cleanup';
    private const GROUP = 'campaign-core-cache';

    public function scheduleNextCleanup(): void {
        if (!class_exists('ActionScheduler')) {
            return;
        }

        as_schedule_single_action(
            time() + 86400,
            self::HOOK,
            [],
            self::GROUP
        );
    }

    public function getNextScheduledTime(): ?int {
        if (!class_exists('ActionScheduler')) {
            return null;
        }

        return as_next_scheduled_action(self::HOOK);
    }

    public function cancelScheduledCleanup(): void {
        if (!class_exists('ActionScheduler')) {
            return;
        }

        as_unschedule_all_actions(self::HOOK);
    }
} 