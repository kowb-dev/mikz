<?php
/**
 * Optimized Synchronization Engine for Shared Hosting
 *
 * @package MoySklad_WC_Sync
 * @version 2.1.0
 * 
 * FILE: class-sync-engine.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/includes/class-sync-engine.php
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Optimized Sync Engine for weak hosting
 */
class Sync_Engine {
    private API $api;
    private Logger $logger;
    
    // Оптимизация для слабого хостинга
    private const BATCH_SIZE = 50;              // Уменьшено с 100 до 50
    private const MAX_EXECUTION_TIME = 120;     // Максимум 2 минуты
    private const MEMORY_LIMIT_THRESHOLD = 0.8; // 80% от лимита памяти
    private const PAUSE_BETWEEN_BATCHES = 1;    // Пауза 1 секунда между партиями

    private int $start_time;
    private int $memory_limit;

    public function __construct() {
        $this->api = new API();
        $this->logger = new Logger();
        $this->start_time = time();
        
        // Определяем лимит памяти
        $memory_limit = ini_get('memory_limit');
        $this->memory_limit = $this->parse_memory_limit($memory_limit);
        
        // Увеличиваем лимиты (если возможно)
        @set_time_limit(300);
        @ini_set('memory_limit', '256M');
    }

    /**
     * Run full synchronization with resource monitoring
     *
     * @return array Sync results
     */
    public function run_sync(): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'duration' => 0,
            'stopped_reason' => null,
        ];

        $this->logger->log('info', 'Sync started', [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'batch_size' => self::BATCH_SIZE
        ]);

        try {
            // Синхронизация товаров с мониторингом ресурсов
            $this->sync_all_products($results);
            
        } catch (\Exception $e) {
            $this->logger->log('error', 'Sync failed: ' . $e->getMessage(), [
                'exception' => $e->getTrace()
            ]);
            $results['errors'][] = $e->getMessage();
        }

        $results['duration'] = time() - $this->start_time;

        $this->logger->log('info', sprintf(
            'Sync completed: %d success, %d failed, %d created, %d updated, %d skipped (Duration: %d seconds)',
            $results['success'],
            $results['failed'],
            $results['created'],
            $results['updated'],
            $results['skipped'],
            $results['duration']
        ), [
            'peak_memory' => $this->format_bytes(memory_get_peak_usage(true)),
            'stopped_reason' => $results['stopped_reason']
        ]);

        return $results;
    }

    /**
     * Sync products with resource limits
     *
     * @param array $results Results array passed by reference
     */
    private function sync_all_products(array &$results): void {
        $offset = 0;
        $batch_number = 0;

        do {
            $batch_number++;
            
            // Проверка ресурсов перед каждой партией
            if (!$this->can_continue($results)) {
                break;
            }

            $this->logger->log('info', "Processing batch #{$batch_number}", [
                'offset' => $offset,
                'memory_usage' => $this->format_bytes(memory_get_usage(true)),
                'elapsed_time' => time() - $this->start_time
            ]);

            // Получаем товары
            $products_response = $this->api->get_products(self::BATCH_SIZE, $offset);

            if (is_wp_error($products_response)) {
                $error_message = $products_response->get_error_message();
                $this->logger->log('error', 'API request failed: ' . $error_message);
                $results['errors'][] = $error_message;
                break;
            }

            if (empty($products_response['rows'])) {
                $this->logger->log('info', 'No more products to sync');
                break;
            }

            // Получаем остатки для текущей партии
            $assortment_map = $this->get_assortment_for_batch($products_response['rows']);

            // Обрабатываем товары партии
            foreach ($products_response['rows'] as $ms_product) {
                // Еще одна проверка перед каждым товаром
                if (!$this->can_continue($results, true)) {
                    break 2; // Выход из двух циклов
                }

                $assortment_data = $assortment_map[$ms_product['id']] ?? null;
                $result = $this->sync_product($ms_product, $assortment_data);

                if ($result['success']) {
                    $results['success']++;
                    $results[$result['action']]++;
                } else {
                    $results['failed']++;
                    if (isset($result['error'])) {
                        $results['errors'][] = $result['error'];
                    }
                }
            }

            $offset += self::BATCH_SIZE;
            $has_more = count($products_response['rows']) === self::BATCH_SIZE;

            // Пауза между партиями чтобы не нагружать хостинг
            if ($has_more && self::PAUSE_BETWEEN_BATCHES > 0) {
                sleep(self::PAUSE_BETWEEN_BATCHES);
            }

            // Принудительная очистка памяти
            $this->cleanup_memory();

        } while ($has_more);
    }

    /**
     * Get assortment data only for current batch (memory optimization)
     *
     * @param array $products Current batch of products
     * @return array Assortment data mapped by product ID
     */
    private function get_assortment_for_batch(array $products): array {
        $assortment_map = [];
        $product_ids = array_column($products, 'id');

        // Получаем только первую страницу остатков (оптимизация)
        $response = $this->api->get_assortment(self::BATCH_SIZE * 2, 0);

        if (is_wp_error($response)) {
            $this->logger->log('warning', 'Failed to load assortment: ' . $response->get_error_message());
            return $assortment_map;
        }

        if (!empty($response['rows'])) {
            foreach ($response['rows'] as $item) {
                if (isset($item['id']) && in_array($item['id'], $product_ids, true)) {
                    $assortment_map[$item['id']] = $item;
                }
            }
        }

        return $assortment_map;
    }

    /**
     * Check if sync can continue based on resources
     *
     * @param array $results Current results
     * @param bool $strict Strict checking (every product)
     * @return bool
     */
    private function can_continue(array &$results, bool $strict = false): bool {
        // Проверка времени выполнения
        $elapsed = time() - $this->start_time;
        if ($elapsed >= self::MAX_EXECUTION_TIME) {
            $results['stopped_reason'] = 'Time limit reached';
            $this->logger->log('warning', 'Sync stopped: time limit reached', [
                'elapsed' => $elapsed,
                'limit' => self::MAX_EXECUTION_TIME
            ]);
            return false;
        }

        // Проверка памяти (только при строгой проверке или каждые 10 товаров)
        if ($strict || $results['success'] % 10 === 0) {
            $memory_usage = memory_get_usage(true);
            $memory_percent = $memory_usage / $this->memory_limit;

            if ($memory_percent >= self::MEMORY_LIMIT_THRESHOLD) {
                $results['stopped_reason'] = 'Memory limit threshold reached';
                $this->logger->log('warning', 'Sync stopped: memory limit threshold', [
                    'memory_usage' => $this->format_bytes($memory_usage),
                    'memory_limit' => $this->format_bytes($this->memory_limit),
                    'percent' => round($memory_percent * 100, 2) . '%'
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Sync single product with assortment data
     *
     * @param array $ms_product MoySklad product data
     * @param array|null $assortment_data Assortment data
     * @return array Result of sync operation
     */
    private function sync_product(array $ms_product, ?array $assortment_data): array {
        // Приоритет: article > code > externalCode > id
        $sku = $ms_product['article'] ?? 
               $ms_product['code'] ?? 
               $ms_product['externalCode'] ?? 
               $ms_product['id'] ?? '';

        if (empty($sku)) {
            return [
                'success' => false,
                'error' => sprintf(
                    __('Product "%s" has no SKU (article/code/externalCode).', 'moysklad-wc-sync'),
                    $ms_product['name'] ?? 'Unknown'
                ),
            ];
        }

        try {
            $product_id = wc_get_product_id_by_sku($sku);
            $action = $product_id ? 'updated' : 'created';

            $product = $product_id ? wc_get_product($product_id) : new \WC_Product_Simple();

            if (!$product) {
                throw new \Exception(__('Failed to load or create product.', 'moysklad-wc-sync'));
            }

            $this->update_product_data($product, $ms_product, $assortment_data);

            $product->save();

            return [
                'success' => true,
                'action' => $action,
                'product_id' => $product->get_id(),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => sprintf(
                    __('Failed to sync product %1$s: %2$s', 'moysklad-wc-sync'),
                    $sku,
                    $e->getMessage()
                ),
            ];
        }
    }

    /**
     * Update product data from MoySklad
     *
     * @param \WC_Product $product WooCommerce product
     * @param array $ms_product MoySklad product data
     * @param array|null $assortment_data Assortment data
     */
    private function update_product_data(\WC_Product $product, array $ms_product, ?array $assortment_data): void {
        $product->set_name(sanitize_text_field($ms_product['name']));
        
        // Используем article (артикул) в приоритете
        $sku = $ms_product['article'] ?? 
               $ms_product['code'] ?? 
               $ms_product['externalCode'] ?? 
               $ms_product['id'] ?? '';
        
        $product->set_sku(sanitize_text_field($sku));

        if (isset($ms_product['description'])) {
            $product->set_description(wp_kses_post($ms_product['description']));
        }

        if ($assortment_data) {
            $this->update_stock($product, $assortment_data);
            $this->update_prices($product, $assortment_data);
        }
    }

    /**
     * Update product stock
     *
     * @param \WC_Product $product WooCommerce product
     * @param array $assortment_data Assortment data from MoySklad
     */
    private function update_stock(\WC_Product $product, array $assortment_data): void {
        if (isset($assortment_data['stock'])) {
            $stock = (int) $assortment_data['stock'];
            $product->set_manage_stock(true);
            $product->set_stock_quantity($stock);
            $product->set_stock_status($stock > 0 ? 'instock' : 'outofstock');
        }
    }

    /**
     * Update product prices (retail and wholesale)
     *
     * @param \WC_Product $product WooCommerce product
     * @param array $assortment_data Assortment data from MoySklad
     */
    private function update_prices(\WC_Product $product, array $assortment_data): void {
        if (empty($assortment_data['salePrices']) || !is_array($assortment_data['salePrices'])) {
            return;
        }

        $retail_price = null;
        $wholesale_price = null;

        foreach ($assortment_data['salePrices'] as $price_item) {
            if (!isset($price_item['priceType']['name'], $price_item['value'])) {
                continue;
            }

            $price_type = mb_strtolower($price_item['priceType']['name']);
            $price_value = (float) $price_item['value'] / 100;

            if (str_contains($price_type, 'розница') || str_contains($price_type, 'retail')) {
                $retail_price = $price_value;
            } elseif (str_contains($price_type, 'опт') || str_contains($price_type, 'wholesale')) {
                $wholesale_price = $price_value;
            }
        }

        if ($retail_price !== null) {
            $product->set_regular_price((string) $retail_price);
            $product->set_price((string) $retail_price);
        }

        if ($wholesale_price !== null) {
            update_post_meta($product->get_id(), '_wholesale_price', $wholesale_price);
        }
    }

    /**
     * Parse memory limit string to bytes
     *
     * @param string $limit Memory limit string (e.g., '128M')
     * @return int Bytes
     */
    private function parse_memory_limit(string $limit): int {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable string
     *
     * @param int $bytes
     * @return string
     */
    private function format_bytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Cleanup memory after batch processing
     */
    private function cleanup_memory(): void {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}