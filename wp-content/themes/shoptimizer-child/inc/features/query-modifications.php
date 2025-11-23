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
