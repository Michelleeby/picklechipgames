<?php
namespace PCG\CampaignCore\Core\Options;

class CampaignCoreOptions extends OptionsManager {
    protected static $options = [
        'cache_enabled' => true,
        'cache_expiration' => 3600, // 1 hour
        'debug_mode' => false,
    ];
} 