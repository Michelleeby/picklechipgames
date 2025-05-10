<?php
namespace PCG\CampaignCore\Core\PostTypes;

use PCG\CampaignCore\Core\Interfaces\PostTypeManagerInterface;

abstract class PostTypeManager implements PostTypeManagerInterface {
    protected $post_types = [];
    protected $plugin_prefix;

    public function __construct(string $plugin_prefix) {
        $this->plugin_prefix = 'cc'; // Set default short prefix
        add_action('init', [$this, 'register_post_types']);
    }

    public function set_plugin_prefix(string $prefix): void {
        $this->plugin_prefix = $prefix;
    }

    public function register_post_types(): void {
        foreach ($this->post_types as $post_type => $args) {
            $this->register_post_type($post_type, $args);
        }
    }

    protected function register_post_type(string $post_type, array $args): void {
        $post_type = $this->plugin_prefix . '_' . $post_type;
        
        // Set default arguments
        $defaults = [
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-admin-post',
            'labels' => [
                'name' => ucfirst($post_type),
                'singular_name' => ucfirst($post_type),
            ],
        ];

        $args = wp_parse_args($args, $defaults);
        register_post_type($post_type, $args);
    }

    public function get_post_types(): array {
        return array_map(function($post_type) {
            return $this->plugin_prefix . '_' . $post_type;
        }, array_keys($this->post_types));
    }

    public function get_post_type_args(string $post_type): ?array {
        $post_type = str_replace($this->plugin_prefix . '_', '', $post_type);
        return $this->post_types[$post_type] ?? null;
    }
} 