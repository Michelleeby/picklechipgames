<?php

namespace PCG\AccessControl\Core;

class Database {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Campaign Users table
        $campaign_users_table = $wpdb->prefix . 'pcg_campaign_users';
        $sql_campaign_users = "CREATE TABLE IF NOT EXISTS $campaign_users_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            campaign_slug varchar(100) NOT NULL,
            role varchar(50) NOT NULL,
            joined_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY campaign_slug (campaign_slug),
            UNIQUE KEY user_campaign_role (user_id, campaign_slug, role)
        ) $charset_collate;";

        // Create invitations table
        $invitations_table = $wpdb->prefix . 'pcg_invitations';
        $sql_invitations = "CREATE TABLE IF NOT EXISTS $invitations_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            code varchar(12) NOT NULL,
            campaign_slug varchar(100) NOT NULL,
            created_by bigint(20) NOT NULL,
            created_date datetime NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            expires_at datetime DEFAULT NULL,
            used_by bigint(20) DEFAULT NULL,
            used_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            KEY campaign_slug (campaign_slug)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Execute each SQL statement separately
        dbDelta($sql_campaign_users);
        dbDelta($sql_invitations);
    }

    public static function drop_tables() {
        global $wpdb;
        
        // Drop campaign users table
        $campaign_users_table = $wpdb->prefix . 'pcg_campaign_users';
        $wpdb->query("DROP TABLE IF EXISTS $campaign_users_table");
        
        // Drop invitations table
        $invitations_table = $wpdb->prefix . 'pcg_invitations';
        $wpdb->query("DROP TABLE IF EXISTS $invitations_table");
    }
} 