<?php
/**
 * Displayed when no products are found matching the current query
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/no-products-found.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="woocommerce-no-products-found">
	<?php wc_print_notice( esc_html__( 'Товаров, соответствующих вашему запросу, не обнаружено.', 'woocommerce' ), 'notice' ); ?>
</div>
<?php
if ( function_exists( 'shoptimizer_child_display_no_results_brands' ) ) {
	shoptimizer_child_display_no_results_brands();
}
