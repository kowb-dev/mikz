<?php
/**
 * Admin Settings Page Template with Tabs and Fixed Buttons
 *
 * @package MoySklad_WC_Sync
 * @version 2.2.1
 * 
 * FILE: admin-page.php
 * PATH: /wp-content/plugins/moysklad-wc-sync/templates/admin-page.php
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Import classes for use in template
use MoySklad\WC\Sync\Admin;
use MoySklad\WC\Sync\Cron;

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'dashboard';
?>
<div class="wrap">
    <h1><?php echo esc_html__('MoySklad WooCommerce Sync', 'moysklad-wc-sync'); ?></h1>

    <!-- Navigation Tabs -->
    <nav class="nav-tab-wrapper">
        <a href="?page=ms-wc-sync&tab=dashboard" class="nav-tab <?php echo $current_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Dashboard', 'moysklad-wc-sync'); ?>
        </a>
        <a href="?page=ms-wc-sync&tab=settings" class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Settings', 'moysklad-wc-sync'); ?>
        </a>
        <a href="?page=ms-wc-sync&tab=stock" class="nav-tab <?php echo $current_tab === 'stock' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Stock Sync', 'moysklad-wc-sync'); ?>
        </a>
        <a href="?page=ms-wc-sync&tab=logs" class="nav-tab <?php echo $current_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Logs', 'moysklad-wc-sync'); ?>
        </a>
    </nav>

    <div class="ms-wc-sync-dashboard">
        
        <?php if ($current_tab === 'dashboard') : ?>
            <!-- Dashboard Tab -->
            <div class="ms-wc-sync-tab-content">
                <h2><?php echo esc_html__('Synchronization Status', 'moysklad-wc-sync'); ?></h2>
                
                <div class="ms-wc-sync-stats">
                    <div class="ms-wc-sync-stat-card">
                        <h3><?php echo esc_html__('Last Full Sync', 'moysklad-wc-sync'); ?></h3>
                        <p class="ms-wc-sync-stat-value">
                            <?php 
                            if ($last_run) {
                                $dt = new DateTime($last_run, new DateTimeZone('UTC'));
                                $dt->setTimezone(wp_timezone());
                                echo esc_html($dt->format('Y-m-d H:i:s'));
                            } else {
                                echo esc_html__('Never', 'moysklad-wc-sync');
                            }
                            ?>
                        </p>
                    </div>

                    <div class="ms-wc-sync-stat-card">
                        <h3><?php echo esc_html__('Next Full Sync', 'moysklad-wc-sync'); ?></h3>
                        <p class="ms-wc-sync-stat-value">
                            <?php 
                            $next_run = $schedule_info['full_sync']['next_run'] ?? null;
                            if ($next_run) {
                                $dt = new DateTime('@' . $next_run);
                                $dt->setTimezone(wp_timezone());
                                echo esc_html($dt->format('Y-m-d H:i:s'));
                            } else {
                                echo esc_html__('Not scheduled', 'moysklad-wc-sync');
                            }
                            ?>
                        </p>
                    </div>

                    <?php if (!empty($last_results)) : ?>
                        <div class="ms-wc-sync-stat-card">
                            <h3><?php echo esc_html__('Last Results', 'moysklad-wc-sync'); ?></h3>
                            <p class="ms-wc-sync-stat-value">
                                <?php
                                printf(
                                    esc_html__('%1$d success / %2$d failed', 'moysklad-wc-sync'),
                                    absint($last_results['success'] ?? 0),
                                    absint($last_results['failed'] ?? 0)
                                );
                                ?>
                            </p>
                        </div>

                        <div class="ms-wc-sync-stat-card">
                            <h3><?php echo esc_html__('Duration', 'moysklad-wc-sync'); ?></h3>
                            <p class="ms-wc-sync-stat-value">
                                <?php
                                printf(
                                    esc_html__('%d seconds', 'moysklad-wc-sync'),
                                    absint($last_results['duration'] ?? 0)
                                );
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($is_locked) : ?>
                        <div class="ms-wc-sync-stat-card" style="border-color: #d63638;">
                            <h3 style="color: #d63638;"><?php echo esc_html__('Sync Status', 'moysklad-wc-sync'); ?></h3>
                            <p class="ms-wc-sync-stat-value" style="color: #d63638; font-size: 1rem;">
                                <?php echo esc_html__('Running', 'moysklad-wc-sync'); ?>
                            </p>
                            <?php if ($lock_info && !$lock_info['is_expired']) : ?>
                                <p style="font-size: 0.75rem; color: #646970; margin-top: 0.5rem;">
                                    <?php 
                                    printf(
                                        esc_html__('Started %d seconds ago', 'moysklad-wc-sync'),
                                        $lock_info['elapsed']
                                    );
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="ms-wc-sync-actions">
                    <button type="button" class="button button-primary" id="ms-wc-sync-manual" <?php echo $is_locked ? 'disabled' : ''; ?>>
                        <?php echo esc_html__('Run Full Sync Now', 'moysklad-wc-sync'); ?>
                    </button>
                    
                    <?php if ($is_locked) : ?>
                        <button type="button" class="button button-link-delete" id="ms-wc-sync-reset-lock">
                            <?php echo esc_html__('Stop Sync', 'moysklad-wc-sync'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <button type="button" class="button button-secondary" id="ms-wc-sync-reschedule">
                        <?php echo esc_html__('Reschedule Cron', 'moysklad-wc-sync'); ?>
                    </button>
                </div>

                <div id="ms-wc-sync-message"></div>
            </div>

        <?php elseif ($current_tab === 'settings') : ?>
            <!-- Settings Tab -->
            <div class="ms-wc-sync-tab-content">
                <form method="post" action="options.php" class="ms-wc-sync-settings-form">
                    <?php settings_fields('ms_wc_sync_settings'); ?>

                    <h2><?php echo esc_html__('API Settings', 'moysklad-wc-sync'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_api_token">
                                    <?php echo esc_html__('MoySklad API Token', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    type="password"
                                    id="ms_wc_sync_api_token"
                                    name="ms_wc_sync_api_token"
                                    value="<?php echo esc_attr(get_option('ms_wc_sync_api_token')); ?>"
                                    class="regular-text"
                                    autocomplete="off"
                                />
                                <p class="description">
                                    <?php echo esc_html__('Enter your MoySklad API token. You can generate it in your MoySklad account settings.', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h2><?php echo esc_html__('Synchronization Settings', 'moysklad-wc-sync'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_interval">
                                    <?php echo esc_html__('Full Sync Interval', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="ms_wc_sync_interval" name="ms_wc_sync_interval">
                                    <?php
                                    $current_interval = get_option('ms_wc_sync_interval', 'daily');
                                    $intervals = Admin::get_sync_intervals();
                                    foreach ($intervals as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_interval, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('How often should the full product sync run?', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_batch_size">
                                    <?php echo esc_html__('Batch Size', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    type="number"
                                    id="ms_wc_sync_batch_size"
                                    name="ms_wc_sync_batch_size"
                                    value="<?php echo esc_attr(get_option('ms_wc_sync_batch_size', 50)); ?>"
                                    min="10"
                                    max="100"
                                    step="10"
                                />
                                <p class="description">
                                    <?php echo esc_html__('Number of products to process per batch (10-100).', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_max_time">
                                    <?php echo esc_html__('Max Execution Time', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    type="number"
                                    id="ms_wc_sync_max_time"
                                    name="ms_wc_sync_max_time"
                                    value="<?php echo esc_attr(get_option('ms_wc_sync_max_time', 180)); ?>"
                                    min="60"
                                    max="600"
                                    step="30"
                                />
                                <p class="description">
                                    <?php echo esc_html__('Maximum time in seconds for sync execution (60-600).', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <h2><?php echo esc_html__('Price Synchronization', 'moysklad-wc-sync'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <?php echo esc_html__('Price Types', 'moysklad-wc-sync'); ?>
                            </th>
                            <td>
                                <p class="description">
                                    <?php echo esc_html__('The plugin synchronizes two price types from MoySklad:', 'moysklad-wc-sync'); ?>
                                </p>
                                <ul style="list-style: disc; margin-left: 20px;">
                                    <li><strong><?php echo esc_html__('Retail Price (Розница)', 'moysklad-wc-sync'); ?></strong> - <?php echo esc_html__('saved as Regular Price in WooCommerce', 'moysklad-wc-sync'); ?></li>
                                    <li><strong><?php echo esc_html__('Wholesale Price (Опт)', 'moysklad-wc-sync'); ?></strong> - <?php echo esc_html__('saved as custom meta field _wholesale_price', 'moysklad-wc-sync'); ?></li>
                                </ul>
                                <p class="description">
                                    <?php echo esc_html__('To display wholesale prices on the frontend, use a wholesale pricing plugin or custom code.', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <?php submit_button(__('Save Settings', 'moysklad-wc-sync'), 'primary', 'submit', false); ?>
                        <button type="button" class="button button-secondary" id="ms-wc-sync-test-connection">
                            <?php echo esc_html__('Test Connection', 'moysklad-wc-sync'); ?>
                        </button>
                    </p>
                </form>
            </div>

        <?php elseif ($current_tab === 'stock') : ?>
            <!-- Stock Sync Tab -->
            <div class="ms-wc-sync-tab-content">
                <h2><?php echo esc_html__('Stock Synchronization', 'moysklad-wc-sync'); ?></h2>
                
                <div class="ms-wc-sync-stats">
                    <div class="ms-wc-sync-stat-card">
                        <h3><?php echo esc_html__('Last Stock Sync', 'moysklad-wc-sync'); ?></h3>
                        <p class="ms-wc-sync-stat-value">
                            <?php 
                            if ($stock_last_run) {
                                $dt = new DateTime($stock_last_run, new DateTimeZone('UTC'));
                                $dt->setTimezone(wp_timezone());
                                echo esc_html($dt->format('Y-m-d H:i:s'));
                            } else {
                                echo esc_html__('Never', 'moysklad-wc-sync');
                            }
                            ?>
                        </p>
                    </div>

                    <div class="ms-wc-sync-stat-card">
                        <h3><?php echo esc_html__('Next Stock Sync', 'moysklad-wc-sync'); ?></h3>
                        <p class="ms-wc-sync-stat-value">
                            <?php 
                            $next_stock = $schedule_info['stock_sync']['next_run'] ?? null;
                            if ($next_stock) {
                                $dt = new DateTime('@' . $next_stock);
                                $dt->setTimezone(wp_timezone());
                                echo esc_html($dt->format('Y-m-d H:i:s'));
                            } else {
                                echo esc_html__('Not scheduled', 'moysklad-wc-sync');
                            }
                            ?>
                        </p>
                    </div>

                    <?php if (!empty($stock_last_results)) : ?>
                        <div class="ms-wc-sync-stat-card">
                            <h3><?php echo esc_html__('Last Stock Results', 'moysklad-wc-sync'); ?></h3>
                            <p class="ms-wc-sync-stat-value">
                                <?php
                                printf(
                                    esc_html__('%1$d updated / %2$d skipped', 'moysklad-wc-sync'),
                                    absint($stock_last_results['updated'] ?? 0),
                                    absint($stock_last_results['skipped'] ?? 0)
                                );
                                ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="post" action="options.php" class="ms-wc-sync-settings-form">
                    <?php settings_fields('ms_wc_sync_settings'); ?>

                    <h3><?php echo esc_html__('Stock Sync Settings', 'moysklad-wc-sync'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_use_webhooks">
                                    <?php echo esc_html__('Use Webhooks', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="ms_wc_sync_use_webhooks" name="ms_wc_sync_use_webhooks">
                                    <option value="no" <?php selected(get_option('ms_wc_sync_use_webhooks', 'no'), 'no'); ?>>
                                        <?php echo esc_html__('No - Use scheduled sync', 'moysklad-wc-sync'); ?>
                                    </option>
                                    <option value="yes" <?php selected(get_option('ms_wc_sync_use_webhooks', 'no'), 'yes'); ?>>
                                        <?php echo esc_html__('Yes - Real-time updates', 'moysklad-wc-sync'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('Enable webhooks for real-time stock updates from MoySklad.', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_stock_interval">
                                    <?php echo esc_html__('Stock Sync Interval', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="ms_wc_sync_stock_interval" name="ms_wc_sync_stock_interval">
                                    <?php
                                    $current_stock_interval = get_option('ms_wc_sync_stock_interval', 'ms_wc_sync_10min');
                                    $stock_intervals = Cron::get_stock_sync_intervals();
                                    foreach ($stock_intervals as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_stock_interval, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('How often should stock be synchronized? (Only used if webhooks are disabled)', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_store_id">
                                    <?php echo esc_html__('Store ID', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <input
                                    type="text"
                                    id="ms_wc_sync_store_id"
                                    name="ms_wc_sync_store_id"
                                    value="<?php echo esc_attr(get_option('ms_wc_sync_store_id', '')); ?>"
                                    class="regular-text"
                                    placeholder="UUID"
                                />
                                <p class="description">
                                    <?php echo esc_html__('Leave empty to sync stock from all stores, or enter specific MoySklad store ID.', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="ms_wc_sync_reservation_mode">
                                    <?php echo esc_html__('Reservation Mode', 'moysklad-wc-sync'); ?>
                                </label>
                            </th>
                            <td>
                                <select id="ms_wc_sync_reservation_mode" name="ms_wc_sync_reservation_mode">
                                    <?php
                                    $current_mode = get_option('ms_wc_sync_reservation_mode', 'ignore');
                                    $reservation_modes = Admin::get_reservation_modes();
                                    foreach ($reservation_modes as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_mode, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php echo esc_html__('How to handle reserved stock quantities.', 'moysklad-wc-sync'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <?php submit_button(__('Save Stock Settings', 'moysklad-wc-sync'), 'primary', 'submit', false); ?>
                        <button type="button" class="button button-secondary" id="ms-wc-sync-stock-manual">
                            <?php echo esc_html__('Run Stock Sync Now', 'moysklad-wc-sync'); ?>
                        </button>
                    </p>
                </form>

                <?php if (get_option('ms_wc_sync_use_webhooks', 'no') === 'yes') : ?>
                    <div class="ms-wc-sync-webhooks-info">
                        <h3><?php echo esc_html__('Webhook Status', 'moysklad-wc-sync'); ?></h3>
                        
                        <p>
                            <strong><?php echo esc_html__('Webhook URL:', 'moysklad-wc-sync'); ?></strong><br>
                            <code><?php echo esc_html($webhook_url); ?></code>
                        </p>
                        
                        <?php if ($webhook_status['success']) : ?>
                            <p>
                                <strong><?php echo esc_html__('Registered Webhooks:', 'moysklad-wc-sync'); ?></strong>
                                <?php echo absint($webhook_status['count']); ?>
                            </p>
                            
                            <?php if (!empty($webhook_status['webhooks'])) : ?>
                                <table class="widefat">
                                    <thead>
                                        <tr>
                                            <th><?php echo esc_html__('Entity Type', 'moysklad-wc-sync'); ?></th>
                                            <th><?php echo esc_html__('Action', 'moysklad-wc-sync'); ?></th>
                                            <th><?php echo esc_html__('Status', 'moysklad-wc-sync'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($webhook_status['webhooks'] as $webhook) : ?>
                                            <tr>
                                                <td><?php echo esc_html($webhook['entityType']); ?></td>
                                                <td><?php echo esc_html($webhook['action']); ?></td>
                                                <td><?php echo $webhook['enabled'] ? '✓ Enabled' : '✗ Disabled'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <p>
                            <button type="button" class="button button-secondary" id="ms-wc-sync-register-webhooks">
                                <?php echo esc_html__('Register/Update Webhooks', 'moysklad-wc-sync'); ?>
                            </button>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($current_tab === 'logs') : ?>
            <!-- Logs Tab -->
            <div class="ms-wc-sync-tab-content">
                <h2><?php echo esc_html__('Recent Logs', 'moysklad-wc-sync'); ?></h2>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 180px;"><?php echo esc_html__('Time', 'moysklad-wc-sync'); ?></th>
                            <th style="width: 100px;"><?php echo esc_html__('Level', 'moysklad-wc-sync'); ?></th>
                            <th><?php echo esc_html__('Message', 'moysklad-wc-sync'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)) : ?>
                            <tr>
                                <td colspan="3"><?php echo esc_html__('No logs found.', 'moysklad-wc-sync'); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($logs as $log) : 
                                $dt = new DateTime($log['log_time'], new DateTimeZone('UTC'));
                                $dt->setTimezone(wp_timezone());
                            ?>
                                <tr>
                                    <td><?php echo esc_html($dt->format('Y-m-d H:i:s')); ?></td>
                                    <td>
                                        <span class="ms-wc-sync-log-level ms-wc-sync-log-<?php echo esc_attr($log['log_level']); ?>">
                                            <?php echo esc_html(ucfirst($log['log_level'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo esc_html($log['message']); ?>
                                        <?php if (!empty($log['context'])) : ?>
                                            <details style="margin-top: 5px;">
                                                <summary style="cursor: pointer; color: #2271b1;">Show details</summary>
                                                <pre style="background: #f6f7f7; padding: 10px; border-radius: 3px; font-size: 11px; margin-top: 5px; overflow-x: auto;"><?php echo esc_html($log['context']); ?></pre>
                                            </details>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>
</div>