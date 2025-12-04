<?php
/**
 * Кастомная форма входа/регистрации для My Account
 *
 * @package Shoptimizer_Child
 * @version 1.6.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="mkx-auth-container">
    <div class="mkx-auth-toggle">
        <button id="mkx-login-btn" class="mkx-auth-tab active" type="button">Вход</button>
        <button id="mkx-register-btn" class="mkx-auth-tab" type="button">Регистрация</button>
    </div>

    <?php if ( isset( $_GET['password-changed'] ) ) : ?>
        <div class="mkx-auth-message success">
            <i class="ph ph-check-circle" aria-hidden="true"></i>
            <span>Пароль успешно изменен. Теперь вы можете войти.</span>
        </div>
    <?php endif; ?>

    <?php if ( isset( $_GET['registration-success'] ) ) : ?>
        <div class="mkx-auth-message success">
            <i class="ph ph-check-circle" aria-hidden="true"></i>
            <span>Регистрация успешна! Теперь вы можете войти.</span>
        </div>
    <?php endif; ?>

    <div id="mkx-login-form" class="mkx-auth-form active">
        <form class="woocommerce-form woocommerce-form-login login" method="post">

            <?php do_action( 'woocommerce_login_form_start' ); ?>

            <div class="mkx-form-group">
                <label for="username">Email или имя пользователя <span class="required">*</span></label>
                <input type="text" class="mkx-form-control" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
            </div>

            <div class="mkx-form-group">
                <label for="password">Пароль <span class="required">*</span></label>
                <input class="mkx-form-control" type="password" name="password" id="password" autocomplete="current-password" required />
            </div>

            <?php do_action( 'woocommerce_login_form' ); ?>

            <div class="mkx-form-remember">
                <label class="mkx-checkbox-label">
                    <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                    <span>Запомнить меня</span>
                </label>
            </div>

            <button type="submit" class="mkx-auth-submit woocommerce-button button woocommerce-form-login__submit" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">Войти</button>

            <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

            <div class="mkx-auth-footer">
                <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">Забыли пароль?</a>
            </div>

            <?php do_action( 'woocommerce_login_form_end' ); ?>

        </form>
    </div>

    <?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>

    <div id="mkx-register-form" class="mkx-auth-form">
        <form method="post" class="woocommerce-form woocommerce-form-register register" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

            <?php do_action( 'woocommerce_register_form_start' ); ?>

            <div class="mkx-form-group">
                <label for="reg_username">Имя пользователя <span class="required">*</span></label>
                <input type="text" class="mkx-form-control" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required />
            </div>

            <div class="mkx-form-group">
                <label for="reg_email">Email <span class="required">*</span></label>
                <input type="email" class="mkx-form-control" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required />
            </div>

            <div class="mkx-form-group">
                <label for="reg_billing_phone">Номер телефона <span class="required">*</span></label>
                <input type="tel" class="mkx-form-control" name="billing_phone" id="reg_billing_phone" autocomplete="tel" placeholder="+7(XXX)XXX-XX-XX" value="<?php echo ( ! empty( $_POST['billing_phone'] ) ) ? esc_attr( wp_unslash( $_POST['billing_phone'] ) ) : ''; ?>" required />
            </div>

            <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

            <div class="mkx-form-group">
                <label for="reg_password">Пароль <span class="required">*</span></label>
                <input type="password" class="mkx-form-control" name="password" id="reg_password" autocomplete="new-password" minlength="6" required />
            </div>

            <div class="mkx-form-group">
                <label for="reg_password_confirm">Подтвердите пароль <span class="required">*</span></label>
                <input type="password" class="mkx-form-control" name="password_confirm" id="reg_password_confirm" autocomplete="new-password" minlength="6" required />
            </div>

            <?php else : ?>

                <p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>

            <?php endif; ?>

            <?php do_action( 'woocommerce_register_form' ); ?>

            <button type="submit" class="mkx-auth-submit woocommerce-button button woocommerce-form-register__submit" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">Зарегистрироваться</button>

            <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

            <?php do_action( 'woocommerce_register_form_end' ); ?>

        </form>
    </div>

    <?php endif; ?>

</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
