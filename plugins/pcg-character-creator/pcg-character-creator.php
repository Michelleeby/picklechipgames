<?php
/**
 * Plugin Name:       Pcg Character Creator
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           0.1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pcg-character-creator
 *
 * @package CreateBlock
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Registers the block using a `blocks-manifest.php` file, which improves the performance of block type registration.
 * Behind the scenes, it also registers all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
 */
function create_block_pcg_character_creator_block_init() {
	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
	 * based on the registered block metadata.
	 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
	 *
	 * @see https://make.wordpress.org/core/2025/03/13/more-efficient-block-type-registration-in-6-8/
	 */
	if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
		wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
		return;
	}

	/**
	 * Registers the block(s) metadata from the `blocks-manifest.php` file.
	 * Added to WordPress 6.7 to improve the performance of block type registration.
	 *
	 * @see https://make.wordpress.org/core/2024/10/17/new-block-type-registration-apis-to-improve-performance-in-wordpress-6-7/
	 */
	if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
		wp_register_block_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	}
	/**
	 * Registers the block type(s) in the `blocks-manifest.php` file.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	$manifest_data = require __DIR__ . '/build/blocks-manifest.php';
	foreach ( array_keys( $manifest_data ) as $block_type ) {
		register_block_type( __DIR__ . "/build/{$block_type}" );
	}
}
add_action( 'init', 'create_block_pcg_character_creator_block_init' );

function pcg_character_creator_enqueue_assets() {
	$block = 'pcg-character-creator';
	$block_path = plugin_dir_path(__FILE__) . 'build/' . $block;
	$image_extension = '.png';

	if (!wp_script_is($block, 'enqueued')) {
		wp_enqueue_script(
			$block,
			plugins_url($block_path . '/view.js', __FILE__),
			array('wp-element'),
			filemtime($block_path . '/view.js')
		);
		wp_localize_script($block, 'pcgCharacterCreator', array(
				'getImageUrlBase' => wp_upload_dir()['baseurl'] . '/' . $block . '/' . 'images' . '/' ,
				'imageExtension' => $image_extension
			)
		);
	}
}
add_action('wp_enqueue_scripts', 'pcg_character_creator_enqueue_assets');
add_action('enqueue_block_editor_assets', 'pcg_character_creator_enqueue_assets');

/**
 * Upload dice images to WordPress media library on plugin activation
 */
function pcg_character_creator_activate() {
    // Check if images are already uploaded (prevent duplicate uploads)
    $upload_dir = wp_upload_dir();
    $pcg_upload_path = $upload_dir['basedir'] . '/pcg-character-creator/images';
    
    // If the upload directory doesn't exist, create it
    if (!file_exists($pcg_upload_path)) {
        wp_mkdir_p($pcg_upload_path);
    }
    
    // Check if a record exists to avoid re-uploading
    $upload_record = get_option('pcg_character_creator_uploaded_assets', array());
    
    if (empty($upload_record)) {
        // Get the paths to image assets
        $assets_dir = plugin_dir_path(__FILE__) . 'assets';
        $dice_images = array('d4.png', 'd6.png', 'd8.png', 'd10.png', 'd12.png', 'd20.png');
        
        // Upload each image to the media library and track their IDs
        $uploaded_assets = array();
        
        foreach ($dice_images as $image) {
            // Source and destination paths
            $source_path = $assets_dir . '/' . $image;
            $dest_path = $pcg_upload_path . '/' . $image;
            
            // Copy the file to the uploads directory
            if (file_exists($source_path)) {
                copy($source_path, $dest_path);
                
                // Prepare file for media library
                $file_type = wp_check_filetype($image, null);
                $attachment = array(
                    'guid'           => $upload_dir['baseurl'] . '/pcg-character-creator/images/' . $image,
                    'post_mime_type' => $file_type['type'],
                    'post_title'     => sanitize_file_name($image),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );
                
                // Insert as attachment
                $attach_id = wp_insert_attachment($attachment, $dest_path);
                
                // Generate metadata for the attachment
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $dest_path);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                // Track the uploaded asset
                $uploaded_assets[$image] = $attach_id;
            }
        }
        
        // Save record of uploaded assets
        update_option('pcg_character_creator_uploaded_assets', $uploaded_assets);
    }
}
register_activation_hook(__FILE__, 'pcg_character_creator_activate');

/**
 * Clean up uploaded images when plugin is deactivated
 */
function pcg_character_creator_deactivate() {
    // Get record of uploaded assets
    $uploaded_assets = get_option('pcg_character_creator_uploaded_assets', array());
    
    // Delete each attachment
    foreach ($uploaded_assets as $filename => $attachment_id) {
        wp_delete_attachment($attachment_id, true);
    }
    
    // Delete the upload directory
    $upload_dir = wp_upload_dir();
    $pcg_upload_path = $upload_dir['basedir'] . '/pcg-character-creator';
    
    // Recursively delete the directory and its contents
    if (file_exists($pcg_upload_path)) {
        pcg_character_creator_delete_directory($pcg_upload_path);
    }
    
    // Delete the record of uploaded assets
    delete_option('pcg_character_creator_uploaded_assets');
}
register_deactivation_hook(__FILE__, 'pcg_character_creator_deactivate');

/**
 * Helper function to recursively delete a directory using WordPress filesystem abstractions
 */
function pcg_character_creator_delete_directory($dir) {
    // Initialize the WordPress Filesystem with proper authentication
    global $wp_filesystem;
    
    if (!is_a($wp_filesystem, 'WP_Filesystem_Base')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        // Try to get credentials and initialize filesystem
        $creds = request_filesystem_credentials(site_url());
        wp_filesystem($creds);
        
        // If still not initialized, try direct method
        if (!is_a($wp_filesystem, 'WP_Filesystem_Base')) {
            // If we can, initialize without credentials using direct method
            if (!WP_Filesystem(false, false, true)) {
                // Legacy fallback implementation if WP_Filesystem fails completely
                if (!file_exists($dir)) {
                    return true;
                }
                
                if (!is_dir($dir)) {
                    return unlink($dir);
                }
                
                foreach (scandir($dir) as $item) {
                    if ($item == '.' || $item == '..') {
                        continue;
                    }
                    
                    if (!pcg_character_creator_delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                        return false;
                    }
                }
                
                return rmdir($dir);
            }
        }
    }
    
    // Use WP_Filesystem to check if the directory exists
    if (!$wp_filesystem->exists($dir)) {
        return true;
    }
    
    // If it's a file, delete it
    if (!$wp_filesystem->is_dir($dir)) {
        return $wp_filesystem->delete($dir);
    }
    
    // Get contents of the directory
    $contents = $wp_filesystem->dirlist($dir);
    
    // Process each item in the directory
    if (is_array($contents)) {
        foreach ($contents as $item) {
            $item_path = trailingslashit($dir) . $item['name'];
            
            if ($item['type'] == 'd') {
                // It's a directory, recursively delete it
                if (!pcg_character_creator_delete_directory($item_path)) {
                    return false;
                }
            } else {
                // It's a file, delete it
                if (!$wp_filesystem->delete($item_path)) {
                    return false;
                }
            }
        }
    }
    
    // Finally delete the empty directory
    return $wp_filesystem->rmdir($dir);
}