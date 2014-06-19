<?php

if (!class_exists('DamUser')) {
    class DamUser
    {
        function __construct()
        {
            add_shortcode('dam-login', array($this, 'DamUserLoginShortcode'));
            add_shortcode('dam-register', array($this, 'DamUserRegisterShortcode'));
        }

        function DamUserLoginShortcode($atts)
        {
            extract(shortcode_atts(array(
                'logout_url' => home_url(),
                'logout_text' => '',
            ), $atts));

            ob_start();
            $userId = get_current_user_id();
            if (!$userId)
                $this->RenderLoginModule($logout_url);
            else
                echo '<a href="' . wp_logout_url(esc_url($logout_url)) . '" class="logout wpml-btn">' . sprintf(_x('%s', 'Logout Text', 'geissinger-wpml'), sanitize_text_field($logout_text)) . '</a>';

            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        function DamUserRegisterShortcode($atts)
        {
            ob_start();
            $this->RenderRegisterModule();
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        }

        function RenderLoginModule($logout_url)
        {
            ?>
            <div class="login wrap">
                <form name="login-form" id="login-form" action="" method="post">
                    <p class="message"></p>

                    <p>
                        <label for="dam_username"><?php echo __("Username", "dam-auction-masters"); ?><br>
                            <input type="text" name="log" id="dam_username" class="input" value="" size="20"></label>
                    </p>

                    <p>
                        <label for="dam_password"><?php echo __("Password", "dam-auction-masters"); ?><br>
                            <input type="password" name="pwd" id="dam_password" class="input" value=""
                                   size="20"></label>
                    </p>

                    <p class="forgetmenot"><label for="dam_remember_me"><input name="dam_remember_me" type="checkbox"
                                                                               id="dam_remember_me"
                                                                               value="forever"> <?php echo __("Remember Me", "dam-auction-masters"); ?>
                        </label></p>

                    <p class="submit">
                        <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large"
                               value="<?php echo __("Log In", "dam-auction-masters"); ?>">
                        <input type="hidden" name="redirect_to" value="<?php echo $logout_url; ?>">
                        <input type="hidden" name="login" value="true"/>
                        <?php wp_nonce_field('ajax-form-nonce', 'security'); ?>
                    </p>
                </form>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $('#login-form').submit(function (event) {
                            event.preventDefault();
                            var self = $(this);
                            $.ajax({
                                type: 'GET',
                                dataType: 'json',
                                url: wpml_script.ajax,
                                data: {
                                    'action': 'ajaxlogin', // Calls our wp_ajax_nopriv_ajaxlogin
                                    'username': $('#dam_username').val(),
                                    'password': $('#dam_password').val(),
                                    'rememberme': $('#dam_remember_me').val(),
                                    'login': $(this).find('input[name="login"]').val(),
                                    'security': $(this).find('input[name="security"]').val()
                                },
                                success: function (results) {
                                    if (results.loggedin === true) {
                                        self.find('p.message').removeClass('notice').addClass('success').text(results.message).show();
                                        window.location.href = self.find('input[name="redirect_to"]').val();
                                    } else {
                                        self.find('p.message').removeClass('notice').addClass('error').text(results.message).show();
                                    }
                                }
                            });

                        });
                    });
                </script>

            </div>
        <?php
        }

        function RenderRegisterModule()
        {
            ?>

            <div class="register">
                <form method="post" action="" id="register-form" name="register-form">
                    <p class="message"></p>

                    <p>
                        <label for="dam_reg_user"><?php echo __("Username", "dam-auction-masters"); ?><br>
                            <input type="text" size="20" value="" class="input" id="dam_reg_user"
                                   name="dam_reg_user"></label>
                    </p>

                    <p>
                        <label for="dam_reg_email"><?php echo __("E-mail", "dam-auction-masters"); ?><br>
                            <input type="text" size="25" value="" class="input" id="dam_reg_email"
                                   name="dam_reg_email"></label>
                    </p>

                    <p id="reg-password-email"><?php echo __("A password will be e-mailed to you.", "dam-auction-masters"); ?></p>
                    <br class="clear">

                    <p class="submit"><input type="submit" value="Register" class="button button-primary button-large"
                                             id="wp-submit" name="wp-submit"></p>
                    <input type="hidden" name="register" value="true"/>
                    <?php wp_nonce_field('ajax-form-nonce', 'security'); ?>
                </form>
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        $('#register-form').submit(function (event) {
                            event.preventDefault();
                            var self = $(this);
                            $.ajax({
                                type: 'GET',
                                dataType: 'json',
                                url: wpml_script.ajax,
                                data: {
                                    'action': 'ajaxlogin', // Calls our wp_ajax_nopriv_ajaxlogin
                                    'username': $('#dam_reg_user').val(),
                                    'email': $('#dam_reg_email').val(),
                                    'register': $(this).find('[name="register"]').val(),
                                    'security': $(this).find('input[name="security"]').val()
                                },
                                success: function (results) {
                                    if (results.registerd === true) {
                                        self.find('p.message').removeClass('notice').addClass('success').text(results.message).show();
                                        self[0].reset();
                                    } else {
                                        self.find('p.message').removeClass('notice').addClass('error').text(results.message).show();
                                    }
                                }
                            });
                        });
                    });
                </script>
            </div>
        <?php
        }
    }

    new DamUser();
}
