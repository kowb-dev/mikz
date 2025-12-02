<?php
/**
 * Скрипт очистки кэша MKX Live Search
 * Положите в корень сайта и откройте в браузере: https://yoursite.com/clear-cache.php
 * УДАЛИТЕ ФАЙЛ ПОСЛЕ ИСПОЛЬЗОВАНИЯ!
 */

// Подключаем WordPress
require_once(__DIR__ . '/wp-load.php');

// Проверка прав (только для администраторов)
if (!current_user_can('manage_options')) {
    die('Access denied. Please login as administrator.');
}

global $wpdb;

echo '<html><head><meta charset="utf-8"><title>Очистка кэша MKX</title>';
echo '<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
    .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 4px; }
    .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 4px; }
    h1 { color: #333; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>';

echo '<h1>🧹 Очистка кэша MKX Live Search</h1>';

// 1. Поиск существующих transients
$existing = $wpdb->get_results(
    "SELECT option_name FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_mkx_search_cats_%' 
    OR option_name LIKE '_transient_timeout_mkx_search_cats_%'",
    ARRAY_A
);

echo '<div class="info">';
echo '<strong>📊 Найдено записей кэша:</strong> ' . count($existing);
if (count($existing) > 0) {
    echo '<pre>';
    foreach ($existing as $row) {
        echo $row['option_name'] . "\n";
    }
    echo '</pre>';
}
echo '</div>';

// 2. Очистка transients
$deleted = $wpdb->query(
    "DELETE FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_mkx_search_cats_%' 
    OR option_name LIKE '_transient_timeout_mkx_search_cats_%'"
);

echo '<div class="success">';
echo '<strong>✅ Удалено записей:</strong> ' . $deleted;
echo '</div>';

// 3. Очистка object cache
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo '<div class="success">✅ Object cache очищен</div>';
}

// 4. Очистка кэша плагина
if (class_exists('MKX_Search_Query')) {
    $query_handler = MKX_Search_Query::instance();
    $query_handler->clear_search_cache();
    echo '<div class="success">✅ Кэш плагина очищен через метод clear_search_cache()</div>';
}

// 5. Проверка
$check = $wpdb->get_var(
    "SELECT COUNT(*) FROM {$wpdb->options} 
    WHERE option_name LIKE '%mkx_search%'"
);

echo '<div class="info">';
echo '<strong>🔍 Осталось записей mkx_search:</strong> ' . $check;
echo '</div>';

echo '<hr>';
echo '<p><strong>⚠️ ВАЖНО:</strong> Удалите этот файл после использования!</p>';
echo '<p><a href="' . home_url() . '" style="display:inline-block;background:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;">← Вернуться на сайт</a></p>';

echo '</body></html>';
?>
