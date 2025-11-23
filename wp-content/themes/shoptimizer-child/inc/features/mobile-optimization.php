<?php
/**
 * Mobile Optimization Features
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
 * Добавление мета-тегов для мобильных устройств
 */
add_action( 'wp_head', 'shoptimizer_child_mobile_meta_tags' );
function shoptimizer_child_mobile_meta_tags() {
    echo '<meta name="theme-color" content="#28B0EA">' . "\n";
    echo '<meta name="msapplication-navbutton-color" content="#28B0EA">' . "\n";
    echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
    echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
}
