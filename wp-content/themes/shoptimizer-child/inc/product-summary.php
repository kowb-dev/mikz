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
add_action( 'init', 'kb_remove_all_single_product_summary_actions' );


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
    $availability_html = empty( $availability['availability'] ) ? '' : '<p class="stock ' . esc_attr( $availability['class'] ) . '">✓ ' . esc_html( $availability['availability'] ) . '</p>';
    echo $availability_html;


    // Prices
    // Here we will need to get the wholesale price. I'll assume it's stored in a meta field for now.
    // I need to check how wholesale prices are implemented. I'll search for "wholesale" in the project.
    // For now, I'll use a placeholder.
    $regular_price = $product->get_regular_price();
    $sale_price = $product->get_sale_price();
    $price_html = $product->get_price_html();

    ?>
    <div class="custom-price-container">
        <div class="price-row retail-price-row">
            <div class="price-label"><?php esc_html_e( 'РОЗН.', 'shoptimizer' ); ?></div>
            <div class="price-value"><?php echo $price_html; ?></div>
        </div>
        <?php
        // Placeholder for wholesale price
        $wholesale_price = get_post_meta( $product->get_id(), '_wholesale_price', true );
        if ( ! empty( $wholesale_price ) && is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $user_roles = $current_user->roles;

            // Check if the user has a role that can see the wholesale price
            if ( in_array( 'wholesale_customer', $user_roles, true ) || in_array( 'administrator', $user_roles, true ) ) {
                $discount = 0;
                if($regular_price > 0){
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

    // Add to cart with quantity
    do_action( 'woocommerce_before_add_to_cart_form' ); ?>
    <form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
        <?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

        <div class="quantity-wrapper">
            <?php
            woocommerce_quantity_input( array(
                'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
                'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
                'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
            ) );
            ?>
        </div>

        <button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

        <div class="product-actions">
            <?php if ( shortcode_exists( 'yith_wcwl_add_to_wishlist' ) ) : ?>
                <div class="action-item">
                    <?php echo do_shortcode( '[yith_wcwl_add_to_wishlist]' ); ?>
                </div>
            <?php endif; ?>

            <?php if ( class_exists( 'YITH_Woocompare' ) ) : ?>
                <div class="action-item">
                    <a href="<?php echo esc_url( add_query_arg( array( 'id' => $product->get_id() ), YITH_Woocompare()->get_compare_url() ) ); ?>" class="compare" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>" rel="nofollow">
                        <i class="ph-chart-bar"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
    </form>
    <?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
    <?php
}
add_action( 'woocommerce_single_product_summary', 'kb_custom_single_product_summary', 10 );

add_filter( 'yith_wcwl_add_to_wishlist_button_html', 'kb_custom_wishlist_button_html', 10, 3 );
function kb_custom_wishlist_button_html( $html, $url, $product_type ) {
    global $product;
    $product_id = $product->get_id();

    $html = '<a href="' . esc_url($url) . '" class="add_to_wishlist single_add_to_wishlist" data-product-id="' . esc_attr($product_id) . '" data-product-type="' . esc_attr($product_type) . '" rel="nofollow">';
    $html .= '<i class="ph-heart"></i>';
    $html .= '</a>';

    return $html;
}
