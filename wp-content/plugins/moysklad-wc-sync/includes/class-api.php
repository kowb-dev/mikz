<?php
/**
 * MoySklad API Handler with Detailed Logging
 *
 * @package MoySklad_WC_Sync
 * @version 2.0.1
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
 * API Handler Class with improved error handling and detailed logging
 */
class API {
    private const API_BASE_URL = 'https://api.moysklad.ru/api/remap/1.2';
    private const TIMEOUT = 30;
    private const MAX_RETRIES = 3;

    private string $token;
    private bool $debug_mode = true; // Включить детальное логирование

    public function __construct(?string $token = null) {
        $this->token = $token ?? get_option('ms_wc_sync_api_token', '');
        
        // Логируем инициализацию
        if ($this->debug_mode) {
            $this->log_debug('API class initialized', [
                'token_length' => strlen($this->token),
                'token_present' => !empty($this->token),
                'token_first_chars' => !empty($this->token) ? substr($this->token, 0, 10) . '...' : 'empty'
            ]);
        }
    }

    /**
     * Get products from MoySklad
     *
     * @param int $limit Products per page
     * @param int $offset Offset for pagination
     * @return array|\WP_Error
     */
    public function get_products(int $limit = 100, int $offset = 0): array|\WP_Error {
        $this->log_debug('Getting products', [
            'limit' => $limit,
            'offset' => $offset
        ]);

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
        $this->log_debug('Getting assortment', [
            'limit' => $limit,
            'offset' => $offset
        ]);

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
        $this->log_debug('Testing connection');
        
        $response = $this->get_products(1, 0);
        
        if (is_wp_error($response)) {
            $this->log_debug('Connection test failed', [
                'error_code' => $response->get_error_code(),
                'error_message' => $response->get_error_message(),
                'error_data' => $response->get_error_data()
            ]);
            return $response;
        }

        $this->log_debug('Connection test successful', [
            'response_keys' => array_keys($response)
        ]);

        return true;
    }

    /**
     * Make API request with retry logic and detailed logging
     *
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param int $retry Current retry attempt
     * @return array|\WP_Error
     */
    private function request(string $endpoint, array $params = [], int $retry = 0): array|\WP_Error {
        // Проверка токена
        if (empty($this->token)) {
            $error = new \WP_Error(
                'ms_wc_sync_no_token',
                __('API token is not configured.', 'moysklad-wc-sync')
            );
            $this->log_error('No API token configured');
            return $error;
        }

        // Построение URL
        $url = self::API_BASE_URL . $endpoint;

        if (!empty($params)) {
            $url = add_query_arg($params, $url);
        }

        // Подготовка заголовков
        $headers = [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json;charset=utf-8',
            'Content-Type' => 'application/json;charset=utf-8',
        ];

        // Логирование запроса
        $this->log_debug('Making API request', [
            'endpoint' => $endpoint,
            'url' => $url,
            'params' => $params,
            'retry_attempt' => $retry,
            'headers' => [
                'Authorization' => 'Bearer ' . substr($this->token, 0, 10) . '...',
                'Accept' => 'application/json;charset=utf-8',
                'Content-Type' => 'application/json;charset=utf-8',
            ]
        ]);

        // Выполнение запроса
        $request_start = microtime(true);
        $response = wp_remote_get($url, [
            'timeout' => self::TIMEOUT,
            'headers' => $headers,
        ]);
        $request_duration = microtime(true) - $request_start;

        // Проверка на ошибки WordPress
        if (is_wp_error($response)) {
            $this->log_error('WordPress request error', [
                'error_code' => $response->get_error_code(),
                'error_message' => $response->get_error_message(),
                'retry_attempt' => $retry,
                'duration' => $request_duration
            ]);

            // Повторная попытка с экспоненциальной задержкой
            if ($retry < self::MAX_RETRIES) {
                $sleep_time = 2 ** $retry;
                $this->log_debug("Retrying in {$sleep_time} seconds...");
                sleep($sleep_time);
                return $this->request($endpoint, $params, $retry + 1);
            }
            
            return $response;
        }

        // Получение деталей ответа
        $response_code = wp_remote_retrieve_response_code($response);
        $response_message = wp_remote_retrieve_response_message($response);
        $body = wp_remote_retrieve_body($response);
        $response_headers = wp_remote_retrieve_headers($response);

        // Детальное логирование ответа
        $this->log_debug('API response received', [
            'response_code' => $response_code,
            'response_message' => $response_message,
            'duration' => $request_duration,
            'body_length' => strlen($body),
            'retry_attempt' => $retry,
            'headers' => $response_headers->getAll()
        ]);

        // Обработка ошибок HTTP
        if ($response_code !== 200) {
            // Попытка декодировать тело ошибки
            $error_body = null;
            try {
                $error_body = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $error_body = $body;
            }

            $this->log_error('API returned non-200 status', [
                'response_code' => $response_code,
                'response_message' => $response_message,
                'endpoint' => $endpoint,
                'url' => $url,
                'params' => $params,
                'body' => $error_body,
                'body_raw' => substr($body, 0, 1000), // Первые 1000 символов
                'retry_attempt' => $retry
            ]);

            // Специальная обработка для кода 400
            if ($response_code === 400) {
                $this->log_error('Bad Request (400) - Detailed Analysis', [
                    'possible_causes' => [
                        'Invalid token format or expired token',
                        'Incorrect query parameters',
                        'Invalid filter syntax',
                        'Missing required parameters'
                    ],
                    'token_info' => [
                        'length' => strlen($this->token),
                        'starts_with' => substr($this->token, 0, 10) . '...',
                        'has_spaces' => strpos($this->token, ' ') !== false,
                        'has_newlines' => strpos($this->token, "\n") !== false
                    ],
                    'request_params' => $params
                ]);
            }

            // Специальная обработка для кода 401
            if ($response_code === 401) {
                $this->log_error('Unauthorized (401) - Token authentication failed', [
                    'suggestion' => 'Please check your API token in MoySklad settings',
                    'token_length' => strlen($this->token)
                ]);
            }

            // Специальная обработка для кода 403
            if ($response_code === 403) {
                $this->log_error('Forbidden (403) - Insufficient permissions', [
                    'suggestion' => 'Token may not have required permissions to access this resource'
                ]);
            }

            // Специальная обработка для кода 429
            if ($response_code === 429) {
                $this->log_error('Rate limit exceeded (429)', [
                    'suggestion' => 'Too many requests. Waiting before retry...',
                    'retry_after' => $response_headers['Retry-After'] ?? 'unknown'
                ]);
            }

            return new \WP_Error(
                'ms_wc_sync_api_error',
                sprintf(
                    __('API request failed with code %d: %s', 'moysklad-wc-sync'),
                    $response_code,
                    $response_message
                ),
                [
                    'response_body' => $error_body,
                    'response_code' => $response_code,
                    'response_message' => $response_message,
                    'endpoint' => $endpoint,
                    'url' => $url
                ]
            );
        }

        // Декодирование успешного ответа
        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            
            $this->log_debug('Response decoded successfully', [
                'data_keys' => array_keys($data),
                'rows_count' => isset($data['rows']) ? count($data['rows']) : 'N/A'
            ]);
            
            return $data;
        } catch (\JsonException $e) {
            $this->log_error('JSON decode error', [
                'exception_message' => $e->getMessage(),
                'exception_code' => $e->getCode(),
                'body_preview' => substr($body, 0, 500)
            ]);

            return new \WP_Error(
                'ms_wc_sync_json_error',
                __('Failed to parse API response.', 'moysklad-wc-sync'),
                [
                    'exception' => $e->getMessage(),
                    'body_preview' => substr($body, 0, 500)
                ]
            );
        }
    }

    /**
     * Log debug message
     *
     * @param string $message Log message
     * @param array $context Additional context
     */
    private function log_debug(string $message, array $context = []): void {
        if (!$this->debug_mode) {
            return;
        }

        $log_entry = sprintf(
            '[MoySklad API Debug] %s | %s',
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : ''
        );

        error_log($log_entry);
    }

    /**
     * Log error message
     *
     * @param string $message Error message
     * @param array $context Additional context
     */
    private function log_error(string $message, array $context = []): void {
        $log_entry = sprintf(
            '[MoySklad API ERROR] %s | %s',
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : ''
        );

        error_log($log_entry);
    }
}