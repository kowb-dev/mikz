#!/usr/bin/env php
<?php
/**
 * Скрипт для установки статуса backorder для товаров WooCommerce
 * Использование: 
 *   php set_backorder.php               - установить backorder с созданием бэкапа
 *   php set_backorder.php --rollback    - откатить изменения из последнего бэкапа
 *   php set_backorder.php --list-backups - показать список бэкапов
 */

// Отключение буферизации вывода
if (ob_get_level()) {
    ob_end_clean();
}

// Путь к файлу wp-load.php (настройте под ваш проект)
$wp_load_path = __DIR__ . '/wp-load.php';

// Директория для хранения бэкапов
$backup_dir = __DIR__ . '/backorder_backups';

// Проверка существования WordPress
if (!file_exists($wp_load_path)) {
    echo "Ошибка: Файл wp-load.php не найден по пути: {$wp_load_path}\n";
    echo "Укажите правильный путь к WordPress в переменной \$wp_load_path\n";
    exit(1);
}

// Загрузка WordPress (без симуляции WP-CLI)
require_once $wp_load_path;

// Отключаем буферизацию после загрузки WP (плагины/тема могли включить её опять)
while (ob_get_level()) {
    ob_end_clean();
}

// Проверка наличия WooCommerce
if (!function_exists('WC') && !class_exists('WC_Product')) {
    echo "Ошибка: WooCommerce не установлен или не активирован\n";
    exit(1);
}

// Создание директории для бэкапов
if (!is_dir($backup_dir)) {
    if (!mkdir($backup_dir, 0755, true) && !is_dir($backup_dir)) {
        echo "Ошибка: Не удалось создать директорию для бэкапов: {$backup_dir}\n";
        exit(1);
    }
}

// Определение режима работы
$mode = isset($argv[1]) ? $argv[1] : 'apply';

switch ($mode) {
    case '--rollback':
        rollback_changes();
        break;
    case '--list-backups':
        list_backups();
        break;
    default:
        apply_backorder();
        break;
}

/**
 * Читает ввод пользователя из консоли
 */
function read_input($prompt = '') {
    if ($prompt) {
        echo $prompt;
        flush();
    }

    $handle = fopen('php://stdin', 'r');
    if ($handle === false) {
        return '';
    }

    $line = fgets($handle);
    fclose($handle);

    if ($line === false) {
        return '';
    }

    return trim($line);
}

/**
 * Применить статус backorder с созданием бэкапа
 */
function apply_backorder() {
    global $backup_dir;

    echo "=== Установка статуса backorder для товаров WooCommerce ===\n\n";
    flush();

    // Выбор статуса backorder
    echo "Выберите статус backorder для товаров:\n";
    echo "1. Не разрешать (no)\n";
    echo "2. Разрешить, но уведомить клиента (notify)\n";
    echo "3. Разрешить (yes)\n";
    echo "\nВведите номер (1-3): ";
    flush();

    $choice = read_input();

    // Определение значения backorder
    $backorder_status_map = array(
        '1' => array('value' => 'no',     'label' => 'Не разрешать'),
        '2' => array('value' => 'notify', 'label' => 'Разрешить, но уведомить клиента'),
        '3' => array('value' => 'yes',    'label' => 'Разрешить'),
    );

    if (!isset($backorder_status_map[$choice])) {
        echo "\nОшибка: Неверный выбор. Используйте цифры 1, 2 или 3.\n";
        exit(1);
    }

    $backorder_value = $backorder_status_map[$choice]['value'];
    $backorder_label = $backorder_status_map[$choice]['label'];

    echo "\nВыбран статус: {$backorder_label} ({$backorder_value})\n";
    echo "\nПодтвердите применение изменений (y/n): ";
    flush();

    $confirm = strtolower(read_input());

    if ($confirm !== 'y' && $confirm !== 'yes') {
        echo "Операция отменена.\n";
        exit(0);
    }

    echo "\n";
    flush();

    // Получение всех товаров
    $args = array(
        'post_type'      => array('product', 'product_variation'),
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    );

    $products = get_posts($args);
    $total    = is_array($products) ? count($products) : 0;

    echo "Найдено товаров: {$total}\n";
    flush();

    // Создание бэкапа
    echo "Создание бэкапа текущих настроек...\n";
    flush();

    $backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.json';
    $backup_data = array(
        'metadata' => array(
            'date'           => date('Y-m-d H:i:s'),
            'applied_status' => $backorder_value,
            'applied_label'  => $backorder_label,
            'total_products' => 0,
        ),
        'products' => array(),
    );

    if (!empty($products)) {
        foreach ($products as $product_id) {
            $product = wc_get_product($product_id);

            if (!$product) {
                continue;
            }

            // Сохраняем текущее значение backorder
            $backup_data['products'][$product_id] = array(
                'id'         => $product_id,
                'name'       => $product->get_name(),
                'backorders' => $product->get_backorders(),
            );
        }
    }

    $backup_data['metadata']['total_products'] = count($backup_data['products']);

    $json = json_encode($backup_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        echo "Ошибка: Не удалось закодировать данные бэкапа в JSON\n";
        exit(1);
    }

    $result = file_put_contents($backup_file, $json);
    if ($result === false) {
        echo "Ошибка: Не удалось записать бэкап в файл {$backup_file}\n";
        exit(1);
    }

    echo "Бэкап сохранен: {$backup_file}\n\n";
    flush();

    // Применение изменений
    echo "Применение изменений...\n\n";
    flush();

    $updated = 0;
    $skipped = 0;

    if (!empty($products)) {
        foreach ($products as $product_id) {
            $product = wc_get_product($product_id);

            if (!$product) {
                $skipped++;
                continue;
            }

            // Устанавливаем выбранный статус backorder
            $product->set_backorders($backorder_value);
            $product->save();

            $updated++;

            // Вывод прогресса каждые 10 товаров
            if ($updated % 10 === 0) {
                echo "Обработано: {$updated}/{$total}\n";
                flush();
            }
        }
    }

    echo "\n=== Результаты ===\n";
    echo "Применен статус: {$backorder_label} ({$backorder_value})\n";
    echo "Всего товаров: {$total}\n";
    echo "Обновлено: {$updated}\n";
    echo "Пропущено: {$skipped}\n";
    echo "Файл бэкапа: {$backup_file}\n";
    echo "\nДля отката используйте: php " . basename(__FILE__) . " --rollback\n";
    echo "Готово!\n";
    flush();
}

/**
 * Откатить изменения из последнего бэкапа
 */
function rollback_changes() {
    global $backup_dir;

    echo "=== Откат изменений из бэкапа ===\n\n";
    flush();

    // Поиск последнего бэкапа
    $backups = glob($backup_dir . '/backup_*.json');

    if (empty($backups)) {
        echo "Ошибка: Файлы бэкапа не найдены\n";
        exit(1);
    }

    // Сортировка по времени (последний - первый)
    rsort($backups);
    $latest_backup = $backups[0];

    echo "Используется бэкап: " . basename($latest_backup) . "\n";
    flush();

    // Чтение данных из бэкапа
    $contents = file_get_contents($latest_backup);
    if ($contents === false) {
        echo "Ошибка: Не удалось прочитать файл бэкапа\n";
        exit(1);
    }

    $backup_data = json_decode($contents, true);

    if (!is_array($backup_data)) {
        echo "Ошибка: Неверный формат файла бэкапа\n";
        exit(1);
    }

    // Вывод информации о бэкапе
    if (isset($backup_data['metadata']) && isset($backup_data['products'])) {
        echo "Дата создания: {$backup_data['metadata']['date']}\n";
        echo "Применялся статус: {$backup_data['metadata']['applied_label']} ({$backup_data['metadata']['applied_status']})\n";
        $products_data = $backup_data['products'];
    } else {
        // Старый формат бэкапа (без метаданных)
        $products_data = $backup_data;
    }

    echo "\nВосстановление данных...\n\n";
    flush();

    $total    = is_array($products_data) ? count($products_data) : 0;
    $restored = 0;
    $failed   = 0;

    foreach ($products_data as $item) {
        if (!isset($item['id'], $item['backorders'])) {
            $failed++;
            continue;
        }

        $product = wc_get_product($item['id']);

        if (!$product) {
            echo "Товар ID {$item['id']} не найден\n";
            flush();
            $failed++;
            continue;
        }

        // Восстанавливаем оригинальное значение
        $product->set_backorders($item['backorders']);
        $product->save();

        $restored++;

        if ($restored % 10 === 0) {
            echo "Восстановлено: {$restored}/{$total}\n";
            flush();
        }
    }

    echo "\n=== Результаты отката ===\n";
    echo "Всего записей в бэкапе: {$total}\n";
    echo "Восстановлено: {$restored}\n";
    echo "Ошибок: {$failed}\n";
    echo "\nОткат завершен!\n";
    flush();
}

/**
 * Показать список доступных бэкапов
 */
function list_backups() {
    global $backup_dir;

    echo "=== Список доступных бэкапов ===\n\n";
    flush();

    $backups = glob($backup_dir . '/backup_*.json');

    if (empty($backups)) {
        echo "Бэкапы не найдены\n";
        return;
    }

    // Сортировка по времени (последний - первый)
    rsort($backups);

    foreach ($backups as $backup) {
        $filename = basename($backup);
        $size     = filesize($backup);
        $date     = filemtime($backup);

        $count          = 0;
        $applied_status = 'N/A';

        $data_raw = file_get_contents($backup);
        if ($data_raw !== false) {
            $data = json_decode($data_raw, true);
            if (is_array($data)) {
                if (isset($data['metadata'])) {
                    $count          = isset($data['metadata']['total_products']) ? (int)$data['metadata']['total_products'] : 0;
                    $applied_status = $data['metadata']['applied_label'] . ' (' . $data['metadata']['applied_status'] . ')';
                } else {
                    $count = count($data);
                }
            }
        }

        echo sprintf(
            "- %s\n  Дата: %s | Размер: %s | Товаров: %d\n  Применялся статус: %s\n\n",
            $filename,
            date('Y-m-d H:i:s', $date),
            format_bytes($size),
            $count,
            $applied_status
        );
        flush();
    }

    echo "Для отката используйте: php " . basename(__FILE__) . " --rollback\n";
    flush();
}

/**
 * Форматирование размера файла
 */
function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    $bytes = max($bytes, 0);
    if ($bytes === 0) {
        return '0 B';
    }

    $pow = floor(log($bytes, 1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
}
