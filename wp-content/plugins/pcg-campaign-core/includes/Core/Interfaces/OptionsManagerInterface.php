<?php
namespace PCG\CampaignCore\Core\Interfaces;

interface OptionsManagerInterface {
    public function set_defaults(): void;
    public function get(string $key, $default = null);
    public function set(string $key, $value): bool;
    public function delete(string $key): bool;
    public function get_all(): array;
    public function reset(): void;
} 