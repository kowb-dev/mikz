<?php
/**
 * Helper Utilities and Functions
 *
 * @package Shoptimizer Child
 * @version 1.0.3
 * @author KW
 * @link https://kowb.ru
 */

// Защита от прямого доступа
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Обработка ошибок и логирование
 */
if ( ! function_exists( 'mkx_log_error' ) ) {
    function mkx_log_error( $message, $data = null ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            error_log( 'MKX Theme Error: ' . $message );
            if ( $data ) {
                error_log( 'MKX Theme Data: ' . print_r( $data, true ) );
            }
        }
    }
}

/**
 * Получение данных каталога для мега-меню
 */
if ( ! function_exists( 'mkx_get_catalog_megamenu_data' ) ) {
    function mkx_get_catalog_megamenu_data() {
        // Кэшируем данные для производительности
        static $catalog_data = null;
        
        if ( $catalog_data === null ) {
            $catalog_data = array(
	            array(
		            'title' => __( 'Аккумуляторы', 'shoptimizer-child' ),
		            'items' => array(
			            array( 'title' => __( 'АКБ для Huawei, Honor', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'АКБ для iPad', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'АКБ для iPhone', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'АКБ для Nokia', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'АКБ для Samsung', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'АКБ для Xiaomi, Redmi', 'shoptimizer-child' ), 'url' => '#' ),
		            )
	            ),
	            array(
		            'title' => __( 'Дисплей/Экраны/LCD', 'shoptimizer-child' ),
		            'items' => array(
			            array( 'title' => __( 'Дисплей Huawei, Honor', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей Infinix', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей iPhone', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей Oppo', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей Realme', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей Redmi, Xiaomi', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей Samsung', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей Tecno', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Дисплей Vivo', 'shoptimizer-child' ), 'url' => '#' ),
		            )
	            ),
	            array(
		            'title' => __( 'Задняя крышка, Рамка, Корпус', 'shoptimizer-child' ),
		            'items' => array(
			            array( 'title' => __( 'Для Honor, Huawei', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Для Iphone', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Для Redmi, Xiaomi', 'shoptimizer-child' ), 'url' => '#' ),
			            array( 'title' => __( 'Для Samsung', 'shoptimizer-child' ), 'url' => '#' ),
		            )
	            ),
                array(
                    'title' => __( 'Шлейфа Платы', 'shoptimizer-child' ),
                    'items' => array(
                        array( 'title' => __( 'Межплатный шлейф Honor Huawei', 'shoptimizer-child' ), 'url' => '#' ),
                        array( 'title' => __( 'Межплатный шлейф Samsung', 'shoptimizer-child' ), 'url' => '#' ),
                        array( 'title' => __( 'Межплатный шлейф Xiaomi Redmi', 'shoptimizer-child' ), 'url' => '#' ),
                        array( 'title' => __( 'Плата зарядки Honor Huawei', 'shoptimizer-child' ), 'url' => '#' ),
                        array( 'title' => __( 'Плата зарядки Samsung', 'shoptimizer-child' ), 'url' => '#' ),
                        array( 'title' => __( 'Плата зарядки Xiaomi Redmi', 'shoptimizer-child' ), 'url' => '#' ),
                        array( 'title' => __( 'Шлейф зарядки Iphone', 'shoptimizer-child' ), 'url' => '#' ),
                    )
                ),
            );
        }
        
        return $catalog_data;
    }
}

/**
 * Безопасное получение опции темы
 */
if ( ! function_exists( 'mkx_get_theme_option' ) ) {
    function mkx_get_theme_option( $option_name, $default = '' ) {
        return get_theme_mod( $option_name, $default );
    }
}

/**
 * Проверка активности WooCommerce
 */
if ( ! function_exists( 'mkx_is_woocommerce_active' ) ) {
    function mkx_is_woocommerce_active() {
        return class_exists( 'WooCommerce' );
    }
}

/**
 * Получение URL страницы магазина
 */
if ( ! function_exists( 'mkx_get_shop_url' ) ) {
    function mkx_get_shop_url() {
        if ( mkx_is_woocommerce_active() ) {
            return get_permalink( wc_get_page_id( 'shop' ) );
        }
        return home_url( '/' );
    }
}

/**
 * Форматирование цены для отображения
 */
if ( ! function_exists( 'mkx_format_price' ) ) {
    function mkx_format_price( $price ) {
        if ( mkx_is_woocommerce_active() && function_exists( 'wc_price' ) ) {
            return wc_price( $price );
        }
        return number_format( $price, 2 ) . ' ₽';
    }
}

/**
 * Get a relevant Phosphor icon class based on a category slug.
 *
 * @param string $slug The category slug.
 * @return string The corresponding icon class.
 */
if ( ! function_exists( 'mkx_get_category_icon' ) ) {
    function mkx_get_category_icon( $slug ) {
        $icon_map = array(
            'mezhplatnyi-shleif' => 'ph-circuitry',
            'shleif-zaryadki' => 'ph-tree-structure',
            'displei' => 'ph-device-mobile-camera',
            'akb' => 'ph-battery-high',
            'akkumulyatory' => 'ph-battery-high',
            'korpus' => 'ph-device-mobile',
            'kryshka' => 'ph-device-mobile',
            'ramka' => 'ph-device-mobile',
            'shleif' => 'ph-plugs-connected',
            'shleify' => 'ph-plugs-connected',
            'plata-zaryadki' => 'ph-lightning',
            'kamery' => 'ph-camera',
            'instrumenty' => 'ph-wrench',
            'oborudovanie' => 'ph-wrench',
            'aksessuary' => 'ph-headphones',
            'zapchasti-dlya-zvuka' => 'ph-speaker-high',
            'dinamiki' => 'ph-speaker-high',
            'steklo-tachskrin' => 'ph-hand-tap',
            'prochee' => 'ph-dots-three',
            'sprei-zhidkosti-ochistiteli' => 'ph-drop',
        );

        // Check for partial matches
        foreach ( $icon_map as $key => $icon ) {
            if ( strpos( $slug, $key ) !== false ) {
                return $icon;
            }
        }

        return 'ph-wrench'; // Default icon
    }
}
