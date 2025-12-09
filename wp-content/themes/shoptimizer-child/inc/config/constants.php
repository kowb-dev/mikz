<?php
/**
 * Theme Constants
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
 * Безопасность - отключение редактирования файлов через админку
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
    define( 'DISALLOW_FILE_EDIT', true );
}
