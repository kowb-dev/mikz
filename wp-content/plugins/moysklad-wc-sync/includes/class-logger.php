<?php
/**
 * Logger Class
 *
 * @package MoySklad_WC_Sync
 * @version 2.2.0
 * 
 * FILE: class-logger.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/includes/class-logger.php
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Logger with prepared statements and better security
 */
class Logger {
    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'ms_wc_sync_logs';
    }

    /**
     * Create logs table
     */
    public static function create_table(): void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'ms_wc_sync_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            log_time datetime NOT NULL,
            log_level varchar(20) NOT NULL,
            message text NOT NULL,
            context longtext DEFAULT NULL,
            PRIMARY KEY (id),
            KEY log_time (log_time),
            KEY log_level (log_level),
            KEY log_level_time (log_level, log_time)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Log message with context
     *
     * @param string $level Log level (info, error, warning, debug)
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function log(string $level, string $message, array $context = []): void {
        global $wpdb;

        $allowed_levels = ['info', 'error', 'warning', 'debug'];
        if (!in_array($level, $allowed_levels, true)) {
            $level = 'info';
        }

        $wpdb->insert(
            $this->table_name,
            [
                'log_time' => current_time('mysql', true),
                'log_level' => $level,
                'message' => wp_kses_post($message),
                'context' => !empty($context) ? wp_json_encode($context) : null,
            ],
            ['%s', '%s', '%s', '%s']
        );

        $this->cleanup_old_logs();
    }

    /**
     * Get recent logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function get_logs(int $limit = 50): array {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} ORDER BY log_time DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        );

        return $results ?: [];
    }

    /**
     * Clear all logs
     */
    public function clear_logs(): void {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
    }

    /**
     * Delete logs older than 30 days
     */
    private function cleanup_old_logs(): void {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table_name} WHERE log_time < DATE_SUB(NOW(), INTERVAL %d DAY)",
                30
            )
        );
    }
}