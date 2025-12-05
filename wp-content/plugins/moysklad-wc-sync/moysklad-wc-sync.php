<?php
/**
 * Plugin Name: MoySklad WooCommerce Sync
 * Plugin URI: https://kowb.ru
 * Description: Оптимизированная односторонняя синхронизация из МойСклад в WooCommerce с инкрементальным обновлением остатков
 * Version: 2.2.0
 * Author: KB
 * Author URI: https://kowb.ru
 * Text Domain: moysklad-wc-sync
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * WC requires at least: 7.0
 * WC tested up to: 9.0
 *
 * @package MoySklad_WC_Sync
 *
 * FILE: moysklad-wc-sync.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/moysklad-wc-sync.php
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

/**
 * Declare compatibility with High-Performance Order Storage (HPOS).
 */
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('MS_WC_SYNC_VERSION', '2.2.0');
define('MS_WC_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MS_WC_SYNC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MS_WC_SYNC_MIN_PHP', '8.0');
define('MS_WC_SYNC_MIN_WC', '7.0');

/**
 * Autoloader for plugin classes
 */
spl_autoload_register(function (string $class): void {
    $prefix = 'MoySklad\\WC\\Sync\\';
    $base_dir = MS_WC_SYNC_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . strtolower(str_replace('\\', '-', str_replace('_', '-', $relative_class))) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Main Plugin Class
 */
final class Plugin {
    private static ?Plugin $instance = null;

    private function __construct() {
        $this->init_hooks();
    }

    public static function get_instance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_hooks(): void {
        add_action('plugins_loaded', [$this, 'check_dependencies']);
        
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public function check_dependencies(): void {
        $this->load_textdomain();

        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        if (!$this->check_php_version()) {
            add_action('admin_notices', [$this, 'php_version_notice']);
            return;
        }

        $this->init_components();
    }

    private function is_woocommerce_active(): bool {
        return class_exists('WooCommerce');
    }

    private function check_php_version(): bool {
        return version_compare(PHP_VERSION, MS_WC_SYNC_MIN_PHP, '>=');
    }

    private function init_components(): void {
        Admin::get_instance();
        Cron::get_instance();
        
        // Initialize webhook handler if webhooks are enabled
        if (get_option('ms_wc_sync_use_webhooks', 'no') === 'yes') {
            new Webhook_Handler();
        }
    }

    public function load_textdomain(): void {
        load_plugin_textdomain(
            'moysklad-wc-sync',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    public function woocommerce_missing_notice(): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html__('MoySklad WooCommerce Sync requires WooCommerce to be installed and active.', 'moysklad-wc-sync')
        );
    }

    public function php_version_notice(): void {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            sprintf(
                esc_html__('MoySklad WooCommerce Sync requires PHP version %s or higher. You are running %s.', 'moysklad-wc-sync'),
                MS_WC_SYNC_MIN_PHP,
                PHP_VERSION
            )
        );
    }

    public function activate(): void {
        if (!$this->is_woocommerce_active()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('WooCommerce must be installed and active.', 'moysklad-wc-sync'),
                esc_html__('Plugin Activation Error', 'moysklad-wc-sync'),
                ['back_link' => true]
            );
        }

        if (!$this->check_php_version()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                sprintf(
                    esc_html__('PHP version %s or higher is required.', 'moysklad-wc-sync'),
                    MS_WC_SYNC_MIN_PHP
                ),
                esc_html__('Plugin Activation Error', 'moysklad-wc-sync'),
                ['back_link' => true]
            );
        }

        $previous_version = get_option('ms_wc_sync_version', '0.0.0');
        
        Logger::create_table();
        Stock_Sync::create_tables();
        
        if (version_compare($previous_version, '2.2.0', '<')) {
            $this->run_upgrade_to_220();
        }
        
        update_option('ms_wc_sync_version', MS_WC_SYNC_VERSION);
        
        $cron = Cron::get_instance();
        $cron->schedule_event();
        $cron->schedule_stock_sync();
        
        if (get_option('ms_wc_sync_use_webhooks', 'no') === 'yes') {
            $webhook_handler = new Webhook_Handler();
            $webhook_handler->register_webhook_endpoint();
        }
        
        flush_rewrite_rules();
    }
    
    private function run_upgrade_to_220(): void {
        global $wpdb;
        
        $stock_table = $wpdb->prefix . 'ms_wc_sync_stock_data';
        $logs_table = $wpdb->prefix . 'ms_wc_sync_logs';
        
        $wpdb->query("ALTER TABLE {$stock_table} ADD INDEX IF NOT EXISTS updated_at (updated_at)");
        $wpdb->query("ALTER TABLE {$logs_table} ADD INDEX IF NOT EXISTS log_level_time (log_level, log_time)");
        
        if (!get_option('ms_wc_sync_webhook_secret')) {
            update_option('ms_wc_sync_webhook_secret', wp_generate_password(32, false));
        }
        
        $logger = new Logger();
        $logger->log('info', 'Upgraded to version 2.2.0', [
            'previous_version' => get_option('ms_wc_sync_version', 'unknown'),
            'new_version' => '2.2.0'
        ]);
    }

    public function deactivate(): void {
        $cron = Cron::get_instance();
        $cron->clear_scheduled_event();
        $cron->clear_stock_sync_event();
        
        flush_rewrite_rules();
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}

// Initialize plugin
Plugin::get_instance();