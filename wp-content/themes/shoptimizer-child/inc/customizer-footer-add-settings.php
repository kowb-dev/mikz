<?php
/**
 * Add footer settings to the Customizer.
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Add footer settings.
 *
 * @param WP_Customize_Manager $wp_customize The Customizer object.
 */
function shoptimizer_child_footer_customize_register( $wp_customize ) {

	// Add a new section for the footer newsletter.
	$wp_customize->add_section(
		'mkx_footer_newsletter_section',
		array(
			'title'    => __( 'Footer Newsletter', 'shoptimizer-child' ),
			'priority' => 140,
			'panel'    => 'shoptimizer_panel', // Assuming 'shoptimizer_panel' exists.
		)
	);

	// Add setting for the newsletter shortcode.
	$wp_customize->add_setting(
		'mkx_footer_newsletter_shortcode',
		array(
			'default'           => '[contact-form-7 id="cbb552c" title="FollowUs"]',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);

	// Add control for the newsletter shortcode.
	$wp_customize->add_control(
		'mkx_footer_newsletter_shortcode_control',
		array(
			'label'       => __( 'Newsletter Form Shortcode', 'shoptimizer-child' ),
			'description' => __( 'Enter the shortcode for your newsletter subscription form.', 'shoptimizer-child' ),
			'section'     => 'mkx_footer_newsletter_section',
			'settings'    => 'mkx_footer_newsletter_shortcode',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'shoptimizer_child_footer_customize_register' );

/**
 * Enqueue scripts for Customizer preview.
 */
function shoptimizer_child_footer_customize_preview_js() {
	wp_enqueue_script(
		'shoptimizer-child-footer-customizer',
		get_stylesheet_directory_uri() . '/assets/js/customizer-footer.js',
		array( 'customize-preview' ),
		'1.0.0',
		true
	);
}
add_action( 'customize_preview_init', 'shoptimizer_child_footer_customize_preview_js' );
