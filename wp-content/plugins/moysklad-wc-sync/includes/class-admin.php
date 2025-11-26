<?php
/**
 * Admin Interface
 *
 * @package MoySklad_WC_Sync
 * @version 2.0.0
 * 
 * FILE: class-admin.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/includes/class-admin.php
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Interface with improved security
 */
class Admin {
    private static ?Admin $instance = null;

    private function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_ms_wc_sync_manual', [$this, 'handle_manual_sync']);
        add_action('wp_ajax_ms_wc_sync_test_connection', [$this, 'handle_test_connection']);
    }

    public static function get_instance(): Admin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add admin menu
     */
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

    /**
     * Register plugin settings
     */
    public function register_settings(): void {
        register_setting('ms_wc_sync_settings', 'ms_wc_sync_api_token', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ]);
    }

    /**
     * Enqueue admin scripts and styles
     *
     * @param string $hook Current admin page hook
     */
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
            'strings' => [
                'sync_in_progress' => __('Synchronization in progress...', 'moysklad-wc-sync'),
                'sync_complete' => __('Synchronization completed', 'moysklad-wc-sync'),
                'sync_error' => __('Synchronization failed', 'moysklad-wc-sync'),
                'test_success' => __('Connection successful', 'moysklad-wc-sync'),
                'test_failed' => __('Connection failed', 'moysklad-wc-sync'),
            ],
        ]);
    }

    /**
     * Render settings page
     */
    public function render_settings_page(): void {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'moysklad-wc-sync'));
        }

        $logger = new Logger();
        $logs = $logger->get_logs(20);
        $last_run = get_option('ms_wc_sync_last_run');
        $last_results = get_option('ms_wc_sync_last_results', []);
        $next_run = wp_next_scheduled('ms_wc_sync_daily_sync');

        require MS_WC_SYNC_PLUGIN_DIR . 'templates/admin-page.php';
    }

    /**
     * Handle manual sync AJAX request
     */
    public function handle_manual_sync(): void {
        check_ajax_referer('ms_wc_sync_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Insufficient permissions.', 'moysklad-wc-sync')]);
        }

        if (get_transient('ms_wc_sync_running')) {
            wp_send_json_error(['message' => __('Sync is already running.', 'moysklad-wc-sync')]);
        }

        set_transient('ms_wc_sync_running', true, HOUR_IN_SECONDS);

        try {
            $sync_engine = new Sync_Engine();
            $results = $sync_engine->run_sync();

            update_option('ms_wc_sync_last_run', current_time('mysql', true));
            update_option('ms_wc_sync_last_results', $results);

            wp_send_json_success($results);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        } finally {
            delete_transient('ms_wc_sync_running');
        }
    }

    /**
     * Handle test connection AJAX request
     */
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
}