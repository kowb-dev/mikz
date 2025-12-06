<?php
/**
 * MoySklad Webhook Handler
 *
 * Handles incoming webhooks from MoySklad for real-time stock updates
 *
 * @package MoySklad_WC_Sync
 * @version 2.2.1
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

class Webhook_Handler {
    private API $api;
    private Logger $logger;
    private Stock_Sync $stock_sync;
    
    public function __construct() {
        // Get API token from options
        $token = get_option('ms_wc_sync_api_token', '');
        
        // Pass token explicitly to API class
        $this->api = new API($token);
        $this->logger = new Logger();
        $this->stock_sync = new Stock_Sync();
        
        // Register webhook endpoint
        add_action('rest_api_init', [$this, 'register_webhook_endpoint']);
    }
    
    /**
     * Register webhook endpoint with WordPress REST API
     */
    public function register_webhook_endpoint(): void {
        register_rest_route('moysklad-wc-sync/v1', '/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'process_webhook'],
            'permission_callback' => [$this, 'verify_webhook'],
        ]);
    }
    
    /**
     * Verify webhook request
     */
    public function verify_webhook(\WP_REST_Request $request): bool {
        // Get webhook secret from settings
        $webhook_secret = get_option('ms_wc_sync_webhook_secret', '');
        
        if (empty($webhook_secret)) {
            $this->logger->log('error', 'Webhook verification failed: No secret configured');
            return false;
        }
        
        // Get authorization header
        $auth_header = $request->get_header('X-MoySklad-Webhook-Signature');
        
        if (empty($auth_header)) {
            $this->logger->log('error', 'Webhook verification failed: No signature header');
            return false;
        }
        
        // Verify signature
        $body = $request->get_body();
        $expected_signature = hash_hmac('sha256', $body, $webhook_secret);
        
        if (!hash_equals($expected_signature, $auth_header)) {
            $this->logger->log('error', 'Webhook verification failed: Invalid signature');
            return false;
        }
        
        return true;
    }
    
    /**
     * Process incoming webhook
     */
    public function process_webhook(\WP_REST_Request $request): \WP_REST_Response {
        $body = $request->get_json_params();
        
        // Update last webhook received timestamp
        update_option('ms_wc_sync_last_webhook_received', time());
        
        $this->logger->log('info', 'Webhook received', [
            'event_type' => $body['eventType'] ?? 'unknown',
            'entity_type' => $body['entityType'] ?? 'unknown',
            'action' => $body['action'] ?? 'unknown'
        ]);
        
        // Check if this is a stock-related webhook
        if (!$this->is_stock_related_webhook($body)) {
            return new \WP_REST_Response([
                'success' => true,
                'message' => 'Webhook received but not stock-related',
            ], 200);
        }
        
        try {
            // Extract entity ID
            $entity_id = $this->extract_entity_id($body);
            
            if (empty($entity_id)) {
                throw new \Exception('Could not extract entity ID from webhook');
            }
            
            // Process stock update for this entity
            $result = $this->process_stock_update($entity_id);
            
            if ($result) {
                return new \WP_REST_Response([
                    'success' => true,
                    'message' => 'Stock updated successfully',
                    'entity_id' => $entity_id
                ], 200);
            } else {
                return new \WP_REST_Response([
                    'success' => false,
                    'message' => 'Failed to update stock',
                    'entity_id' => $entity_id
                ], 500);
            }
            
        } catch (\Exception $e) {
            $this->logger->log('error', 'Webhook processing failed: ' . $e->getMessage(), [
                'webhook_data' => $body
            ]);
            
            return new \WP_REST_Response([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check if webhook is stock-related
     */
    private function is_stock_related_webhook(array $webhook_data): bool {
        // Check for stock-specific webhook (API 1.2+)
        if (isset($webhook_data['entityType']) && $webhook_data['entityType'] === 'stock') {
            return true;
        }
        
        // Check for document types that affect stock
        $stock_affecting_entities = [
            'demand',          // Sales
            'supply',          // Purchases
            'move',            // Transfers
            'enter',           // Stock entries
            'loss',            // Write-offs
            'retaildemand',    // Retail sales
            'retailsalesreturn', // Retail returns
            'salesreturn',     // Sales returns
            'purchasereturn',  // Purchase returns
            'inventory',       // Inventory
            'processing',      // Production
            'processingorder', // Production order
        ];
        
        if (isset($webhook_data['entityType']) && in_array($webhook_data['entityType'], $stock_affecting_entities)) {
            return true;
        }
        
        // Check for product updates
        if (isset($webhook_data['entityType']) && $webhook_data['entityType'] === 'product' && 
            isset($webhook_data['action']) && $webhook_data['action'] === 'UPDATE') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Extract entity ID from webhook data
     */
    private function extract_entity_id(array $webhook_data): string {
        // Extract from entity URL
        if (isset($webhook_data['entityHref'])) {
            return preg_replace('#.*/([^/]+)$#', '$1', $webhook_data['entityHref']);
        }
        
        // Extract from entity meta
        if (isset($webhook_data['meta']['href'])) {
            return preg_replace('#.*/([^/]+)$#', '$1', $webhook_data['meta']['href']);
        }
        
        // Extract from entity
        if (isset($webhook_data['entity']['id'])) {
            return $webhook_data['entity']['id'];
        }
        
        return '';
    }
    
    /**
     * Process stock update for a specific entity
     */
    private function process_stock_update(string $entity_id): bool {
        // Get stock data for this entity
        $stock_response = $this->api->request("/report/stock/bystore/all/by/product/{$entity_id}");
        
        if (is_wp_error($stock_response)) {
            $this->logger->log('error', 'Failed to get stock data: ' . $stock_response->get_error_message(), [
                'entity_id' => $entity_id
            ]);
            return false;
        }
        
        // Extract stock data
        $stock_data = [];
        
        if (!empty($stock_response['rows'])) {
            foreach ($stock_response['rows'] as $item) {
                // Extract product ID
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
        }
        
        if (empty($stock_data)) {
            $this->logger->log('warning', 'No stock data found for entity', [
                'entity_id' => $entity_id
            ]);
            return false;
        }
        
        // Update stock for each product
        $success = false;
        
        foreach ($stock_data as $ms_id => $stock_info) {
            $result = $this->stock_sync->update_product_stock($ms_id, $stock_info);
            
            if ($result) {
                $success = true;
            }
        }
        
        // Store updated stock data
        $this->stock_sync->store_stock_data($stock_data);
        
        return $success;
    }
    
    /**
     * Register webhooks with MoySklad
     */
    public function register_moysklad_webhooks(): bool {
        $webhook_url = rest_url('moysklad-wc-sync/v1/webhook');
        $webhook_secret = get_option('ms_wc_sync_webhook_secret', '');
        
        if (empty($webhook_secret)) {
            // Generate a random secret if none exists
            $webhook_secret = wp_generate_password(32, false);
            update_option('ms_wc_sync_webhook_secret', $webhook_secret);
        }
        
        $this->logger->log('info', 'Registering webhooks with MoySklad', [
            'webhook_url' => $webhook_url
        ]);
        
        // Register webhook for product updates
        $product_webhook = $this->api->register_webhook([
            'url' => $webhook_url,
            'action' => 'UPDATE',
            'entityType' => 'product',
            'diffType' => 'FIELDS',
            'fields' => ['stock', 'quantity', 'reserve'],
        ]);
        
        if (is_wp_error($product_webhook)) {
            $this->logger->log('error', 'Failed to register product webhook: ' . $product_webhook->get_error_message());
            return false;
        }
        
        // Register webhooks for stock-affecting documents
        $stock_entities = ['demand', 'supply', 'move', 'enter', 'loss'];
        $success = true;
        
        foreach ($stock_entities as $entity) {
            $entity_webhook = $this->api->register_webhook([
                'url' => $webhook_url,
                'action' => 'UPDATE',
                'entityType' => $entity
            ]);
            
            if (is_wp_error($entity_webhook)) {
                $this->logger->log('error', "Failed to register {$entity} webhook: " . $entity_webhook->get_error_message());
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Check if webhooks are registered
     */
    public function check_webhooks(): array {
        $response = $this->api->request('/entity/webhook');
        
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
                'webhooks' => []
            ];
        }
        
        $webhooks = [];
        $webhook_url = rest_url('moysklad-wc-sync/v1/webhook');
        
        if (!empty($response['rows'])) {
            foreach ($response['rows'] as $webhook) {
                if ($webhook['url'] === $webhook_url) {
                    $webhooks[] = [
                        'id' => $webhook['id'],
                        'entityType' => $webhook['entityType'],
                        'action' => $webhook['action'],
                        'enabled' => $webhook['enabled']
                    ];
                }
            }
        }
        
        return [
            'success' => true,
            'count' => count($webhooks),
            'webhooks' => $webhooks
        ];
    }
}