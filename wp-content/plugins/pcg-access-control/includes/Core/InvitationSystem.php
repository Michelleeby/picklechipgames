<?php

namespace PCG\AccessControl\Core;

class InvitationSystem {
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Handle invitation code validation
        add_filter('registration_errors', [$this, 'validate_invitation_code'], 10, 3);
        
        // Add invitation code field to registration form
        add_action('register_form', [$this, 'add_invitation_field']);
        
        // AJAX handlers for invitation management
        add_action('wp_ajax_generate_invitation', [$this, 'ajax_generate_invitation']);
        add_action('wp_ajax_revoke_invitation', [$this, 'ajax_revoke_invitation']);

        // Mark invitation as used after registration
        add_action('user_register', [$this, 'mark_invitation_as_used'], 10, 1);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'users.php',
            'User Invitations',
            'User Invitations',
            'manage_options',
            'pcg-invitations',
            [$this, 'render_admin_page']
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get existing invitations
        $invitations = $this->get_invitations();
        ?>
        <div class="wrap">
            <h1>User Invitations</h1>
            
            <div class="card">
                <h2>Generate New Invitation</h2>
                <p>Generate a new invitation code for user registration.</p>
                <button class="button button-primary" id="generate-invitation">Generate Invitation</button>
            </div>

            <div class="card">
                <h2>Active Invitations</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Invitation Code</th>
                            <th>Created By</th>
                            <th>Created Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invitations as $invitation): ?>
                        <tr>
                            <td><?php echo esc_html($invitation->code); ?></td>
                            <td><?php echo esc_html($invitation->created_by); ?></td>
                            <td><?php echo esc_html($invitation->created_date); ?></td>
                            <td><?php echo esc_html($invitation->status); ?></td>
                            <td>
                                <?php if ($invitation->status === 'active'): ?>
                                <button class="button revoke-invitation" data-code="<?php echo esc_attr($invitation->code); ?>">Revoke</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#generate-invitation').on('click', function() {
                $.post(ajaxurl, {
                    action: 'generate_invitation',
                    nonce: '<?php echo wp_create_nonce('generate_invitation'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            });

            $('.revoke-invitation').on('click', function() {
                const code = $(this).data('code');
                $.post(ajaxurl, {
                    action: 'revoke_invitation',
                    code: code,
                    nonce: '<?php echo wp_create_nonce('revoke_invitation'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    }
                });
            });
        });
        </script>
        <?php
    }

    public function add_invitation_field() {
        ?>
        <p>
            <label for="invitation_code"><?php _e('Invitation Code', 'pcg-access-control'); ?></label>
            <input type="text" name="invitation_code" id="invitation_code" class="input" required />
        </p>
        <?php
    }

    public function validate_invitation_code($errors, $sanitized_user_login, $user_email) {
        // Rate limiting: max 5 attempts per 10 minutes per IP
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'pcg_reg_attempts_' . md5($ip);
        $attempts = (int) get_transient($key);
        if ($attempts >= 5) {
            $errors->add('rate_limited', __('Too many registration attempts. Please try again in 10 minutes.', 'pcg-access-control'));
            return $errors;
        }
        set_transient($key, $attempts + 1, 10 * MINUTE_IN_SECONDS);

        if (empty($_POST['invitation_code'])) {
            $errors->add('empty_invitation', __('Invitation code is required.', 'pcg-access-control'));
            return $errors;
        }

        $code = sanitize_text_field($_POST['invitation_code']);
        if (!$this->is_valid_invitation($code)) {
            $errors->add('invalid_invitation', __('Invalid or expired invitation code.', 'pcg-access-control'));
        }

        return $errors;
    }

    public function is_valid_invitation($code) {
        // Public wrapper that can add additional validation or logging
        if (empty($code)) {
            return false;
        }
        
        // Log validation attempts if needed
        do_action('pcg_invitation_validation_attempt', $code);
        
        return $this->validate_invitation_internal($code);
    }

    private function validate_invitation_internal($code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_invitations';
        
        $invitation = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE code = %s AND status = 'active'",
            $code
        ));
        if (empty($invitation)) {
            return false;
        }
        // Check expiration
        if (!empty($invitation->expires_at) && strtotime($invitation->expires_at) < time()) {
            return false;
        }
        return true;
    }

    private function get_invitations() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_invitations';
        
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_date DESC");
    }

    public function ajax_generate_invitation() {
        check_ajax_referer('generate_invitation', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $code = $this->generate_unique_code();
        $this->save_invitation($code);
        
        wp_send_json_success();
    }

    public function ajax_revoke_invitation() {
        check_ajax_referer('revoke_invitation', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }

        $code = sanitize_text_field($_POST['code']);
        $this->revoke_invitation($code);
        
        wp_send_json_success();
    }

    private function generate_unique_code() {
        $code = wp_generate_password(12, false);
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_invitations';
        
        // Ensure code is unique
        while ($wpdb->get_var($wpdb->prepare("SELECT code FROM $table_name WHERE code = %s", $code))) {
            $code = wp_generate_password(12, false);
        }
        
        return $code;
    }

    private function save_invitation($code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_invitations';
        
        $wpdb->insert(
            $table_name,
            [
                'code' => $code,
                'created_by' => get_current_user_id(),
                'created_date' => current_time('mysql'),
                'status' => 'active',
                'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'used_by' => null,
                'used_at' => null
            ],
            ['%s', '%d', '%s', '%s', '%s', '%d', '%s']
        );
    }

    private function revoke_invitation($code) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_invitations';
        
        $wpdb->update(
            $table_name,
            ['status' => 'revoked'],
            ['code' => $code],
            ['%s'],
            ['%s']
        );
    }

    public function mark_invitation_as_used($user_id) {
        if (empty($_POST['invitation_code'])) {
            return;
        }
        $code = sanitize_text_field($_POST['invitation_code']);
        global $wpdb;
        $table_name = $wpdb->prefix . 'pcg_invitations';
        $wpdb->update(
            $table_name,
            [
                'status' => 'used',
                'used_by' => $user_id,
                'used_at' => current_time('mysql')
            ],
            ['code' => $code],
            ['%s', '%d', '%s'],
            ['%s']
        );
    }
} 