<?php
/**
 * Loop Add to Cart with Quantity - With Buttons
 *
 * @package Shoptimizer_Child
 * @version 1.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

// For variable, grouped, etc. products, just show the default button without causing a loop.
if ( ! $product->is_type('simple') || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
    echo apply_filters(
        'woocommerce_loop_add_to_cart_link',
        sprintf(
            '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button product_type_%s" aria-label="%s" title="%s"><i class="ph ph-shopping-cart-simple"></i></a>',
            esc_url( $product->add_to_cart_url() ),
            esc_attr( $product->get_id() ),
            esc_attr( $product->get_sku() ),
            esc_attr( $product->get_type() ),
            esc_attr( $product->add_to_cart_text() ),
            esc_attr( $product->add_to_cart_text() )
        ),
        $product
    );
    return;
}

// For simple, purchasable products, show quantity + button
?>
<div class="mkx-product-actions">
    <div class="quantity">
        <button type="button" class="minus button">-</button>
        <input
            type="number"
            id="quantity_<?php echo esc_attr( $product->get_id() ); ?>"
            class="input-text qty text"
            step="<?php echo esc_attr( apply_filters( 'woocommerce_quantity_input_step', '1', $product ) ); ?>"
            min="<?php echo esc_attr( apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ) ); ?>"
            max="<?php echo esc_attr( apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ) ); ?>"
            name="quantity"
            value="<?php echo esc_attr( $product->get_min_purchase_quantity() ); ?>"
            title="<?php echo esc_attr_x( 'Qty', 'Product quantity input tooltip', 'woocommerce' ); ?>"
            size="4"
            inputmode="numeric" />
        <button type="button" class="plus button">+</button>
    </div>

    <?php
	// Display the add to cart button with AJAX attributes
	echo apply_filters(
		'woocommerce_loop_add_to_cart_link',
		sprintf(
			'<a href="%s" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="%s" data-product_sku="%s" aria-label="%s" title="%s"><i class="ph ph-shopping-cart-simple"></i></a>',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( $product->get_id() ),
			esc_attr( $product->get_sku() ),
			esc_attr( $product->add_to_cart_text() ),
			esc_attr( $product->add_to_cart_text() )
		),
		$product
	);
    ?>
</div>
