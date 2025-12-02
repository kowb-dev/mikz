<?php
/**
 * Custom Query Modifications
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Modify the main query for specific archives to control posts per page.
 *
 * @param WP_Query $query The main query object.
 */
function kb_modify_archive_queries( $query ) {
    // Check if it's the main query, on the frontend, and a blog category archive.
    if ( ! is_admin() && $query->is_main_query() && $query->is_category() ) {
        // Set the number of posts per page to 20.
        $query->set( 'posts_per_page', 20 );
    }
}
add_action( 'pre_get_posts', 'kb_modify_archive_queries' );

/**
 * Exclude products from 'Misc' category from search results (strict version)
 */
function exclude_misc_category_from_search_strict( $query ) {
    if ( ! is_admin() && $query->is_search() && $query->is_main_query() ) {
        
        $misc_category_id = 253;
        
        // Получаем все ID товаров из категории Misc
        $misc_products = get_posts( array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $misc_category_id,
                ),
            ),
        ) );
        
        // Исключаем эти товары из поиска
        if ( ! empty( $misc_products ) ) {
            $query->set( 'post__not_in', $misc_products );
        }
    }
}
add_action( 'pre_get_posts', 'exclude_misc_category_from_search_strict' );
