<?php
/**
 * Custom Notification System
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function mkx_enqueue_notification_assets() {
	wp_enqueue_style(
		'mkx-notifications',
		get_stylesheet_directory_uri() . '/assets/css/notifications.css',
		array(),
		'1.0.0'
	);

	wp_enqueue_script(
		'mkx-notifications',
		get_stylesheet_directory_uri() . '/assets/js/notifications.js',
		array( 'jquery' ),
		'1.0.0',
		true
	);

	wp_localize_script( 'mkx-notifications', 'mkxNotifications', array(
		'addedToCart'    => __( 'Товар добавлен в корзину', 'shoptimizer-child' ),
		'addedToWishlist' => __( 'Товар добавлен в избранное', 'shoptimizer-child' ),
		'addedToCompare' => __( 'Товар добавлен к сравнению', 'shoptimizer-child' ),
		'removedFromWishlist' => __( 'Товар удален из избранного', 'shoptimizer-child' ),
		'removedFromCompare' => __( 'Товар удален из сравнения', 'shoptimizer-child' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'mkx_enqueue_notification_assets' );

function mkx_add_notification_container() {
	echo '<div id="mkx-notification-container" aria-live="polite" aria-atomic="true"></div>';
}
add_action( 'wp_footer', 'mkx_add_notification_container' );
