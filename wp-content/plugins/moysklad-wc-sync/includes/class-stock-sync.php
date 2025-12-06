<?php
/**
 * Stock-Specific Synchronization Handler
 *
 * Optimized for incremental stock updates with minimal server load
 *
 * @package MoySklad_WC_Sync
 * @version 2.2.1
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

class Stock_Sync {
    private API $api;
    private Logger $logger;
    private const STOCK_DATA_TABLE = 'ms_wc_sync_stock_data';
    private const LOCK_KEY = 'ms_wc_sync_stock_lock';
    private const LOCK_TIMEOUT = 300; // 5 minutes
    
    public function __construct() {
        // Get API token from options
        $token = get_option('ms_wc_sync_api_token', '');
        
        // Pass token explicitly to API class
        $this->api = new API($token);
        $this->logger = new Logger();
        
        // Log token status for debugging
        if (empty($token)) {
            $this->logger->log('error', 'Stock_Sync initialized without API token');
        }
    }
    
    /**
     * Create the stock data table if it doesn't exist
     */
    public static function create_tables(): void {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::STOCK_DATA_TABLE;
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            moysklad_id varchar(255) NOT NULL,
            product_id bigint(20) NOT NULL DEFAULT 0,
            sku varchar(255) NOT NULL DEFAULT '',
            stock int(11) NOT NULL DEFAULT 0,
            reserve int(11) NOT NULL DEFAULT 0,
            store_id varchar(255) NOT NULL DEFAULT '',
            updated_at datetime NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY moysklad_id (moysklad_id),
            KEY product_id (product_id),
            KEY sku (sku),
            KEY updated_at (updated_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
    
    /**
     * Run incremental stock synchronization
     */
    public function run_sync(): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => [],
            'duration' => 0,
            'stopped_reason' => null,
        ];
        
        $start_time = microtime(true);
        
        // Check if API token is configured
        $token = get_option('ms_wc_sync_api_token', '');
        if (empty($token)) {
            $results['errors'][] = 'API token is not configured.';
            $results['stopped_reason'] = 'No API token';
            $this->logger->log('error', 'Stock sync failed: API token is not configured.');
            return $results;
        }
        
        // Check if sync is already running
        if ($this->is_locked()) {
            $results['stopped_reason'] = 'Another sync is already running';
            $this->logger->log('warning', 'Stock sync skipped - already running');
            return $results;
        }
        
        // Set lock
        $this->set_lock();
        
        try {
            $this->logger->log('info', 'Stock sync started');
            
            // Get stock report from MoySklad
            $stock_response = $this->get_stock_report();
            
            if (is_wp_error($stock_response)) {
                throw new \Exception($stock_response->get_error_message());
            }
            
            // Process stock data
            $current_stock_data = $this->extract_stock_data($stock_response);
            
            // Get previous stock data
            $previous_stock_data = $this->get_stored_stock_data();
            
            // Find products with changed stock
            $changed_products = $this->find_changed_products($current_stock_data, $previous_stock_data);
            
            $this->logger->log('info', 'Stock changes detected', [
                'total_products' => count($current_stock_data),
                'changed_products' => count($changed_products)
            ]);
            
            // Update products with changed stock
            foreach ($changed_products as $ms_id => $stock_info) {
                $result = $this->update_product_stock($ms_id, $stock_info);
                
                if ($result) {
                    $results['success']++;
                    $results['updated']++;
                } else {
                    $results['failed']++;
                }
            }
            
            // Store updated stock data
            $this->store_stock_data($current_stock_data);
            
            $results['skipped'] = count($current_stock_data) - count($changed_products);
            
        } catch (\Exception $e) {
            $this->logger->log('error', 'Stock sync failed: ' . $e->getMessage());
            $results['errors'][] = $e->getMessage();
        } finally {
            // Release lock
            $this->release_lock();
        }
        
        $results['duration'] = microtime(true) - $start_time;
        
        $this->logger->log('info', 'Stock sync completed', [
            'success' => $results['success'],
            'failed' => $results['failed'],
            'updated' => $results['updated'],
            'skipped' => $results['skipped'],
            'duration' => $results['duration']
        ]);
        
        return $results;
    }
    
    /**
     * Get stock report from MoySklad
     */
    private function get_stock_report(): array|\WP_Error {
        $store_id = get_option('ms_wc_sync_store_id', '');
        
        // Get all stock data with pagination
        $all_stock = [];
        $offset = 0;
        $limit = 1000; // Maximum allowed by MoySklad API
        
        do {
            $params = [
                'limit' => $limit,
                'offset' => $offset
            ];
            
            if (!empty($store_id)) {
                $params['filter'] = "store.id=$store_id";
            }
            
            $response = $this->api->request('/report/stock/bystore', $params);
            
            if (is_wp_error($response)) {
                return $response;
            }
            
            if (!empty($response['rows'])) {
                $all_stock = array_merge($all_stock, $response['rows']);
            }
            
            $offset += $limit;
            $has_more = !empty($response['rows']) && count($response['rows']) === $limit;
            
            // Log progress
            if ($has_more) {
                $this->logger->log('info', 'Fetching stock data', [
                    'fetched' => count($all_stock),
                    'batch' => $offset / $limit
                ]);
            }
            
        } while ($has_more);
        
        $this->logger->log('info', 'Stock data fetched', [
            'total_products' => count($all_stock)
        ]);
        
        return ['rows' => $all_stock];
    }
    
    /**
     * Extract stock data from API response
     */
    private function extract_stock_data(array $response): array {
        $stock_data = [];
        
        if (empty($response['rows'])) {
            return $stock_data;
        }
        
        foreach ($response['rows'] as $item) {
            // Skip if no meta information
            if (empty($item['meta']['href'])) {
                continue;
            }
            
            // Extract product ID from meta href
            $product_id = preg_replace('#.*/([^/]+)$#', '$1', $item['meta']['href']);
            
            // Get stock from appropriate store
            $stock = 0;
            $reserve = 0;
            $store_id = '';
            
            if (!empty($item['stockByStore'])) {
                foreach ($item['stockByStore'] as $store_stock) {
                    // Get store ID
                    if (!empty($store_stock['meta']['href'])) {
                        $store_id = preg_replace('#.*/([^/]+)$#', '$1', $store_stock['meta']['href']);
                    }
                    
                    // Add stock
                    $stock += (int)($store_stock['stock'] ?? 0);
                    $reserve += (int)($store_stock['reserve'] ?? 0);
                }
            }
            
            $stock_data[$product_id] = [
                'moysklad_id' => $product_id,
                'stock' => $stock,
                'reserve' => $reserve,
                'store_id' => $store_id,
                'updated_at' => current_time('mysql', true)
            ];
        }
        
        return $stock_data;
    }
    
    /**
     * Get stored stock data from database
     */
    private function get_stored_stock_data(): array {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::STOCK_DATA_TABLE;
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        
        if (!$results) {
            return [];
        }
        
        $stock_data = [];
        foreach ($results as $row) {
            $stock_data[$row['moysklad_id']] = $row;
        }
        
        return $stock_data;
    }
    
    /**
     * Find products with changed stock
     */
    private function find_changed_products(array $current_data, array $previous_data): array {
        $changed_products = [];
        $reservation_mode = get_option('ms_wc_sync_reservation_mode', 'ignore');
        
        foreach ($current_data as $ms_id => $current_info) {
            // If product not in previous data, it's new or changed
            if (!isset($previous_data[$ms_id])) {
                $changed_products[$ms_id] = $current_info;
                continue;
            }
            
            $previous_info = $previous_data[$ms_id];
            
            // Calculate available stock based on reservation mode
            $current_available = $this->calculate_available_stock($current_info, $reservation_mode);
            $previous_available = $this->calculate_available_stock($previous_info, $reservation_mode);
            
            // If available stock has changed, add to changed products
            if ($current_available !== $previous_available) {
                $changed_products[$ms_id] = $current_info;
            }
        }
        
        return $changed_products;
    }
    
    /**
     * Calculate available stock based on reservation mode
     */
    private function calculate_available_stock(array $stock_info, string $reservation_mode): int {
        $stock = (int)($stock_info['stock'] ?? 0);
        $reserve = (int)($stock_info['reserve'] ?? 0);
        
        if ($reservation_mode === 'subtract') {
            return max(0, $stock - $reserve);
        }
        
        if ($reservation_mode === 'free_to_sell' && isset($stock_info['quantityFreeToSell'])) {
            return (int)$stock_info['quantityFreeToSell'];
        }
        
        return $stock;
    }
    
    /**
     * Update product stock in WooCommerce
     */
    public function update_product_stock(string $moysklad_id, array $stock_info): bool {
        // Get WooCommerce product ID from MoySklad ID
        $product_id = $this->get_wc_product_id_by_moysklad_id($moysklad_id);
        
        if (!$product_id) {
            $this->logger->log('warning', 'Product not found in WooCommerce', [
                'moysklad_id' => $moysklad_id
            ]);
            return false;
        }
        
        // Calculate available stock
        $reservation_mode = get_option('ms_wc_sync_reservation_mode', 'ignore');
        $available_stock = $this->calculate_available_stock($stock_info, $reservation_mode);
        
        // Get current stock
        $product = wc_get_product($product_id);
        if (!$product) {
            $this->logger->log('warning', 'Failed to load WooCommerce product', [
                'product_id' => $product_id,
                'moysklad_id' => $moysklad_id
            ]);
            return false;
        }
        
        $current_stock = $product->get_stock_quantity();
        
        // Only update if stock has changed
        if ($current_stock === $available_stock) {
            return false;
        }
        
        // Update stock
        try {
            wc_update_product_stock($product_id, $available_stock);
            
            $this->logger->log('info', 'Stock updated', [
                'product_id' => $product_id,
                'moysklad_id' => $moysklad_id,
                'old_stock' => $current_stock,
                'new_stock' => $available_stock,
                'reservation_mode' => $reservation_mode
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->log('error', 'Failed to update stock: ' . $e->getMessage(), [
                'product_id' => $product_id,
                'moysklad_id' => $moysklad_id
            ]);
            return false;
        }
    }
    
    /**
     * Store stock data in database
     */
    public function store_stock_data(array $stock_data): void {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::STOCK_DATA_TABLE;
        
        foreach ($stock_data as $ms_id => $info) {
            $product_id = $this->get_wc_product_id_by_moysklad_id($ms_id);
            $sku = '';
            
            if ($product_id) {
                $product = wc_get_product($product_id);
                if ($product) {
                    $sku = $product->get_sku();
                }
            }
            
            $data = [
                'moysklad_id' => $ms_id,
                'product_id' => $product_id ?: 0,
                'sku' => $sku,
                'stock' => $info['stock'],
                'reserve' => $info['reserve'] ?? 0,
                'store_id' => $info['store_id'] ?? '',
                'updated_at' => current_time('mysql', true)
            ];
            
            $format = [
                '%s', // moysklad_id
                '%d', // product_id
                '%s', // sku
                '%d', // stock
                '%d', // reserve
                '%s', // store_id
                '%s'  // updated_at
            ];
            
            // Check if record exists
            $exists = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM $table_name WHERE moysklad_id = %s", $ms_id)
            );
            
            if ($exists) {
                // Update existing record
                $wpdb->update(
                    $table_name,
                    $data,
                    ['moysklad_id' => $ms_id],
                    $format,
                    ['%s']
                );
            } else {
                // Insert new record
                $wpdb->insert(
                    $table_name,
                    $data,
                    $format
                );
            }
        }
    }
    
    /**
     * Get WooCommerce product ID by MoySklad ID
     */
    private function get_wc_product_id_by_moysklad_id(string $moysklad_id): int {
        global $wpdb;
        
        // First check our stock data table
        $table_name = $wpdb->prefix . self::STOCK_DATA_TABLE;
        $product_id = $wpdb->get_var(
            $wpdb->prepare("SELECT product_id FROM $table_name WHERE moysklad_id = %s AND product_id > 0", $moysklad_id)
        );
        
        if ($product_id) {
            return (int)$product_id;
        }
        
        // Then check post meta
        $product_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_moysklad_id' AND meta_value = %s LIMIT 1",
                $moysklad_id
            )
        );
        
        if ($product_id) {
            return (int)$product_id;
        }
        
        return 0;
    }
    
    /**
     * Check if sync is locked
     */
    private function is_locked(): bool {
        $lock = get_transient(self::LOCK_KEY);
        
        if (!$lock) {
            return false;
        }
        
        if (is_array($lock) && isset($lock['timestamp'])) {
            $elapsed = time() - $lock['timestamp'];
            
            if ($elapsed > self::LOCK_TIMEOUT) {
                $this->release_lock();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Set sync lock
     */
    private function set_lock(): void {
        set_transient(self::LOCK_KEY, [
            'timestamp' => time(),
            'user_id' => get_current_user_id(),
        ], self::LOCK_TIMEOUT);
    }
    
    /**
     * Release sync lock
     */
    private function release_lock(): void {
        delete_transient(self::LOCK_KEY);
    }
}