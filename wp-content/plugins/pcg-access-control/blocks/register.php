<?php
// Register the dynamic registration block
add_action('init', function() {
    if (!function_exists('register_block_type')) return;

    // Register the block
    register_block_type(__DIR__ . '/src/register', [
        'render_callback' => function($attributes, $content) {
            if (!class_exists('PCG\\AccessControl\\Core\\CustomRegistration')) return '';
            $registration = new PCG\AccessControl\Core\CustomRegistration();
            return $registration->render_registration_form();
        }
    ]);
}); 