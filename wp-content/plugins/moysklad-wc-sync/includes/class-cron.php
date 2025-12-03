<?php
/**
 * Cron Manager with Fixed Scheduling
 *
 * @package MoySklad_WC_Sync
 * @version 2.1.0
 */

declare(strict_types=1);

namespace MoySklad\WC\Sync;

if (!defined('ABSPATH')) {
    exit;
}

class Cron {
    private const CRON_HOOK = 'ms_wc_sync_daily_sync';
    private static ?Cron $instance = null;

    private function __construct() {
        add_filter('cron_schedules', [$this, 'add_custom_schedule']);
        add_action(self::CRON_HOOK, [$this, 'run_sync']);
        add_action('admin_init', [$this, 'ensure_scheduled']);
    }

    public static function get_instance(): Cron {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_custom_schedule(array $schedules): array {
        $schedules['ms_wc_sync_daily'] = [
            'interval' => DAY_IN_SECONDS,
            'display' => __('Daily at 23:50', 'moysklad-wc-sync'),
        ];

        return $schedules;
    }

    public function ensure_scheduled(): void {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            $this->schedule_event();
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

    public function clear_scheduled_event(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            error_log('[MoySklad WC Sync] Cron event unscheduled');
        }
        
        wp_clear_scheduled_hook(self::CRON_HOOK);
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
    
    public static function get_schedule_info(): array {
        $next_run = wp_next_scheduled(self::CRON_HOOK);
        $schedules = wp_get_schedules();
        
        return [
            'is_scheduled' => (bool) $next_run,
            'next_run' => $next_run,
            'next_run_formatted' => $next_run ? date('Y-m-d H:i:s', $next_run) : 'Not scheduled',
            'schedule_exists' => isset($schedules['ms_wc_sync_daily']),
            'hook' => self::CRON_HOOK,
        ];
    }
}