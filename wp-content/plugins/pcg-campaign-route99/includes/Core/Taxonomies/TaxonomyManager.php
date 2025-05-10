<?php
namespace PCG\CampaignRoute99\Core\Taxonomies;

use PCG\CampaignCore\Core\Taxonomies\TaxonomyManager as CoreTaxonomyManager;

class TaxonomyManager extends CoreTaxonomyManager {
    protected $taxonomies = [
        'location_type' => [
            'hierarchical' => true,
            'show_in_rest' => true,
            'post_types' => ['route99_location'],
        ],
        'mystery_type' => [
            'hierarchical' => true,
            'show_in_rest' => true,
            'post_types' => ['route99_mystery'],
        ],
    ];

    public function __construct() {
        parent::__construct('route99');
        
        // Set labels after parent constructor to ensure text domain is loaded
        $this->taxonomies['location_type']['labels'] = [
            'name' => __('Location Types', 'route99'),
            'singular_name' => __('Location Type', 'route99'),
            'search_items' => __('Search Location Types', 'route99'),
            'all_items' => __('All Location Types', 'route99'),
            'parent_item' => __('Parent Location Type', 'route99'),
            'parent_item_colon' => __('Parent Location Type:', 'route99'),
            'edit_item' => __('Edit Location Type', 'route99'),
            'update_item' => __('Update Location Type', 'route99'),
            'add_new_item' => __('Add New Location Type', 'route99'),
            'new_item_name' => __('New Location Type Name', 'route99'),
            'menu_name' => __('Location Types', 'route99'),
        ];
        
        $this->taxonomies['mystery_type']['labels'] = [
            'name' => __('Mystery Types', 'route99'),
            'singular_name' => __('Mystery Type', 'route99'),
            'search_items' => __('Search Mystery Types', 'route99'),
            'all_items' => __('All Mystery Types', 'route99'),
            'parent_item' => __('Parent Mystery Type', 'route99'),
            'parent_item_colon' => __('Parent Mystery Type:', 'route99'),
            'edit_item' => __('Edit Mystery Type', 'route99'),
            'update_item' => __('Update Mystery Type', 'route99'),
            'add_new_item' => __('Add New Mystery Type', 'route99'),
            'new_item_name' => __('New Mystery Type Name', 'route99'),
            'menu_name' => __('Mystery Types', 'route99'),
        ];
    }
} 