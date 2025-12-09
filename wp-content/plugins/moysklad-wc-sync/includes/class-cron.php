<?php
/**
 * Cron Manager with Fixed Scheduling
 *
 * @package MoySklad_WC_Sync
 * @version 2.2.0
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

class Cron {
    private const CRON_HOOK = 'ms_wc_sync_daily_sync';
    private const STOCK_SYNC_HOOK = 'ms_wc_sync_stock_sync';
    private static ?Cron $instance = null;

    private function __construct() {
        add_filter('cron_schedules', [$this, 'add_custom_schedules']);
        add_action(self::CRON_HOOK, [$this, 'run_sync']);
        add_action(self::STOCK_SYNC_HOOK, [$this, 'run_stock_sync']);
        add_action('admin_init', [$this, 'ensure_scheduled']);
    }

    public static function get_instance(): Cron {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_custom_schedules(array $schedules): array {
        // Daily schedule for full sync
        $schedules['ms_wc_sync_daily'] = [
            'interval' => DAY_IN_SECONDS,
            'display' => __('Daily at 23:50', 'moysklad-wc-sync'),
        ];
        
        // Every 10 minutes for stock sync
        $schedules['ms_wc_sync_10min'] = [
            'interval' => 10 * MINUTE_IN_SECONDS,
            'display' => __('Every 10 minutes', 'moysklad-wc-sync'),
        ];
        
        // Every 5 minutes for stock sync
        $schedules['ms_wc_sync_5min'] = [
            'interval' => 5 * MINUTE_IN_SECONDS,
            'display' => __('Every 5 minutes', 'moysklad-wc-sync'),
        ];
        
        // Every 15 minutes for stock sync
        $schedules['ms_wc_sync_15min'] = [
            'interval' => 15 * MINUTE_IN_SECONDS,
            'display' => __('Every 15 minutes', 'moysklad-wc-sync'),
        ];
        
        // Every 30 minutes for stock sync
        $schedules['ms_wc_sync_30min'] = [
            'interval' => 30 * MINUTE_IN_SECONDS,
            'display' => __('Every 30 minutes', 'moysklad-wc-sync'),
        ];
        
        // Hourly for stock sync
        $schedules['ms_wc_sync_hourly'] = [
            'interval' => HOUR_IN_SECONDS,
            'display' => __('Every hour', 'moysklad-wc-sync'),
        ];

        return $schedules;
    }

    public function ensure_scheduled(): void {
        // Ensure full sync is scheduled
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            $this->schedule_event();
        }
        
        // Ensure stock sync is scheduled
        if (!wp_next_scheduled(self::STOCK_SYNC_HOOK)) {
            $this->schedule_stock_sync();
        }
    }

    public function schedule_event(): void {
        $this->clear_scheduled_event();
        
        $timestamp = $this->get_next_runtime();
        $scheduled = wp_schedule_event($timestamp, 'ms_wc_sync_daily', self::CRON_HOOK);
        
        if ($scheduled === false) {
            error_log('[MoySklad WC Sync] Failed to schedule cron event');
        } else {
            error_log('[MoySklad WC Sync] Cron event scheduled for ' . date('Y-m-d H:i:s', $timestamp));
        }
    }
    
    /**
     * Schedule stock sync event
     */
    public function schedule_stock_sync(): void {
        $this->clear_stock_sync_event();
        
        // Get stock sync interval from settings
        $interval = get_option('ms_wc_sync_stock_interval', 'ms_wc_sync_10min');
        
        // Schedule event to start in 1 minute
        $timestamp = time() + MINUTE_IN_SECONDS;
        $scheduled = wp_schedule_event($timestamp, $interval, self::STOCK_SYNC_HOOK);
        
        if ($scheduled === false) {
            error_log('[MoySklad WC Sync] Failed to schedule stock sync event');
        } else {
            error_log('[MoySklad WC Sync] Stock sync scheduled for ' . date('Y-m-d H:i:s', $timestamp) . ' with interval ' . $interval);
        }
    }

    public function clear_scheduled_event(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            error_log('[MoySklad WC Sync] Cron event unscheduled');
        }
        
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }
    
    /**
     * Clear stock sync event
     */
    public function clear_stock_sync_event(): void {
        $timestamp = wp_next_scheduled(self::STOCK_SYNC_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::STOCK_SYNC_HOOK);
            error_log('[MoySklad WC Sync] Stock sync event unscheduled');
        }
        
        wp_clear_scheduled_hook(self::STOCK_SYNC_HOOK);
    }

    private function get_next_runtime(): int {
        $timezone = wp_timezone();
        $now = new \DateTime('now', $timezone);
        
        $target = clone $now;
        $target->setTime(23, 50, 0);

        if ($target <= $now) {
            $target->modify('+1 day');
        }

        return $target->getTimestamp();
    }

    public function run_sync(): void {
        $lock_key = 'ms_wc_sync_running';
        
        if (get_transient($lock_key)) {
            error_log('[MoySklad WC Sync] Cron sync skipped - already running');
            return;
        }

        set_transient($lock_key, [
            'timestamp' => time(),
            'user_id' => 0,
        ], HOUR_IN_SECONDS);

        try {
            error_log('[MoySklad WC Sync] Cron sync started');
            
            $sync_engine = new Sync_Engine();
            $results = $sync_engine->run_sync();

            update_option('ms_wc_sync_last_run', current_time('mysql', true));
            update_option('ms_wc_sync_last_results', $results);
            
            error_log('[MoySklad WC Sync] Cron sync completed: ' . json_encode([
                'success' => $results['success'],
                'failed' => $results['failed'],
                'duration' => $results['duration']
            ]));
            
            delete_transient($lock_key);
            
        } catch (\Exception $e) {
            error_log('[MoySklad WC Sync] Cron sync failed: ' . $e->getMessage());
            delete_transient($lock_key);
        }
    }
    
    /**
     * Run stock-specific sync
     */
    public function run_stock_sync(): void {
        // Check if webhooks are enabled
        $webhooks_enabled = get_option('ms_wc_sync_use_webhooks', 'no') === 'yes';
        
        // If webhooks are enabled and working, skip scheduled sync
        if ($webhooks_enabled && $this->are_webhooks_working()) {
            error_log('[MoySklad WC Sync] Stock sync skipped - webhooks are active');
            return;
        }
        
        try {
            error_log('[MoySklad WC Sync] Stock sync started');
            
            $stock_sync = new Stock_Sync();
            $results = $stock_sync->run_sync();
            
            update_option('ms_wc_sync_stock_last_run', current_time('mysql', true));
            update_option('ms_wc_sync_stock_last_results', $results);
            
            error_log('[MoySklad WC Sync] Stock sync completed: ' . json_encode([
                'success' => $results['success'],
                'updated' => $results['updated'],
                'skipped' => $results['skipped'],
                'duration' => $results['duration']
            ]));
            
        } catch (\Exception $e) {
            error_log('[MoySklad WC Sync] Stock sync failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Check if webhooks are working
     */
    private function are_webhooks_working(): bool {
        $webhook_handler = new Webhook_Handler();
        $webhook_status = $webhook_handler->check_webhooks();
        
        // If webhooks are registered and last webhook was received recently
        if ($webhook_status['success'] && $webhook_status['count'] > 0) {
            $last_webhook = get_option('ms_wc_sync_last_webhook_received', 0);
            
            // If webhook was received in the last hour, consider webhooks working
            if ($last_webhook > (time() - HOUR_IN_SECONDS)) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function get_schedule_info(): array {
        $next_full_run = wp_next_scheduled(self::CRON_HOOK);
        $next_stock_run = wp_next_scheduled(self::STOCK_SYNC_HOOK);
        $schedules = wp_get_schedules();
        
        return [
            'full_sync' => [
                'is_scheduled' => (bool) $next_full_run,
                'next_run' => $next_full_run,
                'next_run_formatted' => $next_full_run ? date('Y-m-d H:i:s', $next_full_run) : 'Not scheduled',
                'schedule_exists' => isset($schedules['ms_wc_sync_daily']),
                'hook' => self::CRON_HOOK,
            ],
            'stock_sync' => [
                'is_scheduled' => (bool) $next_stock_run,
                'next_run' => $next_stock_run,
                'next_run_formatted' => $next_stock_run ? date('Y-m-d H:i:s', $next_stock_run) : 'Not scheduled',
                'interval' => get_option('ms_wc_sync_stock_interval', 'ms_wc_sync_10min'),
                'hook' => self::STOCK_SYNC_HOOK,
            ],
        ];
    }
    
    /**
     * Get available stock sync intervals
     */
    public static function get_stock_sync_intervals(): array {
        return [
            'ms_wc_sync_5min' => __('Every 5 minutes', 'moysklad-wc-sync'),
            'ms_wc_sync_10min' => __('Every 10 minutes', 'moysklad-wc-sync'),
            'ms_wc_sync_15min' => __('Every 15 minutes', 'moysklad-wc-sync'),
            'ms_wc_sync_30min' => __('Every 30 minutes', 'moysklad-wc-sync'),
            'ms_wc_sync_hourly' => __('Every hour', 'moysklad-wc-sync'),
        ];
    }
}