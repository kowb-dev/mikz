<?php
/**
 * Custom Stock Status Display for WooCommerce
 *
 * This file modifies the default WooCommerce stock status text to display custom statuses.
 *
 * @package Shoptimizer Child
 * @version 1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

add_filter( 'woocommerce_get_availability', 'shoptimizer_child_custom_availability', 999, 2 );

/**
 * Customizes the product availability text based on the availability class.
 *
 * @param array $availability The original availability data.
 * @param WC_Product $product The product object.
 * @return array The modified availability data.
 */
function shoptimizer_child_custom_availability( $availability, $product ) {
    $stock_status = $product->get_stock_status();

    if ( 'onbackorder' === $stock_status ) {
        $availability['availability'] = __( 'ПОД ЗАКАЗ', 'shoptimizer-child' );
        $availability['class'] = 'on-order';
        return $availability;
    }

    if ( 'instock' === $stock_status || $product->is_in_stock() ) {
        $availability['availability'] = __( 'В НАЛИЧИИ', 'shoptimizer-child' );
        $availability['class'] = 'in-stock';
        return $availability;
    }

    return $availability;
}
