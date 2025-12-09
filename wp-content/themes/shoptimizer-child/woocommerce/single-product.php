<?php
get_header( 'shop' ); ?>

    <?php
        do_action( 'woocommerce_before_main_content' );
    ?>

    <div class="mkx-single-product-container">
        <?php while ( have_posts() ) : ?>
            <?php the_post(); ?>

            <div class="mkx-single-product-layout">
                <div class="mkx-sp-col-1">
                    <?php do_action( 'woocommerce_before_single_product_summary' ); ?>
                </div>

                <div class="mkx-sp-col-2">
                    <?php woocommerce_template_single_title(); ?>
                    <div class="product-description">
                        <?php the_content(); ?>
                    </div>
                    <?php wc_display_product_attributes( $product ); ?>
                </div>

                <div class="mkx-sp-col-3">
                    <div class="summary entry-summary">
                        <?php
                            woocommerce_template_single_price();
                            woocommerce_template_single_add_to_cart();
                            woocommerce_template_single_meta();
                        ?>
                    </div>
                </div>
            </div>

        <?php endwhile; // end of the loop. ?>
    </div>

    <?php
        woocommerce_output_related_products();
        do_action( 'woocommerce_after_single_product' );
    ?>

    <?php
        do_action( 'woocommerce_after_main_content' );
    ?>

    <?php
        do_action( 'woocommerce_sidebar' );
    ?>

<?php
get_footer( 'shop' );
