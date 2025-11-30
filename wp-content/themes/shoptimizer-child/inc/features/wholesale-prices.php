<?php
/**
 * Wholesale Prices Display
 *
 * Replaces the default WooCommerce price output to show both retail and wholesale prices.
 * - For archive pages, it replaces the entire price block.
 * - For single product pages, it filters the price HTML to inject the new format.
 *
 * @package Shoptimizer Child
 * @version 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Generates the custom HTML for displaying both retail and wholesale prices.
 * This is a reusable function to keep the code DRY.
 *
 * @param WC_Product $product The product object.
 * @return string The price HTML.
 */
function shoptimizer_child_get_custom_price_html( $product ) {
	if ( ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}

	$wholesale_price = get_post_meta( $product->get_id(), '_wholesale_price', true );
	$retail_price = $product->get_price();

	// Start building the output
	$output = '<div class="custom-price-container">';

	// --- Retail Price Row ---
	if ( '' !== $retail_price ) {
		$output .= '<div class="price-row retail-price-row">';
		$output .= '<div class="price-label">' . esc_html__( 'РОЗН.', 'shoptimizer-child' ) . '</div>';

		// Handle sale price display
		if ( $product->is_on_sale() ) {
			$regular_price = $product->get_regular_price();
			$output .= '<div class="price-value"><del>' . wp_kses_post( wc_price( $regular_price ) ) . '</del> <ins>' . wp_kses_post( wc_price( $retail_price ) ) . '</ins></div>';
		} else {
			$output .= '<div class="price-value">' . wp_kses_post( wc_price( $retail_price ) ) . '</div>';
		}
		$output .= '</div>'; // .price-row
	}

	// --- Wholesale Price Row ---
	if ( ! empty( $wholesale_price ) && (float) $wholesale_price > 0 ) {
		$output .= '<div class="price-row wholesale-price-row">';
		$output .= '<div class="price-label wholesale-label">' . esc_html__( 'ОПТ', 'shoptimizer-child' ) . '</div>';
		$output .= '<div class="price-value wholesale-value">' . wp_kses_post( wc_price( $wholesale_price ) ) . '</div>';
		$output .= '</div>'; // .price-row
	}

	$output .= '</div>'; // .custom-price-container

	return $output;
}

/**
 * Filters the product price HTML on single product pages.
 *
 * @param string $price_html The original price HTML.
 * @param WC_Product $product The product object.
 * @return string The modified price HTML.
 */
function shoptimizer_child_filter_price_html( $price_html, $product ) {
	// Only apply this on single product pages.
	if ( is_product() ) {
		// Replace the default price with our custom formatted price.
		// The container for this is the <p class="price"> tag.
		// We return our custom HTML which will be placed inside that tag.
		return shoptimizer_child_get_custom_price_html( $product );
	}
	
	// For all other pages, return the original price HTML.
	return $price_html;
}
add_filter( 'woocommerce_get_price_html', 'shoptimizer_child_filter_price_html', 100, 2 );


/**
 * Custom price display function for archive/shop pages.
 * This replaces the default woocommerce_template_loop_price.
 */
function shoptimizer_child_template_loop_price() {
	global $product;
	echo shoptimizer_child_get_custom_price_html( $product );
}

/**
 * Replace the default price hook for ARCHIVE pages only.
 * The single product page is now handled by the 'woocommerce_get_price_html' filter.
 */
function shoptimizer_child_replace_archive_price_hook() {
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	add_action( 'woocommerce_after_shop_loop_item_title', 'shoptimizer_child_template_loop_price', 10 );
}
add_action( 'init', 'shoptimizer_child_replace_archive_price_hook' );