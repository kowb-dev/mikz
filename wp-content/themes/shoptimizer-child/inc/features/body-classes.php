<?php
/**
 * Body Classes Modifications
 *
 * @package Shoptimizer Child
 * @version 1.0.2
 * @author KW
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Добавление классов к body
 */
add_filter( 'body_class', 'shoptimizer_child_body_classes' );
function shoptimizer_child_body_classes( $classes ) {
    // Добавляем класс для мобильных устройств
    if ( wp_is_mobile() ) {
        $classes[] = 'mkx-mobile-device';
    }

    // Добавляем класс если включена мобильная навигация
    if ( ! is_admin() && wp_is_mobile() ) {
        $classes[] = 'mkx-mobile-nav-active';
    }

    // Добавляем класс для WooCommerce страниц
    if ( class_exists( 'WooCommerce' ) && ( is_shop() || is_product_category() || is_product_tag() || is_product() ) ) {
        $classes[] = 'mkx-woocommerce-page';
    }

    // Добавляем класс для главной страницы с Hero-секцией
    if ( is_front_page() ) {
        $classes[] = 'mkx-has-hero-section';
    }

    return $classes;
}
