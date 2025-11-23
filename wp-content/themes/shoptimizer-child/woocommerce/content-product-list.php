<?php
/**
 * The template for displaying product content in list view.
 *
 * @package Shoptimizer_Child
 * @version 1.5.0
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
<li <?php wc_product_class( 'mkz-product-list-item', $product ); ?>>

    <div class="mkz-product-list-item__image">
        <a href="<?php echo esc_url( get_the_permalink() ); ?>">
            <?php woocommerce_template_loop_product_thumbnail(); ?>
        </a>
    </div>

    <div class="mkz-product-list-item__info">
        <a href="<?php echo esc_url( get_the_permalink() ); ?>" class="mkz-product-list-item__title-link">
            <?php woocommerce_template_loop_product_title(); ?>
        </a>
        <div class="mkz-product-list-item__excerpt">
            <?php echo get_the_excerpt(); ?>
        </div>
    </div>

    <div class="mkz-product-list-item__price-actions-wrapper">
        <div class="mkz-product-list-item__price-and-actions">
            <div class="mkz-product-list-item__price-stock-wrapper">
                <div class="mkz-product-list-item__price">
                    <?php woocommerce_template_loop_price(); ?>
                </div>
                <div class="mkz-product-list-item__stock">
                    <?php echo wc_get_stock_html( $product ); ?>
                </div>
            </div>

            <div class="mkz-product-list-item__actions">
                <?php woocommerce_template_loop_add_to_cart(); ?>
            </div>
        </div>

        <div class="mkz-product-list-item__meta">
            <?php if ( $product->get_sku() ) : ?>
                <span class="sku-wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo $product->get_sku(); ?></span></span>
            <?php endif; ?>
            <?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' ); ?>
        </div>
    </div>

    <div class="mkz-product-list-item__yith-buttons">
        <div class="mkz-product-list-item__wishlist">
            <?php
            if ( class_exists( 'YITH_WCWL_Shortcode' ) ) {
                echo do_shortcode( '[yith_wcwl_add_to_wishlist]' );
            }
            ?>
        </div>
        <div class="mkz-product-list-item__compare">
            <?php echo do_shortcode('[yith_compare_button]'); ?>
        </div>
    </div>

</li>
