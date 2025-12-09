<?php
/**
 * Admin Notices and Compatibility Checks
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
 * Проверка совместимости с плагинами
 */
add_action( 'admin_notices', 'shoptimizer_child_compatibility_notices' );
function shoptimizer_child_compatibility_notices() {
    // Проверяем наличие WooCommerce
    if ( ! class_exists( 'WooCommerce' ) ) {
        echo '<div class="notice notice-warning"><p>';
        echo __( 'Внимание: Дочерняя тема Shoptimizer Child оптимизирована для работы с WooCommerce. Рекомендуется установить и активировать плагин WooCommerce.', 'shoptimizer-child' );
        echo '</p></div>';
    }

    // Проверяем наличие родительской темы
    if ( ! is_child_theme() ) {
        echo '<div class="notice notice-error"><p>';
        echo __( 'Ошибка: Эта тема является дочерней темой и требует родительскую тему Shoptimizer.', 'shoptimizer-child' );
        echo '</p></div>';
    }
}
