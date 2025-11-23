<?php
/**
 * Скрипт для изменения иерархии категорий WooCommerce.
 * Запуск: Поместите этот файл в корень сайта (например, change-categories.php) и вызовите по URL: https://yoursite.com/change-categories.php
 * Удалите файл после выполнения!
 *
 * Предполагаемая новая структура: Бренд (top-level) > Тип запчасти (подкатегория).
 * Бренды: huawei-honor, iphone, samsung, xiaomi-redmi, nokia, infinix, oppo, realme, tecno, vivo.
 *
 * Требования: WordPress + WooCommerce. SEO игнорируется (нет редиректов).
 *
 * Безопасность: Добавьте пароль (измените $password ниже) для запуска.
 */

if (!defined('ABSPATH')) {
	require_once(dirname(__FILE__) . '/wp-load.php');
}

// Простая защита: укажите пароль в $_GET['key']
$password = '34_Gila'; // Измените на свой!
if (!isset($_GET['key']) || $_GET['key'] !== $password) {
	die('Доступ запрещен. Используйте ?key=your-secret-password');
}

// Включаем отладку (опционально)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Логирование
function log_message($message) {
	echo date('Y-m-d H:i:s') . ' - ' . $message . '<br>';
	error_log($message);
}

log_message('Начало изменения иерархии категорий.');

// Получаем таксономию product_cat
$taxonomy = 'product_cat';

// Определяем slugs брендов (top-level категории)
$brands = [
	'huawei-honor' => 'Huawei, Honor',
	'iphone' => 'iPhone',
	'samsung' => 'Samsung',
	'xiaomi-redmi' => 'Xiaomi, Redmi',
	'nokia' => 'Nokia',
	'infinix' => 'Infinix',
	'oppo' => 'Oppo',
	'realme' => 'Realme',
	'tecno' => 'Tecno',
	'vivo' => 'Vivo'
];

// Создаем бренд-категории, если их нет (top-level, parent=0)
foreach ($brands as $slug => $name) {
	$term = get_term_by('slug', $slug, $taxonomy);
	if (!$term) {
		$new_term = wp_insert_term($name, $taxonomy, ['slug' => $slug]);
		if (is_wp_error($new_term)) {
			log_message('Ошибка создания бренда ' . $slug . ': ' . $new_term->get_error_message());
		} else {
			log_message('Создан бренд: ' . $name . ' (ID: ' . $new_term['term_id'] . ')');
		}
	} else {
		log_message('Бренд ' . $name . ' уже существует (ID: ' . $term->term_id . ')');
	}
}

// Маппинг: текущие подкатегории -> бренд-slug
// На основе вашей исходной структуры (тип > бренд)
$category_mapping = [
	// Аккумуляторы
	'akb-dlya-huawei-honor' => 'huawei-honor',
	'akb-dlya-ipad' => 'iphone', // Предполагаем iPad под iPhone
	'akb-dlya-iphone' => 'iphone',
	'akb-dlya-nokia' => 'nokia',
	'akb-dlya-samsung' => 'samsung',
	'akb-dlya-xiaomi-redmi' => 'xiaomi-redmi',

	// Дисплеи
	'displei-huawei-honor' => 'huawei-honor',
	'displei-infinix' => 'infinix',
	'displei-iphone' => 'iphone',
	'displei-oppo' => 'oppo',
	'displeirealme' => 'realme', // Опечатка в исходном: displeirealme -> realme
	'displei-samsung' => 'samsung',
	'displei-tecno' => 'tecno',
	'displei-vivo' => 'vivo',
	'displei-xiaomi-redmi' => 'xiaomi-redmi',

	// Задние крышки и т.д.
	'dlya-huawei-honor' => 'huawei-honor', // Для задних крышек
	'dlya-iphone' => 'iphone',
	'dlya-samsung' => 'samsung',
	'dlya-xiaomi-redmi' => 'xiaomi-redmi',

	// Стекла/Таچскрины
	'для-huawei-honor' => 'huawei-honor', // Опечатка в исходном: для-huawei-honor
	'для-infinix' => 'infinix',
	'для-oppo' => 'oppo',
	'для-tecno' => 'tecno',
	'для-vivo' => 'vivo',
	'для-xiaomi' => 'xiaomi-redmi',
	'dlya-iphone-steklo-tachskrin-dlya-perekleiki' => 'iphone',
	'dlya-realme' => 'realme',

	// Шлейфы и платы
	'mezhplatnyi-shleif-huawei-honor' => 'huawei-honor',
	'mezhplatnyi-shleif-samsung' => 'samsung',
	'mezhplatnyi-shleif-xiaomi-redmi' => 'xiaomi-redmi',
	'shleif-zaryadki-iphone' => 'iphone',
	'plata-zaryadki-huawei-honor' => 'huawei-honor',
	'plata-zaryadki-samsung' => 'samsung',
	'plata-zaryadki-xiaomi-redmi' => 'xiaomi-redmi',

	// Другие (аксессуары, оборудование и т.д. - если нужно, добавьте)
	'szu' => null, // Общее, оставить без parent
	'dinamiki-dlya-iphone' => 'iphone', // Запчасти для звука
	'sprei-zhidkosti-ochistiteli' => null,
	'prochee' => null,
];

// Изменяем parent для каждой категории
$updated = 0;
$errors = 0;

foreach ($category_mapping as $old_slug => $brand_slug) {
	$term = get_term_by('slug', $old_slug, $taxonomy);
	if (!$term) {
		log_message('Категория не найдена: ' . $old_slug);
		$errors++;
		continue;
	}

	if ($brand_slug === null) {
		// Оставить как top-level
		$new_parent = 0;
	} else {
		$brand_term = get_term_by('slug', $brand_slug, $taxonomy);
		if (!$brand_term) {
			log_message('Бренд не найден для ' . $old_slug . ': ' . $brand_slug);
			$errors++;
			continue;
		}
		$new_parent = $brand_term->term_id;
	}

	// Проверяем, если parent уже правильный
	if ($term->parent == $new_parent) {
		log_message('Категория ' . $term->name . ' уже имеет правильный parent.');
		continue;
	}

	// Обновляем parent
	$result = wp_update_term($term->term_id, $taxonomy, ['parent' => $new_parent]);
	if (is_wp_error($result)) {
		log_message('Ошибка обновления ' . $term->name . ': ' . $result->get_error_message());
		$errors++;
	} else {
		log_message('Обновлен parent для ' . $term->name . ' (ID: ' . $term->term_id . ') -> ' . get_term($new_parent, $taxonomy)->name);
		$updated++;
	}
}

// Опционально: удаляем пустые категории (с 0 товаров)
log_message('Проверка пустых категорий для удаления...');
$all_terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
foreach ($all_terms as $term) {
	if (get_term_children($term->term_id, $taxonomy) || !empty(get_objects_in_term($term->term_id, $taxonomy))) {
		continue; // Не пустая
	}
	if ($term->count == 0 && $term->parent == 0) { // Только top-level пустые, или все?
		$result = wp_delete_term($term->term_id, $taxonomy);
		if (is_wp_error($result)) {
			log_message('Не удалось удалить пустую категорию ' . $term->name . ': ' . $result->get_error_message());
		} else {
			log_message('Удалена пустая категория: ' . $term->name);
		}
	}
}

log_message('Завершено. Обновлено категорий: ' . $updated . '. Ошибок: ' . $errors . '.');

// Очистка
flush_rewrite_rules(); // Обновляем пермалинки
log_message('Пермалинки обновлены.');

echo '<hr><strong>Скрипт завершен!</strong> Проверьте Products > Categories в админке. Удалите этот файл после использования.';
?>