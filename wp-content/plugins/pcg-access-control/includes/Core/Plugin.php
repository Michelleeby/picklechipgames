<?php

namespace PCG\AccessControl\Core;

class Plugin {
    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Add custom capabilities to post types
        add_filter('user_has_cap', [$this, 'check_campaign_capabilities'], 10, 4);
        
        // Add campaign user management
        add_action('admin_init', [$this, 'register_campaign_user_management']);
        
        // Add campaign user columns
        add_filter('manage_users_columns', [$this, 'add_campaign_user_columns']);
        add_filter('manage_users_custom_column', [$this, 'populate_campaign_user_columns'], 10, 3);
    }

    public function check_campaign_capabilities($allcaps, $caps, $args, $user) {
        if (empty($args[0])) {
            return $allcaps;
        }

        $capability = $args[0];
        $user_id = $user->ID;

        // Check if user is a super admin
        if (in_array(Roles::ROLE_SUPER_ADMIN, $user->roles)) {
            $allcaps[$capability] = true;
            return $allcaps;
        }

        // Check campaign-specific capabilities
        switch ($capability) {
            case 'edit_campaign_content':
                // Game Managers can edit campaign content
                $campaigns = Roles::get_user_campaigns($user_id);
                foreach ($campaigns as $campaign) {
                    if ($campaign->role === Roles::ROLE_GAME_MANAGER) {
                        $allcaps[$capability] = true;
                        return $allcaps;
                    }
                }
                break;

            case 'edit_own_content':
                // Adventurers can edit their own content
                if (in_array(Roles::ROLE_ADVENTURER, $user->roles)) {
                    $allcaps[$capability] = true;
                    return $allcaps;
                }
                break;

            case 'create_character':
                // Adventurers can create one character per campaign
                if (in_array(Roles::ROLE_ADVENTURER, $user->roles)) {
                    $allcaps[$capability] = true;
                    return $allcaps;
                }
                break;
        }

        return $allcaps;
    }

    public function register_campaign_user_management() {
        // Add campaign user management UI
        add_action('admin_menu', function() {
            add_users_page(
                __('Campaign Users', 'pcg-access-control'),
                __('Campaign Users', 'pcg-access-control'),
                'manage_options',
                'campaign-users',
                [$this, 'render_campaign_users_page']
            );
        });
    }

    public function render_campaign_users_page() {
        // TODO: Implement campaign users management UI
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Campaign Users', 'pcg-access-control') . '</h1>';
        echo '<p>' . esc_html__('Campaign user management interface coming soon.', 'pcg-access-control') . '</p>';
        echo '</div>';
    }

    public function add_campaign_user_columns($columns) {
        $columns['campaigns'] = __('Campaigns', 'pcg-access-control');
        return $columns;
    }

    public function populate_campaign_user_columns($value, $column_name, $user_id) {
        if ($column_name === 'campaigns') {
            $campaigns = Roles::get_user_campaigns($user_id);
            if (!empty($campaigns)) {
                $campaign_roles = [];
                foreach ($campaigns as $campaign) {
                    $campaign_roles[] = sprintf(
                        '%s (%s)',
                        get_the_title($campaign->campaign_slug),
                        $campaign->role
                    );
                }
                return implode(', ', $campaign_roles);
            }
            return '—';
        }
        return $value;
    }
} 