<?php
/**
 * WooCommerce Integration Functions
 *
 * @package Shoptimizer Child
 * @version 1.0.5
 * @author KW
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shoptimizer_child_remove_parent_theme_related_products_filter() {
    remove_filter( 'woocommerce_output_related_products_args', 'shoptimizer_related_products', 99 );
}
add_action( 'after_setup_theme', 'shoptimizer_child_remove_parent_theme_related_products_filter', 10 );

add_filter( 'woocommerce_add_to_cart_fragments', 'shoptimizer_child_cart_fragments' );
function shoptimizer_child_cart_fragments( $fragments ) {
    $cart_count = WC()->cart->get_cart_contents_count();
    
    ob_start();
    if ( $cart_count > 0 ) {
        echo '<span class="mkx-badge-count mkx-cart-count mkx-badge-visible" aria-label="' . esc_attr( sprintf( _n( '%s товар в корзине', '%s товаров в корзине', $cart_count, 'shoptimizer-child' ), $cart_count ) ) . '" style="animation: cartBounce 0.3s var(--mkx-ease);">';
        echo $cart_count;
        echo '</span>';
    } else {
        echo '';
    }
    $fragments['.mkx-cart-count'] = ob_get_clean();

    ob_start();
    if ( $cart_count > 0 ) {
        echo '<span class="mkx-mobile-nav-cart-count mkx-badge-visible" aria-label="' . esc_attr( sprintf( _n( '%s товар в корзине', '%s товаров в корзине', $cart_count, 'shoptimizer-child' ), $cart_count ) ) . '">';
        echo $cart_count;
        echo '</span>';
    } else {
        echo '';
    }
    $fragments['.mkx-mobile-nav-cart-count'] = ob_get_clean();

    return $fragments;
}

/**
 * Change number of related products output
 */
add_filter( 'woocommerce_output_related_products_args', 'kb_related_products_args', 99 );
function kb_related_products_args( $args ) {
    $args['posts_per_page'] = 5; // 5 related products
    $args['columns'] = 5; // 5 columns
    return $args;
}

/**
 * Remove default wrappers from single product summary to allow for custom layout.
 */
function remove_shoptimizer_wrappers() {
    remove_action( 'woocommerce_before_single_product_summary', 'shoptimizer_product_content_wrapper_start', 5 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_product_content_wrapper_end', 120 );
}
add_action( 'wp_head', 'remove_shoptimizer_wrappers' );

/**
 * Enqueue custom stylesheet for the shop page list view.
 */
function mkz_enqueue_shop_list_view_styles() {
    if ( is_shop() ) {
        wp_enqueue_style(
            'mkz-shop-list-view',
            get_stylesheet_directory_uri() . '/assets/css/shop-list-view.css',
            array(),
            '1.0.0'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'mkz_enqueue_shop_list_view_styles' );