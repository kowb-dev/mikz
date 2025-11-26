<?php
/**
 * Cron Manager
 *
 * @package MoySklad_WC_Sync
 * @version 2.0.0
 * 
 * FILE: class-cron.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/includes/class-cron.php
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Cron Manager with better scheduling
 */
class Cron {
    private const CRON_HOOK = 'ms_wc_sync_daily_sync';
    private static ?Cron $instance = null;

    private function __construct() {
        add_filter('cron_schedules', [$this, 'add_custom_schedule']);
        add_action(self::CRON_HOOK, [$this, 'run_sync']);
    }

    public static function get_instance(): Cron {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add custom cron schedule
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public function add_custom_schedule(array $schedules): array {
        $schedules['ms_wc_sync_daily'] = [
            'interval' => DAY_IN_SECONDS,
            'display' => __('Daily at 23:50', 'moysklad-wc-sync'),
        ];

        return $schedules;
    }

    /**
     * Schedule cron event
     */
    public static function schedule_event(): void {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            $timestamp = self::get_next_runtime();
            wp_schedule_event($timestamp, 'ms_wc_sync_daily', self::CRON_HOOK);
        }
    }

    /**
     * Clear scheduled event
     */
    public static function clear_scheduled_event(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }

    /**
     * Get next runtime timestamp (23:50 in site timezone)
     *
     * @return int Unix timestamp
     */
    private static function get_next_runtime(): int {
        $timezone = wp_timezone();
        $now = new \DateTime('now', $timezone);
        
        $target = clone $now;
        $target->setTime(23, 50, 0);

        if ($target <= $now) {
            $target->modify('+1 day');
        }

        return $target->getTimestamp();
    }

    /**
     * Run synchronization with lock mechanism
     */
    public function run_sync(): void {
        $lock_key = 'ms_wc_sync_running';
        
        if (get_transient($lock_key)) {
            return;
        }

        set_transient($lock_key, true, HOUR_IN_SECONDS);

        try {
            $sync_engine = new Sync_Engine();
            $results = $sync_engine->run_sync();

            update_option('ms_wc_sync_last_run', current_time('mysql', true));
            update_option('ms_wc_sync_last_results', $results);
        } finally {
            delete_transient($lock_key);
        }
    }
}