<?php
/**
 * Single product summary
 *
 * @package shoptimizer-child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Remove all default and theme actions from woocommerce_single_product_summary.
 *
 * Hooked to 'wp_loaded' to ensure it runs after the parent theme and plugins have registered their hooks.
 */
function kb_remove_all_single_product_summary_actions() {
    // Default WooCommerce hooks
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

    // Shoptimizer theme hooks
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_prev_next_product', 0 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_call_back_trigger', 79 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_call_back_modal', 80 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_product_content_wrapper_end', 120 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_product_custom_content', 45 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 20 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_pdp_short_description_position', 9 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 50 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_show_product_sale_flash', 3 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_pdp_modal_wrapper_open', 36 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_pdp_modal_wrapper_close', 38 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_pdp_cross_sells_carousel', 90 );
    remove_action( 'woocommerce_single_product_summary', 'shoptimizer_change_displayed_sale_price_html', 11 );
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_template_single_meta', 15 );
}
add_action( 'wp_loaded', 'kb_remove_all_single_product_summary_actions', 999 );


/**
 * Custom single product summary
 */
function kb_custom_single_product_summary() {
    global $product;

    // Product Meta (SKU, Categories, Brand)
    ?>
    <div class="product_meta">
        <?php if ( wc_product_sku_enabled() && ( $product->get_sku() || $product->is_type( 'variable' ) ) ) : ?>
            <span class="sku_wrapper"><?php esc_html_e( 'Артикул:', 'shoptimizer' ); ?> <span class="sku"><?php echo ( $sku = $product->get_sku() ) ? $sku : esc_html__( 'N/A', 'shoptimizer' ); ?></span></span>
        <?php endif; ?>

        <?php echo wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Категория:', 'Categories:', count( $product->get_category_ids() ), 'shoptimizer' ) . ' ', '</span>' ); ?>

        <?php
        $brands = get_the_terms( $product->get_id(), 'product_brand' );
        if ( $brands && ! is_wp_error( $brands ) ) {
            $brand_links = array();
            foreach ( $brands as $brand ) {
                $brand_links[] = '<a href="' . esc_url( get_term_link( $brand ) ) . '" rel="tag">' . esc_html( $brand->name ) . '</a>';
            }
            echo '<span class="posted_in">' . _n( 'Бренд:', 'Brands:', count( $brands ), 'shoptimizer' ) . ' ' . implode( ', ', $brand_links ) . '</span>';
        }
        ?>
    </div>
    <?php

    // Stock
    $availability = $product->get_availability();
    if ( ! empty( $availability['availability'] ) ) {
        echo '<p class="stock ' . esc_attr( $availability['class'] ) . '">✓ ' . esc_html( $availability['availability'] ) . '</p>';
    }

    // Prices
    $price_html = $product->get_price_html();
    ?>
    <div class="custom-price-container">
        <div class="price-row retail-price-row">
            <div class="price-label"><?php esc_html_e( 'РОЗН.', 'shoptimizer' ); ?></div>
            <div class="price-value"><?php echo $price_html; ?></div>
        </div>
        <?php
        $wholesale_price = get_post_meta( $product->get_id(), '_wholesale_price', true );
        if ( ! empty( $wholesale_price ) && is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $user_roles = $current_user->roles;

            if ( in_array( 'wholesale_customer', $user_roles, true ) || in_array( 'administrator', $user_roles, true ) ) {
                $regular_price = $product->get_regular_price();
                $discount = 0;
                if ( $regular_price > 0 ) {
                    $discount = round( ( ( $regular_price - $wholesale_price ) / $regular_price ) * 100 );
                }

                $min_quantity = get_post_meta( $product->get_id(), '_wholesale_min_quantity', true );
                ?>
                <div class="price-row wholesale-price-row">
                    <div class="price-label wholesale-label"><?php esc_html_e( 'ОПТ', 'shoptimizer' ); ?></div>
                    <div class="price-value wholesale-value"><?php echo wc_price( $wholesale_price ); ?> (<?php printf( esc_html__( 'скидка -%s%%', 'shoptimizer' ), $discount ); ?>)</div>
                </div>
                <?php if ( ! empty( $min_quantity ) ) : ?>
                <div class="min-quantity-row">
                    <div class="min-quantity-label"><?php esc_html_e( 'Мин. партия:', 'shoptimizer' ); ?></div>
                    <div class="min-quantity-value"><?php printf( esc_html__( 'от %s шт.', 'shoptimizer' ), $min_quantity ); ?></div>
                </div>
                <?php endif; ?>
                <?php
            }
        }
        ?>
    </div>
    <?php

    // Add to cart form
    if ( $product->is_in_stock() ) : ?>
        <?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>
        <form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
            <div class="cart-main-actions">
                <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
                <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
                <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
            </div>
        </form>
        <?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
    <?php endif;
}
add_action( 'woocommerce_single_product_summary', 'kb_custom_single_product_summary', 10 );

/**
 * Add Wishlist and Compare buttons after the add to cart form.
 */
function kb_add_product_actions_after_add_to_cart() {
    global $product;
    $product_id = $product->get_id();
    ?>
    <div class="product-actions">
        <?php if ( defined( 'YITH_WCWL' ) ) : ?>
            <div class="action-item">
                <?php
                    $wishlist_url = YITH_WCWL()->get_add_to_wishlist_url( $product_id );
                    echo '<a href="' . esc_url( $wishlist_url ) . '" class="add_to_wishlist single_add_to_wishlist" data-product-id="' . esc_attr( $product_id ) . '" rel="nofollow">';
                    echo '<i class="ph ph-heart"></i>';
                    echo '</a>';
                ?>
            </div>
        <?php endif; ?>

        <?php if ( class_exists( 'YITH_Woocompare' ) ) :
            $compare_url = add_query_arg( array(
                'action' => 'yith-woocompare-add-product',
                'id' => $product_id
            ), site_url() );
        ?>
            <div class="action-item">
                <a href="<?php echo esc_url( $compare_url ); ?>" class="compare" data-product_id="<?php echo esc_attr( $product_id ); ?>" rel="nofollow">
                    <i class="ph ph-chart-bar"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
add_action( 'woocommerce_after_add_to_cart_form', 'kb_add_product_actions_after_add_to_cart', 10 );
