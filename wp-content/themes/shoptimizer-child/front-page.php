<?php
/**
 * The front page template file - Fixed Version
 *
 * This is the template for the homepage with hero section
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shoptimizer Child
 * @version 1.0.2
 * @author KW
 * @link https://kowb.ru
 */

get_header(); ?>

    <main id="primary" class="mkx-site-main">
        <!-- Hero Section -->
        <section class="mkx-hero-section" role="banner" aria-label="<?php esc_attr_e( 'Главные предложения', 'shoptimizer-child' ); ?>">
            <div class="mkx-container">
                <div class="mkx-hero-wrapper">
                    <!-- Large Banner Carousel -->
                    <div class="mkx-hero-large-carousel" role="region" aria-label="<?php esc_attr_e( 'Основные предложения', 'shoptimizer-child' ); ?>">
                        <div class="mkx-carousel-container" data-autoplay="true" data-interval="6000">
                            <div class="mkx-carousel-track" id="largeCarouselTrack">
                                <!-- Slide 1 - АКТИВНЫЙ ПО УМОЛЧАНИЮ -->
                                <div class="mkx-carousel-slide mkx-carousel-slide--active" data-slide="0">
                                    <div class="mkx-slide-content">
                                        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/slide-0.webp')); ?>"
                                             alt="<?php esc_attr_e( 'Качественные запчасти для мобильных телефонов', 'shoptimizer-child' ); ?>"
                                             width="1045"
                                             height="280"
                                             loading="eager"
                                             class="mkx-slide-bg" />
                                        <div class="mkx-slide-overlay">
                                            <div class="mkx-slide-text">
                                                <h2 class="mkx-slide-title">
													<?php esc_html_e( 'Качественные запчасти для мобильных телефонов с гарантией', 'shoptimizer-child' ); ?>
                                                </h2>
                                                <p class="mkx-slide-subtitle">
													<?php esc_html_e( 'Широкий ассортимент деталей для всех популярных брендов', 'shoptimizer-child' ); ?>
                                                </p>
                                            </div>
                                            <div class="mkx-slide-cta-wrapper">
                                                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
                                                   class="mkx-slide-cta">
													<?php esc_html_e( 'Перейти в каталог', 'shoptimizer-child' ); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 2 -->
                                <div class="mkx-carousel-slide" data-slide="1">
                                    <div class="mkx-slide-content">
                                        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/slide-2.webp')); ?>"
                                             alt="<?php esc_attr_e( 'Быстрая доставка запчастей', 'shoptimizer-child' ); ?>"
                                             width="1045"
                                             height="280"
                                             loading="lazy"
                                             class="mkx-slide-bg" />
                                        <div class="mkx-slide-overlay">
                                            <div class="mkx-slide-text">
                                                <h2 class="mkx-slide-title">
													<?php esc_html_e( 'Быстрая доставка и удобный самовывоз', 'shoptimizer-child' ); ?>
                                                </h2>
                                                <p class="mkx-slide-subtitle">
													<?php esc_html_e( 'Выберите наиболее удобный способ получения заказа', 'shoptimizer-child' ); ?>
                                                </p>
                                            </div>
                                            <div class="mkx-slide-cta-wrapper">
    <a href="<?php echo esc_url( '/oplata-i-dostavka/' ); ?>" class="mkx-slide-cta">
        <?php esc_html_e( 'Узнать подробнее', 'shoptimizer-child' ); ?>
    </a>
</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Slide 3 -->
                                <div class="mkx-carousel-slide" data-slide="2">
                                    <div class="mkx-slide-content">
                                        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/slide-1.webp')); ?>"
                                             alt="<?php esc_attr_e( 'Проверенные запчасти с гарантией', 'shoptimizer-child' ); ?>"
                                             width="1045"
                                             height="280"
                                             loading="lazy"
                                             class="mkx-slide-bg" />
                                        <div class="mkx-slide-overlay">
                                            <div class="mkx-slide-text">
                                                <h2 class="mkx-slide-title">
													<?php esc_html_e( 'Проверенные запчасти от надежных поставщиков', 'shoptimizer-child' ); ?>
                                                </h2>
                                                <p class="mkx-slide-subtitle">
													<?php esc_html_e( 'Гарантия качества на все комплектующие', 'shoptimizer-child' ); ?>
                                                </p>
                                            </div>
                                            <div class="mkx-slide-cta-wrapper">
                                                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
                                                   class="mkx-slide-cta">
													<?php esc_html_e( 'Смотреть товары', 'shoptimizer-child' ); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Large Carousel Controls -->
                            <button class="mkx-carousel-nav mkx-carousel-nav--prev"
                                    id="largePrev"
                                    aria-label="<?php esc_attr_e( 'Предыдущий слайд', 'shoptimizer-child' ); ?>"
                                    type="button">
                                <i class="ph ph-caret-left" aria-hidden="true"></i>
                            </button>
                            <button class="mkx-carousel-nav mkx-carousel-nav--next"
                                    id="largeNext"
                                    aria-label="<?php esc_attr_e( 'Следующий слайд', 'shoptimizer-child' ); ?>"
                                    type="button">
                                <i class="ph ph-caret-right" aria-hidden="true"></i>
                            </button>

                            <!-- Large Carousel Indicators -->
                            <div class="mkx-carousel-indicators" role="tablist" aria-label="<?php esc_attr_e( 'Индикаторы слайдов', 'shoptimizer-child' ); ?>">
                                <button class="mkx-carousel-indicator mkx-carousel-indicator--active"
                                        data-slide="0"
                                        role="tab"
                                        aria-selected="true"
                                        aria-label="<?php esc_attr_e( 'Слайд 1', 'shoptimizer-child' ); ?>"
                                        type="button"></button>
                                <button class="mkx-carousel-indicator"
                                        data-slide="1"
                                        role="tab"
                                        aria-selected="false"
                                        aria-label="<?php esc_attr_e( 'Слайд 2', 'shoptimizer-child' ); ?>"
                                        type="button"></button>
                                <button class="mkx-carousel-indicator"
                                        data-slide="2"
                                        role="tab"
                                        aria-selected="false"
                                        aria-label="<?php esc_attr_e( 'Слайд 3', 'shoptimizer-child' ); ?>"
                                        type="button"></button>
                            </div>
                        </div>
                    </div>

                    <!-- Small Banner Carousel -->
                    <div class="mkx-hero-small-carousel" role="region" aria-label="<?php esc_attr_e( 'Дополнительные предложения', 'shoptimizer-child' ); ?>">
                        <div class="mkx-carousel-container" data-autoplay="false">
                            <div class="mkx-carousel-track" id="smallCarouselTrack">
                                <!-- Small Slide 1 -->
                                <div class="mkx-carousel-slide mkx-carousel-slide--active" data-slide="0">
                                    <div class="mkx-slide-content">
                                        <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/iphone_spare_parts.webp' ) ); ?>"
                                             alt="<?php esc_attr_e( 'Новые поступления запчастей для iPhone', 'shoptimizer-child' ); ?>"
                                             width="423"
                                             height="280"
                                             loading="lazy"
                                             class="mkx-slide-bg" />
                                        <div class="mkx-slide-overlay">
                                            <div class="mkx-slide-text mkx-slide-text--small">
                                                <h3 class="mkx-slide-title mkx-slide-title--small">
													<?php esc_html_e( 'Новые поступления запчастей для iPhone', 'shoptimizer-child' ); ?>
                                                </h3>
                                                <a href="<?php echo esc_url( '/cat/apple/' ); ?>" class="mkx-slide-cta">
        <?php esc_html_e( 'Смотреть', 'shoptimizer-child' ); ?>
    </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Small Slide 2 -->
                                <div class="mkx-carousel-slide" data-slide="1">
                                    <div class="mkx-slide-content">
                                        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/slide-samsung-display.webp')); ?>"
                                             alt="<?php esc_attr_e( 'Скидки на дисплеи Samsung', 'shoptimizer-child' ); ?>"
                                             width="423"
                                             height="280"
                                             loading="lazy"
                                             class="mkx-slide-bg" />
                                        <div class="mkx-slide-overlay">
                                            <div class="mkx-slide-text mkx-slide-text--small">
                                                <h3 class="mkx-slide-title mkx-slide-title--small">
													<?php esc_html_e( 'Скидки на дисплеи Samsung', 'shoptimizer-child' ); ?>
                                                </h3>
                                                <a href="<?php echo esc_url( '/cat/samsung/displei-dlya-samsung/' ); ?>" class="mkx-slide-cta">
        <?php esc_html_e( 'Подробнее', 'shoptimizer-child' ); ?>
    </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Small Slide 3 -->
                                <div class="mkx-carousel-slide" data-slide="2">
                                    <div class="mkx-slide-content">
                                        <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/consulting-1.webp')); ?>"
                                             alt="<?php esc_attr_e( 'Консультации по установке запчастей', 'shoptimizer-child' ); ?>"
                                             width="423"
                                             height="280"
                                             loading="lazy"
                                             class="mkx-slide-bg" />
                                        <div class="mkx-slide-overlay">
                                            <div class="mkx-slide-text mkx-slide-text--small">
                                                <h3 class="mkx-slide-title mkx-slide-title--small">
													<?php esc_html_e( 'Консультации по установке запчастей', 'shoptimizer-child' ); ?>
                                                </h3>
                                                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
                                                   class="mkx-slide-cta mkx-slide-cta--small">
													<?php esc_html_e( 'Узнать', 'shoptimizer-child' ); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Small Carousel Controls -->
                            <button class="mkx-carousel-nav mkx-carousel-nav--prev mkx-carousel-nav--small"
                                    id="smallPrev"
                                    aria-label="<?php esc_attr_e( 'Предыдущий слайд', 'shoptimizer-child' ); ?>"
                                    type="button">
                                <i class="ph ph-caret-left" aria-hidden="true"></i>
                            </button>
                            <button class="mkx-carousel-nav mkx-carousel-nav--next mkx-carousel-nav--small"
                                    id="smallNext"
                                    aria-label="<?php esc_attr_e( 'Следующий слайд', 'shoptimizer-child' ); ?>"
                                    type="button">
                                <i class="ph ph-caret-right" aria-hidden="true"></i>
                            </button>

                            <!-- Small Carousel Indicators -->
                            <div class="mkx-carousel-indicators mkx-carousel-indicators--small" role="tablist" aria-label="<?php esc_attr_e( 'Индикаторы слайдов', 'shoptimizer-child' ); ?>">
                                <button class="mkx-carousel-indicator mkx-carousel-indicator--active mkx-carousel-indicator--small"
                                        data-slide="0"
                                        role="tab"
                                        aria-selected="true"
                                        aria-label="<?php esc_attr_e( 'Слайд 1', 'shoptimizer-child' ); ?>"
                                        type="button"></button>
                                <button class="mkx-carousel-indicator mkx-carousel-indicator--small"
                                        data-slide="1"
                                        role="tab"
                                        aria-selected="false"
                                        aria-label="<?php esc_attr_e( 'Слайд 2', 'shoptimizer-child' ); ?>"
                                        type="button"></button>
                                <button class="mkx-carousel-indicator mkx-carousel-indicator--small"
                                        data-slide="2"
                                        role="tab"
                                        aria-selected="false"
                                        aria-label="<?php esc_attr_e( 'Слайд 3', 'shoptimizer-child' ); ?>"
                                        type="button"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

       

        <!-- Catalog Section -->
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

         <!-- Content from page or default WooCommerce shop content -->
        <div class="mkx-page-content">
            <div class="mkx-container">
            <div class="mkx-catalog-header">
                    <h2 class="mkx-catalog-title">
						<?php esc_html_e( 'Популярные товары', 'shoptimizer-child' ); ?>
                    </h2>
                    <p class="mkx-catalog-subtitle">
						<?php esc_html_e( 'Топ товаров, основанный на отзывах покупателей', 'shoptimizer-child' ); ?>
                    </p>
                </div>
				<?php
				// Display page content if exists
				if ( have_posts() ) :
					while ( have_posts() ) :
						the_post();
						if ( get_the_content() ) :
							?>
                            <div class="mkx-page-entry-content">
								<?php the_content(); ?>
                            </div>
						<?php
						endif;
					endwhile;
				endif;

				// Display shop content if WooCommerce is active
				if ( class_exists( 'WooCommerce' ) ) :
					echo do_shortcode( '[products limit="10" columns="4" orderby="popularity"]' );
				endif;
				?>
            </div>
        </div>

    </main>

 <!-- Useful Notes Section -->
        <section class="mkx-useful-notes-section">
            <div class="mkx-container">
                <div class="mkx-catalog-header">
                    <h2 class="mkx-catalog-title">
						<?php esc_html_e( 'Полезные заметки', 'shoptimizer-child' ); ?>
                    </h2>
                    <p class="mkx-catalog-subtitle">
						<?php esc_html_e( 'Наши эксперты делятся опытом и советами', 'shoptimizer-child' ); ?>
                    </p>
                </div>
                <div class="mkx-articles-grid">
                    <article class="mkx-article-card">
                        <a href="http://mix.dev.loc/remont-razema-dlya-zaryadki-svoimi-rukami-poshagovoe-rukovodstvo-dlya-novichkov/" class="mkx-article-card-link">
                            <img src="http://mix.dev.loc/wp-content/uploads/2025/09/remont-razema-dlya-zaryadki-svoimi-rukami.webp"
                                 alt="Ремонт разъема для зарядки своими руками"
                                 width="350"
                                 height="200"
                                 loading="lazy"
                                 class="mkx-article-thumbnail" />
                            <h3 class="mkx-article-title">
								Ремонт разъема для зарядки своими руками: пошаговое руководство для новичков
                            </h3>
                        </a>
                    </article>
                    <article class="mkx-article-card">
                        <a href="http://mix.dev.loc/akkumulyatory-dlya-smartfonov-chto-nuzhno-znat-o-batareyah-populyarnyh-brendov/" class="mkx-article-card-link">
                            <img src="http://mix.dev.loc/wp-content/uploads/2025/09/akkumulyatory-dlya-smartfonov-chto-nuzhno-znat-o-batareyah-populyarnyh-brendov.webp"
                                 alt="Аккумуляторы для смартфонов: что нужно знать о батареях популярных брендов"
                                 width="350"
                                 height="200"
                                 loading="lazy"
                                 class="mkx-article-thumbnail" />
                            <h3 class="mkx-article-title">
								Аккумуляторы для смартфонов: что нужно знать о батареях популярных брендов
                            </h3>
                        </a>
                    </article>
                    <article class="mkx-article-card">
                        <a href="http://mix.dev.loc/ekrany-dlya-iphone-original-oem-kopiya-ili-analog-kak-vybrat-i-ne-oshibitsya/" class="mkx-article-card-link">
                            <img src="http://mix.dev.loc/wp-content/uploads/2025/09/ekrany-dlya-iphone.webp"
                                 alt="Экраны для iPhone: оригинал, OEM, копия или аналог? Как выбрать и не ошибиться"
                                 width="350"
                                 height="200"
                                 loading="lazy"
                                 class="mkx-article-thumbnail" />
                            <h3 class="mkx-article-title">
								Экраны для iPhone: оригинал, OEM, копия или аналог? Как выбрать и не ошибиться
                            </h3>
                        </a>
                    </article>
                </div>
            </div>
        </section>

<?php
get_footer();