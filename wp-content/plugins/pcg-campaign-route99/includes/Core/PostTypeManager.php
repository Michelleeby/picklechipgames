<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignCore\Core\PostTypes\PostTypeManager as CorePostTypeManager;

class PostTypeManager extends CorePostTypeManager {
    protected $post_types;

    public function __construct() {
        parent::__construct('route99');
        
        $this->post_types = [
            'location' => [
                'public' => true,
                'show_in_rest' => true,
                'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'menu_icon' => 'dashicons-location',
                'labels' => [
                    'name' => __('Locations', 'route99'),
                    'singular_name' => __('Location', 'route99'),
                ],
            ],
            'mystery' => [
                'public' => true,
                'show_in_rest' => true,
                'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
                'menu_icon' => 'dashicons-search',
                'labels' => [
                    'name' => __('Mysteries', 'route99'),
                    'singular_name' => __('Mystery', 'route99'),
                ],
            ],
        ];
    }
} 