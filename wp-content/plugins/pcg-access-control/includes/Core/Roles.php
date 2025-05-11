<?php

namespace PCG\AccessControl\Core;

class Roles {
    const ROLE_SUPER_ADMIN = 'pcg_super_admin';
    const ROLE_CAMPAIGN_ADMIN = 'pcg_campaign_admin';
    const ROLE_GAME_MANAGER = 'pcg_game_mentor';
    const ROLE_ADVENTURER = 'pcg_adventurer';

    public static function setup_roles() {
        // Remove roles if they exist to ensure clean setup
        self::cleanup_roles();

        // Add Super Admin role
        add_role(
            self::ROLE_SUPER_ADMIN,
            __('Super Admin', 'pcg-access-control'),
            [
                'manage_options' => true,
                'manage_campaigns' => true,
                'manage_campaign_admins' => true,
                'read' => true,
            ]
        );

        // Add Campaign Admin role
        add_role(
            self::ROLE_CAMPAIGN_ADMIN,
            __('Campaign Admin', 'pcg-access-control'),
            [
                'manage_campaigns' => true,
                'create_campaign_users' => true,
                'read' => true,
            ]
        );

        // Add Game Manager role
        add_role(
            self::ROLE_GAME_MANAGER,
            __('Game Manager', 'pcg-access-control'),
            [
                'edit_campaign_content' => true,
                'view_campaign_content' => true,
                'create_campaign_content' => true,
                'read' => true,
            ]
        );

        // Add Adventurer role
        add_role(
            self::ROLE_ADVENTURER,
            __('Adventurer', 'pcg-access-control'),
            [
                'create_character' => true,
                'edit_own_content' => true,
                'view_campaign_content' => true,
                'read' => true,
            ]
        );

        // Add custom capabilities to administrator role
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('manage_campaigns');
            $admin->add_cap('manage_campaign_admins');
            $admin->add_cap('create_campaign_users');
        }
    }

    public static function cleanup_roles() {
        remove_role(self::ROLE_SUPER_ADMIN);
        remove_role(self::ROLE_CAMPAIGN_ADMIN);
        remove_role(self::ROLE_GAME_MANAGER);
        remove_role(self::ROLE_ADVENTURER);

        // Remove custom capabilities from administrator role
        $admin = get_role('administrator');
        if ($admin) {
            $admin->remove_cap('manage_campaigns');
            $admin->remove_cap('manage_campaign_admins');
            $admin->remove_cap('create_campaign_users');
        }
    }

    public static function assign_user_to_campaign($user_id, $campaign_slug, $role) {
        if (!in_array($role, [self::ROLE_GAME_MANAGER, self::ROLE_ADVENTURER])) {
            return false;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_campaign_users';

        return $wpdb->insert(
            $table_name,
            [
                'user_id' => $user_id,
                'campaign_slug' => $campaign_slug,
                'role' => $role,
                'created_at' => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%s']
        );
    }

    public static function remove_user_from_campaign($user_id, $campaign_slug) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_campaign_users';

        return $wpdb->delete(
            $table_name,
            [
                'user_id' => $user_id,
                'campaign_slug' => $campaign_slug,
            ],
            ['%d', '%s']
        );
    }

    public static function get_user_campaigns($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_campaign_users';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT campaign_slug, role FROM $table_name WHERE user_id = %d",
                $user_id
            )
        );
    }

    public static function get_campaign_users($campaign_slug) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_campaign_users';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, role FROM $table_name WHERE campaign_slug = %s",
                $campaign_slug
            )
        );
    }
} 