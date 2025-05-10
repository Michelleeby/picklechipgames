<?php
namespace PCG\CampaignCore\Core\Taxonomies;

use PCG\CampaignCore\Core\Interfaces\TaxonomyManagerInterface;

abstract class TaxonomyManager implements TaxonomyManagerInterface {
    protected $taxonomies = [];
    protected $plugin_prefix;

    public function __construct(string $plugin_prefix) {
        $this->plugin_prefix = 'cc'; // Set default short prefix
        add_action('init', [$this, 'register_taxonomies']);
    }

    public function set_plugin_prefix(string $prefix): void {
        $this->plugin_prefix = $prefix;
    }

    public function register_taxonomies(): void {
        foreach ($this->taxonomies as $taxonomy => $args) {
            $this->register_taxonomy($taxonomy, $args);
        }
    }

    protected function register_taxonomy(string $taxonomy, array $args): void {
        $taxonomy = $this->plugin_prefix . '_' . $taxonomy;
        
        // Set default arguments
        $defaults = [
            'hierarchical' => true,
            'show_in_rest' => true,
            'labels' => [
                'name' => ucfirst($taxonomy),
                'singular_name' => ucfirst($taxonomy),
            ],
        ];

        $args = wp_parse_args($args, $defaults);
        
        // Get post types from args or use default
        $post_types = $args['post_types'] ?? ['post'];
        unset($args['post_types']);

        register_taxonomy($taxonomy, $post_types, $args);
    }

    public function get_taxonomies(): array {
        return array_map(function($taxonomy) {
            return $this->plugin_prefix . '_' . $taxonomy;
        }, array_keys($this->taxonomies));
    }

    public function get_taxonomy_args(string $taxonomy): ?array {
        $taxonomy = str_replace($this->plugin_prefix . '_', '', $taxonomy);
        return $this->taxonomies[$taxonomy] ?? null;
    }
} 