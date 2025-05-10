<?php
/**
 * Campaign Core Admin Page Template
 *
 * @package CampaignCore
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Campaign Core', 'campaign-core'); ?></h1>
    
    <div class="campaign-core-admin-content">
        <div class="campaign-core-stats">
            <h2><?php _e('Plugin Statistics', 'campaign-core'); ?></h2>
            <div class="campaign-core-stats-grid">
                <div class="campaign-core-stat-box">
                    <h3><?php _e('Cache Status', 'campaign-core'); ?></h3>
                    <?php
                    $cache_status = $cache->getStatus();
                    ?>
                    <ul>
                        <li><?php _e('Persistent Cache:', 'campaign-core'); ?> 
                            <?php echo $cache_status['has_persistent_cache'] ? __('Enabled', 'campaign-core') : __('Disabled', 'campaign-core'); ?>
                        </li>
                        <li><?php _e('Cache Group:', 'campaign-core'); ?> 
                            <?php echo esc_html($cache_status['cache_group']); ?>
                        </li>
                        <li><?php _e('Default Expiration:', 'campaign-core'); ?> 
                            <?php echo esc_html($cache_status['default_expiration']); ?>s
                        </li>
                    </ul>
                </div>
                
                <div class="campaign-core-stat-box">
                    <h3><?php _e('Performance Metrics', 'campaign-core'); ?></h3>
                    <?php
                    $performance = $cache_status['performance_metrics'];
                    if (!empty($performance)) {
                        foreach ($performance as $operation => $metrics) {
                            echo '<h4>' . esc_html(ucfirst($operation)) . '</h4>';
                            echo '<ul>';
                            echo '<li>' . __('Average:', 'campaign-core') . ' ' . number_format($metrics['avg'] * 1000, 2) . 'ms</li>';
                            echo '<li>' . __('Min:', 'campaign-core') . ' ' . number_format($metrics['min'] * 1000, 2) . 'ms</li>';
                            echo '<li>' . __('Max:', 'campaign-core') . ' ' . number_format($metrics['max'] * 1000, 2) . 'ms</li>';
                            echo '<li>' . __('Count:', 'campaign-core') . ' ' . esc_html($metrics['count']) . '</li>';
                            echo '</ul>';
                        }
                    } else {
                        echo '<p>' . __('No performance data available yet.', 'campaign-core') . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div> 