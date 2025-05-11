<?php
// Register the dynamic example block
add_action('init', function() {
    if (!function_exists('register_block_type')) return;
    register_block_type('pcg-campaign-core/example-block', [
        'render_callback' => function($attributes, $content) {
            return '<div>Hello from Example Block (frontend render)</div>';
        }
    ]);
}); 