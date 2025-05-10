<?php
namespace PCG\CampaignCore\Core\Interfaces;

interface DatabaseManagerInterface {
    public function create_tables(): void;
    public function register_table(string $table_name, string $sql): void;
    public function get_table_name(string $table_name): string;
    public function query(string $sql, array $args = []): array;
    public function insert(string $table, array $data): int;
    public function update(string $table, array $data, array $where): int;
    public function delete(string $table, array $where): int;
    public function get_var(string $sql, array $args = []): string;
    public function get_row(string $sql, array $args = []): ?array;
    public function get_col(string $sql, array $args = []): array;
} 