<?php
namespace PCG\CampaignCore\Core\Interfaces;

interface TaxonomyManagerInterface {
    public function register_taxonomies(): void;
    public function get_taxonomies(): array;
    public function get_taxonomy_args(string $taxonomy): ?array;
} 