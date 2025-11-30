<?php
/**
 * Optimized Synchronization Engine with Enhanced Price Debugging
 *
 * @package MoySklad_WC_Sync
 * @version 2.2.0
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

class Sync_Engine {
    private API $api;
    private Logger $logger;
    
    private int $batch_size;
    private int $max_execution_time;
    private const MEMORY_LIMIT_THRESHOLD = 0.8;
    private const PAUSE_BETWEEN_BATCHES = 1;

    private int $start_time;
    private int $memory_limit;
    
    private array $price_stats = [
        'total_products' => 0,
        'products_with_prices' => 0,
        'products_without_prices' => 0,
        'retail_prices_found' => 0,
        'wholesale_prices_found' => 0,
        'price_types_encountered' => [],
    ];

    public function __construct() {
        $this->api = new API();
        $this->logger = new Logger();
        $this->start_time = time();
        
        // Получаем настройки из админки
        $this->batch_size = (int) get_option('ms_wc_sync_batch_size', 50);
        $this->max_execution_time = (int) get_option('ms_wc_sync_max_time', 180);
        
        $memory_limit = ini_get('memory_limit');
        $this->memory_limit = $this->parse_memory_limit($memory_limit);
        
        @set_time_limit($this->max_execution_time + 60);
        @ini_set('memory_limit', '256M');
    }

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
            'price_stats' => [],
            'total_processed' => 0,
        ];

        $this->update_progress(0, 'Запуск синхронизации...');

        $this->logger->log('info', 'Sync started', [
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'batch_size' => $this->batch_size,
            'max_time_setting' => $this->max_execution_time
        ]);

        try {
            $this->sync_all_products($results);
            
            $results['price_stats'] = $this->price_stats;
            $this->update_progress(100, 'Синхронизация завершена');
            
        } catch (\Exception $e) {
            $this->logger->log('error', 'Sync failed: ' . $e->getMessage(), [
                'exception' => $e->getTrace()
            ]);
            $results['errors'][] = $e->getMessage();
            $this->update_progress(-1, 'Ошибка: ' . $e->getMessage());
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
            'stopped_reason' => $results['stopped_reason'],
            'price_stats' => $this->price_stats
        ]);

        return $results;
    }

    private function update_progress(int $percent, string $message): void {
        update_option('ms_wc_sync_progress', [
            'percent' => $percent,
            'message' => $message,
            'timestamp' => time(),
        ], false);
    }

    private function sync_all_products(array &$results): void {
        $offset = 0;
        $batch_number = 0;
        
        $total_products = $this->get_total_products_count();
        
        $this->logger->log('info', 'Total products to sync', [
            'total' => $total_products
        ]);

        do {
            $batch_number++;
            
            if (!$this->can_continue($results)) {
                break;
            }
            
            $progress_percent = $total_products > 0 ? min(100, (int)(($offset / $total_products) * 100)) : 0;
            $this->update_progress(
                $progress_percent, 
                sprintf('Обработка партии #%d (товаров: %d из %d)', $batch_number, $offset, $total_products)
            );

            $this->logger->log('info', "Processing batch #{$batch_number}", [
                'offset' => $offset,
                'memory_usage' => $this->format_bytes(memory_get_usage(true)),
                'elapsed_time' => time() - $this->start_time
            ]);

            $products_response = $this->api->get_products($this->batch_size, $offset);

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

            $assortment_map = $this->get_assortment_for_batch($products_response['rows']);

            foreach ($products_response['rows'] as $ms_product) {
                if (!$this->can_continue($results, true)) {
                    break 2;
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
                
                $results['total_processed']++;
            }

            $offset += $this->batch_size;
            $has_more = count($products_response['rows']) === $this->batch_size;

            if ($has_more && self::PAUSE_BETWEEN_BATCHES > 0) {
                sleep(self::PAUSE_BETWEEN_BATCHES);
            }

            $this->cleanup_memory();

        } while ($has_more);
    }
    
    private function get_total_products_count(): int {
        $response = $this->api->get_products(1, 0);
        
        if (is_wp_error($response)) {
            return 0;
        }
        
        return $response['meta']['size'] ?? 0;
    }

    private function get_assortment_for_batch(array $products): array {
        $assortment_map = [];
        $product_ids = array_column($products, 'id');

        $this->logger->log('info', 'Fetching assortment data for batch', [
            'product_count' => count($product_ids),
            'product_ids_sample' => array_slice($product_ids, 0, 5)
        ]);

        $assortment_offset = 0;
        $assortment_limit = 100;
        $found_ids = [];
        $attempts = 0;
        $max_attempts = 50;

        do {
            $attempts++;
            
            $response = $this->api->get_assortment($assortment_limit, $assortment_offset);

            if (is_wp_error($response)) {
                $this->logger->log('warning', 'Failed to load assortment page: ' . $response->get_error_message(), [
                    'attempt' => $attempts,
                    'offset' => $assortment_offset
                ]);
                break;
            }

            if (empty($response['rows'])) {
                $this->logger->log('info', 'No more assortment data', [
                    'attempts' => $attempts,
                    'found' => count($found_ids)
                ]);
                break;
            }

            $this->logger->log('info', "Processing assortment attempt #{$attempts}", [
                'items_received' => count($response['rows']),
                'offset' => $assortment_offset,
                'found_so_far' => count($found_ids)
            ]);

            foreach ($response['rows'] as $item) {
                if (isset($item['id']) && in_array($item['id'], $product_ids, true) && !isset($assortment_map[$item['id']])) {
                    $assortment_map[$item['id']] = $item;
                    $found_ids[] = $item['id'];
                    
                    $this->log_assortment_item($item);
                }
            }

            if (count($found_ids) >= count($product_ids)) {
                $this->logger->log('info', 'All products found in assortment', [
                    'found' => count($found_ids),
                    'total' => count($product_ids),
                    'attempts' => $attempts
                ]);
                break;
            }

            $assortment_offset += $assortment_limit;
            $has_more_assortment = count($response['rows']) === $assortment_limit;

        } while ($has_more_assortment && $attempts < $max_attempts);

        $missing_ids = array_diff($product_ids, $found_ids);
        if (!empty($missing_ids)) {
            $this->logger->log('warning', 'Some products not found in assortment', [
                'missing_count' => count($missing_ids),
                'missing_ids_sample' => array_slice($missing_ids, 0, 10),
                'attempts_made' => $attempts,
                'records_checked' => $attempts * $assortment_limit
            ]);
        }

        $this->logger->log('info', 'Assortment search completed', [
            'found' => count($found_ids),
            'missing' => count($missing_ids),
            'total_needed' => count($product_ids),
            'attempts' => $attempts
        ]);

        return $assortment_map;
    }

    private function log_assortment_item(array $item): void {
        if (empty($item['salePrices'])) {
            $this->logger->log('warning', 'Product has no salePrices in assortment', [
                'product_id' => $item['id'] ?? 'unknown',
                'product_name' => $item['name'] ?? 'unknown',
                'available_fields' => array_keys($item)
            ]);
            return;
        }

        $price_types_found = [];
        foreach ($item['salePrices'] as $price_item) {
            if (isset($price_item['priceType']['name'])) {
                $price_types_found[] = $price_item['priceType']['name'];
            }
        }

        $this->logger->log('info', 'Product prices found', [
            'product_id' => $item['id'] ?? 'unknown',
            'product_name' => $item['name'] ?? 'unknown',
            'price_types' => $price_types_found,
            'price_count' => count($item['salePrices'])
        ]);
    }

    private function can_continue(array &$results, bool $strict = false): bool {
        $elapsed = time() - $this->start_time;
        if ($elapsed >= $this->max_execution_time) {
            $results['stopped_reason'] = 'Time limit reached';
            $this->logger->log('warning', 'Sync stopped: time limit reached', [
                'elapsed' => $elapsed,
                'limit' => $this->max_execution_time
            ]);
            return false;
        }

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

    private function sync_product(array $ms_product, ?array $assortment_data): array {
        $this->price_stats['total_products']++;
        
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

    private function update_product_data(\WC_Product $product, array $ms_product, ?array $assortment_data): void {
        $product->set_name(sanitize_text_field($ms_product['name']));
        
        $sku = $ms_product['article'] ?? 
               $ms_product['code'] ?? 
               $ms_product['externalCode'] ?? 
               $ms_product['id'] ?? '';
        
        $product->set_sku(sanitize_text_field($sku));

        if (isset($ms_product['description'])) {
            $product->set_description(wp_kses_post($ms_product['description']));
        }

        if ($assortment_data) {
            $this->price_stats['products_with_prices']++;
            
            $this->update_stock($product, $assortment_data);
            $this->update_prices($product, $assortment_data, $ms_product['name'] ?? 'Unknown');
        } else {
            $this->price_stats['products_without_prices']++;
            
            $this->logger->log('warning', 'Product synced without assortment data', [
                'sku' => $sku,
                'name' => $ms_product['name'] ?? 'Unknown',
                'moysklad_id' => $ms_product['id'] ?? 'unknown'
            ]);
        }
    }

    private function update_stock(\WC_Product $product, array $assortment_data): void {
        if (isset($assortment_data['stock'])) {
            $stock = (int) $assortment_data['stock'];
            $product->set_manage_stock(true);
            $product->set_stock_quantity($stock);
            $product->set_stock_status($stock > 0 ? 'instock' : 'outofstock');
        }
    }

    private function update_prices(\WC_Product $product, array $assortment_data, string $product_name): void {
        if (empty($assortment_data['salePrices']) || !is_array($assortment_data['salePrices'])) {
            $this->logger->log('warning', 'No salePrices array found for product', [
                'product_name' => $product_name,
                'sku' => $product->get_sku(),
                'assortment_keys' => array_keys($assortment_data)
            ]);
            return;
        }

        $retail_price = null;
        $wholesale_price = null;
        $all_price_types = [];

        foreach ($assortment_data['salePrices'] as $price_item) {
            if (!isset($price_item['priceType']['name'], $price_item['value'])) {
                $this->logger->log('warning', 'Invalid price item structure', [
                    'product_name' => $product_name,
                    'price_item_keys' => array_keys($price_item)
                ]);
                continue;
            }

            $price_type = $price_item['priceType']['name'];
            $price_type_lower = mb_strtolower($price_type);
            $price_value = (float) $price_item['value'] / 100;

            $all_price_types[] = $price_type;
            if (!isset($this->price_stats['price_types_encountered'][$price_type])) {
                $this->price_stats['price_types_encountered'][$price_type] = 0;
            }
            $this->price_stats['price_types_encountered'][$price_type]++;

            if (str_contains($price_type_lower, 'розница') || 
                str_contains($price_type_lower, 'retail') ||
                str_contains($price_type_lower, 'розничная')) {
                $retail_price = $price_value;
                $this->price_stats['retail_prices_found']++;
            } elseif (str_contains($price_type_lower, 'опт') || 
                      str_contains($price_type_lower, 'wholesale') ||
                      str_contains($price_type_lower, 'оптовая')) {
                $wholesale_price = $price_value;
                $this->price_stats['wholesale_prices_found']++;
            }
        }

        $this->logger->log('info', 'Processing prices for product', [
            'product_name' => $product_name,
            'sku' => $product->get_sku(),
            'all_price_types' => $all_price_types,
            'retail_price' => $retail_price,
            'wholesale_price' => $wholesale_price,
            'total_prices_in_moysklad' => count($assortment_data['salePrices'])
        ]);

        if ($retail_price !== null) {
            $product->set_regular_price((string) $retail_price);
            $product->set_price((string) $retail_price);
            
            $this->logger->log('info', 'Retail price set', [
                'product_name' => $product_name,
                'sku' => $product->get_sku(),
                'price' => $retail_price
            ]);
        } else {
            $this->logger->log('warning', 'No retail price found for product', [
                'product_name' => $product_name,
                'sku' => $product->get_sku(),
                'available_price_types' => $all_price_types
            ]);
        }

        if ($wholesale_price !== null) {
            update_post_meta($product->get_id(), '_wholesale_price', $wholesale_price);
            
            $this->logger->log('info', 'Wholesale price set', [
                'product_name' => $product_name,
                'sku' => $product->get_sku(),
                'price' => $wholesale_price
            ]);
        } else {
            $this->logger->log('info', 'No wholesale price found for product', [
                'product_name' => $product_name,
                'sku' => $product->get_sku(),
                'available_price_types' => $all_price_types
            ]);
        }
    }

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

    private function format_bytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    private function cleanup_memory(): void {
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
}