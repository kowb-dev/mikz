<?php
/**
 * WordPress Cleanup and Optimization
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
 * Удаление неиспользуемых WordPress функций для оптимизации
 */
add_action( 'init', 'shoptimizer_child_cleanup' );
function shoptimizer_child_cleanup() {
    // Удаляем эмодзи
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );

    // Удаляем ненужные RSS ссылки
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );

    // Удаляем генератор WordPress
    remove_action( 'wp_head', 'wp_generator' );
}

/**
 * Оптимизация загрузки ресурсов
 */
add_action( 'wp_head', 'shoptimizer_child_optimize_scripts' );
function shoptimizer_child_optimize_scripts() {
    // DNS prefetch для внешних ресурсов
    echo '<link rel="dns-prefetch" href="//unpkg.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//images.unsplash.com">' . "\n";
}

/**
 * Добавление критического CSS inline
 */
add_action( 'wp_head', 'shoptimizer_child_critical_css', 1 );
function shoptimizer_child_critical_css() {
    // Критические стили для быстрой загрузки
    ?>
    <style id="mkx-critical-css">
        .mkx-site-header { background: #fff; position: sticky; top: 0; z-index: 1000; }
        /*.mkx-mobile-bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: #fff; z-index: 1030; }*/
        .mkx-container { max-width: min(103.2rem, 100vw - 2rem); margin: 0 auto; padding: 0 clamp(1rem, 3vw, 2rem); }
        .mkx-megamenu-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 900; }
        .mkx-hero-section { padding: clamp(1rem, 2vw, 1.5rem) 0;}
        .mkx-carousel-slide { position: absolute; opacity: 0; transition: opacity 0.3s ease; }
        .mkx-carousel-slide--active { opacity: 1; }
        /*body.mkx-mobile-nav-active { padding-bottom: 4rem; }*/
        @media (max-width: 768px) {
            .mkx-header-top-bar, .mkx-primary-menu { display: none; }
            .mkx-mobile-top-bar, .mkx-mobile-search-bar, .mkx-mobile-bottom-nav { display: block; }
        }
    </style>
    <?php
}
