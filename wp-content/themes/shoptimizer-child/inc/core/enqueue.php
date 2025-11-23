<?php
/**
 * Enqueue Styles and Scripts
 *
 * @package Shoptimizer Child
 * @version 1.0.8
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

	// Подключаем стили шапки
	wp_enqueue_style(
		'mkx-header-style',
		get_stylesheet_directory_uri() . '/assets/css/header.css',
		array( 'shoptimizer-style' ),
		$version
	);

	// Подключаем стили мобильной навигации
	wp_enqueue_style(
		'mkx-mobile-nav-style',
		get_stylesheet_directory_uri() . '/assets/css/mobile-nav.css',
		array( 'mkx-header-style' ),
		$version
	);

	// Подключаем стили Hero-секции
	wp_enqueue_style(
		'mkx-hero-carousel-style',
		get_stylesheet_directory_uri() . '/assets/css/hero-carousel.css',
		array( 'mkx-header-style', 'mkx-mobile-nav-style' ),
		$version
	);

	// Подключаем стили каталога
	wp_enqueue_style(
		'mkx-catalog-section-style',
		get_theme_file_uri('/assets/css/catalog-section.css'),
		array(),
		$version
	);

	// Подключаем стили для сетки подкатегорий
	wp_enqueue_style(
		'mkx-subcategory-grid-style',
		get_stylesheet_directory_uri() . '/assets/css/subcategory-grid.css',
		array( 'shoptimizer-style' ),
		$version
	);

	// Подключаем стили футера
	wp_enqueue_style(
		'mkx-footer-style',
		get_stylesheet_directory_uri() . '/assets/css/footer.css',
		array( 'shoptimizer-style' ),
		$version
	);

	// Подключаем стили для аккордеона в футере
	wp_enqueue_style(
		'mkx-footer-accordion-style',
		get_stylesheet_directory_uri() . '/assets/css/footer-accordion.css',
		array( 'mkx-footer-style' ),
		$version
	);

	// Подключаем основные стили дочерней темы
	wp_enqueue_style(
		'shoptimizer-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'mkx-header-style', 'mkx-mobile-nav-style', 'mkx-hero-carousel-style', 'mkx-footer-style' ),
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

	// Подключаем стили для исправления отображения списка товаров
	wp_enqueue_style(
		'mkz-shop-list-overrides',
		get_stylesheet_directory_uri() . '/assets/css/mkz-shop-list-overrides.css',
		array( 'shoptimizer-child-style' ),
		$version
	);

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