<?php
/**
 * Plugin Name: MKX Live Search
 * Plugin URI: https://kowb.ru
 * Description: Advanced live search plugin for WooCommerce with category tags and AJAX filtering
 * Version: 1.0.1
 * Author: KB
 * Author URI: https://kowb.ru
 * Text Domain: mkx-live-search
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.0
 *
 * @package MKX_Live_Search
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('MKX_LS_VERSION')) {
    define('MKX_LS_VERSION', '1.0.1');
}

if (!defined('MKX_LS_PLUGIN_DIR')) {
    define('MKX_LS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('MKX_LS_PLUGIN_URL')) {
    define('MKX_LS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

/**
 * Main MKX Live Search Class
 *
 * @class MKX_Live_Search
 */
final class MKX_Live_Search {

    /**
     * Plugin instance
     *
     * @var MKX_Live_Search
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return MKX_Live_Search
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'check_dependencies'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('init', array($this, 'load_textdomain'));
        add_action('before_woocommerce_init', array($this, 'declare_wc_compatibility'));
    }

    /**
     * Declare WooCommerce compatibility
     *
     * @return void
     */
    public function declare_wc_compatibility() {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
        }
    }

    /**
     * Check plugin dependencies
     *
     * @return void
     */
    public function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
    }

    /**
     * WooCommerce missing notice
     *
     * @return void
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="error">
            <p><?php esc_html_e('MKX Live Search requires WooCommerce to be installed and activated.', 'mkx-live-search'); ?></p>
        </div>
        <?php
    }

    /**
     * Include required files
     *
     * @return void
     */
    private function includes() {
        require_once MKX_LS_PLUGIN_DIR . 'inc/class-mkx-search-query.php';
        require_once MKX_LS_PLUGIN_DIR . 'inc/class-mkx-search-ajax.php';
        require_once MKX_LS_PLUGIN_DIR . 'inc/class-mkx-search-results.php';

        MKX_Search_Query::instance();
        MKX_Search_AJAX::instance();
        MKX_Search_Results::instance();
    }

    /**
     * Load plugin textdomain
     *
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain('mkx-live-search', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    /**
     * Enqueue plugin assets
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'mkx-live-search-css',
            MKX_LS_PLUGIN_URL . 'assets/css/mkx-live-search.css',
            array(),
            MKX_LS_VERSION
        );

        wp_enqueue_script(
            'mkx-live-search-js',
            MKX_LS_PLUGIN_URL . 'assets/js/mkx-live-search.js',
            array('jquery'),
            time(),
            true
        );

        wp_enqueue_script(
            'mkx-live-search-init-js',
            MKX_LS_PLUGIN_URL . 'assets/js/mkx-live-search-init.js',
            array('jquery', 'mkx-live-search-js'),
            time(),
            true
        );

        wp_localize_script('mkx-live-search-js', 'mkxLiveSearch', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkx_live_search_nonce'),
            'minChars' => apply_filters('mkx_live_search_min_chars', 2),
            'delay' => apply_filters('mkx_live_search_delay', 300),
            'maxResults' => apply_filters('mkx_live_search_max_results', 10),
            'strings' => array(
                'searching' => __('Searching...', 'mkx-live-search'),
                'noResults' => __('Nothing found', 'mkx-live-search'),
                'minCharsText' => __('Enter at least 2 characters', 'mkx-live-search'),
            )
        ));
    }
}

/**
 * Initialize plugin
 *
 * @return MKX_Live_Search
 */
function mkx_live_search() {
    return MKX_Live_Search::instance();
}

mkx_live_search();