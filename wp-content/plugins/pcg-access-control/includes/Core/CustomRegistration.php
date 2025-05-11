<?php

namespace PCG\AccessControl\Core;

class CustomRegistration {
    public function __construct() {
        add_shortcode('pcg_custom_register', [$this, 'render_registration_form']);
    }

    public function render_registration_form() {
        if (is_user_logged_in()) {
            return '<p>You are already registered and logged in.</p>';
        }

        $errors = [];
        $success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pcg_register_nonce']) && wp_verify_nonce($_POST['pcg_register_nonce'], 'pcg_register')) {
            $username = sanitize_user($_POST['user_login']);
            $password = $_POST['user_pass'];
            $invitation_code = sanitize_text_field($_POST['invitation_code']);

            // Validate username
            if (empty($username) || strlen($username) < 3) {
                $errors[] = 'Username must be at least 3 characters.';
            } elseif (username_exists($username)) {
                $errors[] = 'Username already exists.';
            }

            // Validate password
            if (empty($password) || strlen($password) < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }

            // Validate invitation code
            $invitation_system = new InvitationSystem();
            if (!$invitation_system->is_valid_invitation($invitation_code)) {
                $errors[] = 'Invalid or expired invitation code.';
            }

            if (empty($errors)) {
                // Generate dummy email
                $email = $username . '@' . parse_url(get_site_url(), PHP_URL_HOST);
                $user_id = wp_create_user($username, $password, $email);
                if (is_wp_error($user_id)) {
                    $errors[] = $user_id->get_error_message();
                } else {
                    // Mark invitation as used and assign to campaign
                    $invitation_system->mark_invitation_as_used($user_id);
                    // Auto-login
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    do_action('wp_login', $username, get_user_by('id', $user_id));
                    // Redirect to portal
                    wp_redirect(site_url('/portal/'));
                    exit;
                }
            }
        }

        ob_start();
        if (!empty($errors)) {
            echo '<div class="pcg-errors"><ul>';
            foreach ($errors as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul></div>';
        }
        ?>
        <form method="post" class="pcg-custom-register">
            <p>
                <label for="user_login">Username</label>
                <input type="text" name="user_login" id="user_login" required minlength="3">
            </p>
            <p>
                <label for="user_pass">Password</label>
                <input type="password" name="user_pass" id="user_pass" required minlength="8">
            </p>
            <p>
                <label for="invitation_code">Invitation Code</label>
                <input type="text" name="invitation_code" id="invitation_code" required>
            </p>
            <?php wp_nonce_field('pcg_register', 'pcg_register_nonce'); ?>
            <p><input type="submit" value="Register"></p>
        </form>
        <?php
        return ob_get_clean();
    }
}

// Register the shortcode on init
add_action('init', function() {
    new \PCG\AccessControl\Core\CustomRegistration();
}); 