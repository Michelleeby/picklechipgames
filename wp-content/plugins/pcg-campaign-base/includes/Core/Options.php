<?php
namespace PCG\CampaignBase\Core;

class Options {
    private static $options = [
        'campaign_version' => PCG_CAMPAIGN_VERSION,
        'enable_character_sheets' => true,
        'enable_session_logs' => true,
        'enable_relationships' => true,
        'enable_timeline' => true,
        'default_character_template' => '',
        'default_session_template' => '',
    ];

    public static function set_defaults() {
        foreach (self::$options as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
    }

    public static function get($key, $default = null) {
        return get_option($key, $default);
    }

    public static function set($key, $value) {
        return update_option($key, $value);
    }

    public static function delete($key) {
        return delete_option($key);
    }

    public static function get_all() {
        $options = [];
        foreach (self::$options as $key => $default) {
            $options[$key] = self::get($key, $default);
        }
        return $options;
    }

    public static function reset() {
        foreach (self::$options as $key => $value) {
            self::delete($key);
        }
        self::set_defaults();
    }
} 