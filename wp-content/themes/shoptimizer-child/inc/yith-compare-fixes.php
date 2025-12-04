<?php
/**
 * YITH WooCommerce Compare fixes.
 *
 * @package shoptimizer-child
 * @version 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Disables the "Product added" notification for YITH Compare by filtering the label.
 *
 * @param string $label The original label text.
 * @return string An empty string to hide the notification.
 */
function mkx_yith_compare_remove_added_notice( $label ) {
    return '';
}
add_filter( 'yith_woocompare_compare_added_label', 'mkx_yith_compare_remove_added_notice' );