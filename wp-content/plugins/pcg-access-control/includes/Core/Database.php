<?php

namespace PCG\AccessControl\Core;

class Database {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Campaign Users table
        $table_name = $wpdb->prefix . 'pcg_campaign_users';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            campaign_id bigint(20) NOT NULL,
            role varchar(50) NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY campaign_id (campaign_id),
            UNIQUE KEY user_campaign_role (user_id, campaign_id, role)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function drop_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_campaign_users';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
} 