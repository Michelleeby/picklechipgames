<?php
namespace PCG\CampaignCore\Core\Database;

use PCG\CampaignCore\Core\Interfaces\DatabaseManagerInterface;

class DatabaseManager implements DatabaseManagerInterface {
    private $wpdb;
    private $tables = [];

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    public function create_tables(): void {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($this->tables as $table_name => $table_sql) {
            dbDelta($table_sql);
        }
    }

    public function register_table(string $table_name, string $sql): void {
        $this->tables[$table_name] = $sql;
    }

    public function get_table_name(string $table_name): string {
        return $this->wpdb->prefix . $table_name;
    }

    public function query(string $sql, array $args = []): array {
        if (!empty($args)) {
            $sql = $this->wpdb->prepare($sql, $args);
        }
        
        return $this->wpdb->get_results($sql, ARRAY_A);
    }

    public function insert(string $table, array $data): int {
        $this->wpdb->insert($this->get_table_name($table), $data);
        return $this->wpdb->insert_id;
    }

    public function update(string $table, array $data, array $where): int {
        return $this->wpdb->update(
            $this->get_table_name($table),
            $data,
            $where
        );
    }

    public function delete(string $table, array $where): int {
        return $this->wpdb->delete(
            $this->get_table_name($table),
            $where
        );
    }

    public function get_var(string $sql, array $args = []): string {
        if (!empty($args)) {
            $sql = $this->wpdb->prepare($sql, $args);
        }
        
        return $this->wpdb->get_var($sql);
    }

    public function get_row(string $sql, array $args = []): ?array {
        if (!empty($args)) {
            $sql = $this->wpdb->prepare($sql, $args);
        }
        
        return $this->wpdb->get_row($sql, ARRAY_A);
    }

    public function get_col(string $sql, array $args = []): array {
        if (!empty($args)) {
            $sql = $this->wpdb->prepare($sql, $args);
        }
        
        return $this->wpdb->get_col($sql);
    }
} 