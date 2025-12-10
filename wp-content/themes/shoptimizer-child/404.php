<?php
/**
 * Template Name: 404 Error Page
 * Description: Creative 404 page for МИКЗ spare parts store
 * Version: 1.0.0
 * Author: Костя Вебин
 * Author URI: https://kowb.ru
 */

get_header();
?>

<main id="mkx-404-main" class="mkx-404-page" role="main">
    
    <!-- Hero Section with Animation -->
    <section class="mkx-404-hero" role="region" aria-label="Страница не найдена">
        <div class="mkx-container">
            <div class="mkx-404-content">
                
                <!-- Animated 404 Icon -->
                <div class="mkx-404-icon" aria-hidden="true">
                    <svg class="mkx-404-svg" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <!-- Broken Phone Screen Illustration -->
                        <g class="mkx-phone-outline">
                            <rect x="60" y="20" width="80" height="160" rx="8" fill="none" stroke="currentColor" stroke-width="3"/>
                            <circle cx="100" cy="165" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
                        </g>
                        
                        <!-- Crack Lines -->
                        <g class="mkx-crack-lines">
                            <path d="M 70 40 L 130 140" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 130 50 L 80 130" stroke="currentColor" stroke-width="2" stroke-linecap="round" opacity="0.6"/>
                            <path d="M 90 40 L 110 100" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                        </g>
                        
                        <!-- Screws -->
                        <circle cx="70" cy="30" r="2" fill="currentColor"/>
                        <circle cx="130" cy="30" r="2" fill="currentColor"/>
                    </svg>
                </div>

                <!-- Error Code -->
                <h1 class="mkx-404-title">
                    <span class="mkx-404-number">404</span>
                </h1>

                <!-- Error Message -->
                <div class="mkx-404-message">
                    <p class="mkx-404-text-primary">
                        Упс! Эта страница сломалась
                    </p>
                    <p class="mkx-404-text-secondary">
                        Похоже, нужная вам страница больше не работает или была перемещена. Но не волнуйтесь — у нас есть все запчасти для её ремонта!
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="mkx-404-actions">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="mkx-404-btn mkx-404-btn-primary">
                        <i class="ph ph-house" aria-hidden="true"></i>
                        <span>На главную</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/shop/')); ?>" class="mkx-404-btn mkx-404-btn-secondary">
                        <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                        <span>Каталог запчастей</span>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- Brand Catalog Section -->
    <section class="mkx-catalog-section mkx-404-catalog" role="region" aria-label="Каталог запчастей по брендам">
        <div class="mkx-container">
            
            <!-- Section Header -->
            <div class="mkx-catalog-header">
                <h2 class="mkx-catalog-title">
                    Найдите запчасти по бренду
                </h2>
                <p class="mkx-catalog-subtitle">
                    Выберите бренд вашего устройства и найдите нужные запчасти
                </p>
            </div>

            <!-- Brand Cards Grid -->
            <div class="mkx-catalog-grid">
                <?php
                // Get product categories with proper caching
                $brands_transient_key = 'mkx_404_brands_' . md5(serialize(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                )));
                
                $brands = get_transient($brands_transient_key);
                
                if (false === $brands) {
                    $brands = get_terms(array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                        'orderby'    => 'name',
                        'order'      => 'ASC',
                        'parent'     => 0,
                        'number'     => 9,
                    ));
                    
                    if (!is_wp_error($brands)) {
                        set_transient($brands_transient_key, $brands, HOUR_IN_SECONDS);
                    }
                }

                if (!empty($brands) && !is_wp_error($brands)) :
                    foreach ($brands as $brand) :
                        $brand_name = esc_html($brand->name);
                        $brand_slug = esc_attr($brand->slug);
                        $brand_link = esc_url(get_term_link($brand));
                        $brand_description = esc_html($brand->description);
                        
                        // Get brand logo from category meta
                        $thumbnail_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
                        $brand_logo = '';
                        
                        if ($thumbnail_id) {
                            $brand_logo = wp_get_attachment_image_url($thumbnail_id, 'thumbnail');
                        } else {
                            // Fallback logo path
                            $brand_logo = get_stylesheet_directory_uri() . '/assets/images/logo/' . $brand_slug . '-logo.svg';
                        }
                        ?>
                        
                        <article class="mkx-brand-card" data-brand="<?php echo $brand_slug; ?>">
                            <a href="<?php echo $brand_link; ?>" 
                               class="mkx-brand-card-link" 
                               aria-label="Перейти к категории <?php echo $brand_name; ?> - <?php echo $brand_description; ?>">
                                
                                <div class="mkx-brand-card-inner">
                                    
                                    <!-- Brand Logo -->
                                    <div class="mkx-brand-logo-wrapper">
                                        <img src="<?php echo esc_url($brand_logo); ?>" 
                                             alt="Логотип <?php echo $brand_name; ?>" 
                                             class="mkx-brand-logo" 
                                             width="60" 
                                             height="60" 
                                             loading="lazy">
                                    </div>

                                    <!-- Brand Name -->
                                    <h3 class="mkx-brand-name">
                                        <?php echo $brand_name; ?>
                                    </h3>

                                    <!-- Brand Description -->
                                    <?php if ($brand_description) : ?>
                                    <p class="mkx-brand-description">
                                        <?php echo $brand_description; ?>
                                    </p>
                                    <?php endif; ?>
                                    
                                </div>
                            </a>
                        </article>
                        
                    <?php endforeach;
                endif;
                ?>
            </div>
            
        </div>
    </section>

    <!-- Search Section -->
    <section class="mkx-404-search" role="region" aria-label="Поиск запчастей">
        <div class="mkx-container">
            <div class="mkx-404-search-content">
                
                <h2 class="mkx-404-search-title">
                    Или воспользуйтесь поиском
                </h2>
                
                <form role="search" method="get" class="mkx-404-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="mkx-404-search-wrapper">
                        <label for="mkx-404-search-input" class="mkx-sr-only">
                            Поиск запчастей
                        </label>
                        <input type="search" 
                               id="mkx-404-search-input"
                               class="mkx-404-search-input" 
                               placeholder="Например: дисплей iPhone 13" 
                               value="<?php echo get_search_query(); ?>" 
                               name="s"
                               autocomplete="off"
                               required>
                        <input type="hidden" name="post_type" value="product">
                        <button type="submit" class="mkx-404-search-btn" aria-label="Найти запчасти">
                            <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                            <span>Найти</span>
                        </button>
                    </div>
                </form>
                
            </div>
        </div>
    </section>

    <!-- Popular Links -->
    <section class="mkx-404-links" role="region" aria-label="Популярные разделы">
        <div class="mkx-container">
            <h2 class="mkx-404-links-title">Популярные разделы</h2>
            <nav class="mkx-404-links-grid" aria-label="Быстрая навигация">
                
                <a href="<?php echo esc_url(home_url('/informaciya/')); ?>" class="mkx-404-link-card">
                    <i class="ph ph-info" aria-hidden="true"></i>
                    <span>Информация</span>
                </a>
                
                <a href="<?php echo esc_url(home_url('/contacts/')); ?>" class="mkx-404-link-card">
                    <i class="ph ph-phone" aria-hidden="true"></i>
                    <span>Контакты</span>
                </a>
                
                <a href="<?php echo esc_url(home_url('/oplata-i-dostavka/')); ?>" class="mkx-404-link-card">
                    <i class="ph ph-truck" aria-hidden="true"></i>
                    <span>Оплата и доставка</span>
                </a>
                
                <a href="<?php echo esc_url(home_url('/akcii/')); ?>" class="mkx-404-link-card">
                    <i class="ph ph-percent" aria-hidden="true"></i>
                    <span>Акции</span>
                </a>
                
            </nav>
        </div>
    </section>

</main>

<?php get_footer(); ?>