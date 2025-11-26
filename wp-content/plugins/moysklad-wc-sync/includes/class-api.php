<?php
/**
 * MoySklad API Handler
 *
 * @package MoySklad_WC_Sync
 * @version 2.0.0
 * 
 * FILE: class-api.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/includes/class-api.php
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * API Handler Class with improved error handling and type safety
 */
class API {
    private const API_BASE_URL = 'https://api.moysklad.ru/api/remap/1.2';
    private const TIMEOUT = 30;
    private const MAX_RETRIES = 3;

    private string $token;

    public function __construct(?string $token = null) {
        $this->token = $token ?? get_option('ms_wc_sync_api_token', '');
    }

    /**
     * Get products from MoySklad
     *
     * @param int $limit Products per page
     * @param int $offset Offset for pagination
     * @return array|\WP_Error
     */
    public function get_products(int $limit = 100, int $offset = 0): array|\WP_Error {
        return $this->request('/entity/product', [
            'limit' => $limit,
            'offset' => $offset,
            'filter' => 'archived=false',
        ]);
    }

    /**
     * Get assortment data (stock and prices)
     *
     * @param int $limit Items per page
     * @param int $offset Offset for pagination
     * @return array|\WP_Error
     */
    public function get_assortment(int $limit = 100, int $offset = 0): array|\WP_Error {
        return $this->request('/entity/assortment', [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Test API connection
     *
     * @return bool|\WP_Error
     */
    public function test_connection(): bool|\WP_Error {
        $response = $this->get_products(1, 0);
        
        if (is_wp_error($response)) {
            return $response;
        }

        return true;
    }

    /**
     * Make API request with retry logic
     *
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param int $retry Current retry attempt
     * @return array|\WP_Error
     */
    private function request(string $endpoint, array $params = [], int $retry = 0): array|\WP_Error {
        if (empty($this->token)) {
            return new \WP_Error(
                'ms_wc_sync_no_token',
                __('API token is not configured.', 'moysklad-wc-sync')
            );
        }

        $url = self::API_BASE_URL . $endpoint;

        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }

        $response = wp_remote_get($url, [
            'timeout' => self::TIMEOUT,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            // Retry with exponential backoff
            if ($retry < self::MAX_RETRIES) {
                sleep(2 ** $retry);
                return $this->request($endpoint, $params, $retry + 1);
            }
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($response_code !== 200) {
            return new \WP_Error(
                'ms_wc_sync_api_error',
                sprintf(
                    __('API request failed with code %d.', 'moysklad-wc-sync'),
                    $response_code
                ),
                ['response_body' => $body, 'response_code' => $response_code]
            );
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            return $data;
        } catch (\JsonException $e) {
            return new \WP_Error(
                'ms_wc_sync_json_error',
                __('Failed to parse API response.', 'moysklad-wc-sync'),
                ['exception' => $e->getMessage()]
            );
        }
    }
}