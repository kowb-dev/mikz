jQuery(document).ready(function($) {
    'use strict';

    const $loginBtn = $('#mkx-login-btn');
    const $registerBtn = $('#mkx-register-btn');
    const $loginForm = $('#mkx-login-form');
    const $registerForm = $('#mkx-register-form');

    $loginBtn.on('click', function() {
        $loginBtn.addClass('active');
        $registerBtn.removeClass('active');
        $loginForm.addClass('active');
        $registerForm.removeClass('active');
    });

    $registerBtn.on('click', function() {
        $registerBtn.addClass('active');
        $loginBtn.removeClass('active');
        $registerForm.addClass('active');
        $loginForm.removeClass('active');
    });

    const $registerFormElement = $('.woocommerce-form-register');
    $registerFormElement.on('submit', function(e) {
        const password = $('#reg_password').val();
        const passwordConfirm = $('#reg_password_confirm').val();

        if (password && passwordConfirm && password !== passwordConfirm) {
            e.preventDefault();
            alert('Пароли не совпадают!');
            return false;
        }
    });

    if (window.location.hash === '#register') {
        $registerBtn.trigger('click');
    }
});
