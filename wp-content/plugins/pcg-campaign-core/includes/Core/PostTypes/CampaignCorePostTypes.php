<?php
namespace PCG\CampaignCore\Core\PostTypes;

class CampaignCorePostTypes extends PostTypeManager {
    protected $post_types = [
        'campaign_entry' => [
            'labels' => [
                'name' => 'Campaign Entries',
                'singular_name' => 'Campaign Entry',
                'add_new' => 'Add New Campaign Entry',
                'add_new_item' => 'Add New Campaign Entry',
                'edit_item' => 'Edit Campaign Entry',
                'new_item' => 'New Campaign Entry',
                'view_item' => 'View Campaign Entry',
                'search_items' => 'Search Campaign Entries',
                'not_found' => 'No campaign entries found',
                'not_found_in_trash' => 'No campaign entries found in Trash',
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
        add_action('add_meta_boxes', [$this, 'add_campaign_meta_box']);
        add_action('save_post', [$this, 'save_campaign_meta'], 10, 2);
    }

    public function add_campaign_meta_box() {
        add_meta_box(
            'campaign_entry_campaign',
            __('Associated Campaign', 'campaign-core'),
            [$this, 'render_campaign_meta_box'],
            'cc_campaign_entry',
            'side',
            'default'
        );
    }

    public function render_campaign_meta_box($post) {
        $value = get_post_meta($post->ID, '_associated_campaign', true);
        // For now, just a text field for campaign slug or ID. Later, this could be a dropdown.
        echo '<label for="associated_campaign">' . __('Campaign Slug/ID', 'campaign-core') . '</label>';
        echo '<input type="text" id="associated_campaign" name="associated_campaign" value="' . esc_attr($value) . '" />';
    }

    public function save_campaign_meta($post_id, $post) {
        if ($post->post_type !== 'cc_campaign_entry') return;
        if (isset($_POST['associated_campaign'])) {
            update_post_meta($post_id, '_associated_campaign', sanitize_text_field($_POST['associated_campaign']));
        }
    }
} 