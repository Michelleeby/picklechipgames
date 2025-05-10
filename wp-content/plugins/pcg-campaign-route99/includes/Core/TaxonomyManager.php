<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignCore\Core\Taxonomies\TaxonomyManager as CoreTaxonomyManager;

class TaxonomyManager extends CoreTaxonomyManager {
    protected $taxonomies;

    public function __construct() {
        parent::__construct('route99');
        
        $this->taxonomies = [
            'location_type' => [
                'hierarchical' => true,
                'show_in_rest' => true,
                'post_types' => ['route99_location'],
                'labels' => [
                    'name' => __('Location Types', 'route99'),
                    'singular_name' => __('Location Type', 'route99'),
                ],
            ],
            'mystery_type' => [
                'hierarchical' => true,
                'show_in_rest' => true,
                'post_types' => ['route99_mystery'],
                'labels' => [
                    'name' => __('Mystery Types', 'route99'),
                    'singular_name' => __('Mystery Type', 'route99'),
                ],
            ],
        ];
    }
} 