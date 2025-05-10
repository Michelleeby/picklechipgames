<?php
namespace PCG\CampaignCore\Core\PostTypes;

class CampaignCorePostTypes extends PostTypeManager {
    protected $post_types = [
        'campaign' => [
            'labels' => [
                'name' => 'Campaigns',
                'singular_name' => 'Campaign',
                'add_new' => 'Add New Campaign',
                'add_new_item' => 'Add New Campaign',
                'edit_item' => 'Edit Campaign',
                'new_item' => 'New Campaign',
                'view_item' => 'View Campaign',
                'search_items' => 'Search Campaigns',
                'not_found' => 'No campaigns found',
                'not_found_in_trash' => 'No campaigns found in Trash',
            ],
            'menu_icon' => 'dashicons-flag',
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields', 'revisions'],
        ],
        'character' => [
            'labels' => [
                'name' => 'Characters',
                'singular_name' => 'Character',
                'add_new' => 'Add New Character',
                'add_new_item' => 'Add New Character',
                'edit_item' => 'Edit Character',
                'new_item' => 'New Character',
                'view_item' => 'View Character',
                'search_items' => 'Search Characters',
                'not_found' => 'No characters found',
                'not_found_in_trash' => 'No characters found in Trash',
            ],
            'menu_icon' => 'dashicons-groups',
            'supports' => ['title', 'editor', 'thumbnail', 'custom-fields', 'revisions'],
        ],
    ];

    public function __construct(string $plugin_prefix) {
        parent::__construct($plugin_prefix);
    }
} 