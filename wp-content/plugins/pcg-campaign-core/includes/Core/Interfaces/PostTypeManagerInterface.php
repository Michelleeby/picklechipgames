<?php
namespace PCG\CampaignCore\Core\Interfaces;

interface PostTypeManagerInterface {
    public function register_post_types(): void;
    public function get_post_types(): array;
    public function get_post_type_args(string $post_type): ?array;
} 