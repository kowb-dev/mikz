<?php
/**
 * Custom Product Actions
 *
 * @package shoptimizer-child
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Remove default single product actions from MKX_Wishlist and MKX_Compare.
 */
function mkx_remove_default_product_actions() {
    if ( class_exists( 'MKX_Wishlist' ) ) {
        remove_action( 'woocommerce_single_product_summary', array( MKX_Wishlist(), 'add_button_single' ), 35 );
    }
    if ( class_exists( 'MKX_Compare' ) ) {
        remove_action( 'woocommerce_single_product_summary', array( MKX_Compare(), 'add_button_single' ), 36 );
    }
}
add_action( 'init', 'mkx_remove_default_product_actions' );

/**
 * Add custom product actions after the add to cart form.
 */
function mkx_add_custom_product_actions() {
    global $product;
    $product_id = $product->get_id();

    $wishlist_class = MKX_Wishlist()->is_in_wishlist( $product_id ) ? 'mkx-wishlist-btn added' : 'mkx-wishlist-btn';
    $wishlist_title = MKX_Wishlist()->is_in_wishlist( $product_id ) ? 'Удалить из избранного' : 'Добавить в избранное';

    $compare_class = MKX_Compare()->is_in_compare( $product_id ) ? 'mkx-compare-btn added' : 'mkx-compare-btn';
    $compare_title = MKX_Compare()->is_in_compare( $product_id ) ? 'Удалить из сравнения' : 'Добавить к сравнению';
    ?>
    <div class="product-actions">
        <a href="#" class="<?php echo esc_attr( $wishlist_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" title="<?php echo esc_attr( $wishlist_title ); ?>"><i class="ph ph-heart" aria-hidden="true"></i></a>
        <a href="#" class="<?php echo esc_attr( $compare_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" title="<?php echo esc_attr( $compare_title ); ?>"><i class="ph ph-chart-bar" aria-hidden="true"></i></a>
    </div>
    <?php
}
add_action( 'woocommerce_after_add_to_cart_form', 'mkx_add_custom_product_actions', 10 );

/**
 * Remove the old product actions hook.
 */
function mkx_remove_old_product_actions() {
    remove_action( 'woocommerce_after_add_to_cart_form', 'kb_add_product_actions_after_add_to_cart', 10 );
}
add_action( 'init', 'mkx_remove_old_product_actions' );
