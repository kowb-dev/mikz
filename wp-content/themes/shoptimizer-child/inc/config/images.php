<?php
/**
 * Image Sizes Configuration
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
 * Добавление размеров изображений
 */
add_action( 'after_setup_theme', 'shoptimizer_child_image_sizes' );
function shoptimizer_child_image_sizes() {
    // Размер для мобильного логотипа
    add_image_size( 'mobile-logo', 120, 40, true );

    // Размер для товаров в мобильной навигации
    add_image_size( 'mobile-product-thumb', 60, 60, true );

    // Размеры для Hero-карусели
    add_image_size( 'hero-large-slide', 1045, 280, true );
    add_image_size( 'hero-small-slide', 423, 280, true );
}
