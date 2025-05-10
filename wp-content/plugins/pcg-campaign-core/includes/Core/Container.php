<?php
namespace PCG\CampaignCore\Core;

use Pimple\Container as PimpleContainer;
use PCG\CampaignCore\Core\Options\CampaignCoreOptions;

class Container extends PimpleContainer {
    private static $instance = null;

    public static function getInstance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        parent::__construct();
        $this->initServices();
    }

    private function initServices(): void {
        // Core services
        $this['cache.repository'] = function($c) {
            global $wp_object_cache;
            return new Cache\Repositories\WordPressCacheRepository($GLOBALS['wpdb'], $wp_object_cache);
        };

        $this['cache.stats'] = function($c) {
            return new Cache\Stats\WordPressCacheStats();
        };

        $this['cache.scheduler'] = function($c) {
            return new Cache\Schedulers\ActionScheduler();
        };

        $this['cache.cleanup'] = function($c) {
            return new Cache\CacheCleanup(
                $c['cache.repository'],
                $c['cache.stats'],
                $c['cache.scheduler']
            );
        };

        $this['cache.manager'] = function($c) {
            return new Cache\CacheManager(
                $c['cache.repository'],
                $c['cache.stats'],
                $c['cache.cleanup'],
                $c['cache.scheduler']
            );
        };

        $this['database'] = function($c) {
            return new Database\DatabaseManager();
        };

        $this['options'] = function($c) {
            return new CampaignCoreOptions('campaign_core');
        };

        // Plugin services
        $this['plugin'] = function($c) {
            return new Plugin(
                $c['cache.manager'],
                $c['database'],
                $c['options']
            );
        };
    }
} 