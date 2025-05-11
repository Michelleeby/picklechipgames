<?php
namespace PCG\CampaignCore\Core\Blocks;

class CampaignCoreBlocks {
    private $plugin_prefix;
    private $plugin_url;
    private $registered_blocks = [];

    public function __construct(string $plugin_prefix, string $plugin_url) {
        $this->plugin_prefix = $plugin_prefix;
        $this->plugin_url = $plugin_url;
    }

    public function register_blocks(): void {
        // Only register blocks if they haven't been registered yet
        if (empty($this->registered_blocks)) {
            $this->register_campaign_card();
            $this->register_character_card();
            $this->registered_blocks = true;
        }
    }

    private function register_campaign_card(): void {
        register_block_type(
            'campaign-core/campaign-card',
            [
                'editor_script' => 'campaign-core-blocks',
                'editor_style' => 'campaign-core-blocks-editor',
                'style' => 'campaign-core-blocks',
                'render_callback' => [$this, 'render_campaign_card']
            ]
        );
    }

    private function register_character_card(): void {
        register_block_type(
            'campaign-core/character-card',
            [
                'editor_script' => 'campaign-core-blocks',
                'editor_style' => 'campaign-core-blocks-editor',
                'style' => 'campaign-core-blocks',
                'render_callback' => [$this, 'render_character_card']
            ]
        );
    }

    public function render_campaign_card($attributes, $content) {
        if (empty($attributes['campaignId'])) {
            return '';
        }

        $campaign = get_post($attributes['campaignId']);
        if (!$campaign || $campaign->post_type !== 'cc_campaign_entry') {
            return '';
        }

        ob_start();
        ?>
        <div class="cc-campaign-card">
            <h3><?php echo esc_html($campaign->post_title); ?></h3>
            <?php if ($attributes['showDescription']): ?>
                <div class="cc-campaign-description">
                    <?php echo wp_kses_post($campaign->post_content); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_character_card($attributes, $content) {
        if (empty($attributes['characterId'])) {
            return '';
        }

        $character = get_post($attributes['characterId']);
        if (!$character || $character->post_type !== 'cc_character') {
            return '';
        }

        ob_start();
        ?>
        <div class="cc-character-card">
            <h3><?php echo esc_html($character->post_title); ?></h3>
            <?php if ($attributes['showDescription']): ?>
                <div class="cc-character-description">
                    <?php echo wp_kses_post($character->post_content); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
} 