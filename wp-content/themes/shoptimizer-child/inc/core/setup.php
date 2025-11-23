<?php
/**
 * Theme Setup Functions
 *
 * @package Shoptimizer Child
 * @version 1.0.3
 * @author KW
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Настройка темы
 * 
 * Загружаем с приоритетом 10 чтобы переводы загружались после init
 */
add_action( 'after_setup_theme', 'shoptimizer_child_setup', 10 );
function shoptimizer_child_setup() {
    // Поддержка пользовательских логотипов
    add_theme_support( 'custom-logo', array(
        'height'      => 100,
        'width'       => 300,
        'flex-height' => true,
        'flex-width'  => true,
        'header-text' => array( 'site-title', 'site-description' ),
    ) );

    // Поддержка HTML5
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style',
    ) );
}

/**
 * Загрузка переводов темы
 * Выполняется после init чтобы избежать предупреждений
 */
add_action( 'init', 'shoptimizer_child_load_textdomain', 1 );
function shoptimizer_child_load_textdomain() {
    load_theme_textdomain( 'shoptimizer-child', get_stylesheet_directory() . '/languages' );
}

/**
 * Буферизация вывода
 */
add_action( 'init', 'shoptimizer_child_buffer_start', 0 );
function shoptimizer_child_buffer_start() {
    if ( ! is_admin() ) {
        ob_start();
    }
}

/**
 * Disable Contact Form 7's auto-paragraph functionality
 * This prevents CF7 from wrapping form fields in <p> tags, allowing for custom styling.
 */
add_filter( 'wpcf7_autop_or_not', '__return_false' );
