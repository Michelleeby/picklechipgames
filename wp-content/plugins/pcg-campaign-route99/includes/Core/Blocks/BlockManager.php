<?php
namespace PCG\CampaignRoute99\Core\Blocks;

use PCG\CampaignCore\Core\Blocks\BlockManager as CoreBlockManager;

class BlockManager extends CoreBlockManager {
    protected $blocks = [
        'location' => [
            'title' => 'Location',
            'description' => 'Display a Route 99 location.',
            'category' => 'route99',
            'icon' => 'location',
            'attributes' => [
                'locationId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
                'showDescription' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ],
        'mystery' => [
            'title' => 'Mystery',
            'description' => 'Display a Route 99 mystery.',
            'category' => 'route99',
            'icon' => 'search',
            'attributes' => [
                'mysteryId' => [
                    'type' => 'number',
                    'default' => 0,
                ],
                'showDescription' => [
                    'type' => 'boolean',
                    'default' => true,
                ],
            ],
        ],
    ];

    public function __construct() {
        parent::__construct('route99', PCG_CAMPAIGN_ROUTE99_PLUGIN_URL);
    }

    public function render_location_block($attributes, $content) {
        if (empty($attributes['locationId'])) {
            return '';
        }

        $location = get_post($attributes['locationId']);
        if (!$location || $location->post_type !== 'route99_location') {
            return '';
        }

        ob_start();
        ?>
        <div class="route99-location">
            <h3><?php echo esc_html($location->post_title); ?></h3>
            <?php if ($attributes['showDescription']): ?>
                <div class="route99-location-description">
                    <?php echo wp_kses_post($location->post_content); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_mystery_block($attributes, $content) {
        if (empty($attributes['mysteryId'])) {
            return '';
        }

        $mystery = get_post($attributes['mysteryId']);
        if (!$mystery || $mystery->post_type !== 'route99_mystery') {
            return '';
        }

        ob_start();
        ?>
        <div class="route99-mystery">
            <h3><?php echo esc_html($mystery->post_title); ?></h3>
            <?php if ($attributes['showDescription']): ?>
                <div class="route99-mystery-description">
                    <?php echo wp_kses_post($mystery->post_content); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
} 