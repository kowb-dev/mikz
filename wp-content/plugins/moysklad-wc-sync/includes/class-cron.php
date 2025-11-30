<?php
/**
 * Cron Manager with Fixed Scheduling
 *
 * @package MoySklad_WC_Sync
 * @version 2.1.0
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
 * Cron Manager with improved scheduling
 */
class Cron {
    private const CRON_HOOK = 'ms_wc_sync_daily_sync';
    private static ?Cron $instance = null;

    private function __construct() {
        add_filter('cron_schedules', [$this, 'add_custom_schedule']);
        add_action(self::CRON_HOOK, [$this, 'run_sync']);
        
        // Проверяем и восстанавливаем расписание если оно отсутствует
        add_action('admin_init', [$this, 'ensure_scheduled']);
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
     * Ensure cron is scheduled (runs on admin_init)
     */
    public function ensure_scheduled(): void {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            $this->schedule_event();
        }
    }

    /**
     * Schedule cron event
     */
    public function schedule_event(): void {
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            $timestamp = $this->get_next_runtime();
            $scheduled = wp_schedule_event($timestamp, 'ms_wc_sync_daily', self::CRON_HOOK);
            
            if ($scheduled === false) {
                error_log('[MoySklad WC Sync] Failed to schedule cron event');
            } else {
                error_log('[MoySklad WC Sync] Cron event scheduled for ' . date('Y-m-d H:i:s', $timestamp));
            }
        }
    }

    /**
     * Clear scheduled event
     */
    public function clear_scheduled_event(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            error_log('[MoySklad WC Sync] Cron event unscheduled');
        }
        
        // Очистить все события этого типа (на случай дублей)
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }

    /**
     * Get next runtime timestamp (23:50 in site timezone)
     *
     * @return int Unix timestamp
     */
    private function get_next_runtime(): int {
        $timezone = wp_timezone();
        $now = new \DateTime('now', $timezone);
        
        $target = clone $now;
        $target->setTime(23, 50, 0);

        // Если уже прошло 23:50 сегодня, планируем на завтра
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
        
        // Проверяем блокировку
        if (get_transient($lock_key)) {
            error_log('[MoySklad WC Sync] Cron sync skipped - already running');
            return;
        }

        // Устанавливаем блокировку
        set_transient($lock_key, [
            'timestamp' => time(),
            'user_id' => 0, // Cron запуск
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
            
        } catch (\Exception $e) {
            error_log('[MoySklad WC Sync] Cron sync failed: ' . $e->getMessage());
        } finally {
            delete_transient($lock_key);
        }
    }
    
    /**
     * Get cron schedule info for debugging
     *
     * @return array
     */
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