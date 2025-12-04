<?php
/**
 * Custom My Account Page
 *
 * @package Shoptimizer Child
 * @version 1.6.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MKX_Custom_Account {
    
    private static $instance = null;
    
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'woocommerce_register_post', array( $this, 'validate_phone_field' ), 10, 3 );
        add_action( 'woocommerce_created_customer', array( $this, 'save_phone_field' ) );
    }
    
    public function enqueue_assets() {
        if ( is_account_page() && ! is_user_logged_in() ) {
            wp_enqueue_style(
                'mkx-account',
                get_stylesheet_directory_uri() . '/assets/css/custom-account.css',
                array(),
                '1.6.0'
            );
            
            wp_enqueue_script(
                'mkx-account',
                get_stylesheet_directory_uri() . '/assets/js/custom-account.js',
                array( 'jquery' ),
                '1.6.0',
                true
            );
        }
    }
    
    public function validate_phone_field( $username, $email, $validation_errors ) {
        if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {
            $validation_errors->add( 'billing_phone_error', 'Пожалуйста, введите номер телефона.' );
        }
    }
    
    public function save_phone_field( $customer_id ) {
        if ( isset( $_POST['billing_phone'] ) ) {
            update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
        }
    }
}

function MKX_Custom_Account() {
    return MKX_Custom_Account::instance();
}

MKX_Custom_Account();
