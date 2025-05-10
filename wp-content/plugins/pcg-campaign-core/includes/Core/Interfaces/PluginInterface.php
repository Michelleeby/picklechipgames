<?php
namespace PCG\CampaignCore\Core\Interfaces;

interface PluginInterface {
    public function init(): void;
    public function activate(): void;
    public function deactivate(): void;
    public function get_plugin_name(): string;
    public function get_plugin_version(): string;
    public function get_plugin_path(): string;
    public function get_plugin_url(): string;
} 