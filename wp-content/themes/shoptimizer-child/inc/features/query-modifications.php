<?php
/**
 * Custom Query Modifications
 *
 * @package Shoptimizer Child
 * @version 1.0.1
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
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

function exclude_misc_category_from_search_strict( $query ) {
    if ( ! is_admin() && $query->is_search() && $query->is_main_query() ) {
        $misc_category_id = 253;
        
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
        
        if ( ! empty( $misc_products ) ) {
            $query->set( 'post__not_in', $misc_products );
        }
    }
}
add_action( 'pre_get_posts', 'exclude_misc_category_from_search_strict' );

function mkx_exclude_misc_from_woocommerce_blocks( $query_args, $attributes, $type ) {
    $misc_category_id = 253;
    
    if ( ! isset( $query_args['tax_query'] ) ) {
        $query_args['tax_query'] = array();
    }
    
    $query_args['tax_query'][] = array(
        'taxonomy' => 'product_cat',
        'field'    => 'term_id',
        'terms'    => array( $misc_category_id ),
        'operator' => 'NOT IN',
    );
    
    return $query_args;
}
add_filter( 'woocommerce_shortcode_products_query', 'mkx_exclude_misc_from_woocommerce_blocks', 10, 3 );
add_filter( 'woocommerce_blocks_product_grid_query', 'mkx_exclude_misc_from_woocommerce_blocks', 10, 3 );
