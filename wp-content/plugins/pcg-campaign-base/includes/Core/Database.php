<?php
namespace PCG\CampaignBase\Core;

class Database {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Character stats table
        $table_name = $wpdb->prefix . 'campaign_character_stats';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            character_id bigint(20) UNSIGNED NOT NULL,
            stats_json JSON NOT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_cached_at timestamp NULL DEFAULT NULL,
            cache_version varchar(32) DEFAULT NULL,
            PRIMARY KEY (character_id),
            INDEX idx_updated_at (updated_at),
            INDEX idx_last_cached (last_cached_at),
            INDEX idx_cache_version (cache_version)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        // Character relationships table
        $table_name = $wpdb->prefix . 'campaign_character_relationships';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            character_id bigint(20) UNSIGNED NOT NULL,
            related_character_id bigint(20) UNSIGNED NOT NULL,
            relationship_type varchar(50) NOT NULL,
            relationship_strength tinyint UNSIGNED NOT NULL DEFAULT 0,
            notes varchar(255) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_cached_at timestamp NULL DEFAULT NULL,
            cache_version varchar(32) DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uk_character_relationship (character_id, related_character_id),
            KEY idx_related_character (related_character_id),
            KEY idx_relationship_type (relationship_type),
            KEY idx_updated_at (updated_at),
            KEY idx_last_cached (last_cached_at),
            KEY idx_cache_version (cache_version)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        // Session metadata table
        $table_name = $wpdb->prefix . 'campaign_session_metadata';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            session_id bigint(20) UNSIGNED NOT NULL,
            session_date timestamp NOT NULL,
            session_number smallint UNSIGNED NOT NULL,
            location_id bigint(20) UNSIGNED DEFAULT NULL,
            weather_conditions varchar(50) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_cached_at timestamp NULL DEFAULT NULL,
            cache_version varchar(32) DEFAULT NULL,
            PRIMARY KEY (session_id),
            KEY idx_session_date (session_date),
            KEY idx_location (location_id),
            KEY idx_session_number (session_number),
            KEY idx_last_cached (last_cached_at),
            KEY idx_cache_version (cache_version)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        // Session characters table
        $table_name = $wpdb->prefix . 'campaign_session_characters';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            session_id bigint(20) UNSIGNED NOT NULL,
            character_id bigint(20) UNSIGNED NOT NULL,
            attendance_status enum('present', 'absent', 'late', 'excused') NOT NULL DEFAULT 'present',
            notes varchar(255) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_cached_at timestamp NULL DEFAULT NULL,
            cache_version varchar(32) DEFAULT NULL,
            PRIMARY KEY (session_id, character_id),
            KEY idx_character (character_id),
            KEY idx_attendance (attendance_status),
            KEY idx_last_cached (last_cached_at),
            KEY idx_cache_version (cache_version)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        // Cache statistics table
        $table_name = $wpdb->prefix . 'campaign_cache_stats';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            cache_key varchar(255) NOT NULL,
            cache_group varchar(50) NOT NULL,
            hits int(10) UNSIGNED NOT NULL DEFAULT 0,
            last_hit timestamp NULL DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_cache_key (cache_key, cache_group),
            KEY idx_cache_group (cache_group),
            KEY idx_hits (hits),
            KEY idx_last_hit (last_hit)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function drop_tables() {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'campaign_character_stats',
            $wpdb->prefix . 'campaign_character_relationships',
            $wpdb->prefix . 'campaign_session_metadata',
            $wpdb->prefix . 'campaign_session_characters',
            $wpdb->prefix . 'campaign_cache_stats'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
} 