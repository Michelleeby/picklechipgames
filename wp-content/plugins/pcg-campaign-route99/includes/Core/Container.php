<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignCore\Core\Container as CoreContainer;
use PCG\CampaignCore\Core\Cache\CacheManager;
use PCG\CampaignCore\Core\Interfaces\DatabaseManagerInterface;
use PCG\CampaignCore\Core\Interfaces\OptionsManagerInterface;
use Pimple\Container as PimpleContainer;

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
        $this->initRoute99Services();
    }

    private function initRoute99Services(): void {
        // Route 99 specific services
        $this['route99.cache'] = function($c) {
            return new Cache($c['cache.manager']);
        };

        $this['route99.post_type_manager'] = function($c) {
            return new PostTypeManager();
        };

        $this['route99.taxonomy_manager'] = function($c) {
            return new TaxonomyManager();
        };

        $this['route99.block_manager'] = function($c) {
            return new BlockManager();
        };

        // Register services
        $this['cache'] = function($c) {
            $coreContainer = CoreContainer::getInstance();
            return $coreContainer['cache.manager'];
        };

        $this['database'] = function($c) {
            $coreContainer = CoreContainer::getInstance();
            return $coreContainer['database'];
        };

        $this['options'] = function($c) {
            $coreContainer = CoreContainer::getInstance();
            return $coreContainer['options'];
        };

        // Override plugin service with Route 99 implementation
        $this['plugin'] = function($c) {
            return new Plugin(
                $c['cache'],
                $c['database'],
                $c['options']
            );
        };
    }
} 