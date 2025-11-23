<?php
/**
 * Enqueue styles for specific pages
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue styles for the 'How to Order' page
add_action('wp_enqueue_scripts', 'mkz_enqueue_order_guide_styles');
function mkz_enqueue_order_guide_styles() {
	if (is_page('how-to-order') || is_page('kak-oformit-zakaz')) {
		wp_enqueue_style(
			'mkz-order-guide-styles',
			get_stylesheet_directory_uri() . '/inc/order-guide-styles.css',
			array(),
			'1.0.0'
		);
	}
}

// Enqueue styles for the 'Payment and Delivery' page
add_action('wp_enqueue_scripts', 'mikz_enqueue_payment_delivery_styles');
function mikz_enqueue_payment_delivery_styles() {
	if (is_page('payment-delivery') || is_page('oplata-i-dostavka')) {
		wp_enqueue_style(
			'mikz-payment-delivery',
			get_stylesheet_directory_uri() . '/inc/mikz-payment-delivery.css',
			array(),
			'1.0.0'
		);

		// Enqueue Phosphor icons
		wp_enqueue_style(
			'phosphor-icons',
			'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css',
			array(),
			'2.1.1'
		);
	}
}

// Enqueue styles for the contacts page
add_action('wp_enqueue_scripts', 'mkz_contacts_page_styles');
function mkz_contacts_page_styles() {
	if (is_page('kontakty') || is_page('contacts')) {
		wp_enqueue_style(
			'mkz-contacts-page',
			get_stylesheet_directory_uri() . '/inc/contacts-page.css',
			array('shoptimizer-style'),
			'1.0.1',
			'all'
		);

		// Enqueue IMask.js from CDN
		wp_enqueue_script(
			'imask-js',
			'https://unpkg.com/imask',
			array(),
			'7.1.3',
			true
		);

		// Enqueue our custom contact form script
		wp_enqueue_script(
			'mkz-contact-form-js',
			get_stylesheet_directory_uri() . '/assets/js/contact-form.js',
			array( 'imask-js' ), // Make it dependent on imask
			'1.0.0',
			true
		);
	}
}
