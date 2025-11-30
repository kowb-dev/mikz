<?php
/**
 * Blog and Post related enhancements
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue styles for articles
add_action( 'wp_enqueue_scripts', 'mkx_enqueue_article_styles' );
function mkx_enqueue_article_styles() {
    // Load these styles only on blog-related pages, not on WooCommerce archives.
    if ( is_home() || is_singular( 'post' ) || is_category() || is_tag() || is_author() || is_date() ) {
        wp_enqueue_style(
            'mkx-article-styles',
            get_stylesheet_directory_uri() . '/assets/css/mkx-article-styles.css',
            array(),
            '1.0.0'
        );
    }
}
