<?php
namespace PCG\CampaignCore\Core\Blocks;

use PCG\CampaignCore\Core\Interfaces\BlockManagerInterface;

abstract class BlockManager implements BlockManagerInterface {
    protected $blocks = [];
    protected $plugin_prefix;
    protected $plugin_url;
    protected $block_namespace;

    public function __construct(string $plugin_prefix, string $plugin_url) {
        $this->plugin_prefix = $plugin_prefix;
        $this->plugin_url = $plugin_url;
        // Convert plugin prefix to proper namespace format (e.g., 'campaign-core' -> 'campaign-core')
        $this->block_namespace = str_replace('_', '-', $plugin_prefix);
        add_action('init', [$this, 'register_blocks']);
    }

    public function register_blocks(): void {
        foreach ($this->blocks as $block_name => $args) {
            $this->register_block($block_name, $args);
        }
    }

    protected function register_block(string $block_name, array $args): void {
        // Ensure block name has proper namespace prefix
        $block_name = $this->block_namespace . '/' . $block_name;
        
        // Set default arguments
        $defaults = [
            'editor_script' => $this->plugin_prefix . '-' . str_replace('/', '-', $block_name) . '-editor',
            'editor_style' => $this->plugin_prefix . '-' . str_replace('/', '-', $block_name) . '-editor-style',
            'style' => $this->plugin_prefix . '-' . str_replace('/', '-', $block_name) . '-style',
            'render_callback' => [$this, 'render_' . str_replace('-', '_', $block_name) . '_block'],
        ];

        $args = wp_parse_args($args, $defaults);
        register_block_type($block_name, $args);
    }

    public function get_blocks(): array {
        return array_map(function($block_name) {
            return $this->block_namespace . '/' . $block_name;
        }, array_keys($this->blocks));
    }

    public function get_block_args(string $block_name): ?array {
        $block_name = str_replace($this->block_namespace . '/', '', $block_name);
        return $this->blocks[$block_name] ?? null;
    }

    public function enqueue_block_assets(): void {
        foreach ($this->blocks as $block_name => $args) {
            $this->enqueue_block_assets_for($block_name, $args);
        }
    }

    protected function enqueue_block_assets_for(string $block_name, array $args): void {
        $block_name = $this->plugin_prefix . '-' . $block_name;
        
        // Enqueue editor script
        if (!empty($args['editor_script'])) {
            wp_enqueue_script(
                $args['editor_script'],
                $this->plugin_url . 'assets/js/blocks/' . $block_name . '/editor.js',
                ['wp-blocks', 'wp-element', 'wp-editor'],
                filemtime($this->plugin_url . 'assets/js/blocks/' . $block_name . '/editor.js')
            );
        }

        // Enqueue editor style
        if (!empty($args['editor_style'])) {
            wp_enqueue_style(
                $args['editor_style'],
                $this->plugin_url . 'assets/css/blocks/' . $block_name . '/editor.css',
                [],
                filemtime($this->plugin_url . 'assets/css/blocks/' . $block_name . '/editor.css')
            );
        }

        // Enqueue frontend style
        if (!empty($args['style'])) {
            wp_enqueue_style(
                $args['style'],
                $this->plugin_url . 'assets/css/blocks/' . $block_name . '/style.css',
                [],
                filemtime($this->plugin_url . 'assets/css/blocks/' . $block_name . '/style.css')
            );
        }
    }
} 