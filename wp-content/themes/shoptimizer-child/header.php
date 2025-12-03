<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Shoptimizer Child
 * @version 1.0.8
 * @author KB
 * @link https://kowb.ru
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="mkx-site">
    <a class="mkx-skip-link mkx-screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'shoptimizer' ); ?></a>

    <header id="masthead" class="mkx-site-header">
        <!-- Desktop Top Header -->
        <div class="mkx-header-top-header">
            <div class="mkx-container">
                <div class="mkx-top-header-content">
                    <!-- Top Menu Links -->
                    <?php
                    if ( has_nav_menu( 'mkx_top_header_menu' ) ) {
                        wp_nav_menu( [
                            'theme_location'       => 'mkx_top_header_menu',
                            'container'            => 'nav',
                            'container_class'      => 'mkx-top-header-menu',
                            'container_aria_label' => esc_attr__( 'Дополнительное меню', 'shoptimizer-child' ),
                            'menu_class'           => 'mkx-top-menu-list',
                            'depth'                => 1,
                            'fallback_cb'          => false,
                        ] );
                    } else {
                        // Fallback for initial setup
                        echo '<nav class="mkx-top-header-menu" role="navigation" aria-label="' . esc_attr__( 'Дополнительное меню', 'shoptimizer-child' ) . '"><ul class="mkx-top-menu-list"><li><a href="#" class="mkx-top-menu-link">' . esc_html__( 'Assign a menu', 'shoptimizer-child' ) . '</a></li></ul></nav>';
                    }
                    ?>

                    <!-- Top Phone -->
                    <div class="mkx-top-header-phone">
                        <?php
                        $phone = get_theme_mod( 'mkx_phone', '+7 (999) 123-45-67' );
                        if ( $phone ) : ?>
                            <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"
                               class="mkx-top-phone-link">
                                <?php echo esc_html( $phone ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Top Bar -->
        <div class="mkx-header-top-bar">
            <div class="mkx-container">
                <div class="mkx-top-bar-content">
                    <!-- Logo + Brand Text -->
                    <div class="mkx-site-branding">
                        <?php if ( ! is_front_page() ) : ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'На главную страницу', 'shoptimizer-child' ); ?>" class="mkx-logo-container">
                            <?php else : ?>
                            <div class="mkx-logo-container">
                                <?php endif; ?>
                                <!-- Основной контейнер для всего бренда -->
                                <div class="mkx-brand-text-container">
                                    <!-- Верхняя часть: логотип + название МИКЗ -->
                                    <div class="mkx-brand-top">
                                        <!-- SVG Logo -->
                                        <div class="mkx-logo-svg">
                                            <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/logo/logo.svg')); ?>"
                                                 alt="Логотип МИКЗ"
                                                 width="512"
                                                 height="512"
                                                 loading="eager" class="mkx-site-logo" />
                                        </div>

                                        <!-- Main Brand Text -->
                                        <div class="mkx-brand-text">
                                            <span class="mkx-brand-line1"><?php esc_html_e( 'МИКЗ', 'shoptimizer-child' ); ?></span>
                                        </div>
                                    </div>

                                    <!-- Brand Tagline - под логотипом и названием -->
                                    <div class="mkx-brand-tagline">
                                        <span class="mkx-tagline-line1"><?php esc_html_e( 'ЗАПЧАСТИ ДЛЯ МОБИЛЬНЫХ', 'shoptimizer-child' ); ?></span>
                                        <span class="mkx-tagline-line2"><?php esc_html_e( 'ТЕЛЕФОНОВ И КОМПЬЮТЕРОВ', 'shoptimizer-child' ); ?></span>
                                    </div>
                                </div>
                                <?php if ( ! is_front_page() ) : ?>
                        </a>
                        <?php else : ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Catalog Button -->
                <div class="mkx-catalog-button">
                    <button class="mkx-catalog-button__btn" id="catalogToggle" aria-expanded="false" aria-haspopup="true">
                        <i class="ph ph-list" aria-hidden="true"></i>
                        <span class="mkx-catalog-button__text"><?php esc_html_e( 'Каталог', 'shoptimizer-child' ); ?></span>
                    </button>
                    <!-- Catalog Mega Menu -->
                    <div class="mkx-catalog-megamenu" id="catalogMegamenu" role="menu" aria-label="<?php esc_attr_e( 'Каталог товаров', 'shoptimizer-child' ); ?>">
                        <!-- Mobile Catalog Header with Close Button -->
                        <div class="mkx-catalog-megamenu__header">
                            <h2 class="mkx-catalog-megamenu__title-main"><?php esc_html_e( 'Каталог товаров', 'shoptimizer-child' ); ?></h2>
                            <button class="mkx-catalog-megamenu__close" id="catalogMegamenuClose" aria-label="<?php esc_attr_e( 'Закрыть каталог', 'shoptimizer-child' ); ?>">
                                <i class="ph ph-x" aria-hidden="true"></i>
                            </button>
                        </div>

                        <div class="mkx-catalog-megamenu__content">
                            <?php
                            if ( has_nav_menu( 'catalog-mega' ) ) {
                                wp_nav_menu( array(
                                    'theme_location' => 'catalog-mega',
                                    'container'      => false,
                                    'items_wrap'     => '%3$s', // Render items without a container ul
                                    'walker'         => new MKX_Mega_Menu_Walker(),
                                    'depth'          => 2, // Process only top-level and one level of sub-items
                                ) );
                            } else {
                                // Fallback hardcoded menu if no menu is assigned
                                $catalog_columns = function_exists('mkx_get_catalog_megamenu_data') ? mkx_get_catalog_megamenu_data() : array();
                                foreach ( $catalog_columns as $column ) : ?>
                                    <div class="mkx-catalog-megamenu__column">
                                        <h3 class="mkx-catalog-megamenu__title"><?php echo esc_html( $column['title'] ); ?></h3>
                                        <ul class="mkx-catalog-megamenu__list">
                                            <?php foreach ( $column['items'] as $item ) : ?>
                                                <li>
                                                    <a href="<?php echo esc_url( $item['url'] ); ?>" class="mkx-catalog-megamenu__link" role="menuitem">
                                                        <?php echo esc_html( $item['title'] ); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach;
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="mkx-header-search">
                    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                        <form role="search" method="get" class="mkx-woocommerce-product-search mkx-search-clear-wrapper" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <label for="woocommerce-product-search-field" class="mkx-screen-reader-text">
                                <?php esc_html_e( 'Поиск товаров:', 'shoptimizer-child' ); ?>
                            </label>
                            <input type="search"
                                   id="woocommerce-product-search-field"
                                   class="mkx-search-field"
                                   placeholder="<?php echo esc_attr__( 'Поиск товара...', 'shoptimizer-child' ); ?>"
                                   value="<?php echo get_search_query(); ?>"
                                   name="s"
                                   autocomplete="off" />
                            <button type="button" class="mkx-search-clear" aria-label="<?php esc_attr_e( 'Очистить', 'shoptimizer-child' ); ?>">
                                <i class="ph ph-x" aria-hidden="true"></i>
                            </button>
                            <button type="submit" class="mkx-search-submit" aria-label="<?php esc_attr_e( 'Найти', 'shoptimizer-child' ); ?>">
                                <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                            </button>
                            <input type="hidden" name="post_type" value="product" />
                        </form>
                    <?php endif; ?>
                </div>

                <!-- User Actions -->
                <div class="mkx-header-actions">
                    <div class="mkx-action-links">
                        <?php
                        if ( has_nav_menu( 'mkx_action_links_menu' ) ) {
                            wp_nav_menu( [
                                'theme_location' => 'mkx_action_links_menu',
                                'container'      => false,
                                'items_wrap'     => '%3$s',
                                'walker'         => new Mkx_Action_Links_Walker(),
                                'depth'          => 1,
                            ] );
                        } else {
                            // Fallback for initial setup
                            echo '<a href="#" class="mkx-action-link"><i class="ph ph-user" aria-hidden="true"></i><span class="mkx-action-text">' . esc_html__( 'Assign Menu', 'shoptimizer-child' ) . '</span></a>';
                        }
                        ?>
                    </div>

                    <!-- Cart -->
                    <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                        <div class="mkx-header-cart">
                            <a class="mkx-cart-contents"
                               href="<?php echo esc_url( wc_get_cart_url() ); ?>"
                               title="<?php esc_attr_e( 'Посмотреть корзину', 'shoptimizer-child' ); ?>">
                                <i class="ph ph-shopping-cart" aria-hidden="true"></i>
                                <span class="mkx-cart-text"><?php esc_html_e( 'Корзина', 'shoptimizer-child' ); ?></span>
                                <?php if ( WC()->cart->get_cart_contents_count() > 0 ) : ?>
                                    <span class="mkx-badge-count mkx-cart-count mkx-badge-visible" aria-label="<?php echo esc_attr( sprintf( _n( '%s товар в корзине', '%s товаров в корзине', WC()->cart->get_cart_contents_count(), 'shoptimizer-child' ), WC()->cart->get_cart_contents_count() ) ); ?>">
                                            <?php echo WC()->cart->get_cart_contents_count(); ?>
                                        </span>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</div>

<!-- Mobile Top Bar -->
<div class="mkx-mobile-top-bar">
    <div class="mkx-container">
        <div class="mkx-mobile-top-content">
            <!-- Phone Link -->
            <?php if ( $phone ) : ?>
                <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="mkx-mobile-contact-link" aria-label="<?php esc_attr_e( 'Позвонить', 'shoptimizer-child' ); ?>">
                    <i class="ph ph-phone" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <!-- Mobile Logo + Brand -->
            <div class="mkx-mobile-branding">
                <?php if ( ! is_front_page() ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'На главную страницу', 'shoptimizer-child' ); ?>" class="mkx-mobile-logo-container">
                    <?php else : ?>
                    <div class="mkx-mobile-logo-container">
                        <?php endif; ?>

                        <!-- 1-й ряд: Логотип + Название (2 колонки) -->
                        <div class="mkx-mobile-brand-top">
                            <!-- SVG Logo -->
                            <div class="mkx-mobile-logo-svg">
                                <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/logo/logo.svg')); ?>"
                                     alt="Логотип МИКЗ"
                                     width="512"
                                     height="512"
                                     loading="eager" class="mkx-mobile-brand-logo" />
                            </div>

                            <!-- Main Brand Text -->
                            <div class="mkx-mobile-brand-text">
                                <span class="mkx-mobile-brand-line1"><?php esc_html_e( 'МИКЗ', 'shoptimizer-child' ); ?></span>
                            </div>
                        </div>

                        <!-- 2-й ряд: Подпись (1 колонка) -->
                        <div class="mkx-mobile-brand-tagline">
                            <span class="mkx-mobile-tagline-line1"><?php esc_html_e( 'ЗАПЧАСТИ ДЛЯ МОБИЛЬНЫХ ТЕЛЕФОНОВ И КОМПЬЮТЕРОВ', 'shoptimizer-child' ); ?></span>
                        </div>

                        <?php if ( ! is_front_page() ) : ?>
                </a>
                <?php else : ?>
            </div>
        <?php endif; ?>
        </div>


        <!-- WhatsApp Link -->
        <?php
        $whatsapp = get_theme_mod( 'mkx_social_whatsapp', 'https://wa.me/1234567890' );
        if ( $whatsapp ) : ?>
            <a href="<?php echo esc_url( $whatsapp ); ?>" class="whatsapp-link mkx-mobile-contact-link" aria-label="<?php esc_attr_e( 'Написать в WhatsApp', 'shoptimizer-child' ); ?>" target="_blank" rel="noopener">
                <i class="ph ph-whatsapp-logo" aria-hidden="true"></i>
            </a>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Mobile Search Bar -->
<div class="mkx-mobile-search-bar">
    <div class="mkx-container">
        <div class="mkx-mobile-search-content">
            <!-- Mobile Menu Toggle -->
            <button class="mkx-mobile-menu-toggle"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                    aria-label="<?php esc_attr_e( 'Открыть меню', 'shoptimizer-child' ); ?>">
                <i class="ph ph-list-magnifying-glass" aria-hidden="true"></i>
            </button>

            <!-- Mobile Search -->
            <div class="mkx-mobile-search">
                <?php if ( class_exists( 'WooCommerce' ) ) : ?>
                    <form role="search" method="get" class="mkx-woocommerce-product-search mkx-search-clear-wrapper" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <label for="mobile-product-search-field" class="mkx-screen-reader-text">
                            <?php esc_html_e( 'Поиск товаров:', 'shoptimizer-child' ); ?>
                        </label>
                        <input type="search"
                               id="mobile-product-search-field"
                               class="mkx-search-field"
                               placeholder="<?php echo esc_attr__( 'Поиск товара...', 'shoptimizer-child' ); ?>"
                               value="<?php echo get_search_query(); ?>"
                               name="s"
                               autocomplete="off" />
                        <button type="button" class="mkx-search-clear" aria-label="<?php esc_attr_e( 'Очистить', 'shoptimizer-child' ); ?>">
                            <i class="ph ph-x" aria-hidden="true"></i>
                        </button>
                        <button type="submit" class="mkx-search-submit" aria-label="<?php esc_attr_e( 'Найти', 'shoptimizer-child' ); ?>">
                            <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                        </button>
                        <input type="hidden" name="post_type" value="product" />
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Main Navigation -->
<nav id="site-navigation" class="mkx-main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Основное меню', 'shoptimizer-child' ); ?>">
    <div class="mkx-container">
        <div class="mkx-nav-wrapper">
            <!-- Desktop Menu -->
            <?php
            if ( has_nav_menu( 'horizontal_menu' ) ) {
                wp_nav_menu( array(
                    'theme_location' => 'horizontal_menu',
                    'menu_id'        => 'primary-menu',
                    'menu_class'     => 'mkx-primary-menu',
                    'container'      => false,
                    'items_wrap'     => '<ul id="%1$s" class="%2$s" role="menubar">%3$s</ul>',
                    'fallback_cb'    => false,
                    'depth'          => 2,
                    'walker'         => new MKX_Nav_Walker(),
                ) );
            }
            ?>

            <!-- Mobile Menu -->
            <div class="mkx-mobile-menu" id="mobile-menu" aria-hidden="true">
                <div class="mkx-mobile-menu__content">
                    <!-- Mobile Menu Header with Close Button -->
                    <div class="mkx-mobile-menu__header">
                        <button class="mkx-mobile-menu__close" id="mobileMenuClose" aria-label="<?php esc_attr_e( 'Закрыть меню', 'shoptimizer-child' ); ?>">
                            <i class="ph ph-x" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Mobile menu items -->
                    <div class="mkx-mobile-menu__items">
                        <?php
                        if ( has_nav_menu( 'mobile_main_menu' ) ) {
                            $menu_locations = get_nav_menu_locations();
                            $menu_id = $menu_locations['mobile_main_menu'];
                            $menu_items = wp_get_nav_menu_items($menu_id);

                            // Create a hierarchical array from the flat menu items list
                            $hierarchical_menu = array();
                            foreach ( (array) $menu_items as $key => $menu_item ) {
                                if ($menu_item->menu_item_parent == 0) {
                                    $hierarchical_menu[$menu_item->ID] = $menu_item;
                                    $hierarchical_menu[$menu_item->ID]->submenu = array();
                                }
                            }
                            foreach ( (array) $menu_items as $key => $menu_item ) {
                                if ($menu_item->menu_item_parent && isset($hierarchical_menu[$menu_item->menu_item_parent])) {
                                    $hierarchical_menu[$menu_item->menu_item_parent]->submenu[] = $menu_item;
                                }
                            }

                            // Render the menu with custom structure
                            foreach ($hierarchical_menu as $menu_item) {
                                $has_children = !empty($menu_item->submenu);
                                ?>
                                <div class="mkx-mobile-menu__item">
                                    <?php if ($has_children) : ?>
                                        <button class="mkx-mobile-menu__toggle" aria-expanded="false">
                                            <?php echo esc_html($menu_item->title); ?>
                                        </button>
                                        <div class="mkx-mobile-submenu">
                                            <?php foreach ($menu_item->submenu as $child_item) : ?>
                                                <a href="<?php echo esc_url($child_item->url); ?>"><?php echo esc_html($child_item->title); ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url($menu_item->url); ?>" class="mkx-mobile-menu__toggle mkx-mobile-menu__link--single"><?php echo esc_html($menu_item->title); ?></a>
                                    <?php endif; ?>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<p style='padding: 20px;'>Пожалуйста, создайте меню и назначьте его на область 'Мобильное меню (аккордеон)' в админ-панели.</p>";
                        }
                        ?>
                    </div>

                    <!-- Mobile Menu Phone -->
                    <div class="mkx-mobile-menu__phone">
                        <?php if ( $phone ) : ?>
                            <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"
                               class="mkx-mobile-menu-phone-link">
                                <i class="ph ph-phone" aria-hidden="true"></i>
                                <span><?php echo esc_html( $phone ); ?></span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
</header>

<div id="content" class="mkx-site-content">
    <!-- Overlay for megamenu -->
    <div class="mkx-megamenu-overlay" id="megamenuOverlay" aria-hidden="true"></div>

    