<?php
/**
 * Enqueue Styles and Scripts
 * Modular CSS architecture for better maintainability
 *
 * @package Shoptimizer Child
 * @version 1.1.0
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

    // 1. CSS Variables (load FIRST - foundation for all other styles)
    wp_enqueue_style(
        'mkx-variables',
        get_stylesheet_directory_uri() . '/assets/css/01-variables.css',
        array( 'shoptimizer-style' ),
        $version
    );

    // 2. Layout (global containers and structure)
    wp_enqueue_style(
        'mkx-layout',
        get_stylesheet_directory_uri() . '/assets/css/02-layout.css',
        array( 'mkx-variables' ),
        $version
    );

    // 3. Utilities (helper classes, accessibility)
    wp_enqueue_style(
        'mkx-utilities',
        get_stylesheet_directory_uri() . '/assets/css/03-utilities.css',
        array( 'mkx-variables' ),
        $version
    );

    // 4. WooCommerce Grid (category/brand pages)
    wp_enqueue_style(
        'mkx-woocommerce-grid',
        get_stylesheet_directory_uri() . '/assets/css/04-woocommerce-grid.css',
        array( 'mkx-variables', 'mkx-layout' ),
        $version
    );

    // 5. WooCommerce List View (shop page desktop)
    wp_enqueue_style(
        'mkx-woocommerce-list',
        get_stylesheet_directory_uri() . '/assets/css/05-woocommerce-list.css',
        array( 'mkx-woocommerce-grid' ),
        $version
    );

    // 6. WooCommerce Base (notices, cart, general)
    wp_enqueue_style(
        'mkx-woocommerce-base',
        get_stylesheet_directory_uri() . '/assets/css/06-woocommerce-base.css',
        array( 'mkx-variables' ),
        $version
    );

    // 7. Header
    wp_enqueue_style(
        'mkx-header-style',
        get_stylesheet_directory_uri() . '/assets/css/header.css',
        array( 'mkx-variables', 'mkx-layout' ),
        $version
    );

    // 8. Mobile Navigation
    wp_enqueue_style(
        'mkx-mobile-nav-style',
        get_stylesheet_directory_uri() . '/assets/css/mobile-nav.css',
        array( 'mkx-header-style' ),
        $version
    );

    // 9. Hero Carousel
    wp_enqueue_style(
        'mkx-hero-carousel-style',
        get_stylesheet_directory_uri() . '/assets/css/hero-carousel.css',
        array( 'mkx-variables' ),
        $version
    );

    // 10. Catalog Section
    wp_enqueue_style(
        'mkx-catalog-section-style',
        get_stylesheet_directory_uri() . '/assets/css/catalog-section.css',
        array( 'mkx-variables' ),
        $version
    );

    // 11. Subcategory Grid
    wp_enqueue_style(
        'mkx-subcategory-grid-style',
        get_stylesheet_directory_uri() . '/assets/css/subcategory-grid.css',
        array( 'mkx-variables' ),
        $version
    );

    // 12. Footer
    wp_enqueue_style(
        'mkx-footer-style',
        get_stylesheet_directory_uri() . '/assets/css/footer.css',
        array( 'mkx-variables' ),
        $version
    );

    // 13. Footer Accordion
    wp_enqueue_style(
        'mkx-footer-accordion-style',
        get_stylesheet_directory_uri() . '/assets/css/footer-accordion.css',
        array( 'mkx-footer-style' ),
        $version
    );

    // 14. Mobile Responsive (CRITICAL - fixes mobile shop page)
    wp_enqueue_style(
        'mkx-responsive-mobile',
        get_stylesheet_directory_uri() . '/assets/css/08-responsive-mobile.css',
        array( 'mkx-woocommerce-grid', 'mkx-woocommerce-list' ),
        $version
    );

    // 15. Main Child Theme Style (minimal overrides only)
    wp_enqueue_style(
        'shoptimizer-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( 'mkx-variables', 'mkx-layout', 'mkx-woocommerce-grid', 'mkx-woocommerce-list', 'mkx-responsive-mobile' ),
        $version
    );

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
        if ( in_array( $handle, array( 'mkx-header-script', 'mkx-hero-carousel-script', 'mkx-footer-script', 'mkx-quantity-handler-script', 'mkx-footer-accordion-script' ) ) ) {
            return str_replace( ' src', ' defer src', $tag );
        }
        return $tag;
    }, 10, 2 );
}