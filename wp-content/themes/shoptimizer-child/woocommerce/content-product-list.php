<?php
/**
 * The template for displaying product content in list view.
 *
 * @package Shoptimizer_Child
 * @version 1.6.0
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
                    <?php shoptimizer_child_template_loop_price(); ?>
                </div>
                <div class="mkz-product-list-item__stock">
                    <?php echo wc_get_stock_html( $product ); ?>
                </div>
            </div>

            <div class="mkz-product-list-item__actions">
                <?php woocommerce_template_loop_add_to_cart(); ?>
            </div>
        </div>
        
        <div class="mkz-product-list-item__mobile-actions">
            <div class="mkz-product-list-item__mobile-price">
                <?php shoptimizer_child_template_loop_price(); ?>
            </div>
            <div class="mkz-product-list-item__mobile-quantity-cart">
                <div class="quantity">
                    <button type="button" class="minus button">-</button>
                    <input type="number" class="input-text qty text" step="1" min="1" max="<?php echo $product->get_max_purchase_quantity(); ?>" name="quantity" value="1" title="<?php esc_attr_e( 'Количество', 'shoptimizer-child' ); ?>" size="4" inputmode="numeric">
                    <button type="button" class="plus button">+</button>
                </div>
                <a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" 
                   data-quantity="1" 
                   class="button product_type_simple add_to_cart_button ajax_add_to_cart mkz-mobile-cart-btn" 
                   data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" 
                   data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>" 
                   aria-label="<?php esc_attr_e( 'В корзину', 'shoptimizer-child' ); ?>" 
                   title="<?php esc_attr_e( 'В корзину', 'shoptimizer-child' ); ?>">
                    <i class="ph ph-shopping-cart-simple"></i>
                </a>
            </div>
        </div>

        <div class="mkz-product-list-item__meta">
            <?php if ( $product->get_sku() ) : ?>
                <span class="sku-wrapper"><?php esc_html_e( 'SKU:', 'woocommerce' ); ?> <span class="sku"><?php echo $product->get_sku(); ?></span></span>
            <?php endif; ?>
            <?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'woocommerce' ) . ' ', '</span>' ); ?>
        </div>
    </div>

    <div class="mkz-product-list-item__action-buttons">
        <?php
        if ( class_exists( 'MKX_Wishlist' ) ) {
            MKX_Wishlist()->add_button_loop();
        }
        if ( class_exists( 'MKX_Compare' ) ) {
            MKX_Compare()->add_button_loop();
        }
        ?>
    </div>

</li>
