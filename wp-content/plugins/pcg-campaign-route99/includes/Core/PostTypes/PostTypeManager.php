<?php
namespace PCG\CampaignRoute99\Core\PostTypes;

use PCG\CampaignCore\Core\PostTypes\PostTypeManager as CorePostTypeManager;

class PostTypeManager extends CorePostTypeManager {
    protected $post_types = [
        'location' => [
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-location',
        ],
        'mystery' => [
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
            'menu_icon' => 'dashicons-search',
        ],
    ];

    public function __construct() {
        parent::__construct('route99');
        
        // Set labels after parent constructor to ensure text domain is loaded
        $this->post_types['location']['labels'] = [
            'name' => __('Locations', 'route99'),
            'singular_name' => __('Location', 'route99'),
        ];
        
        $this->post_types['mystery']['labels'] = [
            'name' => __('Mysteries', 'route99'),
            'singular_name' => __('Mystery', 'route99'),
        ];
    }
} 