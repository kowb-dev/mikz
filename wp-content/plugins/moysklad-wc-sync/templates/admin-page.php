<?php
/**
 * Admin settings page template
 *
 * @package MoySklad_WC_Sync
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php esc_html_e('MoySklad WooCommerce Sync', 'moysklad-wc-sync'); ?></h1>
    
    <div id="ms-wc-sync-message"></div>
    
    <?php settings_errors(); ?>
    
    <h2 class="nav-tab-wrapper">
        <a href="#settings" class="nav-tab nav-tab-active"><?php esc_html_e('Settings', 'moysklad-wc-sync'); ?></a>
        <a href="#stock-sync" class="nav-tab"><?php esc_html_e('Stock Sync', 'moysklad-wc-sync'); ?></a>
        <a href="#webhooks" class="nav-tab"><?php esc_html_e('Webhooks', 'moysklad-wc-sync'); ?></a>
        <a href="#logs" class="nav-tab"><?php esc_html_e('Logs', 'moysklad-wc-sync'); ?></a>
        <a href="#status" class="nav-tab"><?php esc_html_e('Status', 'moysklad-wc-sync'); ?></a>
    </h2>
        
        <div id="settings" class="tab-content active">
            <form method="post" action="options.php" class="ms-wc-sync-settings-form">
                <?php settings_fields('ms_wc_sync_settings'); ?>
                
                <h2><?php esc_html_e('API Settings', 'moysklad-wc-sync'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_api_token"><?php esc_html_e('API Token', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="ms_wc_sync_api_token" name="ms_wc_sync_api_token" 
                                   value="<?php echo esc_attr(get_option('ms_wc_sync_api_token', '')); ?>" 
                                   class="regular-text" autocomplete="off" />
                            <p class="description">
                                <?php esc_html_e('Enter your MoySklad API token. You can generate it in your MoySklad account.', 'moysklad-wc-sync'); ?>
                            </p>
                            <button type="button" id="test-connection" class="button button-secondary">
                                <?php esc_html_e('Test Connection', 'moysklad-wc-sync'); ?>
                            </button>
                            <span id="connection-result"></span>
                        </td>
                    </tr>
                </table>
                
                <h2><?php esc_html_e('Full Sync Settings', 'moysklad-wc-sync'); ?></h2>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_batch_size"><?php esc_html_e('Batch Size', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="ms_wc_sync_batch_size" name="ms_wc_sync_batch_size" 
                                   value="<?php echo esc_attr(get_option('ms_wc_sync_batch_size', 50)); ?>" 
                                   min="10" max="1000" step="10" />
                            <p class="description">
                                <?php esc_html_e('Number of products to process in each batch. Lower values use less memory but take longer.', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_max_time"><?php esc_html_e('Max Execution Time', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="ms_wc_sync_max_time" name="ms_wc_sync_max_time" 
                                   value="<?php echo esc_attr(get_option('ms_wc_sync_max_time', 180)); ?>" 
                                   min="30" max="3600" step="30" />
                            <p class="description">
                                <?php esc_html_e('Maximum execution time in seconds. Sync will stop after this time to prevent timeouts.', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div id="stock-sync" class="tab-content">
            <form method="post" action="options.php" class="ms-wc-sync-settings-form">
                <?php settings_fields('ms_wc_sync_settings'); ?>
                
                <h2><?php esc_html_e('Stock Synchronization Settings', 'moysklad-wc-sync'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Configure how stock levels are synchronized between MoySklad and WooCommerce.', 'moysklad-wc-sync'); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_stock_interval"><?php esc_html_e('Stock Sync Interval', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <select id="ms_wc_sync_stock_interval" name="ms_wc_sync_stock_interval">
                                <?php
                                $current_interval = get_option('ms_wc_sync_stock_interval', 'ms_wc_sync_10min');
                                foreach (Cron::get_stock_sync_intervals() as $value => $label) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($value),
                                        selected($current_interval, $value, false),
                                        esc_html($label)
                                    );
                                }
                                ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e('How often to check for stock changes. This is used as a fallback when webhooks are not available.', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_store_id"><?php esc_html_e('MoySklad Store ID', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ms_wc_sync_store_id" name="ms_wc_sync_store_id" 
                                   value="<?php echo esc_attr(get_option('ms_wc_sync_store_id', '')); ?>" 
                                   class="regular-text" />
                            <p class="description">
                                <?php esc_html_e('ID of the store to use for stock levels. Leave empty to use all stores (sum of all stock).', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_reservation_mode"><?php esc_html_e('Reservation Handling', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <select id="ms_wc_sync_reservation_mode" name="ms_wc_sync_reservation_mode">
                                <?php
                                $current_mode = get_option('ms_wc_sync_reservation_mode', 'ignore');
                                foreach (Admin::get_reservation_modes() as $value => $label) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr($value),
                                        selected($current_mode, $value, false),
                                        esc_html($label)
                                    );
                                }
                                ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e('How to handle reserved stock in MoySklad when updating WooCommerce.', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <h3><?php esc_html_e('Stock Sync Status', 'moysklad-wc-sync'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Last Stock Sync', 'moysklad-wc-sync'); ?></th>
                        <td>
                            <?php if ($stock_last_run): ?>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($stock_last_run))); ?>
                            <?php else: ?>
                                <?php esc_html_e('Never', 'moysklad-wc-sync'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Next Stock Sync', 'moysklad-wc-sync'); ?></th>
                        <td>
                            <?php echo esc_html($schedule_info['stock_sync']['next_run_formatted']); ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Last Results', 'moysklad-wc-sync'); ?></th>
                        <td>
                            <?php if (!empty($stock_last_results)): ?>
                                <ul>
                                    <li><?php printf(esc_html__('Updated: %d', 'moysklad-wc-sync'), $stock_last_results['updated'] ?? 0); ?></li>
                                    <li><?php printf(esc_html__('Skipped: %d', 'moysklad-wc-sync'), $stock_last_results['skipped'] ?? 0); ?></li>
                                    <li><?php printf(esc_html__('Duration: %.2f seconds', 'moysklad-wc-sync'), $stock_last_results['duration'] ?? 0); ?></li>
                                </ul>
                            <?php else: ?>
                                <?php esc_html_e('No results available', 'moysklad-wc-sync'); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <button type="button" id="ms-wc-sync-stock-manual" class="button button-primary">
                        <?php esc_html_e('Run Stock Sync Now', 'moysklad-wc-sync'); ?>
                    </button>
                </p>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div id="webhooks" class="tab-content">
            <form method="post" action="options.php" class="ms-wc-sync-settings-form">
                <?php settings_fields('ms_wc_sync_settings'); ?>
                
                <h2><?php esc_html_e('Webhook Settings', 'moysklad-wc-sync'); ?></h2>
                <p class="description">
                    <?php esc_html_e('Webhooks allow real-time updates when stock changes in MoySklad, reducing server load and providing faster updates.', 'moysklad-wc-sync'); ?>
                </p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_use_webhooks"><?php esc_html_e('Use Webhooks', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="ms_wc_sync_use_webhooks" name="ms_wc_sync_use_webhooks" 
                                       value="yes" <?php checked(get_option('ms_wc_sync_use_webhooks', 'no'), 'yes'); ?> />
                                <?php esc_html_e('Enable webhook integration with MoySklad', 'moysklad-wc-sync'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('When enabled, the plugin will register webhooks with MoySklad to receive real-time stock updates.', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="ms_wc_sync_webhook_secret"><?php esc_html_e('Webhook Secret', 'moysklad-wc-sync'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="ms_wc_sync_webhook_secret" name="ms_wc_sync_webhook_secret" 
                                   value="<?php echo esc_attr(get_option('ms_wc_sync_webhook_secret', '')); ?>" 
                                   class="regular-text" autocomplete="off" />
                            <p class="description">
                                <?php esc_html_e('Secret key used to verify webhook requests. Leave empty to generate automatically.', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <h3><?php esc_html_e('Webhook Status', 'moysklad-wc-sync'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Webhook URL', 'moysklad-wc-sync'); ?></th>
                        <td>
                            <code><?php echo esc_html($webhook_url); ?></code>
                            <p class="description">
                                <?php esc_html_e('This is the URL that will receive webhook notifications from MoySklad.', 'moysklad-wc-sync'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Registered Webhooks', 'moysklad-wc-sync'); ?></th>
                        <td>
                            <?php if ($webhook_status['success']): ?>
                                <?php if ($webhook_status['count'] > 0): ?>
                                    <span class="dashicons dashicons-yes" style="color: green;"></span>
                                    <?php printf(
                                        esc_html__('%d webhooks registered', 'moysklad-wc-sync'),
                                        $webhook_status['count']
                                    ); ?>
                                    <ul>
                                        <?php foreach ($webhook_status['webhooks'] as $webhook): ?>
                                            <li>
                                                <?php printf(
                                                    esc_html__('Entity: %s, Action: %s, Status: %s', 'moysklad-wc-sync'),
                                                    esc_html($webhook['entityType']),
                                                    esc_html($webhook['action']),
                                                    $webhook['enabled'] ? esc_html__('Enabled', 'moysklad-wc-sync') : esc_html__('Disabled', 'moysklad-wc-sync')
                                                ); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span class="dashicons dashicons-no" style="color: red;"></span>
                                    <?php esc_html_e('No webhooks registered', 'moysklad-wc-sync'); ?>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="dashicons dashicons-warning" style="color: orange;"></span>
                                <?php esc_html_e('Could not check webhook status', 'moysklad-wc-sync'); ?>
                                <p class="description">
                                    <?php echo esc_html($webhook_status['message'] ?? ''); ?>
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <button type="button" id="ms-wc-sync-register-webhooks" class="button button-secondary">
                        <?php esc_html_e('Register Webhooks', 'moysklad-wc-sync'); ?>
                    </button>
                </p>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <div id="logs" class="tab-content">
            <h2><?php esc_html_e('Sync Logs', 'moysklad-wc-sync'); ?></h2>
            
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Time', 'moysklad-wc-sync'); ?></th>
                        <th><?php esc_html_e('Level', 'moysklad-wc-sync'); ?></th>
                        <th><?php esc_html_e('Message', 'moysklad-wc-sync'); ?></th>
                        <th><?php esc_html_e('Context', 'moysklad-wc-sync'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4"><?php esc_html_e('No logs available', 'moysklad-wc-sync'); ?></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log['created_at']))); ?></td>
                                <td>
                                    <?php
                                    $level_class = '';
                                    switch ($log['level']) {
                                        case 'error':
                                            $level_class = 'error';
                                            break;
                                        case 'warning':
                                            $level_class = 'warning';
                                            break;
                                        case 'info':
                                            $level_class = 'info';
                                            break;
                                    }
                                    ?>
                                    <span class="log-level <?php echo esc_attr($level_class); ?>">
                                        <?php echo esc_html(ucfirst($log['level'])); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($log['message']); ?></td>
                                <td>
                                    <?php if (!empty($log['context'])): ?>
                                        <button type="button" class="toggle-context button button-small">
                                            <?php esc_html_e('Show', 'moysklad-wc-sync'); ?>
                                        </button>
                                        <div class="context-data" style="display: none;">
                                            <pre><?php echo esc_html(json_encode(json_decode($log['context']), JSON_PRETTY_PRINT)); ?></pre>
                                        </div>
                                    <?php else: ?>
                                        <?php esc_html_e('No context', 'moysklad-wc-sync'); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div id="status" class="tab-content">
            <h2><?php esc_html_e('Sync Status', 'moysklad-wc-sync'); ?></h2>
            
            <h3><?php esc_html_e('Full Sync', 'moysklad-wc-sync'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Last Full Sync', 'moysklad-wc-sync'); ?></th>
                    <td>
                        <?php if ($last_run): ?>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_run))); ?>
                        <?php else: ?>
                            <?php esc_html_e('Never', 'moysklad-wc-sync'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Next Full Sync', 'moysklad-wc-sync'); ?></th>
                    <td>
                        <?php echo esc_html($schedule_info['full_sync']['next_run_formatted']); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Last Results', 'moysklad-wc-sync'); ?></th>
                    <td>
                        <?php if (!empty($last_results)): ?>
                            <ul>
                                <li><?php printf(esc_html__('Success: %d', 'moysklad-wc-sync'), $last_results['success'] ?? 0); ?></li>
                                <li><?php printf(esc_html__('Failed: %d', 'moysklad-wc-sync'), $last_results['failed'] ?? 0); ?></li>
                                <li><?php printf(esc_html__('Created: %d', 'moysklad-wc-sync'), $last_results['created'] ?? 0); ?></li>
                                <li><?php printf(esc_html__('Updated: %d', 'moysklad-wc-sync'), $last_results['updated'] ?? 0); ?></li>
                                <li><?php printf(esc_html__('Skipped: %d', 'moysklad-wc-sync'), $last_results['skipped'] ?? 0); ?></li>
                                <li><?php printf(esc_html__('Duration: %.2f seconds', 'moysklad-wc-sync'), $last_results['duration'] ?? 0); ?></li>
                            </ul>
                        <?php else: ?>
                            <?php esc_html_e('No results available', 'moysklad-wc-sync'); ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Sync Status', 'moysklad-wc-sync'); ?></th>
                    <td>
                        <?php if ($is_locked): ?>
                            <span class="sync-status running">
                                <span class="dashicons dashicons-update"></span>
                                <?php esc_html_e('Sync is currently running', 'moysklad-wc-sync'); ?>
                            </span>
                            <?php if ($lock_info): ?>
                                <p class="description">
                                    <?php printf(
                                        esc_html__('Running for %d seconds', 'moysklad-wc-sync'),
                                        $lock_info['elapsed']
                                    ); ?>
                                </p>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="sync-status idle">
                                <span class="dashicons dashicons-yes"></span>
                                <?php esc_html_e('Idle', 'moysklad-wc-sync'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <div class="sync-progress" style="display: none;">
                <h3><?php esc_html_e('Sync Progress', 'moysklad-wc-sync'); ?></h3>
                <div class="progress-bar-container">
                    <div class="progress-bar" style="width: 0%;"></div>
                </div>
                <p class="progress-text"></p>
            </div>
            
            <p>
                <button type="button" id="run-sync" class="button button-primary" <?php disabled($is_locked); ?>>
                    <?php esc_html_e('Run Full Sync Now', 'moysklad-wc-sync'); ?>
                </button>
                
                <?php if ($is_locked): ?>
                    <button type="button" id="reset-lock" class="button button-secondary">
                        <?php esc_html_e('Reset Lock', 'moysklad-wc-sync'); ?>
                    </button>
                <?php endif; ?>
                
                <button type="button" id="reschedule-cron" class="button button-secondary">
                    <?php esc_html_e('Reschedule Cron', 'moysklad-wc-sync'); ?>
                </button>
            </p>
        </div>
    </div>
</div>

<style>
    .ms-wc-sync-tabs .nav-tab-wrapper {
        margin-bottom: 1em;
    }
    
    .ms-wc-sync-tabs .tab-content {
        display: none;
    }
    
    .ms-wc-sync-tabs .tab-content.active {
        display: block;
    }
    
    .sync-status {
        display: inline-flex;
        align-items: center;
        padding: 5px 10px;
        border-radius: 3px;
    }
    
    .sync-status.running {
        background-color: #f0f6fc;
        color: #0073aa;
    }
    
    .sync-status.idle {
        background-color: #f0f6fc;
        color: #46b450;
    }
    
    .sync-status .dashicons {
        margin-right: 5px;
    }
    
    .progress-bar-container {
        width: 100%;
        height: 20px;
        background-color: #f0f0f0;
        border-radius: 3px;
        margin-bottom: 10px;
    }
    
    .progress-bar {
        height: 100%;
        background-color: #0073aa;
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .log-level {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .log-level.error {
        background-color: #ffebe8;
        color: #d63638;
    }
    
    .log-level.warning {
        background-color: #fff8e5;
        color: #bd8600;
    }
    
    .log-level.info {
        background-color: #f0f6fc;
        color: #0073aa;
    }
    
    .context-data pre {
        background-color: #f6f7f7;
        padding: 10px;
        border-radius: 3px;
        overflow: auto;
        max-height: 200px;
    }
</style>

<script>
jQuery(document).ready(function($) {
    // Tab navigation
    $('.ms-wc-sync-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        $('.ms-wc-sync-tabs .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.ms-wc-sync-tabs .tab-content').removeClass('active');
        $(target).addClass('active');
    });
    
    // Toggle context data
    $('.toggle-context').on('click', function() {
        var $context = $(this).next('.context-data');
        
        if ($context.is(':visible')) {
            $context.hide();
            $(this).text('<?php echo esc_js(__('Show', 'moysklad-wc-sync')); ?>');
        } else {
            $context.show();
            $(this).text('<?php echo esc_js(__('Hide', 'moysklad-wc-sync')); ?>');
        }
    });
    
    // Test connection
    $('#test-connection').on('click', function() {
        var $button = $(this);
        var $result = $('#connection-result');
        
        $button.prop('disabled', true);
        $result.html('<span class="spinner is-active" style="float: none; margin: 0 5px;"></span> <?php echo esc_js(__('Testing...', 'moysklad-wc-sync')); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ms_wc_sync_test_connection',
                nonce: msWcSync.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<span class="dashicons dashicons-yes" style="color: green;"></span> ' + response.data.message);
                } else {
                    $result.html('<span class="dashicons dashicons-no" style="color: red;"></span> ' + response.data.message);
                }
            },
            error: function() {
                $result.html('<span class="dashicons dashicons-no" style="color: red;"></span> <?php echo esc_js(__('Connection test failed', 'moysklad-wc-sync')); ?>');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    
    // Run sync
    $('#run-sync').on('click', function() {
        var $button = $(this);
        
        if (confirm('<?php echo esc_js(__('Are you sure you want to run a full sync? This may take several minutes.', 'moysklad-wc-sync')); ?>')) {
            $button.prop('disabled', true);
            $('.sync-progress').show();
            $('.progress-bar').css('width', '0%');
            $('.progress-text').text('<?php echo esc_js(__('Starting synchronization...', 'moysklad-wc-sync')); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ms_wc_sync_manual',
                    nonce: msWcSync.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.progress-bar').css('width',