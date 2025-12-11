<?php
/**
 * 404 page assets and helpers
 *
 * @package Shoptimizer Child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue 404 page styles and scripts.
 */
function mkx_enqueue_404_assets() {
	if ( ! is_404() ) {
		return;
	}

	$theme_version   = wp_get_theme()->get( 'Version' );
	$child_dir       = get_stylesheet_directory();
	$child_uri       = get_stylesheet_directory_uri();
	$css_rel_path    = '/assets/css/404.css';
	$js_rel_path     = '/assets/js/404.js';
	$css_file        = $child_dir . $css_rel_path;
	$js_file         = $child_dir . $js_rel_path;
	$css_version     = file_exists( $css_file ) ? filemtime( $css_file ) : $theme_version;
	$js_version      = file_exists( $js_file ) ? filemtime( $js_file ) : $theme_version;

	wp_enqueue_style(
		'mkx-404',
		$child_uri . $css_rel_path,
		array(),
		$css_version
	);

	// Ensure Phosphor icons are available (used in template buttons).
	if ( ! wp_style_is( 'phosphor-icons-regular', 'enqueued' ) ) {
		wp_enqueue_style(
			'phosphor-icons-regular',
			'https://unpkg.com/@phosphor-icons/web@2.1.2/src/regular/style.css',
			array(),
			'2.1.2'
		);
	}

	wp_enqueue_script(
		'mkx-404',
		$child_uri . $js_rel_path,
		array(),
		$js_version,
		true
	);

	wp_localize_script(
		'mkx-404',
		'mkx404Data',
		array(
			'homeUrl'           => esc_url( home_url( '/' ) ),
			'shopUrl'           => esc_url( home_url( '/shop/' ) ),
			'currentUrl'        => esc_url( $_SERVER['REQUEST_URI'] ?? '' ),
			'searchPlaceholder' => __( 'Например: дисплей iPhone 13', 'shoptimizer-child' ),
			'nonce'             => wp_create_nonce( 'mkx_404_nonce' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'mkx_enqueue_404_assets' );

/**
 * Add custom body class for 404 page.
 *
 * @param array $classes Body classes.
 * @return array
 */
function mkx_404_body_class( $classes ) {
	if ( is_404() ) {
		$classes[] = 'mkx-is-404';
		$classes[] = 'mkx-error-page';
	}
	return $classes;
}
add_filter( 'body_class', 'mkx_404_body_class' );




