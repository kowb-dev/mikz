<?php
/**
 * Sidebar Registration
 *
 * @package Shoptimizer Child
 * @version 1.0.1
 * @author KB
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom sidebars.
 */
function mkx_register_custom_sidebars() {
    // Сайдбар для страницы магазина
    register_sidebar(
        array(
            'name'          => esc_html__( 'Shop Page Sidebar', 'shoptimizer-child' ),
            'id'            => 'shop-sidebar',
            'description'   => esc_html__( 'Widgets in this area will be shown on the main shop page.', 'shoptimizer-child' ),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<span class="gamma widget-title">',
            'after_title'   => '</span>',
        )
    );
}
add_action( 'widgets_init', 'mkx_register_custom_sidebars' );
