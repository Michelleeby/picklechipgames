<?php
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});
add_action( 'enqueue_block_editor_assets', function() {
  wp_enqueue_script( 'parent-script', get_template_directory_uri() . '/script.js', array(), '1.0.0', true );
});

// Remove date-based folders from uploads path
add_filter( 'upload_dir', function( $uploads ) {
    $uploads['path'] = str_replace( $uploads['subdir'], '', $uploads['path'] );
    $uploads['url'] = str_replace( $uploads['subdir'], '', $uploads['url'] );
    $uploads['subdir'] = '';
    return $uploads;
});