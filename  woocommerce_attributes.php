<?php
/**
 * WooCommerce Product Attributes Script
 * Создает атрибуты и присваивает их товарам на основе заголовков
 * Оптимизировано для бюджетного shared хостинга
 */

// Запускаем только из корня сайта WordPress
if (!file_exists('./wp-config.php')) {
	die('Запустите скрипт из корневой папки WordPress!');
}

// Подключаем WordPress
require_once('./wp-config.php');
require_once('./wp-load.php');

// Проверяем, что WooCommerce активен
if (!function_exists('wc_get_products')) {
	die('WooCommerce не найден или не активен!');
}

// Увеличиваем лимиты для shared хостинга
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

class WC_Attributes_Processor {

	private $attributes_config = [
		'brand' => [
			'name' => 'Бренд',
			'slug' => 'pa_brand',
			'values' => ['Apple', 'Honor', 'Huawei', 'Infinix', 'iPad', 'iPhone', 'Nokia', 'Oppo', 'Redmi', 'Samsung', 'Tecno', 'Vivo', 'Wiko', 'Xiaomi']
		],
		'capacity' => [
			'name' => 'Емкость',
			'slug' => 'pa_capacity',
			'values' => ['2227 mAh', '2406 mAh', '3500 mAh', '3620 mAh', '3687 mAh', '3969 mAh', '4780 mAh', '4860 mAh']
		],
		'color' => [
			'name' => 'Цвет',
			'slug' => 'pa_color',
			'values' => ['Бежевый', 'Белый', 'Бирюзовый', 'Голубой', 'Графитовый', 'Зелёный', 'Золотой', 'Красный', 'Лаванда', 'Розовый', 'Серебро', 'Синий', 'Ультрамарин', 'Фиолетовый', 'Чёрный']
		],
		'part-type' => [
			'name' => 'Тип запчасти',
			'slug' => 'pa_part-type',
			'values' => ['Аккумулятор', 'Динамик', 'Дисплей', 'Корпус', 'Крышка', 'Плата', 'Рамка', 'Стекло', 'Тачскрин', 'Шлейф']
		],
		'quality' => [
			'name' => 'Качество',
			'slug' => 'pa_quality',
			'values' => ['Hard OLED', 'OLED', 'Soft OLED', 'Оригинал', 'Премиум', 'С олеофобным покрытием']
		]
	];

	private $processed_count = 0;
	private $batch_size = 50; // Обрабатываем по 50 товаров за раз

	public function run() {
		echo "<h1>Обработка атрибутов WooCommerce</h1>\n";
		echo "<p>Начинаем обработку...</p>\n";

		// Шаг 1: Создаем атрибуты
		$this->create_attributes();

		// Шаг 2: Обрабатываем товары батчами
		$this->process_products_in_batches();

		echo "<h2>Обработка завершена!</h2>\n";
		echo "<p>Обработано товаров: {$this->processed_count}</p>\n";
	}

	private function create_attributes() {
		echo "<h2>Создание атрибутов...</h2>\n";

		foreach ($this->attributes_config as $key => $config) {
			// Проверяем, существует ли атрибут
			$attribute_id = wc_attribute_taxonomy_id_by_name($config['slug']);

			if (!$attribute_id) {
				// Создаем новый атрибут
				$attribute = [
					'name' => $config['name'],
					'slug' => $config['slug'],
					'type' => 'select',
					'order_by' => 'menu_order',
					'has_archives' => false
				];

				$result = wc_create_attribute($attribute);

				if (is_wp_error($result)) {
					echo "<p style='color: red;'>Ошибка создания атрибута {$config['name']}: " . $result->get_error_message() . "</p>\n";
				} else {
					echo "<p style='color: green;'>Атрибут '{$config['name']}' создан успешно</p>\n";
				}
			} else {
				echo "<p>Атрибут '{$config['name']}' уже существует</p>\n";
			}

			// Создаем термины для атрибута
			$this->create_attribute_terms($config['slug'], $config['values']);
		}

		// Очищаем кеш после создания атрибутов
		wp_cache_flush();
		delete_transient('wc_attribute_taxonomies');
	}

	private function create_attribute_terms($taxonomy, $values) {
		foreach ($values as $value) {
			if (!term_exists($value, $taxonomy)) {
				$result = wp_insert_term($value, $taxonomy);
				if (is_wp_error($result)) {
					echo "<p style='color: orange;'>Предупреждение: не удалось создать термин '$value' для $taxonomy</p>\n";
				}
			}
		}
	}

	private function process_products_in_batches() {
		echo "<h2>Обработка товаров...</h2>\n";

		$offset = 0;
		$total_products = 0;

		do {
			// Получаем товары батчами
			$products = wc_get_products([
				'limit' => $this->batch_size,
				'offset' => $offset,
				'status' => 'publish'
			]);

			if (empty($products)) {
				break;
			}

			echo "<p>Обрабатываем товары " . ($offset + 1) . " - " . ($offset + count($products)) . "</p>\n";

			foreach ($products as $product) {
				$this->process_single_product($product);
				$this->processed_count++;
			}

			$offset += $this->batch_size;
			$total_products += count($products);

			// Принудительная очистка памяти
			if ($offset % 100 == 0) {
				wp_cache_flush();
				if (function_exists('gc_collect_cycles')) {
					gc_collect_cycles();
				}
			}

			// Небольшая пауза для shared хостинга
			usleep(100000); // 0.1 секунды

		} while (count($products) == $this->batch_size);

		echo "<p>Всего найдено товаров: $total_products</p>\n";
	}

	private function process_single_product($product) {
		$product_id = $product->get_id();
		$title = $product->get_name();

		$attributes = [];
		$found_attributes = [];

		// Поиск атрибутов в названии товара
		foreach ($this->attributes_config as $attr_key => $config) {
			$found_values = $this->find_attribute_values($title, $config['values']);

			if (!empty($found_values)) {
				$found_attributes[$config['slug']] = $found_values;

				// Создаем атрибут для товара
				$attribute = new WC_Product_Attribute();
				$attribute->set_id(wc_attribute_taxonomy_id_by_name($config['slug']));
				$attribute->set_name($config['slug']);
				$attribute->set_options($found_values);
				$attribute->set_position(0);
				$attribute->set_visible(true);
				$attribute->set_variation(false);

				$attributes[$config['slug']] = $attribute;
			}
		}

		// Присваиваем атрибуты товару
		if (!empty($attributes)) {
			// Получаем существующие атрибуты товара
			$existing_attributes = $product->get_attributes();

			// Объединяем с новыми атрибутами
			$all_attributes = array_merge($existing_attributes, $attributes);

			// Устанавливаем атрибуты
			$product->set_attributes($all_attributes);

			// Сохраняем товар
			$product->save();

			// Устанавливаем термины для таксономий
			foreach ($found_attributes as $taxonomy => $terms) {
				wp_set_object_terms($product_id, $terms, $taxonomy);
			}

			echo "<p>Товар ID $product_id: найдено атрибутов - " . count($attributes) . "</p>\n";
		}
	}

	private function find_attribute_values($title, $possible_values) {
		$found_values = [];
		$title_lower = mb_strtolower($title, 'UTF-8');

		foreach ($possible_values as $value) {
			$value_lower = mb_strtolower($value, 'UTF-8');

			// Поиск точного совпадения или частичного для брендов
			if (strpos($title_lower, $value_lower) !== false) {
				$found_values[] = $value;
			}

			// Дополнительная логика для брендов
			if (in_array($value, ['iPhone', 'iPad'])) {
				if (strpos($title_lower, mb_strtolower($value, 'UTF-8')) !== false) {
					if (!in_array($value, $found_values)) {
						$found_values[] = $value;
					}
				}
			}
		}

		return $found_values;
	}
}

// Защита от случайного запуска
if (!isset($_GET['run']) || $_GET['run'] !== 'attributes') {
	echo "<h1>WooCommerce Attributes Processor</h1>";
	echo "<p>Этот скрипт создаст атрибуты и присвоит их товарам на основе названий.</p>";
	echo "<p><strong>Внимание!</strong> Перед запуском сделайте резервную копию базы данных!</p>";
	echo "<p><a href='?run=attributes' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>Запустить обработку</a></p>";
	exit;
}

// Запуск обработки
try {
	$processor = new WC_Attributes_Processor();
	$processor->run();
} catch (Exception $e) {
	echo "<p style='color: red;'>Произошла ошибка: " . $e->getMessage() . "</p>";
	echo "<p>Попробуйте запустить скрипт еще раз.</p>";
}
?>