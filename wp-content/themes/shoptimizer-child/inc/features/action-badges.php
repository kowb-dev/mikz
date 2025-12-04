<?php
/**
 * Action Links Badges
 *
 * @package Shoptimizer Child
 * @version 1.3.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function mkx_get_wishlist_count() {
    if ( class_exists( 'YITH_WCWL_Wishlist_Factory' ) ) {
        $wishlist = YITH_WCWL_Wishlist_Factory::get_default_wishlist();
        if ( $wishlist ) {
            return count( $wishlist->get_items() );
        }
    }
    return 0;
}

function mkx_get_compare_count() {
    if ( class_exists( 'YITH_Woocompare' ) && isset( $_COOKIE['yith_woocompare_list'] ) ) {
        $products = json_decode( stripslashes( $_COOKIE['yith_woocompare_list'] ) );
        return is_array( $products ) ? count( $products ) : 0;
    }
    return 0;
}

function mkx_enqueue_badge_assets() {
    wp_enqueue_script(
        'mkx-action-badges',
        get_stylesheet_directory_uri() . '/assets/js/action-badges.js',
        array( 'jquery' ),
        '1.3.0',
        true
    );
    
    wp_localize_script( 'mkx-action-badges', 'mkxBadges', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'mkx-badges-nonce' )
    ) );
}
add_action( 'wp_enqueue_scripts', 'mkx_enqueue_badge_assets' );

function mkx_ajax_get_badge_counts() {
    check_ajax_referer( 'mkx-badges-nonce', 'nonce' );
    
    wp_send_json_success( array(
        'cart' => WC()->cart->get_cart_contents_count(),
        'wishlist' => mkx_get_wishlist_count(),
        'compare' => mkx_get_compare_count()
    ) );
}
add_action( 'wp_ajax_mkx_get_badge_counts', 'mkx_ajax_get_badge_counts' );
add_action( 'wp_ajax_nopriv_mkx_get_badge_counts', 'mkx_ajax_get_badge_counts' );

function mkx_update_badge_fragments( $fragments ) {
    ob_start();
    $wishlist_count = mkx_get_wishlist_count();
    if ( $wishlist_count > 0 ) {
        echo '<span class="mkx-badge-count mkx-wishlist-count">' . $wishlist_count . '</span>';
    }
    $fragments['.mkx-wishlist-count'] = ob_get_clean();

    ob_start();
    $compare_count = mkx_get_compare_count();
    if ( $compare_count > 0 ) {
        echo '<span class="mkx-badge-count mkx-compare-count">' . $compare_count . '</span>';
    }
    $fragments['.mkx-compare-count'] = ob_get_clean();

    return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'mkx_update_badge_fragments' );