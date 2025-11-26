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

<section class="mkx-catalog-section" role="region" aria-label="Каталог запчастей по брендам">
            <div class="mkx-container">
                <!-- Section Header -->
                <div class="mkx-catalog-header">
                    <h2 class="mkx-catalog-title">
						Каталог запчастей по брендам                    </h2>
                    <p class="mkx-catalog-subtitle">
						Выберите бренд вашего устройства и найдите нужные запчасти                    </p>
                </div>

                <!-- Brand Cards Grid -->
                <div class="mkx-catalog-grid">
					                        <article class="mkx-brand-card" data-brand="apple">
                            <a href="<?php echo esc_url( get_term_link( 'apple', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории Apple - запчасти для iPhone/iPad">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/apple-logo.svg' ); ?>" alt="Логотип Apple" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										Apple                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для iPhone/iPad                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="samsung">
                            <a href="<?php echo esc_url( get_term_link( 'samsung', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории Samsung - запчасти для телефонов Galaxy">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/samsung-logo.svg' ); ?>" alt="Логотип Samsung" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										Samsung                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов Galaxy                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="xiaomi-redmi">
                            <a href="<?php echo esc_url( get_term_link( 'xiaomi-redmi', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории Xiaomi - запчасти для телефонов Redmi, Poco">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/xiaomi-logo.svg' ); ?>" alt="Логотип Xiaomi" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										Xiaomi                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов Redmi, Poco                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="huawei-honor">
                            <a href="<?php echo esc_url( get_term_link( 'huawei-honor', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории Huawei - запчасти для телефонов Huawei, Honor">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/huawei-logo.svg' ); ?>" alt="Логотип Huawei" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										Huawei                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов Huawei, Honor                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="oppo">
                            <a href="<?php echo esc_url( get_term_link( 'oppo', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории OPPO - запчасти для телефонов OPPO">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/oppo-logo.svg' ); ?>" alt="Логотип OPPO" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										OPPO                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов OPPO                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="realme">
                            <a href="<?php echo esc_url( get_term_link( 'realme', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории Realme - запчасти для телефонов Realme">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/realme-logo.svg' ); ?>" alt="Логотип Realme" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										Realme                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов Realme                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="vivo">
                            <a href="<?php echo esc_url( get_term_link( 'vivo', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории VIVO - запчасти для телефонов VIVO">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/vivo-logo.svg' ); ?>" alt="Логотип VIVO" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										VIVO                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов VIVO                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="infinix">
                            <a href="<?php echo esc_url( get_term_link( 'infinix', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории Infinix - запчасти для телефонов Infinix">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/infinix-logo.svg' ); ?>" alt="Логотип Infinix" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										Infinix                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов Infinix                                    </p>
                                </div>
                            </a>
                        </article>
						                        <article class="mkx-brand-card" data-brand="tecno">
                            <a href="<?php echo esc_url( get_term_link( 'tecno', 'product_cat' ) ); ?>" class="mkx-brand-card-link" aria-label="Перейти к категории TECNO - запчасти для телефонов TECNO">

                                <div class="mkx-brand-card-inner">
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/logo/tecno-logo.svg' ); ?>" alt="Логотип TECNO" class="mkx-brand-logo loaded" width="60" height="60" loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
										TECNO                                    </h3>

                                    <!-- Brand Description -->
                                    <p class="mkx-brand-description">
										запчасти для телефонов TECNO                                    </p>
                                </div>
                            </a>
                        </article>
						                </div>
            </div>
        </section>
