<?php
/**
 * Plugin Name:       PCG Interactive Blocks
 * Description:       A collection of interactive blocks for PCG.
 * Version:           0.1.0
 * Requires PHP:      8.0
 * Requires at least: 6.7
 * Author:            Michelleeby
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pcg-interactive-blocks
 *
 * @category WordPress
 * @package  PCG_Interactive_Blocks
 * @author   Michelleeby <38360136+Michelleeby@users.noreply.github.com>
 * @license  GPL-2.0-or-later https://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://github.com/Michelleeby/picklechipgames/tree/trunk/plugins/pcg-interactive-blocks
 * @since    0.1.0
 * 
 */
if (! defined('ABSPATH') ) {
    exit; // Exit if accessed directly.
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_pcg_interactive_blocks_block_init()
{
    register_block_type_from_metadata(__DIR__ . '/build/pcg-interactive-blocks-dice-roller');
    register_block_type_from_metadata(__DIR__ . '/build/pcg-interactive-blocks-single-die');
}
add_action('init', 'create_block_pcg_interactive_blocks_block_init');

/**
 * Upload the dice assets to the WP_UPLOADS folder.
 */
function pcg_interactive_blocks_upload_dice_assets()
{
    $dice_assets = [
    'd4.png',
    'd6.png',
    'd8.png',
    'd10.png',
    'd12.png',
    'd20.png'
    ];

    foreach ( $dice_assets as $dice_asset ) {
        $attachment = file_get_contents(__DIR__ . '/assets/dice/' . $dice_asset);
        $upload_file = wp_upload_bits($dice_asset, null, $attachment);
        if ($upload_file['error'] ) {
            error_log('Error uploading dice asset: ' . $upload_file['error']);
            continue;
        }
        $attachment_id = wp_insert_attachment(
            [
            'post_mime_type' => 'image/png',
            'post_title' => $dice_asset,
            'post_content' => '',
            'post_status' => 'inherit'
            ], $upload_file['file'] 
        );
        if (! is_wp_error($attachment_id) ) {
            $attach_data = wp_generate_attachment_metadata($attachment_id, $upload_file['file']);
            wp_update_attachment_metadata($attachment_id, $attach_data);
            
            // Track the uploaded asset
            $uploaded_assets[$dice_asset] = $attachment_id;
        }
    }

    // Save record of uploaded assets
    update_option('pcg_interactive_blocks_uploaded_assets', $uploaded_assets);
}
register_activation_hook(__FILE__, 'pcg_interactive_blocks_upload_dice_assets');

/**
 * Clean up uploaded images when plugin is deactivated
 */
function pcg_interactive_blocks_deactivate()
{
    // Get record of uploaded assets
    $uploaded_assets = get_option('pcg_interactive_blocks_uploaded_assets', array());
    
    // Delete each attachment
    foreach ($uploaded_assets as $filename => $attachment_id) {
        wp_delete_attachment($attachment_id, true);
    }
}
register_deactivation_hook(__FILE__, 'pcg_interactive_blocks_deactivate');