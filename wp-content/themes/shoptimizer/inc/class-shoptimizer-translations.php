<?php
/**
 * Shoptimizer Translation Support
 *
 * This file enables translation of customizer text fields with popular WordPress
 * translation plugins including Polylang and WPML. It works by:
 *
 * 1. Defining an array of customizer field keys that should be translatable
 * 2. Registering these strings with the active translation plugin on init
 * 3. Filtering theme_mod() calls to return translated versions of these strings
 * 4. Supporting multiple translation plugins: Polylang, WPML (new API), and legacy WPML
 *
 * Supported translation plugins:
 * - Polylang (recommended)
 * - WPML (newer API with wpml_register_single_string)
 * - Legacy WPML (deprecated icl_ functions, but still functional)
 *
 * Usage:
 * - Add new translatable fields to the $shoptimizer_translatable_fields array
 * - The translation plugin will automatically pick up these strings
 * - Frontend calls to get_theme_mod() will return translated versions
 *
 * @package shoptimizer
 * @since 1.0.0
 * @author CommerceGurus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define translatable customizer fields
 * 
 * Add new field keys to this array to make them translatable.
 * These should be the exact keys used in your customizer settings.
 */
global $shoptimizer_translatable_fields;
$shoptimizer_translatable_fields = array(
	'shoptimizer_mobile_menu_text',
	'shoptimizer_cross_sells_carousel_heading',
	'shoptimizer_layout_floating_button_text',
	'shoptimizer_upsells_title_text',
	'shoptimizer_layout_search_title',
	'shoptimizer_cart_title',
	'shoptimizer_cart_below_text',
);

/**
 * Register translatable strings with translation plugins
 *
 * This function runs on init and registers all translatable strings
 * with the active translation plugin (Polylang or WPML).
 */
if ( ! function_exists( 'register_shoptimizer_translatable_strings' ) ) {
	function register_shoptimizer_translatable_strings() {
		global $shoptimizer_translatable_fields;
		
		if ( function_exists( 'pll_register_string' ) ) {
			// Polylang support - most efficient method
			foreach ( $shoptimizer_translatable_fields as $key ) {
				$slug = str_replace( '_', '-', $key );
				pll_register_string( $slug, get_theme_mod( $key ), 'shoptimizer' );
			}
		} elseif ( has_action( 'wpml_register_single_string' ) ) {
			// WPML support (newer API) - recommended for WPML users
			foreach ( $shoptimizer_translatable_fields as $key ) {
				$value = get_theme_mod( $key );
				do_action( 'wpml_register_single_string', 'shoptimizer', $value, $value );
			}
		} elseif ( function_exists( 'icl_register_string' ) ) {
			// Legacy WPML support (deprecated but still functional)
			foreach ( $shoptimizer_translatable_fields as $key ) {
				$value = get_theme_mod( $key );
				icl_register_string( 'shoptimizer', $value, $value );
			}
		}
	}
	add_action( 'init', 'register_shoptimizer_translatable_strings' );
}

/**
 * Filter theme mod values to return translated strings
 *
 * This function is hooked to theme_mod_{field_name} filters and
 * returns the translated version of the string when a translation
 * plugin is active and we're on the frontend.
 *
 * @param string $value The original value from the customizer
 * @return string The translated value or original if no translation available
 */
if ( ! function_exists( 'get_shoptimizer_translated_string' ) ) {
	function get_shoptimizer_translated_string( $value ) {
		if ( ! is_admin() ) {
			if ( function_exists( 'pll__' ) ) {
				// Polylang support - most efficient method
				return pll__( $value );
			} elseif ( has_filter( 'wpml_translate_single_string' ) ) {
				// WPML support (newer API) - recommended for WPML users
				return apply_filters( 'wpml_translate_single_string', $value, 'shoptimizer', $value );
			} elseif ( function_exists( 'icl_translate' ) ) {
				// Legacy WPML support (deprecated but still functional)
				return icl_translate( 'shoptimizer', $value );
			}
		}
		return $value;
	}
}

/**
 * Apply translation filters to all translatable fields
 *
 * This loop hooks the translation function to each translatable field
 * so that get_theme_mod() calls return translated versions automatically.
 */
foreach ( $shoptimizer_translatable_fields as $key ) {
	add_filter( 'theme_mod_' . $key, 'get_shoptimizer_translated_string', 10, 1 );
}
