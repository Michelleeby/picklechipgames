<?php
  namespace PCG\AccessControl\Core;

use PCG\CampaignCore\Core\Container as BaseContainer;

class Container extends BaseContainer {
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
        $this['plugin'] = function($c) {
            return new Plugin();
        };
        // Register other access-control-specific services here
    }

    public function offsetGet($offset) {
        if (parent::offsetExists($offset)) {
            return parent::offsetGet($offset);
        }
        // Delegate to core container if not found locally
        $coreContainer = \PCG\CampaignCore\Core\Container::getInstance();
        return $coreContainer->offsetExists($offset) ? $coreContainer->offsetGet($offset) : null;
    }
}