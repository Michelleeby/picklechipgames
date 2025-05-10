<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignBase\Core\Options as CampaignOptions;

class Options extends CampaignOptions {
    private static $options = [
        'route99_version' => PCG_CAMPAIGN_ROUTE99_VERSION,
        'enable_locations' => true,
        'enable_mysteries' => true,
        'enable_discoveries' => true,
        'default_location_template' => '',
        'default_mystery_template' => '',
        'map_center_lat' => '0',
        'map_center_lng' => '0',
        'map_zoom_level' => '12',
        'weather_api_key' => '',
        'enable_weather_effects' => true,
    ];

    public static function set_defaults() {
        // Call parent method to set base defaults
        parent::set_defaults();

        foreach (self::$options as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }

    public static function get($key, $default = null) {
        // Try to get Route 99 specific option
        $value = get_option($key, $default);
        
        // If not found, try parent options
        if ($value === $default) {
            $value = parent::get($key, $default);
        }
        
        return $value;
    }

    public static function set($key, $value) {
        return update_option($key, $value);
    }

    public static function delete($key) {
        return delete_option($key);
    }

    public static function get_all() {
        // Get parent options
        $options = parent::get_all();
        
        // Add Route 99 specific options
        foreach (self::$options as $key => $default) {
            $options[$key] = self::get($key, $default);
        }
        
        return $options;
    }

    public static function reset() {
        // Reset parent options
        parent::reset();
        
        // Reset Route 99 specific options
        foreach (self::$options as $key => $value) {
            self::delete($key);
        }
        self::set_defaults();
    }
} 