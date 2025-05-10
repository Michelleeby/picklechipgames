<?php
namespace PCG\CampaignCore\Core\Interfaces;

interface BlockManagerInterface {
    public function register_blocks(): void;
    public function get_blocks(): array;
    public function get_block_args(string $block_name): ?array;
    public function enqueue_block_assets(): void;
} 