<?php
/**
 * Synchronization Engine
 *
 * @package MoySklad_WC_Sync
 * @version 2.0.0
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
 * Sync Engine with improved error handling and batch processing
 */
class Sync_Engine {
    private API $api;
    private Logger $logger;
    private array $assortment_cache = [];

    public function __construct() {
        $this->api = new API();
        $this->logger = new Logger();
    }

    /**
     * Run full synchronization
     *
     * @return array Sync results
     */
    public function run_sync(): array {
        $start_time = time();
        $results = [
            'success' => 0,
            'failed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => [],
            'duration' => 0,
        ];

        $this->logger->log('info', 'Sync started');

        try {
            $this->load_assortment_data();
            $this->sync_all_products($results);
        } catch (\Exception $e) {
            $this->logger->log('error', 'Sync failed: ' . $e->getMessage(), [
                'exception' => $e->getTrace()
            ]);
            $results['errors'][] = $e->getMessage();
        }

        $results['duration'] = time() - $start_time;

        $this->logger->log('info', sprintf(
            'Sync completed: %d success, %d failed, %d created, %d updated (Duration: %d seconds)',
            $results['success'],
            $results['failed'],
            $results['created'],
            $results['updated'],
            $results['duration']
        ));

        return $results;
    }

    /**
     * Load assortment data into cache
     */
    private function load_assortment_data(): void {
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->api->get_assortment($limit, $offset);

            if (is_wp_error($response)) {
                $this->logger->log('error', 'Failed to load assortment: ' . $response->get_error_message());
                break;
            }

            if (empty($response['rows'])) {
                break;
            }

            foreach ($response['rows'] as $item) {
                if (isset($item['id'])) {
                    $this->assortment_cache[$item['id']] = $item;
                }
            }

            $offset += $limit;
            $has_more = count($response['rows']) === $limit;

        } while ($has_more);
    }

    /**
     * Sync all products from MoySklad
     *
     * @param array $results Results array passed by reference
     */
    private function sync_all_products(array &$results): void {
        $offset = 0;
        $limit = 100;

        do {
            $response = $this->api->get_products($limit, $offset);

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                $this->logger->log('error', 'API request failed: ' . $error_message);
                $results['errors'][] = $error_message;
                break;
            }

            if (empty($response['rows'])) {
                break;
            }

            foreach ($response['rows'] as $ms_product) {
                $result = $this->sync_product($ms_product);

                if ($result['success']) {
                    $results['success']++;
                    $results[$result['action']]++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = $result['error'];
                }
            }

            $offset += $limit;
            $has_more = count($response['rows']) === $limit;

        } while ($has_more);
    }

    /**
     * Sync single product
     *
     * @param array $ms_product MoySklad product data
     * @return array Result of sync operation
     */
    private function sync_product(array $ms_product): array {
        $sku = $ms_product['externalCode'] ?? '';

        if (empty($sku)) {
            return [
                'success' => false,
                'error' => sprintf(
                    __('Product "%s" has no SKU (externalCode).', 'moysklad-wc-sync'),
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

            $this->update_product_data($product, $ms_product);

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
     */
    private function update_product_data(\WC_Product $product, array $ms_product): void {
        $product->set_name(sanitize_text_field($ms_product['name']));
        $product->set_sku(sanitize_text_field($ms_product['externalCode']));

        if (isset($ms_product['description'])) {
            $product->set_description(wp_kses_post($ms_product['description']));
        }

        $assortment_data = $this->assortment_cache[$ms_product['id']] ?? null;

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
}