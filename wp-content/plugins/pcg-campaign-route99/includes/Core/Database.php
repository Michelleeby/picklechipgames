<?php
namespace PCG\CampaignRoute99\Core;

use PCG\CampaignBase\Core\Database as CampaignDatabase;

class Database extends CampaignDatabase {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Call parent method to create base tables
        parent::create_tables();

        // Location metadata table
        $table_name = $wpdb->prefix . 'route99_location_metadata';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            location_id bigint(20) UNSIGNED NOT NULL,
            coordinates_json JSON NOT NULL,
            discovered_by JSON NOT NULL,
            last_visited timestamp NULL DEFAULT NULL,
            weather_conditions varchar(50) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (location_id),
            KEY idx_last_visited (last_visited),
            KEY idx_weather (weather_conditions)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        // Mystery clues table
        $table_name = $wpdb->prefix . 'route99_mystery_clues';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            mystery_id bigint(20) UNSIGNED NOT NULL,
            clue_text varchar(255) NOT NULL,
            discovered_by JSON NOT NULL,
            location_id bigint(20) UNSIGNED DEFAULT NULL,
            discovered_at timestamp NULL DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_mystery (mystery_id),
            KEY idx_location (location_id),
            KEY idx_discovered_at (discovered_at)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        // Character discoveries table
        $table_name = $wpdb->prefix . 'route99_character_discoveries';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            character_id bigint(20) UNSIGNED NOT NULL,
            discovery_type enum('location', 'mystery', 'clue', 'item') NOT NULL,
            discovery_id bigint(20) UNSIGNED NOT NULL,
            discovery_date timestamp NOT NULL,
            notes varchar(255) DEFAULT NULL,
            created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_character_discovery (character_id, discovery_type, discovery_id),
            KEY idx_discovery_type (discovery_type),
            KEY idx_discovery_id (discovery_id),
            KEY idx_discovery_date (discovery_date)
        ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function drop_tables() {
        global $wpdb;
        
        // Call parent method to drop base tables
        parent::drop_tables();

        $tables = [
            $wpdb->prefix . 'route99_location_metadata',
            $wpdb->prefix . 'route99_mystery_clues',
            $wpdb->prefix . 'route99_character_discoveries'
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }

    /**
     * Creates the Route 99 campaign if it doesn't exist
     * 
     * @return int|false The campaign ID if created successfully, false otherwise
     */
    public static function create_route99_campaign() {
        global $wpdb;
        
        // Check if campaign already exists
        $campaign_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'campaign' 
                AND post_title = %s 
                AND post_status = 'publish'",
                'Route 99'
            )
        );

        if ($campaign_id) {
            return $campaign_id;
        }

        // Create the campaign
        $campaign_data = array(
            'post_title'    => 'Route 99',
            'post_content'  => 'A mysterious journey along the historic Route 99, where strange occurrences and supernatural events await.',
            'post_status'   => 'publish',
            'post_type'     => 'campaign',
            'post_author'   => 1
        );

        $campaign_id = wp_insert_post($campaign_data);

        if ($campaign_id) {
            // Add campaign metadata
            update_post_meta($campaign_id, '_campaign_type', 'route99');
            update_post_meta($campaign_id, '_campaign_status', 'active');
            update_post_meta($campaign_id, '_campaign_start_date', current_time('mysql'));
            
            // Set campaign settings
            $settings = array(
                'allow_character_creation' => true,
                'max_characters_per_user' => 1,
                'allow_location_discovery' => true,
                'allow_mystery_tracking' => true
            );
            update_post_meta($campaign_id, '_campaign_settings', $settings);
        }

        return $campaign_id;
    }
} 