<?php
/**
 * Admin Interface with Progress Bar and Reset
 *
 * @package MoySklad_WC_Sync
 * @version 2.1.0
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

class Admin {
    private static ?Admin $instance = null;
    
    private const SYNC_LOCK_KEY = 'ms_wc_sync_running';
    private const SYNC_LOCK_TIMEOUT = 300;

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // Перепланируем cron при сохранении настроек
        add_action('update_option_ms_wc_sync_interval', [$this, 'reschedule_on_settings_change'], 10, 2);
        add_action('update_option_ms_wc_sync_stock_interval', [$this, 'reschedule_stock_sync_on_settings_change'], 10, 2);
        add_action('update_option_ms_wc_sync_use_webhooks', [$this, 'handle_webhooks_setting_change'], 10, 2);
        
        add_action('wp_ajax_ms_wc_sync_manual', [$this, 'handle_manual_sync']);
        add_action('wp_ajax_ms_wc_sync_test_connection', [$this, 'handle_test_connection']);
        add_action('wp_ajax_ms_wc_sync_get_progress', [$this, 'handle_get_progress']);
        add_action('wp_ajax_ms_wc_sync_reset_lock', [$this, 'handle_reset_lock']);
        add_action('wp_ajax_ms_wc_sync_reschedule', [$this, 'handle_reschedule_cron']);
        add_action('wp_ajax_ms_wc_sync_stock_manual', [$this, 'handle_stock_sync']);
        add_action('wp_ajax_ms_wc_sync_register_webhooks', [$this, 'handle_register_webhooks']);
    }

    public static function get_instance(): Admin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_admin_menu(): void {
        add_submenu_page(
            'woocommerce',
            __('MoySklad Sync', 'moysklad-wc-sync'),
            __('MoySklad Sync', 'moysklad-wc-sync'),
            'manage_woocommerce',
            'ms-wc-sync',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void {
        // API Settings
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_api_token', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        
        // General Sync Settings
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_interval', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'daily',
        ]);
        
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_batch_size', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 50,
        ]);
        
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_max_time', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 180,
        ]);
        
        // Stock Sync Settings
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_use_webhooks', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'no',
        ]);
        
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_webhook_secret', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => wp_generate_password(32, false),
        ]);
        
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_stock_interval', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'ms_wc_sync_10min',
        ]);
        
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_store_id', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
        
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_reservation_mode', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'ignore',
        ]);
    }

    public function enqueue_scripts(string $hook): void {
        if ($hook !== 'woocommerce_page_ms-wc-sync') {
            return;
        }

        wp_enqueue_style(
            'ms-wc-sync-admin',
            MS_WC_SYNC_PLUGIN_URL . 'assets/css/admin.css',
            [],
            MS_WC_SYNC_VERSION
        );

        wp_enqueue_script(
            'ms-wc-sync-admin',
            MS_WC_SYNC_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            MS_WC_SYNC_VERSION,
            true
        );

        wp_localize_script('ms-wc-sync-admin', 'msWcSync', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ms_wc_sync_nonce'),
            'is_locked' => $this->is_sync_locked(),
            'strings' => [
                'sync_in_progress' => __('Synchronization in progress...', 'moysklad-wc-sync'),
                'sync_complete' => __('Synchronization completed', 'moysklad-wc-sync'),
                'sync_error' => __('Synchronization failed', 'moysklad-wc-sync'),
                'test_success' => __('Connection successful', 'moysklad-wc-sync'),
                'test_failed' => __('Connection failed', 'moysklad-wc-sync'),
                'reset_confirm' => __('Are you sure you want to reset the sync lock? Do this only if sync is stuck.', 'moysklad-wc-sync'),
                'reset_success' => __('Sync lock has been reset', 'moysklad-wc-sync'),
                'reset_failed' => __('Failed to reset sync lock', 'moysklad-wc-sync'),
            ],
        ]);
    }

    public function render_settings_page(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'moysklad-wc-sync'));
        }

        $logger = new Logger();
        $logs = $logger->get_logs(20);
        
        // Full sync info
        $last_run = get_option('ms_wc_sync_last_run');
        $last_results = get_option('ms_wc_sync_last_results', []);
        
        // Stock sync info
        $stock_last_run = get_option('ms_wc_sync_stock_last_run');
        $stock_last_results = get_option('ms_wc_sync_stock_last_results', []);
        
        // Schedule info
        $schedule_info = Cron::get_schedule_info();
        $is_locked = $this->is_sync_locked();
        $lock_info = $this->get_sync_lock_info();
        
        // Webhook info
        $webhook_handler = new Webhook_Handler();
        $webhook_status = $webhook_handler->check_webhooks();
        $webhook_url = rest_url('moysklad-wc-sync/v1/webhook');

        require MS_WC_SYNC_PLUGIN_DIR . 'templates/admin-page.php';
    }

    public function handle_manual_sync(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        if ($this->is_sync_locked()) {
            wp_send_json_error(['message' => __('Sync is already running. If stuck, use Reset button.', 'moysklad-wc-sync')]);
        }

        $this->set_sync_lock();

        try {
            $sync_engine = new Sync_Engine();
            $results = $sync_engine->run_sync();

            update_option('ms_wc_sync_last_run', current_time('mysql', true));
            update_option('ms_wc_sync_last_results', $results);

            $this->release_sync_lock();
            
            wp_send_json_success($results);
        } catch (\Exception $e) {
            $this->release_sync_lock();
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function handle_test_connection(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        $api = new API();
        $result = $api->test_connection();

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }

        wp_send_json_success(['message' => __('Connection successful!', 'moysklad-wc-sync')]);
    }

    public function handle_get_progress(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        $progress = get_option('ms_wc_sync_progress', [
            'percent' => 0,
            'message' => '',
            'timestamp' => 0,
        ]);

        wp_send_json_success($progress);
    }

    public function handle_reset_lock(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        $this->release_sync_lock();
        delete_option('ms_wc_sync_progress');

        wp_send_json_success(['message' => __('Sync lock has been reset', 'moysklad-wc-sync')]);
    }
    
    /**
     * Handle stock sync AJAX request
     */
    public function handle_stock_sync(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        try {
            $stock_sync = new Stock_Sync();
            $results = $stock_sync->run_sync();

            update_option('ms_wc_sync_stock_last_run', current_time('mysql', true));
            update_option('ms_wc_sync_stock_last_results', $results);
            
            wp_send_json_success($results);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    /**
     * Handle webhook registration AJAX request
     */
    public function handle_register_webhooks(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        try {
            $webhook_handler = new Webhook_Handler();
            $result = $webhook_handler->register_moysklad_webhooks();
            
            if ($result) {
                $webhook_status = $webhook_handler->check_webhooks();
                wp_send_json_success($webhook_status);
            } else {
                wp_send_json_error(['message' => __('Failed to register webhooks', 'moysklad-wc-sync')]);
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }
    
    public function handle_reschedule_cron(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        $cron = Cron::get_instance();
        $cron->schedule_event();
        
        $next_run = wp_next_scheduled('ms_wc_sync_daily_sync');
        $next_run_formatted = $next_run ? date('Y-m-d H:i:s', $next_run) : 'Not scheduled';

        wp_send_json_success([
            'message' => __('Cron schedule updated successfully', 'moysklad-wc-sync'),
            'next_run' => $next_run_formatted
        ]);
    }
    
    /**
     * Get available sync intervals
     */
    public static function get_sync_intervals(): array {
        return [
            'hourly' => __('Every hour', 'moysklad-wc-sync'),
            'twicedaily' => __('Twice daily', 'moysklad-wc-sync'),
            'daily' => __('Once daily', 'moysklad-wc-sync'),
            'weekly' => __('Once weekly', 'moysklad-wc-sync'),
        ];
    }
    
    /**
     * Reschedule cron when interval setting changes
     */
    public function reschedule_on_settings_change($old_value, $new_value): void {
        if ($old_value !== $new_value) {
            $cron = Cron::get_instance();
            $cron->schedule_event();
        }
    }
    
    /**
     * Reschedule stock sync when interval setting changes
     */
    public function reschedule_stock_sync_on_settings_change($old_value, $new_value): void {
        if ($old_value !== $new_value) {
            $cron = Cron::get_instance();
            $cron->clear_stock_sync_event();
            $cron->schedule_stock_sync();
        }
    }
    
    /**
     * Handle webhooks setting change
     */
    public function handle_webhooks_setting_change($old_value, $new_value): void {
        if ($old_value !== $new_value) {
            if ($new_value === 'yes') {
                // Register webhooks with MoySklad
                $webhook_handler = new Webhook_Handler();
                $webhook_handler->register_moysklad_webhooks();
            }
        }
    }
    
    /**
     * Get available reservation modes
     */
    public static function get_reservation_modes(): array {
        return [
            'ignore' => __('Ignore reservations (use total stock)', 'moysklad-wc-sync'),
            'subtract' => __('Subtract reservations (available = total - reserved)', 'moysklad-wc-sync'),
            'free_to_sell' => __('Use "Free to sell" quantity if available', 'moysklad-wc-sync'),
        ];
    }

    private function is_sync_locked(): bool {
        $lock = get_transient(self::SYNC_LOCK_KEY);
        
        if (!$lock) {
            return false;
        }

        if (is_array($lock) && isset($lock['timestamp'])) {
            $elapsed = time() - $lock['timestamp'];
            
            if ($elapsed > self::SYNC_LOCK_TIMEOUT) {
                $this->release_sync_lock();
                return false;
            }
        }

        return true;
    }

    private function set_sync_lock(): void {
        set_transient(self::SYNC_LOCK_KEY, [
            'timestamp' => time(),
            'user_id' => get_current_user_id(),
        ], self::SYNC_LOCK_TIMEOUT);
    }

    private function release_sync_lock(): void {
        delete_transient(self::SYNC_LOCK_KEY);
    }

    private function get_sync_lock_info(): ?array {
        $lock = get_transient(self::SYNC_LOCK_KEY);
        
        if (!$lock || !is_array($lock)) {
            return null;
        }

        $elapsed = time() - ($lock['timestamp'] ?? 0);
        
        return [
            'timestamp' => $lock['timestamp'] ?? 0,
            'user_id' => $lock['user_id'] ?? 0,
            'elapsed' => $elapsed,
            'is_expired' => $elapsed > self::SYNC_LOCK_TIMEOUT,
        ];
    }
}