<?php
/**
 * The template for displaying product archives, including main category and sub-category views.
 *
 * This template is customized to first display sub-categories in a grid if they exist.
 * If no sub-categories are found, it falls back to the default product loop.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package Shoptimizer_Child
 * @version 1.2.0
 * @author KB
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );
?>
<div class="col-full">
<header class="woocommerce-products-header">
	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
		<h1 class="woocommerce-products-header__title page-title"><?php woocommerce_page_title(); ?></h1>
	<?php endif; ?>

	<?php
	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @hooked woocommerce_taxonomy_archive_description - 10
	 * @hooked woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' );
	?>
</header>

<?php
// Get the current queried object
$current_cat = get_queried_object();
$display_type = '';
$sub_categories = array();

// Check if the queried object is a valid product category term
if ( $current_cat && isset( $current_cat->taxonomy ) && $current_cat->taxonomy === 'product_cat' && ! is_wp_error( $current_cat ) ) {
    $display_type = get_term_meta( $current_cat->term_id, 'display_type', true );

    // Get sub-categories, but only those that are not empty
    $sub_categories = get_terms( array(
        'taxonomy'   => 'product_cat',
        'parent'     => $current_cat->term_id,
        'hide_empty' => true, // Hide categories with 0 products
    ) );
}

// If display type is 'subcategories' or it's not set and there are subcategories, show them.
if ( ! empty( $sub_categories ) && ( $display_type == 'subcategories' || $display_type == 'both' || empty( $display_type ) ) ) {

    echo '<div class="mkx-subcategory-grid">';

    foreach ( $sub_categories as $sub_category ) {
        $link = get_term_link( $sub_category );
        
        // Get an icon for the subcategory
        $icon_class = function_exists('mkx_get_category_icon') ? mkx_get_category_icon($sub_category->slug) : 'ph-wrench';

        echo '<a href="' . esc_url( $link ) . '" class="mkx-subcategory-card">';
        echo '<div class="mkx-subcategory-card__icon"><i class="ph ' . esc_attr($icon_class) . '"></i></div>';
        echo '<div class="mkx-subcategory-card__content">';
        echo '<h2 class="mkx-subcategory-card__title">' . esc_html( $sub_category->name ) . '</h2>';
        // The product count has been removed as per the request.
        echo '</div>';
        echo '</a>';
    }

    echo '</div>';

}

// If display type is 'products' or 'both', or if there are no subcategories, show products.
if ( $display_type == 'products' || $display_type == 'both' || empty( $sub_categories ) ) {

    if ( woocommerce_product_loop() ) {

        /**
         * Hook: woocommerce_before_shop_loop.
         *
         * @hooked woocommerce_output_all_notices - 10
         * @hooked woocommerce_result_count - 20
         * @hooked woocommerce_catalog_ordering - 30
         */
        do_action( 'woocommerce_before_shop_loop' );

        woocommerce_product_loop_start();

        if ( wc_get_loop_prop( 'total' ) ) {
            while ( have_posts() ) {
                the_post();

                if ( is_shop() ) {
                    wc_get_template_part( 'content', 'product-list' );
                } else {
                    wc_get_template_part( 'content', 'product-grid' );
                }
            }
        }

        woocommerce_product_loop_end();

        /**
         * Hook: woocommerce_after_shop_loop.
         *
         * @hooked woocommerce_pagination - 10
         */
        do_action( 'woocommerce_after_shop_loop' );

    } else {
        /**
         * Hook: woocommerce_no_products_found.
         *
         * @hooked wc_no_products_found - 10
         */
        do_action( 'woocommerce_no_products_found' );
    }
}
?>
</div>
<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );