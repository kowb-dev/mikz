<?php
/**
 * Navigation Menus Configuration
 *
 * @package Shoptimizer Child
 * @version 1.0.3
 * @author KB
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Регистрация областей меню
 */
add_action( 'init', 'shoptimizer_child_register_menus' );
function shoptimizer_child_register_menus() {
    register_nav_menus( array(
        'mkx_mobile_bottom_nav' => __( 'Нижняя мобильная навигация (MKX)', 'shoptimizer-child' ),
        'mobile-bottom'     => __( 'Мобильная нижняя навигация', 'shoptimizer-child' ),
        'catalog-mega'      => __( 'Мега-меню каталога', 'shoptimizer-child' ),
        'horizontal_menu'   => __( 'Горизонтальное меню', 'shoptimizer-child' ),
        'mobile_main_menu'  => __( 'Мобильное меню (аккордеон)', 'shoptimizer-child' ),
        'footer_additional' => esc_html__( 'Подвал - Дополнительно', 'shoptimizer-child' ),
        'footer_policies'   => esc_html__( 'Подвал - Политики', 'shoptimizer-child' ),
    ) );
}

/**
 * Добавляет CSS классы к ссылкам в меню подвала
 *
 * @param array $atts Атрибуты ссылки.
 * @param WP_Post $item Объект элемента меню.
 * @param stdClass $args Аргументы wp_nav_menu().
 * @return array
 */
function mkx_footer_menu_link_class( $atts, $item, $args ) {
    if ( ! empty( $args->theme_location ) ) {
        if ( $args->theme_location === 'footer_additional' ) {
            $atts['class'] = 'mkx-footer-additional-link';
        }
        if ( $args->theme_location === 'footer_policies' ) {
            $atts['class'] = 'mkx-footer-policy-link';
        }
    }
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'mkx_footer_menu_link_class', 10, 3 );