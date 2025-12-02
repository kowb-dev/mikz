<?php
/**
 * Functions for displaying content on the no-results page.
 *
 * @package Shoptimizer Child
 * @version 1.0.0
 * @author KW
 * @link https://kowb.ru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Display brand catalog section.
 *
 * Renders a grid of brand cards with logos, names, and descriptions.
 * The data is sourced from a predefined array, mirroring the front-page implementation.
 */
function shoptimizer_child_display_no_results_brands() {
	// Brand data array with descriptions
	$brands = array(
		'apple' => array(
			'name' => __( 'Apple', 'shoptimizer-child' ),
			'description' => __( 'запчасти для iPhone/iPad', 'shoptimizer-child' ),
			'logo' => 'apple-logo.svg',
		),
		'samsung' => array(
			'name' => __( 'Samsung', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов Galaxy', 'shoptimizer-child' ),
			'logo' => 'samsung-logo.svg',
		),
		'xiaomi-redmi' => array(
			'name' => __( 'Xiaomi', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов Redmi, Poco', 'shoptimizer-child' ),
			'logo' => 'xiaomi-logo.svg',
		),
		'huawei-honor' => array(
			'name' => __( 'Huawei', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов Huawei, Honor', 'shoptimizer-child' ),
			'logo' => 'huawei-logo.svg',
		),
		'oppo' => array(
			'name' => __( 'OPPO', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов OPPO', 'shoptimizer-child' ),
			'logo' => 'oppo-logo.svg',
		),
		'realme' => array(
			'name' => __( 'Realme', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов Realme', 'shoptimizer-child' ),
			'logo' => 'realme-logo.svg',
		),
		'vivo' => array(
			'name' => __( 'VIVO', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов VIVO', 'shoptimizer-child' ),
			'logo' => 'vivo-logo.svg',
		),
		'infinix' => array(
			'name' => __( 'Infinix', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов Infinix', 'shoptimizer-child' ),
			'logo' => 'infinix-logo.svg',
		),
		'tecno' => array(
			'name' => __( 'TECNO', 'shoptimizer-child' ),
			'description' => __( 'запчасти для телефонов TECNO', 'shoptimizer-child' ),
			'logo' => 'tecno-logo.svg',
		)
	);
	?>
	<section class="mkx-catalog-section" role="region" aria-label="<?php esc_attr_e( 'Каталог запчастей по брендам', 'shoptimizer-child' ); ?>">
		<div class="mkx-container">
			<!-- Section Header -->
			<div class="mkx-catalog-header">
				<h2 class="mkx-catalog-title">
					<?php esc_html_e( 'Каталог запчастей по брендам', 'shoptimizer-child' ); ?>
				</h2>
				<p class="mkx-catalog-subtitle">
					<?php esc_html_e( 'Выберите бренд вашего устройства и найдите нужные запчасти', 'shoptimizer-child' ); ?>
				</p>
			</div>

			<!-- Brand Cards Grid -->
			<div class="mkx-catalog-grid">
				<?php
				// Loop through brands and create cards
				foreach ( $brands as $brand_key => $brand_data ) {
					// Get the category link by slug
					$category_link = get_term_link( $brand_key, 'product_cat' );
					if ( is_wp_error( $category_link ) ) {
						$category_link = '#'; // Fallback if category doesn't exist
					}

					?>
					<article class="mkx-brand-card" data-brand="<?php echo esc_attr( $brand_key ); ?>">
						<a href="<?php echo esc_url( $category_link ); ?>"
						   class="mkx-brand-card-link"
						   aria-label="<?php echo esc_attr( sprintf( __( 'Перейти к категории %s - %s', 'shoptimizer-child' ), $brand_data['name'], $brand_data['description'] ) ); ?>">

							<div class="mkx-brand-card-inner">
								<!-- Brand Logo -->
								<div class="mkx-brand-logo-wrapper">
									<img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/logo/' . $brand_data['logo'] ) ); ?>"
										 alt="<?php echo esc_attr( sprintf( __( 'Логотип %s', 'shoptimizer-child' ), $brand_data['name'] ) ); ?>"
										 class="mkx-brand-logo"
										 width="60"
										 height="60"
										 loading="lazy" />
								</div>

								<!-- Brand Name -->
								<h3 class="mkx-brand-name">
									<?php echo esc_html( $brand_data['name'] ); ?>
								</h3>

								<!-- Brand Description -->
								<p class="mkx-brand-description">
									<?php echo esc_html( $brand_data['description'] ); ?>
								</p>
							</div>
						</a>
					</article>
					<?php
				}
				?>
			</div>
		</div>
	</section>
	<?php
}
