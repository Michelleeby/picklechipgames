<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignCore\Core\Blocks\BlockManager as CoreBlockManager;

class BlockManager extends CoreBlockManager {
    protected $blocks = [
        'location' => [
            'editor_script' => 'route99-location-editor',
            'editor_style' => 'route99-location-editor-style',
            'style' => 'route99-location-style',
        ],
        'mystery' => [
            'editor_script' => 'route99-mystery-editor',
            'editor_style' => 'route99-mystery-editor-style',
            'style' => 'route99-mystery-style',
        ],
    ];

    public function __construct() {
        parent::__construct('route99', PCG_CAMPAIGN_ROUTE99_PLUGIN_URL);
    }

    public function render_location_block($attributes) {
        $location_id = $attributes['locationId'] ?? 0;
        if (!$location_id) {
            return '';
        }

        $location_data = get_post($location_id);
        if (!$location_data) {
            return '';
        }

        ob_start();
        ?>
        <div class="route99-location">
            <h2><?php echo esc_html($location_data->post_title); ?></h2>
            <div class="location-content">
                <?php echo wp_kses_post($location_data->post_content); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_mystery_block($attributes) {
        $mystery_id = $attributes['mysteryId'] ?? 0;
        if (!$mystery_id) {
            return '';
        }

        $mystery_data = get_post($mystery_id);
        if (!$mystery_data) {
            return '';
        }

        ob_start();
        ?>
        <div class="route99-mystery">
            <h2><?php echo esc_html($mystery_data->post_title); ?></h2>
            <div class="mystery-content">
                <?php echo wp_kses_post($mystery_data->post_content); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
} 