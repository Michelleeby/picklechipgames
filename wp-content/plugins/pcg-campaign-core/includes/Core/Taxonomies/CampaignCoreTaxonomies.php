<?php
namespace PCG\CampaignCore\Core\Taxonomies;

class CampaignCoreTaxonomies extends TaxonomyManager {
    protected $taxonomies = [
        'campaign_type' => [
            'post_types' => ['cc_campaign_entry'],
            'labels' => [
                'name' => 'Campaign Types',
                'singular_name' => 'Campaign Type',
                'search_items' => 'Search Campaign Types',
                'all_items' => 'All Campaign Types',
                'parent_item' => 'Parent Campaign Type',
                'parent_item_colon' => 'Parent Campaign Type:',
                'edit_item' => 'Edit Campaign Type',
                'update_item' => 'Update Campaign Type',
                'add_new_item' => 'Add New Campaign Type',
                'new_item_name' => 'New Campaign Type Name',
                'menu_name' => 'Campaign Types',
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'campaign-type'],
        ],
        'character_type' => [
            'post_types' => ['cc_character'],
            'labels' => [
                'name' => 'Character Types',
                'singular_name' => 'Character Type',
                'search_items' => 'Search Character Types',
                'all_items' => 'All Character Types',
                'parent_item' => 'Parent Character Type',
                'parent_item_colon' => 'Parent Character Type:',
                'edit_item' => 'Edit Character Type',
                'update_item' => 'Update Character Type',
                'add_new_item' => 'Add New Character Type',
                'new_item_name' => 'New Character Type Name',
                'menu_name' => 'Character Types',
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => ['slug' => 'character-type'],
        ],
    ];

    public function __construct(string $plugin_prefix) {
        parent::__construct($plugin_prefix);
    }
} 