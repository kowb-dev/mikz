<?php
/**
 * The template for displaying product content in grid view.
 *
 * @package Shoptimizer_Child
 * @version 1.0.0
 * @author  KB
 * @link    https://kowb.ru
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
    <?php
    /**
     * Hook: woocommerce_before_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_open - 10
     */
    do_action( 'woocommerce_before_shop_loop_item' );

    /**
     * Hook: woocommerce_before_shop_loop_item_title.
     *
     * @hooked woocommerce_show_product_loop_sale_flash - 10
     * @hooked woocommerce_template_loop_product_thumbnail - 10
     */
    do_action( 'woocommerce_before_shop_loop_item_title' );

    /**
     * Hook: woocommerce_shop_loop_item_title.
     *
     * @hooked woocommerce_template_loop_product_title - 10
     */
    do_action( 'woocommerce_shop_loop_item_title' );

    $mkx_sku = $product->get_sku();
    $mkx_stock = wc_get_stock_html( $product );

    if ( $mkx_sku || $mkx_stock ) :
        ?>
        <div class="mkx-card-meta">
            <?php if ( $mkx_sku ) : ?>
                <span class="mkx-card-sku"><?php esc_html_e( 'Артикул:', 'shoptimizer-child' ); ?> <span class="sku"><?php echo esc_html( $mkx_sku ); ?></span></span>
            <?php endif; ?>
            <?php if ( $mkx_stock ) : ?>
                <span class="mkx-card-stock"><?php echo wp_kses_post( $mkx_stock ); ?></span>
            <?php endif; ?>
        </div>
        <?php
    endif;

    /**
     * Hook: woocommerce_after_shop_loop_item_title.
     *
     * @hooked woocommerce_template_loop_rating - 5
     * @hooked woocommerce_template_loop_price - 10
     */
    do_action( 'woocommerce_after_shop_loop_item_title' );

    /**
     * Hook: woocommerce_after_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_close - 5
     * @hooked woocommerce_template_loop_add_to_cart - 10
     */
    do_action( 'woocommerce_after_shop_loop_item' );
    ?>
</li>
