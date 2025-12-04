<?php
/**
 * Enqueue Styles and Scripts
 * Modular CSS architecture for better maintainability
 *
 * @package Shoptimizer Child
 * @version 1.6.0
 * @author KB
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Подключение стилей и скриптов
 */
add_action( 'wp_enqueue_scripts', 'shoptimizer_child_enqueue_styles_scripts' );
function shoptimizer_child_enqueue_styles_scripts() {
    // Dequeue parent theme quantity script to prevent conflicts
    wp_dequeue_script( 'shoptimizer-quantity' );
    wp_deregister_script( 'shoptimizer-quantity' );

    $theme = wp_get_theme();
    $parent_theme = $theme->parent();
    $version = $theme->get('Version');

    // Подключаем стили родительской темы
    wp_enqueue_style(
        'shoptimizer-style',
        get_template_directory_uri() . '/style.css',
        array(),
        $parent_theme ? $parent_theme->get('Version') : '1.0.0'
    );
    
    // Enqueue Phosphor Icons
    wp_enqueue_style(
        'phosphor-icons',
        'https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css',
        array(),
        '2.1.1'
    );

    // Enqueue all CSS files from the assets/css directory
    $css_files = array(
        'variables' => [],
        'base' => ['variables'],
        'main' => ['base'],
        'pages' => ['main'],
        'header' => ['main'],
        'mobile-nav' => ['header'],
        'footer' => ['main'],
        'hero-carousel' => ['main'],
        'catalog-section' => ['main'],
        'subcategory-grid' => ['main'],
        'archive-product' => ['main'],
        'shop-list-view' => ['main'],
        'mkz-shop-list-overrides' => ['shop-list-view'],
        'woo_base' => ['main'],
        'woo_category' => ['woo_base'],
        'woo_products' => ['woo_base'],
        'product-card-actions' => ['woo_products'],
        'woo_single' => ['woo_base'],
        'woo_widgets' => ['woo_base'],
        'woo_wishlist' => ['woo_base'],
        'responsive-mobile' => ['main'],
        'print' => ['main'],
    );

    foreach ($css_files as $file => $deps) {
        // Use a consistent version number for all child theme assets
        $file_version = ($file === 'responsive-mobile') ? time() : $version;
        wp_enqueue_style(
            'mkx-' . $file,
            get_stylesheet_directory_uri() . '/assets/css/' . $file . '.css',
            array_map(function($dep) { return 'mkx-' . $dep; }, $deps),
            $file_version
        );
    }

    if ( is_cart() ) {
        wp_enqueue_style(
            'mkx-custom-cart',
            get_stylesheet_directory_uri() . '/assets/css/custom-cart.css',
            array('mkx-woo_base'),
            $version
        );
    }

    if ( is_checkout() ) {
        wp_enqueue_style(
            'mkx-custom-checkout',
            get_stylesheet_directory_uri() . '/assets/css/custom-checkout.css',
            array('mkx-woo_base'),
            $version
        );
    }

    // Подключаем JavaScript для шапки
    wp_enqueue_script(
        'mkx-header-script',
        get_stylesheet_directory_uri() . '/assets/js/header.js',
        array(),
        $version,
        true
    );

    // Подключаем JavaScript для Hero-карусели
    wp_enqueue_script(
        'mkx-hero-carousel-script',
        get_stylesheet_directory_uri() . '/assets/js/hero-carousel.js',
        array(),
        $version,
        true
    );

    // Подключаем JavaScript для футера (CF7 формы и уведомления)
    wp_enqueue_script(
        'mkx-footer-script',
        get_stylesheet_directory_uri() . '/assets/js/footer.js',
        array(),
        $version,
        true
    );

    // Подключаем JavaScript для аккордеона в футере
    wp_enqueue_script(
        'mkx-footer-accordion-script',
        get_stylesheet_directory_uri() . '/assets/js/footer-accordion.js',
        array(),
        $version,
        true
    );

    // Подключаем JavaScript для кнопок +/- в карточке товара
    wp_enqueue_script(
        'mkx-quantity-handler-script',
        get_stylesheet_directory_uri() . '/assets/js/quantity-handler.js',
        array(),
        $version,
        true
    );

    // Подключаем JavaScript для фильтров (drawer functionality)
    wp_enqueue_script(
        'mkx-filters-script',
        get_stylesheet_directory_uri() . '/assets/js/filters.js',
        array(),
        $version,
        true
    );

    // Передаем данные в JavaScript для шапки
    wp_localize_script( 'mkx-header-script', 'mkxHeader', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'mkx_header_nonce' ),
        'isLoggedIn' => is_user_logged_in(),
        'strings' => array(
            'searchPlaceholder' => __( 'Поиск товара...', 'shoptimizer-child' ),
            'cartEmpty' => __( 'Корзина пуста', 'shoptimizer-child' ),
            'menuOpen' => __( 'Открыть меню', 'shoptimizer-child' ),
            'menuClose' => __( 'Закрыть меню', 'shoptimizer-child' ),
        )
    ) );

    // Передаем данные в JavaScript для карусели
    wp_localize_script( 'mkx-hero-carousel-script', 'mkxCarousel', array(
        'strings' => array(
            'prevSlide' => __( 'Предыдущий слайд', 'shoptimizer-child' ),
            'nextSlide' => __( 'Следующий слайд', 'shoptimizer-child' ),
            'slideLabel' => __( 'Слайд', 'shoptimizer-child' ),
            'carouselLabel' => __( 'Карусель изображений', 'shoptimizer-child' ),
        ),
        'settings' => array(
            'autoplayInterval' => 5000,
            'pauseOnHover' => true,
            'enableSwipe' => true,
            'minSwipeDistance' => 50,
        )
    ) );

    // Передаем данные в JavaScript для футера
    wp_localize_script( 'mkx-footer-script', 'mkxFooter', array(
        'strings' => array(
            'emailPlaceholder' => __( 'Введите email', 'shoptimizer-child' ),
            'subscribeSuccess' => __( 'Спасибо за подписку!', 'shoptimizer-child' ),
            'subscribeError' => __( 'Ошибка подписки. Попробуйте позже.', 'shoptimizer-child' ),
            'validationError' => __( 'Проверьте правильность заполнения полей', 'shoptimizer-child' ),
            'spamDetected' => __( 'Обнаружена подозрительная активность', 'shoptimizer-child' ),
        ),
        'settings' => array(
            'hideSuccessDelay' => 5000,
            'hideErrorDelay' => 8000,
            'hideSpamDelay' => 10000,
            'resetFormDelay' => 2000,
        )
    ) );
}

/**
 * Хуки для оптимизации производительности
 */
add_action( 'wp_enqueue_scripts', 'shoptimizer_child_defer_scripts', 999 );
function shoptimizer_child_defer_scripts() {
    // Добавляем атрибут defer к нашим скриптам для оптимизации
    add_filter( 'script_loader_tag', function( $tag, $handle ) {
        if ( in_array( $handle, array( 'mkx-header-script', 'mkx-hero-carousel-script', 'mkx-footer-script', 'mkx-quantity-handler-script', 'mkx-footer-accordion-script', 'mkx-filters-script' ) ) ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }, 10, 2 );
}