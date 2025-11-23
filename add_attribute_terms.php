<?php
/**
 * WooCommerce Attribute Terms and Assignment Script
 * Добавляет значения (термины) к существующим атрибутам и присваивает их товарам
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

class WC_Attribute_Terms_Processor {

	private $attributes_config = [
		'pa_brand' => [
			'name' => 'Бренд',
			'values' => ['Apple', 'Honor', 'Huawei', 'Infinix', 'iPad', 'iPhone', 'Nokia', 'Oppo', 'Redmi', 'Samsung', 'Tecno', 'Vivo', 'Wiko', 'Xiaomi']
		],
		'pa_capacity' => [
			'name' => 'Емкость',
			'values' => ['2227 mAh', '2406 mAh', '3500 mAh', '3620 mAh', '3687 mAh', '3969 mAh', '4780 mAh', '4860 mAh']
		],
		'pa_color' => [
			'name' => 'Цвет',
			'values' => ['Бежевый', 'Белый', 'Бирюзовый', 'Голубой', 'Графитовый', 'Зелёный', 'Золотой', 'Красный', 'Лаванда', 'Розовый', 'Серебро', 'Синий', 'Ультрамарин', 'Фиолетовый', 'Чёрный']
		],
		'pa_part-type' => [
			'name' => 'Тип запчасти',
			'values' => ['Аккумулятор', 'Динамик', 'Дисплей', 'Корпус', 'Крышка', 'Плата', 'Рамка', 'Стекло', 'Тачскрин', 'Шлейф']
		],
		'pa_quality' => [
			'name' => 'Качество',
			'values' => ['Hard OLED', 'OLED', 'Soft OLED', 'Оригинал', 'Премиум', 'С олеофобным покрытием']
		]
	];

	private $processed_count = 0;
	private $batch_size = 30; // Уменьшим размер батча для надежности

	public function run() {
		echo "<h1>Добавление значений атрибутов WooCommerce</h1>\n";
		echo "<p>Начинаем обработку...</p>\n";

		// Шаг 1: Создаем термины для атрибутов
		$this->create_attribute_terms();

		// Шаг 2: Присваиваем атрибуты товарам
		$this->process_products_in_batches();

		echo "<h2>Обработка завершена!</h2>\n";
		echo "<p>Обработано товаров: {$this->processed_count}</p>\n";
	}

	private function create_attribute_terms() {
		echo "<h2>Создание значений атрибутов...</h2>\n";

		foreach ($this->attributes_config as $taxonomy => $config) {
			echo "<h3>Обрабатываем атрибут: {$config['name']} ($taxonomy)</h3>\n";

			// Проверяем, существует ли таксономия
			if (!taxonomy_exists($taxonomy)) {
				echo "<p style='color: red;'>Таксономия $taxonomy не найдена! Сначала создайте атрибуты.</p>\n";
				continue;
			}

			$created_terms = 0;
			$existing_terms = 0;

			foreach ($config['values'] as $value) {
				// Проверяем, существует ли термин
				$term = get_term_by('name', $value, $taxonomy);

				if (!$term) {
					// Создаем термин
					$result = wp_insert_term($value, $taxonomy, [
						'slug' => sanitize_title($value)
					]);

					if (is_wp_error($result)) {
						echo "<p style='color: orange;'>Ошибка создания термина '$value': " . $result->get_error_message() . "</p>\n";
					} else {
						$created_terms++;
						echo "<p style='color: green;'>✓ Создан термин: '$value'</p>\n";
					}
				} else {
					$existing_terms++;
					echo "<p>• Термин '$value' уже существует</p>\n";
				}

				// Пауза для shared хостинга
				usleep(10000); // 0.01 секунды
			}

			echo "<p><strong>Итого для {$config['name']}: создано $created_terms, существовало $existing_terms</strong></p>\n";
		}

		// Очищаем кеш
		wp_cache_flush();
		echo "<p>Кеш очищен.</p>\n";
	}

	private function process_products_in_batches() {
		echo "<h2>Присваивание атрибутов товарам...</h2>\n";

		$offset = 0;
		$total_products = 0;
		$products_with_attributes = 0;

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
				$has_attributes = $this->process_single_product($product);
				if ($has_attributes) {
					$products_with_attributes++;
				}
				$this->processed_count++;
			}

			$offset += $this->batch_size;
			$total_products += count($products);

			// Принудительная очистка памяти каждые 60 товаров
			if ($offset % 60 == 0) {
				wp_cache_flush();
				if (function_exists('gc_collect_cycles')) {
					gc_collect_cycles();
				}
				echo "<p>Очистка памяти... (обработано $offset товаров)</p>\n";
			}

			// Пауза для shared хостинга
			usleep(200000); // 0.2 секунды

		} while (count($products) == $this->batch_size);

		echo "<p><strong>Всего товаров: $total_products</strong></p>\n";
		echo "<p><strong>Товаров с найденными атрибутами: $products_with_attributes</strong></p>\n";
	}

	private function process_single_product($product) {
		$product_id = $product->get_id();
		$title = $product->get_name();

		$product_attributes = [];
		$assigned_terms = [];
		$found_any = false;

		// Поиск атрибутов в названии товара
		foreach ($this->attributes_config as $taxonomy => $config) {
			$found_values = $this->find_attribute_values($title, $config['values']);

			if (!empty($found_values)) {
				$found_any = true;

				// Получаем ID терминов
				$term_ids = [];
				foreach ($found_values as $value) {
					$term = get_term_by('name', $value, $taxonomy);
					if ($term) {
						$term_ids[] = $term->term_id;
					}
				}

				if (!empty($term_ids)) {
					// Создаем атрибут для товара
					$attribute = new WC_Product_Attribute();
					$attribute->set_id(wc_attribute_taxonomy_id_by_name($taxonomy));
					$attribute->set_name($taxonomy);
					$attribute->set_options($term_ids);
					$attribute->set_position(array_search($taxonomy, array_keys($this->attributes_config)));
					$attribute->set_visible(true);
					$attribute->set_variation(false);

					$product_attributes[$taxonomy] = $attribute;
					$assigned_terms[$taxonomy] = $found_values;
				}
			}
		}

		// Присваиваем атрибуты товару
		if (!empty($product_attributes)) {
			// Получаем существующие атрибуты товара
			$existing_attributes = $product->get_attributes();

			// Объединяем с новыми атрибутами (новые перезаписывают старые)
			$all_attributes = array_merge($existing_attributes, $product_attributes);

			// Устанавливаем атрибуты
			$product->set_attributes($all_attributes);

			// Сохраняем товар
			$result = $product->save();

			if (is_wp_error($result)) {
				echo "<p style='color: red;'>Ошибка сохранения товара ID $product_id: " . $result->get_error_message() . "</p>\n";
			} else {
				// Устанавливаем термины для таксономий
				foreach ($assigned_terms as $taxonomy => $terms) {
					wp_set_object_terms($product_id, $terms, $taxonomy);
				}

				echo "<p style='color: green;'>Товар ID $product_id: присвоено атрибутов - " . count($product_attributes) . " (" . implode(', ', array_keys($assigned_terms)) . ")</p>\n";
			}
		}

		return $found_any;
	}

	private function find_attribute_values($title, $possible_values) {
		$found_values = [];
		$title_lower = mb_strtolower($title, 'UTF-8');

		foreach ($possible_values as $value) {
			$value_lower = mb_strtolower($value, 'UTF-8');

			// Поиск точного совпадения
			if (strpos($title_lower, $value_lower) !== false) {
				$found_values[] = $value;
				continue;
			}

			// Специальная логика для разных типов атрибутов

			// Для брендов - поиск по части названия
			if (in_array($value, ['iPhone', 'iPad', 'Samsung', 'Huawei', 'Xiaomi', 'Redmi', 'Honor'])) {
				$brand_patterns = [
					'iPhone' => ['iphone', 'айфон'],
					'iPad' => ['ipad', 'айпад'],
					'Samsung' => ['samsung', 'самсунг'],
					'Huawei' => ['huawei', 'хуавей', 'хуэвей'],
					'Xiaomi' => ['xiaomi', 'сяоми', 'ксиаоми'],
					'Redmi' => ['redmi', 'редми'],
					'Honor' => ['honor', 'хонор', 'хонор']
				];

				if (isset($brand_patterns[$value])) {
					foreach ($brand_patterns[$value] as $pattern) {
						if (strpos($title_lower, $pattern) !== false) {
							$found_values[] = $value;
							break;
						}
					}
				}
			}

			// Для цветов - поиск по ключевым словам
			if (in_array($value, ['Чёрный', 'Белый', 'Золотой', 'Серебро', 'Красный', 'Синий', 'Зелёный'])) {
				$color_patterns = [
					'Чёрный' => ['черн', 'black'],
					'Белый' => ['бел', 'white'],
					'Золотой' => ['золот', 'gold'],
					'Серебро' => ['серебр', 'silver'],
					'Красный' => ['красн', 'red'],
					'Синий' => ['син', 'blue'],
					'Зелёный' => ['зелен', 'green']
				];

				if (isset($color_patterns[$value])) {
					foreach ($color_patterns[$value] as $pattern) {
						if (strpos($title_lower, $pattern) !== false) {
							$found_values[] = $value;
							break;
						}
					}
				}
			}

			// Для типов запчастей
			if (in_array($value, ['Дисплей', 'Тачскрин', 'Стекло', 'Аккумулятор', 'Шлейф'])) {
				$part_patterns = [
					'Дисплей' => ['дисплей', 'display', 'экран'],
					'Тачскрин' => ['тачскрин', 'touchscreen', 'сенсор'],
					'Стекло' => ['стекло', 'glass'],
					'Аккумулятор' => ['аккумулятор', 'батарея', 'battery'],
					'Шлейф' => ['шлейф', 'flex']
				];

				if (isset($part_patterns[$value])) {
					foreach ($part_patterns[$value] as $pattern) {
						if (strpos($title_lower, $pattern) !== false) {
							$found_values[] = $value;
							break;
						}
					}
				}
			}
		}

		// Убираем дубликаты
		return array_unique($found_values);
	}
}

// Защита от случайного запуска
if (!isset($_GET['run']) || $_GET['run'] !== 'terms') {
	echo "<h1>WooCommerce Attribute Terms Processor</h1>";
	echo "<p>Этот скрипт добавит значения к существующим атрибутам и присвоит их товарам.</p>";
	echo "<p><strong>Внимание!</strong> Сначала убедитесь, что атрибуты созданы в админке WooCommerce.</p>";
	echo "<p>Скрипт обработает следующие атрибуты:</p>";
	echo "<ul>";
	echo "<li><strong>Бренд (pa_brand):</strong> Apple, Honor, Huawei, Infinix, iPad, iPhone, Nokia, Oppo, Redmi, Samsung, Tecno, Vivo, Wiko, Xiaomi</li>";
	echo "<li><strong>Емкость (pa_capacity):</strong> 2227 mAh, 2406 mAh, 3500 mAh, 3620 mAh, 3687 mAh, 3969 mAh, 4780 mAh, 4860 mAh</li>";
	echo "<li><strong>Цвет (pa_color):</strong> Бежевый, Белый, Бирюзовый, Голубой, Графитовый, Зелёный, Золотой, Красный, Лаванда, Розовый, Серебро, Синий, Ультрамарин, Фиолетовый, Чёрный</li>";
	echo "<li><strong>Тип запчасти (pa_part-type):</strong> Аккумулятор, Динамик, Дисплей, Корпус, Крышка, Плата, Рамка, Стекло, Тачскрин, Шлейф</li>";
	echo "<li><strong>Качество (pa_quality):</strong> Hard OLED, OLED, Soft OLED, Оригинал, Премиум, С олеофобным покрытием</li>";
	echo "</ul>";
	echo "<p><a href='?run=terms' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>Запустить обработку</a></p>";
	exit;
}

// Запуск обработки
try {
	$processor = new WC_Attribute_Terms_Processor();
	$processor->run();
} catch (Exception $e) {
	echo "<p style='color: red;'>Произошла ошибка: " . $e->getMessage() . "</p>";
	echo "<p>Попробуйте запустить скрипт еще раз.</p>";
}
?>