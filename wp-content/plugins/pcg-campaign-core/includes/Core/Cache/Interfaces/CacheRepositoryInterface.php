<?php
namespace PCG\CampaignCore\Core\Cache\Interfaces;

interface CacheRepositoryInterface {
    public function getAllItems(): array;
    public function delete(string $key, string $group): bool;
    public function get(string $key, string $group);
    public function set(string $key, $value, string $group, int $expiration = 0): bool;
} 