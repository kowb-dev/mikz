<?php
/**
 * Функции дочерней темы Shoptimizer Child
 * Обновленная версия с исправленными модулями email
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
 * Подключение модульных файлов
 *
 * Файлы загружаются в определенном порядке для корректной работы
 * Сначала загружаем константы и утилиты, потом все остальное
 */

// Константы (загружаем первыми)
require_once get_stylesheet_directory() . '/inc/config/constants.php';

// Вспомогательные функции (загружаем рано для использования в других модулях)
require_once get_stylesheet_directory() . '/inc/helpers/utilities.php';
require_once get_stylesheet_directory() . '/inc/helpers/class-mkx-nav-walker.php';
require_once get_stylesheet_directory() . '/inc/helpers/class-mkx-mega-menu-walker.php';
require_once get_stylesheet_directory() . '/inc/helpers/class-mkx-mobile-nav-walker.php';

// Core функции (базовые настройки темы)
require_once get_stylesheet_directory() . '/inc/core/setup.php';
require_once get_stylesheet_directory() . '/inc/core/enqueue.php';
require_once get_stylesheet_directory() . '/inc/core/cleanup.php';
require_once get_stylesheet_directory() . '/inc/core/nav-menus.php';
require_once get_stylesheet_directory() . '/inc/core/sidebars.php';


// Конфигурация
require_once get_stylesheet_directory() . '/inc/config/menus.php';
require_once get_stylesheet_directory() . '/inc/config/images.php';
require_once get_stylesheet_directory() . '/inc/config/customizer-footer.php';
require_once get_stylesheet_directory() . '/inc/config/customizer-contact-info.php';
require_once get_stylesheet_directory() . '/inc/customizer-footer-add-settings.php';

// Функциональные возможности
require_once get_stylesheet_directory() . '/inc/features/mobile-optimization.php';
require_once get_stylesheet_directory() . '/inc/features/body-classes.php';
// require_once get_stylesheet_directory() . '/inc/features/contacts-page.php';
require_once get_stylesheet_directory() . '/inc/features/page-specific-styles.php';
require_once get_stylesheet_directory() . '/inc/features/blog-enhancements.php';
require_once get_stylesheet_directory() . '/inc/features/query-modifications.php';
require_once get_stylesheet_directory() . '/inc/features/wholesale-prices.php';
require_once get_stylesheet_directory() . '/inc/mobile-nav.php';
require_once get_stylesheet_directory() . '/inc/template-tags.php';
require_once get_stylesheet_directory() . '/inc/widget-clear-filters.php';
require_once get_stylesheet_directory() . '/inc/widget-custom-price-filter.php';


// Интеграции с плагинами (только если плагины активны)
if ( class_exists( 'WooCommerce' ) ) {
	require_once get_stylesheet_directory() . '/inc/integrations/woocommerce/functions.php';
    require_once get_stylesheet_directory() . '/inc/product-summary.php';
    require_once get_stylesheet_directory() . '/inc/yith-compare-fixes.php';
}

// Административные функции (только в админке)
if ( is_admin() ) {
	require_once get_stylesheet_directory() . '/inc/admin/notices.php';
}

/**
 * Подключение дополнительных файлов из существующей структуры
 * (если они существуют и не конфликтуют)
 */
$additional_files = array(
	'inc/enqueue-scripts.php',
	'inc/customizer.php',
	'inc/security.php',
);

// Осторожно подключаем header-functions.php только если в нем нет дублирующихся функций
$header_functions_file = get_stylesheet_directory() . '/inc/header-functions.php';
if ( file_exists( $header_functions_file ) ) {
	$header_content = file_get_contents( $header_functions_file );
	// Проверяем что файл не содержит дублирующуюся функцию
	if ( strpos( $header_content, 'function mkx_get_catalog_megamenu_data' ) === false ) {
		require_once $header_functions_file;
	}
}

foreach ( $additional_files as $file ) {
	$filepath = get_stylesheet_directory() . '/' . $file;
	        if ( file_exists( $filepath ) ) {
	    		require_once $filepath;
	    	}
	    }

/**
 * Register custom widgets.
 */
function mkx_register_custom_widgets() {
	register_widget( 'MKX_Widget_Clear_Filters' );
	register_widget( 'MKX_Widget_Custom_Price_Filter' );
}
add_action( 'widgets_init', 'mkx_register_custom_widgets' );

/**
 * Clear MKX Live Search cache when a product is saved or a category is edited.
 */
function mkx_clear_live_search_cache() {
    if (class_exists('MKX_Search_Query')) {
        MKX_Search_Query::instance()->clear_search_cache();
    }
}
add_action('save_post_product', 'mkx_clear_live_search_cache');
add_action('edited_product_cat', 'mkx_clear_live_search_cache');

require_once get_stylesheet_directory() . '/inc/shoptimizer-child-no-results.php';

