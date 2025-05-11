<?php

namespace PCG\AccessControl\Core;

class UserRegistration {
    public function __construct() {
        // Remove email requirement from registration
        add_filter('registration_errors', [$this, 'remove_email_requirement'], 10, 3);
        
        // Add custom validation
        add_filter('registration_errors', [$this, 'validate_registration'], 10, 3);
        
        // Modify user creation process
        add_filter('pre_user_email', [$this, 'set_default_email'], 10, 1);
    }

    /**
     * Remove email requirement from registration
     */
    public function remove_email_requirement($errors, $sanitized_user_login, $user_email) {
        if (isset($errors->errors['empty_email'])) {
            unset($errors->errors['empty_email']);
        }
        if (isset($errors->errors['invalid_email'])) {
            unset($errors->errors['invalid_email']);
        }
        if (isset($errors->errors['email_exists'])) {
            unset($errors->errors['email_exists']);
        }
        return $errors;
    }

    /**
     * Validate registration with custom rules
     */
    public function validate_registration($errors, $sanitized_user_login, $user_email) {
        // Username validation
        if (empty($sanitized_user_login)) {
            $errors->add('empty_username', __('Username is required.', 'pcg-access-control'));
        } elseif (strlen($sanitized_user_login) < 3) {
            $errors->add('username_too_short', __('Username must be at least 3 characters long.', 'pcg-access-control'));
        }

        // Password validation
        if (empty($_POST['pass1'])) {
            $errors->add('empty_password', __('Password is required.', 'pcg-access-control'));
        } elseif (strlen($_POST['pass1']) < 8) {
            $errors->add('password_too_short', __('Password must be at least 8 characters long.', 'pcg-access-control'));
        }

        return $errors;
    }

    /**
     * Set a default email for the user
     */
    public function set_default_email($email) {
        if (empty($email)) {
            // Generate a unique email based on username
            $username = sanitize_user($_POST['user_login']);
            $email = $username . '@' . parse_url(get_site_url(), PHP_URL_HOST);
        }
        return $email;
    }
} 