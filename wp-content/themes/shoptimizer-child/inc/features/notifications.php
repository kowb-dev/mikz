<?php
/**
 * Custom Notification System
 *
 * @package Shoptimizer Child
 * @version 1.4.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mkx_enqueue_notification_assets() {
    wp_enqueue_style(
        'mkx-notifications',
        get_stylesheet_directory_uri() . '/assets/css/notifications.css',
        array(),
        '1.4.0'
    );

    wp_enqueue_script(
        'mkx-notifications',
        get_stylesheet_directory_uri() . '/assets/js/notifications.js',
        array( 'jquery' ),
        '1.4.0',
        true
    );

    wp_localize_script( 'mkx-notifications', 'mkxNotifications', array(
        'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
        'nonce'          => wp_create_nonce( 'mkx_notifications_nonce' ),
        'getProductNameAction' => 'mkx_get_product_name',
        'addedToCart'    => __( 'Товар добавлен в корзину', 'shoptimizer-child' ),
        'addedToWishlist' => __( 'Товар добавлен в избранное', 'shoptimizer-child' ),
        'addedToCompare' => __( 'Товар добавлен к сравнению', 'shoptimizer-child' ),
        'removedFromWishlist' => __( 'Товар удален из избранного', 'shoptimizer-child' ),
        'removedFromCompare' => __( 'Товар удален из сравнения', 'shoptimizer-child' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'mkx_enqueue_notification_assets' );

function mkx_add_notification_container() {
    echo '<div id="mkx-notification-container" aria-live="polite" aria-atomic="true"></div>';
}
add_action( 'wp_footer', 'mkx_add_notification_container' );

/**
 * AJAX handler to get product name by ID.
 */
function mkx_get_product_name_ajax() {
    check_ajax_referer( 'mkx_notifications_nonce', 'nonce' );
    
    if ( isset( $_POST['product_id'] ) ) {
        $product_id = absint( $_POST['product_id'] );
        $product = wc_get_product( $product_id );
        if ( $product ) {
            wp_send_json_success( array( 'product_name' => $product->get_name() ) );
        }
    }
    wp_send_json_error( array( 'message' => 'Product not found.' ) );
}
add_action( 'wp_ajax_mkx_get_product_name', 'mkx_get_product_name_ajax' );
add_action( 'wp_ajax_nopriv_mkx_get_product_name', 'mkx_get_product_name_ajax' );