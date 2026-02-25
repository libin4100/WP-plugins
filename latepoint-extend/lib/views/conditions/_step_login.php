<div class="step-login-w latepoint-step-content" data-step-name="login">
    <div class="latepoint-desc-content" style="padding: 0 0 12px 0;">
        Sign in before submitting an EAP request. You can also continue without login.
    </div>

    <?php
    $gtdLoginStatus = $gtd_login_status ?? 'not_login';
    ?>

    <div class="gtd-login-card">
        <div class="os-row">
            <div class="os-col-12">
                <div class="os-form-group os-form-textfield-group os-form-group-transparent">
                    <label for="gtd_login_username"><?php esc_html_e('Username', 'latepoint-extand-master'); ?></label>
                    <input type="text" id="gtd_login_username" class="os-form-control" name="booking[gtd_login][username]" value="" placeholder="<?php esc_attr_e('Enter username', 'latepoint-extand-master'); ?>">
                </div>
            </div>
            <div class="os-col-12">
                <div class="os-form-group os-form-textfield-group os-form-group-transparent">
                    <label for="gtd_login_password"><?php esc_html_e('Password', 'latepoint-extand-master'); ?></label>
                    <input type="password" id="gtd_login_password" class="os-form-control" name="booking[gtd_login][password]" value="" placeholder="<?php esc_attr_e('Enter password', 'latepoint-extand-master'); ?>">
                </div>
            </div>
            <input type="hidden" name="booking[custom_fields][gtd_login_status]" value="<?php echo esc_attr($gtdLoginStatus); ?>">
            <input type="hidden" name="booking[custom_fields][gtd_reset_requested]" value="0">
            <div class="os-col-12">
                <div class="gtd-login-error" style="display:none;"></div>
                <div class="gtd-login-success" style="display:none;"></div>
            </div>
            <div class="os-col-12 gtd-login-actions">
                <a href="#" class="latepoint-btn gtd-login-submit"><?php esc_html_e('Login and Continue', 'latepoint-extand-master'); ?></a>
                <a href="#" class="latepoint-btn gtd-login-reset"><?php esc_html_e('Reset Password', 'latepoint-extand-master'); ?></a>
                <a href="#" class="latepoint-btn gtd-login-skip"><?php esc_html_e('Request without login', 'latepoint-extand-master'); ?></a>
            </div>
            <div class="os-col-12 gtd-reset-panel" style="display:none;">
                <div class="os-form-group os-form-textfield-group os-form-group-transparent">
                    <label for="gtd_reset_email"><?php esc_html_e('Reset with email', 'latepoint-extand-master'); ?></label>
                    <input type="email" id="gtd_reset_email" class="os-form-control" name="booking[gtd_login][reset_email]" value="" placeholder="<?php esc_attr_e('name@example.com', 'latepoint-extand-master'); ?>">
                </div>
                <div class="gtd-reset-actions">
                    <a href="#" class="latepoint-btn gtd-reset-submit"><?php esc_html_e('Submit reset request', 'latepoint-extand-master'); ?></a>
                    <a href="#" class="gtd-reset-cancel"><?php esc_html_e('Cancel', 'latepoint-extand-master'); ?></a>
                </div>
            </div>
        </div>
    </div>

    <div class="latepoint-desc-content" style="padding: 10px 0 0 0;">
        Need portal access? Open <a href="https://gotodoctor.ca/cosefap" target="_blank" rel="noopener">gotodoctor.ca/cosefap</a>.
    </div>

    <script>
        jQuery(function($) {
            const $root = $('.step-login-w[data-step-name="login"]');
            if (!$root.length) return;

            const $status = $root.find('input[name="booking[custom_fields][gtd_login_status]"]');
            const $resetRequested = $root.find('input[name="booking[custom_fields][gtd_reset_requested]"]');
            const $username = $root.find('#gtd_login_username');
            const $password = $root.find('#gtd_login_password');
            const $resetEmail = $root.find('#gtd_reset_email');
            const $error = $root.find('.gtd-login-error');
            const $success = $root.find('.gtd-login-success');
            const $resetPanel = $root.find('.gtd-reset-panel');

            $('.latepoint-booking-form-element').removeClass('hidden-buttons');
            $('.latepoint-footer').show();

            const triggerNextStep = function() {
                const $nextBtn = $('.latepoint-footer .latepoint-next-btn');
                if ($nextBtn.length) {
                    const $btn = $nextBtn.first();
                    const isDisabled = $btn.hasClass('disabled') || $btn.is(':disabled') || $btn.attr('aria-disabled') === 'true';
                    if (!isDisabled) {
                        $btn.trigger('click');
                        return true;
                    }
                }
                const $form = $('.latepoint-form');
                if ($form.length) {
                    $form.first().trigger('submit');
                    return true;
                }
                if ($nextBtn.length) {
                    $nextBtn.first().removeClass('disabled').prop('disabled', false).removeAttr('aria-disabled').trigger('click');
                    return true;
                }
                return false;
            };

            const goNext = function(status) {
                $status.val(status);
                if (!triggerNextStep()) {
                    $error.text('Unable to continue to next step. Please refresh and try again.').show();
                }
            };

            $root.on('click', '.gtd-login-submit', function(e) {
                e.preventDefault();
                const username = $.trim($username.val());
                const password = $.trim($password.val());
                if (!username || !password) {
                    $error.text('Please enter username and password, or choose "Request without login".').show();
                    $success.hide().text('');
                    return;
                }
                $error.hide().text('');
                $success.hide().text('');
                goNext('login');
            });

            $root.on('click', '.gtd-login-skip', function(e) {
                e.preventDefault();
                $error.hide().text('');
                $success.hide().text('');
                goNext('not_login');
            });

            $root.on('click', '.gtd-login-reset', function(e) {
                e.preventDefault();
                $error.hide().text('');
                $success.hide().text('');
                $resetPanel.slideToggle(120);
            });

            $root.on('click', '.gtd-reset-cancel', function(e) {
                e.preventDefault();
                $resetPanel.slideUp(120);
            });

            $root.on('click', '.gtd-reset-submit', function(e) {
                e.preventDefault();
                const email = $.trim($resetEmail.val());
                if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $error.text('Please enter a valid email address for password reset.').show();
                    $success.hide().text('');
                    return;
                }
                $error.hide().text('');
                $resetRequested.val('1');
                $success.text('Reset request submitted. You can continue with login or choose "Request without login".').show();
            });
        });
    </script>
    <style>
        .step-login-w .gtd-login-card {
            border: 1px solid #d8dfeb;
            border-radius: 10px;
            padding: 14px;
            background: #fbfcff;
        }
        .step-login-w .gtd-login-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .step-login-w .gtd-login-actions .latepoint-btn {
            min-width: 180px;
            text-align: center;
            border-radius: 8px;
        }
        .step-login-w .gtd-login-actions .gtd-login-skip {
            background: #f5f7fb;
            color: #1f3d6d;
            border: 1px solid #cfd7e6;
        }
        .step-login-w .gtd-login-actions .gtd-login-reset {
            background: #ffffff;
            color: #273a67;
            border: 1px solid #9fb2d3;
        }
        .step-login-w .gtd-reset-panel {
            border-top: 1px dashed #ccd5e5;
            margin-top: 12px;
            padding-top: 12px;
        }
        .step-login-w .gtd-reset-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .step-login-w .gtd-reset-cancel {
            color: #475467;
            text-decoration: underline;
        }
        .step-login-w .gtd-login-error {
            color: #b42318;
            font-size: 13px;
            margin-top: 6px;
        }
        .step-login-w .gtd-login-success {
            color: #067647;
            font-size: 13px;
            margin-top: 6px;
        }
    </style>
</div>
