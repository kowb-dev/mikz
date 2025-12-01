<?php
/**
 * YITH WooCommerce Compare fixes.
 *
 * @package shoptimizer-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Disable YITH WooCommerce Compare button on product page.
 */
function kb_disable_yith_compare_button_on_product_page() {
    // Check if YITH WooCommerce Compare is active.
    if ( class_exists( 'YITH_Woocompare' ) ) {
        // Update the option to disable the compare button on product page.
        update_option( 'yith_woocompare_compare_button_in_product_page', 'no' );
    }
}
add_action( 'init', 'kb_disable_yith_compare_button_on_product_page' );
