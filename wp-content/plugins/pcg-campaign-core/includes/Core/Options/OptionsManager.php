<?php
namespace PCG\CampaignCore\Core\Options;

use PCG\CampaignCore\Core\Interfaces\OptionsManagerInterface;

abstract class OptionsManager implements OptionsManagerInterface {
    protected static $options = [];
    protected $plugin_prefix;

    public function __construct(string $plugin_prefix) {
        $this->plugin_prefix = $plugin_prefix;
    }

    public function set_defaults(): void {
        foreach (static::$options as $key => $value) {
            $option_key = $this->get_option_key($key);
            if (get_option($option_key) === false) {
                update_option($option_key, $value);
            }
        }
    }

    public function get(string $key, $default = null) {
        $option_key = $this->get_option_key($key);
        return get_option($option_key, $default);
    }

    public function set(string $key, $value): bool {
        $option_key = $this->get_option_key($key);
        return update_option($option_key, $value);
    }

    public function delete(string $key): bool {
        $option_key = $this->get_option_key($key);
        return delete_option($option_key);
    }

    public function get_all(): array {
        $options = [];
        foreach (static::$options as $key => $default) {
            $options[$key] = $this->get($key, $default);
        }
        return $options;
    }

    public function reset(): void {
        foreach (static::$options as $key => $value) {
            $this->delete($key);
        }
        $this->set_defaults();
    }

    protected function get_option_key(string $key): string {
        return $this->plugin_prefix . '_' . $key;
    }
} 