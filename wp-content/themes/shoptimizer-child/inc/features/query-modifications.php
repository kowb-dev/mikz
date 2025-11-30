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
 * Exclude products from the 'Misc' category from frontend queries.
 *
 * Hides products from a specific category on the shop, category pages,
 * and product search results.
 *
 * @param WP_Query $query The main query object.
 */
function shoptimizer_child_hide_misc_category_products( $query ) {
    // Only run on the frontend, for the main query and not on single post views.
    if ( is_admin() || ! $query->is_main_query() || is_singular() ) {
        return;
    }

    // Determine if the current query is for products.
    $is_product_query = $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) );
    
    // Check if it's a product search.
    $is_product_search = false;
    if ($query->is_search()) {
        if (isset($query->query_vars['post_type']) && ($query->query_vars['post_type'] === 'product' || (is_array($query->query_vars['post_type']) && in_array('product', $query->query_vars['post_type'])))) {
            $is_product_search = true;
        } elseif (!isset($query->query_vars['post_type'])) {
            // This handles the default WordPress search which includes all post types.
            $is_product_search = true;
        }
    }


    if ( $is_product_query || $is_product_search ) {
        
        $tax_query = $query->get( 'tax_query' );
        
        if ( ! is_array( $tax_query ) ) {
            $tax_query = array(
                'relation' => 'AND',
            );
        }
        
        // Add a tax_query to exclude the 'misc' category
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => 'misc', // The slug of the category to hide.
            'operator' => 'NOT IN',
        );

        $query->set( 'tax_query', $tax_query );

        // If it was a default search, we should now specify post_type to avoid showing posts from the 'misc' category.
        if ($is_product_search && !isset($query->query_vars['post_type'])) {
            $query->set('post_type', 'product');
        }
    }
}
add_action( 'pre_get_posts', 'shoptimizer_child_hide_misc_category_products' );
