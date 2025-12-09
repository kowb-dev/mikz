<?php
/**
 * The sidebar containing the main widget area.
 *
 * This file overrides the parent theme's sidebar to display a custom
 * sidebar on the shop page and the default sidebar on other pages.
 *
 * @package shoptimizer-child
 * @version 1.0.1
 * @author  KB, https://kowb.ru
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Determine which sidebar to display based on the page.
if ( is_shop() && is_active_sidebar( 'shop-sidebar' ) ) {
	$sidebar_id = 'shop-sidebar';
} elseif ( is_product_category() && is_active_sidebar( 'sidebar-1' ) ) {
	$sidebar_id = 'sidebar-1';
} elseif ( is_active_sidebar( 'sidebar-1' ) ) {
    // Fallback for other pages that might have a sidebar.
	$sidebar_id = 'sidebar-1';
} else {
	// If no relevant sidebar is active, do nothing.
	return;
}

// Check if the layout has a sidebar enabled in theme options.
$shoptimizer_layout_woocommerce_sidebar = shoptimizer_get_option( 'shoptimizer_layout_woocommerce_sidebar' );

if ( 'no-woocommerce-sidebar' !== $shoptimizer_layout_woocommerce_sidebar ) : ?>
<div class="secondary-wrapper">
    <div id="secondary" class="widget-area" role="complementary">
        <?php dynamic_sidebar( $sidebar_id ); ?>
    </div><!-- #secondary -->
    <button class="filters close-drawer" aria-label="Close filters">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
    </button>
</div>
<?php endif; ?>
