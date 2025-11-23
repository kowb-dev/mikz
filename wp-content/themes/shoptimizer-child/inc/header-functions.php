<?php
/**
 * Header Functions for Shoptimizer Child Theme
 *
 * @package Shoptimizer Child
 * @version 1.0.2
 * @author KW
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Получение данных мега-меню каталога
 *
 * @return array Структурированные данные для мега-меню
 */
function mkx_get_catalog_megamenu_data() {
	// Кэшируем данные мега-меню
	$cache_key = 'mkx_catalog_megamenu_data';
	$cached_data = get_transient( $cache_key );

	if ( false !== $cached_data ) {
		return $cached_data;
	}

	$catalog_data = array(
		array(
			'title' => __( 'Аккумуляторы', 'shoptimizer-child' ),
			'items' => array(
				array( 'title' => __( 'АКБ для Huawei, Honor', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-huawei-honor' ) ),
				array( 'title' => __( 'АКБ для iPad', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-ipad' ) ),
				array( 'title' => __( 'АКБ для iPhone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-iphone' ) ),
				array( 'title' => __( 'АКБ для Nokia', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-nokia' ) ),
				array( 'title' => __( 'АКБ для Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-samsung' ) ),
				array( 'title' => __( 'АКБ для Xiaomi, Redmi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-xiaomi-redmi' ) ),
			)
		),
		array(
			'title' => __( 'Дисплей/Экраны/LCD', 'shoptimizer-child' ),
			'items' => array(
				array( 'title' => __( 'Дисплей Huawei, Honor', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-huawei-honor' ) ),
				array( 'title' => __( 'Дисплей Infinix', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-infinix' ) ),
				array( 'title' => __( 'Дисплей iPhone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-iphone' ) ),
				array( 'title' => __( 'Дисплей Oppo', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-oppo' ) ),
				array( 'title' => __( 'Дисплей Realme', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displeirealme' ) ),
				array( 'title' => __( 'Дисплей Redmi, Xiaomi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-redmi-xiaomi' ) ),
				array( 'title' => __( 'Дисплей Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-samsung' ) ),
				array( 'title' => __( 'Дисплей Tecno', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-tecno' ) ),
				array( 'title' => __( 'Дисплей Vivo', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-vivo' ) ),
			)
		),
		array(
			'title' => __( 'Задняя крышка, Рамка, Корпус', 'shoptimizer-child' ),
			'items' => array(
				array( 'title' => __( 'Для Honor, Huawei', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-honor-huawei' ) ),
				array( 'title' => __( 'Для Iphone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-iphone' ) ),
				array( 'title' => __( 'Для Redmi, Xiaomi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-redmi-xiaomi' ) ),
				array( 'title' => __( 'Для Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-samsung' ) ),
			)
		),
		array(
			'title' => __( 'Шлейфы Платы', 'shoptimizer-child' ),
			'items' => array(
				array( 'title' => __( 'Межплатный шлейф Honor Huawei', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'mezhplatnyi-shleif-honor-huawei' ) ),
				array( 'title' => __( 'Межплатный шлейф Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'mezhplatnyi-shleif-samsung' ) ),
				array( 'title' => __( 'Межплатный шлейф Xiaomi Redmi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'mezhplatnyi-shleif-xiaomi-redmi' ) ),
				array( 'title' => __( 'Плата зарядки Honor Huawei', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'plata-zaryadki-honor-huawei' ) ),
				array( 'title' => __( 'Плата зарядки Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'plata-zaryadki-samsung' ) ),
				array( 'title' => __( 'Плата зарядки Xiaomi Redmi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'plata-zaryadki-xiaomi-redmi' ) ),
				array( 'title' => __( 'Шлейф зарядки Iphone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'shleif-zaryadki-iphone' ) ),
			)
		)
	);

	// Кэшируем на 1 час
	set_transient( $cache_key, $catalog_data, HOUR_IN_SECONDS );

	return $catalog_data;
}

/**
 * Получение ссылки на категорию по slug
 *
 * @param string $slug Slug категории
 * @return string URL категории или #
 */
function get_term_link_by_slug( $slug ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return '#';
	}

	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		return get_term_link( $term );
	}

	return '#';
}

/**
 * Получение данных главного меню
 *
 * @return array Структурированные данные меню
 */
function mkx_get_main_menu_data() {
	$cache_key = 'mkx_main_menu_data';
	$cached_data = get_transient( $cache_key );

	if ( false !== $cached_data ) {
		return $cached_data;
	}

	$menu_data = array(
		array(
			'title' => __( 'HONOR/HUAWEI', 'shoptimizer-child' ),
			'url' => get_term_link_by_slug( 'dlya-honor-huawei' ),
			'submenu' => array(
				array( 'title' => __( 'Межплатный шлейф Honor Huawei', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'mezhplatnyi-shleif-honor-huawei' ) ),
				array( 'title' => __( 'Плата зарядки Honor Huawei', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'plata-zaryadki-honor-huawei' ) ),
				array( 'title' => __( 'Для Honor, Huawei (корпуса)', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-honor-huawei' ) ),
				array( 'title' => __( 'Дисплей Huawei, Honor', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-huawei-honor' ) ),
				array( 'title' => __( 'АКБ для Huawei, Honor', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-huawei-honor' ) ),
			)
		),
		array(
			'title' => __( 'SAMSUNG', 'shoptimizer-child' ),
			'url' => get_term_link_by_slug( 'dlya-samsung' ),
			'submenu' => array(
				array( 'title' => __( 'Межплатный шлейф Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'mezhplatnyi-shleif-samsung' ) ),
				array( 'title' => __( 'Плата зарядки Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'plata-zaryadki-samsung' ) ),
				array( 'title' => __( 'Для Samsung (корпуса)', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-samsung' ) ),
				array( 'title' => __( 'Дисплей Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-samsung' ) ),
				array( 'title' => __( 'АКБ для Samsung', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-samsung' ) ),
			)
		),
		array(
			'title' => __( 'XIAOMI/REDMI', 'shoptimizer-child' ),
			'url' => get_term_link_by_slug( 'dlya-redmi-xiaomi' ),
			'submenu' => array(
				array( 'title' => __( 'Межплатный шлейф Xiaomi Redmi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'mezhplatnyi-shleif-xiaomi-redmi' ) ),
				array( 'title' => __( 'Плата зарядки Xiaomi Redmi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'plata-zaryadki-xiaomi-redmi' ) ),
				array( 'title' => __( 'Для Redmi, Xiaomi (корпуса)', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-redmi-xiaomi' ) ),
				array( 'title' => __( 'Дисплей Redmi, Xiaomi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-redmi-xiaomi' ) ),
				array( 'title' => __( 'АКБ для Xiaomi, Redmi', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-xiaomi-redmi' ) ),
			)
		),
		array(
			'title' => __( 'IPHONE', 'shoptimizer-child' ),
			'url' => get_term_link_by_slug( 'dlya-iphone' ),
			'submenu' => array(
				array( 'title' => __( 'Шлейф зарядки Iphone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'shleif-zaryadki-iphone' ) ),
				array( 'title' => __( 'Для Iphone (корпуса)', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dlya-iphone' ) ),
				array( 'title' => __( 'Дисплей iPhone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-iphone' ) ),
				array( 'title' => __( 'Динамики, Звонки, Вибро для iPhone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'dinamiki-dlya-iphone' ) ),
				array( 'title' => __( 'АКБ для iPhone', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-iphone' ) ),
			)
		),
		array(
			'title' => __( 'ДРУГИЕ БРЕНДЫ', 'shoptimizer-child' ),
			'url' => get_term_link_by_slug( 'prochee' ),
			'submenu' => array(
				array( 'title' => __( 'Дисплей Infinix', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-infinix' ) ),
				array( 'title' => __( 'Дисплей Oppo', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-oppo' ) ),
				array( 'title' => __( 'Дисплей Realme', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displeirealme' ) ),
				array( 'title' => __( 'Дисплей Tecno', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-tecno' ) ),
				array( 'title' => __( 'Дисплей Vivo', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'displei-vivo' ) ),
				array( 'title' => __( 'АКБ для iPad', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-ipad' ) ),
				array( 'title' => __( 'АКБ для Nokia', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'akb-dlya-nokia' ) ),
			)
		),
		array(
			'title' => __( 'ОБОРУДОВАНИЕ', 'shoptimizer-child' ),
			'url' => get_term_link_by_slug( 'oborudovanie-instrumenty' ),
			'submenu' => array(
				array( 'title' => __( 'Спреи, жидкости, очистители', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'sprei-zhidkosti-ochistiteli' ) ),
			)
		),
		array(
			'title' => __( 'АКСЕССУАРЫ', 'shoptimizer-child' ),
			'url' => get_term_link_by_slug( 'aksessuary' ),
			'submenu' => array(
				array( 'title' => __( 'СЗУ', 'shoptimizer-child' ), 'url' => get_term_link_by_slug( 'szu' ) ),
			)
		)
	);

	// Кэшируем на 1 час
	set_transient( $cache_key, $menu_data, HOUR_IN_SECONDS );

	return $menu_data;
}

/**
 * Вывод мега-меню каталога
 */
function mkx_render_catalog_megamenu() {
	$catalog_columns = mkx_get_catalog_megamenu_data();

	echo '<div class="mkx-catalog-megamenu__content">';

	foreach ( $catalog_columns as $column ) {
		echo '<div class="mkx-catalog-megamenu__column">';
		echo '<h3 class="mkx-catalog-megamenu__title">' . esc_html( $column['title'] ) . '</h3>';
		echo '<ul class="mkx-catalog-megamenu__list">';

		foreach ( $column['items'] as $item ) {
			echo '<li>';
			echo '<a href="' . esc_url( $item['url'] ) . '" class="mkx-catalog-megamenu__link" role="menuitem">';
			echo esc_html( $item['title'] );
			echo '</a>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Вывод главного меню
 */
function mkx_render_main_menu() {
	$menu_items = mkx_get_main_menu_data();

	echo '<ul class="mkx-primary-menu" id="primary-menu" role="menubar">';

	foreach ( $menu_items as $item ) {
		$has_submenu = ! empty( $item['submenu'] );
		$item_classes = 'mkx-menu-item' . ( $has_submenu ? ' mkx-menu-item--has-dropdown' : '' );

		echo '<li class="' . esc_attr( $item_classes ) . '" role="none">';
		echo '<a href="' . esc_url( $item['url'] ) . '" class="mkx-menu-item__link" role="menuitem"';

		if ( $has_submenu ) {
			echo ' aria-haspopup="true" aria-expanded="false"';
		}

		echo '>' . esc_html( $item['title'] ) . '</a>';

		if ( $has_submenu ) {
			echo '<ul class="mkx-dropdown-menu" role="menu" aria-label="' . esc_attr( $item['title'] ) . '">';

			foreach ( $item['submenu'] as $subitem ) {
				echo '<li role="none">';
				echo '<a href="' . esc_url( $subitem['url'] ) . '" class="mkx-dropdown-menu__link" role="menuitem">';
				echo esc_html( $subitem['title'] );
				echo '</a>';
				echo '</li>';
			}

			echo '</ul>';
		}

		echo '</li>';
	}

	echo '</ul>';
}

/**
 * Получение информации о корзине для AJAX
 */
function mkx_get_cart_info() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array(
			'count' => 0,
			'total' => '',
			'url' => '#'
		);
	}

	return array(
		'count' => WC()->cart->get_cart_contents_count(),
		'total' => WC()->cart->get_cart_total(),
		'url' => wc_get_cart_url()
	);
}

/**
 * AJAX обработчик для обновления корзины
 */
add_action( 'wp_ajax_mkx_update_cart_info', 'mkx_ajax_update_cart_info' );
add_action( 'wp_ajax_nopriv_mkx_update_cart_info', 'mkx_ajax_update_cart_info' );
function mkx_ajax_update_cart_info() {
	// Проверяем nonce
	if ( ! wp_verify_nonce( $_POST['nonce'], 'mkx_header_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	$cart_info = mkx_get_cart_info();
	wp_send_json_success( $cart_info );
}

/**
 * Очистка кэша меню при изменении категорий
 */
add_action( 'created_product_cat', 'mkx_clear_menu_cache' );
add_action( 'edited_product_cat', 'mkx_clear_menu_cache' );
add_action( 'deleted_product_cat', 'mkx_clear_menu_cache' );
function mkx_clear_menu_cache() {
	delete_transient( 'mkx_catalog_megamenu_data' );
	delete_transient( 'mkx_main_menu_data' );
}

/**
 * Добавление контактной информации в customizer
 */
add_action( 'customize_register', 'mkx_customize_register_contacts' );
function mkx_customize_register_contacts( $wp_customize ) {
	// Секция контактов
	$wp_customize->add_section( 'mkx_contacts', array(
		'title' => __( 'Контактная информация', 'shoptimizer-child' ),
		'priority' => 120,
	) );

	// Телефон
	$wp_customize->add_setting( 'mkx_phone', array(
		'default' => '+1234567890',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( 'mkx_phone', array(
		'label' => __( 'Номер телефона', 'shoptimizer-child' ),
		'section' => 'mkx_contacts',
		'type' => 'text',
	) );

	// WhatsApp
	$wp_customize->add_setting( 'mkx_whatsapp', array(
		'default' => '+1234567890',
		'sanitize_callback' => 'sanitize_text_field',
	) );

	$wp_customize->add_control( 'mkx_whatsapp', array(
		'label' => __( 'WhatsApp номер', 'shoptimizer-child' ),
		'section' => 'mkx_contacts',
		'type' => 'text',
	) );
}

/**
 * Получение номера телефона
 */
function mkx_get_phone_number() {
	return get_theme_mod( 'mkx_phone', '+1234567890' );
}

/**
 * Получение WhatsApp номера
 */
function mkx_get_whatsapp_number() {
	return get_theme_mod( 'mkx_whatsapp', '+1234567890' );
}

/**
 * Генерация WhatsApp ссылки
 */
function mkx_get_whatsapp_link( $message = '' ) {
	$number = mkx_get_whatsapp_number();
	$clean_number = preg_replace( '/[^0-9]/', '', $number );

	$url = 'https://wa.me/' . $clean_number;

	if ( ! empty( $message ) ) {
		$url .= '?text=' . urlencode( $message );
	}

	return $url;
}

/**
 * Проверка активной страницы для мобильной навигации
 */
function mkx_is_nav_item_active( $item_type ) {
	switch ( $item_type ) {
		case 'home':
			return is_front_page();
		case 'catalog':
			return is_shop() || is_product_category() || is_product_tag();
		case 'wishlist':
			return function_exists( 'YITH_WCWL' ) && YITH_WCWL()->get_wishlist_url() === get_permalink();
		case 'profile':
			return is_account_page();
		case 'cart':
			return is_cart();
		default:
			return false;
	}
}

/**
 * Добавление активного класса для мобильной навигации
 */
function mkx_get_mobile_nav_item_class( $item_type ) {
	$classes = array( 'mkx-mobile-nav-item', 'mkx-mobile-nav-item--' . $item_type );

	if ( mkx_is_nav_item_active( $item_type ) ) {
		$classes[] = 'mkx-mobile-nav-item--active';
	}

	return implode( ' ', $classes );
}

/**
 * Улучшение SEO для мега-меню
 */
add_action( 'wp_head', 'mkx_megamenu_structured_data' );
function mkx_megamenu_structured_data() {
	if ( ! is_front_page() && ! is_shop() ) {
		return;
	}

	$catalog_data = mkx_get_catalog_megamenu_data();
	$structured_data = array(
		'@context' => 'https://schema.org',
		'@type' => 'SiteNavigationElement',
		'name' => get_bloginfo( 'name' ),
		'url' => home_url(),
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $structured_data ) . '</script>';
}