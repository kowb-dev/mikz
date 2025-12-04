<?php
/**
 * YITH WooCommerce Wishlist fixes
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mkx_yith_wishlist_ajax_update_count() {
    if ( function_exists( 'yith_wcwl_count_products' ) ) {
        wp_send_json( array( 'count' => yith_wcwl_count_products() ) );
    } else {
        wp_send_json( array( 'count' => 0 ) );
    }
}
add_action( 'wp_ajax_yith_wcwl_update_wishlist_count', 'mkx_yith_wishlist_ajax_update_count' );
add_action( 'wp_ajax_nopriv_yith_wcwl_update_wishlist_count', 'mkx_yith_wishlist_ajax_update_count' );

function mkx_yith_wishlist_localize_scripts() {
    if ( ! wp_script_is( 'jquery-yith-wcwl', 'enqueued' ) ) {
        return;
    }

    wp_localize_script(
        'jquery-yith-wcwl',
        'yith_wcwl_l10n_extra',
        array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'redirect_to_cart' => 'no',
            'multi_wishlist' => get_option( 'yith_wcwl_multi_wishlist_enable' ) === 'yes',
            'hide_add_button' => 'no',
            'enable_ajax_loading' => 'yes',
            'ajax_loader_url' => esc_url( apply_filters( 'yith_wcwl_ajax_loader_gif', YITH_WCWL_URL . 'assets/images/ajax-loader.gif' ) ),
            'remove_from_wishlist_after_add_to_cart' => 'no',
            'labels' => array(
                'cookie_disabled' => __( 'We are sorry, but this feature is available only if cookies are enabled on your browser.', 'yith-woocommerce-wishlist' ),
                'added_to_cart_message' => sprintf( '<div class="woocommerce-notices-wrapper"><div class="woocommerce-message">%s</div></div>', __( 'Product added to cart successfully', 'yith-woocommerce-wishlist' ) ),
            ),
            'actions' => array(
                'add_to_wishlist_action' => 'add_to_wishlist',
                'remove_from_wishlist_action' => 'remove_from_wishlist',
                'reload_wishlist_and_adding_elem_action' => 'reload_wishlist_and_adding_elem',
                'load_mobile_action' => 'load_mobile',
                'delete_item_action' => 'delete_item',
                'save_title_action' => 'save_title',
                'save_privacy_action' => 'save_privacy',
                'load_fragments' => 'load_fragments',
            ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'mkx_yith_wishlist_localize_scripts', 20 );

function mkx_yith_wishlist_suppress_default_message() {
    return '';
}
add_filter( 'yith_wcwl_product_added_to_wishlist_message', 'mkx_yith_wishlist_suppress_default_message' );

function mkx_yith_wishlist_enable_ajax() {
    return 'yes';
}
add_filter( 'yith_wcwl_is_wishlist_responsive', '__return_true' );
add_filter( 'yith_wcwl_ajax_enable', 'mkx_yith_wishlist_enable_ajax' );
